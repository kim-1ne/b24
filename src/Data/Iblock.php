<?php

namespace B24\Devtools\Data;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\PropertyTable;

class Iblock
{
    const CACHE_TTL = 86400;
    const PREFIX_TABLE_PROPERTIES_IBLOCK = 'b_iblock_element_prop_s';

    private static array $code2id = [];
    private static array $id2code = [];
    private ?int $iblockId = null;

    private function __construct(
        private DataManager|string $className,
        private string $iblockCode
    ) {}

    public static function getIdByCode(string $code): ?int
    {
        if (!empty($id = self::$code2id[$code])) {
            return $id;
        }

        $res = IblockTable::getList([
            'select' => ['ID'],
            'filter' => ['CODE' => $code],
            'cache' => self::CACHE_TTL,
        ])->fetch();

        if ($res === false) {
            return null;
        }

        $id = $res['ID'];
        self::$code2id[$code] = $id;
        self::$id2code[$id] = $code;

        return $id;
    }

    public static function getCodeById(int $iblockId): ?string
    {
        if (!empty($code = self::$id2code[$iblockId])) {
            return $code;
        }

        $res = IblockTable::getList([
            'select' => ['CODE'],
            'filter' => ['ID' => $iblockId],
            'cache' => self::CACHE_TTL,
        ])->fetch();

        if ($res === false) {
            return null;
        }

        $code = $res['CODE'];

        self::$code2id[$code] = $iblockId;
        self::$id2code[$iblockId] = $code;

        return $code;
    }

    private static function prepareCode(string $code): string
    {
        return ucfirst(
            StringHelper::stringToCamelCase($code)
        );
    }

    private static function packOrmClass(string $code): string
    {
        return '\Bitrix\Iblock\Elements\Element' . $code . 'Table';
    }

    private static function generateClassName(string $code): string
    {
        return self::packOrmClass(self::prepareCode($code));
    }

    public static function generateByCode(string $iblockCode): ?self
    {
        $class = self::generateClassName($iblockCode);

        if (class_exists($class)) {
            return new self($class, $iblockCode);
        }

        return null;
    }

    public static function generateById(int $iblockId): ?self
    {
        $code = self::getCodeById($iblockId);

        if ($code === null) {
            return null;
        }

        if (empty($code)) {
            throw new \Exception('У инфоблока не задан символьный код API');
        }

        $self = self::generateByCode($code);

        if ($self === null) {
            return null;
        }

        $self->iblockId = $iblockId;
        return $self;
    }

    public function getIblockId(): int
    {
        if ($this->iblockId === null) {
            $this->iblockId = self::getIdByCode($this->iblockCode);
        }

        return $this->iblockId;
    }

    public static function getPropertyIdByIblockIdAndName(int $iblockId, string $name): ?int
    {
        $res = PropertyTable::getList([
            'select' => ['ID'],
            'filter' => ['IBLOCK_ID' => $iblockId, 'NAME' => $name],
            'cache' => self::CACHE_TTL,
            'limit' => 1,
        ])->fetch();

        if ($res === false) {
            return null;
        }

        return $res['ID'];
    }

    public function getPropertyIdByName(string $name): ?int
    {
        return self::getPropertyIdByIblockIdAndName($this->getIblockId(), $name);
    }

    public function getListWithProperties(
        array $properties,
        array $select = [],
        array $filter = [],
        array $order = [],
        array $runtime = [],
        ?int $limit = null,
        ?int $offset = null,
    )
    {
        foreach ($properties as $name) {
            $select[$name . '_VALUE'] = $name . '.VALUE';
        }

        $parameters = [
            'select' => $select,
            'cache' => self::CACHE_TTL,
        ];

        if (!empty($filter)) {
            $parameters['filter'] = $filter;
        }

        if (!empty($order)) {
            $parameters['order'] = $order;
        }

        if (!empty($limit)) {
            $parameters['limit'] = $limit;
        }

        if (!empty($offset)) {
            $parameters['offset'] = $offset;
        }

        if (!empty($runtime)) {
            $parameters['runtime'] = $runtime;
        }

        return $this->className::getList($parameters);
    }

    public function getTableProperties(): string
    {
        return self::getTablePropertiesByIblockId($this->getIblockId());
    }

    private static function getTablePropertiesByIblockId(string $iblockId): string
    {
        return self::PREFIX_TABLE_PROPERTIES_IBLOCK . $iblockId;
    }
}
