<?php

// служебный хандлер. нужен дл€ того, чтобы ”¬≈ƒќћЋя“№ =)

//!!!! обдумать, как сделать, чтобы вызывать его мог только или NNS или другой скрипт Ќѕ∆.

  $tpl->Assign("Preparsed:TITLE", "Notiyard");

  // "registration" ---- ”ведомление о том, что по€вилс€ новый немодерированный аккаунт ======================
  if ($params[0] == "registration")
  {
    $account = &new NpjObject( &$rh, $params["npj_account"] );
    $data    = &$account->Load(3);
    if (!is_array($data)) return GRANTED;

    // получить емайлы всех супермодераторов узла.
    // пока получаем из конфига, а в дальнейшем хотелось бы какой-то более вмен€емой схемы.
    // [!!!..] refactoring node admins
    $admins_draft = explode(" ", $this->rh->node_admins);
    $admins = array();
    foreach($admins_draft as $k=>$v)
    {
      if (strpos($v, "@") === false) $v.="@".$this->rh->node_name;
      $person = &new NpjObject( &$this->rh, $v );
      if ($person->Load(3) != NOT_EXIST)
        if ($person->data["email"] != "")
          if ($person->data["email_confirm "] == "")
            $admins[] = $person->data["user_name"]." <".$person->data["email"].">";
    }

    $tpl->MergeMessageSet($rh->message_set."_mail_registration");

    $a = array( "login", "node_id", "user_name", "bio" );
    foreach( $a as $k=>$v )
      $tpl->Assign( $v, $account->data[$v] );
    $tpl->Assign( "is_user",      $account->data["account_type"] == ACCOUNT_USER );
    $tpl->Assign( "is_community", $account->data["account_type"] == ACCOUNT_COMMUNITY );
    $tpl->Assign( "is_workgroup", $account->data["account_type"] == ACCOUNT_WORKGROUP );
    if ($rh->account_classes)
      $tpl->Assign( "account_class:name", $rh->account_classes[ $account->data["account_class"] ]["name"] );
    else
      $tpl->Assign( "account_class:name", "" );

    $rh->absolute_urls = true;
    $tpl->Assign( "Href:profile", $account->Href( $account->npj_account.":profile" ));
    $tpl->Assign( "Href:alive",   $account->Href( $account->npj_account.":manage/alive" ));

    $tpl->Skin( $rh->theme );
      $subject = $tpl->Parse("mail/registration.html:Subject");
      $html    = $tpl->Parse("mail/registration.html:Body");
      $text = $tpl->Format($html,"html2text");
    $tpl->UnSkin();

    $recipients = $admins;
    $from = "".$tpl->message_set["Mail:From"]." <".$rh->node_mail.">";
    $this->prepMail($subject, $html, $text, $from);
    $this->sendMail($recipients);
    return GRANTED;

  }
  // "nns_net". ---- ”ведомление о том, что узел не €вл€етс€ частью Ќѕ∆-сети =================================
  if ($params[0] == "nnsnet")
  {
    if ($rh->alert_npjnet && $rh->node->data["created_datetime"]=="0000-00-00 00:00:00")
    {
      $tpl->MergeMessageSet($rh->message_set."_nns");

      $_html = str_replace("%1", "<a href=".$rh->base_full.">".$rh->base_full."</a>",
               str_replace("%2", $tpl->message_set["AboutNNS"],
               str_replace("%3", "<a href=".$rh->base_full."manage>".$rh->base_full."manage</a>",
                $tpl->message_set["Mail2Admin"])));

      $_text = $tpl->Format($_html,"html2text");

      $user = &new NpjObject( &$rh, "node@npj" );
      $udata = &$user->_LoadById(3, 3);
//      $debug->Error($debug->Trace_R($udata));

      $recipients = array("admin <".$udata["email"].">");
      $from = "".$tpl->message_set["Mail:From"]." <".$rh->node_mail.">";
      $subject = "From NPJ node";

      $this->prepMail($subject, $_html, $_text, $from);
      $this->sendMail($recipients);
    }
  
    $tpl->Append( "Preparsed:CONTENT", "<p>јдмины заспамлены.</p>" );
    return true;
  }

  $tpl->Append( "Preparsed:CONTENT", "<p>«р€ вы сюда, нет тут ничто</p>".$params[0] );

?>