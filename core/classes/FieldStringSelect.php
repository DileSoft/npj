<?php
/*
    FieldStringSelect( &$rh, $config ) -- работаем со строками, добавляя ещё селект
  ---------

  // overridden:
  * _Preparse( &$tpl_engine )
  * ParseTo( &$tpl_engine )

  // options
  * tpl_groups
  * data
  * data_plain = 0|1 -- флаг того, что дата не заимствуется из message_set
  * sql
  * preface
  * preface_tpl -- если =1, то префейс не приписывается между стрингом и селектом, а ассигнится в _Preface перед селектом
  * add -- дописывать в конец селекта по клику или заменять всё его содержимое?

=============================================================== v.4 (Kuso)
*/

class FieldStringSelect extends FieldString
{

  function FieldStringSelect( &$rh, $config )
  {
    FieldString::FieldString(&$rh, $config);

    // assigning defaults
    if (!isset($this->config["preface"])) $this->config["preface"] = "Form.".$this->config["field"].".Preface";
    if (!isset($this->config["tpl_groups"])) 
      $this->config["tpl_groups"] = "field_radio.html:Select";

  }

  function _Preparse( &$tpl, $tpl_prefix )
  {
    if ($this->config["readonly"]) return;
    FieldString::_Preparse( &$tpl, $tpl_prefix );

    // работа с селектом
    $tpl->Assign( "_StringSelectAdd", 1*$this->config["add"] );
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
      if (isset($this->form->form_config["message_set"]) && !isset($this->config["data_plain"]) ) 
      {
       $data = array();
       foreach ($this->config["data"] as $k=>$item)
        $data[$k] = $tpl->message_set[ $item ];
      } else $data = $this->config["data"];
    }

    $this->sqldata = $data;

    $list = new ListCurrent( &$this->rh, $data, 0, $this->data );
    $list->Parse($tpl_prefix.$this->config["tpl_groups"]."_Groups", "GROUPS");

  }
  function ParseTo( $tpl_prefix, $target_var ) 
  {
    $this->rh->tpl->Assign( "_Name", $this->config["name"] );
    $this->rh->tpl->Assign( "_Desc", $this->config["desc"] );
    if ($this->config["nessesary"]) $this->rh->tpl->Assign( "_Nessesary", $this->rh->tpl->message_set["form_nessesary"] );
    else $this->rh->tpl->Assign( "_Nessesary", "" );

    if ($this->invalid)
    {
      foreach ($this->invalidReasons as $k=>$v)
       if (isset( $this->rh->tpl->message_set[$k] ))
        $this->invalidReasons[$k] = $this->rh->tpl->message_set[$k];

      $errors = &new ListSimple( $this->rh, $this->invalidReasons );
      $errors->Parse( $tpl_prefix."errors.html:List", "_Error", 0 );
    } else $this->rh->tpl->Assign( "_Error", "" );
    $this->_Preparse( &$this->rh->tpl, $tpl_prefix );

    if ($this->config["readonly"])
      $this->rh->tpl->Assign("_Data", $this->_Format( &$this->rh->tpl, $tpl_prefix ));
    else
    {
      $this->rh->tpl->Parse( $tpl_prefix.$this->config["tpl_data"],   "_Data"     );
      if (sizeof($this->sqldata) > 0)
      {
        if ($this->config["preface_tpl"])
           $this->rh->tpl->Assign( "_Preface", $this->rh->tpl->message_set[$this->config["preface"]] );
        else
        if ($this->config["preface"])
          $this->rh->tpl->Append( "_Data", $this->rh->tpl->message_set[$this->config["preface"]] );
        $this->rh->tpl->Parse( $tpl_prefix.$this->config["tpl_groups"]."_Js", "_Data", TPL_APPEND);
      }
    }
    $this->rh->tpl->Parse( $tpl_prefix.$this->config["tpl_row" ], $target_var, 1 );

  }


// EOC { FieldStringSelect }
}


?>