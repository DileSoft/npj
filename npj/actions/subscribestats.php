<?php
//  {{SubscribeStats for=kuso@npj}}

  if (!isset($params[0])) $params[0] = "";
  $params["totals"] = 1;
  return include( dirname(__FILE__)."/subscribers.php" );
?>
