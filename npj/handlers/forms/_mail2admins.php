<?php

// calling from Form::Load( &$data )

  // вставляем по текущему состоянию
  $p = $this->form_config["tpl_prefix"];
  $this->form_config["tpl_prefix"] .= "mail_html_";
  $body = $this->ParsePreview( );
  $this->form_config["tpl_prefix"] = $p;

  $rh = &$this->rh;

  $rh->UseLib( "HtmlMimeMail2" );
  $mail = &new HtmlMimeMail2();

  $mail->setSubject( $rh->tpl->Format( $this->form_config["name"], "html2text") );
  $mail->setHtml   ( $body, $rh->tpl->Format( $body, "html2text")               );

  $mail->setHeader("X-Mailer", "Manifesto");

  $mail->setFrom($this->rh->tpl->message_set["Mail:From"]." <".$this->rh->node_mail.">");

  $mail->buildMessage($rh->tpl->message_set["Encodings"], $this->rh->method_mailsend);
  if (!$this->rh->no_email) 
  {
    // получить емайлы всех супермодераторов узла.
    // пока получаем из конфига, а в дальнейшем хотелось бы какой-то более вменяемой схемы.
    // [!!!..] refactoring node admins
    $admins_draft = explode(" ", $this->rh->node_admins);
    $admins = array();
    foreach($admins_draft as $k=>$v)
    {
      if (strpos($v, "@") === false) $v.="@".$this->rh->node_name;
      $person = &new NpjObject( &$this->rh, $v );
      if ($person->Load(3) != NOT_EXIST)
        if ($person->data["email"] != "")
          if ($person->data["email_confirm "] == "")
            $admins[] = $person->data["user_name"]." <".$person->data["email"].">";
    }

    $result = $mail->send($admins, $this->rh->method_mailsend);
  }

?>
