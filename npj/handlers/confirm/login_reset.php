<?php

  // 1. ��� ����� ������ ����, �������� ���� �� �����
  // 2. ������������ ����� ����������� � ConfirmForm

    $rh = &$this->rh;
    $db = &$this->rh->db;
    $tpl = &$this->rh->tpl;
    $debug = &$this->rh->debug;

    // ����� �������� ������ ������������ (� �������� -- ���� email, email_confirm)
    $data = $rh->object->Load( 2 );
    if ($data == false) { $this->success = false; return; }

    // ����������� ����� ���������� "����-�����"
    $cookie_login = md5( date("Y-m-d H:i:s").$data["password"] );
    $sql = "update ".$rh->db_prefix."users set login_cookie=".$db->Quote($cookie_login).
           " where user_id=".$db->Quote($data["user_id"]);
    $db->Execute( $sql );

    // �������������, ����� ��������� ��� ��� ����
    $principal = &$rh->principal;
    $principal->LoginCookie( $principal->data["login"], $principal->data["password"], 1, $cookie_login );
    
    $this->success = true;
?>