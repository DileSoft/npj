<?php

 // �������� ������ �������� � ��������� ������
 $account = $rh->account;
 $data = $account->Load(2);

 if ($data["account_type"] == ACCOUNT_COMMUNITY) return include( $rh->handlers_dir."friends/_community_add.php" );
 if ($data["account_type"] == ACCOUNT_WORKGROUP) return include( $rh->handlers_dir."friends/_community_add.php" );

 if (!$account->HasAccess( &$principal, "owner" )) return $this->Forbidden("FriendsEdit");

 // �������, ����� �� �� ��������� �����
 if ($this->params[0])
 {
   $this->params[0] = rtrim($this->params[0] , "@");
   if ($this->params[1]) $this->params[0].="@".$this->params[1];
   else 
   if (strpos($this->params[0], "@") === false) $this->params[0].="@".$rh->node_name;
                        
   if ($this->params[2])  
   {
     include( $rh->handlers_dir."friends/_add_ok.php" );
     return GRANTED;
   }
 }
 if ($_POST["_user"])
 {
   $_POST["_user"] = rtrim($_POST["_user"] , "@");
   if (strpos($_POST["_user"], "@") === false) $_POST["_user"].="@".$rh->node_name;

   // -- ��� ������� ���� ���� �������� ������.
   $parts = explode("@", $_POST["_user"] );
   if ($parts[1] != $rh->node_name)
    $parts[1].="/".$rh->node_name;
   $_POST["_user"] = implode("@", $parts);

   $this->params[0] = $_POST["_user"];
 }
 $this->params["_stripped_user"] = preg_replace("/\/.*$/i", "", $this->params[0]);
 
 // �������� ������ ����� "�����������"
 $rs = $db->Execute( "select is_system, group_id, group_name, group_rank from ".$rh->db_prefix."groups where user_id=".
                      $db->Quote($data["user_id"])." and group_rank<".$db->Quote(GROUPS_SELF)." order by group_rank, pos" );
 // �������������� ��� �� ������� $groups[ is_system ][ group_rank ][ group_id ]
 $a = $rs->GetArray(); $groups = array( 0 => array(), 1 => array() );
 foreach( $a as $item )
 {
   if (!isset($groups[$item["is_system"]][$item["group_rank"]])) 
     $groups[$item["is_system"]][$item["group_rank"]] = array();
   $groups[$item["is_system"]][$item["group_rank"]][ $item["group_id"] ] = array(
        "href"  => $item["group_id"],
        "text"  => $item["group_name"],
        "title" => "",
           );
 }

 // �������� ��������� ��� ����������� ���������
 $tpl->LoadDomain( array(
    "Form:Add"       => $state->FormStart( MSS_POST, $this->_NpjAddressToUrl( $account->name.":friends/add" )),
    "/Form"          => $state->FormEnd(),
    "Npj:Friend"     => $this->params["_stripped_user"], 
                 )      );

 // ���������, ���������� �� ��� ������������, �������� ��� ����������
 if ($this->params[0])
 {
   $user = &new NpjObject( &$rh, $this->params[0] );
   $udata = $user->Load(2);
   if (!is_array($udata)) $this->params[0] = ""; 
 }
 // ������ � ������
 if ($_POST["_user"] && ($this->params[0] != ""))
   include( $rh->handlers_dir."friends/_add_save.php" );

 // ��������� �� ������
 $tpl->Skin($rh->theme);
 if ($_POST["_user"]) 
 {
   $parts = explode("@", $_POST["_user"] );
   if ($parts[1] == $rh->node_name) $tpl->Assign("IsForeign", 0);
   else
   {
     $tpl->Assign("IsForeign", 1);
     $rh->absolute_urls=1;
     $tpl->Assign("Href:authto", $rh->base_host_prot.$rh->Href( 
          $state->Plus("authto", preg_replace("/\/.*$/i", "", $parts[1])), STATE_IGNORE ));
     $rh->absolute_urls=0;
   }

   $tpl->Parse("friends.add.html:Error","ERROR");
 }

 // � ��� �� ��� ����� ������������ � �������? ���������� �� ��
 if ($this->params[0])
 { 
   if (is_array($udata))
   {
    $rs = $db->Execute( "select g.group_id as group_id, g.group_rank as group_rank, g.is_system as is_system ".
                        " from ".$rh->db_prefix."user_groups as ug, ".$rh->db_prefix."groups as g ".
                        " where ug.group_id = g.group_id and g.user_id=".$db->Quote($data["user_id"]).
                        " and ug.user_id = ".$db->Quote($udata["user_id"]) );
    $a = $rs->GetArray();
    if (sizeof($a) > 0) $tpl->Assign("IsFriendAlready", 1);

    // ������������ ��������
    foreach( $a as $item )
    if (isset($groups[$item["is_system"]][$item["group_rank"]]))
     if (isset($groups[$item["is_system"]][$item["group_rank"]][$item["group_id"]]))
      $groups[$item["is_system"]][$item["group_rank"]][$item["group_id"]] [ "title" ] = "CHECKED";
 } }

 // �������

   // ������� ����� �����
   $grps = array( GROUPS_FRIENDS, GROUPS_REPORTERS ); $a = array();
   $list = &new ListSimple( &$rh, &$a ); $c=0;
   foreach ($grps as $grp )
   {
     foreach( $groups[1][$grp] as $item )
      $tpl->LoadDomain( array( "All.ID" => $item["href"], 
                               "All.Name" => $item["text"], 
                               "All.Rank" => $rh->group_ranks[$data["account_type"]][$grp],
                               "All.Checked" => $item["title"],
                               "AreConfidents" => ($grp==GROUPS_FRIENDS?1:0),
                               "AreReporters" => ($grp==GROUPS_REPORTERS?1:0),
                             ));
     $list->data = &$groups[0][$grp];
     $list->Parse( "friends.add.html:Groups", "List".($c++) );
   }
   // �������� �������
   $tpl->Parse( "friends.add.html:Main", "Preparsed:CONTENT" );
   $tpl->Assign( "Preparsed:TITLE", "�������� �&nbsp;������ �����������/���������������" ); // !!! to messageset

  $tpl->Unskin();

?>