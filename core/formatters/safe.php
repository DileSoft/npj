<?php

  $text = htmlspecialchars( $text );

  $text = str_replace( "\n", "<br />", $text );

  echo $text;

?>