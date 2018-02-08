<?php
//  {{PageVersions ... }}

  array_unshift( $params, $this->npj_object_address );
  $params["show"] = "versions";
  return include( $dir."/versions.php" );
?>