<?php
//  {{Digests ...}}

  array_unshift( $params, $this->npj_object_address );
  $params["digests"] = 1;
  return include( $dir."/changes.php" );
?>