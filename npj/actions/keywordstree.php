<?php
//  {{KeywordsTree style=ol }}

  array_unshift( $params, $this->npj_account );
  $params["filter"] = "keywords";
  if (!isset($params["depth"])) $params["depth"] = "full";
  if (!$params["style"]) $params["style"] = "ul";
  return include( $dir."/tree.php" );
?>