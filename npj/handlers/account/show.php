<?php

  $obj = &new NpjObject( &$rh, $object->name.":" );
  $this->record = &$obj;
  $data = $obj->Load(4);
  if ($data) 
    echo $obj->Handler( "show", &$params, &$principal );
  else return $this->NotFound("AccountNotFound");

  return GRANTED;

?>