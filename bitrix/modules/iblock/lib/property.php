<?php
namespace Bitrix\Iblock;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class PropertyTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> TIMESTAMP_X datetime mandatory default 'CURRENT_TIMESTAMP'
 * <li> IBLOCK_ID int mandatory
 * <li> NAME string(255) mandatory
 * <li> ACTIVE bool optional default 'Y'
 * <li> SORT int optional default 500
 * <li> CODE string(50) optional
 * <li> DEFAULT_VALUE string optional
 * <li> PROPERTY_TYPE enum ('S', 'N', 'F', 'E' or 'G') optional default 'S'
 * <li> ROW_COUNT int optional default 1
 * <li> COL_COUNT int optional default 30
 * <li> LIST_TYPE enum ('C' or 'L') optional default 'L'
 * <li> MULTIPLE bool optional default 'N'
 * <li> XML_ID string(100) optional
 * <li> FILE_TYPE string(200) optional
 * <li> MULTIPLE_CNT int optional
 * <li> TMP_ID string(40) optional
 * <li> LINK_IBLOCK_ID int optional
 * <li> WITH_DESCRIPTION bool optional default 'N'
 * <li> SEARCHABLE bool optional default 'N'
 * <li> FILTRABLE bool optional default 'N'
 * <li> IS_REQUIRED bool optional default 'N'
 * <li> VERSION enum (1 or 2) optional default 1
 * <li> USER_TYPE string(255) optional
 * <li> USER_TYPE_SETTINGS string optional
 * <li> HINT string(255) optional
 * <li> LINK_IBLOCK reference to {@link \Bitrix\Iblock\IblockTable}
 * <li> IBLOCK reference to {@link \Bitrix\Iblock\IblockTable}
 * </ul>
 *
 * @package Bitrix\Iblock
 **/

class PropertyTable extends Entity\DataManager
{
	const CHECKBOX = 'C';
	const LISTBOX = 'L';

	const STRING = 'S';
	const NUMBER = 'N';
	const FILE = 'F';
	const ELEMENT = 'E';
	const SECTION = 'G';

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_iblock_property';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_ID_FIELD'),
			),
			'TIMESTAMP_X' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_TIMESTAMP_X_FIELD'),
			),
			'IBLOCK_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_IBLOCK_ID_FIELD'),
			),
			'NAME' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateName'),
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_NAME_FIELD'),
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N','Y'),
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_ACTIVE_FIELD'),
			),
			'SORT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_SORT_FIELD'),
			),
			'CODE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateCode'),
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_CODE_FIELD'),
			),
			'DEFAULT_VALUE' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_DEFAULT_VALUE_FIELD'),
			),
			'PROPERTY_TYPE' => array(
				'data_type' => 'enum',
				'values' => array(self::STRING, self::NUMBER, self::FILE, self::ELEMENT, self::SECTION),
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_PROPERTY_TYPE_FIELD'),
			),
			'ROW_COUNT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_ROW_COUNT_FIELD'),
			),
			'COL_COUNT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_COL_COUNT_FIELD'),
			),
			'LIST_TYPE' => array(
				'data_type' => 'enum',
				'values' => array(self::LISTBOX, self::CHECKBOX),
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_LIST_TYPE_FIELD'),
			),
			'MULTIPLE' => array(
				'data_type' => 'boolean',
				'values' => array('N','Y'),
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_MULTIPLE_FIELD'),
			),
			'XML_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateXmlId'),
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_XML_ID_FIELD'),
			),
			'FILE_TYPE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateFileType'),
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_FILE_TYPE_FIELD'),
			),
			'MULTIPLE_CNT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_MULTIPLE_CNT_FIELD'),
			),
			'TMP_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateTmpId'),
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_TMP_ID_FIELD'),
			),
			'LINK_IBLOCK_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_LINK_IBLOCK_ID_FIELD'),
			),
			'WITH_DESCRIPTION' => array(
				'data_type' => 'boolean',
				'values' => array('N','Y'),
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_WITH_DESCRIPTION_FIELD'),
			),
			'SEARCHABLE' => array(
				'data_type' => 'boolean',
				'values' => array('N','Y'),
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_SEARCHABLE_FIELD'),
			),
			'FILTRABLE' => array(
				'data_type' => 'boolean',
				'values' => array('N','Y'),
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_FILTRABLE_FIELD'),
			),
			'IS_REQUIRED' => array(
				'data_type' => 'boolean',
				'values' => array('N','Y'),
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_IS_REQUIRED_FIELD'),
			),
			'VERSION' => array(
				'data_type' => 'enum',
				'values' => array(1, 2),
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_VERSION_FIELD'),
			),
			'USER_TYPE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateUserType'),
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_USER_TYPE_FIELD'),
			),
			'USER_TYPE_SETTINGS' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_USER_TYPE_SETTINGS_FIELD'),
			),
			'HINT' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateHint'),
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_HINT_FIELD'),
			),
			'LINK_IBLOCK' => array(
				'data_type' => 'Bitrix\Iblock\Iblock',
				'reference' => array('=this.LINK_IBLOCK_ID' => 'ref.ID'),
			),
			'IBLOCK' => array(
				'data_type' => 'Bitrix\Iblock\Iblock',
				'reference' => array('=this.IBLOCK_ID' => 'ref.ID'),
			),
		);
	}
	public static function validateName()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}
	public static function validateCode()
	{
		return array(
			new Entity\Validator\Length(null, 50),
		);
	}
	public static function validateXmlId()
	{
		return array(
			new Entity\Validator\Length(null, 100),
		);
	}
	public static function validateFileType()
	{
		return array(
			new Entity\Validator\Length(null, 200),
		);
	}
	public static function validateTmpId()
	{
		return array(
			new Entity\Validator\Length(null, 40),
		);
	}
	public static function validateUserType()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}
	public static function validateHint()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}
}