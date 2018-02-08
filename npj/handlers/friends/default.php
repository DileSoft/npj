<?php

  if ($rh->account->fake) return $this->NotFound("AccountNotFound");
  $data = $rh->account->Load(2);

  $friends_template = "friends";
  if (in_array($data["friends_template"], $rh->friends_templates))
   $friends_template = $data["friends_template"];
  
  if ($data["account_type"] > 0)
    return $this->Handler( "edit", array("readonly"=>1), &$principal );

  $record = &new NpjObject( &$rh, $rh->account->data["login"]."@".$rh->account->data["node_id"].":" );
  $tpl->Assign("Preparsed:TITLE", "Лента корреспондентов");
  $tpl->Assign("Preparsed:CONTENT", $record->Action("feed", 
    array("type"=>"correspondents", "style"=>$friends_template), &$principal ) );

?>