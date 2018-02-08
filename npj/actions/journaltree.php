<?php
//  {{JournalTree style, depth, filter }}

  array_unshift( $params, $this->npj_account );
  return include( $dir."/tree.php" );
?>
