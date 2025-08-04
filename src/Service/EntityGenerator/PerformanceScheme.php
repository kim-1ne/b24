<?php

namespace Kim1ne\B24\Service\EntityGenerator;

use Bitrix\Main\Text\StringHelper;

class PerformanceScheme
{
    private bool $isStartFieldMethod = false;

    private \CPerfomanceSchema $obSchema;

    private ?array $fields;
    private array $aliases;

    public function __construct(
        public readonly TableScheme $tableScheme
    ) {
        $this->init();
    }

    public function getFields(): array
    {
        if ($this->isStartFieldMethod) {
            return $this->fields;
        }

        foreach ($this->getParents() as $columnName => $parentInfo) {
            if ($this->tableScheme->shortAliases())
            {
                $this->aliases[] = 'Bitrix\Main\ORM\Fields\Relations\Reference';
            }

            $parentTableParts = explode('_', $parentInfo['PARENT_TABLE']);
            array_shift($parentTableParts);
            $parentModuleNamespace = ucfirst($parentTableParts[0]);
            $parentClassName = StringHelper::snake2camel(implode('_', $parentTableParts));

            $columnNameEx = preg_replace('/_ID$/', '', $columnName);
            if (isset($descriptions[$columnNameEx])) {
                $columnNameEx = mb_strtoupper($parentClassName);
            }

            $descriptions[$columnNameEx] = ' * &lt;li&gt; ' . $columnName
                . ' reference to {@link \\Bitrix\\' . $parentModuleNamespace
                . '\\' . $parentClassName . 'Table}'
                . "\n";

            $this->fields[$columnNameEx] =
                ($this->tableScheme->useMapIndex() ? "'" . $columnNameEx . "' => " : '')
                . 'new ' . $this->tableScheme->referencePrefix() . "Reference(\n"
                . "\t\t\t\t'" . $columnNameEx . "',\n"
                . "\t\t\t\t'\Bitrix\\" . $parentModuleNamespace . '\\' . $parentClassName . "',\n"
                . "\t\t\t\t['=this." . $columnName . "' => 'ref." . $parentInfo['PARENT_COLUMN'] . "'],\n"
                . "\t\t\t\t['join_type' => 'LEFT']\n"
                . "\t\t\t),\n";
        }

        $this->isStartFieldMethod = true;

        return $this->fields;
    }

    public function getAliases(): array
    {
        return $this->aliases;
    }

    private function init(): void
    {
        $this->obSchema = new \CPerfomanceSchema();
        $this->fields = $this->tableScheme->getFields();
        $this->aliases = $this->tableScheme->getAliases();
    }

    public function getParents(): array
    {
        return $this->obSchema->GetParents($this->tableScheme->tableName);
    }
}
