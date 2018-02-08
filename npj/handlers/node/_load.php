<?php
/*
    ÓÐÎÂÍÈ ÊÝØÈÐÎÂÀÍÈß

0 - all except private info
1 - all


  function Load( $cache_level=2 ) 
*/

  $fields = array();
  $fields[] = "title, title as subject, node_id as id, node_id, is_local, can_nns, is_nns, url, created_datetime, npj_version";
  $fields[] = $fields[0].", passwd, email, ip, alternate_ip, user_pictures_dir";
  $fields[] = $fields[1];
  $fields[] = $fields[2];

  // load page
  $a0 = trim($abs_npj_address, ":");
  $b = explode("@", $a0 );
  $a1 = $b[ sizeof($b) -1 ];
  $c  = explode("/", $a1 );
  $a  = $c[ sizeof($c) -1 ];

  $debug->Trace("<b>LOAD: ".$abs_npj_address."</b>");  

  $rs = $db->Execute( "select ".$fields[$cache_level]." from ".$rh->db_prefix."nodes where ".
                      "node_id=". $db->Quote($a));
  $result = $rs->fields;

  $debug->Trace_R( $result );
//  $debug->Error( $result );

  return ($rs->fields?$result:"empty");

?>