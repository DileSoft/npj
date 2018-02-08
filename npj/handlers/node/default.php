<?php

  $page_name = "ForeignNode";
  // 1. если это местная нода, то берём HomePage от пользователя Node@NPJ
  if ($this->npj_node == $rh->node_name) $page_name = "HomePage";

  $page_name = $rh->node_user.":".$page_name;

  $obj = &new NpjObject( &$rh, $page_name );
  $obj->Load(4);
  $obj->_Trace("Root page");
  $this->method = "show";

  echo $obj->Handler( "show", "", &$principal );

  return GRANTED;

?>