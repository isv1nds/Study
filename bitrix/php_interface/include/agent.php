<?php
function checkfeed(){
    if(CModule::IncludeModule('iblock')){

        $lastTime=date("d.m.Y H:i:s",time()-3600*24);
        $time=date("d.m.Y H:i:s");
        $res=CIBlockElement::GetList(array(), array('IBLOCK_ID'=>6,'><DATE_CREATE'=>array($lastTime,$time )),false, false, array('ID','NAME','DATE_CREATE'));
        $arItem=array();
        while($row=$res->GetNextElement()){

            $fields=$row->GetFields();
            $arItem[]=$fields;


        }

        CEventLog::Add(array(
            "SEVERITY" =>"SECURITY",
            "AUDIT_TYPE_ID" => "CHECK_FEED",
            "MODULE_ID" => "iblock",
            "ITEM_ID" => "",
            "DESCRIPTION" =>"Проверка новых отзывов, новых отзывов: ".count($arItem)

        ));


        if(count($arItem)>0){
            $filter=array("GROUPS_ID"=>array(1));
            $rsUsers = CUser::GetList(($by="personal_country"), ($order="desc"), $filter);
            $arEmail=array();
            while($row=$rsUsers->GetNext()){
                $arEmail[]=$row["EMAIL"];

            }
            if(count($arEmail)>0){

                $arEventFields=array(
                    "TEXT" => count($arItem),
                    "EMAIL" => implode(',',$arEmail),
                );
                CEvent::Send("CHECK_FEED", SITE_ID, $arEventFields);


            }
        }

    }
return "checkfeed();";


}


?>