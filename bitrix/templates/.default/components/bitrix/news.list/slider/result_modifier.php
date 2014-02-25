<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?php

$ids=array();

foreach($arResult["ITEMS"] as $arItem){
    if(is_array($arItem["PROPERTIES"]["LINK"]))  $ids[]=$arItem["PROPERTIES"]["LINK"]["VALUE"];





}


if(CModule::IncludeModule("iblock")){
    $res=CIBlockElement::GetList(array(), array("IBLOCK_ID"=>2,"ID"=>$ids),false, false, array("NAME","ID", "DETAIL_PAGE_URL","PREVIEW_TEXT","PROPERTY_PRICE"));

   while( $ar_res = $res->GetNext()){
      $key= array_search($ar_res["ID"] ,$ids);
       $arResult["ITEMS"][$key]["TOVAR"]=$ar_res;
    }
}






?>