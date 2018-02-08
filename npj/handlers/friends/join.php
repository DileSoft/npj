<?php

 // �������� ������ �������� � ��������� ������
 $account = $rh->account;
 $data = $account->Load(2);

 // ��������, ����� �� ����� �����
 if ($data["account_type"] == ACCOUNT_USER) return $this->Forbidden("AccountNotSupport");
 if (!$account->HasAccess( &$principal, "noguests" )) return $this->Forbidden("JoinNoGuests");

 // �������� �� �������
 if (!$rh->account->HasAccess( &$principal, "not_acl", "banlist" )) return $this->Forbidden("YouAreInBanlist");

 // ============= ���������� ����� ==========
 if ($_POST["do"])
 {
   // ���������� � ����������
   if ($account->HasAccess( &$principal, "rank_greater", GROUPS_REQUESTS ))
   {
     $rs = $db->Execute("select group_id from ".$rh->db_prefix."groups where user_id=".$db->Quote($data["user_id"]));
     $a = $rs->GetArray(); $groups = array();
     foreach( $a as $item )  $groups[] = $item["group_id"];
     // ������� �� ����� ����������
     if (sizeof($groups) > 0)
     $db->Execute( "delete from ".$rh->db_prefix."user_groups where user_id=".$db->Quote($principal->data["user_id"]).
                   " and group_id in (".implode(",",$groups).")");
     // [???] ������� ���������� �� ������ "� ������ �"
     $rs = $db->Execute( "select group_id from ".$rh->db_prefix."groups where user_id=".$db->Quote($principal->data["user_id"]).
                         " and group_rank = ".$db->Quote(GROUPS_COMMUNITIES));
     $a = $rs->GetArray(); $g = array();
     foreach($a as $v) $g[] = $v["group_id"];
     $db->Execute( "delete from ".$rh->db_prefix."user_groups where user_id=".$db->Quote($data["user_id"]).
                   " and group_id in (".implode(",",$g).")");
   }
   else
   if ($data["security_type"]%10 < COMMUNITY_CLOSED)
   {
     // �������� �� ������ �������
     $p = array( "by_script" => 1 );
     if ($_POST["subscribe_comments"]) $p["comments"] = 1;
     if ($_POST["subscribe_post"]) $p["post"] = 1;
     if ($_POST["subscribe_comments"] || $_POST["subscribe_post"])
      $account->Handler("_subscribe", &$p, &$principal);

     // ������� �����������, ���� ��� ���� �����
     $this->Handler( "join_mail", array("user_id"=>$principal->data["user_id"]), &$principal );

     // �������� � ������ ������
     $rs = $db->Execute("select group_id from ".$rh->db_prefix."groups where user_id=".$db->Quote($data["user_id"]).
                        " and group_rank = ".$db->Quote(
                        ($data["security_type"]<COMMUNITY_LIMITED?$data["default_membership"]:GROUPS_REQUESTS)));
     if ($rs->RecordCount() > 0)
     {
       $principal_local_account = $principal->data["login"]."@".$principal->data["node_id"];
       // -- ��� ������� ���� ���� �������� ������.
       if ($principal->data["node_id"] != $rh->node_name)
         $principal_local_account.="/".$rh->node_name;

       $record = &new NpjObject( &$rh, $principal_local_account.":" );
       $data2 = $record->Load(2);
       if (!is_array($data2)) $debug->Error("{!!!!!} how strange, i feel straaaaaaaange",5);
       $group_id = $rs->fields["group_id"];
       $db->Execute("insert into ".$rh->db_prefix."user_groups (group_id, user_id, keyword_id) VALUES ".
                    "(".$db->Quote($group_id).", ".$db->Quote($principal->data["user_id"]).", ".
                    $db->Quote($data2["record_id"]).")");
     }
     // �������� ���������� � ��������������/����������/������ ��� ���������
     // ���������, ������ ���� ��� ��� ��� ���
     $rs = $db->Execute( "select  ug.group_id from ".$rh->db_prefix."groups as g, ".$rh->db_prefix."user_groups as ug where ".
                         " ug.group_id=g.group_id and g.user_id=".$db->Quote($principal->data["user_id"]).
                         " and g.group_rank < ".$db->Quote(GROUPS_SELF)." and g.is_system = 1 ".
                         " and ug.user_id = ".$db->Quote($data["user_id"]) );
     $a = $rs->GetArray(); $g = array(-185);
     foreach($a as $v) $g[] = $v["group_id"];
     $rs = $db->Execute("select g.group_id from ".$rh->db_prefix."groups as g ".
                        " where g.user_id=".$db->Quote($principal->data["user_id"]).
                        " and g.group_id not in (".implode(",",$g).") and g.group_rank < ".$db->Quote(GROUPS_SELF)." and g.is_system = 1 " );
     if ($rs->RecordCount() > 0)
     {
       $record = &new NpjObject( &$rh, $account->npj_account.":" );
       $data2 = $record->Load(2);
       $a = $rs->GetArray(); $sql=""; $f=0;
       foreach ($a as $item)
       { if ($f) $sql.=", "; else $f=1;
         $sql.= "(".$db->Quote($item["group_id"]).
                ", ".$db->Quote($data2["user_id"]).
                ", ".$db->Quote($data2["record_id"]).")";
       }
       $db->Execute("insert into ".$rh->db_prefix."user_groups (group_id, user_id, keyword_id) VALUES ".$sql);
     }


   }
   $this->Handler("_count_friends", array(), &$principal );
   // ������������ �� "��"
   $rh->Redirect( $rh->Href( $this->_NpjAddressToUrl( $account->name.":friends/join/ok" ), IGNORE_STATE ) , IGNORE_STATE );
 }

 // ============== ������� ==================

 $tpl->theme = $rh->theme;
 if ($params[0])  // .../join/ok
 {
   $tpl->Assign( "Preparsed:TITLE", "�������� ���������" ); // !!! to messageset
   $tpl->Parse(  "friends.join.html:Done", "Preparsed:CONTENT" ); 
   // ���� ��������, ��� �� �������
 }
 else
 if ($account->HasAccess( &$principal, "rank_greater", GROUPS_LIGHTMEMBERS ))
 {
   // ����� �������� ����� "�� ��������"
   $tpl->Assign( "Preparsed:TITLE", "����� �� ����������" ); // !!! to messageset
   $tpl->Assign( "FormState", "�� ��� ��������� ������ ����� ����������." ); // !!! to messageset
   $tpl->Assign( "FormAction", "��� ����, ����� ����� �� ����������, ������� �� ��� ������:" ); // !!! to messageset
   $tpl->Assign( "FormButton", "�������� ����������" ); // !!! to messageset
   $tpl->Assign( "IsFriendAlready", "1" ); // !!! to messageset
 }
 else
 if ($account->HasAccess( &$principal, "rank_greater", GROUPS_REQUESTS ))
 {
   $tpl->Assign( "Preparsed:TITLE", "���������� � ����������" ); // !!! to messageset
   $tpl->Assign( "Preparsed:CONTENT", "������ ��� ��������� �� ������������ ������������ ����������. �����..." ); 
   // !!! to messageset
 }
 else
 if ($data["security_type"]%10 > 1)
 {
   // ����� ����������, ������ ��� ���������� ��������
   $tpl->Assign( "Preparsed:TITLE", "���������� � ���������� ����������" ); // !!! to messageset
   $tpl->Assign( "Preparsed:CONTENT", "��������, �� ��� ���������� ��������� ����. ���������� ��������������� � ����������, ����� �� ������� ��� � �����." ); 
 }
 else
 {
   // ����� �������� ����� "��������� � ���� ����"
   $tpl->Assign( "Preparsed:TITLE", "���������� � ����������" ); // !!! to messageset
   if ($data["security_type"] > 0)
     $tpl->Assign( "FormState", "�� ������ ����� ������ ����� ���������� ����� ������������� ������ �����������" ); // !!! to messageset
   else
     $tpl->Assign( "FormState", "�� ������ ����� ������ ����� ���������� ����� ����� �������� ������" ); // !!! to messageset
   $tpl->Assign( "FormAction", "��� ����, ����� ��������� ������ �� ��������, ������� �� ��� ������" ); // !!! to messageset
   $tpl->Assign( "FormButton", "����� � ����������" ); // !!! to messageset
 }

 // �������� �������
 if (!$tpl->GetValue("Preparsed:CONTENT")) 
 {
   $tpl->LoadDomain( array(
          "Form:Join" => $state->FormStart( MSS_POST, $this->_NpjAddressToUrl( $this->npj_address )),
          "/Form"     => $state->FormEnd(),  
                   )      );
   $tpl->Parse( "friends.join.html:Form", "Preparsed:CONTENT" ); 
 }
 $tpl->theme = $rh->skin;

?>