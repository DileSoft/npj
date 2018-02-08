<?php


  $data = &$this->Load( 4 );

  // 404
  if (!data || $data=="empty") return $this->NotFound("RecordNotFound");


  // check access
  $forbidden=0;

  if (!$this->HasAccess( &$principal, $this->security_handlers[$data["type"]], "read" )) 
    $forbidden=1;
  if (!$this->HasAccess( &$principal, $this->security_handlers[$data["type"]], "source" )) 
    $forbidden=1;

  // who always can:
  if ($this->HasAccess( &$principal, "owner" )) 
    $forbidden=0;
  if ($this->HasAccess( &$principal, "acl_text", $rh->node_admins ) && ($this->npj_account==$rh->node_user))
    $forbidden=0;
     
     
  if ($forbidden) return $this->Forbidden("RecordForbidden");


  // preparse --------------------
   $data["subject_post"] = $this->Format($data["subject_r"], $data["formatting"], "post");

   $data["body_source"] = $this->Format($data["body"], "source", array(
                                  "default" => $data["formatting"],
                                  "copy_button" => 1,
                                  "source"  => $this->npj_account.":".$data["tag"]));


  // howl!
   $t = $tpl->message_set["TitleSource"].": ";
   $tpl->Assign( "Preparsed:TITLE", $t.$data["subject_post"] );
   if ($data["subject_post"] == "") $data["subject_post"] = $rh->account->data["journal_name"];
   $tpl->Assign( "Html:TITLE", $t.$data["subject_post"] );
   $tpl->Assign( "Preparsed:CONTENT", $data["body_source"] );

  // done
  return GRANTED;

?>