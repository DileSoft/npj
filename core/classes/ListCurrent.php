<?php
/*
    ListCurrent( &$rh, &$data, $key=0, $value, $cache_id=NULL, $more_fields=false ) -- �������� �������� ������� �������
      - $data     -- ������ ������ ��� recordset ��� sql-������, �� ������� ���������� ���������
      - $key      -- ���� � $data[$pos], �� �������� ������������ ������� �������, ���� ������, �� ������������ ���������� �����
      - $value    -- �������� ����� �����, ������� �������� �������
      - $cache_id -- ���������� �� ���������, � ��� ����� ���������������
      - $more_fields -- ������, � ������� ����� ������ ���. ���� ��� ��������
      - ��������� �� ((ListSimple))

  ---------
  // ����������� (overriden)
  * &Parse( $tpl_root, $store_to, $append ) -- ��������� �� ��������� ��������
      - $tpl_root     -- �������� �������� ������, ����� ��� "file.html:Menu"
      - $store_to    -- ���� �����������, �� ��������� ����� ����������� � ���������� ������ � ����� ������
      - $append      -- ���� �������� $store_to, �� ��������� �� ������� �������� ����������, � ������������ � �����

  // ��� ����������� override �������������:
  * &_ParseOne( $tpl, $pos, $obj, $count=0 ) -- ������ ���� ������� ������

=============================================================== v.1 (Kuso)
*/

class ListCurrent extends ListSimple
{
  var $rh;
  var $data;
  var $cache_id;
  var $current_pos;

  function ListCurrent( &$rh, &$data, $key, $value, $cache_id=NULL, $more_fields=false )
  {
    $this->ListSimple( &$rh, &$data, $cache_id, $more_fields );
    if (!$key) $this->current_pos = array($value);
    else
    {
      $this->current_pos = array();
      foreach( $this->data as $pos=>$v )
      if ($v[$key] == $value) { $this->current_pos[] = $pos; }
    }
  }

  // ��������� �� ��������� ��������, ������ ����� ��� file.html:Menu
  function &Parse( $tpl_root, $store_to=NULL, $append=0 )
  {
    $tpl_empty           = $tpl_root."_Empty";
    $tpl_item            = $tpl_root."_Item";
    $tpl_current_item    = $tpl_root."_Item_Current";

    if (sizeof($this->data) == 0) 
      return $this->tpl->Parse( $tpl_empty, $store_to, $append );

    $data = ""; $count=0;
    if ($this->implode)
    { $_data = array();
      foreach ( $this->data as $pos=>$value)
       $_data[] = $this->_ParseOne( (in_array($pos,$this->current_pos)?$tpl_current_item:$tpl_item), $pos, $value, $count++ );
      $data = implode( $this->tpl->Parse( $tpl_root."_Separator" ), $_data );
    }
    else
    {
      foreach ( $this->data as $pos=>$value)
        $data.=$this->_ParseOne( (in_array($pos,$this->current_pos)?$tpl_current_item:$tpl_item), $pos, $value, $count++ );
    }
    
    $this->tpl->Assign( "@".$tpl_item, $data );
    return $this->tpl->Parse( $tpl_root, $store_to, $append );
  }

  // ������������� ������� �� ��:
  // function _ParseOne( $tpl, $pos, $obj, $count=0 )


// EOC { List }
}


?>