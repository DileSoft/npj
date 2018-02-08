<?php

  // оживление от бана или от фриза (если ещё логгед ин)

  $data = $this->Load(2);
  if (!is_array($data)) return $this->NotFound("AccountNotFound");
  if ($data["alive"] == 2) // незабанненные могут оживлять сами себя
   if (!$principal->IsGrantedTo("acl_text", NULL, NULL, $rh->node_admins))
    if (!$this->HasAccess(&$principal, "owner"))
       return $this->Forbidden("YouDontOwnThisAccount"); 
  else  // остальных могут оживлять только админы    
   if (!$principal->IsGrantedTo("acl_text", NULL, NULL, $rh->node_admins))
     return $this->Forbidden("NotAnAdmin"); 

  $rh->UseClass("ConfirmForm", $rh->core_dir);
  $confirm = &new ConfirmForm( &$rh, "account_alive", $rh->message_set."_confirm_account_alive" );
  
  $result = $confirm->Handle();
  if ($result === false) $result = $confirm->ParseConfirm();
  
  $tpl->Assign("Preparsed:CONTENT", $result);

  return GRANTED;

?>