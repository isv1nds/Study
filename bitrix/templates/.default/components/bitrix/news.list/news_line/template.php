<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

?>
<div class="cn_hp_lastnews">
    <h3><a href="/news/"><?=GetMessage('NEWS')?></a></h3>
    <ul>
        <?foreach($arResult["ITEMS"] as $arItem):?>
        <li>
            <h4><a href="<?=$arItem["DETAIL_PAGE_URL"]?>"><?echo $arItem["DISPLAY_ACTIVE_FROM"]?></a></h4>
            <p><?if(!empty($arItem["PREVIEW_TEXT"])):?>
                    <?echo $arItem["PREVIEW_TEXT"];?>
                    <?else:echo $arItem["NAME"]; ?>
                <?endif;?></p>
        </li>
        <?endforeach;?>

    </ul>
    <br/>
    <a href="/news/" class="cn_hp_lastnews_more"><?=GetMessage('ALL_NEWS')?> &rarr;</a>
</div>

