<?php
/*
    ListCurrentLast( &$rh, &$data, $key=0, $value, $cache_id=NULL ) -- отдельно выделяем текущий элемент
      - $data     -- массив списка или recordset или sql-строка, по которой получается рекордсет
      - $key      -- ключ в $data[$pos], по которому определяется текущий элемент, если пустое, то используется порядковый номер
      - $value    -- значение этого ключа, которое является текущим
      - $cache_id -- кэшировать ли результат, и под каким идентификатором
      - наследует от ((ListCurrent))

  ---------
  // перегружено
  * &Parse( $tpl_root, $store_to, $append ) -- отпарсить по коллекции шаблонок

  // для override рекомендуется:
  * &_ParseOne( $tpl, $pos, $obj, $count=0 ) -- парсит один элемент списка

=============================================================== v.1 (Kuso)
*/

class ListCurrentLast extends ListCurrent
{
  // отпарсить по коллекции шаблонок, корень задаём как file.html:Menu
  function &Parse( $tpl_root, $store_to=NULL, $append=0 )
  {
    $tpl_empty           = $tpl_root."_Empty";
    $tpl_item            = $tpl_root."_Item";
    $tpl_current_item    = $tpl_root."_Item_Current";

    if (sizeof($this->data) == 0) 
      return $this->rh->tpl->Parse( $tpl_empty, $store_to, $append );

    $data = ""; $count=0;
    foreach ( $this->data as $pos=>$value)
      $data.=$this->_ParseOne( 
                                (in_array($pos,$this->current_pos)?$tpl_current_item:$tpl_item).
                                ($count+1 == sizeof($this->data)?"_Last":"")
                             , $pos, $value, $count++ );

    $this->rh->tpl->Assign( "@".$tpl_item, $data );
    return $this->rh->tpl->Parse( $tpl_root, $store_to, $append );
  }

  // перегружаемая функция та же:
  // function _ParseOne( $tpl, $pos, $obj, $count=0 )


// EOC { ListCurrentLast }
}


?>