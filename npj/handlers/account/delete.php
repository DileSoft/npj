<?php

  if ($state->Get("confirm") != "account_delete_granted")
  {
    $data = &$this->Load( 2 );
    $forbidden = true;
    
    if ($this->HasAccess( &$principal, "node_admins" )) $forbidden = false;

    if ($forbidden) return $rh->Redirect( $this->Href( $this->npj_object_address.":manage/freeze", NPJ_ABSOLUTE, IGNORE_STATE ), IGNORE_STATE );
  }
  else
  {
    $tpl->Assign("404", "record deleted");
  }

  $rh->UseClass("ConfirmForm", $rh->core_dir);
  $confirm = &new ConfirmForm( &$rh, "account_delete", $rh->message_set."_confirm_account_delete" );
  
  $result = $confirm->Handle();
  if ($result === false) $result = $confirm->ParseConfirm();
  
  $tpl->Assign("Preparsed:CONTENT", $result);

  return GRANTED;

?>