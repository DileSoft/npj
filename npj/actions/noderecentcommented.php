<?php
//  {{NodeRecentCommented [style="simple|brief|full"]}}

  $params["order"] = "commented";
  array_unshift( $params, "@" );
  return include( $dir."/changes.php" );
?>
