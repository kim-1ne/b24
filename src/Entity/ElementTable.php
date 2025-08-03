<?php

namespace B24DevtoolsEntity;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\Relations\Reference;

class ElementTable extends DataManager
{
	public static function getTableName(): string
	{
		return "b_iblock_element";
	}

	public static function getMap(): array
	{
		return [

			'ID' => new IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => Loc::getMessage('B_IBLOCK_ELEMENT_ENTITY_ID_FIELD'),
				]
			),
			'TIMESTAMP_X' => new DatetimeField(
				'TIMESTAMP_X',
				[
					'title' => Loc::getMessage('B_IBLOCK_ELEMENT_ENTITY_TIMESTAMP_X_FIELD'),
				]
			),
			'MODIFIED_BY' => new Reference(
				'MODIFIED_BY',
				'\Bitrix\User\User',
				['=this.MODIFIED_BY' => 'ref.ID'],
				['join_type' => 'LEFT']
			),
			'DATE_CREATE' => new DatetimeField(
				'DATE_CREATE',
				[
					'title' => Loc::getMessage('B_IBLOCK_ELEMENT_ENTITY_DATE_CREATE_FIELD'),
				]
			),
			'CREATED_BY' => new Reference(
				'CREATED_BY',
				'\Bitrix\User\User',
				['=this.CREATED_BY' => 'ref.ID'],
				['join_type' => 'LEFT']
			),
			'IBLOCK_ID' => new IntegerField(
				'IBLOCK_ID',
				[
					'default' => 0,
					'title' => Loc::getMessage('B_IBLOCK_ELEMENT_ENTITY_IBLOCK_ID_FIELD'),
				]
			),
			'IBLOCK_SECTION_ID' => new IntegerField(
				'IBLOCK_SECTION_ID',
				[
					'title' => Loc::getMessage('B_IBLOCK_ELEMENT_ENTITY_IBLOCK_SECTION_ID_FIELD'),
				]
			),
			'ACTIVE' => new BooleanField(
				'ACTIVE',
				[
					'values' => ['N', 'Y'],
					'default' => 'Y',
					'title' => Loc::getMessage('B_IBLOCK_ELEMENT_ENTITY_ACTIVE_FIELD'),
				]
			),
			'ACTIVE_FROM' => new DatetimeField(
				'ACTIVE_FROM',
				[
					'title' => Loc::getMessage('B_IBLOCK_ELEMENT_ENTITY_ACTIVE_FROM_FIELD'),
				]
			),
			'ACTIVE_TO' => new DatetimeField(
				'ACTIVE_TO',
				[
					'title' => Loc::getMessage('B_IBLOCK_ELEMENT_ENTITY_ACTIVE_TO_FIELD'),
				]
			),
			'SORT' => new IntegerField(
				'SORT',
				[
					'default' => 500,
					'title' => Loc::getMessage('B_IBLOCK_ELEMENT_ENTITY_SORT_FIELD'),
				]
			),
			'NAME' => new StringField(
				'NAME',
				[
					'required' => true,
					'validation' => function()
					{
						return[
							new LengthValidator(null, 255),
						];
					},
					'title' => Loc::getMessage('B_IBLOCK_ELEMENT_ENTITY_NAME_FIELD'),
				]
			),
			'PREVIEW_PICTURE' => new Reference(
				'PREVIEW_PICTURE',
				'\Bitrix\File\File',
				['=this.PREVIEW_PICTURE' => 'ref.ID'],
				['join_type' => 'LEFT']
			),
			'PREVIEW_TEXT' => new TextField(
				'PREVIEW_TEXT',
				[
					'title' => Loc::getMessage('B_IBLOCK_ELEMENT_ENTITY_PREVIEW_TEXT_FIELD'),
				]
			),
			'PREVIEW_TEXT_TYPE' => new StringField(
				'PREVIEW_TEXT_TYPE',
				[
					'values' => ['text', 'html'],
					'default' => 'text',
					'title' => Loc::getMessage('B_IBLOCK_ELEMENT_ENTITY_PREVIEW_TEXT_TYPE_FIELD'),
				]
			),
			'DETAIL_PICTURE' => new Reference(
				'DETAIL_PICTURE',
				'\Bitrix\File\File',
				['=this.DETAIL_PICTURE' => 'ref.ID'],
				['join_type' => 'LEFT']
			),
			'DETAIL_TEXT' => new TextField(
				'DETAIL_TEXT',
				[
					'title' => Loc::getMessage('B_IBLOCK_ELEMENT_ENTITY_DETAIL_TEXT_FIELD'),
				]
			),
			'DETAIL_TEXT_TYPE' => new StringField(
				'DETAIL_TEXT_TYPE',
				[
					'values' => ['text', 'html'],
					'default' => 'text',
					'title' => Loc::getMessage('B_IBLOCK_ELEMENT_ENTITY_DETAIL_TEXT_TYPE_FIELD'),
				]
			),
			'SEARCHABLE_CONTENT' => new TextField(
				'SEARCHABLE_CONTENT',
				[
					'title' => Loc::getMessage('B_IBLOCK_ELEMENT_ENTITY_SEARCHABLE_CONTENT_FIELD'),
				]
			),
			'WF_STATUS_ID' => new IntegerField(
				'WF_STATUS_ID',
				[
					'default' => 1,
					'title' => Loc::getMessage('B_IBLOCK_ELEMENT_ENTITY_WF_STATUS_ID_FIELD'),
				]
			),
			'WF_PARENT_ELEMENT_ID' => new IntegerField(
				'WF_PARENT_ELEMENT_ID',
				[
					'title' => Loc::getMessage('B_IBLOCK_ELEMENT_ENTITY_WF_PARENT_ELEMENT_ID_FIELD'),
				]
			),
			'WF_NEW' => new StringField(
				'WF_NEW',
				[
					'validation' => function()
					{
						return[
							new LengthValidator(null, 1),
						];
					},
					'title' => Loc::getMessage('B_IBLOCK_ELEMENT_ENTITY_WF_NEW_FIELD'),
				]
			),
			'WF_LOCKED_BY' => new Reference(
				'WF_LOCKED_BY',
				'\Bitrix\User\User',
				['=this.WF_LOCKED_BY' => 'ref.ID'],
				['join_type' => 'LEFT']
			),
			'WF_DATE_LOCK' => new DatetimeField(
				'WF_DATE_LOCK',
				[
					'title' => Loc::getMessage('B_IBLOCK_ELEMENT_ENTITY_WF_DATE_LOCK_FIELD'),
				]
			),
			'WF_COMMENTS' => new TextField(
				'WF_COMMENTS',
				[
					'title' => Loc::getMessage('B_IBLOCK_ELEMENT_ENTITY_WF_COMMENTS_FIELD'),
				]
			),
			'IN_SECTIONS' => new BooleanField(
				'IN_SECTIONS',
				[
					'values' => ['N', 'Y'],
					'default' => 'N',
					'title' => Loc::getMessage('B_IBLOCK_ELEMENT_ENTITY_IN_SECTIONS_FIELD'),
				]
			),
			'XML_ID' => new StringField(
				'XML_ID',
				[
					'validation' => function()
					{
						return[
							new LengthValidator(null, 255),
						];
					},
					'title' => Loc::getMessage('B_IBLOCK_ELEMENT_ENTITY_XML_ID_FIELD'),
				]
			),
			'CODE' => new StringField(
				'CODE',
				[
					'validation' => function()
					{
						return[
							new LengthValidator(null, 255),
						];
					},
					'title' => Loc::getMessage('B_IBLOCK_ELEMENT_ENTITY_CODE_FIELD'),
				]
			),
			'TAGS' => new StringField(
				'TAGS',
				[
					'validation' => function()
					{
						return[
							new LengthValidator(null, 255),
						];
					},
					'title' => Loc::getMessage('B_IBLOCK_ELEMENT_ENTITY_TAGS_FIELD'),
				]
			),
			'TMP_ID' => new StringField(
				'TMP_ID',
				[
					'validation' => function()
					{
						return[
							new LengthValidator(null, 40),
						];
					},
					'title' => Loc::getMessage('B_IBLOCK_ELEMENT_ENTITY_TMP_ID_FIELD'),
				]
			),
			'WF_LAST_HISTORY_ID' => new IntegerField(
				'WF_LAST_HISTORY_ID',
				[
					'title' => Loc::getMessage('B_IBLOCK_ELEMENT_ENTITY_WF_LAST_HISTORY_ID_FIELD'),
				]
			),
			'SHOW_COUNTER' => new IntegerField(
				'SHOW_COUNTER',
				[
					'title' => Loc::getMessage('B_IBLOCK_ELEMENT_ENTITY_SHOW_COUNTER_FIELD'),
				]
			),
			'SHOW_COUNTER_START' => new DatetimeField(
				'SHOW_COUNTER_START',
				[
					'title' => Loc::getMessage('B_IBLOCK_ELEMENT_ENTITY_SHOW_COUNTER_START_FIELD'),
				]
			),
			'IBLOCK' => new Reference(
				'IBLOCK',
				'\Bitrix\Iblock\Iblock',
				['=this.IBLOCK_ID' => 'ref.ID'],
				['join_type' => 'LEFT']
			),
			'WF_PARENT_ELEMENT' => new Reference(
				'WF_PARENT_ELEMENT',
				'\Bitrix\Iblock\IblockElement',
				['=this.WF_PARENT_ELEMENT_ID' => 'ref.ID'],
				['join_type' => 'LEFT']
			),
			'IBLOCK_SECTION' => new Reference(
				'IBLOCK_SECTION',
				'\Bitrix\Iblock\IblockSection',
				['=this.IBLOCK_SECTION_ID' => 'ref.ID'],
				['join_type' => 'LEFT']
			),
		];
	}
}