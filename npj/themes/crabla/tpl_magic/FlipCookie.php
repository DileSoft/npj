<?php

  $is_up = 1;
  if ($_COOKIE["flip_criba"] == "down") $is_up=0;
  if (!$rh->principal->IsGrantedTo("noguests")) $is_up=0;

  $tpl->Assign("UserPanel:IsUp", $is_up);
  
?>