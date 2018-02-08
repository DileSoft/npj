<?php
/*
    FieldRelations( &$rh, $config ) -- отношение "многие ко многим" (типа "опубликовать в сообществах:")
  ---------

  // special?

  // overridden:
  * _Load
  - _RestoreFromDb
  - _StoreToDb
  * _Preparse
  * CreateUPDATE
  * CreateSELECT

  // options
  * data( id=>value )
    * OR sql ( "select .. as id, .. as value from .. " )
    * OR target ( "table" =>, "id"=>"id", "value"=>"name", "order"=>"name asc" )
  * mode = "owner" | "item"
  * helper = "r1_subjects_relations"
    * OR helper ( "table" =>, "item"=>"item_id", "owner"=>"owner_id", "filter"=>"filter" )
  * default ( id, id, id )
  * box_size = 5 , box_size_all
  * max_size
  * filter , filter2

=============================================================== v.0 (Kuso)
*/

class FieldRelations extends Field
{

  function FieldRelations( &$rh, $config )
  {
    Field::Field(&$rh, $config);
    // assigning defaults
    if (!isset($this->config["box_size"])) $this->config["box_size"] = "5";
    if (!isset($this->config["box_size_all"])) $this->config["box_size_all"] = $this->config["box_size"];
    if (!isset($this->config["mode"])) $this->config["mode"] = "item";
    if (isset($this->config["target"]))
     if (!is_array($this->config["target"]))
      $this->config["target"] = array("table"=>$this->config["target"], 
                                      "id"=>"id", "value"=>"name", "order" => "name asc");
    if (!is_array($this->config["helper"]))
     $this->config["helper"] = array("table"=>$this->config["helper"], 
                                     "item"=>"item_id", "owner"=>"owner_id", 
                                     "filter"=>"filter", "filter2"=>"filter2");
    if (!isset($this->config["tpl_data"])) $this->config["tpl_data"] = "field_relations.html:Plain";
  }

  // получение из формы
  function _Load( &$data ) 
  { 
    $this->db_data2 = &$data[ "_items_in_".$this->config["field"]];
    $this->data = explode("|", $this->db_data2); 
  }
  // получение из БД
  function _RestoreFromDb( &$data, $skip_char="" ) 
  { 
    if (isset($this->config["filter"])) 
    {  $filter = " and ".$this->config["helper"]["filter"].
                 " = ".$this->rh->db->Quote($this->config["filter"]);
    }
    if (isset($this->config["filter2"])) 
    {  $filter .= " and ".$this->config["helper"]["filter2"].
                  " = ".$this->rh->db->Quote($this->config["filter2"]);
    }
    $from_field = ($this->config["mode"] == "item")?"item" :"owner";
    $to_field   = ($this->config["mode"] == "item")?"owner":"item" ;
    $from_field = $this->config["helper"][$from_field];
    $to_field   = $this->config["helper"][$to_field];

    $sql = "select ".$to_field." as to_field from ".$this->config["helper"]["table"]." where ".
           $from_field."=".$this->rh->db->Quote($this->form->data_id).$filter;
    $rs  = $this->rh->db->Execute( $sql );
    $a   = $rs->GetArray();
    $this->data = array();
    foreach( $a as $k=>$v ) $this->data[] = $v["to_field"];
  }
  // сохранение в БД, реально делается после обработки хандлера формы
  function _StoreToDb() {}
  // правка id у сохранённых в хелперной таблице, если так (вот оно реальное сохранение)
  function AfterHandler()
  {
    Field::AfterHandler();

    //$this->rh->debug->Error($this->form->data_id);
    if ($this->form->success && $this->form->data_id)
    {
      if (isset($this->config["filter"])) 
      {  $filter = " and ".$this->config["helper"]["filter"].
                   " = ".$this->rh->db->Quote($this->config["filter"]);
         $filter1 = ",".$this->config["helper"]["filter"];
         $filter2 = ",".$this->rh->db->Quote($this->config["filter"]);
      }
      if (isset($this->config["filter2"])) 
      {  $filter .= " and ".$this->config["helper"]["filter2"].
                   " = ".$this->rh->db->Quote($this->config["filter2"]);
         $filter1 .= ",".$this->config["helper"]["filter2"];
         $filter2 .= ",".$this->rh->db->Quote($this->config["filter2"]);
      }
      $from_field = ($this->config["mode"] == "item")?"item" :"owner";
      $to_field   = ($this->config["mode"] == "item")?"owner":"item" ;
      $from_field = $this->config["helper"][$from_field];
      $to_field   = $this->config["helper"][$to_field];
      // 1. очистить записи в таблице helper
      if ($this->form->data_id)
      {
        $sql = "delete from ".$this->config["helper"]["table"]." where ".$from_field."=".
               $this->rh->db->Quote($this->form->data_id).$filter;
        $this->rh->db->Execute( $sql );
      }
      if (!is_array($this->data) || !sizeof($this->data)) return;
      // 2. построить sql для занесения новых
      $from_id = $this->rh->db->Quote($this->form->data_id); 
      $sqls = array();
      foreach( $this->data as $k=>$v ) $sqls[]="(".$from_id.", ".$this->rh->db->Quote($v).$filter2.")";
      // 3. занести их
      $sql  = "insert into ".$this->config["helper"]["table"]." ($from_field,$to_field".$filter1.") VALUES ".
              implode(",", $sqls);
      $this->rh->db->Execute( $sql );
    }
  }

  
  // никакого влияния на sql-запросы поле не должно оказывать
  function CreateSELECT() { return ""; }
  function CreateUPDATE() { return ""; }

  function _PrepareData()
  {
    if (isset($this->config["target"]))
    {
      $this->config["sql"] = "select ".$this->config["target"]["id"]." as id, ".
                                       $this->config["target"]["value"]." as value ".
                             " from  ".$this->config["target"]["table"].
                             " order by ".$this->config["target"]["order"];
    }
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
      if (!isset($this->config["data"]))   
        $data = $tpl->message_set[ "Form.".$this->config["field"].".Data" ];
      else
        if (isset($this->form->form_config["message_set"])) 
        {
         $data = array();
         foreach ($this->config["data"] as $k=>$item)
          $data[$k] = $tpl->message_set[ $item ];
        } 
        else $data = $this->config["data"];
   return $data;
  }
  function _PrepareValues( $data )
  {
    $values = array();
    if (!is_array($this->data)) return $values;
    foreach( $this->data as $v )
     if (isset($data[$v])) $values[$v]=$v;
    return $values;
  }

  function _Preparse( &$tpl, $tpl_prefix )
  {
    if ($this->config["readonly"]) return;

    // !!! refactor
    if (isset($this->config["title"]))
      $tpl->Assign("_LeftSubject", $this->config["title"]);
    else
      $tpl->Assign("_LeftSubject", $tpl->message_set[ "Form.".$this->config["field"].".LeftSubject" ]);
    if (isset($this->config["title_all"]))
      $tpl->Assign("_RightSubject", $this->config["title_all"]);
    else
      $tpl->Assign("_RightSubject", $tpl->message_set[ "Form.".$this->config["field"].".RightSubject" ]);

    $this->rh->UseClass("ListSimple", $this->rh->core_dir);
    $this->rh->UseClass("ListCurrent", $this->rh->core_dir);

    $data   = $this->_PrepareData();
    $values = $this->_PrepareValues($data);
    // подготовка двух списков
    $_data = array();  $__data = array();
    $_data_all = array();
    foreach( $data as $k=>$v)
    {
         $o = array(
                "href" => $k,
                "text" => $v,
                    );
         if ($values[$k]) { $_data[]     = $o; $__data[]=$k; }
         else             { $_data_all[] = $o;               }
    }
    $list     = &new ListSimple( &$this->rh, $_data );
    $list_all = &new ListSimple( &$this->rh, $_data_all );
    $tpl->Assign( "_ItemsIn", implode("|",   $__data ) );

    // уже парсинг и assigns
    $tpl->Assign( "_Field", "_".$this->config["field"] );
    $tpl->Assign("_Field", "_".$this->config["field"] );
    $tpl->Assign("_Size",    $this->config["box_size"] );
    $tpl->Assign("_MaxSize", $this->config["max_size"] );
    $tpl->Assign("_Type", "in");
    $list->Parse($tpl_prefix.$this->config["tpl_data"]."_Items",    "ITEMS", 0);
    $tpl->Assign("_Type", "out");
    $tpl->Assign("_Size", $this->config["box_size_all"] );
    $list_all->Parse($tpl_prefix.$this->config["tpl_data"]."_Items",    "ITEMS_ALL", 0);
  }
  function _Format() { return $this->config["data"][ $this->data ]; }

// EOC { FieldRelations }
}


?>