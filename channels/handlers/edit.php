<?php

  // "EDIT" handler

  $channel = &$this->SpawnChannel( $params["type"], $params["id"], CHANNELS_ID ); 

  // =================================================================================
  //  ФАЗА 1. Проверка прав доступа
  $forbidden = 1;
  if ($principal->IsGrantedTo("acl_text", NULL, NULL, $rh->node_admins))
    $forbidden = 0;
  else
  {
    $manager_data = &$this->object->_LoadById( $channel->data["channel:managing_user_id"], 2, "account" );
    if ($principal->IsGrantedTo("owner", "account", $manager_data["user_id"] ))
      $forbidden = 0;
  }
  if ($forbidden) return $rh->account->Forbidden( "Channels.Edit" );

  // Устанавливаем параметры
  $params["mode"] = "edit";
  $params["&channel"] = &$channel;

  // Уходим в инклюд, общий для EDIT / NEW
  include( dirname(__FILE__)."/__include_new_edit.php" );

  $tpl->Assign("Preparsed:TITLE",   $tpl->message_set["Channels.Title:Edit"] );

  return GRANTED;


?>