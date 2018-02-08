<?php

  $rh->UseClass("ListObject", $rh->core_dir);

  if ($tpl->GetValue("Preparsed:PRINT")) return;

  if (!$rh->principal->IsGrantedTo("noguests")) 
   if ($rh->theme_tunings["hide_login"]) return;
   else
   {
     $tpl->Assign( "UserPanel.Guest", 1 );
     $tpl->Assign( "UserPanel.FORM", $state->FormStart( MSS_POST, "login", "id=\"loginFormHere\" name=\"loginFormHere\"" ) );
     $tpl->Assign( "UserPanel.FORM.Return", $rh->Href( $rh->url, STATE_IGNORE ));
   }
  else $tpl->Assign( "UserPanel.Guest", 0 );

  if ($rh->principal->data["node_id"] == $rh->node->data["node_id"])
   $tpl->Assign( "UserPanel.foreign", 0);
  else
   $tpl->Assign( "UserPanel.foreign", 1);

  $tpl->Assign("UserPanel.userpic", "<img title=\"".$tpl->GetValue("Title:Userpic").
               "\" src=\"".$rh->user_pictures_dir.$rh->principal->data["user_id"]."_small_".$rh->principal->data["_pic_id"].
               ".gif\" hspace=\"0\" vspace=\"6\" border=\"0\" alt=\"\" />");

  $tpl->Assign("UserPanel.user_name", $rh->principal->data["user_name"] );

  echo $tpl->Parse("userpanel/head.html");

?>