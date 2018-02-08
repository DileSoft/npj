<?php

    // вызывается из Form::Load()
    // предобработчик on_before_action

    $rh = &$this->rh;
    $db = &$rh->db;
    $form = &$this;

    $rmode = $rh->__registration_mode;

    if (isset($form->hash["account_class"]))
     $account_class = $form->hash["account_class"]->data;
    else
     $account_class = "";

    $new_account_login = $form->hash["login"]->data;
    if ($rh->account_classes[$account_class]["parent_class"])
    if ($rh->account_classes[$account_class]["parent_login_concat"])
    { 
      $parent_id   = $form->hash["parent_id"]->data;
      $parent_data = $this->rh->account->_LoadById( $parent_id );
      if ($parent_data != NOT_EXIST)
      if ($parent_data["account_class"] == $rh->account_classes[$account_class]["parent_class"])
      {
        $new_account_login = $parent_data["login"]."-".$new_account_login;
        $form->hash["login"]->data = $new_account_login;
      }
    }

    // вставляем новую запись  в таблицу npj_users
    $sql = "insert into ".$rh->db_prefix."users set ".
           "account_class = ".$db->Quote( $account_class ).", ".
           "user_name = ".$db->Quote( $form->hash["user_name"]->data ).", ".
           "login = ".$db->Quote( $new_account_login ).", ".
           "password = ".$db->Quote( md5($form->hash["password"]->data) ).", ".
           "node_id = ".$db->Quote( $rh->node_name ).", ".
           "skin_override = ".$db->Quote( $rh->principal->data["skin_override"] ).", ".
           "more = ".$db->Quote( $rh->principal->data["more"] ).", ".
           "alive= ".$db->Quote( ($rmode>1?1:0)) ;
    if ($rh->object->params[0] == "community") 
      $sql.=", account_type=1, owner_user_id=".$db->Quote($rh->principal->data["user_id"]);
    if ($rh->object->params[0] == "workgroup") 
      $sql.=", account_type=2, owner_user_id=".$db->Quote($rh->principal->data["user_id"]);

    if ($rh->account_classes[$account_class]["domain_type"])
      $sql.=", domain_type=".$db->Quote($rh->account_classes[$account_class]["domain_type"]);

//    $rh->debug->Error( $sql );

    $db->Execute( $sql );

    // обновляем скрытое поле
    $this->hash["user_id"]->db_data  = $db->Insert_ID();
    $this->hash["user_id"]->data     = $db->Insert_ID();
    $this->hash["advanced"]->data    = $rh->principal->data["advanced"];


?>