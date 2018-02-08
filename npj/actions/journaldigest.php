<?php
//  {{JournalDigest ... }}

  $params["feed"] = $object->npj_account.":";
  $params["hide_feed"] = 1;
  $params["dtlast"]    = 1;
  return include( $dir."/digest.php" );
?>
