<?php
//  {{DigestTree style=ol }}

  array_unshift( $params, $this->npj_account );
  $params["filter"] = "digests";
  if (!isset($params["depth"])) $params["depth"] = "full";
  if (!$params["style"]) $params["style"] = "ul";
  return include( $dir."/tree.php" );
?>