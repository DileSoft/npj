<?php

 // ��� ���� �� ���������� ������� � ����� ���������. ���� ����� ������ ������ �����!

 // �������� ������
 $data = $this->Load(2);
 if (!is_array($data)) return $this->NotFound("AccountNotFound");

 // ���������� �����
 include( $dir."/!form_manage_unfreeze.php" );
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
    $principal->Login( 0, $data["login"], $form->hash["old_password"]->data );
    // �������� �� profile/edit/ok
    $rh->Redirect( $rh->Href($object->_NpjAddressToUrl( "settings/edited", 1 ),1) ); // !!! ��������
  }

?>