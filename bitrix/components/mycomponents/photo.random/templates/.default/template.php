<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="photo-random">
	<?if(is_array($arResult["PICTURE"])):?>
		<a href="<?=$arResult["DETAIL_PAGE_URL"]?>"><img
				border="0"
				src="<?=$arResult["PICTURE"]["src"]?>"
				width="<?=$arResult["PICTURE"]["width"]?>"
				height="<?=$arResult["PICTURE"]["height"]?>"
				/></a><br />
	<?endif?>
	<a href="<?=$arResult["DETAIL_PAGE_URL"]?>"><?=$arResult["NAME"]?></a>
</div>
