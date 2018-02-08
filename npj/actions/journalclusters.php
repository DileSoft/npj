<?php
//  {{JournalClusters style, depth }}

  array_unshift( $params, $this->npj_account );
  $params["filter"] = "clusters";
  return include( $dir."/tree.php" );
?>
