<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>



<?foreach($arResult["SECTIONS"] as $sect):?>

    <h2><?=$sect["NAME"]?> (<?=count($sect["ITEMS"])?>)</h2>
        <ul>
            <?foreach($sect["ITEMS"] as $item):?>
                <li><h3><?=$item["NAME"]?></h3></li>
            <?endforeach;?>
        </ul>
<?endforeach;?>
