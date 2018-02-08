<?php
 
  // !!!!! Перенести идею кода в NpjRH

  $tpl->Assign("Preparsed:PRINT", 1);

  $res = include( $dir."/show.php" );

  $parsed = $tpl->GetValue("Preparsed:CONTENT");
  $parsed = preg_replace( "/<\/p><br \/>/i", "</p>", $parsed );
  $parsed = preg_replace( "/<br \/>(<a[^>]+><\/a><p)/i", "$1", $parsed );
  $parsed = preg_replace( "/<p class=\"auto\"/i", "<p class=\"auto-print\"", $parsed );
  $tpl->Assign( "Preparsed:CONTENT", $parsed);

  return $res;

?>