<?php
/*
    ListSimple( &$rh, &$data, $cache_id=NULL, $more_fields = false ) -- самый примитивный класс работы со списком
      - $data     -- массив списка или recordset или sql-строка, по которой получается рекордсет
      - $cache_id -- кэшировать ли результат, и под каким идентификатором
      - $more_fields -- массив, в котором можно задать доп. поля для парсинга

  ---------
  * &Parse( $tpl_root, $store_to, $append ) -- отпарсить по коллекции шаблонок
      - $tpl_root     -- корневая шаблонка списка, задаём как "file.html:List"
      - $store_to    -- если установлено, то результат также сохраняется в переменную домена с таким именем
                        если === true, то записывает результат прямо туда, где был задан шаблон
      - $append      -- если непустое $store_to, то результат не стирает значение переменной, а дописывается в конец

  // Для override рекомендуется:
  * &_ParseOne( $tpl_name, $pos, $obj, $count=0 ) -- парсит один элемент списка
      - $tpl_name -- имя шаблона "filename.html:List_Item"
      - $pos      -- ключ элемента в списке (не обязательно число)
      - $obj      -- элемент: или массив {href,text,title} или строка (в последнем случае _Href=$pos)
      - $count    -- номер элемента в списке (число, нумерация с нуля)

  // Важные свойства
  * $this->implode -- в таком случае список собирается как *_Item  *_Separator  *_Item  *_Separator  *_Item

=============================================================== v.2 (Kuso)
*/

class ListSimple
{
  var $rh;
  var $tpl;
  var $data;
  var $cache_id;
                                                      
  function ListSimple( &$rh, &$data, $cache_id=NULL, $more_fields = false )
  {
    $this->rh = &$rh;
    $this->tpl = &$rh->tpl;
    $this->cache_id = $cache_id;
    $this->more_fields = $more_fields;

    if ($cache_id === NULL) $this->data = false; else
     $this->data = $rh->cache->Restore( get_class($this), $cache_id );
    if ($this->data === false)
    {
      if (is_array($data)) $this->data = &$data;
      else
      {
        if (is_string($data)) $data = $rh->db->Execute( $data );
        $this->data = &$data->fields; // !!!! seems errorful
      }
      if ($cache_id === NULL) ; else
       $rh->cache->Store( get_class($this), $cache_id, 2, &$this->data );
    }
  }

  // отпарсить по коллекции шаблонок, корень задаём как file.html:Menu
  function &Parse( $tpl_root, $store_to=NULL, $append=0 )
  {
    $tpl_empty   = $tpl_root."_Empty";
    $tpl_item    = $tpl_root."_Item";

    if (sizeof($this->data) == 0) 
      return $this->tpl->Parse( $tpl_empty, $store_to, $append );

    $data = ""; $count=0;
    if ($this->implode)
    { $_data = array();
      foreach ( $this->data as $pos=>$value)
       $_data[] = $this->_ParseOne( $tpl_item, $pos, &$value, $count++ );
      $data = implode( $this->tpl->Parse( $tpl_root."_Separator" ), $_data );
    }
    else
    {
      foreach ( $this->data as $pos=>$value)
        $data.=$this->_ParseOne( $tpl_item, $pos, &$value, $count++ );
    }

    $this->tpl->Assign( "@".$tpl_item, $data );
    
    $result = $this->tpl->Parse( $tpl_root, $store_to, $append );
    if ($store_to === true)
    {
      $this->tpl->Assign( "@".$tpl_root, $result );
      $this->tpl->Assign( "@".$tpl_root."_Separator", "" );
      $this->tpl->Assign( "@".$tpl_root."_Item", "" );
      $this->tpl->Assign( "@".$tpl_root."_Empty", "" );
    }
    return $result;
  }

  // перегружаемое
  function &_ParseOne( $tpl_name, $pos, &$obj, $count=0 )
  {
    if (!is_array($obj)) 
    {  
      $this->tpl->Assign("_Count", $count );
      $this->tpl->Assign("_Href", $pos );
      $this->tpl->Assign("_Text", $obj );
    }
    else
    {
      $this->tpl->Assign("_Count", $count );
      $this->tpl->Assign("_Href",  $obj["href"] );
      $this->tpl->Assign("_Text",  $obj["text"] );
      $this->tpl->Assign("_Title", $obj["title"] );
      if (is_array( $this->more_fields ))
        foreach($this->more_fields as $field )
         $this->tpl->Assign($field, $obj[$field] );
    }
    return $this->tpl->Parse( $tpl_name );
  } 


// EOC { List }
}


?>