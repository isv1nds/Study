<?php
if(file_exists($_SERVER["DOCUMENT_ROOT"].'/bitrix/php_interface/include/functions.php')){
    require_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/php_interface/include/functions.php');

}
if(file_exists($_SERVER["DOCUMENT_ROOT"].'/bitrix/php_interface/include/agent.php')){
    require_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/php_interface/include/agent.php');

}

if(file_exists($_SERVER["DOCUMENT_ROOT"].'/bitrix/php_interface/include/event_hendlers.php')){
    require_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/php_interface/include/event_hendlers.php');

}

?>