<?php

  $tpl->Assign( "404", 1);
  $tpl->Parse( "404.common.html", "Preparsed:CONTENT" );
  return GRANTED; 

?>