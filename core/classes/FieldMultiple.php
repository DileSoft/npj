<?php
/*
    FieldMultiple( &$rh, $config ) -- выбор до нескольких элементов, или ниодного или все
  ---------

  // overridden:
  * Validate()
  * _Preparse( &$tpl_engine )
  * Format() -- !!! no nice output yet

  // options
  * data_plain = 0|1 -- флаг того, что дата не заимствуется из message_set
  * data ( id => name )
  * size -- размер окна селекта
  * size_all -- размер окна селекта (списка всех, если отсутствует, то равен сайзу
  * db_separator = "|"
  ? default ( id1, id2, id3 )
  * presets ( id => name ) // 0 - nothing, -1 -- any, other -- specific.
  * presets_block ( id, id, id )
  - maxsize

  [to be supplied.]

=============================================================== v.3 (Kuso)
*/

class FieldMultiple extends Field
{

  function FieldMultiple( &$rh, $config )
  {
    Field::Field(&$rh, $config);
    // assigning defaults
    if (!isset($this->config["tpl_data"])) $this->config["tpl_data"] = "field_multiple.html:Plain";
    if (!isset($this->config["default"])) $this->config["default"] = 0;
    if (!isset($this->config["db_separator"])) $this->config["db_separator"] = "|";
    if (!isset($this->config["size"])) $this->config["size"] = 5;
    if (!isset($this->config["size_all"])) $this->config["size_all"] = $this->config["size"];
    
  }

  // проверка на различные ошибки
  function Validate()   
  { 
    $config = &$this->config;
    $data = &$this->data;
    Field::Validate();
    $this->invalid = sizeof($this->invalidReasons) != 0;
    return !$this->invalid; 
  }

  // работа с биде
  function CreateSELECT() { return $this->config["field"]; }
  function CreateUPDATE() { $this->_StoreToDb(); 
                            return $this->config["field"]."=". $this->rh->db->Quote($this->db_data); }
  function SetDefault() { $this->data = $this->config["default"]; return true; }


  // запись/получение из формы/бд
  function _StoreToDb() 
  { if (is_array($this->data)) $this->db_data = implode($this->config["db_separator"], $this->data); 
    else $this->db_data = $this->data; 
  }
  function _Load( &$data ) { $this->_RestoreFromDb( &$data, "_" ); }
  function _RestoreFromDb( &$data, $skip_char="" ) 
  { 
    if ($this->config["db_ignore"] && ($skip_char == "")) return;

    $this->db_data = &$data[ $skip_char.$this->config["field"]];
    $this->db_data2 = &$data[ $skip_char."items_in_".$this->config["field"]];
    if ($this->db_data == -10)
      $this->data = explode($this->config["db_separator"], $this->db_data2); 
    else
      $this->data = array($this->db_data); 
  }

  function _Preparse( &$tpl, $tpl_prefix )
  {
    if ($this->config["readonly"]) return;

    $tpl->Assign("_LeftSubject", $tpl->message_set[ "Form.".$this->config["field"].".LeftSubject" ]);
    $tpl->Assign("_RightSubject", $tpl->message_set[ "Form.".$this->config["field"].".RightSubject" ]);

    // основные элементы
    $this->rh->UseClass("ListSimple", $this->rh->core_dir);
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
    
    $_data = array();  $__data = array();
    $_data_all = array();
    foreach( $data as $k=>$v)
    {
         $o = array(
                "href" => $k,
                "text" => $v,
                    );
         if ((is_array($this->data) && in_array($k, $this->data))?" SELECTED ":"")
         {
           $_data[] = $o;
           $__data[] = $k;
         }
         else
          $_data_all[] = $o; 
    }

    $list = new ListSimple( &$this->rh, $_data );
    $list_all = new ListSimple( &$this->rh, $_data_all );

    $tpl->Assign( "_ItemsIn", implode($this->config["db_separator"], $__data ) );

    // пресеты
    $rf___data = $this->__BuildPresetsArray();
    $data21 = $rf___data["data21"];
    $_data2 = $rf___data["_data2"];
    $data2  = $rf___data["data2"];
    $list2  = $rf___data["list2"];

    // уже парсинг и assigns
    if ($_data2 == -10) $tpl->Assign("_ItemsVisible", "block");
    else $tpl->Assign("_ItemsVisible", "none");
    $tpl->Assign("_Field", "_".$this->config["field"] );
    $tpl->Assign("_Size", $this->config["size"] );
    $tpl->Assign("_MaxSize", $this->config["maxsize"] );
    $tpl->Assign("_Type", "in");
    $list->Parse($tpl_prefix.$this->config["tpl_data"]."_Items",    "ITEMS", 0);
    $tpl->Assign("_Type", "out");
    $tpl->Assign("_Size", $this->config["size_all"] );
    $list_all->Parse($tpl_prefix.$this->config["tpl_data"]."_Items",    "ITEMS_ALL", 0);
    $list2->Parse($tpl_prefix.$this->config["tpl_data"]."_Presets", "PRESETS", 0);
    // !!!! нужно допатчить список пресетов и кустомный пресет, который и показывает ITEMS
  }

  function __BuildPresetsArray()
  {
    if (is_array($this->data)) $_data2 = -10;
    else $_data2 = $this->data;
    if (!isset($this->config["presets"]))   
      $data2 = $this->rh->tpl->message_set[ "Form.".$this->config["field"].".Presets" ];
    else 
      $data2 = $this->config["presets"];
    $data21 = array();

    if (is_array($this->config["presets_block"]))
      foreach($this->config["presets_block"] as $v)
      {
        unset($data2[$v]);
      }

    foreach( $data2 as $k=>$v)
    {
         $o = array(
                "href" => $k,
                "text" => $v,
                "title" => ($k==-10?"block":"none"),
                    );
         $data21[] = $o; 
    }

    $list2 = new ListCurrent( &$this->rh, $data21, "href", $_data2 );

    return array( "list2"  => $list2,
                  "data21" => $data21, 
                  "_data2" => $_data2, 
                  "data2"  => $data2 );
  }

  function _Format() 
  { 
    // !!! не забыть переделать на какой-то красивый вывод
    $this->_StoreToDb();
    return $this->db_data; 
  }

// EOC { FieldMultiple }
}


?>