<?php

 // это типа если мы пароль сбросили и заходим его устанавливать

 // получаем данные
 $data = $this->Load(3);
 if (!is_array($data)) return $this->NotFound("AccountNotFound");
 if (($data["temporary_password"] === "") ||
     (strtotime($data["temporary_password_created"])+ $rh->temporary_password_duration_days*60*60*24
     > time())) 
     return $this->Forbidden("TempPasswordWasnSet");

 // обработчик формы
 include( $dir."/!form_settings_password_new.php" );
 if (!isset($_POST["__form_present"])) 
   $form->ResetSession();

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
    // редирект на profile/edit/ok
    $result = $principal->Login(0, $rh->account->data["login"], $form->hash["password"]->data);
    if ($result) 
    {
      $state->Free();
      $state->Set("cookietest", 1);
      $state->Set(session_name(), session_id());
      $db->Execute( "update ".$rh->db_prefix."profiles set temporary_password=".$db->Quote("none").
                    "where user_id = ".$db->Quote( $data["user_id"] ) );
    $rh->Redirect( $rh->Href($object->_NpjAddressToUrl( "settings/edited", 1 ),1) ); // !!! поменять
  }
  }

?>