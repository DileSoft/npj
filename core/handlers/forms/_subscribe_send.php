<?php
  $magic_word = "this is a magic word";

// calling from Form::Load( &$data )
  $rh    = &$this->rh;
  $debug = &$this->rh->debug;
  $db    = &$rh->db;

  // находим Subject, Body, Section_id
  $subject = "";
  $body    = "";
  $section = 0;
  foreach ($this->fields as $group)
   foreach ($group as $field)
   {
     if ($field->config["field"] == "subject") 
      $subject = $field->data;
     if ($field->config["field"] == "body") 
      $body    = $field->data;
     if ($field->config["field"] == "section_id") 
      $section = $field->data;
   }

  // получаем список адресов
  $query = "select email from ".$rh->db_prefix."subscribe_list where section_id=".$db->Quote($section).
           " and confirmation=".$db->Quote("");
  $rs = $db->Execute( $query );
  $data = $rs->GetArray(); $f=0;
  foreach ($data as $item)
  {
    if ($f) $mailto.=";"; else $f=1;
    $mailto.=$item["email"];
  }

  // отсылаем письмо
  // простор для оптимизации
  $rh->UseClass( "HtmlMimeMail", $rh->core_dir );
  foreach ($data as $item)
  {
    $mail = &new HtmlMimeMail();
    $html = $body.str_replace("[email]", $item["email"]."&checkout=".md5($item["email"].$magic_word), 
                     $this->form_config["mail_postfix"][$section]);
    $nohtml = str_replace("<br>", "\n", 
              str_replace("<li>", "  *  ", 
              str_replace("<hr>", "----------------------------", preg_replace( '/&.*?;/', '#', $html )
              )));

    $nohtml = preg_replace( "/<[^>]+>/i", "", $nohtml );
    $html = preg_replace( "/(((http:\/\/)|(mailto:))(\S{1,50}))\s/i", "<a href=$1>$1</a> ", $html );
    $html = preg_replace( "/(((http:\/\/)|(mailto:))(\S{50})(\S*))/i", "<a href=$1>$2$5...</a>", $html );

    $mail->set_charset( $tpl->message_set["Encoding"]);
    $mail->add_html( $html, $nohtml );
    $mail->build_message();
    $mail->send( '', $item["email"], $rh->cms_project_name, $rh->admin_mail, 
                 $rh->cms_project_name.": ".$subject );
  }


  // правим поля
  foreach ($this->fields as $k1=>$group)
   foreach ($group as $k2=>$field)
   {
     if ($field->config["field"] == "state") 
      $this->fields[$k1][$k2]->data = 1;
     if ($field->config["field"] == "sent_date") 
      $this->fields[$k1][$k2]->data = date("Y-m-d H:i:s");
   }
  
  if ($this->rh->state->Get("id"))
    include( $__dir."_update.php" );
  else
    include( $__dir."_insert.php" );

?>