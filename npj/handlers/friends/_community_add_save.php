<?php

 // �������� add.php

 // ���� $this->params[0], ��� �������� ������������
 // ���� $_POST["_group"], ������� ����� ������� ������, ���� ����� ��������
 // ���� $groups[ group_id ], ��� ���� ������ �����
 // ���� $user -- ���, ���� ���������
 // ���� $account -- ���, ���� ���������
 // ���� $udata, $data -- ������ �������������� ����� � ��������

 // ��� 2. ������� �� ������ // ������������ �� ����� �������� ����� ��� � ����� ������
 $old = array();
 foreach ($groups as $k=>$v) $old[] = $k;
 if (sizeof($old) > 0)
  $db->Execute( "delete from ".$rh->db_prefix."user_groups where user_id = ".$db->Quote($udata["user_id"]).
                " and group_id in (".implode(",", $old).")" );

 if ($_POST["_group"] < 0)
  $rh->Redirect( $rh->Href( $this->_NpjAddressToUrl( $account->name.":friends/add/".$udata["login"]."/".$udata["node_id"]."/doneremove" )
                             , IGNORE_STATE ) , IGNORE_STATE );

 // ������������ ������ ���������
 $tpl->MergeMessageSet( $rh->message_set."_member_state" );

 // ��� 3. ���������� �� ����� � ��
 if (isset($groups[ $_POST["_group"] ]))
 {
   $record = &new NpjObject( &$rh, $this->params[0].":" );
   $data2 = $record->Load(2);
   if (!is_array($data2)) $debug->Error("{!!!} friend of an external user not implemented yet.",3);

   $group = 1*$_POST["_group"];
   $sql = "insert into ".$rh->db_prefix."user_groups (group_id, user_id, keyword_id) VALUES "; $f=0;
   $sql.="(".$group.", ".$db->Quote($udata["user_id"]).", ".$db->Quote($data2["record_id"]).")";

   // ��� ����� �������� � ����-������ "��� ���������"
   $rs = $db->Execute("select group_id from ".$rh->db_prefix."groups where group_rank=".GROUPS_COMMUNITIES.
                      " and is_system=1 and user_id=".$db->Quote($udata["user_id"]));
   if ($rs->RecordCount() > 0)
   {
     $group_id =$rs->fields["group_id"]; 
     $rs = $db->Execute("select record_id from ".$rh->db_prefix."records where user_id=".$db->Quote($data["user_id"]).
                        " and tag=".$db->Quote(""));
     if ($rs->RecordCount() > 0)
     {
       $keyword_id = $rs->fields["record_id"];
       $sql.=",(".$db->Quote($group_id).", ".$db->Quote($data["user_id"]).", ".$db->Quote($keyword_id).")";
     }
   }

   $db->Execute( $sql );
 }

  if ($udata["_group_id"] != $_POST["_group"])
  if ($udata["user_id"] != $data["user_id"])
   $this->Handler("_community_add_notify", 
                  array($groups[$_POST["_group"]]["rank"],  $udata["login"]."@".$udata["node_id"] ), &$principal );

 $rh->Redirect( $rh->Href( $this->_NpjAddressToUrl( $account->name.":friends/add/".$udata["login"]."/".$udata["node_id"]."/done" )
                            , IGNORE_STATE ) , IGNORE_STATE );
?>
