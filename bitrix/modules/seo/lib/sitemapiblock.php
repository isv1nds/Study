<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage seo
 * @copyright 2001-2013 Bitrix
 */
namespace Bitrix\Seo;

use Bitrix\Main\Entity;
use Bitrix\Main\Text\Converter;

class SitemapIblockTable extends Entity\DataManager
{
	const ACTIVE = 'Y';
	const INACTIVE = 'N';

	const TYPE_ELEMENT = 'E';
	const TYPE_SECTION = 'S';

	protected static $iblockCache = array();

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_seo_sitemap_iblock';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'SITEMAP_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'IBLOCK_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'SITEMAP' => array(
				'data_type' => 'Bitrix\Seo\SitemapTable',
				'reference' => array('=this.SITEMAP_ID' => 'ref.ID'),
			),
			'IBLOCK' => array(
				'data_type' => 'Bitrix\Iblock\IblockTable',
				'reference' => array('=this.IBLOCK_ID' => 'ref.ID'),
			),
		);

		return $fieldsMap;
	}

	public static function clearBySitemap($sitemapId)
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$query = $connection->query("
DELETE
FROM ".self::getTableName()."
WHERE SITEMAP_ID='".intval($sitemapId)."'
");
	}

	public static function getByIblock($arFields, $itemType)
	{
		$arSitemaps = array();

		if(!isset(self::$iblockCache[$arFields['IBLOCK_ID']]))
		{
			self::$iblockCache[$arFields['IBLOCK_ID']] = array();

			$dbRes = self::getList(array(
				'filter' => array(
					'IBLOCK_ID' => $arFields['IBLOCK_ID']
				),
				'select' => array('SITEMAP_ID',
					'SITE_ID' => 'SITEMAP.SITE_ID', 'SITEMAP_SETTINGS' => 'SITEMAP.SETTINGS',
					'IBLOCK_CODE' => 'IBLOCK.CODE', 'IBLOCK_XML_ID' => 'IBLOCK.XML_ID',
					'DETAIL_PAGE_URL' => 'IBLOCK.DETAIL_PAGE_URL',
					'SECTION_PAGE_URL' => 'IBLOCK.SECTION_PAGE_URL',
				)
			));

			while($arRes = $dbRes->fetch())
			{
				self::$iblockCache[$arFields['IBLOCK_ID']][] = $arRes;
			}
		}

		foreach(self::$iblockCache[$arFields['IBLOCK_ID']] as $arRes)
		{
			$arSitemapSettings = unserialize($arRes['SITEMAP_SETTINGS']);

			if($itemType == self::TYPE_SECTION)
			{
				$bAdd = self::checkSection(
					$arFields['ID'],
					$arSitemapSettings['IBLOCK_SECTION_SECTION'][$arFields['IBLOCK_ID']],
					$arSitemapSettings['IBLOCK_SECTION'][$arFields['IBLOCK_ID']]
				);
			}
			else
			{
				if(is_array($arFields['IBLOCK_SECTION']) && count($arFields['IBLOCK_SECTION']) > 0)
				{
					foreach($arFields['IBLOCK_SECTION'] as $sectionId)
					{
						$bAdd = self::checkSection(
							$sectionId,
							$arSitemapSettings['IBLOCK_SECTION_ELEMENT'][$arFields['IBLOCK_ID']],
							$arSitemapSettings['IBLOCK_ELEMENT'][$arFields['IBLOCK_ID']]
						);

						if($bAdd)
						{
							break;
						}
					}
				}
				else
				{
					$bAdd = $arSitemapSettings['IBLOCK_ELEMENT'][$arFields['IBLOCK_ID']] == 'Y';
				}
			}

			if($bAdd)
			{
				$arSitemaps[] = array(
					'IBLOCK_CODE' => $arRes['IBLOCK_CODE'],
					'IBLOCK_XML_ID' => $arRes['IBLOCK_XML_ID'],
					'DETAIL_PAGE_URL' => $arRes['DETAIL_PAGE_URL'],
					'SECTION_PAGE_URL' => $arRes['SECTION_PAGE_URL'],
					'SITE_ID' => $arRes['SITE_ID'],
					'PROTOCOL' => $arSitemapSettings['PROTO'] == 1 ? 'https' : 'http',
					'DOMAIN' => $arSitemapSettings['DOMAIN'],
					'ROBOTS' => $arSitemapSettings['ROBOTS'],
					'SITEMAP_DIR' => $arSitemapSettings['DIR'],
					'SITEMAP_FILE' => $arSitemapSettings['FILENAME_INDEX'],
					'SITEMAP_FILE_IBLOCK' => $arSitemapSettings['FILENAME_IBLOCK'],
				);
			}
		}

		return $arSitemaps;
	}

	public static function checkSection($SECTION_ID, $arSectionSettings, $defaultValue)
	{
		$value = $defaultValue;

		if(is_array($arSectionSettings) && count($arSectionSettings) > 0)
		{
			while ($SECTION_ID > 0)
			{
				if(isset($arSectionSettings[$SECTION_ID]))
				{
					$value = $arSectionSettings[$SECTION_ID];
					break;
				}

				$dbRes = \CIBlockSection::getList(array(), array('ID' => $SECTION_ID), false, array('ID', 'IBLOCK_SECTION_ID'));
				$arSection = $dbRes->fetch();

				$SECTION_ID = $arSection["IBLOCK_SECTION_ID"];
			}
		}

		return $value === 'Y';
	}
}

class SitemapIblock
{
	public static function __callStatic($name, $arguments)
	{
		$arFields = $arguments[0];
		$name = ToUpper($name);

		if($name != 'ADDELEMENT' && $name != 'ADDSECTION')
		{
			return;
		}

		if($arFields["ID"] > 0 && $arFields['IBLOCK_ID'] > 0 && $arFields['ACTIVE'] == 'Y')
		{
			$arSitemaps = SitemapIblockTable::getByIblock(
				$arFields,
				$name == 'ADDSECTION' ? SitemapIblockTable::TYPE_SECTION : SitemapIblockTable::TYPE_ELEMENT
			);

			$arFields['TIMESTAMP_X'] = ConvertTimeStamp(false, "FULL");

			if(isset($arFields['IBLOCK_SECTION']) && is_array($arFields['IBLOCK_SECTION']))
			{
				$arFields['IBLOCK_SECTION_ID'] = min($arFields['IBLOCK_SECTION']);
			}

			if(count($arSitemaps) > 0)
			{
				$rule = array(
					'url' => $name == 'ADDSECTION'
						? \CIBlock::replaceDetailUrl($arSitemaps[0]['SECTION_PAGE_URL'], $arFields, false, "S")
						: \CIBlock::replaceDetailUrl($arSitemaps[0]['DETAIL_PAGE_URL'], $arFields, false, "E"),
					'lastmod' => MakeTimeStamp($arFields['TIMESTAMP_X'])
				);

				foreach($arSitemaps as $arSitemap)
				{
					$fileName = str_replace(
						array('#IBLOCK_ID#', '#IBLOCK_CODE#', '#IBLOCK_XML_ID#'),
						array($arFields['IBLOCK_ID'], $arSitemap['IBLOCK_CODE'], $arSitemap['IBLOCK_XML_ID']),
						$arSitemap['SITEMAP_FILE_IBLOCK']
					);

					$sitemapFile = new SitemapFile($fileName, $arSitemap);
					$sitemapFile->appendIblockEntry($rule['url'], $rule['lastmod']);

					$sitemapIndex = new SitemapIndex($arSitemap['SITEMAP_FILE'], $arSitemap);
					$sitemapIndex->appendIndexEntry($sitemapFile);

					if($arSitemap['ROBOTS'] == 'Y')
					{
						$robotsFile = new RobotsFile($arSitemap['SITE_ID']);
						$robotsFile->addRule(
							array(RobotsFile::SITEMAP_RULE, $sitemapIndex->getUrl())
						);
					}
				}

			}
		}
	}
}
