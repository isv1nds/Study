<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>





    <?if(is_array($arResult["DETAIL_PICTURE"])):?>
        <img style="float: left "  border="0" src="<?=$arResult["DETAIL_PICTURE"]["SRC"]?>" alt="<?=$arResult["NAME"]?>"  title="<?=$arResult["NAME"]?>" />
    <?endif?>
    <?if(strlen($arResult["DETAIL_TEXT"])>0):?>
		<?echo $arResult["DETAIL_TEXT"];?>
	<?else:?>
    <?echo $arResult["PREVIEW_TEXT"];?>
    <?endif?>








