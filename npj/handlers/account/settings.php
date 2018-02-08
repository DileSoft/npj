<?php

  $obj = &new NpjObject( &$rh, $object->name.":" );
  $this->record = &$obj;

  if ($params[0] == "password") 
    if ($params[1] == "reset") 
      return $object->Handler( "_settings_password_reset", &$params, &$principal );
    else
    if ($params[1] == "new") 
      return $object->Handler( "_settings_password_new", &$params, &$principal );
    else
      return $object->Handler( "_settings_password", &$params, &$principal );

 // получаем данные
 $data = $this->Load(2);
 if (!is_array($data)) return $this->NotFound("AccountNotFound");
 if (!$this->HasAccess( &$principal, "owner" )) return $this->Forbidden("SettingsEdit");


  if ($params[0] == "login") 
    if ($params[1] == "reset") 
      return $object->Handler( "_settings_login_reset", &$params, &$principal );
    else ; // дописать вызов хандлера "логин" узла

 // обработчик формы
 include( $dir."/!form_settings.php" );
 if (!isset($_POST["__form_present"])) 
 { 
   $form->ResetSession();
   $form->DoSelect( $data["user_id"] );
   $form->hash["_formatting"]->config["default"] = $data["_formatting"];
 }

 $debug->Milestone( "Starting form handler" );

 $state->Set( "id", $data["user_id"] );
 $tpl->theme = $rh->theme;
 $result= $form->Handle();
 $tpl->theme = $rh->skin;
 if ($result !== false) $tpl->Assign("Preparsed:CONTENT", $result);
 $tpl->Assign("Preparsed:TITLE", $tpl->message_set["Form._Name"]);
 $state->Free( "id" );

  if ($form->success)
  {
    // [*****] вытащить и сохранить нетривиальные юзерские настройки
    foreach( $form->hash["more_options"]->data as $k=>$v )
      $this->data["options"][$form->hash["more_options"]->config["fields"][$k]] = $v;
    $this->data["options"]["classification"] = $form->hash["classification"]->data;
    $this->data["options"]["keywords_auto"] = $form->hash["keywords_auto"]->data;
    $this->data["options"]["record_stats"] = $form->hash["record_stats"]->data;
    $this->data["options"]["comments"] = $form->hash["comments"]->data;
    $this->data["options"]["comments_always"] = $form->hash["more_comments"]->data[0];
    $this->data["options"]["post_supertag_ovr"] = $form->hash["post_supertag_override"]->data;
    $this->data["options"]["post_date_ovr"] = $form->hash["post_date_override"]->data;

    $principal->data["more"] = $this->data["more"] = $principal->ComposeOptions($this->data["options"]);
    // сохранить то, что сохраняется не в основную таблицу
    $sql = "update ".$rh->db_prefix."users set _formatting=". $db->Quote($form->hash["_formatting"]->data) .
           ", more=". $db->Quote($this->data["more"]) .
           ", skin_override=". $db->Quote($form->hash["skin_override"]->data) .
           ", group_versions_override=". $db->Quote($form->hash["group_versions_override"]->data) .
           " where user_id=".$db->Quote($data["user_id"]);
    $db->Execute( $sql );
    // перезагружаем данные принципала, вдруг они поменялись, хаха.
    $principal->Unmask( 1 ); // надо снять маски, потому что потом мы воплотимся в последнюю маску, а не в рожу, если маски не снять
    $cache->Clear( "user", $principal->data["user_id"] );
    $principal->AssignById(  $principal->data["user_id"] );
    $principal->Store(); 
    $principal->LoadMenu(); 
    $principal->LoadOptions();
    // редирект на profile/edit/ok
    $rh->Redirect( $rh->Href($object->_NpjAddressToUrl( "settings/edited", 1 ),1) );
  }

?>