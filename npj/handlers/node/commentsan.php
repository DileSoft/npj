<?php
// 

//BAN this spam robot by IP

  $rs = $db->Execute( "insert into ".$rh->db_prefix."ban_ip ".
                      " (ip, iplong, banned_datetime) VALUES (".
                      $db->Quote($_SERVER["REMOTE_ADDR"]).", ".
                      $db->Quote(ip2long($_SERVER["REMOTE_ADDR"])).", ".
                      $db->Quote(date("Y-m-d H:i:s")).")" );

  $rh->UseLib( "HtmlMimeMail2" );
  $mail = &new HtmlMimeMail2();

  $body = "Admin!<br><br> IP ban was made at <a href=".$rh->base_full.">".$rh->base_full."</a> against ".
          $_SERVER["REMOTE_ADDR"]."<br><br>You can check this IP at <a href=http://www.all-nettools.com/toolbox>http://www.all-nettools.com/toolbox</a>".
          "<br><br>Yours, NPJ.";

  $mail->setSubject( "IP ban at ".$rh->base_full );
  $mail->setHtml   ( $body, $rh->tpl->Format( $body, "html2text") );

  $mail->setHeader("X-Mailer", "NPJ");

  $mail->setFrom($rh->tpl->message_set["Mail:From"]." <".$rh->node_mail.">");

  $mail->buildMessage($rh->tpl->message_set["Encodings"], $rh->method_mailsend);
  if (!$rh->no_email) 
  {
    // получить емайлы всех супермодераторов узла.
    // пока получаем из конфига, а в дальнейшем хотелось бы какой-то более вменяемой схемы.
    // [!!!..] refactoring node admins
    $admins_draft = explode(" ", $rh->node_admins);
    $admins = array();
    foreach($admins_draft as $k=>$v)
    {
      if (strpos($v, "@") === false) $v.="@".$rh->node_name;
      $person = &new NpjObject( &$rh, $v );
      if ($person->Load(3) != NOT_EXIST)
        if ($person->data["email"] != "")
          if ($person->data["email_confirm "] == "")
            $admins[] = $person->data["user_name"]." <".$person->data["email"].">";
    }

    $result = $mail->send($admins, $rh->method_mailsend);
  }

  die("Cannot connect to mysql database.");
//die("hey, you now banned");

?>