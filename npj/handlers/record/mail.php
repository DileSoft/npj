<?php

  // поставка записей в очередь на печать
  // ??? пока тут же и отсылка.
  // параметры: 

  // 1. получить всё что можно
  $data = $this->Load( 3 );
  if (!is_array($data)) return $this->NotFound("RecordNotFound"); 

  // for old but edited posts no mailing is done
  if (!$this->is_new && $data["type"]==RECORD_POST) return GRANTED; //!!!!

  if (!$this->is_new) $method = "diff";
  else if ($data["type"]==RECORD_POST) $method = "post";
  else $method = "add";

  $this->mail_method = $method;

  $users   = array();  $moders  = array();
  $rep_ids = array();  $rep_users = array();

  // ==== facet - subscriptions ====
  $sql = "select s.user_id, s.object_method ".
         " from ".$rh->db_prefix."records_ref as ref, ".$rh->db_prefix."subscription as s ".
         " where ref.record_id=".$db->Quote($this->data["record_id"])." AND s.object_class=".$db->Quote("facet").
         " AND s.object_id = ref.keyword_id ".
         " AND s.object_method=".$db->Quote($method)." AND ref.need_moderation=0";
  $rs = $db->Execute( $sql ); 
  $a = $rs->GetArray();
  foreach( $a as $user ) 
   $users[] = $user["user_id"];

  // ==== facet - replica ====
  if ($this->is_new && $data["type"]==RECORD_POST)
  {
   $sql = "select s.user_id, s.method_option ".
          " from ".$rh->db_prefix."records_ref as ref, ".$rh->db_prefix."subscription as s ".
          " where ref.record_id=".$db->Quote($this->data["record_id"])." AND s.object_class=".$db->Quote("facet").
          " AND s.object_id = ref.keyword_id ".
          " AND s.object_method=".$db->Quote("replica")." AND ref.need_moderation=0";
   $rs = $db->Execute( $sql ); 
   $a = $rs->GetArray();
   foreach( $a as $u ) { 
    $rep_ids[]   = $u["method_option"];
    $rep_users[] = $u["user_id"];
   }
  }

  // ==== facet - moderations ====
  if ($method == "post")
  {
   $sql = "select ug.user_id ".
          " from ".$rh->db_prefix."records_ref as ref, ".
                   $rh->db_prefix."user_groups as ug, ".
                   $rh->db_prefix."groups as g ".
          " where ref.record_id=".$db->Quote($this->data["record_id"]).
          " AND g.user_id = ref.keyword_user_id AND g.group_rank>=".GROUPS_MODERATORS.
          " AND g.group_id = ug.group_id".
          " AND ref.need_moderation=1";
   $rs = $db->Execute( $sql ); 
   $a = $rs->GetArray();
   foreach( $a as $user ) 
    $moders[] = $user["user_id"];
  }

  // ==== clusters ====
  $sql = "select r.record_id, r.subject, r.tag, r.supertag, s.user_id from ".$rh->db_prefix."records as r, ".
         $rh->db_prefix."subscription as s ".
         " where ".$db->Quote($this->data["supertag"])." LIKE CONCAT(supertag, '%') ".
         " AND s.object_id = r.record_id AND s.object_class=".$db->Quote("cluster").
         " AND s.object_method=".$db->Quote($method);
  $rs = $db->Execute( $sql ); 
  $a = $rs->GetArray();
  foreach( $a as $user ) 
   $users[] = $user["user_id"];

  // ==== clusters - replica ====
  if ($this->is_new && $data["type"]==RECORD_POST)
  {
   $sql = "select s.user_id, s.method_option from ".$rh->db_prefix."records as r, ".
          $rh->db_prefix."subscription as s ".
          " where ".$db->Quote($this->data["supertag"])." LIKE CONCAT(supertag, '%') ".
          " AND s.object_id = r.record_id AND s.object_class=".$db->Quote("cluster").
          " AND s.object_method=".$db->Quote("replica");
   $rs = $db->Execute( $sql ); 
   $a = $rs->GetArray();
   foreach( $a as $u ) { 
    $rep_ids[]   = $u["method_option"];
    $rep_users[] = $u["user_id"];
   }
  }

  // ==== record ====
  if ($method=="diff")
  {
   $sql = "select s.user_id from ".$rh->db_prefix."subscription as s ".
          " WHERE s.object_class=".$db->Quote("record").
          " AND s.object_method=".$db->Quote($method).
          " AND s.object_id=".$db->Quote($this->data["record_id"]);
   $rs = $db->Execute( $sql ); 
   $a = $rs->GetArray();
   foreach( $a as $user ) 
    $users[] = $user["user_id"];
  }

  // 2a. проверка прав доступа к записи на просмотр  -- !!! refactoring рефакторинг
  $_users = array();
  foreach( $users as $user )
  { 
    $principal->MaskById( $user );
    if ($principal->IsGrantedTo( $this->security_handlers[$data["type"]], 
                                 "record", $data["record_id"]) )
      $_users[] = $user;
    $principal->UnMask();
  }

  // 2a. проверка прав доступа к реплицируемой записи на просмотр  -- !!! refactoring рефакторинг
  /*$c = 0;                   
  foreach( $rep_users as $user )
  { 
    $principal->MaskById( $user );
    if (!$principal->IsGrantedTo( $this->security_handlers[$data["type"]], 
                                 "record", $data["record_id"]) )
      unset($rep_ids[$c]);
    $principal->UnMask();
    $c++;
  }
*/
//  $debug->Trace($sql);
//  $debug->Trace_R("<br>\$_users".$_users."<br>");
//  $debug->Error_R($this->mail_method);
//  $debug->Error_R("this->mail_method:".$this->mail_method ."<br>");


// << max:30-09-2004 >>
// не заморожены ли аккаунты в (array) $_users
   if (is_array($_users) && count($_users))
   {
       $sql = "SELECT user_id FROM ".
              $this->rh->db_prefix."users"." WHERE alive=1 and user_id in (".
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
  if (is_array($_users)) 
    $this->Handler("mail_send", $_users, &$principal);
  $this->mail_method = "moder";
  if (is_array($moders)) 
    $this->Handler("mail_send", $moders, &$principal);

  if (is_array($rep_ids)) 
    $this->Handler("repqueue", $rep_ids, &$principal);

  return GRANTED;  

?>