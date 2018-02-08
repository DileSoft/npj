<?php

  // это типа мы решили пароль сбросить

  $data = $this->Load(2);
  if (!is_array($data)) return $this->NotFound("AccountNotFound");
  if ($data["account_type"] != ACCOUNT_USER)
    return $this->Forbidden( "CommunityNotSupport" );

  $rh->UseClass("ConfirmForm", $rh->core_dir);
  $confirm = &new ConfirmForm( &$rh, "password_reset", $rh->message_set."_confirm_password_reset" );
  
  $result = $confirm->Handle();
  if ($result === false) $result = $confirm->ParseConfirm();
  
  // ??? Prepased:Title
  $tpl->Assign("Preparsed:CONTENT", $result);

  return GRANTED;

?>