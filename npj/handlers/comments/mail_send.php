<?php

  // непосредственная отсылка комментария почтой.
  // параметры: 
  //  * массив id-шников пользователей, которым отсылать

  // 1. получить всё что можно
  $tpl->MergeMessageSet( $rh->message_set."_mail_integration" );
  $rh->absolute_urls = 1;

  $data = &$this->Load( 3 );
  if (!is_array($data)) return $this->NotFound("CommentNotFound");

  $tpl->Assign("Href:Mail.Comment", $this->Href( $this->npj_object_address, NPJ_ABSOLUTE ) );
  $tpl->Assign("Link:Mail.Comment.User", $this->Link($data["user_login"]."@".$data["user_node_id"]));
  $tpl->Assign("Mail.Comment.UserName",  $data["user_name"] );
  $tpl->Assign("Mail.Comment.Body",    $this->Format($data["body_post"], "absurl") );
  $tpl->Assign("Mail.Comment.Subject", $this->Format($data["subject"], "absurl") );
  $tpl->Assign("Mail.Comment.DT",      $data["created_datetime"] );
  $mail_from = $tpl->Format($data["user_name"],"html2text")." - ".$data["user_login"]."@".$data["user_node_id"].
               " (".$tpl->message_set["Mail:From"].")";

  if ($data["parent_id"] > 0)
  {
    $parent = &new NpjObject( &$rh, $object->npj_account.":".$object->npj_context."/comments/".$data["parent_id"] );
    $pdata = &$parent->Load( 3 ); 
    $tpl->Assign("Href:Mail.Parent", $this->Href( $parent->npj_object_address, NPJ_ABSOLUTE ) );
    $tpl->Assign("Mail.Parent.Subject", $this->Format($pdata["subject"], "absurl") );
    $tpl->Assign("Mail.Parent.Body",    $this->Format($pdata["body_post"], "absurl") );
  }
  
  $record = &new NpjObject( &$rh, $object->npj_account.":".$object->npj_context );
  $rdata = &$record->Load( ($data["parent_id"] > 0)?2:3 );
  if ($data["parent_id"] == 0)
  {
    $tpl->Assign("Mail.Parent.Subject", $this->Format($this->Format($rdata["subject_r"], $rdata["formatting"], "post"), "absurl") );
    $tpl->Assign("Mail.Parent.Body",    $this->Format($this->Format($rdata["body_r"], $rdata["formatting"], array("default"=>"post","diff"=>1)), "absurl") );

    // рипаем?
    if (strlen( $tpl->GetValue("Mail.Parent.Body") ) > $rh->mail_comment_parent_maxsize*1024) 
    {
      $tpl->Assign("Mail.Parent.Body", $this->Format( 
              $tpl->GetValue("Mail.Parent.Body"), 
              "auto_abstract", array("default"  =>$rh->mail_comment_parent_maxsize*1024,
                                     "supertag" =>$rdata["supertag"])
                   ) );
    }

    // ключслова + сообщества
    $tpl->Assign("Mail.Record.Keywords",     ""); 
    $tpl->Assign("Mail.Record.Crossposted",  "");
    if ($rdata["keywords"] != "!")
      $tpl->Assign("Mail.Record.Keywords",     $this->Format( $rdata["keywords"], "absurl", "rip_img" ) );
    if ($rdata["crossposted"] != "!")
      $tpl->Assign("Mail.Record.Crossposted",  $this->Format( $rdata["crossposted"], "absurl", "rip_img" ) );
    $tpl->Assign("Mail.Record.Keywords+Crossposted", $tpl->GetValue("Mail.Record.Keywords") || 
                                                     $tpl->GetValue("Mail.Record.Crossposted") ); 
  
  }
  $tpl->Assign("Href:Mail.Record", $this->Href( $record->npj_object_address, NPJ_ABSOLUTE ) );
  if (!is_array($rdata)) return $this->NotFound("RecordNotFound");

  // подписка для владельца записи, её автора и автора комментария, на который отвечали
  array_unshift( $params, $rdata["user_id"] );
  array_unshift( $params, $rdata["author_id"] );
  if ($data["parent_id"] > 0)
   array_unshift( $params, $pdata["user_id"] );

  // не отправлять самому себе
  $params = array_diff( $params, array( $principal->data["user_id"] ) );

  // 3. Сфабриковать письмо в html
  $html = array();
  $text = array();
  $_t = $tpl->theme;
  $tpl->theme = $rh->theme;

    $tplt = "mail/integration.html:";
    $comment = $tpl->Parse( $tplt."Comment" );
    $comment.= $tpl->Parse( $tplt."Comment_Reply" );
    $comment.= $tpl->Parse( $tplt."Goodbye" );

    //$subject = trim($tpl->Parse( $tplt."Comment_Subject" ));
    if (!trim($data["subject"]) || !($_sb = trim($this->Format($data["subject"], "html2text", array("nolinks"=>1)))))
    {
      $subject[0] = $tpl->message_set["Mail:NoSubject_ToRecord"];
      $subject[1] = $tpl->message_set["Mail:NoSubject_ToParent"];
      $subject[2] = $tpl->message_set["Mail:NoSubject_ToAll"];
    }
    else
      $subject[2] = $subject[1] = $subject[0] = $_sb;

    
    $html[0] = $tpl->Parse($tplt."Comment_ToRecord").$comment;
    if ($data["parent_id"] > 0)
     $html[1] = $tpl->Parse($tplt."Comment_ToParent").$comment;
    $html[2] = $tpl->Parse($tplt."Comment_ToAll").$comment;
  $tpl->theme = $_t;

  // 4. Перевести его в текст
  foreach( $html as $i=>$v )
   $text[$i] = $tpl->Format( $html[$i], "html2text" );

  // 6. Всосать адреса из БД
  // NB: У внешних чуваков нет профиля
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

    // а5. Выбрать форму обращения
    $no = 2;
    if ($v["user_id"] == $rdata["user_id"]) $no=0;
    if ($v["user_id"] == $pdata["user_id"]) $no=1;
    //if ($i<$no) $no = $i;    // <----- fucking magic =))) Без него как раз правильно =)))
    $_html = $html[$no]; $_text = $text[$no]; $_subject = $subject[$no];

    // b. Приклеить его к письму
    $html_hello = $tpl->Parse( $tplt."Hello" );
    $text_hello = $tpl->Format( $html_hello, "html2text" );
    $_html = $html_hello.$_html; $_text = $text_hello.$_text;

    // c. отсылка письма
    $recipients = array("".$v["user_name"]." <".$v["email"].">");
    $from = $mail_from." <".$rh->node_mail.">";

    $this->prepMail($_subject, $_html, $_text, $from);
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