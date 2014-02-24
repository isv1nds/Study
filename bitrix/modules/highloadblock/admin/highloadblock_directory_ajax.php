<?
define('STOP_STATISTICS', true);
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);
define('BX_SECURITY_SHOW_MESSAGE', true);
define("PUBLIC_AJAX_MODE", true);
define("NOT_CHECK_PERMISSIONS", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);
header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

if(!CModule::IncludeModule("highloadblock"))
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'SS_MODULE_NOT_INSTALLED'));
	die();
}

if(check_bitrix_sessid())
{
	CUtil::JSPostUnescape();
	function addTableXmlIDCell($intPropID, $arPropInfo)
	{
		return '<input type="text" onBlur="getDirectoryTableHead(this);" name="PROPERTY_DIRECTORY_VALUES['.$intPropID.'][UF_XML_ID]" id="PROPERTY_VALUES_XML_'.$intPropID.'" value="'.htmlspecialcharsbx($arPropInfo['XML_ID']).'" size="15" maxlength="200" style="width:90%">';
	}

	function addTableNameCell($intPropID, $arPropInfo)
	{
		return '<input type="text" name="PROPERTY_DIRECTORY_VALUES['.$intPropID.'][UF_NAME]" id="PROPERTY_VALUES_NAME_'.$intPropID.'" value="'.htmlspecialcharsbx($arPropInfo['NAME']).'" size="35" maxlength="255" style="width:90%">';
	}

	function addTableLinkCell($intPropID, $arPropInfo)
	{
		return '<input type="text" name="PROPERTY_DIRECTORY_VALUES['.$intPropID.'][UF_LINK]" id="PROPERTY_VALUES_LINK_'.$intPropID.'" value="'.htmlspecialcharsbx($arPropInfo['LINK']).'" size="35" style="width:90%">';
	}

	function addTableSortCell($intPropID, $arPropInfo)
	{
		return '<input type="text" name="PROPERTY_DIRECTORY_VALUES['.$intPropID.'][UF_SORT]" id="PROPERTY_VALUES_SORT_'.$intPropID.'" value="100" size="5" maxlength="11">';
	}

	function addTableFileCell($intPropID, $arPropInfo)
	{
		if(!CModule::IncludeModule('fileman'))
			return '';
		return CFile::InputFile("PROPERTY_DIRECTORY_VALUES[$intPropID][FILE]", 20, 0, false, 0, "IMAGE", "", 0, "class=typeinput", "", false, false);
	}

	function addTableDefCell($intPropID, $arPropInfo)
	{
		return '<input type="'.('Y' == $arPropInfo['MULTIPLE'] ? 'checkbox' : 'radio').'" name="PROPERTY_VALUES_DEF'.('Y' == $arPropInfo['MULTIPLE'] ? '[]' : '').'" id="PROPERTY_VALUES_DEF_'.$arPropInfo['ID'].'" value="'.$arPropInfo['ID'].'" '.('Y' == $arPropInfo['DEF'] ? 'checked="checked"' : '').'>';
	}

	function addTableDescriptionCell($intPropID, $arPropInfo)
	{
		return '<input type="text" name="PROPERTY_DIRECTORY_VALUES['.$intPropID.'][UF_DESCRIPTION]" id="PROPERTY_VALUES_DESCRIPTION_'.$intPropID.'" value="'.htmlspecialcharsbx($arPropInfo['DESCRIPTION']).'" style="width:100%">';
	}

	function addTableFullDescriptionCell($intPropID, $arPropInfo)
	{
		return '<input type="text" name="PROPERTY_DIRECTORY_VALUES['.$intPropID.'][UF_FULL_DESCRIPTION]" id="PROPERTY_VALUES_FULL_DESCRIPTION_'.$intPropID.'" value="'.htmlspecialcharsbx($arPropInfo['FULL_DESCRIPTION']).'" style="width:100%">';
	}

	function addTableRow($intPropID, $arPropInfo)
	{
		return'<td>'.addTableNameCell($intPropID, $arPropInfo).'</td>
				<td>'.addTableSortCell($intPropID, $arPropInfo).'</td>
				<td style="text-align:center">'.addTableXmlIDCell($intPropID, $arPropInfo).'</td>
				<td style="text-align:center">'.addTableFileCell($intPropID, $arPropInfo).'</td>
				<td style="text-align:center">'.addTableLinkCell($intPropID, $arPropInfo).'</td>
				<td style="text-align:center">'.addTableDefCell($intPropID, $arPropInfo).'</td>
				<td style="text-align:center">'.addTableDescriptionCell($intPropID, $arPropInfo).'</td>
				<td style="text-align:center">'.addTableFullDescriptionCell($intPropID, $arPropInfo).'</td>';
	}
	$rowNumber = intval($_REQUEST['rowNumber']);
	echo CUtil::PhpToJSObject(addTableRow($rowNumber, 0));
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>