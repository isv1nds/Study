<?php
AddEventHandler("iblock", "OnBeforeIBlockElementUpdate", Array("ElUpdate", "OnBeforeIBlockElementUpdateHandler"));
AddEventHandler("iblock", "OnBeforeIBlockElementDelete", Array("ElDelete", "OnBeforeIBlockElementDeleteHandler"));
AddEventHandler("main", "OnBeforeUserUpdate", Array("UsUpd", "OnBeforeUserUpdateHandler"));
AddEventHandler("main", "OnBeforeUserAdd", Array("UsAdd", "OnBeforeUserAddHandler"));

class ElUpdate
{

    function OnBeforeIBlockElementUpdateHandler(&$arFields)    {

        if($arFields["IBLOCK_ID"]==2){
                $mass=array();
                $res=CIBlockElement::GetList(array(), array("IBLOCK_ID"=>5,"PROPERTY_LINK"=>$arFields["ID"]),false, false);
                  $el = new CIBlockElement;
                while( $ar_res = $res->GetNext()){

                    $res2 = $el->Update($ar_res["ID"], array("ACTIVE"=>$arFields["ACTIVE"]));

                }



        }
    }
}





class ElDelete
{

    function OnBeforeIBlockElementDeleteHandler($ID)
    {
        $mass=array();
        $res=CIBlockElement::GetList(array(), array("IBLOCK_ID"=>5,"PROPERTY_LINK"=>$ID),false, false, array("ID"));
        while( $ar_res = $res->GetNext()){
            $mass[]=$ar_res["ID"];

        }

            if(count($mass)>0)
            {
                global $APPLICATION;
                $APPLICATION->throwException("Элемент с ID ".$ID." удалять нельзя - он прикреплен к слайдеру на главной!");
                return false;
            }

    }
}




class UsUpd
{
    // создаем обработчик события "OnBeforeUserUpdate"
    function OnBeforeUserUpdateHandler(&$arFields)
    {



        foreach($arFields["GROUP_ID"] as $value){
            if($value["GROUP_ID"]==6){

                $filter=array("GROUPS_ID"=>array(1));
                $rsUsers = CUser::GetList(($by="personal_country"), ($order="desc"), $filter);
                $arEmail=array();
                while($row=$rsUsers->GetNext()){
                    $arEmail[]=$row["EMAIL"];

                }
                if(count($arEmail)>0){

                    $arEventFields=array(
                        "TEXT" => $arFields["LOGIN"],
                        "EMAIL" => implode(',',$arEmail),
                    );

                    $flag= CEvent::Send("PART_ADD", "s1", $arEventFields);
                    CEvent::CheckEvents();

                  // dump3(SITE_ID, true);
                }

            }


        }

    }
}

class UsAdd
{
    // создаем обработчик события "OnBeforeUserAdd"
    function OnBeforeUserAddHandler(&$arFields)
    {
        foreach($arFields["GROUP_ID"] as $value){
            if($value["GROUP_ID"]==6){

                $filter=array("GROUPS_ID"=>array(1));
                $rsUsers = CUser::GetList(($by="personal_country"), ($order="desc"), $filter);
                $arEmail=array();
                while($row=$rsUsers->GetNext()){
                    $arEmail[]=$row["EMAIL"];

                }
                if(count($arEmail)>0){

                    $arEventFields=array(
                        "TEXT" => $arFields["LOGIN"],
                        "EMAIL" => implode(',',$arEmail),
                    );

                  CEvent::Send("PART_ADD",  "s1", $arEventFields);
                  CEvent::CheckEvents();
                }

            }


        }
    }
}


?>