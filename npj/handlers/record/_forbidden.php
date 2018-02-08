<?php

  $tpl->Assign( "Access:Forbidden", 1 );
  $tpl->Parse( "forbidden.common.html", "Preparsed:CONTENT" );
  return GRANTED; 

?>