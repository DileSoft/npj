<?php
//  {{PageVersions ... }}

  array_unshift( $params, $this->npj_object_address );
  $params["show"] = "announces";
  return include( $dir."/versions.php" );
?>