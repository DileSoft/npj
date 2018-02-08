<?php

 // �������� groups.php

 // ���� $this->params[0] = "edit"
 // ����� ���� $this->params[1] = $rh->group_rank[ xx ] -- ����� ��� ����� �����������
 // ���� $account -- ������� ����, ��� ������ ��������
 // ���� $data -- ������ �������� ����, ��� ������ ��������

 // ����� ��� �����?

 if ($data["account_type"] > 0) return $this->Forbidden("NotImplemented");

 if (!$account->HasAccess( &$principal, "owner" ))
   if (!$account->HasAccess( &$principal, "acl_text", $rh->node_admins ) || ($account->npj_account != $rh->node_user))
    return $this->Forbidden("YouDontOwnThisAccount");

 $group_ranks = $rh->group_ranks[ $data["account_type"] ];
 $rank_flipped = array_flip( $group_ranks );
 $rank = 0;
 if ($this->params[1])
  if (isset( $rank_flipped[$this->params[1]] ))
   $rank = $rank_flipped[$this->params[1]];

 // ok page
 if ($params[1] == "ok")
 { $tpl->theme = $rh->theme;
   $tpl->Parse( "friends.groups.edit.html:Done", "Preparsed:CONTENT" );
   $tpl->Assign( "Preparsed:TITLE", "������ ��������� �������" ); // !!! to message_set
   $tpl->theme = $rh->skin;
   return GRANTED;
 }

 // �������� ������ ����� "���������" ��� ���� ��� ������
 $rs = $db->Execute( "select is_default, is_system, group_id, group_name, group_rank from ".$rh->db_prefix."groups where user_id=".
                      $db->Quote($data["user_id"])." and group_rank=".$db->Quote($rank)." order by pos" );
 // �������������� ��� �� ������� $groups[ is_system ][ group_id ]
 $a = $rs->GetArray(); $groups = array( 0 => array(), 1 => array() );
 foreach( $a as $item )
 {
   $groups[$item["is_system"]][ $item["group_id"] ] = array(
        "href"  => $item["group_id"],
        "text"  => $item["group_name"],
           );
 }

 // ����������
 if ($_POST["_do"])
 {
   include( $rh->handlers_dir."friends/_groups_edit_save.php" );
   return GRANTED;
 }


 $tpl->theme = $rh->theme;
 // ��� �������� ��� �����������:
 // 1. {{GroupSelect}}   ������ ���� <select size=XX> �� ���� �����
 // 2. {{GroupContents}} ������� ��������� ������������� ����� ��������������, � �������:
 //                       * group1|user1,user2,user3|group2||group3|user2|group4|user1,user3,user4
 //                       * groupX -- group_id
 //                       * userX  -- NpjAddress (kuso@npj)
 // 3. {{AllUsers}}      ������ ���� ������������� 
 // 4. {{GroupRanks}}    ������������� �� ���� ����� ����� (����������, ��������)
 // 5. {{GroupNames}} ������ ����� ���� id|name|id|name

 // 1. {{GroupSelect}}   ������ ���� <select size=XX> �� ���� �����
 $select = &new ListSimple( &$rh, &$groups[0] );
 $select->Parse( "friends.groups.edit.html:Select", "GroupSelect" );
 // 2. {{GroupContents}} ������� ��������� ������������� ����� ��������������, � �������:
 $group_contents = ""; 
 foreach( $groups[0] as $id=>$group ) 
 {
   $rs = $db->Execute( "select u.login, u.node_id from ".$rh->db_prefix."users as u, ".$rh->db_prefix.
          "user_groups as ug where ug.user_id = u.user_id and ug.group_id=".$db->Quote($id) );
   $a = $rs->GetArray();
   $group_contents .= $id."|"; $f=0;
   foreach ($a as $user)
   { if ($f) $group_contents.=","; else $f=1;
     $group_contents.= $user["login"]."@".$user["node_id"]; }
   $group_contents .= "|"; 
 }
 if ($group_contents != "") $group_contents = substr( $group_contents, 0, strlen($group_contents) -1 );
 $tpl->Assign( "GroupContents", $group_contents );
 // 3. {{AllUsers}}      ������ ���� ������������� 
 foreach( $groups[1] as $group ) $all =  $group;
 $rs = $db->Execute( "select u.login, u.node_id from ".$rh->db_prefix."users as u, ".$rh->db_prefix.
        "user_groups as ug where ug.user_id = u.user_id and ug.group_id=".$db->Quote($all["href"]) );
 $a = $rs->GetArray();
 $all_users = ""; $f=0;
 foreach ($a as $user)
 { if ($f) $all_users.=","; else $f=1;
   $all_users.= $user["login"]."@".$user["node_id"]; }
 $tpl->Assign( "AllUsers", $all_users );
 // 4. {{GroupRanks}}    ������������� �� ���� ����� ����� (����������, ��������)
 $rh->UseClass( "ListCurrent", $rh->core_dir );
 $rank_names = array();
 foreach ($group_ranks as $group_nick) $rank_names[$group_nick] = $tpl->message_set["FriendsNames"][$group_nick];
 $ranks = &new ListCurrent( &$rh, &$rank_names, NULL, $group_ranks[$rank] );
 $ranks->Parse( "friends.groups.edit.html:Rank", "GroupRanks" );
 // 5. {{GroupNames}} 
 $grp = "";
 foreach( $groups[0] as $id=>$group ) 
 {
  $grp = $grp.$id."|".$group["text"]."|";
 }
 $tpl->Assign( "GroupNames", rtrim($grp,"|") );

 // �������� ��������� ��� ����������� ���������
 $tpl->LoadDomain( array(
    "Form:Edit" => $state->FormStart( MSS_POST, $this->_NpjAddressToUrl( $account->name.":friends/groups/edit/".$group_ranks[$rank] ),
                                      " name=fg "),
    "/Form"     => $state->FormEnd(),
                 )      );
 // �������
   // �������� �������
   $tpl->Parse( "friends.groups.edit.html:Main", "Preparsed:CONTENT" );
   $tpl->Assign( "Preparsed:TITLE", "�������������� �����" ); // !!! to messageset

 
 $tpl->theme = $rh->skin;

?>