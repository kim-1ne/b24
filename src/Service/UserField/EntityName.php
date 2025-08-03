<?php

namespace B24\Devtools\Service\UserField;

use Bitrix\Crm\Model\Dynamic\TypeTable;
use Bitrix\Crm\Service\Container;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\ORM\Query\Result;

class EntityName
{
    private const
        PREFIX_HL = 'HLBLOCK_',
        PREFIX_SP = 'CRM_';

    private static function queryHlBlock(array $filter): Result
    {
        \CModule::IncludeModule("highloadblock");

        return HighloadBlockTable::getList([
            'filter' => $filter,
            'select' => ['ID'],
            'cache' => UserFieldService::CACHE_TTL,
            'limit' => 1,
        ]);
    }

    private static function queryDynamic(array $filter): Result
    {
        return TypeTable::getList([
            'filter' => $filter,
            'select' => ['ID'],
            'limit' => 1,
            'cache' => UserFieldService::CACHE_TTL,
        ]);
    }

    public static function byHlBlockId(int $hlBlockId): string
    {
        return self::PREFIX_HL . $hlBlockId;
    }

    public static function byHlBlockName(string $name): ?string
    {
        $res = self::queryHlBlock(['NAME' => $name])->fetch();

        if ($res === false) {
            return null;
        }

        return self::byHlBlockId($res['ID']);
    }

    private static function bySmartProcessFilter(array $filter): ?string
    {
        $res = self::queryDynamic($filter)->fetch();

        if ($res === false) {
            return null;
        }

        return self::PREFIX_SP . $res['ID'];
    }

    public static function byEntityTypeId(int $entityTypeId): ?string
    {
        return Container::getInstance()
            ->getFactory($entityTypeId)
            ?->getUserFieldEntityId()
        ;
    }

    public static function bySmartProcessName(string $name): ?string
    {
        return self::bySmartProcessFilter(['NAME' => $name]);
    }

    public static function bySmartProcessCode(string $code): ?string
    {
        return self::bySmartProcessFilter(['CODE' => $code]);
    }
}
