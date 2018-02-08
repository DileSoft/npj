<?php

   session_start();

   $f = fopen( "z.gif", "rb" );
   fpassthru ($f);
   exit;

?>