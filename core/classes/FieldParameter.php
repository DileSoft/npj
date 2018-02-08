<?php
/*
    FieldParameter( &$rh, $config ) -- такой сложный контрол с двумя селектами и параметром
  ---------

  // overridden:
  * Validate()
  * _Preparse( &$tpl_engine )
  * Format()

  // options
  - only_more -- не показывать блоки value, param, add.
  - data = array( "value" => "name" ) -- что показывать в селекте "value"
  - add_data = array( "value" => "name" ) -- что показывать в радиоконтроле "add"
  - param_max -- максимальная длина параметра 
  - структура $this->data = array( "value", "param", "add", "more", "more_param" )
  ? lowercase
  ? omit
  ? notrim
  ? replaceEmpty

=============================================================== v.0 (Kuso)
*/

class FieldParameter extends Field
{

  function FieldParameter( &$rh, $config )
  {
    Field::Field(&$rh, $config);
    // assigning defaults
    if (!isset($this->config["tpl_data"])) 
        $this->config["tpl_data"] = "field_parameter.html:Plain";
    if (!isset($this->config["param_max"])) $this->config["param_max"] = 50;
  }

  // проверка на различные ошибки
  function Validate()   
  { 
    $config = &$this->config;
    $data = &$this->data;
    Field::Validate();
    // 1. min/max
    if (isset($config["param_max"]) && ($config["param_max"] < strlen($data[1])))
     $this->invalidReasons["FormError_TooLong"] = "Слишком длинное значение параметра, нужно не более ".$config["maxlen"]." символов";
    if (isset($config["param_max"]) && ($config["param_max"] < strlen($data[4])))
     $this->invalidReasons["FormError_TooLong"] = "Слишком длинное значение параметра, нужно не более ".$config["maxlen"]." символов";

    $this->invalid = sizeof($this->invalidReasons) != 0;
    return !$this->invalid; 
  }

  function CreateSELECT() { return $this->config["field"].", ".$this->config["field"]."_param "
                                   .", ".$this->config["field"]."_more, ".$this->config["field"]."_more_param, "
                                   .$this->config["field"]."_add "; }
  function CreateUPDATE() { $this->_StoreToDb(); 
                            return 
                             $this->config["field"]."=". $this->rh->db->Quote($this->db_data[0]).", ".
                             $this->config["field"]."_param=". $this->rh->db->Quote($this->db_data[1]).", ".
                             $this->config["field"]."_add=". $this->rh->db->Quote($this->db_data[2]).", ".
                             $this->config["field"]."_more=". $this->rh->db->Quote($this->db_data[3]).", ".
                             $this->config["field"]."_more_param=". $this->rh->db->Quote($this->db_data[4])
                            ; }

  // получение из формы/бд
  function _Load( &$data ) { $this->_RestoreFromDb( &$data, "_" ); }
  function _RestoreFromDb( &$data, $skip_char="" ) 
  { 
    $this->data = array();
    if (!$this->config["only_more"] || ($skip_char == ""))
    {
      $this->data[0] = &$data[ $skip_char.$this->config["field"]         ]; 
      $this->data[1] = &$data[ $skip_char.$this->config["field"]."_param"]; 
      $this->data[2] = &$data[ $skip_char.$this->config["field"]."_add"  ]; 
    }
    $this->data[3] = &$data[ $skip_char.$this->config["field"]."_more" ]; 
    $this->data[4] = &$data[ $skip_char.$this->config["field"]."_more_param"]; 

    foreach( $this->data as $i=>$v )
    {
      if (isset($this->config["lowercase"])) $this->data[$i] = strtolower($this->data[$i]);
      if (isset($this->config["omit"])) $this->data[$i] = preg_replace($this->config["omit"], "", $this->data[$i]);
      if (!isset($this->config["notrim"])) $this->data[$i] = trim($this->data[$i]);
      if (isset($this->config["replaceEmpty"]) && ($this->data[$i]=="")) $this->data[$i] = $this->config["replaceEmpty"];
    }
  }

  function _Preparse( &$tpl, $tpl_prefix )
  {
    $this->rh->UseClass("ListSimple", $this->rh->core_dir);
    $this->rh->UseClass("ListCurrent", $this->rh->core_dir);
    // №1 присваиваем параметры
    $tpl->Assign("_Field", "_".$this->config["field"] );
    $tpl->Assign("_Only_More", $this->config["only_more"] );
    $tpl->Assign("_Param_MaxLen", $this->config["param_max"] );
    $tpl->Assign("_Param", htmlspecialchars($this->data[1]) );
    $tpl->Assign("_More_Param", htmlspecialchars($this->data[4]) );
    // №2 формируем первый список селекта "валю"
    if (!isset($this->config["data"]))   
      $data = $tpl->message_set[ "Form.".$this->config["field"].".Data" ];
    else
    if (isset($this->form->form_config["message_set"])) 
    {
     $data = array();
     foreach ($this->config["data"] as $k=>$item)
      $data[$k] = $tpl->message_set[ $item ];
    } else $data = $this->config["data"];
    $list = &new ListCurrent( &$this->rh, $data, 0, $this->data[0] );
    $list->Parse( $tpl_prefix.$this->config["tpl_data"]."_Groups", "GROUPS", 0);
    // №3 формируем второй список селекта "море"
    $tpl->Assign("_Field", "_".$this->config["field"]."_more" );
    $list2 = &new ListCurrent( &$this->rh, $data, 0, $this->data[3] );
    $list2->Parse( $tpl_prefix.$this->config["tpl_data"]."_Groups", "GROUPS_MORE", 0);
    $tpl->Assign("_Field", "_".$this->config["field"] );
    // №4 формируем список радиогруппы "адд"
    if (!isset($this->config["add_data"]))   
      $data = $tpl->message_set[ "Form.".$this->config["field"].".AddData" ];
    else
    if (isset($this->form->form_config["message_set"])) 
    {
     $data = array();
     foreach ($this->config["add_data"] as $k=>$item)
      $data[$k] = $tpl->message_set[ $item ];
    } else $data = $this->config["add_data"];
    $list3 = &new ListCurrent( &$this->rh, $data, 0, $this->data[2] );
    $list3->Parse( $tpl_prefix.$this->config["tpl_data"]."_Radio", "ADD", 0);
  }
  function _Format() 
  { 
    // !!! это можно переделать как-нибудь
    if ($this->data[0] == "")
     if (isset($this->rh->tpl->message_set["Form._StringEmpty"])) return $this->rh->tpl->message_set["Form._StringEmpty"];
     else return "<small>(не указано)</small>";

    return $this->data[0]; 
  }

// EOC { FieldParameter }
}


?>