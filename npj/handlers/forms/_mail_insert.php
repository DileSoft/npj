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
  $mail->setHtml($body, $rh->tpl->Format( $body, "html2text") );

  $mail->setHeader("X-Mailer", "Manifesto");
  $mail->setFrom($rh->cms_project_name." <".$rh->admin_mail.">");
  //$mail->SetBcc( implode(";", $emails) );

  $mail->buildMessage($rh->tpl->message_set["Encodings"], $this->rh->method_mailsend);
  if (!$this->rh->no_email) 
  {
    $result = $mail->send(array("Редактор сайта <".$rh->admin_mail.">"), $this->rh->method_mailsend);
  }

  // а потом переходим к вставке
  include( $__dir."_insert.php" );

?>