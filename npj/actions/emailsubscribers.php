<?php
//  {{EmailSubscribers for=kuso@npj totals=1}}

  if (!isset($params[0])) $params[0] = "";
  $params["only_email"] = 1;
  return include( dirname(__FILE__)."/subscribers.php" );
?>
