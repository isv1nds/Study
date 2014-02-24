<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
IncludeTemplateLangFile(__FILE__);
?>
<!DOCTYPE HTML>
<html lang="<?=LANGUAGE_ID;?>">
<head>
    <?$APPLICATION->ShowHead();?>
    <title><?$APPLICATION->ShowTitle()?></title>


    <?
    $APPLICATION->SetAdditionalCSS("/bitrix/templates/.default/template_styles.css");
    $APPLICATION->AddHeadScript('/bitrix/templates/.default/js/jquery-1.8.2.min.js');

    $APPLICATION->AddHeadScript('/bitrix/templates/.default/js/functions.js');
    ?>

    <link rel="icon"  href="/bitrix/templates/.default/favicon.ico"/>
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
        "bitrix:breadcrumb",
        "breadcrumbs",
        Array(
            "START_FROM" => "0",
            "PATH" => "",
            "SITE_ID" => "-"
        )
    );?>
    <div class="main_container page">
        <div class="mn_container">
            <div class="mn_content">
                <div class="main_post">
                    <div class="main_title">
                        <h1><?$APPLICATION->ShowTitle(false)?></h1>
                    </div>
                    <!-- workarea -->