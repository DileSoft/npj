<?php
//  {{RecentComments [style="simple|brief|full"]}}

  $params["order"] = "comments";

  array_unshift( $params, $this->npj_account.":" );
  return include( $dir."/changes.php" );
?>
