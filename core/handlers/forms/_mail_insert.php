<?php

// calling from Form::Load( &$data )

  // вставляем по текущему состоянию
  $p = $this->form_config["tpl_prefix"];
  $this->form_config["tpl_prefix"] .= "mail_";
  $body = $this->ParsePreview( );
  $this->form_config["tpl_prefix"] = $p;

  $rh = &$this->rh;

  $rh->UseClass( "HtmlMimeMail", $rh->core_dir );

  $mail = &new HtmlMimeMail();
  $mail->set_body( $body );
  $mail->build_message();
  $mail->send( $rh->project_name, $rh->admin_mail, $rh->project_name, $rh->admin_mail, 
               $this->form_config["name"] );


  // а потом переходим к вставке
  include( $__dir."_insert.php" );

?>