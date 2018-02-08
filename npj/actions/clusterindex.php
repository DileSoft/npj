<?php
//  {{ClusterIndex depth=1, filter }}

  array_unshift( $params, $this->npj_object_address );
  if (!$params["depth"]) $params["depth"] = 1;
  $params["index"] = 1;
  return include( $dir."/tree.php" );
?>
