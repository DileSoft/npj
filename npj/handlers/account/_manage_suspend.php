<?php

  // бан пользователей от узла

  $data = $this->Load(2);
  if (!is_array($data)) return $this->NotFound("AccountNotFound");
  if (!$principal->IsGrantedTo("acl_text", NULL, NULL, $rh->node_admins))
     return $this->Forbidden("NotAnAdmin"); 

  $rh->UseClass("ConfirmForm", $rh->core_dir);
  $confirm = &new ConfirmForm( &$rh, "account_suspend", $rh->message_set."_confirm_account_suspend" );
  
  $result = $confirm->Handle();
  if ($result === false) $result = $confirm->ParseConfirm();
  
  $tpl->Assign("Preparsed:CONTENT", $result);

  return GRANTED;

?>