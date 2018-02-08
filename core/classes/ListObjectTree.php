<?php
/*
    ListObjectTree( &$rh, &$data, $cache_id=NULL ) -- �������� � ���-���������, ������ �������� �������
      - $data     -- ������ ������ ��� recordset ��� sql-������, �� ������� ���������� ���������
      - $cache_id -- ���������� �� ���������, � ��� ����� ���������������
      - "more", "lft_id", "rgt_id" -- � ��
      - "_depth" -- ����� ������� ��������� lft/rgt, ���� �� ��� �� ������ ��������� ������
      - "_Depth" = 0, 20, 40, ..;  "_Level" = 0, 1, 2
      - ��������� �� ((ListObject))

  ---------
  * &Parse( $tpl_root, $store_to=NULL, $append=0 ) -- ������ ������ �������
  * &_ParseOne( $tpl_name, $pos, $obj, $count=0 ) -- ������ ���� ������� ������
      - $tpl_name -- ��� ������� "filename.html:List_Item" / "_Item_2"
      - $pos      -- ���� �������� � ������ (�� ����������� �����)
      - $obj      -- �������: ��� ������ {href,text,title} ��� ������ (� ��������� ������ _Href=$pos)
      - $count    -- ����� �������� � ������ (�����, ��������� � ����)

  // �������� �������
  * $this->level_depth = 20 -- ��������� ��� ����� ""{{_Depth}}"", ����� ��������� ������
  * $this->item2_level = 0  -- �� ����� ������ ������ ���������� �� ������ ��� �������

=============================================================== v.2npj (Kuso)
*/

class ListObjectTree extends ListObject
{
  var $level_depth=20; // ��������� ��� ""{{_Depth}}""
  var $item2_level=0;  // ����� ���������� �� ""{{TEMPLATE:List_Item_2}}""

  // �������������
  function &Parse( $tpl_root, $store_to=NULL, $append=0 )
  {
    $tpl_empty   = $tpl_root."_Empty";
    $tpl_item    = $tpl_root."_Item";
    if ($this->item2_level) $tpl_item2   = $tpl_root."_Item_2";
    else                    $tpl_item2   = $tpl_root."_Item";

    if (sizeof($this->data) == 0) 
      return $this->rh->tpl->Parse( $tpl_empty, $store_to, $append );

    $data = "";
    $count=0;
    $depth=-1; $prev_r=0;
    foreach ( $this->data as $pos=>$value)
    {
      $distance = $value["lft_id"] - $prev_r -1;
      if ($depth<0) $depth=0; else
      if ($distance < 0) $depth ++; else
       if ($distance > 0) $depth -= $distance; 
      $prev_r = $value["rgt_id"];

      if (isset($value["_depth"]))
       $depth = $value["_depth"];

      // [!!!!] �� �� ����� ����������� ���, ��, �������, 
      //        ����� ��� � ��� �� ��� �� �����������
      if (isset($this->data[$pos+1]["_depth"]))
       $is_last = $depth >= $this->data[$pos+1]["_depth"];
      else $is_last = 1;

      $this->rh->tpl->Assign("_Level", $depth );
      $this->rh->tpl->Assign("_Depth", $depth*$this->level_depth );
      $this->rh->tpl->Assign("_Is_Terminator", ($depth==$this->item2_level-1) && !$is_last );
      $data.=$this->_ParseOne( (($depth>=$this->item2_level)?$tpl_item2:$tpl_item), 
                               $pos, &$value, $count++ );
    }

    $this->rh->tpl->Assign( "@".$tpl_item, $data );
    
    $result = $this->rh->tpl->Parse( $tpl_root, $store_to, $append );
    if ($store_to === true)
    {
      $this->rh->tpl->Assign( "@".$tpl_root, $result );
      $this->rh->tpl->Assign( "@".$tpl_root."_Item", "" );
      $this->rh->tpl->Assign( "@".$tpl_root."_Empty", "" );
    }
    return $result;
  }

// EOC { ListObjectTree }
}


?>