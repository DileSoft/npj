<?php

  // уходы в подфункцию 
  if ($object->params[0] == "ok")           return $object->Handler( "_registration_ok", &$params, &$principal );

  // что делать, если пытаются зарегистрироваться не с текущего узла.
  if ($this->npj_node != $rh->node_name) 
    return $this->Forbidden("RegistrationHomeNodeOnly");

  // Регистрация с аккаунтом класса CLASS по ссылке "registration@npj:user/CLASS"
  // Есть ли у нас такой класс?
  $target_class = false;
  if ($rh->account_classes && isset($rh->account_classes[$object->params[1]]))
  {
    $target_class = $rh->account_classes[$object->params[1]];
    $_type = ACCOUNT_USER;
    if ($object->params[0] == "community") $_type = ACCOUNT_COMMUNITY;
    if ($object->params[0] == "workgroup") $_type = ACCOUNT_WORKGROUP;
    if (is_array($target_class["type"]))
     if (!in_array($_type, $target_class["type"])) $target_class=false;
     else;
    else if ($_type != $target_class["type"]) $target_class=false;
  }
  if ($target_class)
  {
    $this->_target_class = $target_class;
    $this->_target_class["supertag"] = $object->params[1];
  }

  $debug->Trace_R( $this->_target_class );

  // что делать, если регистрация запрещена
  if ($object->params[0] == "community") $rmode = $rh->community_creation_mode;
  else
  if ($object->params[0] == "workgroup") $rmode = $rh->workgroup_creation_mode;
  else $rmode = $rh->registration_mode;

  // если у нас регистрация с классом происходит, то может быть, у нас необычные настройки
  // доступа для этого класса?
  if ($this->_target_class)
    if (isset($rh->account_classes_registration_mode[ $this->_target_class["supertag"] ]))
     $rmode = $rh->account_classes_registration_mode[ $this->_target_class["supertag"] ];
  
  $debug->Trace_R( $rmode );

  // администраторам узла можно всегда и всё 
  if ($principal->IsGrantedTo("acl_text", NULL, NULL, $rh->node_admins)) $rmode = 2;

  // если у нас навороченный режим с массивом?
  if (is_array($rmode))
  {
    $rmodes = $rmode;
    $rmode=0;
    $debug->Trace_R( $rmodes );
    foreach( $rmodes as $access_acl=> $value )
    {
     $debug->Trace( $access_acl ."=>". $value );
     if ($principal->IsGrantedTo( "acl_text", "node", $object->data["id"], $access_acl ))
      if ($rmode < $value) 
      {
        $rmode = $value;
      }
    }
//    $debug->Error( $rmode );
  }

  // нельзя так нельзя
  if ($rmode === 0)
    return $this->Forbidden("RegistrationAdminOnly");

  // сообщества и РГ нельзя создавать "гостям"
  if (($params[0] == "community") || ($params[0] == "workgroup"))
   if (!$principal->IsGrantedTo("noguests"))
     return $this->Forbidden("RegistrationNoGuestsOnly");


   $rh->__registration_mode = $rmode;
   // обработчик формы
   include( $dir."/!form_registration.php" );
   if (!isset($_POST["__form_present"])) 
   { 
     $form->ResetSession();
   }

   $debug->Milestone( "Starting form handler" );

 $tpl->theme = $rh->theme;
 $result= $form->Handle();
 $tpl->theme = $rh->skin;
 if ($result !== false) $tpl->Assign("Preparsed:CONTENT", $result);
 if ($object->params[0] == "community")
   $tpl->Assign("Preparsed:TITLE", "Создание нового сообщества");  // !!! to messageset
 else
 if ($object->params[0] == "workgroup")
   $tpl->Assign("Preparsed:TITLE", "Создание новой рабочей группы");
 else
   $tpl->Assign("Preparsed:TITLE", "Регистрация нового пользователя");

 if ($this->_target_class)
  $tpl->Append("Preparsed:TITLE", " (".$this->_target_class["name"].")");

  if ($form->success)
  {
    // если сразу готово, то вставляем целый кластер документов
    $account = &new NpjObject( &$rh, $form->hash["login"]->data."@". $rh->node_name );
    $_a = &$account->Load(1);
    if ($rmode == 2)
    {
      $node_principal = &new NpjPrincipal( &$rh );
      $principal_user_id = $rh->principal->data["user_id"];
      $principal_record_id = $rh->principal->data["root_record_id"];
      $rh->principal->MaskById( $_a["user_id"] );
      $account->Handler( "populate", array("_p_user_id"  =>$principal_user_id,
                                           "_p_record_id"=>$principal_record_id), 
                          &$node_principal );
      $rh->principal->UnMask();
    }
    else
    // иначе -- нужно выслать уведомление о том, что появился такой аккаунт
    {
      $this->handler( "notify", array( 0 => "registration", "npj_account" => $account->npj_account ) );
    }

    // если был указан e-mail, отправляем письмо с его нотификацией
    if ($form->hash["email"]->data != "")
    {
      $account = &new NpjObject( &$rh, $form->hash["login"]->data."@". $rh->node_name );
//      $user = &new NpjObject( &$rh, "kuso@npj" );
      $account->Handler( "_manage_confirm", array("email"=>$form->hash["email"]->data, "confirm"=>"",
                                               "by_script" => 1,
                                               "messages"=>$rh->message_set."_confirmation"), &$principal );
    }

    // редирект на registration/ok/%login%
    if (($params[0] != "community") && ($params[0] != "workgroup"))
    if (($rh->registration_mode == 2) && !$principal->IsGrantedTo("noguests"))
    {
      $result = $principal->Login(0, $form->hash["login"]->data, $form->hash["password"]->data);
      if ($result) 
      {
        $state->Free();
        $state->Set("cookietest", 1);
        $state->Set(session_name(), session_id());
        $rh->Redirect( $this->Href( "login@".$rh->node_name.":".$login."/welcome", NPJ_ABSOLUTE ), STATE_USE );
      }
    }
    $rh->Redirect( $object->Href( "registration::ok/".$form->hash["login"]->data ) );
  }

?>
