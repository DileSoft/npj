<?php

  // "NEW" handler

  // =================================================================================
  //  ���� 1. �������� ���� �������
  $forbidden = 1;
  if ($principal->IsGrantedTo("acl_text", NULL, NULL, $this->config["security_acl"])) 
    $forbidden = 0;
  if (isset($this->config["security_account_classes"][ $principal->data["account_class"] ]))
    $forbidden = 0;
  if ($principal->IsGrantedTo("acl_text", NULL, NULL, $rh->node_admins))
    $forbidden = 0;

  if ($forbidden) return $rh->account->Forbidden( "Channels.New" );

  // ������������� ���������
  $params["mode"] = "add";
  $params["&channel"] = &$this->SpawnChannel( $params["type"] );

  // ������ � ������, ����� ��� EDIT / NEW
  include( dirname(__FILE__)."/__include_new_edit.php" );

  $tpl->Assign("Preparsed:TITLE",   $tpl->message_set["Channels.Title:New"] );

  return GRANTED;


?>