<?php

namespace B24\Devtools\Service\EntityGenerator;

use B24\Devtools\Data\StringHelper;
use Bitrix\Main\Application;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\ORM\Entity;

class EntityGenerator
{
    private ?string $namespace = 'B24\Devtools\Entity';

    public readonly string $tableNameNotPrefix;

    public readonly string $tableName;

    private ?string $className = null;

    public function __construct(
        string $tableName,
    )
    {
        $this->prepare($tableName);
        $this->clearPrefix();
    }

    private function prepare(string $tableName): void
    {
        $connection = Application::getConnection();

        $helper = $connection->getSqlHelper();

        $this->tableName = $helper->forSql($tableName);
    }

    public function setNamespace(?string $namespace): self
    {
        $this->namespace = $namespace;
        return $this;
    }

    public function setClassName(?string $className): self
    {
        $this->className = $className;
        return $this;
    }

    private function generateClassName(): string
    {
        if (!empty($this->className)) {
            return $this->className;
        }

        $className = StringHelper::stringToCamelCase($this->tableNameNotPrefix) . 'Table';

        return ucfirst($className);
    }

    private function generateFileName(): string
    {
        return $this->generateClassName() . '.php';
    }

    public function getFullClassName(): string|DataManager
    {
        $str = '\\';

        $className = $this->generateClassName();

        if (!empty($this->namespace)) {
            $str .= $this->namespace . '\\' . $className;
        } else {
            $str .= $className;
        }

        return $str;
    }

    private function clearPrefix(): void
    {
        $this->tableNameNotPrefix = mb_substr($this->tableName, 2);
    }

    private function check(): void
    {
        $res = Application::getConnection()->query("show table status like '" . $this->tableName . "'")->fetch();

        if ($res === false) {
            throw new \Exception('Not Found table=' . $this->tableName);
        }

        $comment = $res["Comment"];
        if ($comment === 'VIEW') {
            throw new \Exception('This table for view');
        }
    }

    private function getFilePath(string $fileDir): string
    {
        $filePath = $fileDir . '/' . $this->generateFileName();
        return str_replace('//', '/', $filePath);
    }

    public function createFile(string $fileDir): bool
    {
        return $this->createFileByFilePath(
            $this->getFilePath($fileDir)
        );
    }

    private function createFileByFilePath(string $filePath): bool
    {
        $this->check();

        $string = '<?php' . PHP_EOL . PHP_EOL . $this->createStringClass();

        file_put_contents($filePath, $string);

        return file_exists($filePath);
    }

    public function createFileIfNotExists(string $fileDir): bool
    {
        $filePath = $this->getFilePath($fileDir);

        if (file_exists($filePath)) {
            return true;
        }

        return $this->createFileByFilePath($filePath);
    }

    public static function generateEntity(string $tableName): Entity
    {
        $self = new self($tableName);
        $className = $self->getFullClassName();

        if (class_exists($className)) {
            return $className::getEntity();
        }

        $self->check();

        $string = $self->createStringClass();

        eval($string);

        return $className::getEntity();
    }

    private function compileFieldsForTable(): array
    {
        \CModule::IncludeModule("perfmon");

        $connection = Application::getConnection();

        $scheme = new PerformanceScheme(
            new TableScheme($this->tableName, $connection)
        );

        $fields = $scheme->getFields();
        $aliases = $scheme->getAliases();

        return [$aliases, $fields];
    }

    private function createStringClass(): string
    {
        [$aliases, $fields] = $this->compileFieldsForTable();

        $className = $this->generateClassName();

        $string = '';

        if (!empty($this->namespace)) {
            $string .= 'namespace ' . $this->namespace . ';' . PHP_EOL . PHP_EOL;
        }

        $aliases = array_unique($aliases);

        foreach ($aliases as $alias) {
            $string .= "use " . $alias . ';' . PHP_EOL;
        }

        $string .= PHP_EOL;

        $string .= 'class ' . $className . ' extends DataManager';
        $string .= PHP_EOL . '{' . PHP_EOL;

        $string .= $this->createTableMethod() . PHP_EOL;

        $string .= $this->createMapMethod($fields) . '}';

        return $string;
    }

    private function createTableMethod(): string
    {
        return $this->createMethod(
            true,
            'getTableName',
            'string',
            'return ' . '"' . $this->tableName . '"' . ';'
        );
    }

    private function createMapMethod(array $fields): string
    {
        $body = 'return [' . PHP_EOL . PHP_EOL;

        foreach ($fields as $columnName => $columnInfo) {
            $body .= "\t\t\t'" . $columnName . "' => " . $columnInfo;
        }

        $body .= "\t\t];";

        return $this->createMethod(
            true,
            'getMap',
            'array',
            $body
        );
    }

    private function createMethod(bool $isStatic, string $methodName, string $return, string $body): string
    {
        $string = "\t";

        $string .= 'public ';
        $string .= ($isStatic) ? 'static' : '';


        $string .= ' function ' . $methodName . '(): ' . $return;
        $string .= "\n\t{";
        $string .= "\n\t\t" . $body;
        $string .= "\n\t}\n";

        return $string;
    }
}
