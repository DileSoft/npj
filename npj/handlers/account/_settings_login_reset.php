<?php

  // это типа мы решили все перманентные логины сбросить

  $data = $this->Load(2);
  if (!is_array($data)) return $this->NotFound("AccountNotFound");

  $rh->UseClass("ConfirmForm", $rh->core_dir);
  $confirm = &new ConfirmForm( &$rh, "login_reset", $rh->message_set."_confirm_login_reset" );
  
  $result = $confirm->Handle();
  if ($result === false) $result = $confirm->ParseConfirm();
  
  // ??? Prepased:Title
  $tpl->Assign("Preparsed:CONTENT", $result);

  return GRANTED;

?>