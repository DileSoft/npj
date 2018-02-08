<?php

  // установка картинки как "по-умолчанию"

  $data = $this->Load(2);
  if (!is_array($data)) return $this->NotFound("AccountNotFound");
 if (!$this->HasAccess( &$principal, "owner" )
     && 
     !($this->HasAccess( &$principal, "acl_text", $rh->node_admins ) && 
       (($this->npj_account == $rh->node_user) || ($this->npj_account == $rh->guest_user)))
    ) 
    return $this->Forbidden("UserpicsTune"); 

  $rh->UseClass("ConfirmForm", $rh->core_dir);
  $confirm = &new ConfirmForm( &$rh, "userpic_default", $rh->message_set."_confirm_userpic_default" );
  
  $result = $confirm->Handle();
  if ($result === false) $result = $confirm->ParseConfirm();
  
  // ??? Prepased:Title
  $tpl->Assign("Preparsed:CONTENT", $result);

  return GRANTED;

?>