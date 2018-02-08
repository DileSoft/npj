<?php

  // получаем запись
  $this->record = &new NpjObject( &$rh, $object->npj_account.":".$object->npj_context );
  $this->record->Load(3);
  if (!is_array($this->record->data)) return $this->NotFound("RecordNotFound");

  $p = array(
          "for"     => $object->npj_object_address,
          "show"    => "all",
          "style"   => "diff",
          "wrapper" => "none",
            );
  if ($params["_all"]) $p["limit"] = 10000;
  else
  if ($this->name != "") return $this->Handler( "show", &$params, &$principal );

  
  $this->record->data["subject_post"] = 
    $this->record->Format($this->record->data["subject_r"], $this->record->data["formatting"], "post");
  $tpl->Assign( "Preparsed:TITLE", $this->record->data["subject_post"] );
  $tpl->Assign( "Preparsed:CONTENT", $this->record->Action( "versions", $p, &$principal ) );

  return GRANTED;

?>