<?php

  // 1. проверка прав делается где-то в аккаунте
  // 2. соответствие формы проверяется в ConfirmForm

    $rh = &$this->rh;
    $db = &$this->rh->db;
    $tpl = &$this->rh->tpl;
    $debug = &$this->rh->debug;

    // нужно получить юзерид (в основном -- ради email, email_confirm)
    $data = $rh->object->Load( 0 );
    if ($data == false) { $this->success = false; return; }

    // записываем freezed state
    $sql = "update ".$rh->db_prefix."users set alive=2 ".
           " where user_id=".$db->Quote($data["user_id"]);
    $db->Execute( $sql );

    // отправка письма-оповещения о заморозке
    if ($data["email_confirm"] === "")
    {
      $_t  = $tpl->theme;
      $tpl->theme = $rh->theme;

      // придумать что написано в письме
      $rh->absolute_urls = 1;
      $tpl->Assign("Mail.UserName", $data["user_name"]);
      $tpl->Assign("Link:Mail.Login", $rh->object->Link( $data["login"]."@".$data["node_id"] ));
      $tpl->Assign("Href:Mail.Unfreeze", $rh->object->Href( $data["login"]."@".$data["node_id"].":manage/unfreeze" ));
      $_html = $tpl->Parse( "mail/notification.html:Body" );
      $_html = str_replace( "%%alive%%", $rh->object->Href( $data["login"]."@".$data["node_id"].":manage/unfreeze", NPJ_ABSOLUTE ), $_html );
      $_text = $tpl->Format( $_html, "html2text" );
      $rh->absolute_urls = 0;

      // отправить письмо
      $recipients = array("".$data["user_name"]." <".$data["email"].">");
      $from = "".$tpl->message_set["Mail:From"]." <".$rh->node_mail.">";
      $subject = $tpl->Parse( "mail/notification.html:Subject" );

      $rh->object->prepMail($subject, $_html, $_text, $from);
      $rh->object->sendMail($recipients);

      $tpl->theme = $_theme; 
      //$debug->Trace(" sent mail <br />".$_html );
      
      $tpl->theme = $_t;
    }
    
    $this->success = true;
?>