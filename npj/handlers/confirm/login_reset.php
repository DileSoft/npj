<?php

  // 1. это можно делать всем, проверка прав не нужна
  // 2. соответствие формы проверяется в ConfirmForm

    $rh = &$this->rh;
    $db = &$this->rh->db;
    $tpl = &$this->rh->tpl;
    $debug = &$this->rh->debug;

    // нужно получить запись пользователя (в основном -- ради email, email_confirm)
    $data = $rh->object->Load( 2 );
    if ($data == false) { $this->success = false; return; }

    // сгенерируем новый магикслово "куки-логин"
    $cookie_login = md5( date("Y-m-d H:i:s").$data["password"] );
    $sql = "update ".$rh->db_prefix."users set login_cookie=".$db->Quote($cookie_login).
           " where user_id=".$db->Quote($data["user_id"]);
    $db->Execute( $sql );

    // перелогинимся, чтобы запомнить его для себя
    $principal = &$rh->principal;
    $principal->LoginCookie( $principal->data["login"], $principal->data["password"], 1, $cookie_login );
    
    $this->success = true;
?>