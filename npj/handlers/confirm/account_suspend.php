<?php

  // 1. �������� ���� �������� ���-�� � ��������
  // 2. ������������ ����� ����������� � ConfirmForm

    $rh = &$this->rh;
    $db = &$this->rh->db;
    $tpl = &$this->rh->tpl;
    $debug = &$this->rh->debug;

    // ����� �������� ������ (� �������� -- ���� email, email_confirm)
    $data = $rh->object->Load( 3 );
    if ($data == false) { $this->success = false; return; }

    // ���������� banned state
    $sql = "update ".$rh->db_prefix."users set alive=3 ".
           " where user_id=".$db->Quote($data["user_id"]);
    $db->Execute( $sql );
    
    // �������� ������-���������� � ����
    if ($data["email_confirm"] === "")
    {
      $_t  = $tpl->theme;
      $tpl->theme = $rh->theme;

      // ��������� ��� �������� � ������
      $rh->absolute_urls = 1;
      $tpl->Assign("Mail.UserName", $data["user_name"]);
      $tpl->Assign("Link:Mail.Login", $rh->object->Link( $data["login"]."@".$data["node_id"] ));
      $_html = $tpl->Parse( "mail/notification.html:Body" );
      $_text = $tpl->Format( $_html, "html2text" );
      $rh->absolute_urls = 0;

      // ��������� ������
      $recipients = array("".$data["user_name"]." <".$data["email"].">");
      $from = "".$tpl->message_set["Mail:From"]." <".$rh->node_mail.">";
      $subject = $tpl->Parse( "mail/notification.html:Subject" );

      $rh->object->prepMail($subject, $_html, $_text, $from);
      $rh->object->sendMail($recipients);

      $tpl->theme = $_theme; 
      //$debug->Trace(" sent mail <br />".$_html );
      
      $tpl->theme = $_t;
    }

    $this->success = true;
?>