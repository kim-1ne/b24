<?php

namespace Kim1ne\B24\Service\UserField;

use Bitrix\Crm\Service\Display\Field\IblockElementField;
use Bitrix\Crm\UserField\Types\ElementType;
use Bitrix\Main\UserField\Types\BooleanType;
use Bitrix\Main\UserField\Types\DateTimeType;
use Bitrix\Main\UserField\Types\DateType;
use Bitrix\Main\UserField\Types\DoubleType;
use Bitrix\Main\UserField\Types\EnumType;
use Bitrix\Main\UserField\Types\FileType;
use Bitrix\Main\UserField\Types\IntegerType;
use Bitrix\Main\UserField\Types\StringType;
use Bitrix\Main\UserFieldLangTable;

class UserField
{
    public readonly int $id;
    public readonly string $entityId;
    public readonly string $fieldCode;
    public readonly string $userTypeId;
    public readonly string $xmlId;
    public readonly bool $isMultiple;
    public readonly bool $isMandatory;
    public readonly array $settings;

    private ?array $lang = null;

    private ?EnumCollection $enumCollection = null;

    public function __construct(array $data)
    {
        $this->prepare($data);
    }

    private function prepare(array $data): void
    {
        $this->id = $data['ID'] ?? 0;
        $this->entityId = $data['ENTITY_ID'] ?? '';
        $this->fieldCode = $data['FIELD_NAME'] ?? '';
        $this->userTypeId = $data['USER_TYPE_ID'] ?? 0;
        $this->xmlId = $data['XML_ID'] ?? '';
        $this->isMultiple = $data['MULTIPLE'] === 'Y';
        $this->isMandatory = $data['MANDATORY'] === 'Y';
        $this->settings = $data['SETTINGS'] ?? [];
    }

    public function isEnumType(): bool
    {
        return EnumType::USER_TYPE_ID === $this->userTypeId;
    }

    public function isIblockElementType(): bool
    {
        return IblockElementField::TYPE === $this->userTypeId;
    }

    public function isBooleanType(): bool
    {
        return BooleanType::USER_TYPE_ID === $this->userTypeId;
    }

    public function isStringType(): bool
    {
        return StringType::USER_TYPE_ID === $this->userTypeId;
    }

    public function isIntegerType(): bool
    {
        return IntegerType::USER_TYPE_ID === $this->userTypeId;
    }

    public function isDoubleType(): bool
    {
        return DoubleType::USER_TYPE_ID === $this->userTypeId;
    }

    public function isDateType(): bool
    {
        return DateType::USER_TYPE_ID === $this->userTypeId;
    }

    public function isFileType(): bool
    {
        return FileType::USER_TYPE_ID === $this->userTypeId;
    }

    public function isDateTimeType(): bool
    {
        return DateTimeType::USER_TYPE_ID === $this->userTypeId;
    }

    public function isCrmType(): bool
    {
        return ElementType::USER_TYPE_ID === $this->userTypeId;
    }

    public function isEmployee(): bool
    {
        return \Bitrix\Mobile\Field\Type\UserField::TYPE === $this->userTypeId;
    }

    public function getEnums(): ?EnumCollection
    {
        if ($this->isEnumType() === false) {
            return null;
        }

        if ($this->enumCollection !== null) {
            return $this->enumCollection;
        }

        $enum = new \CUserFieldEnum();
        $res = $enum->GetList([], ['USER_FIELD_ID' => $this->id]);

        $data = [];
        while ($row = $res->fetch()) {
            $data[] = new Enum(
                $row['ID'],
                $row['VALUE'],
                $row['XML_ID'],
                $row['USER_FIELD_ID'],
                $row['DEF'],
            );
        }

        $enumCollection = new EnumCollection($data);

        $this->enumCollection = $enumCollection;

        return $enumCollection;
    }

    public function getLang(): ?array
    {
        if ($this->lang !== null) {
            return $this->lang;
        }

        $res = UserFieldLangTable::getList([
            'filter' => [
                'USER_FIELD_ID' => $this->id,
            ],
            'cache' => UserFieldService::CACHE_TTL,
        ])->fetch();

        if ($res === null) {
            return null;
        }

        $this->lang = $res;

        return $res;
    }
}
