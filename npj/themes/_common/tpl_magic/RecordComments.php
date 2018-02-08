<?php

  if ($rh->theme_tunings["hide_comments_for_guest"])
   if (!$rh->principal->IsGrantedTo("noguests"))
      { echo "<br /><br /><br />"; return GRANTED; }

  if ($tpl->GetValue("Preparsed:COMMENTS") == "")
      { echo "<br /><br /><br />"; return GRANTED; }

  echo $tpl->Parse("record_comments.html");

?>