<?php
/*
    ListCurrentLast( &$rh, &$data, $key=0, $value, $cache_id=NULL ) -- �������� �������� ������� �������
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

class ListCurrentLast extends ListCurrent
{
  // ��������� �� ��������� ��������, ������ ����� ��� file.html:Menu
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

  // ������������� ������� �� ��:
  // function _ParseOne( $tpl, $pos, $obj, $count=0 )


// EOC { ListCurrentLast }
}


?>