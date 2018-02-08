<?php

  // 1. это можно делать всем, проверка прав не нужна
  // 2. соответствие формы проверяется в ConfirmForm

    $rh = &$this->rh;
    $db = &$this->rh->db;
    $tpl = &$this->rh->tpl;
    $debug = &$this->rh->debug;

    // нужно получить профиль (в основном -- ради email, email_confirm)
    $data = $rh->object->Load( 3 );
    if ($data == false) { $this->success = false; return; }

    // можем ли высылать (есть ли мыло, подтверждено ли)
    if (($data["email_confirm"] != "") || ($data["email"] == "")) { $this->success = false; return; }

    // сгенерируем пароль
    $tmp_pwd = md5( date("Y-m-d H:i:s").$data["password"] );
    $sql = "update ".$rh->db_prefix."profiles set temporary_password=".$db->Quote($tmp_pwd).", ".
           "temporary_password_created=".$db->Quote( date("Y-m-d H:i:s") ).
           " where user_id=".$db->Quote($data["user_id"]);
    $db->Execute( $sql );

    $_theme=$tpl->theme; 
    $rh->absolute_urls = 1;
    $tpl->theme = $rh->theme;
    // придумать что написано в письме
    $tpl->LoadDomain( array(
        "_Email"   => $params["email"],
        "_Confirm" => $tmp_pwd,
        "Href:Confirm" => 
          $rh->object->Href($rh->object->name.":settings/password/new/".$tmp_pwd,0),
        "Link:BlindConfirm" => 
          "<a href=\"".$rh->object->Href($rh->object->name.":settings/password/new")."\">".
          $rh->object->Href($rh->object->name.":settings/password/new").
          "</a>",
                      ) );
    $tpl->Assign("Mail.UserName", $data["user_name"]);
    $tpl->Assign("Link:Mail.Login", $rh->object->Link( $data["login"]."@".$data["node_id"] ));
    $_html = $tpl->Parse( "mail/confirmation.html:Body" );
    $_text = $tpl->Format( $_html, "html2text" );

    // отправить письмо
    $recipients = array("".$data["user_name"]." <".$data["email"].">");
    $from = "".$tpl->message_set["Mail:From"]." <".$rh->node_mail.">";
    $subject = $tpl->Parse( "mail/confirmation.html:Subject" );

    $rh->object->prepMail($subject, $_html, $_text, $from);
    $rh->object->sendMail($recipients);

    $tpl->theme = $_theme; 
    $rh->debug->Trace(" sent mail <br />".$_html );
    $rh->absolute_urls = 0;
    
    $this->success = true;
?>