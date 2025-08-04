<?php

namespace Kim1ne\B24\Service\EntityGenerator;

use Bitrix\Main\Config\Option;
use Bitrix\Main\DB\Connection;
use Bitrix\Main\Text\StringHelper;

class TableScheme
{
    private \CPerfomanceTable $obTable;
    private ?bool $shortAliases = null;
    private ?bool $useMapIndex = null;
    private ?string $referencePrefix = null;

    private array $aliases = [];

    private ?array $fields = null;

    public function __construct(
        public readonly string $tableName,
        public Connection $connection
    ) {
        $this->init();
    }

    private function init(): void
    {
        $obTable = new \CPerfomanceTable();
        $obTable->Init($this->tableName, $this->connection);
        $this->obTable = $obTable;
    }

    public function getTableFields(bool $extended = true): array
    {
        return $this->obTable->GetTableFields(false, $extended);
    }

    public function getUniqueIndexes()
    {
        $arUniqueIndexes = $this->obTable->getUniqueIndexes();

        $hasID = false;
        foreach ($arUniqueIndexes as $indexName => $indexColumns) {
            if (array_values($indexColumns) === ['ID']) {
                $hasID = $indexName;
            }
        }

        if ($hasID) {
            $arUniqueIndexes = [$hasID => $arUniqueIndexes[$hasID]];
        }

        return $arUniqueIndexes;
    }

    private function type2classField(): array
    {
        return [
            'integer' => 'IntegerField',
            'float' => 'FloatField',
            'boolean' => 'BooleanField',
            'string' => 'StringField',
            'text' => 'TextField',
            'enum' => 'EnumField',
            'date' => 'DateField',
            'datetime' => 'DatetimeField'
        ];
    }

    private function getDateFunctions(): array
    {
        return [
            'curdate' => true,
            'current_date' => true,
            'current_time' => true,
            'current_timestamp' => true,
            'curtime' => true,
            'localtime' => true,
            'localtimestamp' => true,
            'now' => true
        ];
    }

    public function shortAliases(): bool
    {
        if ($this->shortAliases === null) {
            $this->shortAliases = Option::get('perfmon', 'tablet_short_aliases') === 'Y';
        }

        return $this->shortAliases;
    }

    public function useMapIndex(): bool
    {
        if (null === $this->useMapIndex) {
            $this->useMapIndex = Option::get('perfmon', 'tablet_use_map_index') === 'Y';
        }

        return $this->useMapIndex;
    }

    public function referencePrefix(): string
    {
        return $this->referencePrefix ?? '';
    }

    public function getFields(): array
    {
        if ($this->fields !== null) {
            return $this->fields;
        }

        $arUniqueIndexes = $this->getUniqueIndexes();

        $tableParts = [$this->tableName];

        $aliases = [
            'Bitrix\Main\Localization\Loc',
            'Bitrix\Main\ORM\Data\DataManager'
        ];

        $arValidators = [];
        $arMessages = [];

        $shortAliases = $this->shortAliases();
        $useValidationClosure = Option::get('perfmon', 'tablet_validation_closure') === 'Y';
        $objectSettings = Option::get('perfmon', 'tablet_object_settings') === 'Y';
        $useMapIndex = Option::get('perfmon', 'tablet_use_map_index') === 'Y';

        $referencePrefix = '';
        $dateFunctions = $this->getDateFunctions();

        if (!$shortAliases)
        {
            $fieldClassPrefix = 'Fields\\';
            $validatorPrefix = $fieldClassPrefix . 'Validators\\';
            $referencePrefix = $fieldClassPrefix . 'Relations\\';
            $datetimePrefix = 'Type\\';
            $aliases[] = 'Bitrix\Main\ORM\Fields';
        }

        $this->referencePrefix = $referencePrefix;

        $fieldClasses = $this->type2classField();

        $arFields = $this->getTableFields();

        $fields = [];
        $descriptions = [];

        foreach ($arFields as $columnName => $columnInfo)
        {
            $type = $columnInfo['orm_type'];
            if ($shortAliases)
            {
                $aliases[] = 'Bitrix\Main\ORM\Fields\\' . $fieldClasses[$type];
            }

            $match = [];
            if (
                preg_match('/^(.+)_TYPE$/', $columnName, $match)
                && $columnInfo['length'] == 4
                && isset($arFields[$match[1]])
            )
            {
                $columnInfo['nullable'] = true;
                $columnInfo['orm_type'] = 'enum';
                $columnInfo['enum_values'] = ["'text'", "'html'"];
                $columnInfo['length'] = '';
            }

            $columnInfo['default'] = (string)$columnInfo['default'];
            if ($columnInfo['default'] !== '')
            {
                $columnInfo['nullable'] = true;
            }

            switch ($type)
            {
                case 'integer':
                case 'float':
                    break;
                case 'boolean':
                    if ($columnInfo['default'] !== '')
                    {
                        $columnInfo['default'] = "'" . $columnInfo['default'] . "'";
                    }
                    $columnInfo['type'] = 'bool';
                    $columnInfo['length'] = '';
                    $columnInfo['enum_values'] = ["'N'", "'Y'"];
                    break;
                case 'string':
                case 'text':
                    $columnInfo['type'] = $columnInfo['orm_type'];
                    if ($columnInfo['default'] !== '')
                    {
                        $columnInfo['default'] = "'" . $columnInfo['default'] . "'";
                    }
                    break;
                case 'enum':
                    if ($columnInfo['default'] !== '' && !is_numeric($columnInfo['default']))
                    {
                        $columnInfo['default'] = "'" . $columnInfo['default'] . "'";
                    }
                    break;
                case 'date':
                case 'datetime':
                    if ($columnInfo['default'] !== '' && !is_numeric($columnInfo['default']))
                    {
                        $defaultValue = mb_strtolower($columnInfo['default']);
                        if (mb_strlen($defaultValue) > 2)
                        {
                            if (substr_compare($defaultValue, '()', -2, 2, true) === 0)
                            {
                                $defaultValue = mb_substr($defaultValue, 0, -2);
                            }
                        }
                        if (isset($dateFunctions[$defaultValue]))
                        {
                            if ($type == 'date')
                            {
                                if ($shortAliases)
                                {
                                    $aliases[] = 'Bitrix\Main\Type\Date';
                                }
                                else
                                {
                                    $aliases[] = 'Bitrix\Main\Type';
                                }
                                $columnInfo['default_text'] = 'current date';
                                $columnInfo['default'] = "function()\n"
                                    . "\t\t\t\t\t{\n"
                                    . "\t\t\t\t\t\treturn new " . $datetimePrefix . "Date();\n"
                                    . "\t\t\t\t\t}";
                            }
                            else
                            {
                                if ($shortAliases)
                                {
                                    $aliases[] = 'Bitrix\Main\Type\DateTime';
                                }
                                else
                                {
                                    $aliases[] = 'Bitrix\Main\Type';
                                }
                                $columnInfo['default_text'] = 'current datetime';
                                $columnInfo['default'] = "function()\n"
                                    . "\t\t\t\t\t{\n"
                                    . "\t\t\t\t\t\treturn new " . $datetimePrefix . "DateTime();\n"
                                    . "\t\t\t\t\t}";
                            }
                        }
                        else
                        {
                            $columnInfo['default'] = "'" . $columnInfo['default'] . "'";
                        }
                    }
                    break;
            }

            $primary = false;
            foreach ($arUniqueIndexes as $arColumns)
            {
                if (in_array($columnName, $arColumns))
                {
                    $primary = true;
                    break;
                }
            }

            $messageId = mb_strtoupper(implode('_', $tableParts) . '_ENTITY_' . $columnName . '_FIELD');
            $arMessages[$messageId] = '';

            $descriptions[$columnName] = ' * &lt;li&gt; ' . $columnName
                . ' ' . $columnInfo['type'] . ($columnInfo['length'] != '' ? '(' . $columnInfo['length'] . ')' : '')
                . ($columnInfo['orm_type'] === 'enum' || $columnInfo['orm_type'] === 'boolean' ?
                    ' (' . implode(', ', $columnInfo['enum_values']) . ')'
                    : ''
                )
                . ' ' . ($columnInfo['nullable'] ? 'optional' : 'mandatory')
                . ($columnInfo['default'] !== ''
                    ? ' default ' . ($columnInfo['default_text'] ?? $columnInfo['default'])
                    : ''
                )
                . "\n";

            $useValidator = false;
            $validateFunctionName = '';
            if (
                $columnInfo['orm_type'] === 'string'
                && $columnInfo['length'] > 0
            )
            {
                $useValidator = true;
                if ($shortAliases)
                {
                    $aliases[] = 'Bitrix\Main\ORM\Fields\Validators\LengthValidator';
                }
                if (!$useValidationClosure)
                {
                    $validateFunctionName = 'validate' . StringHelper::snake2camel($columnName);
                    $arValidators[$validateFunctionName] = [
                        'length' => $columnInfo['length'],
                        'field' => $columnName,
                    ];
                }
            }

            $size = 0;
            if ($columnInfo['orm_type'] === 'integer')
            {
                if (str_starts_with($columnInfo['type~'], 'tinyint'))
                {
                    $size = 1;
                }
                elseif (str_starts_with($columnInfo['type~'], 'smallint'))
                {
                    $size = 2;
                }
                elseif (str_starts_with($columnInfo['type~'], 'mediumint'))
                {
                    $size = 3;
                }
                elseif (str_starts_with($columnInfo['type~'], 'bigint'))
                {
                    $size = 8;
                }
            }

            if ($objectSettings)
            {
                $offset = ($useMapIndex ? "\t\t\t\t" : "\t\t\t");
                $initParams = $offset . "\t[]\n";
                if ($useValidator)
                {
                    if ($useValidationClosure)
                    {
                        $initParams =
                            $offset . "\t[\n"
                            . $offset . "\t\t'validation' => function()\n"
                            . $offset . "\t\t{\n"
                            . $offset . "\t\t\treturn[\n"
                            . $offset . "\t\t\t\tnew " . $validatorPrefix . 'LengthValidator(null, ' . $columnInfo['length'] . "),\n"
                            . $offset . "\t\t\t];\n"
                            . $offset . "\t\t},\n"
                            . $offset . "\t]\n"
                        ;
                    }
                    else
                    {
                        $initParams =
                            $offset . "\t[\n"
                            . $offset . "\t\t'validation' => [_" . '_CLASS_' . "_, '" . $validateFunctionName . "']\n"
                            . $offset . "\t]\n"
                        ;
                    }
                }

                $fields[$columnName] =
                    ""
                    . ($useMapIndex ? "'" . $columnName . "' => " : '')
                    . '(new ' . $fieldClassPrefix . $fieldClasses[$type] . "('" . $columnName . "',\n"
                    . $initParams
                    . $offset . "))->configureTitle(Loc::getMessage('" . $messageId . "'))\n"
                    . ($primary ? $offset . "\t\t->configurePrimary(true)\n" : '')
                    . ($columnInfo['increment'] ? $offset . "\t\t->configureAutocomplete(true)\n" : '')
                    . (!$primary && $columnInfo['nullable'] === false ? $offset . "\t\t->configureRequired(true)\n" : '')
                    . ($columnInfo['orm_type'] === 'boolean'
                        ? $offset . "\t\t->configureValues(" . implode(', ', $columnInfo['enum_values']) . ")\n"
                        : ''
                    )
                    . ($columnInfo['orm_type'] === 'enum'
                        ? $offset . "\t\t->configureValues([" . implode(', ', $columnInfo['enum_values']) . "])\n"
                        : ''
                    )
                    . ($columnInfo['default'] !== ''
                        ? $offset . "\t\t->configureDefaultValue(" . $columnInfo['default'] . ")\n"
                        : ''
                    )
                    . ($size
                        ? $offset . "\t\t->configureSize(" . $size . ")\n"
                        : ''
                    )
                ;

                $fields[$columnName] =
                    mb_substr($fields[$columnName], 0, -1)
                    . "\n"
                    . "\t\t\t,\n"
                ;
            }
            else
            {
                $validator = '';
                if ($useValidator)
                {
                    if ($useValidationClosure)
                    {
                        $offset = "\t\t\t\t\t";
                        $validator =
                            $offset . "'validation' => function()\n"
                            . $offset . "{\n"
                            . $offset . "\treturn[\n"
                            . $offset . "\t\tnew " . $validatorPrefix . 'LengthValidator(null, ' . $columnInfo['length'] . "),\n"
                            . $offset . "\t];\n"
                            . $offset . "},\n"
                        ;
                    }
                    else
                    {
                        $validator = "\t\t\t\t\t'validation' => [_" . '_CLASS_' . "_, '" . $validateFunctionName . "'],\n";
                    }
                }

                $fields[$columnName] =
                    ""
                    . ($useMapIndex ? "'" . $columnName . "' => " : '')
                    . 'new ' . $fieldClassPrefix . $fieldClasses[$type] . "(\n"
                    . "\t\t\t\t'" . $columnName . "',\n"
                    . "\t\t\t\t[\n"
                    . ($primary ? "\t\t\t\t\t'primary' => true,\n" : '')
                    . ($columnInfo['increment'] ? "\t\t\t\t\t'autocomplete' => true,\n" : '')
                    . (!$primary && $columnInfo['nullable'] === false ? "\t\t\t\t\t'required' => true,\n" : '')
                    . ($columnInfo['orm_type'] === 'boolean' || $columnInfo['orm_type'] === 'enum'
                        ? "\t\t\t\t\t'values' => [" . implode(', ', $columnInfo['enum_values']) . "],\n"
                        : ''
                    )
                    . ($columnInfo['default'] !== '' ? "\t\t\t\t\t'default' => " . $columnInfo['default'] . ",\n" : '')
                    . $validator
                    . "\t\t\t\t\t'title' => Loc::getMessage('" . $messageId . "'),\n"
                    . ($size ? "\t\t\t\t\t'size' => " . $size . ",\n" : '')
                    . "\t\t\t\t]\n"
                    . "\t\t\t),\n"
                ;
            }
        }

        $this->aliases = array_merge($this->aliases, $aliases);
        $this->fields = $fields;

        return $fields;
    }

    public function getAliases(): array
    {
        return $this->aliases;
    }
}
