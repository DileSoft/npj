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

    // проверяем, это только что зарегистрировавшийся пользователь или жертва бана?
    $sql = "select alive, owner_user_id from ".$rh->db_prefix."users ".
           " where user_id=".$db->Quote($data["user_id"]);
    $rs = $db->Execute( $sql );

    // популяция кластера теперь, раз уж подтвердили
    if ($rs->fields["alive"] == 0)
    {
      $owner_user_id = $rs->fields["owner_user_id"];
      $account = &new NpjObject( &$rh, $rh->object->name );
      $node_principal = &new NpjPrincipal( &$rh );
      $_a = &$account->Load(1);
      $principal_user_id = $rh->principal->data["user_id"];
      $principal_record_id = $rh->principal->data["root_record_id"];

      // account should become "owned" by registrar, not by "node admin"
      if ($owner_user_id > 0)
      {
        $_owner = &$account->_LoadById( $owner_user_id, 2 );
        $principal_user_id = $_owner["user_id"];
        $principal_record_id = $_owner["root_record_id"];
      }

      $rh->principal->MaskById( $_a["user_id"] );
      $account->Handler( "populate", array("_p_user_id"  =>$principal_user_id,
                                           "_p_record_id"=>$principal_record_id), 
                          &$node_principal );
      $rh->principal->UnMask();
    }

    // записываем новый alive state
    $sql = "update ".$rh->db_prefix."users set alive=1 ".
           " where user_id=".$db->Quote($data["user_id"]);
    $db->Execute( $sql );
    
    // отправка письма-оповещения о возвращении в светлые ряды
    if ($data["email_confirm"] === "")
    {
      $_t  = $tpl->theme;
      $tpl->theme = $rh->theme;
      if ($rs->fields["alive"] == 0) $tpl->MergeMessageSet( $rh->message_set."_confirm_account_welcome" );

      // придумать что написано в письме
      $rh->absolute_urls = 1;
      $tpl->Assign("Mail.UserName", $data["user_name"]);
      $tpl->Assign("Link:Mail.Login", $rh->object->Link( $data["login"]."@".$data["node_id"] ));
      $tpl->Assign("Href:LoginForm", $rh->object->Href( "login@".$data["node_id"].":".$data["login"] ));
      $_html = $tpl->Parse( "mail/notification".(($rs->fields["alive"] == 0)?"_welcome":"").".html:Body" );
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