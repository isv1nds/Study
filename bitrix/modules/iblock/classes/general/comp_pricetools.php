<?
IncludeModuleLangFile(__FILE__);

class CIBlockPriceTools
{
	public static function GetCatalogPrices($IBLOCK_ID, $arPriceCode)
	{
		global $USER;
		$arCatalogPrices = array();
		if(CModule::IncludeModule("catalog"))
		{
			$arCatalogGroupCodesFilter = array();
			foreach($arPriceCode as $value)
			{
				$t_value = trim($value);
				if('' != $t_value)
					$arCatalogGroupCodesFilter[$value] = true;
			}
			$arCatalogGroupsFilter = array();
			$arCatalogGroups = CCatalogGroup::GetListArray();
			foreach($arCatalogGroups as $key => $value)
			{
				if(array_key_exists($value["NAME"], $arCatalogGroupCodesFilter))
				{
					$arCatalogGroupsFilter[] = $key;
					$arCatalogPrices[$value["NAME"]] = array(
						"ID" => htmlspecialcharsbx($value["ID"]),
						"TITLE" => htmlspecialcharsbx($value["NAME_LANG"]),
						"SELECT" => "CATALOG_GROUP_".$value["ID"],
					);
				}
			}
			$arPriceGroups = CCatalogGroup::GetGroupsPerms($USER->GetUserGroupArray(), $arCatalogGroupsFilter);
			foreach($arCatalogPrices as $name=>$value)
			{
				$arCatalogPrices[$name]["CAN_VIEW"]=in_array($value["ID"], $arPriceGroups["view"]);
				$arCatalogPrices[$name]["CAN_BUY"]=in_array($value["ID"], $arPriceGroups["buy"]);
			}
		}
		else
		{
			$arPriceGroups = array(
				"view" => array(),
			);
			$rsProperties = CIBlockProperty::GetList(array(), array(
				"IBLOCK_ID"=>$IBLOCK_ID,
				"CHECK_PERMISSIONS"=>"N",
				"PROPERTY_TYPE"=>"N",
			));
			while($arProperty = $rsProperties->Fetch())
			{
				if($arProperty["MULTIPLE"]=="N" && in_array($arProperty["CODE"], $arPriceCode))
				{
					$arPriceGroups["view"][]=htmlspecialcharsbx("PROPERTY_".$arProperty["CODE"]);
					$arCatalogPrices[$arProperty["CODE"]] = array(
						"ID"=>$arProperty["ID"],
						"TITLE"=>htmlspecialcharsbx($arProperty["NAME"]),
						"SELECT" => "PROPERTY_".$arProperty["ID"],
						"CAN_VIEW"=>true,
						"CAN_BUY"=>false,
					);
				}
			}
		}
		return $arCatalogPrices;
	}

	public static function GetAllowCatalogPrices($arPriceTypes)
	{
		$arResult = array();
		if (!empty($arPriceTypes) && is_array($arPriceTypes))
		{
			foreach ($arPriceTypes as &$arOnePriceType)
			{
				if ($arOnePriceType['CAN_VIEW'] || $arOnePriceType['CAN_BUY'])
					$arResult[] = intval($arOnePriceType['ID']);
			}
			if (isset($arOnePriceType))
				unset($arOnePriceType);
		}
		return $arResult;
	}

	public static function SetCatalogDiscountCache($arCatalogGroups, $arUserGroups)
	{
		global $DB;
		if (CModule::IncludeModule('catalog'))
		{
			if (!is_array($arCatalogGroups))
				return false;
			if (!is_array($arUserGroups))
				return false;
			CatalogClearArray($arCatalogGroups);
			if (empty($arCatalogGroups))
				return false;
			CatalogClearArray($arUserGroups);
			if (empty($arUserGroups))
				return false;

			$arRestFilter = array(
				'PRICE_TYPES' => $arCatalogGroups,
				'USER_GROUPS' => $arUserGroups,
			);
			$arRest = CCatalogDiscount::GetRestrictions($arRestFilter, false, false);
			$arDiscountFilter = array();
			$arDiscountResult = array();
			if (empty($arRest) || (array_key_exists('DISCOUNTS', $arRest) && empty($arRest['DISCOUNTS'])))
			{
				foreach ($arCatalogGroups as &$intOneGroupID)
				{
					$strCacheKey = CCatalogDiscount::GetDiscountFilterCacheKey(array($intOneGroupID), $arUserGroups, false);
					$arDiscountFilter[$strCacheKey] = array();
				}
				if (isset($intOneGroupID))
					unset($intOneGroupID);
			}
			else
			{
				$arSelect = array(
					"ID", "TYPE", "SITE_ID", "ACTIVE", "ACTIVE_FROM", "ACTIVE_TO",
					"RENEWAL", "NAME", "SORT", "MAX_DISCOUNT", "VALUE_TYPE", "VALUE", "CURRENCY",
					"PRIORITY", "LAST_DISCOUNT",
					"COUPON", "COUPON_ONE_TIME", "COUPON_ACTIVE", 'UNPACK'
				);
				$strDate = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")));
				$arFilter = array(
					"ID" => $arRest['DISCOUNTS'],
					"SITE_ID" => SITE_ID,
					"TYPE" => DISCOUNT_TYPE_STANDART,
					"ACTIVE" => "Y",
					"RENEWAL" => 'N',
					"+<=ACTIVE_FROM" => $strDate,
					"+>=ACTIVE_TO" => $strDate,
					'+COUPON' => array(),
				);

				$arResultDiscountList = array();

				$rsPriceDiscounts = CCatalogDiscount::GetList(
					array(),
					$arFilter,
					false,
					false,
					$arSelect
				);

				while ($arPriceDiscount = $rsPriceDiscounts->Fetch())
				{
					$arPriceDiscount['ID'] = intval($arPriceDiscount['ID']);
					$arResultDiscountList[$arPriceDiscount['ID']] = $arPriceDiscount;
				}

				foreach ($arCatalogGroups as &$intOneGroupID)
				{
					$strCacheKey = CCatalogDiscount::GetDiscountFilterCacheKey(array($intOneGroupID), $arUserGroups, false);
					$arDiscountDetailList = array();
					$arDiscountList = array();
					foreach ($arRest['RESTRICTIONS'] as $intDiscountID => $arDiscountRest)
					{
						if (empty($arDiscountRest['PRICE_TYPE']) || array_key_exists($intOneGroupID, $arDiscountRest['PRICE_TYPE']))
						{
							$arDiscountList[] = $intDiscountID;
							if (array_key_exists($intDiscountID, $arResultDiscountList))
								$arDiscountDetailList[] = $arResultDiscountList[$intDiscountID];
						}
					}
					sort($arDiscountList);
					$arDiscountFilter[$strCacheKey] = $arDiscountList;
					$strResultCacheKey = CCatalogDiscount::GetDiscountResultCacheKey($arDiscountList, SITE_ID, 'N');
					$arDiscountResult[$strResultCacheKey] = $arDiscountDetailList;
				}
				if (isset($intOneGroupID))
					unset($intOneGroupID);
			}
			$boolFlag = CCatalogDiscount::SetAllDiscountFilterCache($arDiscountFilter, false);
			$boolFlagExt = CCatalogDiscount::SetAllDiscountResultCache($arDiscountResult);
			return $boolFlag && $boolFlagExt;
		}
		return true;
	}

	public static function GetItemPrices($IBLOCK_ID, $arCatalogPrices, $arItem, $bVATInclude = true, $arCurrencyParams = array(), $USER_ID = 0, $LID = SITE_ID)
	{
		static $arCurUserGroups = array();
		static $strBaseCurrency = '';

		global $USER;

		$arPrices = array();
		if(CModule::IncludeModule("catalog"))
		{
			$USER_ID = intval($USER_ID);
			$intUserID = $USER_ID;
			if (0 >= $intUserID)
				$intUserID = $USER->GetID();
			if (!array_key_exists($intUserID, $arCurUserGroups))
			{
				$arUserGroups = (0 < $USER_ID ? CUser::GetUserGroup($USER_ID) : $USER->GetUserGroupArray());
				CatalogClearArray($arUserGroups);
				$arCurUserGroups[$intUserID] = $arUserGroups;
			}
			else
			{
				$arUserGroups = $arCurUserGroups[$intUserID];
			}

			$boolConvert = false;
			$strCurrencyID = '';
			if (!empty($arCurrencyParams) && is_array($arCurrencyParams))
			{
				if (array_key_exists('CURRENCY_ID', $arCurrencyParams) && !empty($arCurrencyParams['CURRENCY_ID']))
				{
					$boolConvert = true;
					$strCurrencyID = $arCurrencyParams['CURRENCY_ID'];
				}
			}
			if (!$boolConvert && '' == $strBaseCurrency)
				$strBaseCurrency = CCurrency::GetBaseCurrency();

			$strMinCode = '';
			$boolStartMin = true;
			$dblMinPrice = 0;
			$strMinCurrency = ($boolConvert ? $strCurrencyID : $strBaseCurrency);
			CCatalogDiscountSave::Disable();
			foreach($arCatalogPrices as $key => $value)
			{
				if($value["CAN_VIEW"] && strlen($arItem["CATALOG_PRICE_".$value["ID"]]) > 0)
				{
					// get final price with VAT included.
					if ($arItem['CATALOG_VAT_INCLUDED'] != 'Y')
					{
						$arItem['CATALOG_PRICE_'.$value['ID']] *= (1 + $arItem['CATALOG_VAT'] * 0.01);
					}
					// so discounts will include VAT
					$arDiscounts = CCatalogDiscount::GetDiscount(
						$arItem["ID"],
						$arItem["IBLOCK_ID"],
						array($value["ID"]),
						$arUserGroups,
						"N",
						$LID,
						array()
					);
					$discountPrice = CCatalogProduct::CountPriceWithDiscount(
						$arItem["CATALOG_PRICE_".$value["ID"]],
						$arItem["CATALOG_CURRENCY_".$value["ID"]],
						$arDiscounts
					);
					// get clear prices WO VAT
					$arItem['CATALOG_PRICE_'.$value['ID']] /= (1 + $arItem['CATALOG_VAT'] * 0.01);
					$discountPrice /= (1 + $arItem['CATALOG_VAT'] * 0.01);

					$vat_value_discount = $discountPrice * $arItem['CATALOG_VAT'] * 0.01;
					$vat_discountPrice = $discountPrice + $vat_value_discount;

					$vat_value = $arItem['CATALOG_PRICE_'.$value['ID']] * $arItem['CATALOG_VAT'] * 0.01;
					$vat_price = $arItem["CATALOG_PRICE_".$value["ID"]] + $vat_value;

					if ($boolConvert && $strCurrencyID != $arItem["CATALOG_CURRENCY_".$value["ID"]])
					{
						$strOrigCurrencyID = $arItem["CATALOG_CURRENCY_".$value["ID"]];
						$dblOrigNoVat = $arItem["CATALOG_PRICE_".$value["ID"]];
						$dblNoVat = CCurrencyRates::ConvertCurrency($dblOrigNoVat, $strOrigCurrencyID, $strCurrencyID);
						$dblVatPrice = CCurrencyRates::ConvertCurrency($vat_price, $strOrigCurrencyID, $strCurrencyID);
						$dblVatValue = CCurrencyRates::ConvertCurrency($vat_value, $strOrigCurrencyID, $strCurrencyID);
						$dblDiscountValueNoVat = CCurrencyRates::ConvertCurrency($discountPrice, $strOrigCurrencyID, $strCurrencyID);
						$dblVatDiscountPrice = CCurrencyRates::ConvertCurrency($vat_discountPrice, $strOrigCurrencyID, $strCurrencyID);
						$dblDiscountValueVat = CCurrencyRates::ConvertCurrency($vat_value_discount, $strOrigCurrencyID, $strCurrencyID);

						$arPrices[$key] = array(
							'ORIG_VALUE_NOVAT' => $dblOrigNoVat,
							"VALUE_NOVAT" => $dblNoVat,
							"PRINT_VALUE_NOVAT" => FormatCurrency($dblNoVat, $strCurrencyID),

							'ORIG_VALUE_VAT' => $vat_price,
							"VALUE_VAT" => $dblVatPrice,
							"PRINT_VALUE_VAT" => FormatCurrency($dblVatPrice, $strCurrencyID),

							'ORIG_VATRATE_VALUE' => $vat_value,
							"VATRATE_VALUE" => $dblVatValue,
							"PRINT_VATRATE_VALUE" => FormatCurrency($dblVatValue, $strCurrencyID),

							'ORIG_DISCOUNT_VALUE_NOVAT' => $discountPrice,
							"DISCOUNT_VALUE_NOVAT" => $dblDiscountValueNoVat,
							"PRINT_DISCOUNT_VALUE_NOVAT" => FormatCurrency($dblDiscountValueNoVat, $strCurrencyID),

							"ORIG_DISCOUNT_VALUE_VAT" => $vat_discountPrice,
							"DISCOUNT_VALUE_VAT" => $dblVatDiscountPrice,
							"PRINT_DISCOUNT_VALUE_VAT" => FormatCurrency($dblVatDiscountPrice, $strCurrencyID),

							'ORIG_DISCOUNT_VATRATE_VALUE' => $vat_value_discount,
							'DISCOUNT_VATRATE_VALUE' => $dblDiscountValueVat,
							'PRINT_DISCOUNT_VATRATE_VALUE' => FormatCurrency($dblDiscountValueVat, $strCurrencyID),

							'ORIG_CURRENCY' => $strOrigCurrencyID,
							"CURRENCY" => $strCurrencyID,
						);
					}
					else
					{
						$arPrices[$key] = array(
							"VALUE_NOVAT" => $arItem["CATALOG_PRICE_".$value["ID"]],
							"PRINT_VALUE_NOVAT" => FormatCurrency($arItem["CATALOG_PRICE_".$value["ID"]],$arItem["CATALOG_CURRENCY_".$value["ID"]]),

							"VALUE_VAT" => $vat_price,
							"PRINT_VALUE_VAT" => FormatCurrency($vat_price, $arItem["CATALOG_CURRENCY_".$value["ID"]]),

							"VATRATE_VALUE" => $vat_value,
							"PRINT_VATRATE_VALUE" => FormatCurrency($vat_value, $arItem["CATALOG_CURRENCY_".$value["ID"]]),

							"DISCOUNT_VALUE_NOVAT" => $discountPrice,
							"PRINT_DISCOUNT_VALUE_NOVAT" => FormatCurrency($discountPrice, $arItem["CATALOG_CURRENCY_".$value["ID"]]),

							"DISCOUNT_VALUE_VAT" => $vat_discountPrice,
							"PRINT_DISCOUNT_VALUE_VAT" => FormatCurrency($vat_discountPrice, $arItem["CATALOG_CURRENCY_".$value["ID"]]),

							'DISCOUNT_VATRATE_VALUE' => $vat_value_discount,
							'PRINT_DISCOUNT_VATRATE_VALUE' => FormatCurrency($vat_value_discount, $arItem["CATALOG_CURRENCY_".$value["ID"]]),

							"CURRENCY" => $arItem["CATALOG_CURRENCY_".$value["ID"]],
						);
					}
					$arPrices[$key]["ID"] = $arItem["CATALOG_PRICE_ID_".$value["ID"]];
					$arPrices[$key]["CAN_ACCESS"] = $arItem["CATALOG_CAN_ACCESS_".$value["ID"]];
					$arPrices[$key]["CAN_BUY"] = $arItem["CATALOG_CAN_BUY_".$value["ID"]];
					$arPrices[$key]['MIN_PRICE'] = 'N';

					if ($bVATInclude)
					{
						$arPrices[$key]['VALUE'] = $arPrices[$key]['VALUE_VAT'];
						$arPrices[$key]['PRINT_VALUE'] = $arPrices[$key]['PRINT_VALUE_VAT'];
						$arPrices[$key]['DISCOUNT_VALUE'] = $arPrices[$key]['DISCOUNT_VALUE_VAT'];
						$arPrices[$key]['PRINT_DISCOUNT_VALUE'] = $arPrices[$key]['PRINT_DISCOUNT_VALUE_VAT'];
					}
					else
					{
						$arPrices[$key]['VALUE'] = $arPrices[$key]['VALUE_NOVAT'];
						$arPrices[$key]['PRINT_VALUE'] = $arPrices[$key]['PRINT_VALUE_NOVAT'];
						$arPrices[$key]['DISCOUNT_VALUE'] = $arPrices[$key]['DISCOUNT_VALUE_NOVAT'];
						$arPrices[$key]['PRINT_DISCOUNT_VALUE'] = $arPrices[$key]['PRINT_DISCOUNT_VALUE_NOVAT'];
					}
					if ($arPrices[$key]['VALUE'] == $arPrices[$key]['DISCOUNT_VALUE'])
					{
						$arPrices[$key]['DISCOUNT_DIFF_PERCENT'] = 0;
						$arPrices[$key]['DISCOUNT_DIFF'] = 0;
						$arPrices[$key]['PRINT_DISCOUNT_DIFF'] = FormatCurrency(0, $arPrices[$key]['CURRENCY']);
					}
					else
					{
						$arPrices[$key]['DISCOUNT_DIFF'] = $arPrices[$key]['VALUE'] - $arPrices[$key]['DISCOUNT_VALUE'];
						$arPrices[$key]['PRINT_DISCOUNT_DIFF'] = FormatCurrency($arPrices[$key]['DISCOUNT_DIFF'], $arPrices[$key]['CURRENCY']);
						$arPrices[$key]['DISCOUNT_DIFF_PERCENT'] = 100*$arPrices[$key]['DISCOUNT_DIFF']/$arPrices[$key]['VALUE'];
					}

					if ($value["CAN_VIEW"])
					{
						if ($boolStartMin)
						{
							$dblMinPrice = ($boolConvert || ($arPrices[$key]['CURRENCY'] == $strMinCurrency)
								? $arPrices[$key]['DISCOUNT_VALUE']
								: CCurrencyRates::ConvertCurrency($arPrices[$key]['DISCOUNT_VALUE'], $arPrices[$key]['CURRENCY'], $strMinCurrency)
							);
							$strMinCode = $key;
							$boolStartMin = false;
						}
						else
						{
							$dblComparePrice = ($boolConvert || ($arPrices[$key]['CURRENCY'] == $strMinCurrency)
								? $arPrices[$key]['DISCOUNT_VALUE']
								: CCurrencyRates::ConvertCurrency($arPrices[$key]['DISCOUNT_VALUE'], $arPrices[$key]['CURRENCY'], $strMinCurrency)
							);
							if ($dblMinPrice > $dblComparePrice)
							{
								$dblMinPrice = $dblComparePrice;
								$strMinCode = $key;
							}
						}
					}
				}
			}
			if ('' != $strMinCode)
				$arPrices[$strMinCode]['MIN_PRICE'] = 'Y';
			CCatalogDiscountSave::Enable();
		}
		else
		{
			$strMinCode = '';
			$boolStartMin = true;
			$dblMinPrice = 0;
			foreach($arCatalogPrices as $key => $value)
			{
				if($value["CAN_VIEW"])
				{
					$dblValue = round(doubleval($arItem["PROPERTY_".$value["ID"]."_VALUE"]), 2);
					if ($boolStartMin)
					{
						$dblMinPrice = $dblValue;
						$strMinCode = $key;
						$boolStartMin = false;
					}
					else
					{
						if ($dblMinPrice > $dblValue)
						{
							$dblMinPrice = $dblComparePrice;
							$strMinCode = $key;
						}
					}
					$arPrices[$key] = array(
						"ID" => $arItem["PROPERTY_".$value["ID"]."_VALUE_ID"],
						"VALUE" => $dblValue,
						"PRINT_VALUE" => $dblValue." ".$arItem["PROPERTY_".$value["ID"]."_DESCRIPTION"],
						"DISCOUNT_VALUE" => $dblValue,
						"PRINT_DISCOUNT_VALUE" => $dblValue." ".$arItem["PROPERTY_".$value["ID"]."_DESCRIPTION"],
						"CURRENCY" => $arItem["PROPERTY_".$value["ID"]."_DESCRIPTION"],
						"CAN_ACCESS" => true,
						"CAN_BUY" => false,
						'DISCOUNT_DIFF_PERCENT' => 0,
						'DISCOUNT_DIFF' => 0,
						'PRINT_DISCOUNT_DIFF' => '0 '.$arItem["PROPERTY_".$value["ID"]."_DESCRIPTION"],
						"MIN_PRICE" => "N"
					);
				}
			}
			if ('' != $strMinCode)
				$arPrices[$strMinCode]['MIN_PRICE'] = 'Y';
		}
		return $arPrices;
	}

	public static function CanBuy($IBLOCK_ID, $arCatalogPrices, $arItem)
	{
		if (array_key_exists('CATALOG_AVAILABLE', $arItem) && 'N' == $arItem['CATALOG_AVAILABLE'])
			return false;

		if(is_array($arItem["PRICE_MATRIX"]))
		{
			return $arItem["PRICE_MATRIX"]["AVAILABLE"] == "Y";
		}
		else
		{
			foreach($arCatalogPrices as $arPrice)
			{
				if($arPrice["CAN_BUY"] && strlen($arItem["CATALOG_PRICE_".$arPrice["ID"]]) > 0)
				{
					return true;
				}
			}
		}
		return false;
	}

	public static function GetProductProperties($IBLOCK_ID, $ELEMENT_ID, $arPropertiesList, $arPropertiesValues)
	{
		$arResult = array();
		foreach($arPropertiesList as $pid)
		{
			$prop = $arPropertiesValues[$pid];
			$arResult[$pid] = array("VALUES" => array(), "SELECTED" => false);
			$product_props = &$arResult[$pid];

			if($prop["MULTIPLE"] == "Y" && is_array($prop["VALUE"]))
			{
				switch($prop["PROPERTY_TYPE"])
				{
				case "S":
				case "N":
					foreach($prop["VALUE"] as $value)
					{
						if(!is_array($value) && strlen($value))
						{
							if($product_props["SELECTED"] === false)
								$product_props["SELECTED"] = $value;
							$product_props["VALUES"][$value] = $value;
						}
					}
					break;
				case "G":
					$ar = array();
					foreach($prop["VALUE"] as $value)
					{
						$value = intval($value);
						if($value > 0)
							$ar[] = $value;
					}
					$rsSections = CIBlockSection::GetList(array("LEFT_MARGIN"=>"ASC"), array("=ID"=>$ar));
					while($arSection = $rsSections->GetNext())
					{
						if($product_props["SELECTED"] === false)
							$product_props["SELECTED"] = $arSection["ID"];
						$product_props["VALUES"][$arSection["ID"]] = $arSection["NAME"];
					}
					break;
				case "E":
					$ar = array();
					foreach($prop["VALUE"] as $value)
					{
						$value = intval($value);
						if($value > 0)
							$ar[] = $value;
					}
					$rsElements = CIBlockElement::GetList(array("ID"=>"ASC"), array("=ID"=>$ar), false, false, array("ID", "NAME"));
					while($arElement = $rsElements->GetNext())
					{
						if($product_props["SELECTED"] === false)
							$product_props["SELECTED"] = $arElement["ID"];
						$product_props["VALUES"][$arElement["ID"]] = $arElement["NAME"];
					}
					break;
				case "L":
					foreach($prop["VALUE"] as $i => $value)
					{
						if($product_props["SELECTED"] === false)
							$product_props["SELECTED"] = $prop["VALUE_ENUM_ID"][$i];
						$product_props["VALUES"][$prop["VALUE_ENUM_ID"][$i]] = $value;
					}
					break;
				}
			}
			elseif($prop["MULTIPLE"] == "N")
			{
				switch($prop["PROPERTY_TYPE"])
				{
				case "L":
					$rsEnum = CIBlockPropertyEnum::GetList(array("SORT"=>"ASC", "VALUE"=>"ASC"), array("IBLOCK_ID"=>$IBLOCK_ID, "CODE"=>$pid));
					while($arEnum = $rsEnum->GetNext())
					{
						$product_props["VALUES"][$arEnum["ID"]] = $arEnum["VALUE"];
						if($arEnum["DEF"] == "Y")
							$product_props["SELECTED"] = $arEnum["ID"];
					}
					break;
				case "E":
					if($prop["LINK_IBLOCK_ID"] > 0)
					{
						$rsElements = CIBlockElement::GetList(
							array("NAME"=>"ASC", "SORT"=>"ASC"),
							array("IBLOCK_ID"=>$prop["LINK_IBLOCK_ID"], "ACTIVE"=>"Y"),
							false, false,
							array("ID", "NAME")
						);
						while($arElement = $rsElements->GetNext())
						{
							if($product_props["SELECTED"] === false)
								$product_props["SELECTED"] = $arElement["ID"];
							$product_props["VALUES"][$arElement["ID"]] = $arElement["NAME"];
						}
					}
					break;
				}
			}
		}
		return $arResult;
	}

	/*
	Checks arPropertiesValues against DB values
	returns array on success
	or number on fail (may be used for debug)
	*/
	public static function CheckProductProperties($IBLOCK_ID, $ELEMENT_ID, $arPropertiesList, $arPropertiesValues)
	{
		$SORT=1;
		$arResult = array();
		$param_props = array_flip($arPropertiesList);
		$rsProps = CIBlockElement::GetProperty($IBLOCK_ID, $ELEMENT_ID);
		while($arProp = $rsProps->Fetch())
		{
			if(in_array($arProp["CODE"], $arPropertiesList))
				$pid = $arProp["CODE"];
			elseif(in_array($arProp["ID"], $arPropertiesList))
				$pid = $arProp["CODE"];
			else
				continue; //Skip next. It's not an product property

			//Check if already handled
			if(!array_key_exists($pid, $param_props))
				continue;

			if(!strlen($arPropertiesValues[$pid])) //Property value MUST be there
			{
				return 1;
			}
			elseif($arProp["MULTIPLE"] == "Y")
			{
				switch($arProp["PROPERTY_TYPE"])
				{
				case "S":
				case "N":
					if($arProp["VALUE"] == $arPropertiesValues[$pid])
					{
						$arResult[] = array(
							"NAME" => $arProp["NAME"],
							"CODE" => $pid,
							"VALUE" => $arProp["VALUE"],
							"SORT" => $SORT++,
						);
						unset($param_props[$pid]);//mark as found
					}
					break;
				case "G":
					if($arProp["VALUE"] == $arPropertiesValues[$pid])
					{
						$rsSection = CIBlockSection::GetList(array(), array("=ID"=>$arProp["VALUE"]));
						if($arSection = $rsSection->Fetch())
						{
							$arResult[] = array(
								"NAME" => $arProp["NAME"],
								"CODE" => $pid,
								"VALUE" => $arSection["NAME"],
								"SORT" => $SORT++,
							);
							unset($param_props[$pid]);//mark as found
						}
					}
					break;
				case "E":
					if($arProp["VALUE"] == $arPropertiesValues[$pid])
					{
						$rsElement = CIBlockElement::GetList(array(), array("=ID"=>$arProp["VALUE"]), false, false, array("ID", "NAME"));
						if($arElement = $rsElement->Fetch())
						{
							$arResult[] = array(
								"NAME" => $arProp["NAME"],
								"CODE" => $pid,
								"VALUE" => $arElement["NAME"],
								"SORT" => $SORT++,
							);
							unset($param_props[$pid]);//mark as found
						}
					}
					break;
				case "L":
					if($arProp["VALUE"] == $arPropertiesValues[$pid])
					{
						$rsEnum = CIBlockPropertyEnum::GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "PROPERTY_ID" => $pid, "ID" => $arPropertiesValues[$pid]));
						if($arEnum = $rsEnum->Fetch())
						{
							$arResult[] = array(
								"NAME" => $arProp["NAME"],
								"CODE" => $pid,
								"VALUE" => $arEnum["VALUE"],
								"SORT" => $SORT++,
							);
							unset($param_props[$pid]);//mark as found
						}
					}
					break;
				default:
					return 2;
				}
			}
			else
			{
				switch($arProp["PROPERTY_TYPE"])
				{
				case "L":
					$rsEnum = CIBlockPropertyEnum::GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "PROPERTY_ID" => $pid, "ID" => $arPropertiesValues[$pid]));
					if($arEnum = $rsEnum->Fetch())
					{
						$arResult[] = array(
							"NAME" => $arProp["NAME"],
							"CODE" => $pid,
							"VALUE" => $arEnum["VALUE"],
							"SORT" => $SORT++,
						);
						unset($param_props[$pid]);//mark as found
					}
					break;
				case "E":
					if($arProp["LINK_IBLOCK_ID"] > 0)
					{
						$rsElement = CIBlockElement::GetList(array(), array("IBLOCK_ID"=>$arProp["LINK_IBLOCK_ID"], "ACTIVE" => "Y", "=ID" => $arPropertiesValues[$pid]), false, false, array("ID", "NAME"));
						if($arElement = $rsElement->Fetch())
						{
							$arResult[] = array(
								"NAME" => $arProp["NAME"],
								"CODE" => $pid,
								"VALUE" => $arElement["NAME"],
								"SORT" => $SORT++,
							);
							unset($param_props[$pid]);//mark as found
						}
					}
					break;
				default:
					return 3;
				}
			}
		}

		if(count($param_props))
			return 4;

		return $arResult;
	}

	public static function GetOffersIBlock($IBLOCK_ID)
	{
		$arResult = false;
		$IBLOCK_ID = intval($IBLOCK_ID);
		if (0 < $IBLOCK_ID)
		{
			if (CModule::IncludeModule("catalog"))
			{
				$arCatalog = CCatalogSKU::GetInfoByProductIBlock($IBLOCK_ID);
				if (!empty($arCatalog) && is_array($arCatalog))
				{
					$arResult = array(
						'OFFERS_IBLOCK_ID' => $arCatalog['IBLOCK_ID'],
						'OFFERS_PROPERTY_ID' => $arCatalog['SKU_PROPERTY_ID'],
					);
				}
			}
		}
		return $arResult;
	}

	public static function GetOfferProperties($OFFER_ID, $IBLOCK_ID, $arPropertiesList)
	{
		$arResult = array();

		$arOffersIBlock = CIBlockPriceTools::GetOffersIBlock($IBLOCK_ID);
		if(!$arOffersIBlock)
			return $arResult;

		$SORT = 1;
		$rsProps = CIBlockElement::GetProperty(
			$arOffersIBlock["OFFERS_IBLOCK_ID"]
			,$OFFER_ID
			,array("sort"=>"asc", "enum_sort" => "asc", "value_id"=>"asc")
			,array("EMPTY"=>"N")
		);

		while($arProp = $rsProps->Fetch())
		{
			if(in_array($arProp["CODE"], $arPropertiesList))
				$pid = $arProp["CODE"];
			elseif(in_array($arProp["ID"], $arPropertiesList))
				$pid = $arProp["CODE"];
			else
				continue; //Skip next. It's not an product property

			switch($arProp["PROPERTY_TYPE"])
			{
			case "S":
			case "N":
				$arResult[] = array(
					"NAME" => $arProp["NAME"],
					"CODE" => $pid,
					"VALUE" => $arProp["VALUE"],
					"SORT" => $SORT++,
				);
				break;
			case "G":
				$rsSection = CIBlockSection::GetList(array(), array("=ID"=>$arProp["VALUE"]), false, array("NAME"));
				if($arSection = $rsSection->Fetch())
				{
					$arResult[] = array(
						"NAME" => $arProp["NAME"],
						"CODE" => $pid,
						"VALUE" => $arSection["NAME"],
						"SORT" => $SORT++,
					);
				}
				break;
			case "E":
				$rsElement = CIBlockElement::GetList(array(), array("=ID"=>$arProp["VALUE"]), false, false, array("ID", "NAME"));
				if($arElement = $rsElement->Fetch())
				{
					$arResult[] = array(
						"NAME" => $arProp["NAME"],
						"CODE" => $pid,
						"VALUE" => $arElement["NAME"],
						"SORT" => $SORT++,
					);
				}
				break;
			case "L":
				$arResult[] = array(
					"NAME" => $arProp["NAME"],
					"CODE" => $pid,
					"VALUE" => $arProp["VALUE_ENUM"],
					"SORT" => $SORT++,
				);
				break;
			}
		}

		return $arResult;
	}

	public static function GetOffersArray($arFilter, $arElementID, $arOrder, $arSelectFields, $arSelectProperties, $limit, $arPrices, $vat_include, $arCurrencyParams = array(), $USER_ID = 0, $LID = SITE_ID)
	{
		$arResult = array();

		$boolCheckPermissions = false;
		$boolHideNotAvailable = false;
		$IBLOCK_ID = 0;
		if (!empty($arFilter) && is_array($arFilter))
		{
			if (isset($arFilter['IBLOCK_ID']))
				$IBLOCK_ID = $arFilter['IBLOCK_ID'];
			if (isset($arFilter['HIDE_NOT_AVAILABLE']))
				$boolHideNotAvailable = 'Y' === $arFilter['HIDE_NOT_AVAILABLE'];
			if (isset($arFilter['CHECK_PERMISSIONS']))
				$boolCheckPermissions = 'Y' === $arFilter['CHECK_PERMISSIONS'];
		}
		else
		{
			$IBLOCK_ID = $arFilter;
		}

		$arOffersIBlock = CIBlockPriceTools::GetOffersIBlock($IBLOCK_ID);
		if($arOffersIBlock)
		{
			$arDefaultMeasure = self::GetDefaultMeasure();

			$limit = intval($limit);
			if (0 > $limit)
				$limit = 0;

			if(!isset($arOrder["ID"]))
				$arOrder["ID"] = "DESC";

			$arFilter = array(
				"IBLOCK_ID" => $arOffersIBlock["OFFERS_IBLOCK_ID"],
				"PROPERTY_".$arOffersIBlock["OFFERS_PROPERTY_ID"] => $arElementID,
				"ACTIVE" => "Y",
				"ACTIVE_DATE" => "Y",
			);
			if ($boolHideNotAvailable)
				$arFilter['CATALOG_AVAILABLE'] = 'Y';
			if ($boolCheckPermissions)
			{
				$arFilter['CHECK_PERMISSIONS'] = "Y";
				$arFilter['MIN_PERMISSION'] = "R";
			}

			$arSelect = array(
				"ID" => 1,
				"IBLOCK_ID" => 1,
				"PROPERTY_".$arOffersIBlock["OFFERS_PROPERTY_ID"] => 1,
				"CATALOG_QUANTITY" => 1
			);
			//if(!$arParams["USE_PRICE_COUNT"])
			{
				foreach($arPrices as $value)
				{
					if (!$value['CAN_VIEW'] && !$value['CAN_BUY'])
						continue;
					$arSelect[$value["SELECT"]] = 1;
				}
			}

			foreach($arSelectFields as $code)
				$arSelect[$code] = 1; //mark to select
			if (!isset($arSelect['PREVIEW_PICTURE']))
				$arSelect['PREVIEW_PICTURE'] = 1;
			if (!isset($arSelect['DETAIL_PICTURE']))
				$arSelect['DETAIL_PICTURE'] = 1;

			$boolPropCacheExist = method_exists('CCatalogDiscount', 'SetProductPropertiesCache');
			$arOfferIDs = array();
			$arMeasureMap = array();
			$arKeyMap = array();
			$intKey = 0;
			$arOffersPerElement = array();
			$rsOffers = CIBlockElement::GetList($arOrder, $arFilter, false, false, array_keys($arSelect));
			while($obOffer = $rsOffers->GetNextElement())
			{
				$arOffer = $obOffer->GetFields();
				$arOffer['ID'] = intval($arOffer['ID']);
				$element_id = $arOffer["PROPERTY_".$arOffersIBlock["OFFERS_PROPERTY_ID"]."_VALUE"];
				//No more than limit offers per element
				if($limit > 0)
				{
					$arOffersPerElement[$element_id]++;
					if($arOffersPerElement[$element_id] > $limit)
						continue;
				}

				if($element_id > 0)
				{
					$arOffer["LINK_ELEMENT_ID"] = $element_id;
					$arOffer["PROPERTIES"] = array();
					$arOffer["DISPLAY_PROPERTIES"] = array();
					if(!empty($arSelectProperties))
					{
						$arOffer["PROPERTIES"] = $obOffer->GetProperties();
						if ($boolPropCacheExist)
							CCatalogDiscount::SetProductPropertiesCache($arOffer['ID'], $arOffer["PROPERTIES"]);
						foreach($arSelectProperties as $pid)
						{
							if (!isset($arOffer["PROPERTIES"][$pid]))
								continue;
							$prop = &$arOffer["PROPERTIES"][$pid];
							$boolArr = is_array($prop["VALUE"]);
							if(
								($boolArr && !empty($prop["VALUE"])) ||
								(!$boolArr && strlen($prop["VALUE"])>0))
							{
								$arOffer["DISPLAY_PROPERTIES"][$pid] = CIBlockFormatProperties::GetDisplayValue($arOffer, $prop, "catalog_out");
							}
						}
					}
					$arOffer['CATALOG_MEASURE_NAME'] = $arDefaultMeasure['SYMBOL_RUS'];
					$arOffer['~CATALOG_MEASURE_NAME'] = $arDefaultMeasure['SYMBOL_RUS'];
					$arOffer["CATALOG_MEASURE_RATIO"] = 1;
					if (!isset($arOffer['CATALOG_MEASURE']))
						$arOffer['CATALOG_MEASURE'] = 0;
					$arOffer['CATALOG_MEASURE'] = intval($arOffer['CATALOG_MEASURE']);
					if (0 > $arOffer['CATALOG_MEASURE'])
						$arOffer['CATALOG_MEASURE'] = 0;
					if (0 < $arOffer['CATALOG_MEASURE'])
					{
						if (!isset($arMeasureMap[$arOffer['CATALOG_MEASURE']]))
							$arMeasureMap[$arOffer['CATALOG_MEASURE']] = array();
						$arMeasureMap[$arOffer['CATALOG_MEASURE']][] = $intKey;
					}

					$arOfferIDs[] = $arOffer['ID'];
					$arKeyMap[$arOffer['ID']] = $intKey;
					$arResult[$intKey] = $arOffer;
					$intKey++;
				}
			}
			if (!empty($arKeyMap))
			{
				$rsRatios = CCatalogMeasureRatio::getList(
					array(),
					array('@PRODUCT_ID' => array_keys($arKeyMap)),
					false,
					false,
					array('PRODUCT_ID', 'RATIO')
				);
				while ($arRatio = $rsRatios->Fetch())
				{
					if (isset($arKeyMap[$arRatio['PRODUCT_ID']]))
					{
						$intRatio = intval($arRatio['RATIO']);
						$dblRatio = doubleval($arRatio['RATIO']);
						$arResult[$arKeyMap[$arRatio['PRODUCT_ID']]]['CATALOG_MEASURE_RATIO'] = ($dblRatio > $intRatio ? $dblRatio : $intRatio);
					}
				}
			}
			if (!empty($arOfferIDs))
			{
				if (method_exists('CCatalogDiscount', 'SetProductSectionsCache'))
					CCatalogDiscount::SetProductSectionsCache($arOfferIDs);
				foreach ($arResult as &$arOffer)
				{
					$arOffer['MIN_PRICE'] = false;
					$arOffer["PRICES"] = CIBlockPriceTools::GetItemPrices($arOffersIBlock["OFFERS_IBLOCK_ID"], $arPrices, $arOffer, $vat_include, $arCurrencyParams, $USER_ID, $LID);
					if (!empty($arOffer["PRICES"]))
					{
						foreach ($arOffer['PRICES'] as &$arOnePrice)
						{
							if ('Y' == $arOnePrice['MIN_PRICE'])
							{
								$arOffer['MIN_PRICE'] = $arOnePrice;
								break;
							}
						}
						unset($arOnePrice);
					}
					$arOffer["CAN_BUY"] = CIBlockPriceTools::CanBuy($arOffersIBlock["OFFERS_IBLOCK_ID"], $arPrices, $arOffer);
				}
				if (isset($arOffer))
					unset($arOffer);
			}
			if (!empty($arMeasureMap))
			{
				$rsMeasures = CCatalogMeasure::getList(
					array(),
					array('@ID' => array_keys($arMeasureMap)),
					false,
					false,
					array()
				);
				while ($arMeasure = $rsMeasures->GetNext())
				{
					$arMeasure['ID'] = intval($arMeasure['ID']);
					if (isset($arMeasureMap[$arMeasure['ID']]) && !empty($arMeasureMap[$arMeasure['ID']]))
					{
						foreach ($arMeasureMap[$arMeasure['ID']] as &$intOneKey)
						{
							$arResult[$intOneKey]['CATALOG_MEASURE_NAME'] = $arMeasure['SYMBOL_RUS'];
							$arResult[$intOneKey]['~CATALOG_MEASURE_NAME'] = $arMeasure['~SYMBOL_RUS'];
						}
						unset($intOneKey);
					}
				}
			}
		}

		return $arResult;
	}

	public static function GetDefaultMeasure()
	{
		$arDefaultMeasure = array();
		if(CModule::IncludeModule("catalog"))
		{
			$rsMeasures = CCatalogMeasure::getList(
				array(),
				array('IS_DEFAULT' => 'Y'),
				false,
				false,
				array()
			);
			if ($arMeasure = $rsMeasures->GetNext())
			{
				$arMeasure['ID'] = intval($arMeasure['ID']);
				$arMeasure['CODE'] = intval($arMeasure['CODE']);
				$arDefaultMeasure = $arMeasure;
			}
			if (empty($arDefaultMeasure))
			{
				$rsMeasures = CCatalogMeasure::getList(
					array(),
					array('CODE' => 796),
					false,
					false,
					array()
				);
				if ($arMeasure = $rsMeasures->GetNext())
				{
					$arMeasure['ID'] = intval($arMeasure['ID']);
					$arMeasure['CODE'] = intval($arMeasure['CODE']);
					$arDefaultMeasure = $arMeasure;
				}
			}
		}
		if (empty($arDefaultMeasure))
		{
			$arDefaultMeasure = array(
				'ID' => 0,
				'CODE' => 0,
				'SYMBOL_RUS' => GetMessage('IB_CATALOG_DEFAULT_MEASURE_SHORT_NAME'),
				'~SYMBOL_RUS' => GetMessage('IB_CATALOG_DEFAULT_MEASURE_SHORT_NAME'),
				'SYMBOL_INTL' => ''
			);
		}
		return $arDefaultMeasure;
	}
}
?>