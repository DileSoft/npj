<?php

 // �������� add.php

 // ���� $this->params[0], ��� �������� ������������
 // ���� $_POST["_group_XX"], ��� �� -- ������� �����, ���� ���� ��������
 // ���� $groups[ is_system ][ group_rank ][ group_id ], ��� ���� ������ �����
 // ���� $user -- ���, ���� ���������
 // ���� $account -- ���, ���� ���������
 // ���� $udata, $data -- ������ �������������� ����� � ��������

 // ��� 1. �������� �� ������ � ��������� �� �����
 $old = array(); $new = array(); $ranks = array();
 for ($i=1; $i>=0; $i--)
  foreach ($groups[$i] as $r=>$group_rank)
   foreach ($group_rank as $id=>$group)
   {
     if ($i == 1) $ranks[$r]=1; 
     if ($ranks[$r])
      if ($_POST["_group_".$id]) $new[] = $id; 
   }

 // ��� 2. ������� �� ������
 if (sizeof($old) > 0)
  $db->Execute( "delete from ".$rh->db_prefix."user_groups where user_id = ".$db->Quote($udata["user_id"]).
                " and group_id in (".implode(",", $old).")" );

 // ��� 3. ���������� �� ����� � ��
 if (sizeof($new) > 0)
 {
   $record = &new NpjObject( &$rh, $this->params[0].":" );
   $data2 = $record->Load(2);

   if (!is_array($data2)) //$debug->Error("{!!!} friend of an external user not implemnted yet.",3);
     $tpl->Assign("ERROR", "<div class='error'>��� ������ ������������</div>" ); // !!! to messageset
   else
   {

   $sql = "insert into ".$rh->db_prefix."user_groups (group_id, user_id, keyword_id) VALUES "; $f=0;
   foreach ($new as $group)
   { if ($f) $sql.=", "; else $f=1;
     $sql.="(".$db->Quote($group).", ".$db->Quote($udata["user_id"]).", ".$db->Quote($data2["record_id"]).")";
   }
   $db->Execute( $sql );

    $rh->Redirect( $rh->Href( $this->_NpjAddressToUrl( $account->name.":friends/add/".$udata["login"]."/".$udata["node_id"]."/done" )
                               , IGNORE_STATE ) , IGNORE_STATE );
   }
 }
 else
  $rh->Redirect( $rh->Href( $this->_NpjAddressToUrl( $account->name.":friends/add/".$udata["login"]."/".$udata["node_id"]."/done" )
                            , IGNORE_STATE ) , IGNORE_STATE );

?>
