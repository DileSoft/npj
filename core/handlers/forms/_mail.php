<?php

// calling from Form::Load( &$data )

  // вставл€ем по текущему состо€нию
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


  // а потом "отмен€ем" к черт€м
  include( $__dir."_cancel.php" );

?>