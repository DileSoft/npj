<?php

  // заморозка аккаунта к чертям

  $data = $this->Load(2);
  if (!is_array($data)) return $this->NotFound("AccountNotFound");
  if (!$principal->IsGrantedTo("acl_text", NULL, NULL, $rh->node_admins))
    if (!$this->HasAccess(&$principal, "owner"))
      return $this->Forbidden("YouDontOwnThisAccount"); 

  $rh->UseClass("ConfirmForm", $rh->core_dir);
  $confirm = &new ConfirmForm( &$rh, "account_freeze", $rh->message_set."_confirm_account_freeze" );
  
  $result = $confirm->Handle();
  if ($result === false) $result = $confirm->ParseConfirm();
  
  $tpl->Assign("Preparsed:CONTENT", $result);

  return GRANTED;

?>