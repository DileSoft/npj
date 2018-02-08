<?php

  $desc = htmlspecialchars($rh->account->data["journal_desc"]);
  $desc = str_replace("\n", "<br />", $desc);


  if ($rh->account->data["journal_name"] == "")
    $tpl->Assign("JournalHead.Title", "∆урнал &laquo;".$rh->account->data["user_name"]."&raquo;");
  else
    $tpl->Assign("JournalHead.Title", $rh->account->data["journal_name"]);
  $tpl->Assign("JournalHead.Desc", $desc);

  // ??? subject to change:
  /*
  $rs = $db->Execute("select have_big, have_small from ".$rh->db_prefix."userpics where user_id=".
                     $db->Quote($rh->account->data["user_id"])." and pic_id = ".
                     $db->Quote($rh->account->data["_pic_id"]));
  if ($rs->fields["have_big"]) 
   $ext = $rh->account->data["user_id"]."_big_".$rh->account->data["_pic_id"].$rs->fields["have_big"];
  else if ($rs->fields["have_small"]) 
   $ext = $rh->account->data["user_id"]."_small_".$rh->account->data["_pic_id"].$rs->fields["have_small"];
  else $ext = "z.gif";
  $tpl->Assign("JournalHead.UserPic", "<img src=/".$rh->user_pictures_dir.$ext." hspace=0 vspace=6 border=0 alt='' />");
  */
  if (($rh->object->method != "profile") || ($rh->object->params[0] == "pictures"))
  {
   $tpl->Assign("Href:Userpic", $rh->object->Href( $rh->account->npj_object_address.":profile", NPJ_ABSOLUTE, IGNORE_STATE ));
   $tpl->Assign("Title:Userpic", $tpl->message_set["ShowProfile"]);
  }
  else
  {
   $tpl->Assign("Href:Userpic", $rh->object->Href( $rh->account->npj_object_address.":profile/pictures", NPJ_ABSOLUTE, IGNORE_STATE ));
   $tpl->Assign("Title:Userpic", $tpl->message_set["ShowUserpics"]);
  }
  $tpl->Assign("JournalHead.UserPic", "<img title=\"".$tpl->GetValue("Title:Userpic").
               "\" src=\"".$rh->user_pictures_dir.$rh->account->data["user_id"]."_big_".$rh->account->data["_pic_id"].
               ".gif\" hspace=\"0\" vspace=\"6\" border=\"0\" alt=\"\" />");


  $selected=1;

  // ---------- панели ссылок, ссылок панели ------------------------------
  // ѕќЋ№«ќ¬ј“≈Ћ№
  if ($rh->account->data["account_type"]== ACCOUNT_USER)
  { // кака€ выбрана?
  if (($rh->object->class=="account") &&
      (($rh->object->method == "settings") || ($rh->object->method == "profile")))
      $selected = 4;
  if ($rh->object->method=="changes") $selected = 2;
  if ($rh->object->class=="friends") $selected = 3;
    // какие есть?
  $links = array("href", $tpl->GetValue("Href:Account"),
                          $tpl->GetValue("Href:Account")."/changes",
                          $tpl->GetValue("Href:Account")."/friends",
                          $tpl->GetValue("Href:Account")."/profile",
                          );
  $texts = array("linktext", 
                          $tpl->message_set["Journal"],
                          $tpl->message_set["Changes"],
                            $tpl->message_set["Correspondents"],
                          $tpl->message_set["Profile"],
                          );
  }
  // —ќќЅў≈—“¬ќ
  if ($rh->account->data["account_type"]== ACCOUNT_COMMUNITY)
  { // кака€ выбрана?
    if (($rh->object->class=="account") &&
        (($rh->object->method == "settings") || ($rh->object->method == "profile")))
        $selected = 3;
    if ($rh->object->class=="friends") $selected = 2;
    // какие есть?
    $links = array("href", $tpl->GetValue("Href:Account"),
                            $tpl->GetValue("Href:Account")."/friends",
                            $tpl->GetValue("Href:Account")."/profile",
                            );
    $texts = array("linktext", 
                            $tpl->message_set["Journal"],
                            $tpl->message_set["Members2"],
                            $tpl->message_set["Profile"],
                            );
  }
  // –абоча€ группа
  if ($rh->account->data["account_type"]== ACCOUNT_WORKGROUP)
  { // кака€ выбрана?
    if (($rh->object->class=="account") &&
        (($rh->object->method == "settings") || ($rh->object->method == "profile")))
        $selected = 5;
    if ($rh->object->class=="friends") $selected = 4;
    if ($rh->object->method=="journalindex") $selected = 2;
    if ($rh->object->method=="changes") $selected = 3;
    // какие есть?
    $links = array("href", $tpl->GetValue("Href:Account"),
                            $tpl->GetValue("Href:Account")."/journalindex",
                            $tpl->GetValue("Href:Account")."/changes",
                            $tpl->GetValue("Href:Account")."/friends",
                            $tpl->GetValue("Href:Account")."/profile",
                            );
    $texts = array("linktext", 
                            $tpl->message_set["Journal"],
                            $tpl->message_set["Documents"],
                            $tpl->message_set["Changes"],
                            $tpl->message_set["Members2"],
                            $tpl->message_set["Profile"],
                            );
  }
  // ---------- панели ссылок, ссылок панели ------------------------------

  for ($i=1; $i<sizeof($links)+1; $i++)
  {
    if ($links[$i] == "/".$rh->base_url.$rh->url)
      $tpl->Assign("JournalHead.Panel.Link", $texts[$i]);
    else
      $tpl->Assign("JournalHead.Panel.Link", "<a href=\"".$links[$i]."\">".$texts[$i]."</a>");

    if ($rh->account->data["account_type"] != ACCOUNT_COMMUNITY)
      $tpl->Assign("JournalHead.Panel.Width", ($i<3)?"33%":"100%" );
    else
    $tpl->Assign("JournalHead.Panel.Width", ($i<3)?"50%":"100%" );

    $tpl->Parse("journal.head.html:Panel".
    (($links[$i] == "/".$rh->base_url.$rh->url)
     ?"_Here":(($i==$selected)?"_Current":""))
    , "JournalHead.Panel".$i );
  }

  $bonus = "User";
  if ($rh->account->data["account_type"]== ACCOUNT_WORKGROUP) $bonus = "WorkGroup";
  if ($rh->account->data["account_type"]== ACCOUNT_COMMUNITY) $bonus = "Community";

  if ($rh->object->class=="node")
   echo $tpl->Parse("journal.head.html:Node");
  else
   echo $tpl->Parse("journal.head.html:".$bonus);

?>