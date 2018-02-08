<?php

  $order = $this->GetValue("PanelOrderSplit:order");
  $prev  = $this->GetValue("PanelOrderSplit:current");
  $next  = $this->GetValue($order);

  $params = $this->GetValue("PanelOrderSplit:params"); 

  if ($prev != $next)
  {
    $msg = $params["split_".$next];
    if (isset($msg))
    {
      $this->Assign( "PanelOrderSplit:name", $msg );
      echo $this->Parse( "magic_panel_order_split.html" );
    }
    $this->Assign( "PanelOrderSplit:current", $next );
  }
?>