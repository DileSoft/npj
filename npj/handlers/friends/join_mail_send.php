<?php

  // непосредственная отсылка уведомлений о заджойнивании.
  // параметры: 
  //  * "user_id" -- кто джойнится
  //  * "to" массив id-шников пользователей, которым отсылать

  // 1. получить всё что можно
  $rh->absolute_urls = 1;

   // a. account
   $account = &new NpjObject( &$rh, $this->npj_account );
   $data = &$account->Load( 3 );
   $tpl->Assign("Href:Community", $account->Href( $account->npj_object_address, NPJ_ABSOLUTE ) );
   $tpl->Assign("Link:Community", $account->Link( $account->npj_object_address ));
   $tpl->Assign("Mail.CommunityName", $data["user_name"]);
   $type = ($data["security_type"]%10 < COMMUNITY_LIMITED)?"Open":"Limited";

   // b. new member
   $mdata = &$account->_LoadById( $params["user_id"], 1 );
   $tpl->Assign("Mail.MemberName", $mdata["user_name"]);
   $tpl->Assign("Link:Mail.MemberLogin", $account->Link( $mdata["login"]."@".$mdata["node_id"] ));
   $tpl->Assign("Link:MemberLogin", $account->Link( $mdata["login"]."@".$mdata["node_id"] ));
   $tpl->Assign("Npj:Member", $mdata["login"]."@".$mdata["node_id"]);

  // 2. Сфабриковать письмо в html
  $tpl->MergeMessageSet( $rh->message_set."_mail_membership" );
  $_t = $tpl->theme;
  $tpl->theme = $rh->theme;

    $tplt = "mail/membership.html:";
    $body = $tpl->Parse( $tplt."Join".$type );
    $body.= $tpl->Parse( $tplt."Join".$type."_Reply" );
    $body.= $tpl->Parse( $tplt."Goodbye" );
    $html = $body;
    $subject = trim($tpl->Parse( $tplt."Join".$type."_Subject" ));
    
  $tpl->theme = $_t;

  // 4. Перевести его в текст
  $text = $tpl->Format( $html, "html2text" );

  // 6. Всосать адреса из БД
  $user_ids = $params["to"];
  if (sizeof($user_ids) == 0) return GRANTED;
  foreach( $user_ids as $i=>$v ) $user_ids[$i] = 1*$user_ids[$i];
  $sql = "select u.user_name, u.node_id, u.login, p.user_id, p.email from ".
         $rh->db_prefix."profiles as p, ".$rh->db_prefix."users as u  where email_confirm=".$db->Quote("").
         " and u.user_id=p.user_id and u.user_id in (".implode(",", $user_ids).")";
  $sql.= " and u.alive=1"; // << max@jetstyle bug#100 2004-11-16 />>
  $rs = $db->Execute( $sql );
  $a = $rs->GetArray();

  // 7. Отсылка письма каждому из них
  $_t = $tpl->theme;
  $tpl->theme = $rh->theme;
  foreach( $a as $i=>$v )
  {

    // a. Сфабриковать префикс письма
    $tpl->Assign("Mail.UserName", $v["user_name"]);
    $tpl->Assign("Link:Mail.Login", $this->Link( $v["login"]."@".$v["node_id"] ));

    // а5. Выбрать форму обращения
    $_html = $html; $_text = $text;

    // b. Приклеить его к письму
    $html_hello = $tpl->Parse( $tplt."Hello" );
    $text_hello = $tpl->Format( $html_hello, "html2text" );
    $_html = $html_hello.$_html; $_text = $text_hello.$_text;

    // c. отсылка письма
    $recipients = array("".$v["user_name"]." <".$v["email"].">");
    $from = "".$tpl->message_set["Mail:From"]." <".$rh->node_mail.">";

    $this->prepMail($subject, $_html, $_text, $from);
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