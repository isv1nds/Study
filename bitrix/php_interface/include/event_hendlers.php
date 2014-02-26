<?php
AddEventHandler("iblock", "OnBeforeIBlockElementUpdate", Array("ElUpdate", "OnBeforeIBlockElementUpdateHandler"));

class ElUpdate
{

    function OnBeforeIBlockElementUpdateHandler(&$arFields)    {

        if($arFields["IBLOCK_ID"]==2){
                $mass=array();
                $res=CIBlockElement::GetList(array(), array("IBLOCK_ID"=>5,"PROPERTY_LINK"=>$arFields["ID"]),false, false, array("ID"));
                while( $ar_res = $res->GetNext()){


                    if($arFields["ACTIVE"]=="N"){
                        $res = CIBlockElement::Update($ar_res["ID"], array("ACTIVE"=>"N"));
                    }
                    else{

                        $res = CIBlockElement::Update($ar_res["ID"], array("ACTIVE"=>"Y"));
                    }



                }

            //dump3($mass, true);

        }
    }
}





?>