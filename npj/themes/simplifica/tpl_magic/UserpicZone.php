<?php


  $desc = htmlspecialchars($rh->account->data["journal_desc"]);
  $desc = str_replace("\n", "<br />", $desc);


  if ($rh->account->data["journal_name"] == "")
    $tpl->Assign("JournalHead.Title", $rh->account->data["user_name"]);
  else
    $tpl->Assign("JournalHead.Title", $rh->account->data["journal_name"]);
  $tpl->Assign("JournalHead.Desc", $desc);

  // userpic
  if (($rh->object->method != "default") || ($rh->object->params[0] == "show"))
  {
   $tpl->Assign("Href:Userpic", $rh->object->Href( $rh->account->npj_object_address.":", NPJ_ABSOLUTE, IGNORE_STATE ));
   $tpl->Assign("Title:Userpic", "������� � �������");
  }
  else
  {
   $tpl->Assign("Href:Userpic", $rh->object->Href( $rh->account->npj_object_address.":profile", NPJ_ABSOLUTE, IGNORE_STATE ));
   $tpl->Assign("Title:Userpic", $tpl->message_set["ShowProfile"]);
  }

  $tpl->Assign("JournalHead.UserPic", "<img title=\"".$tpl->GetValue("Title:Userpic").
               "\" src=\"".$rh->user_pictures_dir.$rh->account->data["user_id"]."_big_".$rh->account->data["_pic_id"].
               ".gif\" hspace=\"0\" vspace=\"0\" border=\"0\" alt=\"\" />");

  // ---------- ������ ������, ������ ������ ------------------------------
  switch( $rh->account->data["account_type"] )
  {
     case ACCOUNT_USER:
                
                $menu = array(
                           "/"        => "������",
                           "/info"    => "� ������������",
                           "/2005"        => "���������",
                           "/keywordstree" => "������� �������",
                           "/friends" => "����������",
                             );

                if ($rh->account->HasAccess( $rh->principal, "owner"))
                {
                  $menu[ "/manage" ] = "��������� �������";
                }

                $menu["--"] = "";

                $menu[$rh->principal->data["login"]."@".
                      $rh->principal->data["node_id"].":friends/add/".$rh->account->npj_account
                                          ] = "�������� �&nbsp;��������������";
                $menu["/subscribe"]         = "����������� ��&nbsp;������";

                break;

     default:
                $menu = array(
                           "/"             => "������",
                           "/info"         => "� ������",
                           "/2005"        => "���������",
                         //  "/keywordstree" => "������� �������",
                             );

                $menu["--"] = "";

                $menu[$rh->principal->data["login"]."@".
                      $rh->principal->data["node_id"].":friends/add/".$rh->account->npj_account
                                          ] = "�������� �&nbsp;��������������";
                $menu["/subscribe"]         = "����������� ��&nbsp;������";

                if ($rh->account->HasAccess( $rh->principal, "rank_greater", GROUPS_LIGHTMEMBERS))
                {
                  $menu[ "/join" ] = "�������� ������";
                }
                else
                {
                  $menu[ "/join" ] = "�������� � ������";
                }
  
  }
  $_menu = array();
  foreach( $menu as $k=>$v )
   if ($v == "") $_menu[$k] = $v;
   else          $_menu[ $rh->account->Href($k) ] = $v;

  $menu = array();
  foreach( $_menu as $k=>$v )
  {
    $menu[$k]["href"] = $k;
    $menu[$k]["text"] = $v;
    $menu[$k]["title"] = ($k == $rh->Href($rh->url)) && !($v == ""); 
  }


  //$debug->Error_R( $_menu );

  $list = &new ListSimple( $rh, $menu );
  $list->Parse( "design/userpic.html:List", "Menu" );

  echo $tpl->Parse( "design/userpic.html:Body" );


?>