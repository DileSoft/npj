<?php
/*
    FieldDump( &$rh, $config ) -- делаем дамп массива в таблицу
  ---------

  // overridden:
  - AfterHandler()

  // options
  * $this->data = ( rows ( key=>value ) )
  * dump_keys ( key )
  * strict_keys ( key => value )
  * db_target
  * parent_id
  * always_insert
  * db_ignore
  * after_handler -- ignore db_ignore, readonly in AfterHandler

  * tpl_readonly = "field_dump.html:Grid"
  * grid_keys = ( key => name )
  * order_column


=============================================================== v.1 (Kuso)
*/

class FieldDump extends Field
{

  function FieldDump( &$rh, $config )
  {
    Field::Field(&$rh, $config);
    // assigning defaults
    if (!isset($this->config["tpl_data"])) $this->config["tpl_data"] = "field_string.html:Hidden";
    if (!isset($this->config["strict_keys"])) $this->config["strict_keys"] = array();
    if (!isset($this->config["order_column"])) $this->config["order_column"] = "pos";
  }


  function AfterHandler()
  { $db = &$this->rh->db;
    $parent  = $this->form->data_id;

    Field::AfterHandler();

    if (!$this->config["after_handler"])
    {
      if ($this->config["db_ignore"]) return;
      if ($this->config["readonly"]) return;
    }
    // 1. remove oldies
    if (!$this->config["always_insert"])
    {
      $db->Execute( "delete from ". $this->config["db_target"] . " where ". $this->config["parent_id"].
                    " = ". $db->Quote( $parent ) );
    }
    // 2. построим базу SQL-запроса
    $keys = $this->config["dump_keys"];
    $this->config["strict_keys"][ $this->config["parent_id"] ] = $parent;
    foreach($this->config["strict_keys"] as $k=>$v)
     if (!in_array($k, $keys)) $keys[] = $k;
    $sql = "insert into ". $this->config["db_target"] . " (". implode(",",$keys) .") VALUES ";
    // 3. цепл€ем пол€
    $f=0;
    foreach ($this->data as $i=>$row)
    {
      if ($f) $sql.=", "; else $f=1;
      $sql.="(";
      $ff=0;
      foreach($keys as $j=>$key)
      {
        if ($ff) $sql.=", "; else $ff=1;
        if ($this->config["strict_keys"][$key]) $value = $this->config["strict_keys"][$key];
        else                                    $value = $row[$key];  
        $sql.= $db->Quote($value);
      }
      $sql.=")";
    }
    // 4. сохран€ем
    $db->Execute( $sql );
  }


  // получение из формы -- пока не умеем
  // получение из Ѕƒ -- пока не умеем, ща научимс€
  function _StoreToDb() { $this->db_data = 1; }
  function _RestoreFromDb( &$data, $skip_char="" ) 
  { $db = &$this->rh->db;
    if (!isset($this->form->data_id)) return;
    $rset = $db->Execute( "select * from ". $this->config["db_target"]." where ". $this->config["parent_id"].
                    " = ". $db->Quote( $this->form->data_id ). " order by ".$this->config["order_column"] );
    $this->data = $rset->GetArray();
  }


  // отображение -- пока самое простое
  function _Format( &$tpl, $tpl_prefix ) 
  { 
    if (isset($this->config["tpl_readonly"]))
    { 
      // подготовить заголовок
      $list = &new ListSimple( &$this->rh, &$this->config["grid_keys"] );
      $list->Parse( $this->form->form_config["tpl_prefix"].$this->config["tpl_readonly"]."_Rows", "GRID_HEAD" );
      // подготовить тело
      $this->rh->tpl->Assign("GRID_ROWS", "");
      if (is_array($this->data))
      foreach ($this->data as $k=>$row)
      {
        $cols = array();
        foreach($this->config["grid_keys"] as $key=>$v)
         $cols[$key] = $row[$key];
        $list = &new ListSimple( &$this->rh, &$cols );
        $list->Parse( $this->form->form_config["tpl_prefix"].$this->config["tpl_readonly"]."_Rows", "GRID_ROWS", 1 );
      }
      // итоговый парсинг
      $result = $this->rh->tpl->Parse( $this->form->form_config["tpl_prefix"].$this->config["tpl_readonly"] );
    }
    else
    {
      $result = "<ul class=\"field-dump-\">";
      foreach( $this->data as $row )
       $result.="<li>".$row["name"]."</li>";
      $result.= "</ul>";
      $tpl->Assign( "_Value", $result );
    }
    return $result; 

  }

// EOC { FieldDump }
}


?>  