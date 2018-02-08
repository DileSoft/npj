<?php
/*
      ??? NOTE THAT:   ЭТО ЕСТЬ КОПИЯ _LOAD
*/
/*
    УРОВНИ КЭШИРОВАНИЯ

0 - comment_id - (без БД) 
1 - active, frozen, record_id, parent_id, rep_node_id, rep_user_id, rep_original_id - (только ключи) 
2 - все прочие поля, кроме body_post - (без блобов) 
3 - плюс body_post - (для вывода) 


  function _LoadById( $cache_level=2 ) 

  * $id
  * $cache_class
  * $cache_level
*/
  $fields = array();
  $fields[] = "comment_id as id, comment_id";
  $fields[] = $fields[0].", user_id, active, frozen, lft_id, rgt_id, FLOOR((rgt_id-lft_id-1)/2) as number_comments, parent_id, record_id, rep_node_id, replicator_user_id, rep_original_id ";
  $fields[] = $fields[1].", user_login, user_node_id, user_name, created_datetime, ip_xff, pic_id, subject, disallow_replicate ";
  $fields[] = $fields[2].", body_post";

  if ($id)
  {
    // load page
    $rs = $db->Execute( "select ".$fields[$cache_level]." from ".$rh->db_prefix."comments where ".
                        "comment_id=".$db->Quote($id));  
    $result = &$rs->fields;
  }

  return ($result?$result:"empty");

?>