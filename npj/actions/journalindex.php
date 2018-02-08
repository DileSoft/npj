<?php
//  {{JournalIndex filter }}

  array_unshift( $params, $this->npj_account );
  $params["index"] = 1;
  $params["style"] = "ol";
  return include( $dir."/tree.php" );
?>
