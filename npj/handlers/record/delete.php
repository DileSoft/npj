<?php

  $account = &new NpjObject( &$rh, $this->npj_account );
  $adata = $account->Load(3);
  if (!is_array($adata))
     return $this->account->Forbidden("NoSuchUser"); 

  if ($state->Get("confirm") != "record_delete_granted")
  {
    $data = &$this->Load( 2 );
    if (!is_array($data))
       return $this->Forbidden("NoSuchRecord"); 
    $forbidden = true;
    
    if ($this->GetType() == RECORD_DOCUMENT)
      if ($this->HasAccess( &$principal, "acl", "remove" )) $forbidden = false;

    if ($this->HasAccess( &$principal, "owner" )) $forbidden = false;

    // админы узла могут удалять записи (опция)
    if ($rh->admins_delete_records)
      if ($this->HasAccess( &$principal, "node_admins" )) $forbidden = false;

    if ($forbidden) return $this->Forbidden("RecordForbiddenDelete"); 
  }
  else
  {
    $tpl->Assign("404", "record deleted");
  }

  // проверка на банлист
  if (!$rh->account->HasAccess( &$principal, "not_acl", "banlist" )) return $this->Forbidden("YouAreInBanlist");

  $rh->UseClass("ConfirmForm", $rh->core_dir);
  $confirm = &new ConfirmForm( &$rh, "record_delete", $rh->message_set."_confirm_record_delete" );
  
  $result = $confirm->Handle();
  if ($result === false) $result = $confirm->ParseConfirm();
  
  $tpl->Assign("Preparsed:CONTENT", $result);

  return GRANTED;

?>