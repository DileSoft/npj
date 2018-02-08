<?php

// calling from Form::Load( &$data )

  // вставл€ем по текущему состо€нию
  $p = $this->form_config["tpl_prefix"];
  $this->form_config["tpl_prefix"] .= "mail_";
  $body = $this->ParsePreview( );
  $this->form_config["tpl_prefix"] = $p;

  $rh = &$this->rh;

/* old
  $mail->set_body( $body );
  $mail->build_message();
  $mail->send( $rh->project_name, $rh->admin_mail, $rh->project_name, $rh->admin_mail, 
               $this->form_config["name"] );


  DEPRECATED vvvv
*/
  $rh->UseLib("HtmlMimeMail2");
  $mail = &new HtmlMimeMail2();

  $recipients = array("ni@sharpdesign.ru");
  $from = "".$tpl->message_set["Mail:From"]." <".$rh->node_mail.">";

  $mail->setHtml("ALERT! npj\handlers\forms\_mail.php", "ALERT! npj\handlers\forms\_mail.php");
  $mail->setFrom($from);
  $mail->setSubject($subject);
  $mail->buildMessage($tpl->message_set["Encodings"], $rh->method_mailsend);
  $result = $mail->send($recipients, $rh->method_mailsend);
  if (!$result) $debug->Trace_R($mail->errors);


  // а потом "отмен€ем" к черт€м
  include( $__dir."_cancel.php" );

?>