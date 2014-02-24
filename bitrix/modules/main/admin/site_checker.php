<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

/**
 * Bitrix vars
 *
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

@ini_set("track_errors", "1");
@ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
$message = null;

define('DEBUG_FLAG', str_replace('\\','/',$_SERVER['DOCUMENT_ROOT'] . '/bitrix/site_checker_debug'));
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/classes/general/site_checker.php');

// NO AUTH TESTS
if ($_REQUEST['unique_id'])
{
	if (!file_exists(DEBUG_FLAG) && $_REQUEST['unique_id'] != checker_get_unique_id())
		die('<h1>Permission denied: UNIQUE ID ERROR</h1>');

	switch ($_GET['test_type'])
	{
		case 'socket_test':
			echo "SUCCESS";
		break;
		case 'perf':
			define("NOT_CHECK_PERMISSIONS", true);
			require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
			require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
			echo $main_exec_time;
		break;
		case 'fast_download':
			header('X-Accel-Redirect: /bitrix/tmp/success.txt');
		break;
		case 'dbconn_test':
			ob_start();
			define('NOT_CHECK_PERMISSIONS', true);
			require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
			$buff = '';
			while(ob_get_level())
			{
				$buff .= ob_get_contents();
				ob_end_clean();
			}
			ob_end_clean();
			if (function_exists('mb_internal_encoding'))
				mb_internal_encoding('ISO-8859-1');
			echo $buff === '' ? 'SUCCESS' : 'Length: '.strlen($buff).' ('.$buff . ')';
		break;
		case 'pcre_recursion_test':
			$a = str_repeat('a',10000);
			if (preg_match('/(a)+/',$a)) // Segmentation fault (core dumped)
				echo 'SUCCESS';
			else
				echo 'CLEAN';
		break;
		case 'method_exists':
			$arRes= Array
			(
				"CLASS" => "",
				"CALC_METHOD" => ""
			);
			method_exists($arRes['CLASS'], $arRes['CALC_METHOD']);
			echo 'SUCCESS';
		break;
		case 'upload_test':
			if (function_exists('mb_internal_encoding'))
				mb_internal_encoding('ISO-8859-1');

			$dir = $_SERVER['DOCUMENT_ROOT'].'/bitrix/tmp';
			if (!file_exists($dir))
				mkdir($dir);

			$binaryData = '';
			for($i=40;$i<240;$i++)
				$binaryData .= chr($i);
			if ($_REQUEST['big'])
				$binaryData = str_repeat($binaryData, 21000);

			if ($_REQUEST['raw'])
				$binaryData_received = file_get_contents('php://input');
			elseif (move_uploaded_file($tmp_name = $_FILES['test_file']['tmp_name'], $image = $dir.'/site_checker.bin'))
			{
				$binaryData_received = file_get_contents($image);
				unlink($image);
			}
			else
			{
				echo 'move_uploaded_file('.$tmp_name.','.$image.')=false'."\n";
				echo '$_FILES='."\n";
				print_r($_FILES);
				die();
			}

			if ($binaryData === $binaryData_received)
				echo "SUCCESS";
			else
				echo 'strlen($binaryData)='.strlen($binaryData).', strlen($binaryData_received)='.strlen($binaryData_received);
		break;
		case 'post_test':
			$ok = true;
			for ($i=0;$i<201;$i++)
				$ok = $ok && ($_POST['i'.$i] == md5($i));

			echo $ok ? 'SUCCESS' : 'FAIL';
			break;
		case 'memory_test':
			@ini_set("memory_limit", "512M");
			$max = intval($_GET['max']);
			if ($max)
			{
				for($i=1;$i<=$max;$i++)
					$a[] = str_repeat(chr($i),1024*1024); // 1 Mb

				echo "SUCCESS";
			}
		break;
		case 'auth_test':
			$remote_user = $_SERVER["REMOTE_USER"] ? $_SERVER["REMOTE_USER"] : $_SERVER["REDIRECT_REMOTE_USER"];
			$strTmp = base64_decode(substr($remote_user,6));
			if ($strTmp)
				list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', $strTmp);
			if ($_SERVER['PHP_AUTH_USER']=='test_user' && $_SERVER['PHP_AUTH_PW']=='test_password')
				echo('SUCCESS');
		break;
		case 'session_test':
			session_start();
			echo $_SESSION['CHECKER_CHECK_SESSION'];
			$_SESSION['CHECKER_CHECK_SESSION'] = 'SUCCESS';
		break;
		case 'redirect_test':
			foreach(array('SERVER_PORT','HTTPS','FCGI_ROLE','SERVER_PROTOCOL','SERVER_PORT','HTTP_HOST') as $key)
				$GLOBALS['_SERVER'][$key] = $GLOBALS['_REQUEST'][$key];
			function IsHTTPS()
			{
				return ($_SERVER["SERVER_PORT"]==443 || strtolower($_SERVER["HTTPS"])=="on");
			}

			function SetStatus($status)
			{
				$bCgi = (stristr(php_sapi_name(), "cgi") !== false);
				$bFastCgi = ($bCgi && (array_key_exists('FCGI_ROLE', $_SERVER) || array_key_exists('FCGI_ROLE', $_ENV)));
				if($bCgi && !$bFastCgi)
					header("Status: ".$status);
				else
					header($_SERVER["SERVER_PROTOCOL"]." ".$status);
			}

			if ($_REQUEST['done'])
				echo 'SUCCESS';
			else
			{
				SetStatus("302 Found");
				$protocol = (IsHTTPS() ? "https" : "http");
				$host = $_SERVER['HTTP_HOST'];
				if($_SERVER['SERVER_PORT'] <> 80 && $_SERVER['SERVER_PORT'] <> 443 && $_SERVER['SERVER_PORT'] > 0 && strpos($_SERVER['HTTP_HOST'], ":") === false)
					$host .= ":".$_SERVER['SERVER_PORT'];
				$url = "?redirect_test=Y&done=Y&unique_id=".checker_get_unique_id();
				header("Request-URI: ".$protocol."://".$host.$url);
				header("Content-Location: ".$protocol."://".$host.$url);
				header("Location: ".$protocol."://".$host.$url);
				exit;
			}
		break;
		default:
		break;
	}

	if ($fix_mode = intval($_GET['fix_mode']))
	{
		if ($_REQUEST['charset'])
		{
			define('LANG_CHARSET', $_REQUEST['charset']);
			header('Content-type: text/plain; charset='.LANG_CHARSET);
		}
		define('LANGUAGE_ID', preg_match('#[a-z]{2}#',$_REQUEST['lang'],$regs) ? $regs[0] : 'en');
		include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/lang/'.LANGUAGE_ID.'/admin/site_checker.php');
		InitPureDB();
		if (!function_exists('AddMessage2Log'))
		{
			function AddMessage2Log($sText, $sModule = "", $traceDepth = 6, $bShowArgs = false)
			{
				echo $sText;
			}
		}

		if (!function_exists('htmlspecialcharsbx'))
		{
			function htmlspecialcharsbx($string, $flags=ENT_COMPAT)
			{
				//shitty function for php 5.4 where default encoding is UTF-8
				return htmlspecialchars($string, $flags, (defined("BX_UTF")? "UTF-8" : "ISO-8859-1"));
			}
		}

		if (!function_exists('GetMessage'))
		{
			function GetMessage($code, $arReplace = array())
			{
				global $MESS;
				$strResult = $MESS[$code];
				foreach($arReplace as $k => $v)
					$strResult = str_replace($k, $v, $strResult);
				return $strResult;
			}
		}

		if (!function_exists('JSEscape'))
		{
			function JSEscape($s)
			{
				static $aSearch = array("\xe2\x80\xa9", "\\", "'", "\"", "\r\n", "\r", "\n", "\xe2\x80\xa8");
				static $aReplace = array(" ", "\\\\", "\\'", '\\"', "\n", "\n", "\\n'+\n'", "\\n'+\n'");
				$val =  str_replace($aSearch, $aReplace, $s);
				return preg_replace("'</script'i", "</s'+'cript", $val);
			}
		}

		$oTest = new CSiteCheckerTest($_REQUEST['step'], 0, $fix_mode);
		if (file_exists(DEBUG_FLAG))
			$oTest->timeout = 30;

		if ($_REQUEST['global_test_vars'] && ($d = base64_decode($_REQUEST['global_test_vars'])))
			$oTest->arTestVars = unserialize($d);
		else
			$oTest->arTestVars = array();

		$oTest->Start();
		if ($oTest->percent < 100)
		{
			$strNextRequest = '&step='.$oTest->step.'&global_test_vars='.base64_encode(serialize($oTest->arTestVars));
			$strFinalStatus = '';
		}
		else
		{
			$strNextRequest = '';
			$strFinalStatus = '100%';
		}
		// fix mode
		echo '
			iPercent = '.$oTest->percent.';
			test_percent = '.$oTest->test_percent.';
			strCurrentTestFunc = "'.$oTest->last_function.'";
			strCurrentTestName = "'.JSEscape($oTest->strCurrentTestName).'";
			strNextTestName = "'.JSEscape($oTest->strNextTestName).'";
			strNextRequest = "'.JSEscape($strNextRequest).'";
			strResult = "'.JSEscape(str_replace(array("\r","\n"),"",$oTest->strResult)).'";
			strFinalStatus = "'.JSEscape($strFinalStatus).'";
		';
	}
	die();
}
// END NO AUTH TESTS

if (file_exists(DEBUG_FLAG))
{
	define('NOT_CHECK_PERMISSIONS', true);
	define("BX_COMPRESSION_DISABLED", true);
}

if($_REQUEST['test_start'])
{
	define("NO_KEEP_STATISTIC", true);
	define("NO_AGENT_CHECK", true);
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
IncludeModuleLangFile(__FILE__);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/prolog.php");
define("HELP_FILE", "utilities/site_checker.php");
//error_reporting(E_ALL &~E_NOTICE);

////////////////////////////////////////////////////////////////////////
//////////   PARAMS   //////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////
define("SUPPORT_PAGE", (LANGUAGE_ID == 'ru' ? 'http://www.1c-bitrix.ru/support/' : 'http://www.bitrixsoft.com/support/'));

$Apache_vercheck_min = "1.3.0";
$Apache_vercheck_max = "";

$IIS_vercheck_min = "5.0.0";
$IIS_vercheck_max = "";

////////////////////////////////////////////////////////////////////////
//////////   END PARAMS   //////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////
if ($USER->CanDoOperation('view_other_settings'))
{
	if (file_exists(DEBUG_FLAG))
		if (!unlink(DEBUG_FLAG))
			CAdminMessage::ShowMessage(Array("TYPE"=>"ERROR", "MESSAGE"=>'Can\'t delete ' . DEBUG_FLAG));
}
elseif(!defined('NOT_CHECK_PERMISSIONS') || NOT_CHECK_PERMISSIONS !== true)
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

if ($_POST['access_check'])
{
	if (defined('NOT_CHECK_PERMISSIONS') && NOT_CHECK_PERMISSIONS ===true || check_bitrix_sessid())
	{
		$ob = new CSearchFiles;
		$ob->TimeLimit = 10;

		if ($_REQUEST['break_point'])
			$ob->SkipPath = $_REQUEST['break_point'];

		$check_type = $_REQUEST['check_type'];

		$sNextPath = '';
		if ($check_type == 'upload')
		{
			if (!file_exists($tmp = $_SERVER['DOCUMENT_ROOT'].BX_PERSONAL_ROOT.'/tmp'))
				mkdir($tmp);
			$upload = $_SERVER['DOCUMENT_ROOT'].'/'.COption::GetOptionString('main', 'upload_dir', 'upload');

			if (0===strpos($_REQUEST['break_point'], $upload))
				$path = $upload;
			else
			{
				$path = $tmp;
				$sNextPath = $upload;
			}
		}
		elseif($check_type == 'kernel')
			$path = $_SERVER['DOCUMENT_ROOT'].'/bitrix';
		elseif($check_type == 'personal')
			$path = $_SERVER['DOCUMENT_ROOT'].BX_PERSONAL_ROOT;
		else
		{
			$path = $_SERVER['DOCUMENT_ROOT'];
			$check_type = 'full';
		}

		if ($ob->Search($path))
		{
			if ($ob->BreakPoint || $sNextPath)
			{
				if ($ob->BreakPoint)
					$sNextPath = $ob->BreakPoint;
				$cnt_total = intval($_REQUEST['cnt_total']) + $ob->FilesCount;
				?><form method=post id=postform>
					<input type=hidden name=access_check value="Y">
					<input type=hidden name=lang value="<?=LANGUAGE_ID?>">
					<?=bitrix_sessid_post();?>
					<input type=hidden name=cnt_total value="<?=$cnt_total?>">
					<input type=hidden name=check_type value="<?=$check_type?>">
					<input type=hidden name=break_point value="<?=htmlspecialcharsbx($sNextPath)?>">
				</form>
				<?
				CAdminMessage::ShowMessage(array(
					'TYPE' => 'OK',
					'HTML' => true,
					'MESSAGE' => GetMessage('SC_TESTING'),
					'DETAILS' => str_replace(array('#NUM#','#PATH#'),array($cnt_total,$sNextPath),GetMessage('SC_FILES_CHECKED')),
					)
				);
				?>
				<script>
				if (parent.document.getElementById('access_submit').disabled)
					window.setTimeout("parent.ShowWaitWindow();document.getElementById('postform').submit()",500);
				</script><?
			}
			else
			{
				if ($check_type == 'full')
					COption::SetOptionString('main', 'site_checker_access', 'Y');
				CAdminMessage::ShowMessage(Array("TYPE"=>"OK", "MESSAGE"=>GetMessage("SC_FILES_OK")));
				?><script>parent.access_check_start(0);</script><?
			}
		}
		else
		{
			COption::SetOptionString('main', 'site_checker_access', 'N');
			CAdminMessage::ShowMessage(array(
				'TYPE' => 'ERROR',
				'MESSAGE' => GetMessage("SC_FILES_FAIL"),
				'DETAILS' => implode("<br>",$ob->arFail),
				'HTML' => true
				)
			);
			?><script>parent.access_check_start(0);</script><?
		}
	}
	else
		echo '<h1>Permission denied: BITRIX SESSID ERROR</h1>';
	exit;
}
elseif($_REQUEST['test_start'])
{
	if (defined('NOT_CHECK_PERMISSIONS') && NOT_CHECK_PERMISSIONS ===true || check_bitrix_sessid())
	{
		$oTest = new CSiteCheckerTest($_REQUEST['step'], (int) $_REQUEST['fast']);
		if ($_REQUEST['global_test_vars'] && ($d = base64_decode($_REQUEST['global_test_vars'])))
		{
			if (!CheckSerializedData($d))
				die('Error unserialize');
			$oTest->arTestVars = unserialize($d);
		}

		$oTest->Start();
		if ($oTest->percent < 100)
		{
			$strNextRequest = '&step='.$oTest->step.'&global_test_vars='.base64_encode(serialize($oTest->arTestVars));
			$strFinalStatus = '';
		}
		else
		{
			$strNextRequest = '';
			$strFinalStatus = '100%';
		}
		// test mode
		echo '
			iPercent = '.$oTest->percent.';
			test_percent = '.$oTest->test_percent.';
			strCurrentTestFunc = "'.$oTest->last_function.'";
			strCurrentTestName = "'.CUtil::JSEscape($oTest->strCurrentTestName).'";
			strNextTestName = "'.CUtil::JSEscape($oTest->strNextTestName).'";
			strNextRequest = "'.CUtil::JSEscape($strNextRequest).'";
			strResult = "'.CUtil::JSEscape(str_replace(array("\r","\n"),"",$oTest->strResult)).'";
			strFinalStatus = "'.CUtil::JSEscape($strFinalStatus).'";
			strGroupName = "'.CUtil::JSEscape($oTest->group_name).'";
			strGroupDesc = "'.CUtil::JSEscape($oTest->group_desc).'";
			test_result = '.($oTest->result === true ? 1 : ($oTest->result === false ? -1 : 0)).'; // 0 = note
		';
	}
	else
		echo '<h1>Permission denied: BITRIX SESSID ERROR</h1>';
	exit;
}
elseif ($_REQUEST['read_log']) // after prolog to send correct charset
{
	header('Content-type: text/plain; charset='.LANG_CHARSET);
	$oTest = new CSiteCheckerTest();
	echo file_get_contents($_SERVER['DOCUMENT_ROOT'].$oTest->LogFile);
	exit;
}
elseif ($_REQUEST['help_id'])
{
	echo '<div style="font-size:1.2em;padding:20px">';
	if ($h = GetMessage('SC_HELP_' . strtoupper($_REQUEST['help_id'])))
	{
		$h = str_replace('<code>','<div style="border:1px solid #CCC;margin:10px;padding:10px;font-family:monospace;background-color:#FEFEFA">',$h);
		$h = str_replace('</code>','</div>',$h);
		echo nl2br($h);
		echo '<br><br>'.GetMessage('SC_READ_MORE');
	}
	else
		echo GetMessage('SC_HELP_NOTOPIC');
	echo '</div>';
	exit;
}
elseif ($fix_mode = intval($_REQUEST['fix_mode']))
{
	?>
	<table id="fix_table" width="100%" class="internal" style="padding:20px;padding-bottom:0;">
		<tr class="heading">
			<td class="align-left" colspan="2"><?=GetMessage('SC_GR_FIX')?></td>
		</tr>
	</table>
	<div id="fix_status"></div>
	<script>
		var fix_mode = <?=$fix_mode?>;
		BX.ajax.get('site_checker.php?fix_mode=' + fix_mode + '&test_start=Y&lang=<?=LANGUAGE_ID?>&charset=<?=LANG_CHARSET?>&<?=bitrix_sessid_get()?>&unique_id=<?=checker_get_unique_id()?>', fix_onload);
	</script>
	<?
	exit;
}

$bIntranet = IsModuleInstalled('intranet');
$aTabs = array();
if ($bIntranet)
	$aTabs[] = array("DIV" => "edit0", "TAB" => GetMessage("SC_PORTAL_WORK"), "ICON" => "site_check", "TITLE" => GetMessage("SC_PORTAL_WORK_DESC"));
$aTabs[] = array("DIV" => "edit1", "TAB" => GetMessage("SC_TEST_CONFIG"), "ICON" => "site_check", "TITLE" => GetMessage("SC_FULL_CP_TEST"));
$aTabs[] = array("DIV" => "edit2", "TAB" => GetMessage("SC_TAB_2"), "ICON" => "site_check", "TITLE" => GetMessage("SC_SUBTITLE_DISK"));
$aTabs[] = array("DIV" => "edit5", "TAB" => GetMessage("SC_TAB_5"), "ICON" => "site_check", "TITLE" => GetMessage("SC_TIK_TITLE"));

$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);

$APPLICATION->SetTitle(GetMessage("SC_SYSTEM_TEST"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

?>

	<style>
		.sc_help_link {
			float:right;
			cursor:pointer;
			background: url("/bitrix/themes/.default/icons/status_icons.png") no-repeat scroll -12px -236px transparent;
			width:25px;
			height:25px;
			margin-left:10px;
		}
		
		.sc_icon_success {
			background: url("/bitrix/themes/.default/icons/status_icons.png") no-repeat scroll -14px -19px transparent;
			width:25px;
			height:25px;
		}

		.sc_icon_warning{
			background: url("/bitrix/themes/.default/icons/status_icons.png") no-repeat scroll -12px -184px transparent;
			width:25px;
			height:25px;
		}

		.sc_icon_error{
			background: url("/bitrix/themes/.default/icons/status_icons.png") no-repeat scroll -12px -76px transparent;
			width:25px;
			height:25px;
		}

		.sc_error {
			color:#DD0000;
			font-weight:bold;
		}

		.sc_success {
			color:#408218;
			font-weight:bold
		}
	</style>
	<script>
		var bTestFinished = false;
		var bSubmit;

		function show_popup(title, link, confirm_text)
		{
			if (confirm_text && !confirm(confirm_text))
				return;

			var d = new BX.CAdminDialog({
				'title': title,
				'content_url': '/bitrix/admin/site_checker.php' + link,
				//   'content_post': this.JSParamsToPHP(arParams, 'PARAMS')+ '&' +
				//  this.JSParamsToPHP(arProp, 'PROP')+'&'+this.SESS,
				'draggable': true,
				'resizable': true,
				'buttons': [BX.CAdminDialog.btnClose]
			});

			d.Show();
		}

		function fix_onload(result)
		{
			try
			{
				eval(result);

				fix_status = document.getElementById('fix_status');
				if (test_percent < 100)
					fix_status.innerHTML = '<table width="100%" class="internal" style="padding:20px;padding-top:0"><tr><td width="40%">' + strNextTestName + '</td><td><div style="text-align:center;font-weight:bold;background-color:#b9cbdf;padding:2px;width:' + test_percent + '%">' + test_percent +  '%</div></table>';
				else
					fix_status.innerHTML = '';

				if (strResult != '')
				{
					var oTable = document.getElementById('fix_table');
					var oRow = oTable.insertRow(-1);

					var oCell = oRow.insertCell(-1);
					oCell.style.width = '40%';
					oCell.innerHTML = strCurrentTestName;

					oCell = oRow.insertCell(-1);
					oCell.innerHTML = strResult;
				}

				if (strNextRequest)
					BX.ajax.get('site_checker.php?fix_mode=' + fix_mode + '&test_start=Y&lang=<?=LANGUAGE_ID?>&charset=<?=LANG_CHARSET?>&<?=bitrix_sessid_get()?>&unique_id=<?=checker_get_unique_id()?>' + strNextRequest, fix_onload);
				else // Finish
					fix_status.innerHTML = '';
			}
			catch(e)
			{
				var o;
				if (o = document.getElementById('fix_status'))
				{
					o.innerHTML = result;
					alert('<?=GetMessage("SC_TEST_FAIL")?>');
				}
			}
		}

		function set_start(val)
		{
			document.getElementById('test_start').disabled = val ? 'disabled' : '';
			document.getElementById('test_stop').disabled = val ? '' : 'disabled';
			document.getElementById('progress').style.visibility = val ? 'visible' : 'hidden';

			if (val)
			{
				ShowWaitWindow();

				if (ob = BX('express_result'))
					ob.innerHTML = '';
				document.getElementById('result').innerHTML = '<table id="result_table" width="100%" class="internal"></table>';
				document.getElementById('status').innerHTML = '<?
					$oTest = new CSiteCheckerTest();
					echo $oTest->strCurrentTestName;
				?>';

				document.getElementById('percent').innerHTML = '0%';
				document.getElementById('indicator').style.width = '0%';

				BX.ajax.get('site_checker.php?test_start=Y&lang=<?=LANGUAGE_ID?>&<?=bitrix_sessid_get()?>', test_onload);
			}
			else
				CloseWaitWindow();
		}

		var strGroupName_last = '';
		function test_onload(result)
		{
			try
			{
				if (result)
					eval(result);
				else
					throw 'Empty result';
			}
			catch(e)
			{
				console.log(result);
				strNextRequest = '';
				strResult = '<span class="sc_error"><?=GetMessage("SC_TEST_FAIL")?></span>';
			}

			if (document.getElementById('test_start').disabled) // Stop was not pressed
			{
				document.getElementById('percent').innerHTML = iPercent + '%';
				document.getElementById('indicator').style.width = iPercent + '%';
				document.getElementById('status').innerHTML = strNextTestName;

				if (!(oRow = BX('in_progress')))
				{
					var oTable = BX('result_table');
					if (strGroupName != strGroupName_last)
					{
						strGroupName_last = strGroupName;
						oRow = oTable.insertRow(-1);
						oRow.className = 'heading';
						oCell = oRow.insertCell(-1);
						oCell.className = 'align-left';
						oCell.setAttribute("colSpan", "3");
						oCell.innerHTML = strGroupName;
					}

					oRow = oTable.insertRow(-1);
					oCell = oRow.insertCell(-1);
					oCell.style.width = '40%';
					oCell.innerHTML = strCurrentTestName;
					oCell = oRow.insertCell(-1);
					oCell.style.width = '29px';
					oCell = oRow.insertCell(-1);
				}

				if (strResult != '') // test finished
				{
					oRow.setAttribute('id', '');

					oCell = oRow.cells[1];
					if (test_result == 1)
						oCell.innerHTML = '<div class="sc_icon_success"></div>';
					else if (test_result == 0)
						oCell.innerHTML = '<div class="sc_icon_warning"></div>';
					else if (test_result == -1)
						oCell.innerHTML = '<div class="sc_icon_error"></div>';

					oCell = oRow.cells[2];
					oCell.innerHTML = '<div class="sc_help_link"></div>' + strResult;

					var oDiv = oCell.firstChild;
					oDiv.id = strCurrentTestFunc;
					oDiv.title = strCurrentTestName;
					oDiv.onclick = function(){show_popup(this.title, '?help_id=' + this.id + '&lang=<?=LANGUAGE_ID?>')};
				}
				else
				{
					oRow.setAttribute('id', 'in_progress');

					oCell = oRow.cells[2];
					oCell.innerHTML = '<div style="text-align:center;font-weight:bold;background-color:#b9cbdf;padding:2px;width:' + test_percent + '%">' + test_percent +  '%</div>';
				}

				if (strNextRequest)
				<? if ($_GET['HTTP_HOST']) { ?>
					BX.ajax.get('site_checker.php?HTTP_HOST=<?=urlencode($_GET['HTTP_HOST'])?>&SERVER_PORT=<?=urlencode($_GET['SERVER_PORT'])?>&HTTPS=<?=urlencode($_GET['HTTPS'])?>&test_start=Y&lang=<?=LANGUAGE_ID?>&<?=bitrix_sessid_get()?>' + strNextRequest, test_onload);
				<? } else { ?>
					BX.ajax.get('site_checker.php?HTTP_HOST=' + window.location.hostname + '&SERVER_PORT=' + window.location.port + '&HTTPS=' + (window.location.protocol == 'https:' ? 'on' : '') + '&test_start=Y&lang=<?=LANGUAGE_ID?>&<?=bitrix_sessid_get()?>' + strNextRequest, test_onload);
				<? } ?>
				else // Finish
				{
					set_start(0);
					bTestFinished = true;
					if (bSubmit)
					{
						if (window.tabControl)
							tabControl.SelectTab('edit5');
						SubmitToSupport();
					}
				}
			}
		}

		var oExpressTable;
		var strGroupName_last_e;
		var group_num = 1;
		var group_test_result = 1;
		function ExpressTest(result, begin)
		{
			if (begin)
			{
				set_start(0);
				BX('express_start').disabled = true;
				strNextRequest = '';
				ob = BX('express_result');
				ob.innerHTML = '<table width="100%" class="internal" style="margin-top:10px"></table>';
				oExpressTable = ob.firstChild;
				ShowWaitWindow();
			}
			else
			{
				try 
				{
					if (result)
						eval(result);
					else
						throw 'Empty result';

					if (strResult)
					{
						if (strGroupName != strGroupName_last_e || !strNextRequest)
						{
							if (oCell = BX('group_test'))
							{
								oCell.id = "";
								oCell.setAttribute("colSpan", "2");
								if (group_test_result == 1)
									oCell.innerHTML = '<span onclick="ShowTestResult(' + group_num + ')" class="sc_success" style="cursor:pointer;border-bottom:1px dashed #408218"><?=GetMessageJS("SC_ERRORS_NOT_FOUND")?></span>';
								else if (group_test_result == -1)
									oCell.innerHTML = '<span onclick="ShowTestResult(' + group_num + ')" class="sc_error" style="cursor:pointer;border-bottom:1px dashed #DD0000"><?=GetMessageJS("SC_ERRORS_FOUND")?></span>'
								else
									oCell.innerHTML = '<span onclick="ShowTestResult(' + group_num + ')" style="cursor:pointer;border-bottom:1px dashed"><?=GetMessageJS("SC_WARNINGS_FOUND")?></span>'
								if (group_test_result < 1)
									window.setTimeout('ShowTestResult(' + group_num + ', 1)', 500);

								group_num++;
							}

							if (strGroupName != strGroupName_last_e)
							{
								group_test_result = 1;

								strGroupName_last_e = strGroupName;
								oRow = oExpressTable.insertRow(-1);
								oRow.className = 'heading';

								oCell = oRow.insertCell(-1);
								oCell.className = 'align-left';
								oCell.innerHTML = strGroupName;

								oCell = oRow.insertCell(-1);
								oCell.className = "align-right";
								oCell.innerHTML = '<span style="color:black"><?=GetMessageJS("SC_TESTING1")?></span>';
								oCell.id = 'group_test';

								oRow = oExpressTable.insertRow(-1);
								oRow.id = 'express_group' + group_num;
								oCell = oRow.insertCell(-1);
								oCell.setAttribute('colSpan', '3');
								oCell.innerHTML = strGroupDesc;
							}
						}

						if (test_result < group_test_result)
							group_test_result = test_result;

						oRow = oExpressTable.insertRow(-1);
						oRow.style.display = 'none';
						oCell = oRow.insertCell(-1);
						oCell.style.width = '40%';
						oCell.innerHTML = strCurrentTestName;
						
						oCell = oRow.insertCell(-1);
						oCell.style.width = '29px';
						if (test_result == 1)
							oCell.innerHTML = '<div class="sc_icon_success"></div>';
						else if (test_result == 0)
							oCell.innerHTML = '<div class="sc_icon_warning"></div>';
						else if (test_result == -1)
							oCell.innerHTML = '<div class="sc_icon_error"></div>';

						oCell = oRow.insertCell(-1);
						oCell.innerHTML = '<div class="sc_help_link"></div>' + strResult;

						var oDiv = oCell.firstChild;
						oDiv.id = strCurrentTestFunc;
						oDiv.title = '<?=GetMessageJS("SC_HELP")?> ' + strCurrentTestName;
						oDiv.onclick = function(){show_popup(this.title, '?help_id=' + this.id + '&lang=<?=LANGUAGE_ID?>')};
					}
				}
				catch(e)
				{
					console.log(e);
					console.log(result);
					strNextRequest = '';
					strResult = '<span class="sc_error"><?=GetMessage("SC_TEST_FAIL")?></span>';
				}
			}

			HTTP_HOST = 	(tmp = "<?=urlencode($_GET['HTTP_HOST'])?>") ? tmp : window.location.hostname;
			SERVER_PORT = 	(tmp = "<?=urlencode($_GET['SERVER_PORT'])?>") ? tmp : window.location.port;
			HTTPS = 	(tmp = "<?=urlencode($_GET['HTTPS'])?>") ? tmp : (window.location.protocol == 'https:' ? 'on' : '');

			if (strNextRequest || begin)
				BX.ajax.get('site_checker.php?test_start=Y&fast=1&lang=<?=LANGUAGE_ID?>&<?=bitrix_sessid_get()?>&HTTP_HOST=' + HTTP_HOST + '&SERVER_PORT=' + SERVER_PORT + '&HTTPS=' + HTTPS + strNextRequest, ExpressTest);
			else
			{
				BX('express_start').disabled = false;
				CloseWaitWindow();
			}
		}

		function ShowTestResult(num, open)
		{
			start = 0;
			l = oExpressTable.rows.length;
			for(i = 0; i < l; i++)
			{
				oRow = oExpressTable.rows[i];
				if (oRow.id == 'express_group' + num)
				{
					start = 1;
				}
				else if (start)
				{
					if (oRow.className != '')
						break;

					oRow.style.display = oRow.style.display == 'none' || open ? '' : 'none';
				}
			}
		}
		<?=$_REQUEST['express_test'] ? 'window.setTimeout(\'ExpressTest("", true)\', 1000);' : ''?>
	</script>

<?
$tabControl->Begin();

if ($bIntranet)
{
	// portal checker
$tabControl->BeginNextTab();
?>
	<tr>
	<td colspan="2">
		<input type=button id="express_start" value="<?=GetMessage("SC_TEST_START")?>" onclick="ExpressTest('', true)" class="adm-btn-green">
	</td>
	</tr>
	<tr><td colspan="2" id="express_result"></td></tr>
<?
}

// site_checker
$tabControl->BeginNextTab();
?>
	<tr>
		<td colspan="2"><?=GetMessage("SC_FULL_TEST_DESC")?></td>
	</tr>
	<tr>
	<td colspan="2">
		<br>
		<input type=button value="<?=GetMessage("SC_START_TEST_B")?>" id="test_start" onclick="set_start(1)" class="adm-btn-green">
		<input type=button value="<?=GetMessage("SC_STOP_TEST_B")?>" disabled id="test_stop" onclick="bSubmit=false;set_start(0)">
		<div id="progress" style="visibility:hidden;padding-top:4px;" width="100%">
			<div id="status" style="font-weight:bold;font-size:1.2em"></div>
			<table border="0" cellspacing="0" cellpadding="2" width="100%">
				<tr>
					<td height="20">
						<div style="border:1px solid #B9CBDF">
							<div id="indicator" style="height:20px; width:0; background-color:#B9CBDF"></div>
						</div>
					</td>
					<td width=30>&nbsp;<span id="percent" style="font-size:1.4em">0%</span></td>
				</tr>
			</table>
		</div>
		<div id="result" style="padding-top:10px"></div>




	</td>
	</tr>
<?flush();

$tabControl->BeginNextTab();?>
	<tr>
		<td colspan="2"><?echo GetMessage("SC_SUBTITLE_DISK_DESC");?></td>
	</tr>
	<tr>
		<td colspan="2">
		<script>
		function onFrameLoad(ob)
		{
			CloseWaitWindow();
			var oDoc;
			if (ob.contentDocument)
				oDoc = ob.contentDocument;
			else
				oDoc = ob.contentWindow.document;

			document.getElementById('access_result').innerHTML = oDoc.body.innerHTML
		}

		function access_check_start(val)
		{
			document.getElementById('access_submit').disabled = val ? 'disabled' : '';
			document.getElementById('access_stop').disabled = val ? '' : 'disabled';

			if (val)
				ShowWaitWindow();
			else
				CloseWaitWindow();
		}
		</script>
			<? // CAdminMessage::ShowMessage(Array("MESSAGE"=>GetMessage("SC_CHECK_FILES_ATTENTION"), "TYPE"=>"ERROR","DETAILS"=>GetMessage("SC_CHECK_FILES_WARNING")));	?>
			<form method="POST" action="site_checker.php" target="access_frame" onsubmit="access_check_start(1)">
			<input type=hidden name=access_check value=Y>
			<input type=hidden name=lang value="<?=LANGUAGE_ID?>">
			<?=bitrix_sessid_post();?>
			<label><input type=radio name=check_type value=full checked> <?=GetMessage("SC_CHECK_FULL")?></label><br>
			<label><input type=radio name=check_type value=upload> <?=GetMessage("SC_CHECK_UPLOAD")?></label><br>
			<label><input type=radio name=check_type value=kernel> <?=GetMessage("SC_CHECK_KERNEL")?></label><br>
			<? if ('/bitrix' != BX_PERSONAL_ROOT): ?>
				<label><input type=radio name=check_type value=cache> <?=GetMessage("SC_CHECK_FOLDER")?> <b><?=BX_PERSONAL_ROOT?></b></label><br>
			<? endif; ?>
			<br>
			<input type=submit value="<?=GetMessage("SC_CHECK_B")?>" id="access_submit">
			<input type=button value="<?=GetMessage("SC_STOP_B")?>" disabled id="access_stop" onclick="access_check_start(0)">
			</form>
			<div width="100%" id="access_result"></div>
			<iframe name="access_frame" style="width:1px;height:1px;visibility:hidden" onload="onFrameLoad(this)"></iframe>
		</td>
	</tr>
<?
flush();

$tabControl->BeginNextTab();

if(!isset($strTicketError))
	$strTicketError = "";
?>
<tr><td colspan="2"><?
	if(isset($ticket_sent))
	{
		if(!empty($aMsg))
		{
			$e = new CAdminException($aMsg);
			$APPLICATION->ThrowException($e);
			if($e = $APPLICATION->GetException())
			{
				$message = new CAdminMessage(GetMessage("SC_ERROR0"), $e);
				if($message)
					echo $message->Show();
			}
		}

		if(strlen($strTicketError)>0 && !$message)
			CAdminMessage::ShowMessage($strTicketError);
		elseif(!$message)
			CAdminMessage::ShowNote(str_replace("#EMAIL#", "", GetMessage("SC_TIK_SEND_SUCCESS")));
	}
		?></td>
</tr>
<script>
	function SubmitToSupport()
	{
		var frm = document.forms.fticket;

		if (frm.ticket_text.value == '')
		{
			alert('<?=GetMessage("SC_NOT_FILLED")?>');
			return;
		}

//		frm.submit_button.disabled = 'disabled';

		if (!bTestFinished && frm.ticket_test.checked)
		{
			alert('<?=GetMessage("SC_TEST_WARN")?>');
//			if (window.tabControl)
//				tabControl.SelectTab('edit3');
			bSubmit = true; // submit after test
			set_start(1);
		}
		else if(frm.ticket_test.checked)
		{
			CHttpRequest.Action = function (result)
			{
				document.forms.fticket.test_file_contents.value = result;
				frm.submit();
			};
			CHttpRequest.Send('?read_log=Y');
		}
		else
			frm.submit();
	}
</script>
<?
		?>
<form method="POST" action="<?=SUPPORT_PAGE?>" name="fticket">
<?echo bitrix_sessid_post();?>
<input type="hidden" name="send_ticket" value="Y">
<input type="hidden" name="license_key" value="<?=(LICENSE_KEY == "DEMO"? "DEMO" : md5("BITRIX".LICENSE_KEY."LICENCE"))?>">
<input type="hidden" name="test_file_contents" value="">
<input type="hidden" name="ticket_title" value="<?=GetMessage('SC_RUS_L1').' '.htmlspecialcharsbx($_SERVER['HTTP_HOST'])?>">
<input type="hidden" name="BX_UTF" value="<?=(defined('BX_UTF') && BX_UTF==true)?'Y':'N'?>">
<input type="hidden" name="tabControl_active_tab" value="edit5">
<tr>
	<td valign="top"><span class="required">*</span><?=GetMessage("SC_TIK_DESCR")?><br>
			<small><?=GetMessage("SC_TIK_DESCR_DESCR")?></small></td>
	<td valign="top"><textarea name="ticket_text" rows="6" cols="60"><?= htmlspecialcharsbx($_REQUEST["ticket_text"])?></textarea></td>
</tr>
<tr>
	<td valign="top"><label for="ticket_test"><?=GetMessage("SC_TIK_ADD_TEST")?></label></td>
	<td valign="top"><input type="checkbox" id="ticket_test" name="ticket_test" value="Y" checked></td>
</tr>
<?if (strlen($_REQUEST["last_error_query"])>0):?>
	<tr>
		<td valign="top"><?=GetMessage("SC_TIK_LAST_ERROR")?></td>
		<td valign="top">
			<?=GetMessage("SC_TIK_LAST_ERROR_ADD")?>
			<input type="hidden" name="last_error_query" value="<?= htmlspecialcharsbx($_REQUEST["last_error_query"])?>">
		</td>
	</tr>
<?endif;?>
<tr>
	<td></td>
	<td>
		<input type="button" name="submit_button" onclick="SubmitToSupport()" value="<?=GetMessage("SC_TIK_SEND_MESS")?>">
	</td>
</tr>
<tr>
	<td colspan=2>
	<?
	echo BeginNote();
	echo GetMessage("SC_SUPPORT_COMMENT").' <a href="'.SUPPORT_PAGE.'" target=_blank>'.SUPPORT_PAGE.'</a>';
	echo EndNote();
	?>
	</td>
</tr>
</form>
<?
//$tabControl->Buttons();
$tabControl->End();
$tabControl->ShowWarnings("fticket", $message);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
