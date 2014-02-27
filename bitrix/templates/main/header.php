<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
IncludeTemplateLangFile(__FILE__);
?>
<!DOCTYPE HTML>
<html lang="<?=LANGUAGE_ID;?>">
<head>
    <?$APPLICATION->ShowHead();?>
    <title><?$APPLICATION->ShowTitle()?></title>

    <link rel="icon"  href="/bitrix/templates/.default/favicon.ico"/>

   <?
          $APPLICATION->SetAdditionalCSS("/bitrix/templates/.default/template_styles.css");
          $APPLICATION->AddHeadScript('/bitrix/templates/.default/js/jquery-1.8.2.min.js');
          $APPLICATION->AddHeadScript('/bitrix/templates/.default/js/slides.min.jquery.js');

          $APPLICATION->AddHeadScript('/bitrix/templates/.default/js/functions.js');

   ?>


    <!--[if gte IE 9]><style type="text/css">.gradient {filter: none;}</style><![endif]-->
</head>
<body>
<?$APPLICATION->ShowPanel();?>
<div class="wrap">
<div class="hd_header_area">
    <? include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/.default/include/header.php');?>
</div>

<!--- // end header area --->

<?$APPLICATION->IncludeComponent(
    "bitrix:news.list",
    "slider",
    Array(
        "IBLOCK_TYPE" => "contentt",
        "IBLOCK_ID" => "5",
        "NEWS_COUNT" => "10",
        "SORT_BY1" => "ACTIVE_FROM",
        "SORT_ORDER1" => "DESC",
        "SORT_BY2" => "SORT",
        "SORT_ORDER2" => "ASC",
        "FILTER_NAME" => "",
        "FIELD_CODE" => array(0=>"NAME",1=>"PREVIEW_TEXT",2=>"PREVIEW_PICTURE",3=>"IBLOCK_ID",4=>"",),
        "PROPERTY_CODE" => array(0=>"LINK",1=>"",),
        "CHECK_DATES" => "Y",
        "DETAIL_URL" => "",
        "AJAX_MODE" => "N",
        "AJAX_OPTION_JUMP" => "N",
        "AJAX_OPTION_STYLE" => "Y",
        "AJAX_OPTION_HISTORY" => "N",
        "CACHE_TYPE" => "A",
        "CACHE_TIME" => "36000000",
        "CACHE_FILTER" => "N",
        "CACHE_GROUPS" => "Y",
        "PREVIEW_TRUNCATE_LEN" => "",
        "ACTIVE_DATE_FORMAT" => "d.m.Y",
        "SET_TITLE" => "Y",
        "SET_STATUS_404" => "N",
        "INCLUDE_IBLOCK_INTO_CHAIN" => "Y",
        "ADD_SECTIONS_CHAIN" => "Y",
        "HIDE_LINK_WHEN_NO_DETAIL" => "N",
        "PARENT_SECTION" => "",
        "PARENT_SECTION_CODE" => "",
        "INCLUDE_SUBSECTIONS" => "Y",
        "PAGER_TEMPLATE" => ".default",
        "DISPLAY_TOP_PAGER" => "N",
        "DISPLAY_BOTTOM_PAGER" => "N",
        "PAGER_TITLE" => "Новости",
        "PAGER_SHOW_ALWAYS" => "Y",
        "PAGER_DESC_NUMBERING" => "N",
        "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
        "PAGER_SHOW_ALL" => "Y",
        "DISPLAY_DATE" => "N",
        "DISPLAY_NAME" => "N",
        "DISPLAY_PICTURE" => "N",
        "DISPLAY_PREVIEW_TEXT" => "N",
        "AJAX_OPTION_ADDITIONAL" => ""
    )
);?>


<!--- // end slider area --->

<div class="main_container homepage">

    <!-- events -->
    <div class="ev_events">
        <div class="ev_h">
            <h3>Ближайшие события</h3>
            <a href="" class="ev_allevents">Все мероприятия &rarr;</a>
        </div>
        <ul class="ev_lastevent">
            <li>
                <h4><a href="">29 августа 2012, Москва</a></h4>
                <p>Семинар производителей мебели России и СНГ, Обсуждение тенденций.</p>
            </li>
            <li>
                <h4><a href="">30 августа 2012, Санкт-Петербург</a></h4>
                <p>Открытие шоу-рума на Невском проспекте. Последние модели в большом ассортименте.</p>
            </li>
            <li>
                <h4><a href="">31 августа 2012, Краснодар</a></h4>
                <p>Открытие нового магазина в нашей дилерской сети.</p>
            </li>
        </ul>
        <div class="clearboth"></div>
    </div>
    <!-- // end events -->
    <div class="cn_hp_content">
        <div class="cn_hp_category">
            <ul>
                <li>
                    <img src="/bitrix/templates/.default/content/1.png" alt=""/>
                    <h2><a href="">Мягкая мебель</a></h2>
                    <p>Диваны, кресла и прочая мягкая мебель <a class="cn_hp_categorymore" href="">&rarr;</a></p>
                    <div class="clearboth"></div>
                </li>
                <li>
                    <img src="/bitrix/templates/.default/content/2.png" alt=""/>
                    <h2><a href="">Офисная мебель</a></h2>
                    <p>Диваны, столы, стулья <a class="cn_hp_categorymore" href="">&rarr;</a></p>
                    <div class="clearboth"></div>
                </li>
                <li>
                    <img src="/bitrix/templates/.default/content/3.png" alt=""/>
                    <h2><a href="">Мебель для кухни</a></h2>
                    <p>Полки, ящики, столы и стулья <a class="cn_hp_categorymore" href="">&rarr;</a></p>
                    <div class="clearboth"></div>
                </li>
                <li>
                    <img src="/bitrix/templates/.default/content/4.png" alt=""/>
                    <h2><a href="">Детская мебель</a></h2>
                    <p>Кровати, стулья, мягкая детская мебель <a class="cn_hp_categorymore" href="">&rarr;</a></p>
                    <div class="clearboth"></div>
                </li>
            </ul>
            <a href="" class="cn_hp_category_more">Все разделы каталога &rarr;</a>
        </div>
        <div class="cn_hp_post">
            <div class="cn_hp_post_new">
                <h3>Новинки</h3>
                <?$APPLICATION->IncludeComponent("mycomponents:photo.random", "template1", array(
	"IBLOCK_TYPE" => "products",
	"IBLOCK_ID" => "2",
	"IBLOCKS_PROP" => "15",
	"IMG_WIDTH" => "130",
	"IMG_HEIGHT" => "96",
	"DETAIL_URL" => "",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "180",
	"CACHE_GROUPS" => "Y",
	"PARENT_SECTION" => ""
	),
	false
);?>
            </div>
            <div class="cn_hp_post_action">
                <h3>Акции</h3>
                <?$APPLICATION->IncludeComponent("mycomponents:photo.random", "template1", array(
	"IBLOCK_TYPE" => "products",
	"IBLOCK_ID" => "2",
	"IBLOCKS_PROP" => "14",
	"IMG_WIDTH" => "130",
	"IMG_HEIGHT" => "96",
	"DETAIL_URL" => "",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "180",
	"CACHE_GROUPS" => "Y",
	"PARENT_SECTION" => ""
	),
	false
);?>
            </div>
            <div class="cn_hp_post_bestsellersn">
                <h3>Хиты продаж</h3>
                <?$APPLICATION->IncludeComponent("mycomponents:photo.random", "template1", array(
	"IBLOCK_TYPE" => "products",
	"IBLOCK_ID" => "2",
	"IBLOCKS_PROP" => "13",
	"IMG_WIDTH" => "130",
	"IMG_HEIGHT" => "96",
	"DETAIL_URL" => "",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "180",
	"CACHE_GROUPS" => "Y",
	"PARENT_SECTION" => ""
	),
	false
);?>
            </div>
        </div>
        <?$APPLICATION->IncludeComponent(
            "bitrix:news.list",
            "news_line",
            Array(
                "IBLOCK_TYPE" => "news",
                "IBLOCK_ID" => "1",
                "NEWS_COUNT" => "4",
                "SORT_BY1" => "ACTIVE_FROM",
                "SORT_ORDER1" => "DESC",
                "SORT_BY2" => "SORT",
                "SORT_ORDER2" => "ASC",
                "FILTER_NAME" => "",
                "FIELD_CODE" => array(0=>"",1=>"",),
                "PROPERTY_CODE" => array(0=>"",1=>"",),
                "CHECK_DATES" => "Y",
                "DETAIL_URL" => "",
                "AJAX_MODE" => "N",
                "AJAX_OPTION_JUMP" => "N",
                "AJAX_OPTION_STYLE" => "Y",
                "AJAX_OPTION_HISTORY" => "N",
                "CACHE_TYPE" => "A",
                "CACHE_TIME" => "36000000",
                "CACHE_FILTER" => "N",
                "CACHE_GROUPS" => "Y",
                "PREVIEW_TRUNCATE_LEN" => "",
                "ACTIVE_DATE_FORMAT" => "j F Y",
                "SET_TITLE" => "Y",
                "SET_STATUS_404" => "N",
                "INCLUDE_IBLOCK_INTO_CHAIN" => "Y",
                "ADD_SECTIONS_CHAIN" => "Y",
                "HIDE_LINK_WHEN_NO_DETAIL" => "N",
                "PARENT_SECTION" => "",
                "PARENT_SECTION_CODE" => "",
                "INCLUDE_SUBSECTIONS" => "Y",
                "PAGER_TEMPLATE" => ".default",
                "DISPLAY_TOP_PAGER" => "N",
                "DISPLAY_BOTTOM_PAGER" => "N",
                "PAGER_TITLE" => "Новости",
                "PAGER_SHOW_ALWAYS" => "Y",
                "PAGER_DESC_NUMBERING" => "N",
                "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
                "PAGER_SHOW_ALL" => "Y",
                "DISPLAY_DATE" => "Y",
                "DISPLAY_NAME" => "Y",
                "DISPLAY_PICTURE" => "Y",
                "DISPLAY_PREVIEW_TEXT" => "Y",
                "AJAX_OPTION_ADDITIONAL" => ""
            )
        );?>
        <div class="clearboth"></div>
    </div>
</div>


<?$APPLICATION->IncludeComponent(
    "bitrix:news.list",
    "feed",
    Array(
        "IBLOCK_TYPE" => "contentt",
        "IBLOCK_ID" => "6",
        "NEWS_COUNT" => "4",
        "SORT_BY1" => "ID",
        "SORT_ORDER1" => "ASC",
        "SORT_BY2" => "SORT",
        "SORT_ORDER2" => "ASC",
        "FILTER_NAME" => "",
        "FIELD_CODE" => array(0=>"ID",1=>"NAME",2=>"PREVIEW_TEXT",3=>"PREVIEW_PICTURE",4=>"",),
        "PROPERTY_CODE" => array(0=>"DOLJ",1=>"",),
        "CHECK_DATES" => "Y",
        "DETAIL_URL" => "",
        "AJAX_MODE" => "N",
        "AJAX_OPTION_JUMP" => "N",
        "AJAX_OPTION_STYLE" => "Y",
        "AJAX_OPTION_HISTORY" => "N",
        "CACHE_TYPE" => "A",
        "CACHE_TIME" => "36000000",
        "CACHE_FILTER" => "N",
        "CACHE_GROUPS" => "Y",
        "PREVIEW_TRUNCATE_LEN" => "",
        "ACTIVE_DATE_FORMAT" => "d.m.Y",
        "SET_TITLE" => "Y",
        "SET_STATUS_404" => "N",
        "INCLUDE_IBLOCK_INTO_CHAIN" => "Y",
        "ADD_SECTIONS_CHAIN" => "Y",
        "HIDE_LINK_WHEN_NO_DETAIL" => "N",
        "PARENT_SECTION" => "",
        "PARENT_SECTION_CODE" => "",
        "INCLUDE_SUBSECTIONS" => "Y",
        "PAGER_TEMPLATE" => ".default",
        "DISPLAY_TOP_PAGER" => "N",
        "DISPLAY_BOTTOM_PAGER" => "N",
        "PAGER_TITLE" => "Новости",
        "PAGER_SHOW_ALWAYS" => "Y",
        "PAGER_DESC_NUMBERING" => "N",
        "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
        "PAGER_SHOW_ALL" => "Y",
        "DISPLAY_DATE" => "N",
        "DISPLAY_NAME" => "N",
        "DISPLAY_PICTURE" => "N",
        "DISPLAY_PREVIEW_TEXT" => "N",
        "AJAX_OPTION_ADDITIONAL" => ""
    )
);?>

