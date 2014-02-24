<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arParams["RECORDS"] = (is_array($arParams["RECORDS"]) ? $arParams["RECORDS"] : array());
$arParams["NAV_STRING"] = (!!$arParams["NAV_STRING"] && is_string($arParams["NAV_STRING"]) ? $arParams["NAV_STRING"] : "");
$arParams["PREORDER"] = ($arParams["PREORDER"] == "Y" ? "Y" : "N");
$arParams["RIGHTS"] = (is_array($arParams["RIGHTS"]) ? $arParams["RIGHTS"] : array());
foreach (array("MODERATE", "EDIT", "DELETE") as $act)
	$arParams["RIGHTS"][$act] = in_array(strtoupper($arParams["RIGHTS"][$act]), array("Y", "ALL", "OWN", "OWNLAST")) ? $arParams["RIGHTS"][$act] : "N";
$arParams["NOTIFY_TAG"] = trim($arParams["NOTIFY_TAG"]);
$arParams["VISIBLE_RECORDS_COUNT"] = (!!$arParams["NAV_RESULT"] ? intval($arParams["VISIBLE_RECORDS_COUNT"]) : 0);
$arParams["TEMPLATE_ID"] = (!!$arParams["TEMPLATE_ID"] ? $arParams["TEMPLATE_ID"] : 'COMMENT_'.$arParams["ENTITY_XML_ID"].'_');
$arParams["AVATAR_SIZE"] = ($arParams["AVATAR_SIZE"] > 0 ? $arParams["AVATAR_SIZE"] : 30);
$_SESSION["UC"] = (!!$_SESSION["UC"] ? $_SESSION["UC"] : array());
$_SESSION["UC"][$arParams["ENTITY_XML_ID"]] = array("ACTIVITY" => 0, "RECORDS" => array());
if ($arParams["VISIBLE_RECORDS_COUNT"] > 0)
{
	if ($arParams["NAV_RESULT"]->bShowAll)
		$arParams["VISIBLE_RECORDS_COUNT"] = 0;
	else if (array_key_exists($arResult['RESULT'], $arParams["RECORDS"]))
		$arParams["VISIBLE_RECORDS_COUNT"] = count($arResult["MESSAGES"]);
	else if ($arParams["NAV_RESULT"]->NavRecordCount <= $arParams["VISIBLE_RECORDS_COUNT"])
		$arParams["VISIBLE_RECORDS_COUNT"] = $arParams["NAV_RESULT"]->NavRecordCount;
	else if (isset($_REQUEST["PAGEN_".$arParams["NAV_RESULT"]->NavNum]) ||
		isset($_REQUEST["FILTER"]) && $arParams["ENTITY_XML_ID"] == $_REQUEST["ENTITY_XML_ID"])
		$arParams["VISIBLE_RECORDS_COUNT"] = 0;
	if (!!$arParams["NAV_STRING"])
	{
		$path = "PAGEN_".$arParams["NAV_RESULT"]->NavNum."=";
		if ($arParams["VISIBLE_RECORDS_COUNT"] > 0)
			$path .= $arParams["NAV_RESULT"]->NavPageNomer;
		else if ($arParams["NAV_RESULT"]->bDescPageNumbering)
			$path .= ($arParams["NAV_RESULT"]->NavPageNomer - 1);
		else
			$path .= ($arParams["NAV_RESULT"]->NavPageNomer + 1);
		$arParams["NAV_STRING"] .= (strpos($arParams["NAV_STRING"], "?") === false ? "?" : "&").$path;
	}
}
$arParams["LAST_RECORD"] = array();
if (!empty($arParams["RECORDS"]))
{
	if ($arParams["VISIBLE_RECORDS_COUNT"] > 0)
	{
		for ($ii = 0; $ii < $arParams["VISIBLE_RECORDS_COUNT"]; $ii++)
		{
			$res = array_shift($arParams["RECORDS"]);
			$list[$res["ID"]] = $res;
		}

		$arParams["LAST_RECORD"] = $res;
		$arParams["RECORDS"] = $list;
	}
	if ($arParams["PREORDER"] === "N")
		$arParams["RECORDS"] = array_reverse($arParams["RECORDS"], true);
}

foreach ($arParams["RECORDS"] as $key => $res)
{
	$res = array(
		"ID" => $res["ID"], // integer
		"ENTITY_XML_ID" => $arParams["ENTITY_XML_ID"], // string
		"FULL_ID" => array($arParams["ENTITY_XML_ID"], $res["ID"]),
		"NEW" => $res["NEW"], //"Y" | "N"
		"APPROVED" => $res["APPROVED"], //"Y" | "N"
		"POST_TIMESTAMP" => ($res["POST_TIMESTAMP"] - CTimeZone::GetOffset()),
		"POST_TIME" => $res["POST_TIME"],
		"POST_DATE" => $res["POST_DATE"],
		"~POST_MESSAGE_TEXT" => $res["~POST_MESSAGE_TEXT"],
		"POST_MESSAGE_TEXT" => $res["POST_MESSAGE_TEXT"],
		"PANELS" => array(
			"EDIT" => $res["PANELS"]["EDIT"], //"Y" | "N"
			"MODERATE" => $res["PANELS"]["MODERATE"],//"Y" | "N"
			"DELETE" => $res["PANELS"]["DELETE"]//"Y" | "N"
		),
		"URL" => array(
			"LINK" => $res["URL"]["LINK"],
			"EDIT" => $res["URL"]["EDIT"],
			"MODERATE" => $res["URL"]["MODERATE"],
			"DELETE" => $res["URL"]["DELETE"]
		),
		"AUTHOR" => array(
			"ID" => $res["AUTHOR"]["ID"],
			"NAME" => $res["AUTHOR"]["NAME"],
			"URL" => $res["AUTHOR"]["URL"],
			"AVATAR" => $res["AUTHOR"]["AVATAR"]),
		"FILES" => $res["FILES"],
		"UF" => $res["UF"],
		"BEFORE_HEADER" => $res["BEFORE_HEADER"].$APPLICATION->GetViewContent(implode('_', array($arParams["TEMPLATE_ID"], 'ID', $res['ID'], 'BEFORE_HEADER'))),
		"BEFORE_ACTIONS" => $res["BEFORE_ACTIONS"].$APPLICATION->GetViewContent(implode('_', array($arParams["TEMPLATE_ID"], 'ID', $res['ID'], 'BEFORE_ACTIONS'))),
		"AFTER_ACTIONS" => $res["AFTER_ACTIONS"].$APPLICATION->GetViewContent(implode('_', array($arParams["TEMPLATE_ID"], 'ID', $res['ID'], 'AFTER_ACTIONS'))),
		"AFTER_HEADER" => $res["AFTER_HEADER"].$APPLICATION->GetViewContent(implode('_', array($arParams["TEMPLATE_ID"], 'ID', $res['ID'], 'AFTER_HEADER'))),
		"BEFORE" => $res["BEFORE"].$APPLICATION->GetViewContent(implode('_', array($arParams["TEMPLATE_ID"], 'ID', $res['ID'], 'BEFORE'))),
		"AFTER" => $res["AFTER"].$APPLICATION->GetViewContent(implode('_', array($arParams["TEMPLATE_ID"], 'ID', $res['ID'], 'AFTER'))),
		"BEFORE_RECORD" => $res["BEFORE_RECORD"].$APPLICATION->GetViewContent(implode('_', array($arParams["TEMPLATE_ID"], 'ID', $res['ID'], 'BEFORE_RECORD'))),
		"AFTER_RECORD" => $res["AFTER_RECORD"].$APPLICATION->GetViewContent(implode('_', array($arParams["TEMPLATE_ID"], 'ID', $res['ID'], 'AFTER_RECORD')))
	);
	$arParams["RECORDS"][$key] = $res;
}

ob_start();
	$this->IncludeComponentTemplate();
$output = ob_get_clean();
foreach (GetModuleEvents('main.post.list', 'OnCommentsDisplayTemplate', true) as $arEvent)
{
	$result = ExecuteModuleEventEx($arEvent, array(&$output, &$arParams, &$arResult));
}
return array("HTML" => $output, "JSON" => $arResult["JSON"]);
?>