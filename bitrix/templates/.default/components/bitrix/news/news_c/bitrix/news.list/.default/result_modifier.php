<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

foreach($arResult["ITEMS"] as $KEY=>$arItem){

    $thumb=CFile::ResizeImageGet($arItem["DETAIL_PICTURE"], array('width'=>$arParams["NEWS_IMG_W"], 'height'=>$arParams["NEWS_IMG_H"]), BX_RESIZE_IMAGE_PROPORTIONAL, true);
    $arResult["ITEMS"][$KEY]["PREVIEW_PICTURE"]=$thumb;


}




?>

