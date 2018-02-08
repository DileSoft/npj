<?php
//  {{RecentCommented [style="simple|brief|full"]}}

  $params["order"] = "commented";

  array_unshift( $params, $this->npj_account.":" );
  return include( $dir."/changes.php" );
?>
