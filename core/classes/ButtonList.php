<?php
/*
    ButtonList( &$rh, &$data ) -- ����� ��� ������ ��������
      - $data     -- ������ ������ ��� recordset ��� sql-������, �� ������� ���������� ���������

  ---------

  * _ParseOne( $tpl_name, $pos, $obj ) -- ������ ���� ������� ������

=============================================================== v.1 (Kuso)
*/

class ButtonList extends ListSimple
{
  // �������������
  function &_ParseOne( $tpl_name, $pos, $obj )
  {
    $this->rh->tpl->Assign("Value",  $obj["name"] );
    $this->rh->tpl->Assign("Field",  "__button" );
    return $this->rh->tpl->Parse( $obj["tpl_name"].$this->postfix );
  } 


// EOC { ButtonList }
}

?>