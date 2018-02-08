<?php
//  {{NodeRecentComments [style="simple|brief|full"]}}

  $params["order"] = "comments";
  array_unshift( $params, "@" );
  return include( $dir."/changes.php" );
?>
