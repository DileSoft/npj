<?php

  // spawned from "frozen"

  // проверка на банлист
  if (!$rh->account->HasAccess( &$principal, "not_acl", "banlist" )) return $this->Forbidden("YouAreInBanlist");

  // загрузка записи
  $record = &new NpjObject( &$rh, $object->npj_account.":".$object->npj_context );
  $record->Load(2); // ??? Suspicious.
  $this->record = &$record;
  
  if (!is_array($record->data)) return $this->NotFound("RecordNotFound");

  // загрузка комментария
  $data = $object->Load(3);
  if (!is_array($data)) return $this->NotFound("CommentNotFound");


  // проверка наличия фильтра в адресе и опции на узле, доступа к фильтру
  $cf = $rh->community_filter && ($rh->object->npj_filter != "");
  if (!$cf) return $this->Forbidden("CommunityFilterNotSet"); 

  $filter_object = &new NpjObject( &$rh, $rh->object->npj_filter."@".$rh->node_name );
  $filter_data   = $filter_object->Load(2);
  if (!is_array($filter_data)) return $this->Forbidden("CommunityFilterNotSet"); 

  if (!$rh->principal->IsGrantedTo( "rank_greater", "account", $filter_data["user_id"], GROUPS_MODERATORS ))
    return $this->Forbidden("CommunityFilterDenied"); 


  // конфирм-форма
  $rh->UseClass("ConfirmForm", $rh->core_dir);
  $confirm = &new ConfirmForm( &$rh, "comment_filter", $rh->message_set.
                  ($params[0]=="reset"?"_confirm_comment_unfilter":"_confirm_comment_filter")
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