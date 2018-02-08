<?php

  // for use in "feeds", "forums", etc.
  // moved to UtilityAction as a property,
  // BUT CURRENTLY (2004-11-26) IN USE by actions:
  // "directorychilds"
  // usage:
  // $sql = "select ".$__db_record_fields." from ". $__db_record_tables." where ..."

  $__db_record_fields = 
           " r.record_id, r.record_id as id, r.subject, subject_post, tag, supertag, ".
           " r.user_id, edited_user_name, edited_user_login, edited_user_node_id, ".
           " r.commented_datetime, r.created_datetime, r.created_datetime as server_datetime, ".
           " r.edited_datetime, r.user_datetime, ".
           " r.body_post, r.crossposted, r.keywords, ".
           " r.filter, r.by_module, ".
           ($is_digest?"body, ":"").
           " r.number_comments, r.disallow_comments, ".
           " r.group1, r.group2, r.group3, r.group4, ".
           " r.type, r.is_digest, r.formatting, ".
           " r.is_announce, ".
           " version_tag, is_parent, depth, r.disallow_replicate, ".
           " r.pic_id, ".
           " r.last_comment_id, ".

           // comments
           " c.user_id       as comment_user_id, ".
           " c.user_login    as comment_user_login, ".
           " c.user_name     as comment_user_name, ".
           " c.user_node_id  as comment_user_node_id, ".

           // rares
           " rr.announced_id, rr.announced_comments, rr.announced_disallow_comments, ".
           " rr.announced_supertag, rr.announced_title,".
           " rr.replicator_user_id ";


  $__db_record_tables = 
           $rh->db_prefix."records as r ".
           " left join ".
           $rh->db_prefix."records_rare as rr on rr.record_id=r.record_id ".
           " left join ".
           $rh->db_prefix."comments as c on c.comment_id=r.last_comment_id ";


?>