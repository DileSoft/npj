<?php
/*

  * сохраняет **только** в основную таблицу по подготовленной ##$this->data##
  * заполняет поля сохранено когда и кем

*/

  //======== preformat, format, typo
  $this->data["body"] = $this->Format($this->data["body"], $this->data["formatting"], "pre");
  $data["body_r"]  = $this->Format($this->data["body"], $data["formatting"]);
  $data["body_r"]  = $this->Format($data["body_r"], "paragrafica");
  $data["body_toc"] = $this->data["body_toc"];
//  if ($data["body_toc"] == "") $debug->Error("- no toc -");
  $data["subject_r"]  = $this->Format($data["subject"], $data["formatting"]."_subject");

  if ($owner->data["advanced_options"]["typografica"])
  {
    $data["body_r"]     = $this->Format($data["body_r"],"typografica");
    $data["subject_r"]  = $this->Format($data["subject_r"],"typografica");
    $uactn = &$rh->UtilityAction(); // actions теперь живут в отдельном классе. << max@jetstyle 2004-11-18 >>
    $data["body_toc"]   = $uactn->TypografeToc( $data["body_toc"] );

  }

  if ($type==RECORD_POST)
  { 
    // если кому интересно, то "default"=>"post" это не про постинги, а про постформаттеры.
    if ($data["strict:body_post"])
    {
      $data["strict:body_post"] = $this->Format($this->data["strict:body_post"], 
                                                $this->data["formatting"], "pre");
      $data["strict:body_post"] = $this->Format($this->data["strict:body_post"], 
                                                $this->data["formatting"]);
      if ($owner->data["advanced_options"]["typografica"])
        $data["strict:body_post"] = $this->Format($this->data["strict:body_post"], 
                                                  "typografica");
      $body_post_ready = $data["strict:body_post"];
    }
    else
      $body_post_ready = $data["body_r"];
    $data["body_post"] = $this->Format($body_post_ready, $data["formatting"], array("default"=>"post","feed"=>1));
    $data["subject_post"] = $this->Format($data["subject_r"], $data["formatting"], 
                                          array("default"=>"post","feed"=>1,"subject"=>1, "stripnotypo"=>1));
  }
  
  //======== кто владелец и кто отредактировал
  $data["user_id"] = $owner_data["user_id"];
  $data["owner_login"] = $owner_data["login"];
  $data["owner_node"] = $owner_data["node_id"];
  $data["edited_user_login"] = $principal->data["login"];
  $data["edited_user_name"] = $principal->data["user_name"];
  $data["edited_user_node_id"] = $principal->data["node_id"];
  $data["author_id"] = $principal->data["user_id"];
  //а вот нечево тут!
  if ($data["tag"]) $data["tag"]  = trim($data["tag"], "/");

  //построение супертага (для новых постов потом поменяется)
  $_supertag = $owner_data["login"]."@".$owner_data["node_id"].":".$this->NpjTranslit($data["tag"]);
  $supertag = $db->Quote($_supertag);

/* есть четыре варианта:
                        новый пост  правим пост  новый док  правим док
                            3           2            5         4
record_id,                              -                      -
type,                                   -                      -
user_id,                                -                      -
subject,                                                       +
tag,                                    -                      -
depth,                                  -                      -
supertag,                               -                      -
default_show_parameter*,                                       +
body,                                                          +
body_r,                                                        +
formatting,                             -                      -
version_tag,                 -          -                      +
pic_id,                                                        +
user_datetime,                                                 +
created_datetime,                       -                      -
edited_datetime,                                               +
disallow_comments,                                             +
disallow_syndicate,                                  -         -
disallow_replicate,                                            + 
disallow_notify_comments,                                      + 
number_comments,             -          -            -         -
is_digest,                                           +           
is_announce,                                                   + 
is_keyword,                                          +           
group1,                                              -         -
group2,                                              -         -
group3,                                              -         -
group4,                                              -         -
edited_user_login,                                             +
edited_user_name,                                              +
edited_user_node_id                                            +
author_id                                            +
===============NB: таблица устарела.
*/

  //=========== массив, содержащий поля запроса, которые заполнятся автоматом согласно $this->data
  $fields = array();
  $fields[2] = array("subject", "subject_r", "subject_post", "body", "body_r", "body_toc", "body_post", "pic_id", "user_datetime", "disallow_comments",
  "disallow_replicate", "disallow_syndicate", "disallow_notify_comments", "group1", "group2", "group3", "group4",
  "default_show_parameter", "default_show_parameter_param", "default_show_parameter_add", "default_show_parameter_more","default_show_parameter_more_param",
  "edited_user_login", "edited_user_name", "edited_user_node_id", "is_announce");
  $fields[3] = array("depth", "user_id", "author_id", "type", "tag", "formatting", "subject", "subject_r", "subject_post", "body", "body_r", "body_toc", "body_post", "pic_id",
  "user_datetime", "disallow_comments", "disallow_replicate", "disallow_syndicate", "disallow_notify_comments",
  "default_show_parameter", "default_show_parameter_param", "default_show_parameter_add", "default_show_parameter_more","default_show_parameter_more_param",
  "group1", "group2", "group3", "group4",  "is_announce",
  "edited_user_login", "edited_user_name", "edited_user_node_id");
  $fields[4] = array("subject", "subject_r", "body", "body_r", "body_toc", "pic_id", "user_datetime", "version_tag",
  "disallow_comments", "disallow_replicate", "disallow_notify_comments",
  "default_show_parameter", "default_show_parameter_param", "default_show_parameter_add", "default_show_parameter_more","default_show_parameter_more_param",
  "edited_user_login", "edited_user_name", "edited_user_node_id" , "is_digest", "is_keyword");
  $fields[5] = array("depth", "user_id", "author_id", "type", "tag", "formatting", "subject", "subject_r", "body", "body_r", "body_toc", "pic_id",
  "user_datetime", "version_tag", "disallow_comments", "disallow_replicate", "disallow_notify_comments",
  "default_show_parameter", "default_show_parameter_param", "default_show_parameter_add", "default_show_parameter_more","default_show_parameter_more_param",
  "edited_user_login", "edited_user_name", "edited_user_node_id" , "is_digest", "is_keyword");
  
  foreach( $fields as $k=>$v )
  {
    $fields[$k][] = "by_module";
  }

  if (isset($data["server_datetime"])) 
    $_now = $data["server_datetime"]; // в рефах уже заполнили дату, можно не трудиться
  else 
    $_now = date("Y-m-d H:i:s");
  $now = $db->Quote($_now);

  //это волшебная штучка вариант. Выбирает один из четырёх вариантов, описанных в таблице.
  $variant = $is_new+2*$type;
  switch ($variant)
  {
   case 2:
   //old post
    $query = "update ".$rh->db_prefix."records set ";
    $query_end = ", edited_datetime=NOW() where supertag=".$supertag;
    break;
   case 3:
   //new post
   // !!! LIMIT 1
    $query = "select tag from ".$rh->db_prefix."records where type=1 AND user_id=".$db->Quote($owner_data["user_id"]).
     " order by record_id desc";
    $rs = $db->Execute($query);

    if (!$data["rare"]["replicator_user_id"] && !$data["tag__leave_as_is"])
    {
     //tag - равен тагу предыдущего поста + случайное число 1..500
     $data["tag"] = rand(1, 500)+(int)$rs->fields["tag"];  
     if ($data["tag"]<3000) $data["tag"] = 3000 + rand(1, 500); //problem-3000
     $_supertag = $owner_data["login"]."@".$owner_data["node_id"].":".$this->npjTranslit($data["tag"]);
     $supertag = $db->Quote($_supertag);
    }
    $_depth_array = explode("/", $data["tag"] );
    $data["depth"] = sizeof( $_depth_array );
    $data["type"] = 1;
    $data["created_datetime"] = $_now;
    
    $query = "insert into ".$rh->db_prefix."records set ";
    $query_end = ", supertag=".$supertag.
    ", created_datetime=".$now.
    ", edited_datetime=".$now;
    break;
   case 4:
   //old document
    $query = "select version_id from ".$rh->db_prefix."record_versions where record_id=".$db->Quote($data["id"]).
     " order by version_id desc";

    $rs = $db->Execute($query);

    //сохраняем предыдущую версию. NB: безо всяких объектов типа version
    $query = "insert into ".$rh->db_prefix."record_versions (record_id, version_id, body, body_r, formatting,".
     "edited_datetime, version_tag, edited_user_login, edited_user_name, edited_user_node_id) ".
     "select record_id, ".($rs->fields["version_id"]+1).", body, body_r, formatting, edited_datetime, version_tag, ".
     "edited_user_login, edited_user_name, edited_user_node_id from ".$rh->db_prefix."records where record_id=".
     $db->Quote($data["id"]);

    $_depth_array = explode("/", $data["tag"] );
    $data["depth"] = sizeof( $_depth_array );

    $db->Execute($query);
    $debug->Trace("Saving version: ".$query);

    $query = "update ".$rh->db_prefix."records set ";
    $query_end = ", edited_datetime=NOW() where supertag=".$supertag;
    break;
   case 5:
   //new document
    $data["type"] = 2;

    $_depth_array = explode("/", $data["tag"] );
    if ($data["tag"] == "")
     $data["depth"] = 0;
    else
     $data["depth"] = sizeof( $_depth_array );
    $data["created_datetime"] = $_now;
    $query = "insert into ".$rh->db_prefix."records set ";

    $query_end = ", supertag=".$supertag.
    ", created_datetime=".$now.
    ", edited_datetime=".$now;
    break;
  }

  // Урезка ката ==================================================================================
  if ($type==RECORD_POST)
  {
    $data["body_post"] = $data["body_post"]."</cut>"; // what if have not-closed <cut>?
    $data["body_post"] = $this->Format($data["body_post"], "cut", array("supertag"=>$_supertag ));
    $data["body_post"] = preg_replace( "!</cut>$!", "", $data["body_post"]); // remove kostyli
  }

  //========= Финальное конструирование запроса согласно варианта
  $flag=0;
  $debug->Trace("Variant: ".$variant);
  foreach( $fields[$variant] as $f )
  {
    if ($flag) $query.=", "; else $flag=1;
    $query.= $f."=".$db->Quote( $data[$f] );

  }
  $debug->Trace("Saving record: ".$query.$query_end);

  $_db_raiseErrorFn =  $db->raiseErrorFn;
  $db->raiseErrorFn = "DBAL_Error_Silent";
  $db->Execute( $query.$query_end );
  $db->raiseErrorFn = $_db_raiseErrorFn;
  if (sizeof($debug->dbal_errors))
  {
    $this->_record_save_forbidden = true; // flag to parent
    return $this->Forbidden("ThereIsSuchRecord"); // !!! add reason in messageset
  }

  if ($rh->debug_file_name)
  {
     $fp = fopen( $rh->debug_file_name ,"a");
     fputs($fp,"[".date("Y-m-d H:i:s")."] (".
               sprintf("%0.4f",$debug->_getmicrotime()).
               ") -- Save main sql processed: ". $this->data["tag"]."\n");
     fclose($fp);
  }

  //========== Восстанавливаем собственно идшник новой записи ====
  $data["_record_id"]=$db->Insert_ID(); //??? на не-mysql может быть косячным.
  if (!$data["record_id"]) $data["record_id"]=$data["_record_id"]; 

?>