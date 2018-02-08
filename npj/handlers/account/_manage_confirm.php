<?php

  /*  
      Required templates:
        * mail/confirmation.html {Subject, Body}
        * account.email.html {Ok, Ok_Confirmed, Ok_Notified, ConfirmForm}
      Required messages:
        * Mail:From
  */

  $data = $this->Load(3);
  $debug->Trace(" confirming ".$params["email"] );

  // confirmation of an email.
  $tpl->theme = $rh->theme;
   
  //if ($params["messages"]) 
  $tpl->MergeMessageSet( $rh->message_set."_confirmation" );

  if (isset($_REQUEST["ok"]))
  {
    if ($_REQUEST["ok"] == "Confirmed")  $tpl->Parse("account.email.html:Ok_Confirmed","Preparsed:CONTENT"); else
    if ($_REQUEST["ok"] == "Notified")   $tpl->Parse("account.email.html:Ok_Notified","Preparsed:CONTENT"); else
                                         $tpl->Parse("account.email.html:Ok","Preparsed:CONTENT");
    $tpl->Assign("Preparsed:TITLE", $tpl->message_set["Title"]);
    $done = 1;
  }
  else
  // проверить валидность конфирма
  if (isset($_POST["confirm"]))
  {
    $sql="update ".$rh->db_prefix."profiles set email_confirm=".$db->Quote("").
         " where email_confirm=".$db->Quote($_POST["confirm"])." and email=".$db->Quote($_REQUEST["email"]);
    $db->Execute( $sql );
    $rh->Redirect( $rh->Href($rh->url.$state->Plus("ok", "Confirmed"), NPJ_ABSOLUTE) );
    $done = 1;
  }
  else
  if (!$params["by_script"]) 
 // if ($params["confirm"])
  {
    if ($_REQUEST["confirm"]) $params["confirm"] = $_REQUEST["confirm"];
    if ($_REQUEST["email"]) $params["email"] = $_REQUEST["email"];
    // показать форму 
    $tpl->LoadDomain( array(
      "Form"   => $state->FormStart(MSS_POST, $this->_NpjAddressToUrl($this->npj_object_address.":manage/confirm") ),
      "/Form"  => $state->FormEnd(),
      "_Email" => $params["email"],
      "_Confirm" => $params["confirm"],
                    ) );
    $_theme=$tpl->theme; 
    $tpl->theme = $rh->theme;
    $tpl->Parse("account.email.html:ConfirmForm","Preparsed:CONTENT");
    $tpl->Assign("Preparsed:TITLE", $tpl->message_set["Title"]);
    $tpl->theme = $_theme; 
    $done=1;
  }

  // генерация нового мыльного конфирма и отсылка
  if (!$done)
  {
    $confirm = md5($params["email"].date("D d M Y H:i:s").rand());
    $params["confirm"] = $confirm;

    // занести адрес в базу
    $query = "update ".$rh->db_prefix."profiles SET ".
             "email_confirm = ".$db->Quote($confirm)." where ".
             "email = ".$db->Quote($params["email"])." and user_id=".$db->Quote($this->data["user_id"]);
    $db->Execute($query);

    $_theme=$tpl->theme; 
    $rh->absolute_urls = 1;
    $tpl->theme = $rh->theme;
    // придумать что написано в письме
    $tpl->LoadDomain( array(
        "_Email"   => $params["email"],
        "_Confirm" => $params["confirm"],
        "Href:Confirm" => 
          $this->Href($this->name.":manage/confirm/".preg_replace("/^([^@]+)@/","$1.",$params["email"])."/".$params["confirm"],0),
        "Link:BlindConfirm" => 
          "<a href=\"".$this->Href($this->name.":manage/confirm")."\">".
          $this->Href($this->name.":manage/confirm").
          "</a>",
                      ) );
    $tpl->Assign("Mail.UserName", $data["user_name"]);
    $tpl->Assign("Link:Mail.Login", $this->Link( $data["login"]."@".$data["node_id"] ));
    $_html = $tpl->Parse( "mail/confirmation.html:Body" );
    $_text = $tpl->Format( $_html, "html2text" );

    // отправить письмо
    $recipients = array("".$data["user_name"]." <".$params["email"].">");//array($params["email"]);
    $from = "".$tpl->message_set["Mail:From"]." <".$rh->node_mail.">";
    $subject = $tpl->Parse( "mail/confirmation.html:Subject" );

    $this->prepMail($subject, $_html, $_text, $from);
    $this->sendMail($recipients);

    $tpl->theme = $_theme; 
    $debug->Trace(" sent mail <br />".$_html );
    $rh->absolute_urls = 0;
  }                                                       
  else ;

 $tpl->theme = $rh->skin;



?>