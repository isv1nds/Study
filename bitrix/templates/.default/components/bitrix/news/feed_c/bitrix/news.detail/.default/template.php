<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

    <div class="review-text">
        <div class="review-text-cont">
            <?if(strlen($arResult["DETAIL_TEXT"])>0):?>
                <?echo $arResult["DETAIL_TEXT"];?>
            <?else:?>
                <?echo $arResult["PREVIEW_TEXT"];?>
            <?endif?>
        </div>
        <div style="float: right; font-style: italic;">
            <?=$arResult["NAME"]?>
        </div>
    </div>
    <div style="clear: both;" class="review-img-wrap"><img src="<?=$arResult["DETAIL_PICTURE"]["SRC"]?>" alt="<?=$arResult["NAME"]?>"  title="<?=$arResult["NAME"]?>"></div>
