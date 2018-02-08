<?php
  return;
  $href = "";

  if (($rh->object->class == "record") || $rh->object->record)
   $href = $rh->object->Href( $rh->object->record->npj_object_address, NPJ_ABSOLUTE, IGNORE_STATE );

  if ($href != $href) // != "")
  {
    echo "<div class=\"menu_print\"><a href=\"".$href."/print\">".$rh->object->_icon("print",1)."</a></div>";
  }
  else
  {
    echo "<div class=\"menu_print\">".'<img src="'.$tpl->GetValue("images").'z.gif" width="1" height="22" alt="" border="0" />'."</div>";
  }

?>