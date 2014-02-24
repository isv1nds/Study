<?php

class iblockTest{


    public $include;

    /*
     *
     * Констуктор
     *
     *
     */
    function __construct(){

        if(CModule::IncludeModule('iblock')){
            $this->include=true;

        }
        else  $this->include=false;

    }
    /*
     *
     * Создание обратных цепочек вложенности по ID элемента, для всех разделов и подразделов,
     * которым принадлжеит данный элемент
     *
     */

    public function underbread($id){

        if($this->include){

            $bread=array();
            $res=CIBlockElement::GetByID($id);
            if($ar_res = $res->GetNext()) {$name=$ar_res['NAME']; $iblock=$ar_res['IBLOCK_ID'];}
            $res=CIBlockSection::GetList(array(), array('IBLOCK_ID'=>$iblock),false, array('ID','DEPTH_LEVEL','NAME','IBLOCK_SECTION_ID'));
            while($ar_res = $res->GetNext()){

                $razd[]=$ar_res;
            }

            $res = CIBlockElement::GetElementGroups($id,false, array('ID','DEPTH_LEVEL','NAME','IBLOCK_SECTION_ID'));

            while($ar_res = $res->GetNext()){
                $path=$name.' < '.$ar_res['NAME'];

                if($ar_res['DEPTH_LEVEL']>1) {
                    $depth=$ar_res['DEPTH_LEVEL'];
                    $parent=$ar_res['IBLOCK_SECTION_ID'];//

                    while($depth>1){

                        foreach($razd as $val){
                            if( $val['ID']==$parent){
                                $path=$path.' < '.$val['NAME'];
                                $depth=$val['DEPTH_LEVEL'];
                                $parent=$val['IBLOCK_SECTION_ID'];
                                break;
                            }

                        }

                    }

                }

                $bread[]=$path;
            }
            return $bread;

        }
        else return false;
    }





}



$test=new iblockTest();







?>