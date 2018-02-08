<?php
//  {{ClusterTree style=ol }}

  array_unshift( $params, $this->npj_object_address );
  $params["filter"] = "clusters";
  $params["depth"]  = "full";
  if (!$params["style"]) $params["style"] = "ol";
  return include( $dir."/tree.php" );
?>
