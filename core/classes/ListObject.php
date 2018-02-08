<?php
/*
    ListObject( &$rh, &$data, $cache_id=NULL ) -- �������� � ���-���������. + more
      - $data     -- ������ ������ ��� recordset ��� sql-������, �� ������� ���������� ���������
      - $cache_id -- ���������� �� ���������, � ��� ����� ���������������

  ---------
  * &_ParseOne( $tpl_name, $pos, &$obj, $count=0 ) -- ������ ���� ������� ������
      - $tpl_name -- ��� ������� "filename.html:List_Item"
      - $pos      -- ���� �������� � ������ (�� ����������� �����)
      - $obj      -- �������: ��� ������ {href,text,title} ��� ������ (� ��������� ������ _Href=$pos)
      - $count    -- ����� �������� � ������ (�����, ��������� � ����)

=============================================================== v.4 (Kuso)
*/

class ListObject extends ListSimple
{
  // �������������
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