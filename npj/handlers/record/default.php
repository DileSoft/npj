<?php
 
  // 70% from
  $tpl->Assign( "Preparsed:READABLE", 1 );

  $this->method = "show";
  $this->Handler( $this->method, $params, $principal );

?>