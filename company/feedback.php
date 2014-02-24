<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("test", "Тест");
$APPLICATION->SetPageProperty("keywords", "отзывы, офисная мебель, мебель для кухни, детская мебель");
$APPLICATION->SetTitle("Отзывы");
?>
   <p>Title <? $APPLICATION->ShowTitle(); ?></p>
    <p>Title <? $APPLICATION->ShowProperty('test'); ?></p>

    Текст<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>