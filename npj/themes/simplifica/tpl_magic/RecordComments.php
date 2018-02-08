<?php

  if ($rh->theme_tunings["hide_comments_for_guest"])
   if (!$rh->principal->IsGrantedTo("noguests"))
      { echo "<br /><br /><br />"; return GRANTED; }

  if ($tpl->GetValue("Preparsed:COMMENTS") == "")
      { echo "<br /><br /><br />"; return GRANTED; }

  if (isset($rh->hide_keyword_comments))
  {
    if ($rh->object->class == "account") return;
    if (($rh->object->class == "record")
        && $rh->object->data["is_keyword"]) return;
  }
  
  echo $tpl->Parse("record_comments.html");

?>