<?php
namespace Bitrix\Iblock;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class PropertyEnumerationTable extends Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_iblock_property_enum';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'PROPERTY_ID' => array(
				'data_type' => 'integer'
			),
			'PROPERTY' => array(
				'data_type' => 'Property',
				'reference' => array('=this.PROPERTY_ID' => 'ref.ID')
			),
			'VALUE' => array(
				'data_type' => 'string'
			),
			'DEF' => array(
				'data_type' => 'boolean',
				'values' => array('N','Y')
			),
			'SORT' => array(
				'data_type' => 'integer'
			),
			'XML_ID' => array(
				'data_type' => 'string'
			),
			'TMP_ID' => array(
				'data_type' => 'string'
			),
		);
	}
}
