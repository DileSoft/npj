<?php

  // непосредственная отсылка записи почтой.
  // параметры: 
  //  * массив id-шников пользователей, которым отсылать

  // 1. получить всё что можно
  $rh->absolute_urls = 1;

  $data = &$this->Load( 4 );
  if (!is_array($data)) return $this->NotFound("RecordNotFound");

  $tpl->Assign("Href:Mail.Record", $this->Href( $this->npj_object_address, NPJ_ABSOLUTE ) );
//  $debug->Error($tpl->GetValue("Href:Mail.Record"));

  $tpl->Assign("Link:Mail.Record.User", $this->Link($data["edited_user_login"]."@".$data["edited_user_node_id"]));
  $tpl->Assign("Mail.Record.UserName",  $data["edited_user_name"] );
  if ($this->mail_method!="diff") 
  {
    $tpl->Assign("Mail.Record.Body",    $this->Format($this->Format($data["body_r"], $data["formatting"], array("default"=>"post","diff"=>1)), "absurl") );

   // ключслова + сообщества
   $tpl->Assign("Mail.Record.Keywords",     ""); 
   $tpl->Assign("Mail.Record.Crossposted",  "");
   if ($data["keywords"] != "!")
     $tpl->Assign("Mail.Record.Keywords",     $this->Format( $data["keywords"], "absurl", "rip_img" ) );
   if ($data["crossposted"] != "!")
     $tpl->Assign("Mail.Record.Crossposted",  $this->Format( $data["crossposted"], "absurl", "rip_img" ) );
   $tpl->Assign("Mail.Record.Keywords+Crossposted", $tpl->GetValue("Mail.Record.Keywords") || 
                                                    $tpl->GetValue("Mail.Record.Crossposted") ); 
  
  }
  else 
  {
   $sql = "select max(version_id) as vid from ".$rh->db_prefix."record_versions where record_id=".
     $db->Quote($this->data["record_id"]);
   $rs = $db->Execute($sql);
   $latest = $rs->fields["vid"];
   $version = &new NpjObject( &$rh, $this->npj_object_address."/versions/".$latest ); 
   $version->Handler( "diff", array("fastdiff"), &$principal );
   $tpl->Assign("Mail.Record.Body",   $this->Format($tpl->GetValue("Preparsed:CONTENT"), "absurl")  );
  }
  $tpl->Assign("Mail.Record.Subject", $data["subject"] );
  if ($data["subject"])   $tpl->Assign("Mail.Record.Subject2", ": ".$data["subject"] );
  $tpl->Assign("Mail.Record.DT",      $data["created_datetime"] );
  $mail_from = $tpl->Format($data["edited_user_name"],"html2text")." - ".$data["edited_user_login"]."@".$data["edited_user_node_id"].
               " (".$tpl->message_set["Mail:From"].")";

  // не отправлять самому себе
  $params = array_diff( $params, array( $principal->data["user_id"] ) );

  // 3. Сфабриковать письмо в html
  $tpl->MergeMessageSet( $rh->message_set."_mail_integration" );
  $_t = $tpl->theme;
  $tpl->theme = $rh->theme;

  $tplt = "mail/integration.html:";
  $html = $tpl->Parse( $tplt."Record_".ucfirst($this->mail_method) );
  $html.= $tpl->Parse( $tplt."Record_Body_".ucfirst($this->mail_method) );
  $html.= $tpl->Parse( $tplt."Record_Reply" );
  $html.= $tpl->Parse( $tplt."Goodbye" );

  $subject = trim($tpl->Parse( $tplt."Record_Subject_".ucfirst($this->mail_method) ));
  
  $tpl->theme = $_t;

  // 4. Перевести его в текст
  $text = $tpl->Format( $html, "html2text" );

  // 6. Всосать адреса из БД
  if (sizeof($params) == 0) return GRANTED;
  $utility_mail = &$rh->UtilityMail();
  $a = $utility_mail->LoadSubsriberEmails( $params );

  // 7. Отсылка письма каждому из них
  $_t = $tpl->theme;
  $tpl->theme = $rh->theme;
  foreach( $a as $i=>$v )
  {

    // a. Сфабриковать префикс письма
    $security_code = md5( (int)$data["record_id"].(int)$data["comment_id"].(int)$v["user_id"]. $rh->node_secret_word );
    $magic_code =  (int)$v["user_id"]."Z".(int)$data["record_id"]."Z".(int)$data["comment_id"]."Z".$security_code;
    $npj_code = "NPJCODE:".$magic_code." <br />".$tpl->message_set["DontDeleteNpjCode"]." <br />";

    $tpl->Assign("NPJCODE", $npj_code);
    $tpl->Assign("Mail.UserName", $v["user_name"]);
    $tpl->Assign("Link:Mail.Login", $this->Link( $v["login"]."@".$v["node_id"] ));

    // b. Приклеить его к письму
    $html_hello = $tpl->Parse( $tplt."Hello" );
    $text_hello = $tpl->Format( $html_hello, "html2text" );
    $_html = $html_hello.$html; $_text = $text_hello.$text;

    // c. отсылка письма
    $recipients = array("".$v["user_name"]." <".$v["email"].">");
    $from = $mail_from." <".$rh->node_mail.">";
//    $from = "".$tpl->message_set["Mail:From"]." <".$rh->node_mail.">";

    $this->prepMail($subject, $_html, $_text, $from);
    $this->mail->setHeader('Reply-To', "\"".$magic_code."\" <".$rh->node_mail.">");
    $this->mail->setHeader('Message-ID', "<".$magic_code."@".preg_replace("/:.*$/","",$_SERVER["HTTP_HOST"]).">");
    $this->sendMail($recipients);

    // #. отладочный вывод
    //$debug->Trace( "HTML:<br />".$_html );
    //$debug->Trace( "TEXT:<br /><pre>".$_text."</pre>" );
    //$debug->Trace( "to:".$v["email"] );
  }
  $tpl->theme = $_t;

  //$debug->Error( $params[0] );

  $rh->absolute_urls = 0;
  return GRANTED;  

?>