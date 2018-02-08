<?php

  // spawned from "delete"

  // проверка на банлист
  if (!$rh->account->HasAccess( &$principal, "not_acl", "banlist" )) return $this->Forbidden("YouAreInBanlist");

  $record = &new NpjObject( &$rh, $object->npj_account.":".$object->npj_context );
  $record->Load(2); // ??? Suspicious.
  $this->record = &$record;
  
  if (!is_array($record->data)) return $this->NotFound("RecordNotFound");

  $data = $object->Load(3);
  if (!is_array($data)) return $this->NotFound("CommentNotFound");

  if (!(
       $record->HasAccess(&$principal, "owner") || $rh->account->HasAccess(&$principal, "rank_greater", GROUPS_MODERATORS)
       )
     )
      return $this->Forbidden("CommentFreeze"); 

  $rh->UseClass("ConfirmForm", $rh->core_dir);
  $confirm = &new ConfirmForm( &$rh, "comment_freeze", $rh->message_set.
                  ($script_name=="freeze"?"_confirm_comment_freeze":"_confirm_comment_unfreeze")
                             );
  
  $tpl->Assign("Href:Record", $record->Href( $record->npj_address, NPJ_ABSOLUTE, STATE_IGNORE ) );
  $tpl->Assign("have_parent", $data["parent_id"]==0?0:1 );
  $tpl->Assign("Href:Parent", $record->Href( $record->npj_address."/comments/".$data["parent_id"], NPJ_ABSOLUTE, STATE_IGNORE ) );
  
  $result = $confirm->Handle();
  if ($result === false) $result = $confirm->ParseConfirm();
  
  $tpl->Assign("Preparsed:TITLE", ""); 
  $tpl->Assign("Preparsed:CONTENT", $result);

  return GRANTED;

?>