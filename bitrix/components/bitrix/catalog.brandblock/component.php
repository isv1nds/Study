<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
{
	ShowError(GetMessage("IBLOCK_CBB_IBLOCK_NOT_INSTALLED"));
	return false;
}

if(!CModule::IncludeModule('highloadblock'))
{
	ShowError(GetMessage("IBLOCK_CBB_HLIBLOCK_NOT_INSTALLED"));
	return false;
}

$arParams["ELEMENT_ID"] = intval($arParams["ELEMENT_ID"]);

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 36000000;

if(!isset($arParams["WIDTH"]) || intval($arParams["WIDTH"]) <= 0)
	$arParams["WIDTH"] = 120;

if(!isset($arParams["HEIGHT"]) || intval($arParams["HEIGHT"]) <= 0)
	$arParams["HEIGHT"] = 50;

if(!isset($arParams["WIDTH_SMALL"]) || intval($arParams["WIDTH_SMALL"]) <= 0)
	$arParams["WIDTH_SMALL"] = 21;

if(!isset($arParams["HEIGHT_SMALL"]) || intval($arParams["HEIGHT_SMALL"]) <= 0)
	$arParams["HEIGHT_SMALL"] = 17;

//Let's cache it
if($this->StartResultCache())
{
	//Handle case when ELEMENT_CODE used
	if($arParams["ELEMENT_ID"] <= 0)
	{
		$arParams["ELEMENT_ID"] = CIBlockFindTools::GetElementID(
			$arParams["ELEMENT_ID"],
			$arParams["ELEMENT_CODE"],
			false,
			false,
			array(
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"IBLOCK_LID" => SITE_ID,
				"IBLOCK_ACTIVE" => "Y",
				"ACTIVE_DATE" => "Y",
				"ACTIVE" => "Y",
				"CHECK_PERMISSIONS" => "Y",
			)
		);
	}

	$arBrandBlocks = array();
	$arParams["ELEMENT_ID"] = intval($arParams["ELEMENT_ID"]);


	// Show only linked to element brands
	if($arParams["ELEMENT_ID"] > 0)
	{
		$rsProps = CIBlockElement::GetProperty(
			$arParams['IBLOCK_ID'],
			$arParams['ELEMENT_ID'],
			"sort",
			"asc",
			array(
				'ACTIVE' => 'Y',
				'CODE' => $arParams['PROP_CODE']
			)
		);
	}
	else // Show all rows from table
	{
		$rsProps = CIBlockProperty::GetList(
			array("SORT" => "ASC", "ID" => "ASC"),
			array(
				"IBLOCK_ID" => $arParams['IBLOCK_ID'],
				"ACTIVE" => "Y",
				'CODE' => $arParams['PROP_CODE']
			)
		);
	}

	$hlblocks = array();
	$reqParams = array();

	while($arProp = $rsProps->Fetch())
	{
		if(!isset($arProp['USER_TYPE_SETTINGS']) || !isset($arProp['USER_TYPE_SETTINGS']['TABLE_NAME']))
			continue;

		if(!isset($hlblocks[$arProp['USER_TYPE_SETTINGS']['TABLE_NAME']]))
		{
			$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getList(
				array(
					"filter" => array(
						'TABLE_NAME' => $arProp['USER_TYPE_SETTINGS']['TABLE_NAME']
					)
				)
			)->fetch();

			$hlblocks[$arProp['USER_TYPE_SETTINGS']['TABLE_NAME']] = $hlblock;
		}
		else
		{
			$hlblock = $hlblocks[$arProp['USER_TYPE_SETTINGS']['TABLE_NAME']];
		}

		if (isset($hlblock['ID']))
		{
			if(!isset($reqParams[$hlblock['ID']]))
			{
				$reqParams[$hlblock['ID']] = array();
				$reqParams[$hlblock['ID']]['HLB'] = $hlblock;
			}

			$reqParams[$hlblock['ID']]['VALUES'][] = $arProp['VALUE'];
		}
	}

	foreach ($reqParams as $params)
	{
		$boolName = true;
		$boolPict = true;

		$entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($params['HLB']);
		$entity_data_class = $entity->getDataClass();

		if($arParams["ELEMENT_ID"] > 0)
		{
			$arFilter =	array(
				'filter' => array(
					'UF_XML_ID' => $params['VALUES']
				)
			);
		}
		else
			$arFilter =	array();

		$rsPropEnums = $entity_data_class::getList($arFilter);

		while ($arEnum = $rsPropEnums->fetch())
		{
			if (!isset($arEnum['UF_NAME']))
			{
				$boolName = false;
				break;
			}

			$arEnum['PREVIEW_PICTURE'] = false;
			$arEnum['ID'] = intval($arEnum['ID']);

			if (!isset($arEnum['UF_FILE']) || strlen($arEnum['UF_FILE']) <= 0)
				$boolPict = false;

			if ($boolPict)
			{
				if(strlen($arEnum['UF_DESCRIPTION']) > 0)
				{
					$width = $arParams["WIDTH_SMALL"];
					$height = $arParams["HEIGHT_SMALL"];
					$type = "PIC_TEXT";
				}
				else
				{
					$width = $arParams["WIDTH"];
					$height = $arParams["HEIGHT"];
					$type = "ONLY_PIC";
				}

				$arEnum['PREVIEW_PICTURE'] = CFile::GetFileArray($arEnum['UF_FILE']);

				if ($arEnum['PREVIEW_PICTURE']
					&& (
						intval($arEnum['PREVIEW_PICTURE']['WIDTH']) > $width
						||
						intval($arEnum['PREVIEW_PICTURE']['HEIGHT']) > $height
						)
					)

				{
					$arEnum['PREVIEW_PICTURE'] = CFile::ResizeImageGet(
						$arEnum['PREVIEW_PICTURE'],
						array("width" => $width, "height" => $height),
						BX_RESIZE_IMAGE_PROPORTIONAL,
						true
					);

					$arEnum['PREVIEW_PICTURE']['SRC'] = $arEnum['PREVIEW_PICTURE']['src'];
				}
			}
			elseif(strlen($arEnum['UF_DESCRIPTION']) > 0)
			{
				$type = "ONLY_TEXT";
			}
			else //Nothing to show
			{
				continue;
			}

			$arBrandBlocks[$arEnum['ID']] = array(
				'TYPE' => $type,
				'NAME' => $arEnum['UF_NAME'],
				'LINK' => $arEnum['UF_LINK'],
				'DESCRIPTION' => $arEnum['UF_DESCRIPTION'],
				'FULL_DESCRIPTION' => $arEnum['UF_FULL_DESCRIPTION'],
				'PICT' => ($boolPict ?
					array(
						'SRC' => $arEnum['PREVIEW_PICTURE']['SRC'],
					)
					: false
				)
			);
		}
	}

	$arResult["BRAND_BLOCKS"] = $arBrandBlocks;

	$this->IncludeComponentTemplate();
}
?>