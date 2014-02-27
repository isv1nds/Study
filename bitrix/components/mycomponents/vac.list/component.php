<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
global $DB;
/** @global CUser $USER */
global $USER;
/** @global CMain $APPLICATION */
global $APPLICATION;

/*************************************************************************
	Processing of received parameters
*************************************************************************/
if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 180;



$arParams["IBLOCK_ID"]=intval($arParams["IBLOCK_ID"]);


if( $arParams["IBLOCK_ID"]>0&& $this->StartResultCache(false, ($arParams["CACHE_GROUPS"]==="N"? false: $USER->GetGroups())))
{
	if(!CModule::IncludeModule("iblock"))
	{
		$this->AbortResultCache();
		ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
		return;
	}


    //---------------------------------------------------------------------

    $sect=array();
   $uf_arresult = CIBlockSection::GetList(Array("SORT"=>"ASC"), Array("IBLOCK_ID" =>$arParams["IBLOCK_ID"]), false,array("NAME","ID"));
    while($res=$uf_arresult->GetNext()){
        $arResult["SECTIONS"][$res["ID"]]["NAME"]=$res["NAME"];
    }


    //----------------------------------------------------------------------

	//SELECT




	$arSelect = array(
		"ID",
		"IBLOCK_ID",
		"CODE",
		"IBLOCK_SECTION_ID",
		"NAME",
		"DETAIL_PAGE_URL",
        "IBLOCK_SECTION_ID",
        "DETAIL_TEXT"

	);


    $properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$arParams["IBLOCK_ID"]));

    while ($prop_fields = $properties->GetNext())
    {      $arSelect[]="PROPERTY_".$prop_fields["CODE"];

    }



	//WHERE
	$arFilter = array(
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"ACTIVE_DATE" => "Y",
		"ACTIVE"=>"Y",
		"CHECK_PERMISSIONS"=>"Y",


	);

	//ORDER BY
	$arSort = array(
		"SORT"=>"ASC",
	);
	//EXECUTE
	$rsIBlockElement = CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);
	$rsIBlockElement->SetUrlTemplates($arParams["DETAIL_URL"]);
	while($res = $rsIBlockElement->GetNext())
	{



        $arResult["SECTIONS"][$res["IBLOCK_SECTION_ID"]]["ITEMS"][]=$res;




	}
if($arResult["SECTIONS"]){
    $this->SetResultCacheKeys(array(
    ));
    $this->IncludeComponentTemplate();}
	else
	{
		$this->AbortResultCache();
	}
}
?>
