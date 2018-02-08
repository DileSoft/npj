<?php

 $tpl->theme = $rh->theme;
   $tpl->Assign( "Link:Result", $this->Link( $params[1]. "@".$rh->node_name ) );
   $tpl->Parse( "registration.success.html", "Preparsed:CONTENT" );
 $tpl->theme = $rh->skin;


?>