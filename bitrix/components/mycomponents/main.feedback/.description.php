<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("MAIN_ADD_NEW"),
	"DESCRIPTION" => GetMessage("MAIN_ADD_NEW_DESCR"),
	"ICON" => "/images/feedback.gif",
    "PATH" => array(
        "ID" => "content",
        "CHILD" => array(
            "ID" => "vacancy_ext",

        )
    ),
);
?>