<?php
/*
Запускается по htCron.
Формирует пакет репликации и отсылает его по почте. 
Следующий узел хранится в таблице NPZ.
--Пока размер пакета не более 200K и не более 10 записей.--

  !!! NB: Если запись уже удалили, то в очереди остаётся висящий хвост. =(

  !!!!!: Про пакет не более 200К я не нашёл нигде. kuso@npj

*/
 $fields_record  = array("supertag","tag","depth","id","record_id","user_id","type","created_datetime","user_datetime",
                    "disallow_comments","disallow_notify_comments","disallow_syndicate","formatting","is_keyword",
                    "is_announce","is_digest","pic_id","subject","body","rare");
// что делать с этими: crossposted, version_tag, keywords ???
 $fields_user    = array("user_id","login","user_name","node_id","account_type","alive","_formatting","_pic_id",
                    "theme","lang","email","more");
 $fields_reprule = array("object_id","object_class","node_id","datetime","rep_rule_id");

 $fields_comment = array("comment_id", "active", "pic_id", "subject", "body_post", "user_id", "user_login", "user_name",
                    "user_node_id", "created_datetime", "ip_xff", "record_id", "rep_original_id", "rep_node_id", 
                    "replicator_user_id");

 define("MAX_RECORDS", 15);
 define("MAX_COMMENTS", 25);

/////////////// узнаём node_id 
 $rs = $db->Execute( "select * from ".$rh->db_prefix."npz where id=".$db->Quote($_GET["npzid"]));  
 $last = $rs->fields["param"];
 if (!$last) // seems that there's no record in htcron
 {
   $addr = $rh->node->data["url"];

   $rs = $db->Execute("SELECT * FROM ".$rh->db_prefix."nodes ORDER BY node_id");
   $n  = $rs->fields["node_id"];
   $rs = $db->Execute("DELETE FROM ".$rh->db_prefix."npz WHERE command='".$addr."repsend'");

   $rs = $db->Execute("INSERT INTO ".$rh->db_prefix."npz (spec, command, last, chunk, time_last_chunk, state, param) VALUES ".
         "('* * * * *', '".$addr."repsend', '1073299946', '-1', '', 0, '".$n."')");
 }

 $debug->Trace("NODE 4 REP: ".$last);

/////////////// достаём из очереди всё для этого node_id 
 $rs = $db->Execute( "select * from ".$rh->db_prefix."replica_queue where node_id=".$db->Quote($last));  
 $a = $rs->GetArray();
 $debug->Trace_R($a);

 // если в очереди ничего не было -- включаем "турбо-режим" и мотаем "до первого непустого"
 $turbo_mode_node_id = false;
 if (sizeof($a) == 0)
 {
     $rs = $db->SelectLimit( "SELECT n.node_id FROM ".$rh->db_prefix."nodes as n, ".
                                          $rh->db_prefix."replica_queue as rq ".
                         " WHERE rq.node_id = n.node_id ".
                         " ORDER BY n.node_id",1);
     $next_nodes = $rs->GetArray();
     if (sizeof($next_nodes))
     {
       $turbo_mode_next = $next_nodes[0]["node_id"];
       $debug->Trace("turbo mode on = ".$turbo_mode_next);
     }
 }

 $rules = array(); $num_rec = 0; $num_comm = 0;
/////////////// формируем массив "правил" и массивы id-шников записей и комментариев
 foreach( $a as $reprule ) 
 {
   if ($reprule["object_class"]=="record" && $num_rec<MAX_RECORDS)
   {
     $_record_ids[] = $reprule["object_id"];
     $num_rec++;
     $_rules = array();
     foreach ($fields_reprule as $fld)
       $_rules[$fld] = $reprule[$fld];
     $rules[] = $_rules;
   }
   else 
     if ($reprule["object_class"]=="comment" && $num_comm<MAX_COMMENTS)
     {
       $_comment_ids[] = $reprule["object_id"];
       $num_comm++;
       $_rules = array();
       foreach ($fields_reprule as $fld)
         $_rules[$fld] = $reprule[$fld];
       $rules[] = $_rules;
     }
     else 
       if ($num_rec>=MAX_RECORDS && $num_comm>=MAX_COMMENTS) break;
 }

/////////////// уникализируем
 if (is_array($_record_ids)) $record_ids = array_unique($_record_ids);
 if (is_array($_comment_ids)) $comment_ids = array_unique($_comment_ids);

 $debug->Trace_R($record_ids);

/////////////// загружаем первые MAX_RECORDS записей

 $n_rec=0;
 if ($record_ids)
 foreach ($record_ids as $id)
 {
   $record =& $this->_LoadById($id, 4, "record");

   if ($record["disallow_replicate"] || $record["group1"]!=0) continue;

   foreach ($fields_record as $fld)
     $records[$id][$fld] = $record[$fld];

   $user =& $this->_LoadById($records[$id]["user_id"], 2, "account");
   foreach ($fields_user as $fld)
     $users[$user["id"]][$fld] = $user[$fld];

   $n_rec++;
   if ($n_rec>=MAX_RECORDS) break;
 }

/////////////// загружаем первые MAX_COMMENTS комментов
 $n_com=0;
 if ($comment_ids)
 foreach ($comment_ids as $id)
 {
   $comment =& $this->_LoadById($id, 3, "comments");

   if ($comment["disallow_replicate"]) continue;

   $rec =& $this->_LoadById($comment["record_id"], 1, "record");

   if ($rec["group1"]!=0) continue;

   foreach ($fields_comment as $fld)
     $comments[$id][$fld] = $comment[$fld];

   $user =& $this->_LoadById($comments[$id]["user_id"], 2, "account");
   foreach ($fields_user as $fld)
     $users[$user["id"]][$fld] = $user[$fld];

   $n_com++;
   if ($n_com>=MAX_COMMENTS) break;
 }

 if ($n_com+$n_rec>0)
 {

  /////////////// отправляем письмо; здесь нужна архивация !!!!
   $node = $rh->node_name;
   $struct = compact("rules", "records", "comments", "users", "node");
   $text = serialize($struct);
   $text = str_replace("\t", "\t1",$text);
   $text = str_replace("\n", "\t2",$text);
   $text = str_replace("\r", "\t3",$text);

  ///// Извлекаем емейл узла
   $rs = $db->Execute( "select email from ".$rh->db_prefix."nodes where node_id=".$db->Quote($last));  
   $recipient_email = $rs->fields["email"];
   $recipients = array("node <".$recipient_email.">");
   $from = "".$tpl->message_set["Mail:From"]." <".$rh->node_mail.">";

   $subject = "REPLICATION";

   // $recipients[] = "kuso <nikolay@jetstyle.ru>";

   $this->prepMail($subject, NULL, $text, $from);
   $tpl->message_set["Encodings"]["text_wrap"]=998;
   $tpl->message_set["Encodings"]["text_encoding"]="base64";
   $this->sendMail($recipients);

  /////////////// чистим очередь
   foreach ($records as $k=>$v)
   {
      $sql = "delete from ".$rh->db_prefix."replica_queue where ".
                          "node_id=".$db->Quote($last)." AND object_id=".$db->Quote($k).
                          "AND object_class=".$db->Quote("record");
      $rs = $db->Execute( $sql );
      $debug->Trace( $sql );
   }

   foreach ($comments as $k=>$v)
    $rs = $db->Execute( "delete from ".$rh->db_prefix."replica_queue where ".
                        "node_id=".$db->Quote($last)." AND object_id=".$db->Quote($k).
                        "AND object_class=".$db->Quote("comment")
                      );  

 }

/////////////// прописываем новый "СЛЕДУЮЩИЙ" узел
 if ($turbo_mode_next) $next = $turbo_mode_next;
 else
 {
   $rs = $db->Execute( "SELECT * FROM ".$rh->db_prefix."nodes ORDER BY node_id");
   $nodes = $rs->GetArray();
   foreach ($nodes as $node) 
   {
    if ($flag) 
    {
     $next = $node["node_id"];
     break;
    }
    if ($node["node_id"]==$last) $flag = 1;
   }
   if (!$next) $next = $nodes[0]["node_id"];
 }

 $rs = $db->Execute( "UPDATE ".$rh->db_prefix."npz SET param=".$db->Quote($next).
                     "WHERE id=".$db->Quote($_GET["npzid"]));  
/*
Хочу на выходе структуру:
$struc = array(
 "rules" => array(
   "518" => array(
    ...все штуки правила...
   );
   ...
 );
 "records" =>  array(
   "267" => array(
    ...все штуки записи без рефов...
   );
   ...
 );
 "comments" =>  array(
   "267" => array(
    ...все штуки камента...
   );
   ...
 );
);

*/
/*
 $debug->Error( "REPLICATION_CHASM: to ".  $recipient_email ." -- ". $subject . " == ". 
                implode(",", $recipients). "<hr />". $text."<hr />",
                4 );
*/

 $debug->Error("REPSEND: hmm");


 return GRANTED;
?>