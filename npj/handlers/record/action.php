<?php

  $data = &$this->Load( 4 );

  if ($data && $data!="empty") 
  {
   if (!$this->HasAccess( &$principal, $this->security_handlers[$data["type"]] )) 
     return $this->Forbidden("RecordForbidden");


   $action = strtolower( array_shift( $params ) );
   $params["wrapper"] = "none";
   $params["action_as_handler"] = 1;
   $params["action_target"] = $this->npj_object_address;
   $result = $this->Action( $action, &$params, &$principal );

   $tpl->Assign("Preparsed:CONTENT", $result );
   $tpl->Assign("Preparsed:TITLE", $tpl->GetValue("Action:TITLE") ); 
   $tpl->Assign("Preparsed:COMMENTS", ""); 

   $tpl->Assign("NoRecordStats", 2);
   //$tpl->Assign("404", 1);

  } 
  else return $this->NotFound("RecordNotFound");

  return GRANTED;
?>