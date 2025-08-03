<?php

namespace B24\Devtools\Service\UserField;

use B24\Devtools\Service\UserField\ORM\UserFieldEnumTable;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM\Query\Result;
use Bitrix\Main\UserFieldTable;

/**
 * @method static null|self getByEntityTypeId(int $entityTypeId, string $fieldName, string $xmlId)
 * @method static null|self getBySmartProcessName(string $smartProcessName, string $fieldName, string $xmlId)
 * @method static null|self getBySmartProcessCode(string $smartProcessCode, string $fieldName, string $xmlId)
 * @method static null|self getByHlBlockName(string $hlBlockName, string $fieldName, string $xmlId)
 * @method static null|self getByHlBlockId(string $hlBlockId, string $fieldName, string $xmlId)
 */
class Enum
{
    public readonly bool $isDefault;

    public function __construct(
        public readonly int $id,
        public readonly string $value,
        public readonly string $xmlId,
        public readonly int $userFieldId,
        string $def,
    )
    {
        $this->isDefault = ($def === 'Y');
    }

    public static function __callStatic(string $name, array $arguments)
    {
        $search = 'getBy';

        if (str_starts_with($name, $search) === false) {
            return null;
        }

        $name = str_replace($search, 'by', $name);

        $argumentFirst = array_shift($arguments);
        $entityId = EntityName::{$name}($argumentFirst);

        if ($entityId === null) {
            return null;
        }

        return self::get($entityId, ...$arguments);
    }

    public static function get(string $entityId, string $fieldName, string $xmlId): ?self
    {
        $res = self::query($entityId, $fieldName, $xmlId)->fetch();

        if ($res === false) {
            return null;
        }

        return new self(
            $res['ENUM_ID'],
            $res['ENUM_VALUE'],
            $res['ENUM_XML_ID'],
            $res['ENUM_USER_FIELD_ID'],
            $res['ENUM_DEF']
        );
    }

    private static function query(string $entityId, string $fieldName, string $xmlId): Result
    {
        return UserFieldTable::getList([
            'filter' => [
                'ENTITY_ID' => $entityId,
                'FIELD_NAME' => $fieldName,
                'ENUM.XML_ID' => $xmlId,
            ],
            'select' => ['ENUM_' => 'ENUM'],
            'runtime' => [
                (new ReferenceField(
                    'ENUM',
                    UserFieldEnumTable::class,
                    ['this.ID' => 'ref.USER_FIELD_ID']
                ))->configureJoinType('inner')
            ],
            'cache' => UserFieldService::CACHE_TTL
        ]);
    }
}
