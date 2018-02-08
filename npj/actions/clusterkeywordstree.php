<?php
//  {{ClusterTree style=ol }}

  array_unshift( $params, $this->npj_object_address );
  $params["filter"] = "keywords";

  return include( $dir."/tree.php" );
?>
