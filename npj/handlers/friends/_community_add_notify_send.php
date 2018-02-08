<?php

// специальный внутренний обработчик.
// высылает письмо-оповещение о том, что данный чувак добавился в модераторы/члены, ещё куда.

// параметры
//  !0  - group_rank, куда добавились
//  !1  - login@npj

      // получить профиль
      $user = &new NpjObject( &$rh, $params[1] );
      $data = $user->Load(3);
      $type = ($params[0]<0)?"Remove":"State";
      // если неконфирмено мыло, выйти
      if ($data["email_confirm"]) return DENIED;

  $rh->absolute_urls = 1;

      // получить сообщество
      $account = &new NpjObject( &$rh, $this->npj_account );
      $adata = &$account->Load( 3 );
      $tpl->Assign("Href:Community", $account->Href( $account->npj_object_address, NPJ_ABSOLUTE ) );
      $tpl->Assign("Link:Community", $account->Link( $account->npj_object_address ));
      $tpl->Assign("Mail.CommunityName", $adata["user_name"]);

      $debug->Trace( $params[1]." (".$params[0].") mailed" );

      $group_nick = $rh->group_ranks[ $adata["account_type"] ][$params[0]];
      $tpl->LoadDomain( array(
          "Login@Node"   => $data["login"]."@".$data["node_id"],
          "Link:Mail.MemberLogin" => $account->Link( $data["login"]."@".$data["node_id"] ),
          "UserName"     => $data["user_name"],
          "State"        => $tpl->message_set["FriendsNames"][ $group_nick ] ,
                        ) );

      $tpl->MergeMessageSet( $rh->message_set."_mail_membership" );
      $_t = $tpl->theme;
      $tpl->theme = $rh->theme;

        $tplt = "mail/membership.html:";
        $body = $tpl->Parse( $tplt.$type );
        $body.= $tpl->Parse( $tplt."Goodbye" );
        $html = $body;
        $subject = trim($tpl->Parse( $tplt.$type."_Subject" ));
        
      $tpl->theme = $_t;

      // 4. Перевести его в текст
      $text = $tpl->Format( $html, "html2text" );


      // 7. Отсылка письма 
      $_t = $tpl->theme;
      $tpl->theme = $rh->theme;

      $v = &$data;

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

      $tpl->theme = $_t;
      
//  $debug->Error("dsd");
  $rh->absolute_urls = 0;
      
      return GRANTED; 

?>