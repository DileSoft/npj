<?php
  $ms = &$tpl->message_set;

  if ($tpl->GetValue("Preparsed:PRINT")) return;
  if ($tpl->GetValue( "UserPanel.Guest" )) return;


  $_f=0;
   if (is_array($rh->principal->data["user_menu"]))
   {
    foreach ($rh->principal->data["user_menu"] as $k=>$item)
    {
      if ($item["title"] == "") $item["title"] = $rh->object->AddSpaces( $item["title"] , " " );
      $rh->principal->data["user_menu"][$k]["link"] = $rh->object->Link($item["npj_address"], "", $item["title"]);
      if ($rh->object->npj_address == $item["npj_address"]) 
        $_f=1;
    }
   }
  if ($rh->principal->data["user_id"] != 1) 
  { 
     if ($_f)
      $tpl->Assign( "Link:UserMenu.Add", "-&nbsp;<a href=\"?menu=1\"> ".$ms["UserMenu.Remove"]."</a>");
     else
      $tpl->Assign( "Link:UserMenu.Add", "+&nbsp;<a href=\"?menu=1\">".$ms["UserMenu.Add"]."</a>");
     $tpl->Append( "Link:UserMenu.Add", "&nbsp;&nbsp;(<span title='редактировать ваше меню'>".$rh->object->Link( $tpl->GetValue("Npj:Principal").":manage/usermenu",
                                                           "", "..." )."</span>)");
  }

  $list = &new ListObject( &$rh, &$rh->principal->data["user_menu"] );
  echo $list->Parse("userpanel/usermenu.html:List");

?>
