<?php
/*
    FieldRadioGrouped( &$rh, $config ) -- работаем с радиогруппой, поделенной на сегменты
  ---------

  // overridden:

  // options
  * groups[ "tag" => data ]
  * groups_field
  * tpl_groups

  + same as FieldRadio, except sql, data

=============================================================== v.0 (Kuso)
*/

class FieldRadioGrouped extends FieldRadio
{

  function FieldRadioGrouped( &$rh, $config )
  {
    if (!isset($config["tpl_data"])) $config["tpl_data"] = "field_radio_grouped.html:Plain";

    FieldRadio::FieldRadio(&$rh, $config);
    // preparsing groups
    $this->groups_hash    = array();
    $this->config["data"] = array();
    foreach( $this->config["groups"] as $k=>$v)
     foreach( $v as $kk=>$vv )
     {
       $this->groups_hash[$kk] = $k;
       $this->config["data"][$kk] = $vv;
     }

    // assigning defaults
    if (!isset($this->config["tpl_groups"])) $this->config["tpl_groups"] = "field_radio_grouped.html:Hor";
  }

  function SetDefault() 
  { 
    $result = FieldRadio::SetDefault();
    $this->groups_data = $this->groups_hash[ $this->data ];
    return $result; 
  }

  // сохранение
  function _StoreToDb()   { FieldRadio::_StoreToDb(); $this->db_groups_data = $this->groups_data; }
  function CreateUPDATE() { $result = FieldRadio::CreateUPDATE();
                            return $result.", ".
                                   $this->config["groups_field"]."=".$this->rh->db->Quote($this->db_groups_data);
                          }

  // получение из формы/бд
  function _RestoreFromDb( &$data, $skip_char="" ) 
  { 
    FieldRadio::_RestoreFromDb( &$data, $skip_char );
    $this->groups_data = $this->groups_hash[ $this->data ];
  }

  function _Preparse( &$tpl, $tpl_prefix )
  {
    if ($this->config["readonly"]) return;

    $this->rh->UseClass("ListSimple",  $this->rh->core_dir);
    $this->rh->UseClass("ListCurrent", $this->rh->core_dir);
    $this->rh->UseClass("ListObject",  $this->rh->core_dir);

    $groups = $this->config["groups"];

    $tpl->Assign("_Field", "_".$this->config["field"] );

    $G = array();
    foreach( $groups as $k=>$v )
    {
      $tpl->Assign("_GroupId", $k );
      $list = &new ListCurrent( &$this->rh, $v, 0, $this->data );
      $G[]  = array(
                "GROUPS"  => $list->Parse($tpl_prefix.$this->config["tpl_data"]."_Groups"),
                "_GroupName"   => $this->rh->tpl->message_set["Form.".$this->config["field"].".Groups"][$k],
                "_GroupId"     => $k,
                   );
    }
    $list = &new ListObject( &$this->rh, $G );
    $list->Parse($tpl_prefix.$this->config["tpl_groups"], "GROUPS", 0);

  }
  function _Format() 
  { 
    $radio = FieldRadio::_Format();

    return $this->rh->tpl->message_set["Form.".$this->config["field"].".Groups"][$this->groups_data].
           " &mdash; ".$radio;
  }

// EOC { FieldRadioGrouped }
}


?>