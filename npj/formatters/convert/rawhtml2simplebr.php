<?php

  // #1. remove "\n"
  $text = str_replace( "\n", "", $text );

  // #2. replace </P><P> => \n
  $text = preg_replace( "/<\/p><p>/i", "\n", $text );

  // #3. remove <P>,</P>
  $text = preg_replace( "/<\/?p>/i", "", $text );

  // #4. replace <BR> => \n
  $text = preg_replace( "/<br(\s*/)>/i", "\n", $text );

  echo $text;

?>