<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
IncludeTemplateLangFile(__FILE__);
?>
<div class="hd_header">
    <table>
        <tr>
            <td rowspan="2" class="hd_companyname">
                <h1><a href="/">Мебельный магазин</a></h1>
            </td>
            <td rowspan="2" class="hd_txarea">
                        <span class="tel"><?$APPLICATION->IncludeComponent(
                                "bitrix:main.include",
                                "",
                                Array(
                                    "AREA_FILE_SHOW" => "file",
                                    "PATH" => "/include/phone.php",
                                    "EDIT_TEMPLATE" => ""
                                ),
                                false
                            );?></span>	<br/>
                <?=GetMessage("WORKTIME");?>
                <span class="workhours">ежедневно с 9-00 до 18-00</span>
            </td>
            <td style="width:232px">
                <form action="">
                    <div class="hd_search_form" style="float:right;">
                        <input placeholder="Поиск" type="text"/>
                        <input type="submit" value=""/>
                    </div>
                </form>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 11px;">
                <?$APPLICATION->IncludeComponent(
                    "bitrix:system.auth.form",
                    "auth_form",
                    Array(
                        "REGISTER_URL" => "/user/registration.php",
                        "FORGOT_PASSWORD_URL" => "/user/",
                        "PROFILE_URL" => "/user/profile.php",
                        "SHOW_ERRORS" => "N"
                    )
                );?>
            </td>
        </tr>
    </table>

    <?$APPLICATION->IncludeComponent(
        "bitrix:menu",
        "top_multi",
        Array(
            "ROOT_MENU_TYPE" => "top",
            "MAX_LEVEL" => "2",
            "CHILD_MENU_TYPE" => "left",
            "USE_EXT" => "N",
            "DELAY" => "N",
            "ALLOW_MULTI_SELECT" => "N",
            "MENU_CACHE_TYPE" => "N",
            "MENU_CACHE_TIME" => "3600",
            "MENU_CACHE_USE_GROUPS" => "Y",
            "MENU_CACHE_GET_VARS" => ""
        )
    );?>


</div>