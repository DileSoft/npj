<?php
/*
    ButtonList( &$rh, &$data ) -- класс для вывода кнопочек
      - $data     -- массив списка или recordset или sql-строка, по которой получается рекордсет

  ---------

  * _ParseOne( $tpl_name, $pos, $obj ) -- парсит один элемент списка

=============================================================== v.1 (Kuso)
*/

class ButtonList extends ListSimple
{
  // перегружаемое
  function &_ParseOne( $tpl_name, $pos, $obj )
  {
    $this->rh->tpl->Assign("Value",  $obj["name"] );
    $this->rh->tpl->Assign("Field",  "__button" );
    return $this->rh->tpl->Parse( $obj["tpl_name"].$this->postfix );
  } 


// EOC { ButtonList }
}

?>