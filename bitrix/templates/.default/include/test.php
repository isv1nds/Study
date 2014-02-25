<?php


class iblockTest{


    public $include;

    /*
     *
     * Констуктор
     * Подключает модуль iblock
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
     * id номер документа
     * возвращает массив путей от элемента до корневого каталога для всех разделов и подразделов,
     * к которым прикреплен элемент
     */

    public function underbread($id){

        if($this->include){

            $bread=array();
            $res=CIBlockElement::GetByID($id);
            if($ar_res = $res->GetNext()) {$name=$ar_res['NAME']; $iblock=$ar_res['IBLOCK_ID'];} // получаем имя элемента и id инф. блока
            $res=CIBlockSection::GetList(array(), array('IBLOCK_ID'=>$iblock),false, array('ID','DEPTH_LEVEL','NAME','IBLOCK_SECTION_ID'));
            while($ar_res = $res->GetNext()){

                $razd[]=$ar_res;//список всех разделов инфоблока, чтоб не делать запрос всякий раз, когда нам нужен parent
            }

            $res = CIBlockElement::GetElementGroups($id,false, array('ID','DEPTH_LEVEL','NAME','IBLOCK_SECTION_ID'));//все разделы, которым принадлежит элемент

            while($ar_res = $res->GetNext()){
                $path=$name.' < '.$ar_res['NAME'];//добавляем текущего родителя элемента в цепочку

                if($ar_res['DEPTH_LEVEL']>1) { //и если у него тоже есть родитель
                    $depth=$ar_res['DEPTH_LEVEL'];
                    $parent=$ar_res['IBLOCK_SECTION_ID'];

                    while($depth>1){

                        foreach($razd as $val){
                            if( $val['ID']==$parent){//перебераем массив $razd с данными раздела, пока уровень вложенности не станет 1
                                $path=$path.' < '.$val['NAME'];
                                $depth=$val['DEPTH_LEVEL'];
                                $parent=$val['IBLOCK_SECTION_ID'];
                                break;
                            }

                        }

                    }

                }

                $bread[]=$path;//сохраняем путь
            }
            return $bread;

        }
        else return false;
    }


/*
 * Функция склейки дубликатов значений
 *
 * iblock -  иденификатор информационного блока
 * code - код свойства
 * search - искомое значение (id для списка)
 * change - заменяемое значение
 * $islist - является ли списком необязательное
 *
 * возвращает true если склйека удалась
 */
    public function merge($iblock,$code,$search,$change,$islist=false){

        if($this->include){



            $res=CIBlockElement::GetList(array(), array('IBLOCK_ID'=>$iblock,'PROPERTY_'.$code=>$search),false, false, array('ID','NAME'));
            while($item=$res->GetNext()){

                CIBlockElement::SetPropertyValues($item['ID'], $iblock, $change,$code);

            }
           if($islist) CIBlockPropertyEnum::Delete($search);
            return true;



        }

        else return false;
    }

/*
 * Функция вывода значения пользователського поля раздела
 *
 * iblock -  иденификатор информационного блока
 * idsec -  иденификатор раздела
 * paramcode - симовльный код поля
 *
 */

    public function usfields($iblock,$idsec, $paramcode){
        if($this->include){
        $res=CIBlockSection::GetList(array(), array('IBLOCK_ID'=>$iblock,'ID'=>$idsec),false, array('NAME', $paramcode));
        if($ar_res = $res->GetNext()) return $ar_res[$paramcode];
            else return false;


        }
        else return false;
    }

}
//--------------Использование-----------------------------------------------------------------


$test=new iblockTest();
//dump3($test->merge(4,'BR',20,9, true), false, true);


dump3($test->underbread(31), false, true);

dump3($test->usfields(4,8, 'UF_DATE'),false, true);



?>