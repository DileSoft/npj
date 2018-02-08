<?php

  $tpl->Assign("Href:NodeMenu.Root", $tpl->GetValue("/"));
  $tpl->Assign("NodeMenu.NodeName", $rh->node_title );

  echo $tpl->Parse( "userpanel/nodemenu.html:Static" );

?>