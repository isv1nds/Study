<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

use Bitrix\Main\Localization\CultureTable;

IncludeModuleLangFile(__FILE__);

class main extends CModule
{
	var $MODULE_ID = "main";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;

	function main()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/install/index.php"));
		include($path."/classes/general/version.php");

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}
		else
		{
			$this->MODULE_VERSION = SM_VERSION;
			$this->MODULE_VERSION_DATE = SM_VERSION_DATE;
		}

		$this->MODULE_NAME = GetMessage("MAIN_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("MAIN_MODULE_DESC");
	}

	function InstallDB()
	{
		/** @global string $DBType */
		global $DB, $DBType, $DBHost, $DBLogin, $DBPassword, $DBName, $APPLICATION;

		if (!is_object($APPLICATION))
			$APPLICATION = new CMain;

		$DB = new CDatabase;
		$DB->DebugToFile = false;
		$DB->debug = true;

		if (!defined("DBPersistent"))
			define("DBPersistent", false);

		if (!$DB->Connect($DBHost, $DBName, $DBLogin, $DBPassword))
		{
			$APPLICATION->ThrowException(GetMessage("MAIN_INSTALL_DB_ERROR"));
			return false;
		}

		$result = $DB->Query("SELECT * FROM b_module WHERE ID='main'", true, "", array("fixed_connection"=>true));
		$success = $result && $result->Fetch();
		if ($success)
			return true;

		if ($DBType == "mysql" && defined("MYSQL_TABLE_TYPE") && strlen(MYSQL_TABLE_TYPE)>0)
			$DB->Query("SET storage_engine = '".MYSQL_TABLE_TYPE."'", true);

		$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/".$DBType."/install.sql");
		if ($errors !== false)
		{
			$APPLICATION->ThrowException(implode("", $errors));
			return false;
		}

		$this->InstallTasks();

		$group = new CGroup;

		$arGroups = array(
			array(
				"~ID" => 1,
				"ACTIVE" => "Y",
				"C_SORT" => 1,
				"NAME" => GetMessage("MAIN_ADMIN_GROUP_NAME"),
				"ANONYMOUS" => "N",
				"DESCRIPTION" => GetMessage("MAIN_ADMIN_GROUP_DESC")
			),
			array(
				"~ID" => 2,
				"ACTIVE" => "Y",
				"C_SORT" => 2,
				"NAME" => GetMessage("MAIN_EVERYONE_GROUP_NAME"),
				"ANONYMOUS" => "Y",
				"DESCRIPTION" => GetMessage("MAIN_EVERYONE_GROUP_DESC")
			),
			array(
				"~ID" => 3,
				"ACTIVE" => "Y",
				"C_SORT" => 3,
				"NAME" => GetMessage("MAIN_VOTE_RATING_GROUP_NAME"),
				"ANONYMOUS" => "N",
				"DESCRIPTION" => GetMessage("MAIN_VOTE_RATING_GROUP_DESC"),
				"STRING_ID" => "RATING_VOTE"
			),
			array(
				"~ID" => 4,
				"ACTIVE" => "Y",
				"C_SORT" => 4,
				"NAME" => GetMessage("MAIN_VOTE_AUTHORITY_GROUP_NAME"),
				"ANONYMOUS" => "N",
				"DESCRIPTION" => GetMessage("MAIN_VOTE_AUTHORITY_GROUP_DESC"),
				"STRING_ID" => "RATING_VOTE_AUTHORITY"
			)
		);

		foreach ($arGroups as $arGroup)
		{
			$rsGroup = CGroup::GetByID($arGroup["~ID"]);
			if ($rsGroup->Fetch())
				continue;

			//mssql does not allow insert identity by default
			if(strtolower($DB->type) == "mssql")
				unset($arGroup["~ID"]);

			$success = (bool)$group->Add($arGroup);
			if (!$success)
			{
				$APPLICATION->ThrowException($group->LAST_ERROR);
				return false;
			}
		}

		self::InstallRatings();

		$addResult = CultureTable::add(array(
			"NAME" => LANGUAGE_ID,
			"CODE" => LANGUAGE_ID,
			"FORMAT_DATE" => GetMessage("MAIN_DEFAULT_LANGUAGE_FORMAT_DATE"),
			"FORMAT_DATETIME" => GetMessage("MAIN_DEFAULT_LANGUAGE_FORMAT_DATETIME"),
			"FORMAT_NAME" => GetMessage("MAIN_DEFAULT_LANGUAGE_FORMAT_NAME"),
			"WEEK_START" => (LANGUAGE_ID=='en' ? 0 : 1),
			"CHARSET" => (defined("BX_UTF") ? "UTF-8" : GetMessage("MAIN_DEFAULT_LANGUAGE_FORMAT_CHARSET"))
		));
		$cultureId = $addResult->getId();

		$arLanguages = array(
			array(
				"LID" => LANGUAGE_ID,
				"ACTIVE" => "Y",
				"SORT" => 1,
				"DEF" => "Y",
				"NAME" => GetMessage("MAIN_DEFAULT_LANGUAGE_NAME"),
				"CULTURE_ID" => $cultureId,
			)
		);

		if (LANGUAGE_ID <> "en")
		{
			$addResult = CultureTable::add(array(
				"NAME" => "en",
				"CODE" => "en",
				"FORMAT_DATE" => "MM/DD/YYYY",
				"FORMAT_DATETIME" => "MM/DD/YYYY H:MI:SS T",
				"FORMAT_NAME" => "#NAME# #LAST_NAME#",
				"WEEK_START" => 0,
				"CHARSET" => (defined("BX_UTF") ? "UTF-8" : "iso-8859-1")
			));
			$cultureId = $addResult->getId();

			$arLanguages[] = array(
				"LID" => "en",
				"ACTIVE" => "Y",
				"SORT" => 2,
				"DEF" => "N",
				"NAME" => "English",
				"CULTURE_ID" => $cultureId,
			);
		}

		if (LANGUAGE_ID <> "de" && file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/lang/de/install/index.php'))
		{
			$addResult = CultureTable::add(array(
				"NAME" => "de",
				"CODE" => "de",
				"FORMAT_DATE" => "DD.MM.YYYY",
				"FORMAT_DATETIME" => "DD.MM.YYYY HH:MI:SS",
				"FORMAT_NAME" => "#NAME# #LAST_NAME#",
				"WEEK_START" => 1,
				"CHARSET" => (defined("BX_UTF") ? "UTF-8" : "iso-8859-1")
			));
			$cultureId = $addResult->getId();

			$arLanguages[] = array(
				"LID" => "de",
				"ACTIVE" => "Y",
				"SORT" => 3,
				"DEF" => "N",
				"NAME" => "German",
				"CULTURE_ID" => $cultureId,
			);
		}

		if (LANGUAGE_ID <> "ru" && file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/lang/ru/install/index.php'))
		{
			$addResult = CultureTable::add(array(
				"NAME" => "ru",
				"CODE" => "ru",
				"FORMAT_DATE" => "DD.MM.YYYY",
				"FORMAT_DATETIME" => "DD.MM.YYYY HH:MI:SS",
				"FORMAT_NAME" => "#NAME# #LAST_NAME#",
				"WEEK_START" => 1,
				"CHARSET" => (defined("BX_UTF") ? "UTF-8" : "windows-1251")
			));
			$cultureId = $addResult->getId();

			$arLanguages[] = array(
				"LID" => "ru",
				"ACTIVE" => "Y",
				"SORT" => 3,
				"DEF" => "N",
				"NAME" => "Russian",
				"CULTURE_ID" => $cultureId,
			);
		}

		$lang = new CLanguage;
		foreach ($arLanguages as $arLanguage)
		{
			$rsLang = CLanguage::GetByID($arLanguage["LID"]);
			if ($rsLang->Fetch())
				continue;

			$success = (bool)$lang->Add($arLanguage);
			if (!$success)
			{
				$APPLICATION->ThrowException($lang->LAST_ERROR);
				return false;
			}
		}

		$culture = CultureTable::getRow(array('filter'=>array(
			"=FORMAT_DATE" => GetMessage("MAIN_DEFAULT_SITE_FORMAT_DATE"),
			"=FORMAT_DATETIME" => GetMessage("MAIN_DEFAULT_SITE_FORMAT_DATETIME"),
			"=FORMAT_NAME" => GetMessage("MAIN_DEFAULT_SITE_FORMAT_NAME"),
			"=CHARSET" =>  (defined("BX_UTF") ? "UTF-8" : GetMessage("MAIN_DEFAULT_SITE_FORMAT_CHARSET")),
		)));

		if($culture)
		{
			$cultureId = $culture["ID"];
		}
		else
		{
			$addResult = CultureTable::add(array(
				"NAME" => "s1",
				"CODE" => "s1",
				"FORMAT_DATE" => GetMessage("MAIN_DEFAULT_SITE_FORMAT_DATE"),
				"FORMAT_DATETIME" => GetMessage("MAIN_DEFAULT_SITE_FORMAT_DATETIME"),
				"FORMAT_NAME" => GetMessage("MAIN_DEFAULT_SITE_FORMAT_NAME"),
				"CHARSET" =>  (defined("BX_UTF") ? "UTF-8" : GetMessage("MAIN_DEFAULT_SITE_FORMAT_CHARSET")),
			));
			$cultureId = $addResult->getId();
		}

		$arSite = array(
			"LID" => "s1",
			"ACTIVE" => "Y",
			"SORT" => 1,
			"DEF" => "Y",
			"NAME" => GetMessage("MAIN_DEFAULT_SITE_NAME"),
			"DIR" => "/",
			"LANGUAGE_ID" => LANGUAGE_ID,
			"CULTURE_ID" => $cultureId,
		);

		$rsSites = CSite::GetByID($arSite["LID"]);
		if (!$rsSites->Fetch())
		{
			$site = new CSite;
			$success = (bool)$site->Add($arSite);
			if (!$success)
			{
				$APPLICATION->ThrowException($site->LAST_ERROR);
				return false;
			}
		}

		RegisterModule("main");
		RegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', 'main', 'CIBlockPropertyUserID', 'GetUserTypeDescription', 100, '/modules/main/tools/prop_userid.php');
		RegisterModuleDependences('main', 'OnUserDelete','main', 'CFavorites','OnUserDelete', 100, "/modules/main/classes/".strtolower($GLOBALS["DB"]->type)."/favorites.php");
		RegisterModuleDependences('main', 'OnLanguageDelete','main', 'CFavorites','OnLanguageDelete', 100, "/modules/main/classes/".strtolower($GLOBALS["DB"]->type)."/favorites.php");
		RegisterModuleDependences('main', 'OnUserDelete','main', 'CUserOptions','OnUserDelete');
		RegisterModuleDependences('main', 'OnChangeFile','main', 'CMain','OnChangeFileComponent');
		RegisterModuleDependences('main', 'OnUserTypeRightsCheck','main', 'CUser','UserTypeRightsCheck');
		RegisterModuleDependences('main', 'OnUserLogin', 'main', 'UpdateTools','CheckUpdates');
		RegisterModuleDependences('main', 'OnModuleUpdate', 'main', 'UpdateTools','SetUpdateResult');
		RegisterModuleDependences('main', 'OnUpdateCheck', 'main', 'UpdateTools','SetUpdateError');
		RegisterModuleDependences('main', 'OnPanelCreate', 'main', 'CUndo', 'CheckNotifyMessage');
		RegisterModuleDependences('main', 'OnAfterAddRating', 	 'main', 'CRatingsComponentsMain', 'OnAfterAddRating');
		RegisterModuleDependences('main', 'OnAfterUpdateRating', 'main', 'CRatingsComponentsMain', 'OnAfterUpdateRating');
		RegisterModuleDependences('main', 'OnSetRatingsConfigs', 'main', 'CRatingsComponentsMain', 'OnSetRatingConfigs');
		RegisterModuleDependences('main', 'OnGetRatingsConfigs', 'main', 'CRatingsComponentsMain', 'OnGetRatingConfigs');
		RegisterModuleDependences('main', 'OnGetRatingsObjects', 'main', 'CRatingsComponentsMain', 'OnGetRatingObject');
		RegisterModuleDependences('main', 'OnGetRatingContentOwner', 'main', 'CRatingsComponentsMain', 'OnGetRatingContentOwner');
		RegisterModuleDependences('main', 'OnAfterAddRatingRule', 	 'main', 'CRatingRulesMain', 'OnAfterAddRatingRule');
		RegisterModuleDependences('main', 'OnAfterUpdateRatingRule', 'main', 'CRatingRulesMain', 'OnAfterUpdateRatingRule');
		RegisterModuleDependences('main', 'OnGetRatingRuleObjects',  'main', 'CRatingRulesMain', 'OnGetRatingRuleObjects');
		RegisterModuleDependences('main', 'OnGetRatingRuleConfigs',  'main', 'CRatingRulesMain', 'OnGetRatingRuleConfigs');
		RegisterModuleDependences('main', 'OnAfterUserAdd', 'main', 'CRatings', 'OnAfterUserRegister');
		RegisterModuleDependences('main', 'OnUserDelete', 'main', 'CRatings', 'OnUserDelete');
		RegisterModuleDependences('main', 'OnUserDelete', 'main', 'CAccess', 'OnUserDelete');
		RegisterModuleDependences('main', 'OnAfterGroupAdd', 'main', 'CGroupAuthProvider', 'OnAfterGroupAdd');
		RegisterModuleDependences('main', 'OnBeforeGroupUpdate', 'main', 'CGroupAuthProvider', 'OnBeforeGroupUpdate');
		RegisterModuleDependences('main', 'OnBeforeGroupDelete', 'main', 'CGroupAuthProvider', 'OnBeforeGroupDelete');
		RegisterModuleDependences('main', 'OnAfterUserUpdate', 'main', 'CGroupAuthProvider', 'OnAfterUserUpdate');
		RegisterModuleDependences('main', 'OnUserLogin', 'main', 'CGroupAuthProvider', 'OnUserLogin');
		RegisterModuleDependences("main", "OnEventLogGetAuditTypes", "main", "CEventMain", "GetAuditTypes");
		RegisterModuleDependences("main", "OnEventLogGetAuditHandlers", "main", "CEventMain", "MakeMainObject");
		RegisterModuleDependences("perfmon", "OnGetTableSchema", "main", "CTableSchema", "OnGetTableSchema");

		RegisterModuleDependences("main", "OnUserTypeBuildList", "main", "CUserTypeString", "GetUserTypeDescription", 110);
		RegisterModuleDependences("main", "OnUserTypeBuildList", "main", "CUserTypeInteger", "GetUserTypeDescription", 120);
		RegisterModuleDependences("main", "OnUserTypeBuildList", "main", "CUserTypeDouble", "GetUserTypeDescription", 130);
		RegisterModuleDependences("main", "OnUserTypeBuildList", "main", "CUserTypeDateTime", "GetUserTypeDescription", 140);
		RegisterModuleDependences("main", "OnUserTypeBuildList", "main", "CUserTypeBoolean", "GetUserTypeDescription", 150);
		RegisterModuleDependences("main", "OnUserTypeBuildList", "main", "CUserTypeFile", "GetUserTypeDescription", 160);
		RegisterModuleDependences("main", "OnUserTypeBuildList", "main", "CUserTypeEnum", "GetUserTypeDescription", 170);
		RegisterModuleDependences("main", "OnUserTypeBuildList", "main", "CUserTypeIBlockSection", "GetUserTypeDescription", 180);
		RegisterModuleDependences("main", "OnUserTypeBuildList", "main", "CUserTypeIBlockElement", "GetUserTypeDescription", 190);
		RegisterModuleDependences("main", "OnUserTypeBuildList", "main", "CUserTypeStringFormatted", "GetUserTypeDescription", 200);

		COption::SetOptionString('main', 'auth_comp2', 'Y');
		COption::SetOptionString("main", "PARAM_MAX_SITES", "2");
		COption::SetOptionString("main", "PARAM_MAX_USERS", "0");
		COption::SetOptionString("main", "distributive6", "Y");
		COption::SetOptionString("main", "~new_license11_sign", "Y");
		COption::SetOptionString("main", "GROUP_DEFAULT_TASK", "1");

		if (LANGUAGE_ID == "ru")
			COption::SetOptionString("main", "vendor", "1c_bitrix");
		else
			COption::SetOptionString("main", "vendor", "bitrix");

		COption::SetOptionString("main", "admin_lid", LANGUAGE_ID);
		COption::SetOptionString("main", "update_site", "www.bitrixsoft.com");
		COption::SetOptionString("main", "update_site_ns", "Y");
		COption::SetOptionString("main", "optimize_css_files", "Y");
		COption::SetOptionString("main", "optimize_js_files", "Y");

		CAgent::AddAgent("CEvent::CleanUpAgent();","main", "Y", 86400);
		CAgent::AddAgent("CUser::CleanUpHitAuthAgent();","main", "Y", 86400);
		CAgent::AddAgent("CCaptchaAgent::DeleteOldCaptcha(3600);","main", "N", 3600);
		CAgent::AddAgent("CUndo::CleanUpOld();", "main", "Y", 86400);
		CAgent::AddAgent("CSiteCheckerTest::CommonTest();", "main", "N", 86400);

		self::InstallDesktop();

		self::InstallSmiles();

		return true;
	}

	function GetModuleTasks()
	{
		return array(
			'' => array(
				"OPERATIONS" => array(
					"edit_php",
				),
			),
			'main_denied' => array(
				"LETTER" => "D",
				"BINDING" => "module",
				"OPERATIONS" => array(
				),
			),
			'main_change_profile' => array(
				"LETTER" => "P",
				"BINDING" => "module",
				"OPERATIONS" => array(
					"view_own_profile",
					"edit_own_profile",
				),
			),
			'main_view_all_settings' => array(
				"LETTER" => "R",
				"BINDING" => "module",
				"OPERATIONS" => array(
					"view_own_profile",
					"view_all_users",
					"view_groups",
					"view_tasks",
					"view_other_settings",
				),
			),
			'main_view_all_settings_change_profile' => array(
				"LETTER" => "T",
				"BINDING" => "module",
				"OPERATIONS" => array(
					"view_own_profile",
					"view_all_users",
					"view_groups",
					"view_tasks",
					"view_other_settings",
					"edit_own_profile",
				),
			),
			'main_edit_subordinate_users' => array(
				"LETTER" => "V",
				"BINDING" => "module",
				"OPERATIONS" => array(
					"view_own_profile",
					"view_subordinate_users",
					"view_groups",
					"view_tasks",
					"view_other_settings",
					"edit_own_profile",
					"edit_subordinate_users",
				),
			),
			'main_full_access' => array(
				"LETTER" => "W",
				"BINDING" => "module",
				"OPERATIONS" => array(
					"view_own_profile",
					"view_all_users",
					"view_groups",
					"view_tasks",
					"view_other_settings",
					"edit_own_profile",
					"edit_all_users",
					"edit_groups",
					"edit_tasks",
					"edit_other_settings",
					"cache_control",
					"lpa_template_edit",
					"view_event_log",
					"edit_ratings",
					"manage_short_uri",
				),
			),
			'fm_folder_access_denied' => array(
				"LETTER" => "D",
				"BINDING" => "file",
				"OPERATIONS" => array(
				),
			),
			'fm_folder_access_read' => array(
				"LETTER" => "R",
				"BINDING" => "file",
				"OPERATIONS" => array(
					"fm_view_permission",
					"fm_view_file",
					"fm_view_listing",
				),
			),
			'fm_folder_access_write' => array(
				"LETTER" => "W",
				"BINDING" => "file",
				"OPERATIONS" => array(
					"fm_view_permission",
					"fm_edit_existent_folder",
					"fm_create_new_file",
					"fm_edit_existent_file",
					"fm_create_new_folder",
					"fm_delete_file",
					"fm_delete_folder",
					"fm_view_file",
					"fm_view_listing",
					"fm_edit_in_workflow",
					"fm_rename_file",
					"fm_rename_folder",
					"fm_upload_file",
					"fm_add_to_menu",
					"fm_download_file",
					"fm_lpa",
				),
			),
			'fm_folder_access_full' => array(
				"LETTER" => "X",
				"BINDING" => "file",
				"OPERATIONS" => array(
					"fm_view_permission",
					"fm_edit_permission",
					"fm_edit_existent_folder",
					"fm_create_new_file",
					"fm_edit_existent_file",
					"fm_create_new_folder",
					"fm_delete_file",
					"fm_delete_folder",
					"fm_view_file",
					"fm_view_listing",
					"fm_edit_in_workflow",
					"fm_rename_file",
					"fm_rename_folder",
					"fm_upload_file",
					"fm_add_to_menu",
					"fm_download_file",
					"fm_lpa",
				),
			),
			'fm_folder_access_workflow' => array(
				"LETTER" => "U",
				"BINDING" => "file",
				"OPERATIONS" => array(
					"fm_view_permission",
					"fm_edit_existent_file",
					"fm_view_file",
					"fm_view_listing",
					"fm_edit_in_workflow",
				),
			),
		);
	}

	private static function InstallRatings()
	{
		global $DB;

		// add  ratings
		$arFields = array(
			'ACTIVE' => 'N',
			'NAME' => GetMessage("MAIN_RATING_NAME"),
			'ENTITY_ID' => 'USER',
			'CALCULATION_METHOD' => 'SUM',
			'~CREATED' 	=> $DB->GetNowFunction(),
			'CALCULATED' => 'N',
			'POSITION' => 'Y',
			'AUTHORITY' => 'N',
			'CONFIGS' => 'a:3:{s:4:"MAIN";a:2:{s:4:"VOTE";a:1:{s:4:"USER";a:2:{s:11:"COEFFICIENT";s:1:"1";s:5:"LIMIT";s:2:"30";}}s:6:"RATING";a:1:{s:5:"BONUS";a:2:{s:6:"ACTIVE";s:1:"Y";s:11:"COEFFICIENT";s:1:"1";}}}s:5:"FORUM";a:2:{s:4:"VOTE";a:2:{s:5:"TOPIC";a:3:{s:6:"ACTIVE";s:1:"Y";s:11:"COEFFICIENT";s:3:"0.5";s:5:"LIMIT";s:2:"30";}s:4:"POST";a:3:{s:6:"ACTIVE";s:1:"Y";s:11:"COEFFICIENT";s:3:"0.1";s:5:"LIMIT";s:2:"30";}}s:6:"RATING";a:1:{s:8:"ACTIVITY";a:9:{s:6:"ACTIVE";s:1:"Y";s:16:"TODAY_TOPIC_COEF";s:3:"0.4";s:15:"WEEK_TOPIC_COEF";s:3:"0.2";s:16:"MONTH_TOPIC_COEF";s:3:"0.1";s:14:"ALL_TOPIC_COEF";s:1:"0";s:15:"TODAY_POST_COEF";s:3:"0.2";s:14:"WEEK_POST_COEF";s:3:"0.1";s:15:"MONTH_POST_COEF";s:4:"0.05";s:13:"ALL_POST_COEF";s:1:"0";}}}s:4:"BLOG";a:2:{s:4:"VOTE";a:2:{s:4:"POST";a:3:{s:6:"ACTIVE";s:1:"Y";s:11:"COEFFICIENT";s:3:"0.5";s:5:"LIMIT";s:2:"30";}s:7:"COMMENT";a:3:{s:6:"ACTIVE";s:1:"Y";s:11:"COEFFICIENT";s:3:"0.1";s:5:"LIMIT";s:2:"30";}}s:6:"RATING";a:1:{s:8:"ACTIVITY";a:9:{s:6:"ACTIVE";s:1:"Y";s:15:"TODAY_POST_COEF";s:3:"0.4";s:14:"WEEK_POST_COEF";s:3:"0.2";s:15:"MONTH_POST_COEF";s:3:"0.1";s:13:"ALL_POST_COEF";s:1:"0";s:18:"TODAY_COMMENT_COEF";s:3:"0.2";s:17:"WEEK_COMMENT_COEF";s:3:"0.1";s:18:"MONTH_COMMENT_COEF";s:4:"0.05";s:16:"ALL_COMMENT_COEF";s:1:"0";}}}}'
		);
		$DB->Add("b_rating", $arFields, array("CONFIGS"));

		// add  authority ratings
		$arFields = array(
			'ACTIVE' => 'N',
			'NAME' => GetMessage("MAIN_RATING_AUTHORITY_NAME"),
			'ENTITY_ID' => 'USER',
			'CALCULATION_METHOD' => 'SUM',
			'~CREATED' => $DB->GetNowFunction(),
			'CALCULATED' => 'N',
			'POSITION' => 'Y',
			'AUTHORITY' => 'Y',
			'CONFIGS' => 'a:3:{s:4:"MAIN";a:2:{s:4:"VOTE";a:1:{s:4:"USER";a:3:{s:6:"ACTIVE";s:1:"Y";s:11:"COEFFICIENT";s:1:"1";s:5:"LIMIT";s:1:"0";}}s:6:"RATING";a:1:{s:5:"BONUS";a:2:{s:6:"ACTIVE";s:1:"Y";s:11:"COEFFICIENT";s:1:"1";}}}s:5:"FORUM";a:2:{s:4:"VOTE";a:2:{s:5:"TOPIC";a:2:{s:11:"COEFFICIENT";s:1:"1";s:5:"LIMIT";s:2:"30";}s:4:"POST";a:2:{s:11:"COEFFICIENT";s:1:"1";s:5:"LIMIT";s:2:"30";}}s:6:"RATING";a:1:{s:8:"ACTIVITY";a:8:{s:16:"TODAY_TOPIC_COEF";s:2:"20";s:15:"WEEK_TOPIC_COEF";s:2:"10";s:16:"MONTH_TOPIC_COEF";s:1:"5";s:14:"ALL_TOPIC_COEF";s:1:"0";s:15:"TODAY_POST_COEF";s:3:"0.4";s:14:"WEEK_POST_COEF";s:3:"0.2";s:15:"MONTH_POST_COEF";s:3:"0.1";s:13:"ALL_POST_COEF";s:1:"0";}}}s:4:"BLOG";a:2:{s:4:"VOTE";a:2:{s:4:"POST";a:2:{s:11:"COEFFICIENT";s:1:"1";s:5:"LIMIT";s:2:"30";}s:7:"COMMENT";a:2:{s:11:"COEFFICIENT";s:1:"1";s:5:"LIMIT";s:2:"30";}}s:6:"RATING";a:1:{s:8:"ACTIVITY";a:8:{s:15:"TODAY_POST_COEF";s:3:"0.4";s:14:"WEEK_POST_COEF";s:3:"0.2";s:15:"MONTH_POST_COEF";s:3:"0.1";s:13:"ALL_POST_COEF";s:1:"0";s:18:"TODAY_COMMENT_COEF";s:3:"0.2";s:17:"WEEK_COMMENT_COEF";s:3:"0.1";s:18:"MONTH_COMMENT_COEF";s:4:"0.05";s:16:"ALL_COMMENT_COEF";s:1:"0";}}}}'
		);
		$ratingId = $DB->Add("b_rating", $arFields, array("CONFIGS"));
		COption::SetOptionString("main", "rating_authority_rating", $ratingId);

		// set default rating vote group config
		$rsGroup = $DB->Query("SELECT * FROM b_group WHERE STRING_ID='RATING_VOTE'", true);
		if ($arGroup = $rsGroup->Fetch())
		{
			$arVoteGroup[] = array(
				'GROUP_ID' => 1,
				'TYPE' => "'R'"
			);
			$arVoteGroup[] = array(
				'GROUP_ID' => $arGroup['ID'],
				'TYPE' => "'R'"
			);
			foreach($arVoteGroup as $arField)
				$DB->Insert("b_rating_vote_group", $arField);

			$arFields = array(
				'ACTIVE' => 'N',
				'NAME' => GetMessage("MAIN_RULE_ADD_GROUP_RATING_NAME"),
				'ENTITY_TYPE_ID' => 'USER',
				'CONDITION_NAME' => 'AUTHORITY',
				'CONDITION_CLASS' => 'CRatingRulesMain',
				'CONDITION_METHOD' => 'ratingCheck',
				'CONDITION_CONFIG' => array(
					'AUTHORITY' => array(
						'RATING_CONDITION' => 1,
						'RATING_VALUE' => 1
					),
				),
				'ACTION_NAME' => 'ADD_TO_GROUP',
				'ACTION_CONFIG' => array(
					'ADD_TO_GROUP' => array(
						'GROUP_ID' => $arGroup['ID']
					),
				),
				'ACTIVATE' => 'N',
				'ACTIVATE_CLASS' => 'CRatingRulesMain',
				'ACTIVATE_METHOD' => 'addToGroup',
				'DEACTIVATE' => 'N',
				'DEACTIVATE_CLASS' => 'CRatingRulesMain ',
				'DEACTIVATE_METHOD' => 'addToGroup',
				"~CREATED" => $DB->GetNowFunction(),
				"~LAST_MODIFIED" => $DB->GetNowFunction(),
			);
			$arFields['CONDITION_CONFIG'] = serialize($arFields['CONDITION_CONFIG']);
			$arFields['ACTION_CONFIG'] = serialize($arFields['ACTION_CONFIG']);
			$DB->Add("b_rating_rule", $arFields, array("ACTION_CONFIG", "CONDITION_CONFIG"));

			$arFields = array(
				'ACTIVE' => 'N',
				'NAME' => GetMessage("MAIN_RULE_REM_GROUP_RATING_NAME"),
				'ENTITY_TYPE_ID' => 'USER',
				'CONDITION_NAME' => 'AUTHORITY',
				'CONDITION_CLASS' => 'CRatingRulesMain',
				'CONDITION_METHOD' => 'ratingCheck',
				'CONDITION_CONFIG' => array(
					'AUTHORITY' => array(
						'RATING_CONDITION' => 2,
						'RATING_VALUE' => 1
					),
				),
				'ACTION_NAME' => 'REMOVE_FROM_GROUP',
				'ACTION_CONFIG' => array(
					'REMOVE_FROM_GROUP' => array(
						'GROUP_ID' => $arGroup['ID']
					),
				),
				'ACTIVATE' => 'N',
				'ACTIVATE_CLASS' => 'CRatingRulesMain',
				'ACTIVATE_METHOD' => 'removeFromGroup',
				'DEACTIVATE' => 'N',
				'DEACTIVATE_CLASS' => 'CRatingRulesMain ',
				'DEACTIVATE_METHOD' => 'removeFromGroup',
				"~CREATED" => $DB->GetNowFunction(),
				"~LAST_MODIFIED" => $DB->GetNowFunction(),
			);
			$arFields['CONDITION_CONFIG'] = serialize($arFields['CONDITION_CONFIG']);
			$arFields['ACTION_CONFIG'] = serialize($arFields['ACTION_CONFIG']);
			$DB->Add("b_rating_rule", $arFields, array("ACTION_CONFIG", "CONDITION_CONFIG"));

			COption::SetOptionString("main", "rating_assign_rating_group_add", 1);
			COption::SetOptionString("main", "rating_assign_rating_group_delete", 1);
			COption::SetOptionString("main", "rating_assign_rating_group", $arGroup['ID']);

		}
		$rsGroup = $DB->Query("SELECT * FROM b_group WHERE STRING_ID='RATING_VOTE_AUTHORITY'", true);
		if ($arGroup = $rsGroup->Fetch())
		{
			$arVoteGroup[] = array(
				'GROUP_ID' => 1,
				'TYPE' => "'A'"
			);
			$arVoteGroup[] = array(
				'GROUP_ID' => $arGroup['ID'],
				'TYPE' => "'A'"
			);
			foreach($arVoteGroup as $arField)
				$DB->Insert("b_rating_vote_group", $arField);

			$arFields = array(
				'ACTIVE' => 'N',
				'NAME' => GetMessage("MAIN_RULE_ADD_GROUP_AUTHORITY_NAME"),
				'ENTITY_TYPE_ID' => 'USER',
				'CONDITION_NAME' => 'AUTHORITY',
				'CONDITION_CLASS' => 'CRatingRulesMain',
				'CONDITION_METHOD' => 'ratingCheck',
				'CONDITION_CONFIG' => array(
					'AUTHORITY' => array(
						'RATING_CONDITION' => 1,
						'RATING_VALUE' => 2
					),
				),
				'ACTION_NAME' => 'ADD_TO_GROUP',
				'ACTION_CONFIG' => array(
					'ADD_TO_GROUP' => array(
						'GROUP_ID' => $arGroup['ID']
					),
				),
				'ACTIVATE' => 'N',
				'ACTIVATE_CLASS' => 'CRatingRulesMain',
				'ACTIVATE_METHOD' => 'addToGroup',
				'DEACTIVATE' => 'N',
				'DEACTIVATE_CLASS' => 'CRatingRulesMain ',
				'DEACTIVATE_METHOD' => 'addToGroup',
				"~CREATED" => $DB->GetNowFunction(),
				"~LAST_MODIFIED" => $DB->GetNowFunction(),
			);
			$arFields['CONDITION_CONFIG'] = serialize($arFields['CONDITION_CONFIG']);
			$arFields['ACTION_CONFIG'] = serialize($arFields['ACTION_CONFIG']);
			$DB->Add("b_rating_rule", $arFields, array("ACTION_CONFIG", "CONDITION_CONFIG"));

			$arFields = array(
				'ACTIVE' => 'N',
				'NAME' => GetMessage("MAIN_RULE_REM_GROUP_AUTHORITY_NAME"),
				'ENTITY_TYPE_ID' => 'USER',
				'CONDITION_NAME' => 'AUTHORITY',
				'CONDITION_CLASS' => 'CRatingRulesMain',
				'CONDITION_METHOD' => 'ratingCheck',
				'CONDITION_CONFIG' => array(
					'AUTHORITY' => array(
						'RATING_CONDITION' => 2,
						'RATING_VALUE' => 2
					),
				),
				'ACTION_NAME' => 'REMOVE_FROM_GROUP',
				'ACTION_CONFIG' => array(
					'REMOVE_FROM_GROUP' => array(
						'GROUP_ID' => $arGroup['ID']
					),
				),
				'ACTIVATE' => 'N',
				'ACTIVATE_CLASS' => 'CRatingRulesMain',
				'ACTIVATE_METHOD' => 'removeFromGroup',
				'DEACTIVATE' => 'N',
				'DEACTIVATE_CLASS' => 'CRatingRulesMain ',
				'DEACTIVATE_METHOD' => 'removeFromGroup',
				"~CREATED" => $DB->GetNowFunction(),
				"~LAST_MODIFIED" => $DB->GetNowFunction(),
			);
			$arFields['CONDITION_CONFIG'] = serialize($arFields['CONDITION_CONFIG']);
			$arFields['ACTION_CONFIG'] = serialize($arFields['ACTION_CONFIG']);
			$DB->Add("b_rating_rule", $arFields, array("ACTION_CONFIG", "CONDITION_CONFIG"));

			COption::SetOptionString("main", "rating_assign_authority_group_add", 2);
			COption::SetOptionString("main", "rating_assign_authority_group_delete", 2);
			COption::SetOptionString("main", "rating_assign_authority_group", $arGroup['ID']);
		}

		// auto authority vote
		$arFields = array(
			'ACTIVE' => 'Y',
			'NAME' => GetMessage("MAIN_RULE_AUTO_AUTHORITY_VOTE_NAME"),
			'ENTITY_TYPE_ID' => 'USER',
			'CONDITION_NAME' => 'VOTE',
			'CONDITION_CLASS' => 'CRatingRulesMain',
			'CONDITION_METHOD' => 'voteCheck',
			'CONDITION_CONFIG' => array(
				'VOTE' => array(
					'VOTE_LIMIT' => 90,
					'VOTE_RESULT' => 10,
					'VOTE_FORUM_TOPIC' => 0.5,
					'VOTE_FORUM_POST' => 0.1,
					'VOTE_BLOG_POST' => 0.5,
					'VOTE_BLOG_COMMENT' => 0.1,
				),
			),
			'ACTION_NAME' => 'empty',
			'ACTION_CONFIG' => array(
			),
			'ACTIVATE' => 'N',
			'ACTIVATE_CLASS' => 'empty',
			'ACTIVATE_METHOD' => 'empty',
			'DEACTIVATE' => 'N',
			'DEACTIVATE_CLASS' => 'empty ',
			'DEACTIVATE_METHOD' => 'empty',
			"~CREATED" => $DB->GetNowFunction(),
			"~LAST_MODIFIED" => $DB->GetNowFunction(),
		);
		$arFields['CONDITION_CONFIG'] = serialize($arFields['CONDITION_CONFIG']);
		$arFields['ACTION_CONFIG'] = serialize($arFields['ACTION_CONFIG']);
		$DB->Add("b_rating_rule", $arFields, array("ACTION_CONFIG", "CONDITION_CONFIG"));

		// rating default config
		COption::SetOptionString("main", "rating_community_size", 1);
		COption::SetOptionString("main", "rating_community_authority", round(1*3*10, 4));
		COption::SetOptionString("main", "rating_vote_weight", 10);
		COption::SetOptionString("main", "rating_normalization_type", "auto");
		COption::SetOptionString("main", "rating_normalization", 10);
		COption::SetOptionString("main", "rating_count_vote", 10);
		COption::SetOptionString("main", "rating_authority_weight_formula", 'Y');
		COption::SetOptionString("main", "rating_community_last_visit", 90);
		COption::SetOptionString("main", "rating_text_like_y", GetMessage("MAIN_RATING_TEXT_LIKE_Y"));
		COption::SetOptionString("main", "rating_text_like_n", GetMessage("MAIN_RATING_TEXT_LIKE_N"));
		COption::SetOptionString("main", "rating_text_like_d", GetMessage("MAIN_RATING_TEXT_LIKE_D"));
		COption::SetOptionString("main", "rating_assign_type", 'auto');
		COption::SetOptionString("main", "rating_vote_type", 'like');
		COption::SetOptionString("main", "rating_self_vote", 'Y');
		COption::SetOptionString("main", "rating_vote_show", 'Y');
		COption::SetOptionString("main", "rating_vote_template", 'like');
		COption::SetOptionString("main", "rating_start_authority", 3);
	}

	private static function InstallDesktop()
	{
		$info_table = '<table class="bx-gadgets-info-site-table" cellspacing="0">';
		$info_table .= '<tr>';
		$info_table .= '	<td class="bx-gadget-gray">'.GetMessage("MAIN_DESKTOP_CREATEDBY_KEY").':</td>';
		$info_table .= '	<td>'.GetMessage("MAIN_DESKTOP_CREATEDBY_VALUE").'</td>';
		$info_table .= '	<td class="bx-gadgets-info-site-logo" rowspan="5"><img src="'.'/bitrix/components/bitrix/desktop/templates/admin/images/site_logo.png'.'"></td>';
		$info_table .= '</tr>';
		$info_table .= '<tr>';
		$info_table .= '	<td class="bx-gadget-gray">'.GetMessage("MAIN_DESKTOP_URL_KEY").':</td>';
		$info_table .= '	<td>'.GetMessage("MAIN_DESKTOP_URL_VALUE").'</td>';
		$info_table .= '</tr>';
		$info_table .= '<tr>';
		$info_table .= '	<td class="bx-gadget-gray">'.GetMessage("MAIN_DESKTOP_PRODUCTION_KEY").':</td>';
		$info_table .= '	<td>'.GetMessage("MAIN_DESKTOP_PRODUCTION_VALUE").'</td>';
		$info_table .= '</tr>';
		$info_table .= '<tr>';
		$info_table .= '	<td class="bx-gadget-gray">'.GetMessage("MAIN_DESKTOP_RESPONSIBLE_KEY").':</td>';
		$info_table .= '	<td>'.GetMessage("MAIN_DESKTOP_RESPONSIBLE_VALUE").'</td>';
		$info_table .= '</tr>';
		$info_table .= '<tr>';
		$info_table .= '	<td class="bx-gadget-gray">'.GetMessage("MAIN_DESKTOP_EMAIL_KEY").':</td>';
		$info_table .= '	<td>'.GetMessage("MAIN_DESKTOP_EMAIL_VALUE").'</td>';
		$info_table .= '</tr>';
		$info_table .= '</table>';

		switch (LANGUAGE_ID)
		{
			case "ru":
				$rss_url = "https://www.1c-bitrix.ru/about/life/news/rss/";
				break;
			case "de":
				$rss_url = "http://www.bitrix.de/company/news/rss/";
				break;
			default:
				$rss_url = "http://www.bitrixsoft.com/company/news/rss/";
		}

		if (
			file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/install/index.php")
			&& !file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/install/index.php")
		)
		{
			$arOptions = array(
				array(
					"GADGETS" => array(
						"ADMIN_ORDERS_GRAPH@111111111" => array(
							"COLUMN" => 0,
							"ROW" => 0,
							"HIDE" => "N"
						),
						"ADMIN_ORDERS@111111111" => array(
							"COLUMN" => 0,
							"ROW" => 1,
							"HIDE" => "N"
						),
						"ADMIN_STAT@222222222" => array(
							"COLUMN" => 0,
							"ROW" => 3,
							"HIDE" => "N"
						),
						"HTML_AREA@444444444" => array(
							"COLUMN" => 1,
							"ROW" => 0,
							"HIDE" => "N",
							"USERDATA" => array(
								"content" => $info_table
							),
							"SETTINGS" => array(
								"TITLE_STD" => GetMessage("MAIN_DESKTOP_INFO_TITLE")
							)
						),
						"ADMIN_SECURITY@555555555" => array(
							"COLUMN" => 1,
							"ROW" => 1,
							"HIDE" => "N"
						),
						"ADMIN_PERFMON@666666666" => array(
							"COLUMN" => 1,
							"ROW" => 2,
							"HIDE" => "N"
						),
						"ADMIN_PRODUCTS@111111111" => array(
							"COLUMN" => 1,
							"ROW" => 5,
							"HIDE" => "N"
						),
						"ADMIN_INFO@333333333" => array(
							"COLUMN" => 1,
							"ROW" => 6,
							"HIDE" => "N"
						),
						"ADMIN_CHECKLIST@777888999" => array(
							"COLUMN" => 1,
							"ROW" => 7,
							"HIDE" => "N",
						),
						"RSSREADER@777777777" => array(
							"COLUMN" => 1,
							"ROW" => 8,
							"HIDE" => "N",
							"SETTINGS" => array(
								"TITLE_STD" => GetMessage("MAIN_DESKTOP_RSS_TITLE"),
								"CNT" => 10,
								"RSS_URL" => $rss_url
							)
						),
					)
				)
			);
			if (LANGUAGE_ID == "ru")
			{
				$arOptions[0]["GADGETS"]["ADMIN_MARKETPALCE@22549"] = Array(
					"COLUMN" => 1,
					"ROW" => 3,
					"HIDE" => "N",
				);
				$arOptions[0]["GADGETS"]["ADMIN_MOBILESHOP@13391"] = Array(
					"COLUMN" => 1,
					"ROW" => 4,
					"HIDE" => "N"
				);
			}
		}
		elseif (!file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/install/index.php"))
		{
			$arOptions = array(
				array(
					"GADGETS" => array(
						"ADMIN_ORDERS_GRAPH@111111111" => array(
							"COLUMN" => 0,
							"ROW" => 0,
							"HIDE" => "N"
						),
						"ADMIN_ORDERS@111111111" => array(
							"COLUMN" => 0,
							"ROW" => 1,
							"HIDE" => "N"
						),
						"HTML_AREA@444444444" => array(
							"COLUMN" => 1,
							"ROW" => 0,
							"HIDE" => "N",
							"USERDATA" => array(
								"content" => $info_table
							),
							"SETTINGS" => array(
								"TITLE_STD" => GetMessage("MAIN_DESKTOP_INFO_TITLE")
							)
						),
						"ADMIN_SECURITY@555555555" => array(
							"COLUMN" => 1,
							"ROW" => 1,
							"HIDE" => "N"
						),
						"ADMIN_PERFMON@666666666" => array(
							"COLUMN" => 1,
							"ROW" => 2,
							"HIDE" => "N"
						),
						"ADMIN_PRODUCTS@111111111" => array(
							"COLUMN" => 1,
							"ROW" => 5,
							"HIDE" => "N"
						),
						"ADMIN_INFO@333333333" => array(
							"COLUMN" => 1,
							"ROW" => 6,
							"HIDE" => "N"
						),
						"ADMIN_CHECKLIST@777888999" => array(
							"COLUMN" => 1,
							"ROW" => 7,
							"HIDE" => "N",
						),
						"RSSREADER@777777777" => array(
							"COLUMN" => 1,
							"ROW" => 8,
							"HIDE" => "N",
							"SETTINGS" => array(
								"TITLE_STD" => GetMessage("MAIN_DESKTOP_RSS_TITLE"),
								"CNT" => 10,
								"RSS_URL" => $rss_url
							)
						),
					)
				)
			);
			if (LANGUAGE_ID == "ru")
			{
				$arOptions[0]["GADGETS"]["ADMIN_MARKETPALCE@22549"] = Array(
					"COLUMN" => 1,
					"ROW" => 3,
					"HIDE" => "N",
				);
				$arOptions[0]["GADGETS"]["ADMIN_MOBILESHOP@13391"] = Array(
					"COLUMN" => 1,
					"ROW" => 4,
					"HIDE" => "N"
				);
			}
		}
		elseif (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/install/index.php"))
		{
			$arOptions = array(
				array(
					"GADGETS" => array(
						"ADMIN_SECURITY@555555555" => array(
							"COLUMN" => 0,
							"ROW" => 0,
							"HIDE" => "N"
						),
						"ADMIN_PERFMON@666666666" => array(
							"COLUMN" => 0,
							"ROW" => 1,
							"HIDE" => "N"
						),
						"ADMIN_STAT@222222222" => array(
							"COLUMN" => 0,
							"ROW" => 2,
							"HIDE" => "N"
						),
						"HTML_AREA@444444444" => array(
							"COLUMN" => 1,
							"ROW" => 0,
							"HIDE" => "N",
							"USERDATA" => array(
								"content" => $info_table
							),
							"SETTINGS" => array(
								"TITLE_STD" => GetMessage("MAIN_DESKTOP_INFO_TITLE")
							)
						),
						"ADMIN_INFO@333333333" => array(
							"COLUMN" => 1,
							"ROW" => 1,
							"HIDE" => "N"
						),
						"RSSREADER@777777777" => array(
							"COLUMN" => 1,
							"ROW" => 3,
							"HIDE" => "N",
							"SETTINGS" => array(
								"TITLE_STD" => GetMessage("MAIN_DESKTOP_RSS_TITLE"),
								"CNT" => 5,
								"RSS_URL" => $rss_url
							)
						),
						"ADMIN_CHECKLIST@777888999" => array(
							"COLUMN" => 1,
							"ROW" => 2,
							"HIDE" => "N",
						)
					)
				)
			);
		}
		else
		{
			$arOptions = array(
				array(
					"GADGETS" => array(
						"ADMIN_INFO@333333333" => array(
							"COLUMN" => 0,
							"ROW" => 0,
							"HIDE" => "N"
						),
						"ADMIN_SECURITY@555555555" => array(
							"COLUMN" => 0,
							"ROW" => 1,
							"HIDE" => "N"
						),
						"ADMIN_PERFMON@666666666" => array(
							"COLUMN" => 0,
							"ROW" => 2,
							"HIDE" => "N"
						),
						"HTML_AREA@444444444" => array(
							"COLUMN" => 1,
							"ROW" => 0,
							"HIDE" => "N",
							"USERDATA" => array(
								"content" => $info_table
							),
							"SETTINGS" => array(
								"TITLE_STD" => GetMessage("MAIN_DESKTOP_INFO_TITLE")
							)
						),
						"RSSREADER@777777777" => array(
							"COLUMN" => 1,
							"ROW" => 2,
							"HIDE" => "N",
							"SETTINGS" => array(
								"TITLE_STD" => GetMessage("MAIN_DESKTOP_RSS_TITLE"),
								"CNT" => 5,
								"RSS_URL" => $rss_url
							)
						),
						"ADMIN_CHECKLIST@777888999" => array(
							"COLUMN" => 1,
							"ROW" => 1,
							"HIDE" => "N",
						)
					)
				)
			);
		}

		CUserOptions::SetOption('intranet', "~gadgets_admin_index", $arOptions, true);
	}

	private static function InstallSmiles()
	{
		/** @global string $DBType */
		global $DBType;

		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/virtual_io.php");
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/virtual_file.php");
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/file.php");
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/".$DBType."/file.php");
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/archive.php");
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/csv_data.php");
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/file_temp.php");
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/smile.php");

		$result = CSmileSet::getByStringId('main');
		if (!empty($result))
			return;

		$arLang = Array();
		$langs = CLanguage::GetList($b="", $o="");
		while($language = $langs->Fetch())
		{
			$lid = $language["LID"];
			$MESS = IncludeModuleLangFile(__FILE__, $lid, true);
			if ($MESS && isset($MESS['MAIN_SMILE_DEF_SET_NAME']))
				$arLang[$lid] = $MESS['MAIN_SMILE_DEF_SET_NAME'];
		}
		$smileSetId = CSmileSet::add(Array(
			'STRING_ID' => 'main',
			'LANG' => $arLang
		));
		if (intval($smileSetId) > 0)
		{
			CSmile::import(array(
				'FILE' => $_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/main/install/smiles/smiles_default.zip',
				'SET_ID' => intval($smileSetId),
				'IMPORT_IF_FILE_EXISTS' => 'Y'
			));
		}
	}

	function UnInstallDB()
	{
		return true;
	}

	function InstallEvents()
	{
		$arEventTypes = array();
		$langs = CLanguage::GetList($b="", $o="");
		while($language = $langs->Fetch())
		{
			$lid = $language["LID"];
			IncludeModuleLangFile(__FILE__, $lid);

			$arEventTypes[] = array(
				"LID" => $lid,
				"EVENT_NAME" => "NEW_USER",
				"NAME" => GetMessage("MAIN_NEW_USER_TYPE_NAME"),
				"DESCRIPTION" => GetMessage("MAIN_NEW_USER_TYPE_DESC"),
				"SORT" => 1
			);
			$arEventTypes[] = array(
				"LID" => $lid,
				"EVENT_NAME" => "USER_INFO",
				"NAME" => GetMessage("MAIN_USER_INFO_TYPE_NAME"),
				"DESCRIPTION" => GetMessage("MAIN_USER_INFO_TYPE_DESC"),
				"SORT" => 2
			);
			$arEventTypes[] = array(
				"LID" => $lid,
				"EVENT_NAME" => "NEW_USER_CONFIRM",
				"NAME" => GetMessage("MAIN_NEW_USER_CONFIRM_TYPE_NAME"),
				"DESCRIPTION" => GetMessage("MAIN_NEW_USER_CONFIRM_TYPE_DESC"),
				"SORT" => 3
			);
			$arEventTypes[] = array(
				"LID" => $lid,
				"EVENT_NAME" => "USER_PASS_REQUEST",
				"NAME" => GetMessage("MAIN_USER_PASS_REQUEST_TYPE_NAME"),
				"DESCRIPTION" => GetMessage("MAIN_USER_INFO_TYPE_DESC"),
				"SORT" => 4
			);
			$arEventTypes[] = array(
				"LID" => $lid,
				"EVENT_NAME" => "USER_PASS_CHANGED",
				"NAME" => GetMessage("MAIN_USER_PASS_CHANGED_TYPE_NAME"),
				"DESCRIPTION" => GetMessage("MAIN_USER_INFO_TYPE_DESC"),
				"SORT" => 5
			);
			$arEventTypes[] = array(
				"LID" => $lid,
				"EVENT_NAME" => "USER_INVITE",
				"NAME" => GetMessage("MAIN_USER_INVITE_TYPE_NAME"),
				"DESCRIPTION" => GetMessage("MAIN_USER_INVITE_TYPE_DESC"),
				"SORT" => 6
			);
			$arEventTypes[] = array(
				"LID" => $lid,
				"EVENT_NAME" => "FEEDBACK_FORM",
				"NAME" => GetMessage("MF_EVENT_NAME"),
				"DESCRIPTION" => GetMessage("MF_EVENT_DESCRIPTION"),
				"SORT" => 7
			);
		}

		$type = new CEventType;
		foreach ($arEventTypes as $arEventType)
			$type->Add($arEventType);

		IncludeModuleLangFile(__FILE__);

		$arMessages = array();
		$arMessages[] = array(
			"EVENT_NAME" => "NEW_USER",
			"LID" => "s1",
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#DEFAULT_EMAIL_FROM#",
			"SUBJECT" => GetMessage("MAIN_NEW_USER_EVENT_NAME"),
			"MESSAGE" => GetMessage("MAIN_NEW_USER_EVENT_DESC")
		);
		$arMessages[] = array(
			"EVENT_NAME" => "USER_INFO",
			"LID" => "s1",
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL#",
			"SUBJECT" => GetMessage("MAIN_USER_INFO_EVENT_NAME"),
			"MESSAGE" => GetMessage("MAIN_USER_INFO_EVENT_DESC")
		);
		$arMessages[] = array(
			"EVENT_NAME" => "USER_PASS_REQUEST",
			"LID" => "s1",
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL#",
			"SUBJECT" => GetMessage("MAIN_USER_PASS_REQUEST_EVENT_NAME"),
			"MESSAGE" => GetMessage("MAIN_USER_PASS_REQUEST_EVENT_DESC")
		);
		$arMessages[] = array(
			"EVENT_NAME" => "USER_PASS_CHANGED",
			"LID" => "s1",
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL#",
			"SUBJECT" => GetMessage("MAIN_USER_PASS_CHANGED_EVENT_NAME"),
			"MESSAGE" => GetMessage("MAIN_USER_PASS_CHANGED_EVENT_DESC")
		);
		$arMessages[] = array(
			"EVENT_NAME" => "NEW_USER_CONFIRM",
			"LID" => "s1",
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL#",
			"SUBJECT" => GetMessage("MAIN_NEW_USER_CONFIRM_EVENT_NAME"),
			"MESSAGE" => GetMessage("MAIN_NEW_USER_CONFIRM_EVENT_DESC")
		);
		$arMessages[] = array(
			"EVENT_NAME" => "USER_INVITE",
			"LID" => "s1",
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL#",
			"SUBJECT" => GetMessage("MAIN_USER_INVITE_EVENT_NAME"),
			"MESSAGE" => GetMessage("MAIN_USER_INVITE_EVENT_DESC")
		);
		$arMessages[] = array(
			"EVENT_NAME" => "FEEDBACK_FORM",
			"LID" => "s1",
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"SUBJECT" => GetMessage("MF_EVENT_SUBJECT"),
			"MESSAGE" => GetMessage("MF_EVENT_MESSAGE")
		);

		$message = new CEventMessage;
		foreach ($arMessages as $arMessage)
			$message->Add($arMessage);

		return true;
	}

	function UnInstallEvents()
	{
		return true;
	}

	function InstallFiles()
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/bitrix", $_SERVER["DOCUMENT_ROOT"]."/bitrix", true, true);
		CopyDirFiles($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/install/admin", $_SERVER['DOCUMENT_ROOT']."/bitrix/admin");
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/tools", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/images", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/themes", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/sounds", $_SERVER["DOCUMENT_ROOT"]."/bitrix/sounds", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/components/bitrix", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/gadgets/bitrix", $_SERVER["DOCUMENT_ROOT"]."/bitrix/gadgets/bitrix", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/image_uploader", $_SERVER["DOCUMENT_ROOT"]."/bitrix/image_uploader", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/panel", $_SERVER["DOCUMENT_ROOT"]."/bitrix/panel", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/fonts", $_SERVER["DOCUMENT_ROOT"]."/bitrix/fonts", true, true);

		return true;
	}

	function UnInstallFiles()
	{
		return true;
	}

	function DoInstall()
	{
	}

	function DoUninstall()
	{
	}
}
