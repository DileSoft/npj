<?php

  if (!is_array($object->data)) return $this->NotFound("AccountNotFound");

  $obj = &new NpjObject( &$rh, $object->name.":" );
  $this->record = &$obj;

  if ($params[0] == "add_friend")
//   $rh->Redirect( $this->Href( $principal->data["login"]."@".$principal->data["node_id"].":friends/add", NPJ_ABSOLUTE).
//                  "/".$this->npj_account );
  $rh->Redirect
//  $debug->Error
  ($object->Href
    ($this->_NpjAddressToUrl
      ($principal->data["login"]."@".$principal->data["node_id"].
                  ($principal->data["node_id"]==$rh->node_name?"":"/".$rh->node_name).
                  ":friends/add/".$object->name, 
                 NPJ_ABSOLUTE), IGNORE_STATE)
//                               );
   );
  
  if ($this->HasAccess( &$principal, "owner" ) ||
      ($this->HasAccess( &$principal, "acl_text", $rh->node_admins ) && 
        (($this->npj_account == $rh->node_user) || ($this->npj_account == $rh->guest_user))
       ))
    if ($object->params[0] == "usermenu") 
      return $object->Handler( "_manage_usermenu", &$params, &$principal );

  if ($object->params[0] == "unfreeze") 
    return $object->Handler( "_manage_unfreeze", &$params, &$principal );

//  if ($this->HasAccess( &$principal, "owner" )) // commented by kukutz @ 18102003
  if ($object->params[0] == "confirm")      
    return $object->Handler( "_manage_confirm", array(
                                             "email"=>preg_replace("/^([^\.]+)\./","$1@", $params[1] ),
                                             "confirm"=>$params[2],
                                             "messages"=>$rh->message_set."_confirmation"
                                                      ), &$principal );

  if (($object->params[0] == "freeze") || ($object->params[0] == "suspend") 
     || ($object->params[0] == "alive"))
    return $object->Handler( "_manage_".$object->params[0], &$params, &$principal );

  if ($this->HasAccess( &$principal, "owner" ))
  {
    if ($object->params[0] == "moderate")
      return $object->Handler( "_manage_".$object->params[0], &$params, &$principal );

    // [!!!] вообще говоря, это подпорка, которую надо будет убрать
    if ($this->HasAccess( &$principal, "acl_text", $rh->node_admins ) && ($this->npj_account == $rh->node_user))
      $tpl->Assign("IsNode", 1);
    else
      $tpl->Assign("IsNode", 0);

    $tpl->theme = $rh->theme;
    $tpl->Parse( "manage.html:Account".$rh->account->data["account_type"], "Preparsed:CONTENT" );
    $tpl->Assign( "Preparsed:TITLE", "Администрирование журнала" ); // !!! to messageset
    $tpl->theme = $rh->skin;
    return GRANTED;
  }

  if ($rh->account->HasAccess( &$principal, "rank_greater", GROUPS_MODERATORS ))
  {
    if ($object->params[0] == "moderate")
      return $object->Handler( "_manage_".$object->params[0], &$params, &$principal );

    $tpl->theme = $rh->theme;
    $tpl->Parse( "manage.html:Account".$rh->account->data["account_type"]."_Moderators", "Preparsed:CONTENT" );
    $tpl->Assign( "Preparsed:TITLE", "Модерирование журнала" ); // !!! to messageset
    $tpl->theme = $rh->skin;
    return GRANTED;
  }


  return $this->Forbidden("SettingsEdit");

?>
