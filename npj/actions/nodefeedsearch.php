<?php
//  {{NodeFeedSearch ...}}

  $params["where"] = "node";
  $params["mode"] = "feed";
  $params["filter"] = "posts";
  return include( $dir."/search.php" );
?>
