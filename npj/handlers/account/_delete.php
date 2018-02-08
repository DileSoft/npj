<?php

// Программный хандлер удаления АККАУНТА

// ТОЛЬКО ДЛЯ ВЫЗОВА ИЗ ПРОГРАММНОГО ОКРУЖЕНИЯ

  $data = $this->Load( 2 );
  if (($data == false) || ($data == "empty")) 
  { 
    return DENIED; 
  }

  $debug->Trace("DELETING ".$data["user_id"] );

  // ========= records
  $rs = $db->Execute("select record_id from ".$rh->db_prefix."records where ".
          "supertag LIKE ".$db->Quote($data["login"]."@".$data["node_id"].":%").
          " OR supertag=".$db->Quote($data["login"]."@".$data["node_id"].":"));
  $a = $rs->GetArray();

  $obj = &new NpjObject( &$rh, $this->name.":", "record" );
  foreach ($a as $v) 
  {
    $obj->Handler( "_delete", array("record_id"=>$v), &$principal );
  }

  $debug->Trace("DELETED RECORDS");

  // 1. users
  $query[] = "delete from ".$rh->db_prefix."users where user_id=".$db->Quote($data["user_id"]);

  $debug->Trace("DELETED USER");

  // 2. userpics
  $query[] = "delete from ".$rh->db_prefix."userpics where user_id=".$db->Quote($data["user_id"]);

  $debug->Trace("DELETED USERPICS");

  // 3. user_menu
  $query[] = "delete from ".$rh->db_prefix."user_menu where user_id=".$db->Quote($data["user_id"]);

  // 4. subscription
  $query[] = "delete from ".$rh->db_prefix."subscription where user_id=".$db->Quote($data["user_id"]);

  // 5. profiles
  $query[] = "delete from ".$rh->db_prefix."profiles where user_id=".$db->Quote($data["user_id"]);

  // 6. links
  $query[] = "delete from ".$rh->db_prefix."links where  from_user_id=".$db->Quote($data["user_id"]);

  $debug->Trace("DELETED LINKS");

  // ========= groups
  $rs = $db->Execute("select group_id from ".$rh->db_prefix."groups where user_id=".$db->Quote($data["user_id"]));
  $a = $rs->GetArray();
  $groups = array();

  foreach ($a as $v) $groups[] = $v["group_id"];

  // 7. user_groups
  if (sizeof($groups))
    $query[] = "delete from ".$rh->db_prefix."user_groups where  group_id in (".implode(",", $groups).")";

  // 8. groups
  $query[] = "delete from ".$rh->db_prefix."groups where user_id=".$db->Quote($data["user_id"]);

  $debug->Trace("DELETED GROUPS");

  // ========= acls
  // 9. acls
  $query[] = "delete from ".$rh->db_prefix."acls where object_type='account' and object_id=".$db->Quote($data["user_id"]);

  // !!!
  //  пересчёт lft_id и rgt_id - пока никак не считаются, но пометки оставить надо.
  $debug->Trace("DELETED ACLS");

  // do sql!
  foreach($query as $q) $db->Execute( $q );
  //$debug->Error_R($query);


  return GRANTED;

?>