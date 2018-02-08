<?php
/*
    FieldRubrika( &$rh, $config ) -- о, это рубрика!
  ---------

  // overridden:
  * _Preparse( &$tpl_engine )
  * ParseTo( &$tpl_engine )

  // options
  * tpl_groups
  * data
  * data_plain = 0|1 -- флаг того, что дата не заимствуетс€ из message_set
  * sql
  * preface
  * preface_tpl -- если =1, то префейс не приписываетс€ между стрингом и селектом, а ассигнитс€ в _Preface перед селектом
  * add -- дописывать в конец селекта по клику или замен€ть всЄ его содержимое?

=============================================================== v.4 (Kuso)
*/

class FieldRubrika extends FieldStringSelect
{

  function FieldRubrika( &$rh, $config )
  {
    // assigning defaults
    if (!isset($config["tpl_groups"])) 
      $config["tpl_groups"] = "field_rubrika.html:All";
    if (!isset($config["tpl_data"])) 
      $config["tpl_data"] = "field_rubrika.html:Rubrika";

    $this->_predata = $config["data"];
    $config["data"] = array();
    foreach( $this->_predata as $k=>$v )
      $config["data"][$v] = $v;

    FieldStringSelect::FieldStringSelect(&$rh, $config);
  }

  // в форме получаем то, что надо
  function _Load( &$data ) 
  { 
     FieldStringSelect::_RestoreFromDb( &$data, "_" );
  }

  // в Ѕƒ храним вс€кую чушь, надо преобразовывать
  function _RestoreFromDb( &$data, $skip_char="" ) 
  { 
    FieldStringSelect::_RestoreFromDb( &$data, $skip_char );
    $datas = explode(" ", $this->data );
    $this->data = "";
    foreach( $datas as $k=>$v )
    {
      $this->data[] = $this->_predata[strtolower($v)];
    }
    $this->data = implode(";", $this->data);
  }

  // при складывании в Ѕƒ тоже надо преобразовывать в формат, совместимый с чушью
  function _StoreToDb() 
  {
    $datas = explode(";", $this->data );
    $dd = array_flip( $this->_predata );
    $this->db_data = "";
    foreach( $datas as $k=>$v )
    {
      $this->db_data[] = $dd[$v];
    }
    $this->db_data = implode(" ", $this->db_data);
  }


// EOC { FieldRubrika }
}


?>