<?php

  $data = &$this->Load( 2 );
  if (!is_array($data)) return $this->NotFound("RecordNotFound");

  //Optimize from 2N to 2 queries !!!!
  foreach ($params as $rule_id)
  {
   $rs = $db->Execute( "select * from ".$rh->db_prefix."replica_rules where ".
                       "rep_rule_id=".$db->Quote($rule_id));  
   $repdata = $rs->fields;
   if (!$repdata["valid"]) continue;
   if ($repdata["dont_doublereplicate"] && $data["rep_original_id"]) continue;
   if ($repdata["date_from"]>date("Y-m-d H:i:s")) continue;
   if ($repdata["date_to"]<date("Y-m-d H:i:s")) continue;
   //!!! authors
   // facet here by kuso@npj ----------------------------------------------------
   if ($repdata["facet_white"])
   {
     $uref = &$rh->UtilityRef();
     $keyword_ids = $uref->KeywordsToRecordIds( &$this->npj_account, $repdata["facet_white"] );
     $is_pub      = $uref->IsPublishedIn( $data["record_id"], $keyword_ids );
     if (!$is_pub) continue;
   }
   if ($repdata["facet_black"])
   {
     $uref = &$rh->UtilityRef();
     $keyword_ids = $uref->KeywordsToRecordIds( &$this->npj_account, $repdata["facet_black"] );
     $is_pub      = $uref->IsPublishedIn( $data["record_id"], $keyword_ids );
     if ($is_pub) continue;
   }
   // ---- end of facet ----
   //!!! search
   if ($repdata["last"]<date("Y-m-d 00:00:00")) 
    $count = 1;
   else
    $count = $repdata["todaycount"]++;
   if ($repdata["maxperday"] && $repdata["maxperday"]<$count) continue;

   $rs = $db->Execute( "UPDATE ".$rh->db_prefix."replica_rules SET last=NOW(), todaycount=".$db->Quote($count));  
   
   $rs = $db->Execute( "INSERT INTO ".$rh->db_prefix."replica_queue (rep_rule_id, object_id, object_class, node_id, datetime) ".
                       "VALUES (".$db->Quote($rule_id).", ".$db->Quote($data["record_id"]).", ".
                       $db->Quote("record").", ".$db->Quote($repdata["node_id"]).", NOW())");  

  }

?>