<?php

  // Ó·ÌÓ‚ËÏ-Í‡ Ò˜∏Ú˜ËÍË
  $data = $object->Load(3);
  if (!is_array($data)) return $this->NotFound("AccountNotFound");

  $friends = &new NpjObject( &$rh, $object->name.":friends" );
  $friends->Handler("_count_friends", array(), &$principal );

  $this->record = &new NpjObject( &$rh, $object->name.":" );

  if ($object->params[0] == "edit") 
    return $object->Handler( "_profile_edit", array(), &$principal );
  if ($object->params[0] == "pictures") 
  {
    if ($object->params[1] == "add") 
      return $object->Handler( "_profile_pictures_add", &$params, &$principal );
    if ($object->params[1] == "remove") 
      return $object->Handler( "_profile_pictures_remove", &$params, &$principal );
    if ($object->params[1] == "default") 
      return $object->Handler( "_profile_pictures_default", &$params, &$principal );
    if ($object->params[1] == "edit") 
      return $object->Handler( "_profile_pictures_edit", &$params, &$principal );
    else
      return $object->Handler( "_profile_pictures", &$params, &$principal );
  }

  // Á‡ÔÓÎÌÂÌËÂ ÔÓÙËÎˇ
  $tpl->MergeMessageSet( $rh->message_set."_profile".$data["account_type"] );
  $profile = array();

  // - ÔÓÍ‡Á˚‚‡ÂÏ ‡ÍÍ‡ÛÌÚ ÍÎ‡ÒÒ
  if (($data["account_class"] != "") && (isset($rh->account_classes[$data["account_class"]])))
  $profile[] = array( "href" => $tpl->message_set["P.account_class"],
                      "text" => "<h2>".htmlspecialchars($rh->account_classes[$data["account_class"]]["name"])."</h2>" );

  // - ÔÓÍ‡Á˚‚‡ÂÏ "ÔÂ‰Í‡"
  if ($data["parent_id"])
  {
    $parent_data = $this->_LoadById( $data["parent_id"] );
    if ($parent_data != NOT_EXIST)
    {
      $profile[] = array( "href" => $tpl->message_set["P.parent_id"],
                          "text" => "<b>".
                                    $this->Link( $parent_data["login"]."@".$parent_data["node_id"] ).
                                    " &mdash; ".
                                    htmlspecialchars($parent_data["user_name"]).
                                    "</b><hr /><br />" );
    }
  }
              
  if ($rh->NPJ_QUASI_NODES[$data["node_id"]])
    $node_more = "/".$rh->node_name;
  else
    $node_more = "";
  
  $profile[] = array( "href" => $tpl->message_set["P.user_name"],
                      "text" => htmlspecialchars($data["user_name"]) );
  $profile[] = array( "href" => $tpl->message_set["P.npj"],
                      "text" => "<strong>".
                                $object->Link( $data["login"]."@".$data["node_id"].$node_more ).
                                "</strong>" );

  if ($data["sex"] > 0)
  $profile[] = array( "href" => $tpl->message_set["P.sex"],
                      "text" => $tpl->message_set["P.sex.Data"][$data["sex"]]) ;

  // ƒÀﬂ —ŒŒ¡Ÿ≈—“¬¿ =================================================
  if ($data["account_type"] == ACCOUNT_COMMUNITY)
  {
    $profile[] = array( "href" => $tpl->message_set["P.security_type"],
                        "text" => $tpl->message_set["P.security_type.Data"][$data["security_type"]]) ;
    $profile[] = array( "href" => $tpl->message_set["P.default_membership"],
                        "text" => $tpl->message_set["P.default_membership.Data"][$data["default_membership"]]) ;
    $profile[] = array( "href" => $tpl->message_set["P.post_membership"],
                        "text" => $tpl->message_set["P.post_membership.Data"][$data["post_membership"]]) ;
    $profile[] = array( "href" => $tpl->message_set["P.announce_membership"],
                        "text" => $tpl->message_set["P.announce_membership.Data"][$data["announce_membership"]]) ;
  }
  // ƒÀﬂ –¿¡Œ◊≈… √–”œœ€ =================================================
  if ($data["account_type"] == ACCOUNT_WORKGROUP)
  {
    $profile[] = array( "href" => $tpl->message_set["P.security_type"],
                        "text" => $tpl->message_set["P.security_type.Data"][$data["security_type"]]) ;
    $profile[] = array( "href" => $tpl->message_set["P.default_membership"],
                        "text" => $tpl->message_set["P.default_membership.Data"][$data["default_membership"]]) ;
    $profile[] = array( "href" => $tpl->message_set["P.post_membership"],
                        "text" => $tpl->message_set["P.post_membership.Data"][$data["post_membership"]]) ;
    $profile[] = array( "href" => $tpl->message_set["P.announce_membership"],
                        "text" => $tpl->message_set["P.announce_membership.Data"][$data["announce_membership"]]) ;
  }

  if ($profile[sizeof($profile)-1]["href"] != "<br />") $profile[] = array( "href" => "<br />", "text" => "&nbsp;"  );

  $profile[] = array( "href" => "",
                      "text" => "<div class=\"journal-name-\">".$data["journal_name"]."</div>".
                                 "<div class=\"journal-desc-\">".$data["journal_desc"]."</div>"  );

  if ($data["website_url"] != "")
  {
    if (strpos($data["website_url"], "://") === false)
     $data["website_url"] = "http://".$data["website_url"];
    $profile[] = array( "href" => $tpl->message_set["P.website"],
                        "text" => $object->Link($data["website_url"],"",$data["website_name"]) );
  }
  if ($data["file_url_prefix"] != "")
  {
    if (strpos($data["file_url_prefix"], "://") === false)
     $data["file_url_prefix"] = "http://".$data["file_url_prefix"];
    $profile[] = array( "href" => $tpl->message_set["P.file_url_prefix"],
                        "text" => $object->Link($data["file_url_prefix"]) );
  }

  $where = array();
  if ($data["country"]) $where[] = $data["country"];
  if ($data["region"]) $where[] = $data["region"];
  if ($data["city"]) $where[] = $data["city"];
  if (sizeof($where)) 
    $profile[] = array( "href" => $tpl->message_set["P.where"],
                        "text" => implode(", ",$where ));

  if ($profile[sizeof($profile)-1]["href"] != "<br />") $profile[] = array( "href" => "<br />", "text" => "&nbsp;"  );

  if ($data["icq_uin"] != "0")
  if ($data["icq_uin"] != "")
  $profile[] = array( "href" => $tpl->message_set["P.icq_uin"],
                      "text" => 
     "<img hspace='0' align='absmiddle' alt='".$tpl->message_set["P.icq_uin.Status"].
     "' src='http://web.icq.com/whitepages/online?icq=".htmlspecialchars($data["icq_uin"])."&amp;img=5' height='18' ".
     "width='18' /><span class='icq_uin'>".htmlspecialchars($data["icq_uin"])."</span>".
     " (<b><a href='http://wwp.icq.com/scripts/search.dll?to=".htmlspecialchars($data["icq_uin"]).
     "'>".$tpl->message_set["P.icq_uin.Add"]."</a>, <a href='http://wwp.icq.com/scripts/contact.dll?msgto=".
     htmlspecialchars($data["icq_uin"])."'>".$tpl->message_set["P.icq_uin.Send"]."</a></b>)" );

  //loading confidents
  {
    $rs = $db->Execute("select distinct u.user_id, u.login, u.node_id, u.user_name from ".$rh->db_prefix."users as u, ".$rh->db_prefix."user_groups as ug, ".
                       $rh->db_prefix."groups as g where u.user_id = ug.user_id and ".
                       "g.group_id = ug.group_id and g.group_rank = ".$db->Quote(GROUPS_FRIENDS)." and g.user_id=".
                       $db->Quote($data["user_id"]). " order by node_id, login");
    $arr_confidents = $rs->GetArray();
    $confidents = array($data["user_id"]);
    foreach( $arr_confidents as $item )
     $confidents[] = $item["user_id"];
  }
  
  // check that seeing user is this data's confident
  if ($data["email"] != "" && ($data["advanced_options"]["hide_email"]==0 || in_array($principal->data["user_id"], $confidents)))
  $profile[] = array( "href" => $tpl->message_set["P.email"],
                      "text" => $object->Link($data["email"]) );

  if ($data["bio"] != "")
  $profile[] = array( "href" => $tpl->message_set["P.bio"],
                      "text" => $tpl->Format($data["bio"], "wiki")."<br />" );

  if ($data["interests"] != "") 
  { $i = str_replace( "\n", ",", $data["interests"] );
    $interests = explode(",", $i );
    foreach( $interests as $k=>$v ) if (trim($v) != "") $interests[$k] = trim($v);
    $profile[] = array( "href" => $tpl->message_set["P.interests"],
                        "text" => implode(", ", $interests) );
  }

  if ($profile[sizeof($profile)-1]["href"] != "<br />") $profile[] = array( "href" => "<br />", "text" => "&nbsp;"  );

  //  Œ––≈—œŒÕƒ≈Õ“€ / ◊À≈Õ€ —ŒŒ¡Ÿ≈—“¬ =================================
  $rs = $db->Execute("select u.user_id from ".$rh->db_prefix."users as u, ".$rh->db_prefix."user_groups as ug, ".
                     $rh->db_prefix."groups as g where u.user_id = ug.user_id and ".
                     "g.group_id = ug.group_id and g.group_rank = ".$db->Quote(GROUPS_REPORTERS)." and g.user_id=".
                     $db->Quote($principal->data["user_id"]));
  $a = $rs->GetArray(); $myfriends = array(); 
  foreach( $a as $item ) $myfriends[$item["user_id"]] =1;
  // ƒÀﬂ œŒÀ‹«Œ¬¿“≈Àﬂ -------------------------------------------------
  if ($data["account_type"] == 0)
  {
    $rs = $db->Execute("select distinct u.user_id, u.login, u.node_id, u.user_name from ".$rh->db_prefix."users as u, ".$rh->db_prefix."user_groups as ug, ".
                       $rh->db_prefix."groups as g where u.user_id = ug.user_id and ".
                       "g.group_id = ug.group_id and g.group_rank = ".$db->Quote(GROUPS_REPORTERS)." and g.user_id=".
                       $db->Quote($data["user_id"]). " order by node_id, login");
    $a = $rs->GetArray(); if (sizeof($a)) $friends = array(); else $friends = array($tpl->message_set["P.friends.none"]);
    foreach( $a as $item )
    if ($principal->data["user_id"] != $data["user_id"])
     $friends[] = (isset($myfriends[$item["user_id"]])?"<b>":"").
            "<a title=\"".$item["user_name"]."\" href=\"".$this->Href($item["login"]."@".$item["node_id"].":profile")."\">".
            $item["login"]."@".$item["node_id"]."</a>".
            (isset($myfriends[$item["user_id"]])?"</b>":"");
    else
     $friends[] = 
            "<a title=\"".$item["user_name"]."\" href=\"".$this->Href($item["login"]."@".$item["node_id"].":profile")."\">".
            $item["login"]."@".$item["node_id"]."</a>";

    
    $profile[] = array( "href" => "<a href=\"".$this->Href( $this->npj_account.":friends" )."\">".$tpl->message_set["P.friends"]."</a>:",
                        "text" => (sizeof($a)?("<strong>".$data["number_friends"].": </strong>"):"").
                                  implode(", ", $friends ).((($principal->data["user_id"] == $data["user_id"]) ||
                                  ($principal->data["user_id"] == $data["owner_user_id"]))?
                                  "&nbsp;<small><strong>(<a href=\"".$this->Href( $this->npj_account.":friends/edit/correspondents" )."\">".$tpl->message_set["P.friends.Edit"]."</a>)</strong></small>":
                                  ""));

    if ($principal->data["user_id"] == $data["user_id"])
    {
       if (sizeof($arr_confidents)) $confidents = array(); else $confidents = array($tpl->message_set["P.friends.none"]);
       foreach( $arr_confidents as $item )
        $confidents[] = 
               "<a title=\"".$item["user_name"]."\" href=\"".$this->Href($item["login"]."@".$item["node_id"].":profile")."\">".
               $item["login"]."@".$item["node_id"]."</a>";
       $profile[] = array( "href" => $tpl->message_set["P.confidents"].":",
                           "text" => implode(", ", $confidents )."&nbsp;<small><strong>(<a href=\"".$this->Href( $this->npj_account.":friends/edit/confidents" )."\">".$tpl->message_set["P.friends.Edit"]."</a>)</strong></small>"  );
    }                                                                                                                                 

    if ($profile[sizeof($profile)-1]["href"] != "<br />") $profile[] = array( "href" => "<br />", "text" => "&nbsp;"  );
    // ‚ Í‡ÍËı ÒÓÓ·˘ÂÒÚ‚‡ı ÒÓÒÚÓ˛
    $rs = $db->Execute("select distinct u.user_id, u.login, u.node_id, u.user_name from ".
                       $rh->db_prefix."users as u, ".
                       $rh->db_prefix."profiles as p, ".
                       $rh->db_prefix."user_groups as ug, ".
                       $rh->db_prefix."groups as g where u.user_id = g.user_id and ".
                       "p.user_id = u.user_id and p.security_type < ".$db->Quote(COMMUNITY_SECRET)." and ".
                       "g.group_rank < ".$db->Quote(GROUPS_SELF)." and account_type > 0 and ".
                       "g.group_id = ug.group_id and g.group_rank >= ".$db->Quote(GROUPS_LIGHTMEMBERS)." and ug.user_id=".
                       $db->Quote($data["user_id"]). " order by node_id, login");
    $a = $rs->GetArray(); if (sizeof($a)) $memberof = array(); else $memberof = array($tpl->message_set["P.friends.none"]);
    foreach( $a as $item )
     $memberof[] = (isset($myfriends[$item["user_id"]])?"<b>":"").
            "<a title=\"".$item["user_name"]."\" href=\"".$this->Href($item["login"]."@".$item["node_id"].":profile")."\">".
            $item["login"]."@".$item["node_id"]."</a>".
            (isset($myfriends[$item["user_id"]])?"</b>":"");
    if (sizeof($memberof))
    $profile[] = array( "href" => $tpl->message_set["P.memberof"].":",
                        "text" => implode(", ", $memberof )  );
  
  }
  // ƒÀﬂ —ŒŒ¡Ÿ≈—“¬¿ -------------------------------------------------
  if ($data["account_type"] == 1)
  {
    $rs = $db->Execute("select distinct u.user_id, u.login, u.node_id, u.user_name from ".$rh->db_prefix."users as u, ".$rh->db_prefix."user_groups as ug, ".
                       $rh->db_prefix."groups as g where u.user_id = ug.user_id and ".
                       "g.group_rank < ".$db->Quote(GROUPS_SELF)." and ".
                       "g.group_id = ug.group_id and g.group_rank >= ".$db->Quote(GROUPS_LIGHTMEMBERS)." and g.user_id=".
                       $db->Quote($data["user_id"]). " order by node_id, login");
    $a = $rs->GetArray(); if (sizeof($a)) $friends = array(); else $friends = array($tpl->message_set["P.friends.none"]);
    foreach( $a as $item )
     $friends[] = (isset($myfriends[$item["user_id"]])?"<b>":"").
            "<a title=\"".$item["user_name"]."\" href=\"".$this->Href($item["login"]."@".$item["node_id"].":profile")."\">".
            $item["login"]."@".$item["node_id"]."</a>".
            (isset($myfriends[$item["user_id"]])?"</b>":"");
    $profile[] = array( "href" => "<a href=\"".$this->Href( $this->npj_account.":friends" )."\">".$tpl->message_set["P.members"]."</a>:",
                        "text" => implode(", ", $friends )  );

    {
       $rs = $db->Execute("select distinct u.user_id, u.login, u.node_id, u.user_name from ".$rh->db_prefix."users as u, ".$rh->db_prefix."user_groups as ug, ".
                          $rh->db_prefix."groups as g where u.user_id = ug.user_id and ".
                          "g.group_rank < ".$db->Quote(GROUPS_SELF)." and ".
                          "g.group_id = ug.group_id and g.group_rank >= ".$db->Quote(GROUPS_MODERATORS)." and g.user_id=".
                          $db->Quote($data["user_id"]). " order by node_id, login");
       $a = $rs->GetArray(); if (sizeof($a)) $confidents = array(); else $confidents = array($tpl->message_set["P.friends.none"]);
       foreach( $a as $item )
        $confidents[] = (isset($myfriends[$item["user_id"]])?"<b>":"").
               "<a title=\"".$item["user_name"]."\" href=\"".$this->Href($item["login"]."@".$item["node_id"].":profile")."\">".
               $item["login"]."@".$item["node_id"]."</a>".
               (isset($myfriends[$item["user_id"]])?"</b>":"");
       $profile[] = array( "href" => $tpl->message_set["P.moderators"].":",
                           "text" => implode(", ", $confidents )  );
    }
  }
  // ƒÀﬂ Œ¡Œ»’ -------------------------------------------------
  {
    if ($profile[sizeof($profile)-1]["href"] != "<br />") $profile[] = array( "href" => "<br />", "text" => "&nbsp;"  );
    $rs = $db->Execute("select distinct u.user_id, u.login, u.node_id, u.user_name from ".$rh->db_prefix."users as u, ".$rh->db_prefix."user_groups as ug, ".
                       $rh->db_prefix."groups as g where u.user_id = g.user_id and ".
                       "g.group_id = ug.group_id and g.group_rank = ".$db->Quote(GROUPS_REPORTERS)." and ug.user_id=".
                       $db->Quote($data["user_id"]). " order by node_id, login");
    $a = $rs->GetArray(); if (sizeof($a)) $friendof = array(); else $friendof = array($tpl->message_set["P.friends.none"]);
    foreach( $a as $item )
     $friendof[] = (isset($myfriends[$item["user_id"]])?"<b>":"").
            "<a title=\"".$item["user_name"]."\" href=\"".$this->Href($item["login"]."@".$item["node_id"].":profile")."\">".
            $item["login"]."@".$item["node_id"]."</a>".
            (isset($myfriends[$item["user_id"]])?"</b>":"");
    $profile[] = array( "href" => $tpl->message_set["P.friendof"].":",
                        "text" => (sizeof($a)?("<strong>".$data["number_friendof"].": </strong>"):"").
                                  implode(", ", $friendof )  );
  }


  if ($profile[sizeof($profile)-1]["href"] != "<br />") $profile[] = array( "href" => "<br />", "text" => "&nbsp;"  );

  $profile[] = array( "href" => $tpl->message_set["P.creation_date"],
                      "text" => strftime("%d.%m.%Y", strtotime($data["creation_date"])) );
  $profile[] = array( "href" => $tpl->message_set["P.total_posted"],
                      "text" => $data["total_posted"] );
  $profile[] = array( "href" => $tpl->message_set["P.last_updated"],
                      "text" => strftime("%H:%M %d.%m.%Y", strtotime($data["last_updated"])));
   
  if ($object->HasAccess(&$principal, "acl_text", $rh->node_admins) ||
      ($principal->data["user_id"] == $data["user_id"]) ||
      ($principal->data["user_id"] == $data["owner_user_id"]))
  {
    if ($profile[sizeof($profile)-1]["href"] != "<br />") $profile[] = array( "href" => "<br />", "text" => "&nbsp;"  );

    $profile[] = array( "href" => $tpl->message_set["P.alive"],
                        "text" => $tpl->message_set["P.alive.Data"][$data["alive"]] );

    if ($profile[sizeof($profile)-1]["href"] != "<br />") $profile[] = array( "href" => "<br />", "text" => "&nbsp;"  );

    if ($data["account_type"] == 0)
    {
      $profile[] = array( "href" => $tpl->message_set["P.last_login_dt"],
                          "text" => strftime("%H:%M %d.%m.%Y", strtotime($data["last_login_datetime"])) );
      /* deprecated because of weak reality basis
      $profile[] = array( "href" => $tpl->message_set["P.last_logout_dt"],
                          "text" => strftime("%H:%M %d.%m.%Y", strtotime($data["last_logout_datetime"])) );
      */
      $profile[] = array( "href" => $tpl->message_set["P.theme"],
                          "text" => $data["theme"] );
      $profile[] = array( "href" => $tpl->message_set["P.skin"],
                          "text" => $data["_skin"] );
      $profile[] = array( "href" => $tpl->message_set["P.lang"],
                          "text" => $data["lang"] );
    }
  }


//  $debug->Error( $debug->Trace_R( $data ));
  // ƒËÁ‡ÈÌ ÔÓÙËÎˇ
  $tpl->Assign("Preparsed:TITLE", $tpl->message_set["Profile". $data["account_type"]]);
  $list = &new ListSimple( &$rh, &$profile );
  $result = $list->Parse( "profile.html:List" );

  if (($principal->data["user_id"] == $data["user_id"]) ||
      ($principal->data["user_id"] == $data["owner_user_id"]))
    $tpl->Parse("profile.html:Edit", "Preparsed:CONTENT" );
  else
    $tpl->Assign("Preparsed:CONTENT", "" );
  $tpl->Append("Preparsed:CONTENT", $result );

?>