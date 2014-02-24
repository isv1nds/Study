<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$INPUT_ID = trim($arParams["~INPUT_ID"]);
if(strlen($INPUT_ID) <= 0)
	$INPUT_ID = "title-search-input";
$INPUT_ID = CUtil::JSEscape($INPUT_ID);

$CONTAINER_ID = trim($arParams["~CONTAINER_ID"]);
if(strlen($CONTAINER_ID) <= 0)
	$CONTAINER_ID = "title-search";
$CONTAINER_ID = CUtil::JSEscape($CONTAINER_ID);

if($arParams["SHOW_INPUT"] !== "N"):?>
<div id="<?echo $CONTAINER_ID?>" class="bx_search_container">
	<form action="<?echo $arResult["FORM_ACTION"]?>">
		<div class="bx_field">
			<input id="<?echo $INPUT_ID?>" type="text" name="q" value="" size="23" maxlength="50" autocomplete="off" class="bx_input_text"/>
			<input name="s" type="submit" value="" class="bx_input_submit"/>
		</div>
	</form>
</div>
<?endif?>
<script type="text/javascript">
var jsControl_<?echo md5($CONTAINER_ID)?> = new JCTitleSearch({
	//'WAIT_IMAGE': '/bitrix/themes/.default/images/wait.gif',
	'AJAX_PAGE' : '<?echo CUtil::JSEscape(POST_FORM_ACTION_URI)?>',
	'CONTAINER_ID': '<?echo $CONTAINER_ID?>',
	'INPUT_ID': '<?echo $INPUT_ID?>',
	'MIN_QUERY_LEN': 2
});

<?if (isset($_REQUEST["q"])):?>
BX.ready(function(){
	BX("<?=$INPUT_ID?>").value = "<?=CUtil::JSEscape($_REQUEST["q"])?>";
});
<?endif?>
</script>