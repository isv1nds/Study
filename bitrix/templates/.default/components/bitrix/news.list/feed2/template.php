<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>





            <?foreach($arResult["ITEMS"] as $arItem):?>





                <div class="review-block">
                    <div class="review-text">

                        <div class="review-block-title"><span class="review-block-name"><?=$arItem['NAME']?></span><span class="review-block-description">><?=$arItem['PROPERTIES']['DOLJ']['VALUE']?></span></div>

                        <div class="review-text-cont">
                            <?echo $arItem["PREVIEW_TEXT"];?>
                        </div>
                    </div>
                    <div class="review-img-wrap">  <?if( is_array($arItem["PREVIEW_PICTURE"])):?>
                            <img src="<?echo $arItem["PREVIEW_PICTURE"]["SRC"];?>" class="rw_avatar" alt=""/>
                        <?endif?></div>
                </div>

            <?endforeach;?>

<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
    <br /><?=$arResult["NAV_STRING"]?>
<?endif;?>