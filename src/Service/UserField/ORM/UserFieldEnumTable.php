<?php

namespace Kim1ne\B24\Service\UserField\ORM;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

class UserFieldEnumTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'b_user_field_enum';
    }

    public static function getMap(): array
    {
        return [
            new IntegerField(
                'ID',
                [
                    'primary' => true,
                    'autocomplete' => true,
                    'title' => 'ID',
                ]
            ),
            new IntegerField(
                'USER_FIELD_ID',
                [
                    'title' => 'USER_FIELD_ID',
                ]
            ),
            new StringField(
                'VALUE',
                [
                    'required' => true,
                    'validation' => function()
                    {
                        return[
                            new LengthValidator(null, 255),
                        ];
                    },
                    'title' => 'VALUE',
                ]
            ),
            new BooleanField(
                'DEF',
                [
                    'values' => ['N', 'Y'],
                    'default' => 'N',
                    'title' => 'DEF',
                ]
            ),
            new IntegerField(
                'SORT',
                [
                    'default' => 500,
                    'title' => 'SORT',
                ]
            ),
            new StringField(
                'XML_ID',
                [
                    'required' => true,
                    'validation' => function()
                    {
                        return[
                            new LengthValidator(null, 255),
                        ];
                    },
                    'title' => 'XML_ID',
                ]
            ),
        ];
    }
}
