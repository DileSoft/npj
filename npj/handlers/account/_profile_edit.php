<?php

 // получаем данные
 $data = $this->Load(3);
 if (!is_array($data)) return $this->NotFound("AccountNotFound");
 if (!$this->HasAccess( &$principal, "owner" )) return $this->Forbidden("ProfileEdit");
 $email = $data["email"];

 // обработчик формы
 include( $dir."/!form_profile.php" );
 if (!isset($_POST["__form_present"])) 
 { 
   $form->ResetSession();
   $form->DoSelect( $data["user_id"] );
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
//    if ($form->hash["email"]->previous_data != $form->hash["email"]->data)
    if ($email != $form->hash["email"]->data)
    if ($form->hash["email"]->data != "")
    {
      $this->Handler( "_manage_confirm", array("email"=>$form->hash["email"]->data, "confirm"=>"",
                                               "by_script" => 1,
                                               "messages"=>$rh->message_set."_confirmation"), &$principal );
    }

    // вытащить и сохранить нетривиальные настройки журнала
    foreach( $form->hash["advanced_options"]->data as $k=>$v )
      $this->data["advanced_options"][$form->hash["advanced_options"]->config["fields"][$k]] = $v;
    foreach( $form->hash["advanced_post_options"]->data as $k=>$v )
      $this->data["advanced_options"][$form->hash["advanced_post_options"]->config["fields"][$k]] = $v;
    $this->data["advanced"] = $principal->ComposeOptions($this->data["advanced_options"]);
    $sql = "update ".$rh->db_prefix."profiles set advanced=". $db->Quote($this->data["advanced"]) .
           " where user_id=".$db->Quote($data["user_id"]);
    $db->Execute( $sql );

    // сохранить то, что сохраняется не в основную таблицу
    $sql = "update ".$rh->db_prefix."users set user_name=". $db->Quote($form->hash["user_name"]->data) .
           " where user_id=".$db->Quote($data["user_id"]);
    $db->Execute( $sql );
    // редирект на profile/edit/ok
    $rh->Redirect( $rh->Href($object->_NpjAddressToUrl( "profile/edited", 1 ),1) );
  }

?>