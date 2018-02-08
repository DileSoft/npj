<?php
/*
    ListObject( &$rh, &$data, $cache_id=NULL ) -- работаем с хэш-массивами. + more
      - $data     -- массив списка или recordset или sql-строка, по которой получается рекордсет
      - $cache_id -- кэшировать ли результат, и под каким идентификатором

  ---------
  * &_ParseOne( $tpl_name, $pos, &$obj, $count=0 ) -- парсит один элемент списка
      - $tpl_name -- имя шаблона "filename.html:List_Item"
      - $pos      -- ключ элемента в списке (не обязательно число)
      - $obj      -- элемент: или массив {href,text,title} или строка (в последнем случае _Href=$pos)
      - $count    -- номер элемента в списке (число, нумерация с нуля)

=============================================================== v.4 (Kuso)
*/

class ListObject extends ListSimple
{
  // перегружаемое
  function &_ParseOne( $tpl_name, $pos, &$obj, $count=0 )
  {
    $this->tpl->Assign("_Count",  $count );
    foreach( $obj as $key=>$value )
    if (!is_numeric($key))
      if ($key == "more")
        $this->tpl->Parse($value,  $key );
      else
      if (!is_array($value))
        $this->tpl->Assign($key,  &$value );

    return $this->tpl->Parse( $tpl_name );
  } 


// EOC { ListObject }
}


?>