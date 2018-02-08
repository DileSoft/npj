<?php
/*
    ”–ќ¬Ќ»  ЁЎ»–ќ¬јЌ»я

0 - version_id - (без Ѕƒ) 
1 - все прочие пол€, кроме body, body_r - (без блобов) 
2 - плюс body_r - (дл€ вывода) 
3 - плюс body - т.е. все пол€ - (дл€ редактировани€)
4 -- дл€ совместимости с record


  function Load( $cache_level=2 ) 

  * $abs_npj_address
  * $cache_class
*/

  $fields = array();
  $fields[] = "version_id, version_id as id";
  $fields[] = $fields[0].", record_id, edited_datetime, formatting, version_tag, edited_user_login, edited_user_name, edited_user_node_id";
  $fields[] = $fields[1].", body_r";
  $fields[] = $fields[2].", body";
  $fields[] = $fields[3];

  if (!isset($this->record))
  {
    $this->record = &new NpjObject( &$rh, $object->npj_account.":".$object->npj_context );
    $data = $this->record->Load( 2 ); // запись не надо показывать
    if ($data === "empty") return $this->record->NotFound(); // ???
  }


  // load version
  $sql = "select ".$fields[$cache_level]." from ".$rh->db_prefix."record_versions where ".
                      "record_id=".$db->Quote($this->record->data["record_id"])." and version_id=".$db->Quote($this->name);
  $rs = $db->Execute( $sql );
  $debug->Trace( $sql );

  if (($cache_level>1) && ($rs->RecordCount()!=0))
  {
    $record = &new NpjObject( &$rh, $object->npj_account.":".$object->npj_context );
    // пометка, что body-dependant actions must now quit prematurely
    $record->wrong_body = 1; 
    $rs->fields["body_post"] = $record->Format($rs->fields["body_r"], $rs->fields["formatting"], "post"); 
  }

// $debug->Trace_R( $rs->fields );
// $debug->Error( $abs_npj_address );
  $result = $rs->fields;
  $result["type"] = "version";

  return ($rs->fields?$result:"empty");

?>