<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>





<?foreach($arResult["ITEMS"] as $arItem):?>

    <div class="sb_reviewed">
        <?if( is_array($arItem["PREVIEW_PICTURE"])):?>
            <a href="<?echo $arItem["DETAIL_PAGE_URL"];?>"><img src="<?echo $arItem["PREVIEW_PICTURE"]["SRC"];?>" class="rw_avatar" alt=""/></a>
        <?endif?>
        <span class="sb_rw_name"><?=$arItem['NAME']?></span>
        <span class="sb_rw_job"><?=$arItem['PROPERTIES']['DOLJ']['VALUE']?></span>
        <p> <?echo $arItem["PREVIEW_TEXT"];?></p>
        <div class="clearboth"></div>
        <div class="sb_rw_arrow"></div>
    </div>



<?endforeach;?>

