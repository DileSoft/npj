<?php

  if (!$rh->principal->IsGrantedTo("noguests")) 
   if ($rh->theme_tunings["hide_login"]) return;

  echo $tpl->Parse("userpanel/top.html");

?>