<?
define("NO_KEEP_STATISTIC", true);
define('NO_AGENT_CHECK', true);
define("NO_AGENT_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define('DisableEventsCheck', true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(check_bitrix_sessid())
{

	if (!CModule::IncludeModule('iblock')) die("module iblock not installed");

	if(isset($_REQUEST["PARAMS"]) && is_array($_REQUEST["PARAMS"]))
		$commParams = $_REQUEST["PARAMS"];
	else
		$commParams = array();


	$commParams["BLOG_AJAX"] = "Y";

	$APPLICATION->IncludeComponent(
		"bitrix:catalog.comments",
		"",
		$commParams,
		false,
		array("HIDE_ICONS" => "Y")
	);
}

die();
?>
