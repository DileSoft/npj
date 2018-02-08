<?php

 $param = explode("Z", $this->data["npjcode"]);
 $user_id = $param[0];
 $record_id = $param[1];
 $comment_id = $param[2];
 $security_code = $param[3];
 if ($security_code!=md5( $record_id.$comment_id.$user_id.$rh->node_secret_word ))
 {
  $this->data["error"] = "COMMENTS: Wrong NPJCODE:".$this->data["npjcode"];
  $debug->trace($security_code."<>".md5( $record_id.$comment_id.$user_id.$rh->node_secret_word ));
//  $debug->Error();
  $this->Save();
  return FORBIDDEN;
 }

 /* $fields = array(
            "pic_id", "subject", "body_post", 
            "user_id", "user_login", "user_name", "user_node_id", 
            "created_datetime", "ip_xff",
            "record_id", "parent_id", "lft_id", "rgt_id",
                  );
 */
 $rs = $db->Execute("SELECT login, user_name, node_id, _pic_id, _formatting FROM ".$rh->db_prefix."users where user_id=".$db->Quote($user_id));
 if (!$rs->fields)
 {
  $this->data["error"] = "COMMENTS: No such user_id";
  $this->Save();
 }
 else
 {
  $rs2 = $db->Execute("SELECT supertag, disallow_comments, is_announce FROM ".$rh->db_prefix."records where record_id=".$db->Quote($record_id));
  if (!$rs2->fields)
  {
   $this->data["error"] = "COMMENTS: No such record_id";
   $this->Save();
  }
  else if ($rs2->fields["disallow_comments"]==1 || $rs2->fields["is_announce"]==2)
  {
   $this->data["error"] = "COMMENTS: comments not allowed";
   $this->Save();
  }
  else 
  {
   $supertag = $rs2->fields["supertag"];
   $principal->MaskById( $user_id );
   $comment =& new NpjObject(&$rh, $supertag."/comments");
   $account =& new NpjObject(&$rh, $comment->npj_account);
   $record  =& new NpjObject(&$rh, $supertag);
   $account->Load(2);
   $record->Load(2);

   if (!$account->HasAccess( &$principal, "not_acl", "banlist" )) 
   {
    $this->data["error"] = "COMMENTS: comments not allowed2";
    $this->Save();
   }
   else if (!$record->HasAccess( &$principal, $record->security_handlers[$record->GetType()], "comment" )) 
   {
    $this->data["error"] = "COMMENTS: comments not allowed3";
    $this->Save();
   }
   else
   {

    $comment->data["pic_id"] = $rs->fields["_pic_id"];
    $debug->Trace_R($this->data["headers"]);
    $comment->data["subject"] = $this->parser->um_decode($this->data["headers"]["subject"]);
    if (stristr($comment->data["subject"], $tpl->message_set["Mail:DeleteSubjectRec"]) ||
        stristr($comment->data["subject"], $tpl->message_set["Mail:DeleteSubjectCom"])) 
      $comment->data["subject"] = "";
    if ($this->data["text"])
    {
     $text = $this->data["text"];
    }
    else 
    {
      $text = $this->Format($this->data["html"], "after_htmlmail");
    }
    $formatting = $rs->fields["_formatting"];
    //!!! пока нет форматтера htmlmail
    if ($formatting=="rawhtml") $formatting="simplebr";

    $comment->data["body_post"] = 
       $this->Format(
         $this->Format(
           $this->Format(trim($text), $formatting, "pre"), 
         $formatting), 
       $formatting, "post");
    $comment->data["user_id"] = $user_id;
    $comment->data["user_login"] = $rs->fields["login"];
    $comment->data["user_name"] = $rs->fields["user_name"];
    $comment->data["user_node_id"] = $rs->fields["node_id"];
    $comment->data["created_datetime"] = date("Y-m-d H:i:s");
    $comment->data["record_id"] = $record_id;
    $comment->data["parent_id"] = (int)$comment_id;
    $comment->data["lft_id"] = 0;
    $comment->data["rgt_id"] = 0;
    $debug->Trace($comment->data["body_post"]);
    $comment->Save();

   }
   $principal->UnMask();
  }
 }
 
  //0, subject, body, user etc, created, "[mail]", record_id, 0/comment_id, 0, 0

?>