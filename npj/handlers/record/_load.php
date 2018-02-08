<?php
/*
    УРОВНИ КЭШИРОВАНИЯ

0 - supertag - (без БД) 
1 - record_id, user_id, type - (только ключи) +subject+user_datetime (for prettyURLs)
    + by_module
2 - все прочие поля, кроме body, body_r - (без блобов) 
  - + rare
3 - плюс body_r + body_post + body_toc + crossposted + keywords - (для вывода) 
4 - плюс body - т.е. все поля - (для редактирования)


  function Load( $cache_level=2 ) 

  * $abs_npj_address
  * $cache_class
*/

  $fields = array();
  $fields[] = "supertag, tag";
  $fields[] = $fields[0].", depth, record_id as id, record_id, author_id, user_id, type, ".
                           "filter, ".
                           "group1, group2, group3, group4, subject, user_datetime, by_module";
  $fields[] = $fields[1].", created_datetime, edited_datetime, ".
              "default_show_parameter, default_show_parameter_param, default_show_parameter_add, default_show_parameter_more, default_show_parameter_more_param, ".
              "disallow_comments, disallow_notify_comments, disallow_replicate, disallow_syndicate, ".
              "group_versions, template, ".
              "formatting, is_keyword, is_announce, is_digest, number_comments, pic_id, subject_r, version_tag, edited_user_login, edited_user_name, edited_user_node_id";
  $fields[] = $fields[2].", body_r, body_post, body_toc, body_options, crossposted, keywords, subject_post";
  $fields[] = $fields[3].", body";

  $debug->Trace("<b>LOAD: ".$abs_npj_address."</b>");  
  $debug->Trace("select ".$fields[$cache_level]." from ".$rh->db_prefix."records where ".
                      "supertag=".$db->Quote($abs_npj_address));  

  // load page
  $rs = $db->Execute( "select ".$fields[$cache_level]." from ".$rh->db_prefix."records where ".
                      "supertag=".$db->Quote($abs_npj_address));  

  $data = $rs->fields;

  $data["server_datetime"] = $data["created_datetime"]; // создаём дубликат, чтобы не запутаться с рефами

  // загружаем rares -----------------------------------------------------------------------------
  $data["rare"] = array();
  if (($cache_level>1) && ($rs->RecordCount()!=0)) 
  {

    $rs2 = $db->Execute( "select ".$rh->_records_rare." from ".$rh->db_prefix."records_rare where ".
                         "record_id=".$db->Quote($data["record_id"]));  
    if ($rs2->RecordCount()>0)
     $data["rare"] = $rs2->fields;
  }
  // обновляем кросспостед -----------------------------------------------------------------------------
  if (($cache_level>2) && ($rs->RecordCount()!=0)) 
  {
    if ($data["crossposted"] == "") 
    {
      $kwds = &$this->CompileCrossposted( $data["record_id"] );
      $data["crossposted"] = $kwds[0];
      $data["keywords"   ] = $kwds[1];
    }
    if ($data["crossposted"] == "!") $data["crossposted"] = "";
  }
  // !!!! общая проблема. если пост-процессор вызывается ДО кэширования, то всё кэширование идёт в далеко =(
  // поэтому надо скэшировать не пост-процессаную, а потом кэшировать ещё раз
  if (($cache_level>2) && ($rs->RecordCount()!=0))
  {
      $this->rh->cache->Store( "npj", $abs_npj_address, $cache_level-1, $data );
      $this->rh->cache->Store( $cache_class, $data["id"], $cache_level-1, &$data );
      $debug->Trace("Precaching (npj=$abs_npj_address) [$cache_level] $cache_class, ". $data["id"]);

  }

  return ($rs->fields?$data:"empty");

?>