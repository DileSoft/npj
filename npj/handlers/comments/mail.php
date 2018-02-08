<?php

  // поставка комментариев в очередь на печать
  // ??? пока тут же и отсылка.
  // параметры: 

  // 1. получить всё что можно
  $data = $this->Load( 3 );
  if (!is_array($data)) return $this->NotFound("CommentNotFound");
  $record = &new NpjObject( &$rh, $object->npj_account.":".$object->npj_context );
  $rdata = &$record->Load( 2 );
  if (!is_array($rdata)) return $this->NotFound("RecordNotFound");

  // 2. Получить список адресов
  $users = array();
  $rep_ids = array();  $rep_users = array();

  //    * подписчики записи (??? слабо тестировано)
  $sql = "select distinct s.user_id from ".$rh->db_prefix."subscription as s where object_class=".$db->Quote("comments").
         " and object_id=0 and method_option=".$db->Quote( $data["record_id"] );
  $rs = $db->Execute( $sql ); $a = $rs->GetArray();
  foreach( $a as $user ) $users[] = $user["user_id"];

  //    * ещё одни подписчики записи (??? слабо тестировано)
  $sql = "select distinct s.user_id from ".$rh->db_prefix."subscription as s where object_class=".$db->Quote("record").
         " and object_method=".$db->Quote("comments")." and object_id=".$db->Quote( $data["record_id"] );
  $rs = $db->Execute( $sql ); $a = $rs->GetArray();
  foreach( $a as $user ) $users[] = $user["user_id"];
//  $debug->Error($sql);
//  if (in_array(48, $users)) $debug->Error("ariman subscribed");

  //    * подписчики записи (replica)
  $sql = "select s.user_id, s.method_option from ".$rh->db_prefix."subscription as s where object_class=".$db->Quote("record").
         " and object_id=".$db->Quote( $data["record_id"] )." AND s.object_method=".$db->Quote("commentreplica");
  $rs = $db->Execute( $sql ); $a = $rs->GetArray();
  foreach( $a as $u ) { 
   $rep_ids[]   = $u["method_option"];
   $rep_users[] = $u["user_id"];
  }

  //    * подписчики отдельных подветвей (??? слабо тестировано)
  $sql = "select comment_id from ".$rh->db_prefix."comments where lft_id<".$db->Quote($data["lft_id"]).
         " and rgt_id>".$db->Quote($data["rgt_id"])." and record_id=".$db->Quote( $data["record_id"] );
  $rs = $db->Execute( $sql ); $a = $rs->GetArray(); $comments = array();
  foreach( $a as $comment ) $comments[] = $db->Quote($comment["comment_id"]);
  if (sizeof($comments))
  {
    $sql = "select distinct s.user_id from ".$rh->db_prefix."subscription as s where object_class=".$db->Quote("comments").
           " and object_id in (".implode(",",$comments).") and method_option=".$db->Quote( $data["record_id"] );
    $rs = $db->Execute( $sql ); $a = $rs->GetArray();
    foreach( $a as $user ) $users[] = $user["user_id"];
  }

  // ==== facet - subscriptions ====
  $sql = "select s.user_id, s.object_method ".
         " from ".$rh->db_prefix."records_ref as ref, ".$rh->db_prefix."subscription as s ".
         " where ref.record_id=".$db->Quote($data["record_id"])." AND s.object_class=".$db->Quote("facet").
         " AND s.object_id = ref.keyword_id ".
         " AND s.object_method=".$db->Quote("comments")." AND ref.need_moderation=0";
  $rs = $db->Execute( $sql ); 
  $a = $rs->GetArray();
  foreach( $a as $user ) 
   $users[] = $user["user_id"];

  // ==== facet - replica ====
  $sql = "select s.user_id, s.method_option ".
         " from ".$rh->db_prefix."records_ref as ref, ".$rh->db_prefix."subscription as s ".
         " where ref.record_id=".$db->Quote($data["record_id"])." AND s.object_class=".$db->Quote("facet").
         " AND s.object_id = ref.keyword_id ".
         " AND s.object_method=".$db->Quote("commentreplica")." AND ref.need_moderation=0";
  $rs = $db->Execute( $sql ); 
  $a = $rs->GetArray();
  foreach( $a as $u ) { 
   $rep_ids[]   = $u["method_option"];
   $rep_users[] = $u["user_id"];
  }

  // ==== clusters ====
  $sql = "select r.record_id, r.subject, r.tag, r.supertag, s.user_id from ".$rh->db_prefix."records as r, ".
         $rh->db_prefix."subscription as s ".
         " where ".$db->Quote($rdata["supertag"])." LIKE CONCAT(supertag, '%') ".
         " AND s.object_id = r.record_id AND s.object_class=".$db->Quote("cluster").
         " AND s.object_method=".$db->Quote("comments");
  $rs = $db->Execute( $sql ); 
  $a = $rs->GetArray();
  foreach( $a as $user ) 
   $users[] = $user["user_id"];

  // ==== clusters - replica ====
  $sql = "select s.user_id, s.method_option from ".$rh->db_prefix."records as r, ".
         $rh->db_prefix."subscription as s ".
         " where ".$db->Quote($rdata["supertag"])." LIKE CONCAT(supertag, '%') ".
         " AND s.object_id = r.record_id AND s.object_class=".$db->Quote("cluster").
         " AND s.object_method=".$db->Quote("commentreplica");
  $rs = $db->Execute( $sql ); 
  $a = $rs->GetArray();
  foreach( $a as $u ) { 
   $rep_ids[]   = $u["method_option"];
   $rep_users[] = $u["user_id"];
  }

  // 2a. проверка прав доступа к записи на просмотр  -- !!! refactoring рефакторинг
  $_users = array();
  foreach( $users as $user )
  { $principal->MaskById( $user );
    if ($principal->IsGrantedTo( $this->security_handlers[$rdata["type"]], 
                                 "record", $rdata["record_id"]) )
      $_users[] = $user;
    $principal->UnMask();
  }

  // 2a. проверка прав доступа к реплицируемой записи на просмотр  -- !!! refactoring рефакторинг
  $c = 0;
  foreach( $rep_users as $user )
  { 
    $principal->MaskById( $user );
    if (!$principal->IsGrantedTo( $this->security_handlers[$rdata["type"]], 
                                 "record", $rdata["record_id"]) )
      unset($rep_ids[$c]);
    $principal->UnMask();
    $c++;
  }

// << max:30-09-2004 >>
// не заморожены ли аккаунты в (array) $_users
   if (is_array($_users) && count($_users))
   {
       $sql = "SELECT user_id FROM ".
              $this->rh->db_prefix."users WHERE alive=1 and user_id in (".
              implode(",", $_users).")";
       $rs = $db->Execute( $sql );
       $rcpt_arr = $rs->GetArray();
       $_users = array();
       foreach ($rcpt_arr as $recV) // по всем записям в массиве
         $_users[] = $recV["user_id"];
   }
/*
 $rcpt_users содержит всех ЖИВЫХ подписчиков
*/
// <<  max:30-09-2004 />>

  // 3. ??? постановка в очередь // рассылка
  $this->Handler("mail_send", $_users, &$principal);

  if (is_array($rep_ids)) 
    $this->Handler("repqueue", $rep_ids, &$principal);

  return GRANTED;  

?>