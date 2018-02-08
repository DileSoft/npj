<?php

  $data = &$this->Load( 1 );

  // проверка на банлист
  if (!$rh->account->HasAccess( &$principal, "not_acl", "banlist" )) return $this->Forbidden("YouAreInBanlist");

  if (!$this->HasAccess( &$principal, $this->security_handlers[$data["type"]], "acl_read" )) return $this->Forbidden("RecordForbiddenACL");

  if ($data["type"]==1) $security = "_groups";
  else if ($data["type"]==2) $security = "_acl";
  else $debug->Error("Handler [records.show] -- unknown record type: ".$data["type"].".");

  $this->method = $security;
  $this->Handler( $this->method, $params, $principal );

?>