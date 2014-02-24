<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage seo
 * @copyright 2001-2013 Bitrix
 */
namespace Bitrix\Seo;

use Bitrix\Main\IO\Path;
use Bitrix\Main\IO\File;
use Bitrix\Main\SiteTable;
use Bitrix\Main\Text\Converter;

class SitemapFile
	extends File
{
	const XML_HEADER = '<?xml version="1.0" encoding="UTF-8"?>';

	const FILE_HEADER = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
	const FILE_FOOTER = '</urlset>';

	const ENTRY_TPL = '<url><loc>%s</loc><lastmod>%s</lastmod></url>';

	protected $settings = array();
	protected $parser = false;

	public function __construct($fileName, $arSettings)
	{
		$this->settings = array(
			'SITE_ID' => $arSettings['SITE_ID'],
			'PROTOCOL' => $arSettings['PROTOCOL'] == 'https' ? 'https' : 'http',
			'DOMAIN' => $arSettings['DOMAIN'],
		);

		$arSite = SiteTable::getRow(array("filter" => array("LID" => $this->settings['SITE_ID'])));

		$this->siteRoot = Path::combine(
			SiteTable::getDocumentRoot($this->settings['SITE_ID']),
			$arSite['DIR']
		);

		if(substr($fileName, -4) != '.xml')
			$fileName .= '.xml';

		parent::__construct($this->siteRoot.'/'.$fileName, $this->settings['SITE_ID']);
	}

	public function addHeader()
	{
		$this->putContents(self::XML_HEADER.self::FILE_HEADER);
	}

	public function addEntry($entry)
	{
		$this->putContents(
			sprintf(
				self::ENTRY_TPL,
				Converter::getXmlConverter()->encode($entry['XML_LOC']),
				Converter::getXmlConverter()->encode($entry['XML_LASTMOD'])
			), self::APPEND
		);
	}

	public function appendEntry($entry)
	{
		$fd = $this->open('r+');
		fseek($fd, $this->getFileSize()-strlen(self::FILE_FOOTER));
		fwrite($fd, sprintf(
			self::ENTRY_TPL,
			Converter::getXmlConverter()->encode($entry['XML_LOC']),
			Converter::getXmlConverter()->encode($entry['XML_LASTMOD'])
		).self::FILE_FOOTER);
		fclose($fd);
	}

	public function addFileEntry(File $f)
	{
		if($f->isExists() && !$f->isSystem())
		{
			$this->addEntry(array(
				'XML_LOC' => $this->settings['PROTOCOL'].'://'.$this->settings['DOMAIN'].$this->getFileUrl($f),
				'XML_LASTMOD' => date('c', $f->getModificationTime()),
			));
		}
	}

	public function addIBlockEntry($url, $modifiedDate)
	{
		$this->addEntry(array(
			'XML_LOC' => $this->settings['PROTOCOL'].'://'.$this->settings['DOMAIN'].$url,
			'XML_LASTMOD' => date('c', $modifiedDate - \CTimeZone::getOffset()),
		));
	}

	public function appendIBlockEntry($url, $modifiedDate)
	{
		if($this->isExists())
		{
			$this->appendEntry(array(
				'XML_LOC' => $this->settings['PROTOCOL'].'://'.$this->settings['DOMAIN'].$url,
				'XML_LASTMOD' => date('c', $modifiedDate - \CTimeZone::getOffset()),
			));
		}
		else
		{
			$this->addHeader();
			$this->addIBlockEntry($url, $modifiedDate);
			$this->addFooter();
		}
	}

	public function isNotEmpty()
	{
		if($this->isExists())
		{
			$c = $this->getContents();
			return strlen($c) > 0 && $c != self::XML_HEADER.self::FILE_HEADER;
		}

		return false;
	}

	public function addFooter()
	{
		$this->putContents(self::FILE_FOOTER, self::APPEND);
	}

	public function getSiteRoot()
	{
		return $this->siteRoot;
	}

	public function getUrl()
	{
		return $this->settings['PROTOCOL'].'://'.$this->settings['DOMAIN'].$this->getFileUrl($this);
	}

	public function parse()
	{
		if(!$this->parser)
		{
			if($this->isExists())
			{
				$this->parser = new \CDataXML();
				$this->parser->loadString($this->getContents());
			}
		}

		return $this->parser;
	}

	protected function getFileUrl(File $f)
	{
		static $arIndexNames;
		if(!is_array($arIndexNames))
		{
			$arIndexNames = GetDirIndexArray();
		}

		if (substr($this->path, 0, strlen($this->documentRoot)) === $this->documentRoot)
		{
			$path = '/'.substr($f->getPath(), strlen($this->documentRoot));
		}

		$path = Path::convertLogicalToUri($path);

		$path = in_array($f->getName(), $arIndexNames)
			? str_replace('/'.$f->getName(), '/', $path)
			: $path;

		return '/'.ltrim($path, '/');
	}
}