<?php

  $f=0;
  echo "<div class=\"head_menu\">";
  foreach( $rh->node_menu as $npj=>$text )
  {
    if ($f) echo "&nbsp;|&nbsp;"; else $f=1;
    echo $rh->object->Link( $npj, "deprecated", $text, NPJ_RELATIVE, NPJ_IGNORE_STATE );
  }
  echo "</div>";

?>