<?php

// Программный хандлер удаления записи

// ТОЛЬКО ДЛЯ ВЫЗОВА ИЗ ПРОГРАММНОГО ОКРУЖЕНИЯ

  if ($params["record_id"]) $data["record_id"] = $params["record_id"];
  else
  {
    $data = $this->Load( 2 );
    if (($data == false) || ($data == "empty")) 
    { 
      return DENIED; 
    }
  }

  $debug->Trace("DELETING RECORD ".$data["record_id"] );

  // 1. records
  $query[] = "delete from ".$rh->db_prefix."records where record_id=".$db->Quote($data["record_id"]);
  // 2. records_rare
  $query[] = "delete from ".$rh->db_prefix."records_rare where record_id=".$db->Quote($data["record_id"]).
             " or announced_id = ".$db->Quote($data["record_id"]);
  // 3. records_ref
  $query[] = "delete from ".$rh->db_prefix."records_ref where record_id=".$db->Quote($data["record_id"]).
             " or keyword_id = ".$db->Quote($data["record_id"]);
  // 4. comments
  if ($rh->record_delete_comments)
  $query[] = "delete from ".$rh->db_prefix."comments where record_id=".$db->Quote($data["record_id"]);
  // 5. links
  $query[] = "delete from ".$rh->db_prefix."links where from_id=".$db->Quote($data["record_id"]);
  // 6. subscription
  $query[] = "delete from ".$rh->db_prefix."subscription where object_class=".
             $db->Quote("record")." and object_id=".$db->Quote($data["record_id"]);
  // 7. versions 
  $query[] = "delete from ".$rh->db_prefix."record_versions where record_id=".$db->Quote($data["record_id"]);
  // 8. records_replicas
  $query[] = "delete from ".$rh->db_prefix."records_replicas where record_id=".$db->Quote($data["record_id"]);
  // 9. user_groups
  $query[] = "delete from ".$rh->db_prefix."user_groups where keyword_id=".$db->Quote($data["record_id"]);
  
  // kukutz @ 29 october 2004
  // 10. records_ref_rules
  $query[] = "delete from ".$rh->db_prefix."records_ref_rules where keyword_id = ".$db->Quote($data["record_id"]);
  // 11. acls
  $query[] = "delete from ".$rh->db_prefix."acls where object_type='record' AND object_id = ".$db->Quote($data["record_id"]);
  // -----------------------

  // do sql!
  foreach($query as $q) $db->Execute( $q );

  return GRANTED;

?>