<?php
/*
    ListCurrent2( &$rh, &$data, $key=0, $value, $cache_id=NULL ) -- �������� �������� ������� �������
      - $data     -- ������ ������ ��� recordset ��� sql-������, �� ������� ���������� ���������
      - $key      -- ���� � $data[$pos], �� �������� ������������ ������� �������, ���� ������, �� ������������ ���������� �����
      - $value    -- �������� ����� �����, ������� �������� �������
      - $cache_id -- ���������� �� ���������, � ��� ����� ���������������
      - ��������� �� ((ListCurrent))

  ---------
  // �����������
  * &Parse( $tpl_root, $store_to, $append ) -- ��������� �� ��������� ��������

  // ��� override �������������:
  * &_ParseOne( $tpl, $pos, $obj, $count=0 ) -- ������ ���� ������� ������

=============================================================== v.1 (Kuso)
*/

class ListCurrent2 extends ListCurrent
{

  // ��������� �� ��������� ��������, ������ ����� ��� file.html:Menu
  function &Parse( $tpl_root, $store_to, $append=0 )
  {
    $tpl_empty           = $tpl_root."_Empty";
    $tpl_item            = $tpl_root."_Item";
    $tpl_current_item    = $tpl_root."_Item_Current";
    $tpl_current_item2   = $tpl_root."_Item_Current_Next";

    if (sizeof($this->data) == 0) 
      return $this->rh->tpl->Parse( $tpl_empty, $store_to, $append );

    $data = ""; $count=0; $cc=-2;

    foreach ( $this->data as $pos=>$value)
    {
      if (in_array($pos,$this->current_pos)) $cc = $count;
      $data.=$this->_ParseOne( (in_array($pos,$this->current_pos)?$tpl_current_item:
                                 (($cc == $count-1)?$tpl_current_item2:$tpl_item)), 
                                 $pos, $value, $count++ );
    }

    $this->rh->tpl->Assign( "@".$tpl_item, $data );
    return $this->rh->tpl->Parse( $tpl_root, $store_to, $append );
  }

  // ������������� ������� �� ��:
  // function _ParseOne( $tpl, $pos, $obj, $count=0 )


// EOC { List }
}


?>