<?php

 // получаем данные
 $data = $this->Load(2);
 if (!is_array($data)) return $this->NotFound("AccountNotFound");
 if (!$this->HasAccess( &$principal, "owner" )) return $this->Forbidden("ChangePassword");

 // обработчик формы
 include( $dir."/!form_settings_password.php" );
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
    $rh->Redirect( $rh->Href($object->_NpjAddressToUrl( "settings/edited", 1 ),1) ); // !!! поменять
  }

?>