<?php

  // модерирование записей

  $data = $this->Load(2);

  $record = &new NpjObject( &$rh, $rh->node_user.":" );
  $record_data = $record->_LoadById( $params[1], 3 );
  if (!is_array($record_data)) $this->Forbidden("RecordNotFound");
  $object->_PreparseArray( $record_data );

  // -------------------------------- отклонение сообщения ---------------
  if ($_POST["approve_decline"])
  {
    $query = "delete from ".$rh->db_prefix."records_ref ".
             " where record_id=".$db->Quote($params[1])." and ".
             " keyword_user_id=".$db->Quote($data["user_id"]);
    $db->Execute( $query );

    // новая пометка в компилированных полях 
    $object->CompileCrossposted($record_data["record_id"]);
    
    // sendmail
    $rs = $db->Execute( "select p.email_confirm, p.email, u.user_name from ".
                        $rh->db_prefix."profiles as p, ".
                        $rh->db_prefix."users as u ".
                        " where p.user_id = u.user_id and p.user_id=".
                        $db->Quote($record_data["user_id"] ) );
    if ($rs->fields["email_confirm"] == "")
    {  
       $tpl->Skin( $rh->theme );

         $tpl->MergeMessageSet( $rh->message_set."_moderate_decline");

         $t = $this->rh->absolute_urls;
         $this->rh->absolute_urls = true;

         // ???? suspicious formatting "wiki" used. 
         $tpl->Assign( "Reason", $tpl->Format( $_POST["reason"], "wiki" ) );
         // ----
         
         $tpl->Assign( "Link:Login",    $this->Link( $record_data["edited_user_login"]."@".
                                                  $record_data["edited_user_node_id"]));
         $tpl->Assign( "UserName",    $record_data["edited_user_name"] );
         $tpl->Assign( "Link:Record",    $this->Link( $record_data["edited_user_login"]."@".
                       $record_data["edited_user_node_id"].":".$record_data["tag"] ) );

         $tpl->Assign( "Subject",    $record_data["non_empty_subject"] );

         // отправить письмо в формате HTML
         $html = $tpl->Parse( "mail/moderation.decline.html:Decline" );
         $text = $tpl->Format($html, "html2text" );


         $recipients = array("".$rs->fields["user_name"]." <".$rs->fields["email"].">");

         $from = "".$tpl->message_set["Mail:From"]." <".$rh->node_mail.">";
         $subject = $tpl->Parse( "mail/moderation.decline.html:DeclineSubject" );

         $this->prepMail($subject, $html, $text, $from);
         $this->sendMail($recipients);

         $this->rh->absolute_urls = $t;

         $debug->Trace( $from);
         $debug->Trace( $html);
         $debug->Trace( $text);
         $debug->Trace( $subject);
         $debug->Trace( $recipients[0]);

       $tpl->UnSkin();
    }
    // --------
    $rh->Redirect( $this->Href( $record_data["supertag"], NPJ_ABSOLUTE, IGNORE_STATE ), INGORE_STATE );
  }
  // -------------------------------- получение подтверждения, что замодерировано ---------------
  if ($_POST["approve"])
  {
    $query = "update ".$rh->db_prefix."records_ref set need_moderation=0 ".
             " where record_id=".$db->Quote($params[1])." and ".
             " keyword_user_id=".$db->Quote($data["user_id"]);
    $db->Execute( $query );

    // новая пометка в компилированных полях 
    $object->CompileCrossposted($record_data["record_id"]);

    $rh->Redirect( $this->Href( $record_data["supertag"], NPJ_ABSOLUTE, IGNORE_STATE ), INGORE_STATE );
  }
  // -------------------------------- сброс статуса "отмодерировано" ----------------------------
  if ($params[2] == "reset")
  {
    $tpl->Assign( "Confirm.RecordId",   $params[1] ); 
    $tpl->Assign( "Confirm.AccountId",  $data["user_id"] ); 

    $rh->UseClass("ConfirmForm", $rh->core_dir);
    $confirm = &new ConfirmForm( &$rh, "moderate_reset", $rh->message_set."_confirm_moderate_reset" );
    $result = $confirm->Handle();
    if ($result === false) $result = $confirm->ParseConfirm();
    
    $result = str_replace( "%%href%%", 
            $this->Href( $record_data["supertag"], NPJ_ABSOLUTE, IGNORE_STATE ),
            $result );
  
    $tpl->Assign("Preparsed:CONTENT", $result);
    return GRANTED;
  }
  // -------------------------------- вывод типа сообщения что типа отказ в публикации ----------------------------

//  $debug->Trace_R( $record_data );
//  $debug->Error( $record_data["supertag"] );

  $tpl->Assign( "Href:Record",    $this->Href( $record_data["supertag"], NPJ_ABSOLUTE, IGNORE_STATE ) );
  $tpl->Assign( "Link:Record",    $this->Link( $record_data["edited_user_login"]."@".
                                               $record_data["edited_user_node_id"].":".$record_data["tag"] ) );
  $tpl->Assign( "Link:Author",    $this->Link( $record_data["edited_user_login"]."@".
                                               $record_data["edited_user_node_id"]));
  $tpl->Assign( "Author",    $record_data["edited_user_name"] );
  $tpl->Assign( "Record.Subject", $record_data["non_empty_subject"]);

  $tpl->Assign( "Form:Decline", $state->FormStart( MSS_POST, $rh->url ) );
  $tpl->Assign( "/Form", $state->FormEnd());

  $t = $tpl->theme; $tpl->theme = $rh->theme;
    $tpl->Parse( "account.moderate.html:Decline", "Preparsed:CONTENT" );
  $tpl->theme = $t;

  $tpl->Assign( "Preparsed:TITLE", "Отклонить запрос о публикации сообщения" ); // !!! to message_set

  return GRANTED;

?>