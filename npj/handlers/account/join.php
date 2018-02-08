<?php
  // alias.
  $_object = &new NpjObject( &$rh, $this->npj_object_address.":friends/join" );
  return $_object->Handler( "join", &$params, &$principal );
?>