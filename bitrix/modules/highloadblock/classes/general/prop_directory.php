<?php
use Bitrix\Highloadblock as HL;
IncludeModuleLangFile(__FILE__);

/**
 * Class CIBlockPropertyDirectory
 */
class CIBlockPropertyDirectory
{
	/**
	 * @return array
	 */
	function GetUserTypeDescription()
	{
		return array(
			"PROPERTY_TYPE" => "S",
			"USER_TYPE" => "directory",
			"DESCRIPTION" => GetMessage("HIBLOCK_PROP_DIRECTORY_DESCRIPTION"),
			"GetSettingsHTML" => array('CIBlockPropertyDirectory', "GetSettingsHTML"),
			"GetPropertyFieldHtml" => array('CIBlockPropertyDirectory', "GetPropertyFieldHtml"),
			"GetPropertyFieldHtmlMulty" => array('CIBlockPropertyDirectory', "GetPropertyFieldHtmlMulty"),
			"PrepareSettings" =>array("CIBlockPropertyDirectory", "PrepareSettings"),
			"GetAdminListViewHTML" =>array("CIBlockPropertyDirectory", "GetAdminListViewHTML"),
			"GetPublicViewHTML" =>array("CIBlockPropertyDirectory", "GetPublicViewHTML"),
		);
	}

	/**
	 * @param $arProperty
	 * @return array
	 */
	function PrepareSettings($arProperty)
	{
		$size = 0;
		if(is_array($arProperty["USER_TYPE_SETTINGS"]))
			$size = intval($arProperty["USER_TYPE_SETTINGS"]["size"]);
		if($size <= 0)
			$size = 1;

		$width = 0;
		if(is_array($arProperty["USER_TYPE_SETTINGS"]))
			$width = intval($arProperty["USER_TYPE_SETTINGS"]["width"]);
		if($width <= 0)
			$width = 0;

		if(is_array($arProperty["USER_TYPE_SETTINGS"]) && $arProperty["USER_TYPE_SETTINGS"]["group"] === "Y")
			$group = "Y";
		else
			$group = "N";

		if(is_array($arProperty["USER_TYPE_SETTINGS"]) && $arProperty["USER_TYPE_SETTINGS"]["multiple"] === "Y")
			$multiple = "Y";
		else
			$multiple = "N";
		$directoryTableName = '';
		if(is_array($arProperty["USER_TYPE_SETTINGS"]) && isset($arProperty["USER_TYPE_SETTINGS"]["TABLE_NAME"]))
			$directoryTableName = $arProperty["USER_TYPE_SETTINGS"]['TABLE_NAME'];
		return array(
			"size" =>  $size,
			"width" => $width,
			"group" => $group,
			"multiple" => $multiple,
			"TABLE_NAME" => $directoryTableName,
		);
	}

	/**
	 * @param $arProperty
	 * @param $strHTMLControlName
	 * @param $arPropertyFields
	 * @return string
	 */
	function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields)
	{
		CUtil::InitJSCore(array('translit'));
		$cellOption = "<option value=-1>".htmlspecialcharsbx(GetMessage('HIBLOCK_PROP_DIRECTORY_NEW_DIRECTORY'))."</option>";
		$settings = CIBlockPropertyDirectory::PrepareSettings($arProperty);
		$rsData = HL\HighloadBlockTable::getList(array());
		while($arData = $rsData->fetch())
		{
			$selected = ($settings["TABLE_NAME"] == $arData['TABLE_NAME']) ? ' selected' : '';
			$cellOption .= "<option ".$selected." value=".$arData["TABLE_NAME"].">".htmlspecialcharsbx($arData["NAME"].' ('.$arData["TABLE_NAME"]).")</option>";
		}
		$arPropertyFields = array(
			"HIDE" => array("ROW_COUNT", "COL_COUNT", "MULTIPLE_CNT", "DEFAULT_VALUE"),
		);

		$selectDir = htmlspecialcharsbx(GetMessage("HIBLOCK_PROP_DIRECTORY_SELECT_DIR"));
		$headingXmlId = htmlspecialcharsbx(GetMessage("HIBLOCK_PROP_DIRECTORY_XML_ID"));
		$headingName = htmlspecialcharsbx(GetMessage("HIBLOCK_PROP_DIRECTORY_NAME"));
		$headingSort = htmlspecialcharsbx(GetMessage("HIBLOCK_PROP_DIRECTORY_SORT"));
		$headingDef = htmlspecialcharsbx(GetMessage("HIBLOCK_PROP_DIRECTORY_DEF"));
		$headingLink = htmlspecialcharsbx(GetMessage("HIBLOCK_PROP_DIRECTORY_LINK"));
		$headingFile = htmlspecialcharsbx(GetMessage("HIBLOCK_PROP_DIRECTORY_FILE"));
		$headingDescription = htmlspecialcharsbx(GetMessage("HIBLOCK_PROP_DIRECTORY_DECSRIPTION"));
		$headingFullDescription = htmlspecialcharsbx(GetMessage("HIBLOCK_PROP_DIRECTORY_FULL_DESCRIPTION"));
		$directoryName = htmlspecialcharsbx(GetMessage("HIBLOCK_PROP_DIRECTORY_NEW_NAME"));
		$directoryMore = htmlspecialcharsbx(GetMessage("HIBLOCK_PROP_DIRECTORY_MORE"));
		return <<<"HIBSELECT"
	<script>
	function getDirectoryTableRow(myDataObj)
	{
		if(BX('hlb_directory_table_id').value == '-1')
		{
			BX('hlb_directory_table_tr').style.display = 'table-row';
			BX('hlb_directory_table_button').style.display = 'table-row';
			BX('hlb_directory_title_tr').style.display = 'table-row';
			var rowNumber = BX('hlb_directory_row_number').value;
			var query_data = {
				'method': 'POST',
				'dataType': 'json',
				'timeout': 90,
				'url': '/bitrix/admin/highloadblock_directory_ajax.php?rowNumber=' + rowNumber + '&sessid=' + BX.bitrix_sessid(),
				'data':  BX.ajax.prepareData(myDataObj),
				'onsuccess': BX.delegate(function(data) {
					var newTr = BX('hlb_directory_table').appendChild(BX.create('tr', {}));
					newTr.innerHTML = data;
					BX('hlb_directory_row_number').value = Number(BX('hlb_directory_row_number').value) + 1;
				}),
				'onfailure': BX.delegate(function(data) {
				})
			};
			return BX.ajax(query_data);
		}
		else
		{
			BX('hlb_directory_table_tr').style.display = 'none';
			BX('hlb_directory_table_button').style.display = 'none';
			BX('hlb_directory_title_tr').style.display = 'none';
		}
		return '';
	}
	function getDirectoryTableHead(e)
	{
		e.value = BX.translit(e.value, {
			'max_len' : 35,
			'change_case' : 'L',
			'replace_space' : '',
			'replace_other' : '',
			'delete_repeat_replace' : true
		});

		if(BX('hlb_directory_table_id').value == '-1')
		{
			BX('hlb_directory_table_id_hidden').disabled = false;
			BX('hlb_directory_table_id_hidden').value = 'b_'+BX('hlb_directory_table_name').value;
		}
	}
	</script>
	<tr>
		<td>{$selectDir}:</td>
		<td>
			<select name="{$strHTMLControlName["NAME"]}[TABLE_NAME]" id="hlb_directory_table_id"  onChange="getDirectoryTableRow();"/>
				$cellOption
			</select>
			<input type="hidden" name="{$strHTMLControlName["NAME"]}[TABLE_NAME]" disabled id="hlb_directory_table_id_hidden">
		</td>
	</tr>
	<tr id="hlb_directory_title_tr" class="adm-detail-required-field">
		<td>$directoryName</td>
		<td><input type='text' name="HLB_NEW_TITLE" size="30" id="hlb_directory_table_name" onBlur="getDirectoryTableHead(this);"></td>
	</tr>
	<tr id="hlb_directory_table_tr">
		<td colspan="2" align="center">
			<table class="internal" id="hlb_directory_table" style="margin: 0 auto;">
				<tr class="heading">
					<td>$headingName</td>
					<td>$headingSort</td>
					<td>$headingXmlId</td>
					<td>$headingFile</td>
					<td>$headingLink</td>
					<td>$headingDef</td>
					<td>$headingDescription</td>
					<td>$headingFullDescription</td>
				</tr>
				<tr>
					<script>getDirectoryTableRow();</script>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<input type='hidden' value='0' id='hlb_directory_row_number'>
			<input type='hidden' name="{$strHTMLControlName["NAME"]}[LANG][UF_NAME]" value="{$headingName}">
			<input type='hidden' name="{$strHTMLControlName["NAME"]}[LANG][UF_SORT]" value="{$headingSort}">
			<input type='hidden' name="{$strHTMLControlName["NAME"]}[LANG][UF_XML_ID]" value="{$headingXmlId}">
			<input type='hidden' name="{$strHTMLControlName["NAME"]}[LANG][UF_FILE]" value="{$headingFile}">
			<input type='hidden' name="{$strHTMLControlName["NAME"]}[LANG][UF_LINK]" value="{$headingLink}">
			<input type='hidden' name="{$strHTMLControlName["NAME"]}[LANG][UF_DEF]" value="{$headingDef}">
			<input type='hidden' name="{$strHTMLControlName["NAME"]}[LANG][UF_DESCRIPTION]" value="{$headingDescription}">
			<input type='hidden' name="{$strHTMLControlName["NAME"]}[LANG][UF_FULL_DESCRIPTION]" value="{$headingFullDescription}">
			<input type='button' value='$directoryMore' onClick='getDirectoryTableRow()' id="hlb_directory_table_button">
		</td>
	</tr>
HIBSELECT;
	}

	/**
	 * @param $arProperty
	 * @param $value
	 * @param $strHTMLControlName
	 * @return string
	 */
	function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
	{
		$settings = CIBlockPropertyDirectory::PrepareSettings($arProperty);
		if($settings["size"] > 1)
			$size = ' size="'.$settings["size"].'"';
		else
			$size = '';

		if($settings["width"] > 0)
			$width = ' style="width:'.$settings["width"].'px"';
		else
			$width = '';

		$options = CIBlockPropertyDirectory::GetOptionsHtml($arProperty, array($value["VALUE"]));
		$html = '<select name="'.$strHTMLControlName["VALUE"].'"'.$size.$width.'>';
		$html .= $options;
		$html .= '</select>';
		return  $html;
	}

	function GetPropertyFieldHtmlMulty($arProperty, $value, $strHTMLControlName)
	{
		$max_n = 0;
		$values = array();
		if(is_array($value))
		{
			foreach($value as $property_value_id => $arValue)
			{
				$values[$property_value_id] = $arValue["VALUE"];
				if(preg_match("/^n(\\d+)$/", $property_value_id, $match))
				{
					if($match[1] > $max_n)
						$max_n = intval($match[1]);
				}
			}
		}

		$settings = CIBlockPropertyDirectory::PrepareSettings($arProperty);
		if($settings["size"] > 1)
			$size = ' size="'.$settings["size"].'"';
		else
			$size = '';

		if($settings["width"] > 0)
			$width = ' style="width:'.$settings["width"].'px"';
		else
			$width = '';

		if($settings["multiple"]=="Y")
		{
			$bWasSelect = false;
			$options = CIBlockPropertyDirectory::GetOptionsHtml($arProperty, $values, $bWasSelect);

			$html = '<input type="hidden" name="'.$strHTMLControlName["VALUE"].'[]" value="">';
			$html .= '<select multiple name="'.$strHTMLControlName["VALUE"].'[]"'.$size.$width.'>';
			if($arProperty["IS_REQUIRED"] != "Y")
				$html .= '<option value=""'.(!$bWasSelect? ' selected': '').'>'.GetMessage("HIBLOCK_PROP_DIRECTORY_NO_VALUE").'</option>';
			$html .= $options;
			$html .= '</select>';
		}
		else
		{
			if(end($values) != "" || substr(key($values), 0, 1) != "n")
				$values["n".($max_n+1)] = "";

			$name = $strHTMLControlName["VALUE"]."VALUE";

			$html = '<table cellpadding="0" cellspacing="0" border="0" class="nopadding" width="100%" id="tb'.md5($name).'">';
			foreach($values as $property_value_id=>$value)
			{
				$html .= '<tr><td>';

				$bWasSelect = false;
				$options = CIBlockPropertyDirectory::GetOptionsHtml($arProperty, array($value), $bWasSelect);

				$html .= '<select name="'.$strHTMLControlName["VALUE"].'['.$property_value_id.'][VALUE]"'.$size.$width.'>';
				$html .= '<option value=""'.(!$bWasSelect? ' selected': '').'>'.GetMessage("HIBLOCK_PROP_DIRECTORY_NO_VALUE").'</option>';
				$html .= $options;
				$html .= '</select>';

				$html .= '</td></tr>';
			}
			$html .= '</table>';

			$html .= '<input type="button" value="'.GetMessage("HIBLOCK_PROP_DIRECTORY_MORE").'" onClick="if(window.addNewRow){addNewRow(\'tb'.md5($name).'\', -1)}else{addNewTableRow(\'tb'.md5($name).'\', 1, /\[(n)([0-9]*)\]/g, 2)}">';
		}
		return  $html;
	}

	/**
	 * @param $arProperty
	 * @param $values
	 * @return string
	 */
	function GetOptionsHtml($arProperty, $values)
	{
		$cellOption = '';
		$highLoadIBTableName = '';
		if(isset($arProperty["USER_TYPE_SETTINGS"]["TABLE_NAME"]))
			$highLoadIBTableName = $arProperty["USER_TYPE_SETTINGS"]["TABLE_NAME"];
		if($highLoadIBTableName != '')
		{
			$arData = self::getEntityFieldsByFilter(array("TABLE_NAME" => $highLoadIBTableName));
			foreach($arData as $data)
			{
				$options = '';
				if(in_array($data["UF_XML_ID"], $values))
					$options = ' selected';
				$cellOption .= "<option ".$options." value=".$data['UF_XML_ID'].">".htmlspecialcharsbx($data["UF_NAME"].' ['.$data["ID"])."]</option>";
			}

		}

		return $cellOption;
	}

	/**
	 * @param array $filter
	 * @return array
	 */
	private static function getEntityFieldsByFilter($filter = array())
	{
		$arResult = array();
		if(is_array($filter) > 0)
		{
			$hlblock = HL\HighloadBlockTable::getList(array("filter" => $filter))->fetch();
			if(isset($hlblock['ID']))
			{
				$entity = HL\HighloadBlockTable::compileEntity($hlblock);
				$entity_data_class = $entity->getDataClass();
				$rsData = $entity_data_class::getList(array());
				while($arData = $rsData->fetch())
				{
					$arResult[] = $arData;
				}
			}
		}
		return $arResult;
	}

	/**
	 * @param $arProperty
	 * @param $value
	 * @param $strHTMLControlName
	 * @return string
	 */
	public static function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
	{
		$arData = self::getEntityFieldsByFilter(array("TABLE_NAME" => $arProperty["USER_TYPE_SETTINGS"]["TABLE_NAME"]));
		if(is_array($arData))
		{
			foreach($arData as $data)
				if($data["UF_XML_ID"] == $value["VALUE"])
					return htmlspecialcharsbx($data["UF_NAME"]);
		}
		return "&nbsp;";
	}

	/**
	 * @param $arProperty
	 * @param $value
	 * @param $strHTMLControlName
	 * @return string
	 */
	public static function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
	{
		return self::GetAdminListViewHTML($arProperty, $value, $strHTMLControlName);
	}
}