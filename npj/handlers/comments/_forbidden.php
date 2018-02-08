<?php

  $tpl->Assign( "404", 1 );
  $tpl->Parse( "forbidden.common.html", "Preparsed:CONTENT" );
  return GRANTED; 

?>