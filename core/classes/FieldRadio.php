<?php
/*
    FieldRadio( &$rh, $config ) -- работаем с радиогруппами
  ---------

  // overridden:
  * Validate()
  * _Preparse( &$tpl_engine )
  * Format()

  // options
  * data( key=>value )
  * sql ( "select .. as key, .. as value from .. " )
  * onchange -- вызвать js вида function_name( {{FormName}}, {{_Field}} ) (NB: теоретически есть у всех типов полей)
  * validador, validator_params -- внешний валидатор, возвращает ===0 или сообщение об ошибке. ћожет быть использован как преобразователь
  * data_raw -- не брать из messageset никогда


  // дл€ R3
  - cols
  - freetext
  - freelen

=============================================================== v.4 (Kuso)
*/

class FieldRadio extends Field
{

  function FieldRadio( &$rh, $config )
  {
    Field::Field(&$rh, $config);
    // assigning defaults
    if (!isset($this->config["tpl_data"])) $this->config["tpl_data"] = "field_radio.html:Plain";
  }

  // проверка на различные ошибки
  function Validate()   
  { 
    $config = &$this->config;
    $data = &$this->data;
    Field::Validate();

    if ($this->config["validator"])
    { $result = call_user_func( $this->config["validator"], &$this->data, &$this->config["validator_param"] );
      if ($result !== 0)
      {
       $this->invalidReasons["FormError_Foreign"] = $result;
      }
    }

    $this->invalid = sizeof($this->invalidReasons) != 0;
    return !$this->invalid; 
  }

  // получение из формы/бд
  function _Load( &$data ) { $this->_RestoreFromDb( &$data, "_" ); }
  function _RestoreFromDb( &$data, $skip_char="" ) 
  { 
    $this->data = &$data[ $skip_char.$this->config["field"]]; 
    if ($this->data === null) $this->data = "";
  }


  function _Preparse( &$tpl, $tpl_prefix )
  {
    if ($this->config["readonly"]) return;

    $this->rh->UseClass("ListSimple", $this->rh->core_dir);
    $this->rh->UseClass("ListCurrent", $this->rh->core_dir);

    if (isset($this->config["sql"]))
    {
      $rs= $this->rh->db->Execute( $this->config["sql"] );
      if ($rs === false) $this->config["data"]=array();
      else 
      { 
        $a = $rs->GetArray();
        $this->config["data"] = array();
        foreach ($a as $i)
          $this->config["data"][ $i["id"] ] = $i["value"];
      }
      $data = $this->config["data"];
    }
    else
    {
      if (!isset($this->config["data"]))   
        $data = $tpl->message_set[ "Form.".$this->config["field"].".Data" ];
      else
      if (isset($this->form->form_config["message_set"]) && !$this->config["data_raw"]) 
      {
       $data = array();
       foreach ($this->config["data"] as $k=>$item)
        $data[$k] = $tpl->message_set[ $item ];
      } else $data = $this->config["data"];
    }
    $list = new ListCurrent( &$this->rh, $data, 0, $this->data );
    
    $tpl->Assign("_Field", "_".$this->config["field"] );
    $list->Parse($tpl_prefix.$this->config["tpl_data"]."_Groups", "GROUPS", 0);
  }
  function _Format() { 
    if (1*$this->data == $this->data)
    if (isset($this->config["data"][ 1*$this->data ]))
      return $this->config["data"][ 1*$this->data ];
    else
      return $this->config["data"][ $this->data ];
    else
      return $this->config["data"][ $this->data ];
     }

// EOC { FieldRadio }
}


?>