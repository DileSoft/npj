<?php
//  {{keywords style=br }}

  array_unshift( $params, $this->npj_account );
  $params["filter"] = "keywords";
  $params["depth"]  = "full";
  if (!$params["style"]) $params["style"] = "br";
  return include( $dir."/tree.php" );
?>
