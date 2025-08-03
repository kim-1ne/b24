<?php

namespace B24\Devtools\Service\UserField;

use B24\Devtools\Trait\SingletonTrait;
use Bitrix\Main\ORM\Query\Result;
use Bitrix\Main\UserFieldTable;

/**
 * @method null|UserField getFieldByEntityTypeId(int $entityTypeId, string $fieldName)
 * @method null|UserField getFieldBySmartProcessName(string $smartProcessName, string $fieldName)
 * @method null|UserField getFieldBySmartProcessCode(string $smartProcessCode, string $fieldName)
 * @method null|UserField getFieldByHlBlockName(string $hlBlockName, string $fieldName)
 * @method null|UserField getFieldByHlBlockId(string $hlBlockId, string $fieldName)
 */
class UserFieldService
{
    use SingletonTrait;

    const CACHE_TTL = 86400;

    /**
     * @var UserField[]
     */
    private array $fields = [];

    public function __construct() {}

    public function __call(string $name, array $arguments)
    {
        $search = 'getFieldBy';

        if (str_starts_with($name, $search) === false) {
            return null;
        }

        return $this->getFieldWrap(
            str_replace($search, 'by', $name),
            ...$arguments
        );
    }

    private function getKeyInCache(string $entityId, string $fieldName): string
    {
        return $entityId . ':' . $fieldName;
    }

    private function setInCache(string $entityId, string $fieldName, UserField $field): void
    {
        $this->fields[$this->getKeyInCache($entityId, $fieldName)] = $field;
    }

    private function inCache(string $entityId, string $fieldName): false|UserField
    {
        return $this->fields[$this->getKeyInCache($entityId, $fieldName)] ?? false;
    }

    public function getField(string $entityId, string $fieldName): ?UserField
    {
        if ($field = $this->inCache($entityId, $fieldName)) {
            return $field;
        }

        $data = $this->queryField($entityId, $fieldName)->fetch();

        if ($data === false) {
            return null;
        }

        $field = new UserField($data);

        $this->setInCache($entityId, $fieldName, $field);

        return $field;
    }

    private function queryField(string $entityId, string $fieldName): Result
    {
        return UserFieldTable::getList([
            'filter' => [
                'ENTITY_ID' => $entityId,
                'FIELD_NAME' => $fieldName,
            ],
            'cache' => self::CACHE_TTL,
            'limit' => 1
        ]);
    }

    private function getFieldWrap(string $method, string $value, string $fieldName): ?UserField
    {
        $entityId = EntityName::$method($value);

        if ($entityId === null) {
            return null;
        }

        return $this->getField($entityId, $fieldName);
    }
}
