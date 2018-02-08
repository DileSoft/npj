<?php
/*
    FieldCheckboxes( &$rh, $config ) -- работаем с группой чекбоксов
  ---------

  // overridden:
  * Validate()
  * _Preparse( &$tpl_engine )
  * Format()

  // options
  * field -- для левой панели формы
  * fields( field, field, )
  * names( name, name )
  * default( 0|1, 0|1, )

=============================================================== v.1 (Kuso)
*/

class FieldCheckboxes extends Field
{

  function FieldCheckboxes( &$rh, $config )
  {
    $this->data = array();
    Field::Field(&$rh, $config);
    // assigning defaults
    if (!isset($this->config["tpl_data"])) $this->config["tpl_data"] = "field_checkboxes.html:Plain";
  }

  // проверка на различные ошибки -- здесь их быть не может
  function Validate()   
  { 
    $this->invalid = false;
    return !$this->invalid; 
  }

  function CreateSELECT() { return implode(", ",$this->config["fields"]); }
  function CreateUPDATE() { $this->_StoreToDb(); 
                            $result=""; 
                            foreach( $this->config["fields"] as $key=>$field )
                            {
                              if ($result != "") $result.=", ";
                              $result.= $field."=". $this->rh->db->Quote($this->db_data[$key]);
                            }
                            return $result; 
                          }
  
  // получение из формы/бд
  function _Load( &$data ) { $this->_RestoreFromDb( &$data, "_" ); }
  function _RestoreFromDb( &$data, $skip_char="" ) 
  { 
    if ($this->config["db_ignore"] && ($skip_char=="")) return;
    foreach( $this->config["fields"] as $key=>$field )
    {
     if ($data[ $skip_char.$field ])   $this->data[$key] = 1;
     else                              $this->data[$key] = 0;
    }
  }


  function _Preparse( &$tpl, $tpl_prefix )
  {
    if ($this->config["readonly"]) return;

    $this->rh->UseClass("ListSimple", $this->rh->core_dir);

    // message_set support
    if (isset($this->form->form_config["message_set"])) 
      foreach( $this->config["fields"] as $key=>$field )
       $this->config["names"][$key] = $this->rh->tpl->message_set[ "Form.".$field ];

    $data = array();
    foreach( $this->config["fields"] as $key=>$field )
    {
      $data[] = array(
          "href" => $field,
          "text" => $this->config["names"][$key],
          "title"=> ($this->data[$key]?" CHECKED ":""),
                       );
    }

    $list = new ListSimple( &$this->rh, $data );

    $tpl->Assign("_Field", "_".$this->config["field"] );
    $list->Parse($tpl_prefix.$this->config["tpl_data"]."_Groups", "GROUPS", 0);
  }
  function _Format() 
  { 
    // message_set support
    if (isset($this->form->form_config["message_set"])) 
      foreach( $this->config["fields"] as $key=>$field )
       $this->config["names"][$key] = $this->rh->tpl->message_set[ "Form.".$field ];
    $result=""; 
    foreach( $this->config["fields"] as $key=>$field )
    if ($this->data[$key])
    {
      if ($result != "") $result.="; ";
      $result.= $this->config["names"][$key];
    }
    return $result; 
  }

// EOC { FieldCheckboxes }
}


?>