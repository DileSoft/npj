<?php
//  {{Communities account_class}}

  $params["show"] = "communities";
  if (isset($params[0]) && !isset($params["class"])) $params["class"] = $params[0];
  return include( $dir."/directory.php" );
?>
