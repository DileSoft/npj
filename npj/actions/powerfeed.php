<?php
/*



























                                        D E P R E C A T E D






















*/

// {{feed [type="mod|my|corr|correspondents|rank"] [minrank="5"] [groups="25,167"] [style="subjects|full"] 
//        [pagesize="20"]
// }}
//  searches for $_REQUEST["before"]

//  $debug->Trace_R( $params );
//  $debug->Trace_R( $object->data );
//  $debug->Error( $object->class );
  $rh->UseClass( "ListObject", $rh->core_dir );

  // нам не нужен враппер!
  $tpl->Assign("Action:NoWrap", 1);

  $templates = array("subjects", "full", "friends", "members");
  if (!isset($params["style"])) $params["style"] = "full";

  if ($params["type"] == "corr" || $params["type"] == "correspondents" ) $type = "corr"; else
  if ($params["type"] == "rank" ) $type = "rank"; else
  $type = "my"; 
  if (!isset($params["pagesize"])) 
   $params["pagesize"] = $rh->account->data["_".($type=="my"?"personal":"friends")."_page_size"];
  if (!isset($params["pagesize"])) 
   $params["pagesize"] = 20;

  // -. Need Moderation
  $need_moderation = 0;
  if ($params["type"] == "mod") 
  {
    $roubric = &$object; // ??? потом заменится, когда введём параметр npj-address
    if ($principal->IsGrantedTo("rank_greater", "account", $roubric->data["user_id"], GROUPS_MODERATORS))
     $need_moderation = 1;
  }

  // 0. frequent numbers
  $owner_id = $rh->account->data["user_id"];
  $user_id  = $principal->data["user_id"];
  $data = array(); // here will be records

  $friends = array();
  $psize    = $params["pagesize"];
//  $psize    = 5;
  $before = $_REQUEST["before"];
  $state->Free( "before" ); // ??? place smw else
  if ($before)
  $lasttime = $db->Quote(date("Y-m-d H:i:s", 
  //          HH                   mm                    ss                    MM                   dd                   YYYY
      mktime( substr($before,8,2), substr($before,10,2), substr($before,12,2), substr($before,4,2), substr($before,6,2), substr($before,0,4) )
              ));
  else $lasttime = $db->Quote(date("Y-m-d H:i:s"));


  if ($type == "my")
  {
     $mysql = "select r.record_id from ".$rh->db_prefix."records as r, ".$rh->db_prefix."users as u ".
                 " where r.supertag = CONCAT(u.login,".$db->Quote("@").
                 ($rh->account->data["node_id"]==$rh->node_name?
                      ",u.node_id,":
                      ",u.node_id,".$db->Quote("/".$rh->node_name).",")
                 .$db->Quote(":").
                 ") and u.user_id=".$db->Quote( $owner_id )." and r.user_id=u.user_id";
     $debug->Trace("<b>MY FEED:</b> ".$mysql);                          
     $rs = $db->Execute( $mysql );
     $friends[] = $rs->fields["record_id"];
  }
  else 
  {
    // 1. получить группы
    if ($params["groups"]) $_groups = explode( ",", $params["groups"] );  else $_groups = array();
    $gs = sizeof($_groups);
    $groups = array();
    $rs = $db->Execute("select group_id, group_rank, is_system from ".$rh->db_prefix.
                       "groups where is_system<2 and user_id=".$db->Quote($owner_id));
    $a = $rs->GetArray(); 
    foreach ($a as $item) if ($gs==0) 
      {
        if ($type=="corr")
        if (($item["group_rank"]==GROUPS_REPORTERS) && ($item["is_system"] == 1)) 
            { $groups[] = $item["group_id"]; break; }
        if ($type=="rank") 
        {
          $debug->Trace( $item["group_rank"]." ** ".$item["group_id"] );
          if (($item["group_rank"]>=$params["minrank"]) && ($item["is_system"] == 1)) 
              { $groups[] = $item["group_id"]; }
        }
      }
      else if (in_array($_groups, $item["group_id"])) $groups[]=$item["group_id"];

    // 2. получить содержимое групп
    if (sizeof($groups) == 0) $no=1;
    if (!$no)
    {
      $rs = $db->Execute("select user_id from ".$rh->db_prefix."user_groups where group_id in (".implode(",",$groups).")");
      // ??? may be use DISTINCT instead
      $a = $rs->GetArray(); $b = array();
      foreach( $a as $item ) $b[] = $item["user_id"];
//      $b = array_unique( $b ); 
      // 2a. Get root records forall
      foreach( $b as $user )
      {
       $rs = $db->Execute("select r.record_id from ".$rh->db_prefix."records as r, ".$rh->db_prefix."users as u ".
                 " where r.supertag = CONCAT(u.login,".$db->Quote("@").
                 ($rh->account->data["node_id"]==$rh->node_name?
                      ",u.node_id,":
                      ",u.node_id,".$db->Quote("/".$rh->node_name).",")
                 .$db->Quote(":").
                 ") and u.user_id=".$db->Quote( $user )." and r.user_id=u.user_id" );
       $friends[] = $rs->fields["record_id"];
      }
    }
    if (sizeof($friends) == 0) $no=1;
  }

 $debug->Trace_R( $groups );
 $debug->Trace_R( $friends );
// $debug->Error("$owner_id::: minrank = ".$params["minrank"]);
 if (!$no)
 {
  // 3. СУПЕРЗАПРОС, 3.4я версия
  $sql = "
SELECT m.record_id, m.owner_id, m.keyword_id, m.keyword_user_id, m.group1, m.group2 
FROM ".$rh->db_prefix."records_ref as m,
  ".$rh->db_prefix."groups as g,
  ".$rh->db_prefix."user_groups as ug,
  ".$rh->db_prefix."groups as gc,
  ".$rh->db_prefix."user_groups as ugc
WHERE
 ( 
  (m.keyword_id in (".implode(",",$friends)."))
 )
AND 
 ( 
   (m.group1=0 AND g.group_id=gc.group_id AND g.group_rank=".GROUPS_SELF.") OR
   (m.group2=-1 AND m.keyword_user_id=".$user_id." AND g.group_id=gc.group_id AND g.group_rank=".GROUPS_SELF.") OR
   ( 
     (m.group1 = g.group_id OR m.group2 = g.group_id OR
      m.group3 = g.group_id OR m.group4 = g.group_id
     ) 
       AND g.group_rank < ".GROUPS_SELF."
       AND
        ( 
         (".$user_id." = ug.user_id AND ugc.user_id = ".$user_id." AND gc.group_rank=".GROUPS_SELF.") 
          OR ( m.keyword_user_id = gc.user_id AND ug.user_id = gc.user_id 
               AND ugc.user_id = ".$user_id."  AND (gc.group_type=3) AND gc.group_rank>=".GROUPS_LIGHTMEMBERS." )
        )
   ) 
 )

AND g.user_id = m.owner_id
AND g.group_id = ug.group_id
AND gc.group_id = ugc.group_id

AND m.server_datetime < ".$lasttime."
AND m.syndicate = 1
AND m.need_moderation = ".$need_moderation."

ORDER BY server_datetime DESC, record_id DESC";
  // patches: kuso nchanged "m.priority=0" from "m.priority>0" due to refactoring of priority AND THEN REMOVED priority at all
  //          kuso added   "m.syndicate=1" due to refactoring of priority
  //          kuso added   "m.need_moderation=xxx" for moderation support
  //          kuso added   "select ... m.keyword*" to support xposted
  //          kuso added   "m.group1, m.group2" to support friends/private notification
  //          kuso added   << (m.group2=-1 AND m.keyword_user_id=".$user_id." AND g.group_id=gc.group_id 
  //                          AND g.group_rank=".GROUPS_SELF.") OR >> to fix "no-private" bug

  // bonus:     ($nodeuser = ug.user_id AND ".$user_id."=gc.user_id AND gc.group_rank=".GROUPS_SELF.") OR 
  // anus seems to be fixed:  disallow_syndicate missing.
  $debug->Trace( "<b>FEED SUPER QUERY:</b>".$sql );
  $rs = $db->SelectLimit( $sql, $psize );
  $record_ids = array(); $a = $rs->GetArray();
  $by_id = array(); $all_pub = array(); $by_pub = array();
  $security_levels = array();
  foreach( $a as $item ) 
  {
    $by_id[ $item["record_id"] ] = $item;
    $record_ids[] = $item["record_id"];
    if ($item["group1"])
     switch($item["group2"])
     { case  0: $by_id[ $item["record_id"] ]["security"]= "custom";  break;  
       case -1: $by_id[ $item["record_id"] ]["security"]= "private";  break; 
       case -2: $by_id[ $item["record_id"] ]["security"]= "friends";  break; 
     }
    else $by_id[ $item["record_id"] ]["security"] = "public";

    if ($item["owner_id"] != $item["keyword_user_id"])
    {
      //$by_id[ $item["record_id"] ]["crossposted"] = array();
      if (!is_array($by_pub[$item["keyword_user_id"]])) 
      {
        $all_pub[] = $item["keyword_user_id"];
        $by_pub[$item["keyword_user_id"]] = array();
      }
      $by_pub[$item["keyword_user_id"]][] = &$by_id[ $item["record_id"] ];
    }
  }
//  $debug->Error( $debug->Trace_R( $record_ids ) );

  if (sizeof($record_ids) == 0) $no = 1;
 }
 if (!$no)
 {
  // 3.2. получить все кросспостед
  if (sizeof($all_pub) > 0)
  {
    $sql = "select user_id, user_name, login, node_id from ".$rh->db_prefix."users where user_id in (".implode(",",$all_pub).")";
    $rs = $db->Execute($sql);
    $a = $rs->GetArray();
    foreach( $a as $item )
     if (is_array($by_pub[ $item["user_id"] ]))
      foreach($by_pub[ $item["user_id"] ] as $v=>$rec)
       if ($by_pub[ $item["user_id"] ][$v]["x_byid"][$item["user_id"]]) ;
       else
       {
         $by_pub[ $item["user_id"] ][$v]["x_byid"][$item["user_id"]] = 1;
         $by_pub[ $item["user_id"] ][$v]["crossposted"][] = $object->Link( $item["login"]."@".$item["node_id"] );
       }
    {
    }
  }
  // 3.5. Получить записи по их ids.
  $sql = "select record_id, user_id, subject, tag, supertag, body_r, formatting, pic_id, ".
         "user_datetime, created_datetime, edited_datetime,  disallow_comments, disallow_replicate, number_comments, ".
         "is_digest, is_announce, edited_user_name, edited_user_login, edited_user_node_id from ".
         $rh->db_prefix."records where record_id in (".implode(",",$record_ids).") order by created_datetime DESC";
  $rs = $db->Execute( $sql ); $data = $rs->GetArray();
  foreach( $data as $k=>$record )
  {
    $data[$k]["crossposted"] = sizeof($by_id[ $record["record_id"] ]["crossposted"])
                               ?($tpl->message_set["Crossposted"].implode(", ", $by_id[ $record["record_id"] ]["crossposted"]))
                               :"";
    $data[$k]["security"]       = $by_id[ $record["record_id"] ]["security"];
    $data[$k]["security_title"] = $tpl->message_set["Record.Stats.Sec"][$by_id[ $record["record_id"] ]["security"] ];

    if ($params["style"] != "subjects")     
      $data[$k]["body_post"] = $object->Format($record["body_r"], $record["formatting"], "post");
    $data[$k]["Link:user"] = $object->Link( $data[$k]["edited_user_login"]."@".$data[$k]["edited_user_node_id"] );
    $data[$k]["Href:tag"]  = $object->Href( $data[$k]["supertag"], NPJ_ABSOLUTE, IGNORE_STATE );
    if ($params["style"] == "subjects")     
    if (trim($data[$k]["subject"]) == "") 
      $data[$k]["subject"] = "(".substr($data[$k]["body_r"], 0,50)." ...)"; // change to "word-trim"
    $data[$k]["dt"] = strftime("%H:%M <b>%d.%m.%Y</b>", strtotime($record["created_datetime"]));
    $data[$k]["userpic"] = "<img border=\"0\" src=\"".$rh->user_pictures_dir.$record["user_id"]."_big_".
                           $record["pic_id"].".gif\" />";
    $data[$k]["userpic_small"] = "<img border=\"0\" src=\"/".$rh->user_pictures_dir.$record["user_id"]."_small_".
                           $record["pic_id"].".gif\" />";
  }
 }

  // 4. choose template
  foreach( $templates as $k=>$v )
   if (($params["style"] == $k) || ($params["style"] == $v))
    $tplt = "List_".$v; 

  // 5. parse 
  $tpl->Assign("Childs", "");
  $tpl->Assign("Before", str_replace(" ","",str_replace("-","",str_replace(":","",
                                ($data[ sizeof($data)-1 ]["created_datetime"])))) );
  $list = &new ListObject( &$rh, &$data );
  return $list->Parse( "actions/feed.html:".$tplt );

?>