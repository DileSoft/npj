<?php

//
// {{feed [type="mod|my|corr|correspondents|rank"] [minrank="5"] [groups="25,167"]
//        [mode=, style= -- по общей спецификации]
//        [pagesize="20"]                 -- никому не говорите, что он есть!
//        [for="kuso@npj:Аспирантура"]
//        [for_or="kuso@npj: something@npj:"]
//        [filter="announce|events|documents"] or [param0="announce"]
//        [dtfrom="2003-09-15"]
//        [dtto  ="2003-09-23"]
//        [dt    ="today|yesterday|week|tomorrow"  param0="today/yesterday/tomorrow"]
//        [order ="server|user"]
// }}
//  searches for $_REQUEST["before"], $_REQUEST["after"]

//  $debug->Trace_R( $params );
//  $debug->Trace_R( $object->data );
//  $debug->Error( $object->class );
  $rh->UseClass( "ListObject", $rh->core_dir );

  // нам не нужен враппер!
  $tpl->Assign("Action:NoWrap", 1);

  if ($params["page_size"]) $params["pagesize"] = $params["page_size"];

  // для дайджеста
  $is_digest = (strtolower($params["mode"]) == "digest");
  // для календаря
  $is_calendar = (strtolower($params["mode"]) == "calendar");

  // флаг анонса ставим тут
  $filter_announce="";

  if ($params[0] == "announce")
  { array_shift( $params );
    $filter_announce=">0";
    if ($params[0] == "events")    { $filter_announce="=1"; array_shift( $params ); }
    if ($params[0] == "documents") { $filter_announce="=2"; array_shift( $params ); }
  }
  // навешиваем фильтры по разновидностям постов
  if ($params["filter"] == "announce")  $filter_announce=">0";
  if ($params["filter"] == "events")    $filter_announce="=1";
  if ($params["filter"] == "documents") $filter_announce="=2";
  if ($filter_announce) $filter_announce = " and m.announce".$filter_announce." ";

  // если за сегодня/завтра
  if ($params[0] == "today")     { $params["dt"] = "today";     array_shift( $params ); }
  if ($params[0] == "yesterday") { $params["dt"] = "yesterday"; array_shift( $params ); }
  if ($params[0] == "week")      { $params["dt"] = "week";      array_shift( $params ); }
  if ($params[0] == "tomorrow")  { $params["dt"] = "tomorrow";  array_shift( $params ); }


  // выбираем тип ленты -- моя или корреспондентская
  if ($params["type"] == "corr" || $params["type"] == "correspondents" ) $type = "corr"; else
  if ($params["type"] == "rank" ) $type = "rank"; else
  $type = "my";

  // лента для рубрики
  if ($params["for"])
  {
    $supertag = $this->_UnwrapNpjAddress( $params["for"] );
    $supertag = $this->RipMethods( $supertag, RIP_STRONG );     // свести до ближайшей записи

    $roubric  = &new NpjObject( &$rh, $supertag );
    $roubric_data = &$roubric->Load(1);
    if (!is_array($roubric_data))
    {
      $roubric = &$object;
      unset($params["for"]);
    } else $type = "for";
  } else
  {
    if (($type == "my") && ($object->data["tag"] != "")) $type="for";
    $roubric = &$object;
  }

  // если лента преднамеренная, а не ранговая/корреспондентская
  // а также если это my-лента журнала человека
  // то сортировать её надо по user_datetime
  $_datetime = "server_datetime";
  if ($type["for"] || (($type=="my") && ($rh->account->data["account_type"] == ACCOUNT_USER)))
    $_datetime = "user_datetime";
  if ($params["order"]=="server") $_datetime = "server_datetime";
  if ($params["order"]=="user")   $_datetime = "user_datetime";

  // выставляем размер страницы
  if (!isset($params["pagesize"]))
   $params["pagesize"] = $rh->account->data["_".($type=="my"?"personal":"friends")."_page_size"];
  if (!isset($params["pagesize"]))
   $params["pagesize"] = 20;

  // -. Need Moderation
  $need_moderation = 0;
  if ($params["type"] == "mod")
  {
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
  $after = $_REQUEST["after"];
  $state->Free( "before" ); // ??? place smw else
  $state->Free( "after" ); // ??? place smw else
  $mode = "Before"; $rmode = "After";
  $rvalue = $before;
  if ($before)
  {
    $lasttime = $db->Quote(date("Y-m-d H:i:s",
  //          HH                   mm                    ss                    MM                   dd                   YYYY
      mktime( substr($before,8,2), substr($before,10,2), substr($before,12,2), substr($before,4,2), substr($before,6,2), substr($before,0,4) )
              ));
  }
  else $lasttime = $db->Quote($_datetime=="user_datetime"?date("2037-01-01 23:59:59"):date("Y-m-d H:i:s"));
  if ($after)
  {
    // reverse mode
    $mode = "After"; $rmode = "Before";
    $rvalue = $after;
    $rlasttime = $db->Quote(date("Y-m-d H:i:s",
  //          HH                   mm                    ss                    MM                   dd                   YYYY
      mktime( substr($after,8,2), substr($after,10,2), substr($after,12,2), substr($after,4,2), substr($after,6,2), substr($after,0,4) )
              ));
  }
  // формирование ленты за период
  $filter_dt = " AND m.$_datetime < ".$lasttime;
  if ($rlasttime) $filter_dt = " AND m.$_datetime > ".$rlasttime;
  if ($params["dt"] == "today")
  {
    $params["dtfrom"] = date( "Y-m-d 00:00:00" );
    $params["dtto"]   = date( "Y-m-d 23:59:59" );
  }

  if ($params["dt"] == "tomorrow")
  {
    $tomorrow = mktime (0,0,0, date("m"), date("d")+1, date("Y"));
    $params["dtfrom"] = date( "Y-m-d 00:00:00", $tomorrow);
    $params["dtto"]   = date( "Y-m-d 23:59:59", $tomorrow);
  }

  if ($params["dt"] == "week")
  {
    $_day = date("w")-1; $_dm = date("j");
    $params["dtfrom"] = date( "Y-m-d 00:00:00", mktime(0,0,0,date("m"),$_dm-$_day,date("Y")) );
    $params["dtto"]   = date( "Y-m-d 23:59:59", mktime(0,0,0,date("m"),$_dm-$_day+6,date("Y"))  );
  }

  if ($params["dt"] == "yesterday")
  {
    $yesterday = mktime (0,0,0, date("m"), date("d")-1, date("Y"));
    $params["dtfrom"] = date( "Y-m-d 00:00:00", $yesterday );
    $params["dtto"]   = date( "Y-m-d 23:59:59", $yesterday );
  }
  if ($params["dtto"])
    $filter_dt = " AND m.$_datetime <= ".$db->Quote($params["dtto"]);
  if ($params["dtfrom"])
    $filter_dt .= " AND m.$_datetime >= ".$db->Quote($params["dtfrom"]);

  //$debug->Error( $_datetime." = ".$params["dtfrom"]." -> ".$params["dtto"] );

  $pagesize = $psize;

  if ($params["for_or"])
  {
    $type = "for";
    $fors = explode(" ",$params["for_or"]);
    foreach($fors as $k=>$v)
    {
      $supertag = $this->_UnwrapNpjAddress( $v );
      $supertag = $this->RipMethods( $supertag, RIP_STRONG );     // свести до ближайшей записи
      $_roubric      = &new NpjObject( &$rh, $supertag );
      $_roubric_data = &$_roubric->Load(1);
      if (is_array($_roubric_data) && $_roubric->data["record_id"] > 0)
        $friends[] = $_roubric->data["record_id"];
    }
  }

  if ($type == "my")
  {
     $mysql = "select r.record_id from ".$rh->db_prefix."records as r, ".$rh->db_prefix."users as u ".
                 " where r.supertag = CONCAT(u.login,".$db->Quote("@").
                 ($rh->account->data["node_id"]==$rh->node_name?
                      ",u.node_id,":
                      ",u.node_id,".$db->Quote("/".$rh->node_name).",")
                 .$db->Quote(":").
                 ") ".
                 "and u.user_id=".$db->Quote( $owner_id )." and r.user_id=u.user_id";
     $debug->Trace("<b>MY FEED:</b> ".$mysql);
     $rs = $db->Execute( $mysql );
     $friends[] = $rs->fields["record_id"];
  }
  else
  if ($type == "for")
  {
    $friends[] = $roubric->data["record_id"];
  }
  else
  {
    // 1. получить группы
    if ($params["groups"]) $_groups = explode( ",", $params["groups"] );  else $_groups = array();
    $gs = sizeof($_groups);
    $groups = array();
    $rs = $db->Execute("select group_id, group_rank, is_default, is_system from ".$rh->db_prefix.
                       "groups where is_system<2 and user_id=".$db->Quote($owner_id));
    $a = $rs->GetArray();
    // берём дефолт или проверяем наличие группов
    foreach ($a as $item)
    if (($gs==0) && $item["is_default"])            $groups[]=$item["group_id"];
    else if (in_array($item["group_id"], $_groups)) $groups[]=$item["group_id"];

    // если всё ещё пусто, то берём "всех по максимуму"
    $gs = sizeof($groups);
    if ($gs == 0)
    foreach ($a as $item)
    {
      if ($type=="corr")
      if (($item["group_rank"]==GROUPS_REPORTERS) && ($item["is_system"] == 1))
          { $groups[] = $item["group_id"]; break; }
      if ($type=="rank")
      {
//r        $debug->Trace( $item["group_rank"]." ** ".$item["group_id"] );
        if (($item["group_rank"]>=$params["minrank"]) && ($item["is_system"] == 1))
            { $groups[] = $item["group_id"]; }
      }
    }

    // 2. получить содержимое групп
    if (sizeof($groups) == 0) $no=1;
    if (!$no)
    {
      $rs = $db->Execute("select distinct root_record_id from ".$rh->db_prefix."user_groups as ug, ".$rh->db_prefix."users as u ".
                         "where ug.user_id=u.user_id and ug.group_id in (".implode(", ",$groups).")");
      $a = $rs->GetArray();
      foreach( $a as $item ) $friends[] = $item["root_record_id"];
    }
    if (sizeof($friends) == 0) $no=1;
  }

//r $debug->Trace_R( $groups );
//r $debug->Trace_R( $friends );
// $debug->Error("$owner_id::: minrank = ".$params["minrank"]);
 if (!$no)
 {
  // pre3. перед СуперЗапросом получаем сообщества, в которых состоит принципал
  $m1 = $debug->_getmicrotime(); // ???(DBG)
  $rs = $db->Execute( "select distinct g.user_id from ".$rh->db_prefix."user_groups as ug, ".
                      $rh->db_prefix."groups as g where g.group_id = ug.group_id and ".
                      " g.group_type > 2 and g.group_rank >=".GROUPS_LIGHTMEMBERS.
                      " and ug.user_id = ". $db->Quote($principal->data["user_id"]) );
  $a = $rs->GetArray(); $g = array();
  foreach( $a as $v ) $g[] = $v["user_id"];

  $debug->Trace("principal is somewhat member in accounts: ". implode(", ", $g));

  if (sizeof($g) > 0)
   $sql_add = "OR (g.user_id <> ug.user_id AND ".
                  "(".
                     "(ug.user_id = m.keyword_user_id) OR ". // в фиде мы запросили какой-то из фасетов этого сообщества
                     "((m.group2  = ".$db->Quote(ACCESS_GROUP_COMMUNITIES).") AND (m.group3=ug.user_id))".
                   ")".
                  " AND ug.user_id IN (". implode(", ",$g) .") )";
  else
   $sql_add = "";

//  if ($debug->kuso)
//    $debug->Error($sql_add);

  $m2 = $debug->_getmicrotime(); // ???(DBG)

  // 3. СУПЕРЗАПРОС, 3.41я версия
//       m.announce,
//       m.keyword_id, m.keyword_user_id,
  $sql = "
SELECT
       m.record_id, m.owner_id, m.$_datetime as datetime,
       m.group1, m.group2
FROM
  ".$rh->db_prefix."records_ref as m,
  ".$rh->db_prefix."groups as g,
  ".$rh->db_prefix."user_groups as ug
WHERE
 (
   m.keyword_id in (".implode(",",$friends).")
 )
AND
 (
   (m.group1 = 0 AND g.group_rank=".GROUPS_SELF.") OR ". // this is public
   "
   (g.group_rank=".GROUPS_SELF." and g.user_id = ".$user_id.") OR ". // this is MINE PRIVATE. should be private for AUTHORS OWNER
   "
   (
     ((m.group1 = g.group_id OR m.group2 = g.group_id OR
       m.group3 = g.group_id OR m.group4 = g.group_id)
     )
       AND g.group_rank > 0
       AND g.group_rank < ".GROUPS_SELF."
       AND
       (
         ug.user_id = ".$user_id."
         ".$sql_add."
       )
   )
 )
".
($type!="my"?"AND m.syndicate = 1":"AND m.syndicate > -1").
"
AND m.need_moderation = ".$need_moderation."

AND g.user_id = m.owner_id
AND g.group_id = ug.group_id
".
$filter_dt.
$filter_announce."

ORDER BY m.$_datetime ".($rlasttime?"ASC":"DESC").", record_id DESC";
  // patches: kuso changed "m.priority=0" from "m.priority>0" due to refactoring of priority AND THEN REMOVED priority at all
  //          kuso added   "m.syndicate=1" due to refactoring of priority
  //          kuso added   "m.need_moderation=xxx" for moderation support
  //          kuso added   "select ... m.keyword*" to support xposted
  //          kuso added   "m.group1, m.group2" to support friends/private notification
  //          kuso added   << (m.group2=-1 AND m.keyword_user_id=".$user_id." AND g.group_id=gc.group_id
  //                          AND g.group_rank=".GROUPS_SELF.") OR >> to fix "no-private" bug
  //          kukutz added  ASC/DESC flipper
  //          kuso splitted out "pre3" section and removed ug + ugc
  // 14022004
  //          kuso removed that line:
  //              (m.group2='-1' AND m.keyword_user_id=".$user_id." AND g.group_rank=".GROUPS_SELF.") OR
  //          kuso replaced it by line:
  //              (g.group_rank=".GROUPS_SELF.") OR
  //          kuso removed all ref-based data from SELECT <..field list..>
  //          kuso+kukutz tested over SELECT DISTINCT and claim that inefficient, then simple doubled pagesize for record_id`s gathering =)
  //          kuso added << $type="my" >> part
  //          kuso added $_datetime
  // .. missed one near 19102004.. securities


  // bonus:     ($nodeuser = ug.user_id AND ".$user_id."=gc.user_id AND gc.group_rank=".GROUPS_SELF.") OR
  // anus seems to be fixed:  disallow_syndicate missing.

  // anus: ????   why there is priority=0, =1 in some cases. what does it meanz?

  $debug->Trace( "<b>FEED SUPER QUERY:</b>".$sql );
  if ($is_calendar) // для календаря получаем всё
    $rs = $db->Execute( $sql );
  else
    $rs = $db->SelectLimit( $sql, floor($pagesize*3.5) );
  $record_ids = array(); $a = $rs->GetArray();

//  if ($debug->kuso)
//  $debug->Error(1);

  // улёт в календарь
  if ($is_calendar)
  {
    $tpl->Assign("Preparsed:CALENDAR", &$a );
    return "";
  }
//  $debug->Trace_R( $a );

  $_rids = array();
  foreach( $a as $k=>$item )
  {
    $_rids[ $item["record_id"] ] = 1;
  }
  $_ridsize=0;
  foreach( $_rids as $k=>$v )
  {
    $_ridsize++;
    $record_ids[] = $k;
  }

  // если слишком много получилось -- идём в календарик
  if ($_ridsize > $rh->feed_dt_limit && $params["dtto"])
  {
    $dtto = strtotime( $params["dtto"] );
    $params["year"] = date("Y", $dtto);
    $params["month"] = date("m", $dtto);
    unset($params["style"]);
    return $this->Action("calendar", $params, &$principal);
  }



  // анализ, добрались ли до дна потока
  if ($_ridsize <= $pagesize) $reach_bottom = 1;
  else
  {
    $reach_bottom=0;
    //array_pop($a); // ??? возможно для after надо array_shift()
  }

  $m3 = $debug->_getmicrotime(); // ???(DBG)

  // 3.5. Получить записи по их ids.
  // ??? проверить, удовлетворяем ли спецификации по полям для (2)
  if ( sizeof($record_ids) == 0) $data = array();
  else
  {
    //  << max@jetstyle 2004-11-26 />>
    $order_issue =  $_datetime." ".($rlasttime?"ASC":"DESC");
    $uactn = &$this->rh->UtilityAction(); // actions теперь живут в отдельном классе.
    $data = $uactn->GetRecordBodies ( &$record_ids, $params, 1, false, $order_issue, $pagesize );
  }

  $account_object = &new NpjObject( &$rh, $object->npj_account );
  $account_object->Load(2);
  $needmod = (($type == "for") || ($type == "my")) && ($account_object->data["account_type"] != ACCOUNT_USER)
             && $account_object->HasAccess( &$principal, "rank_greater", GROUPS_MODERATORS);
  foreach($data as $k=>$item)
  {
     if (!$needmod) $data[$k]["Href:mod"] = "";
     else $data[$k]["Href:mod"] = $object->Href( "/manage/moderate/".$data[$k]["record_id"], NPJ_RELATIVE, STATE_IGNORE );
     $data[$k]["is_owner"] = $data[$k]["user_id"] == $principal->data["user_id"];
     $data[$k]["announce"] = $by_id[ $item["record_id"] ]["announce"];   // задаём тон анонса
     $data[$k]["datetime"] = $data[$k]["user_datetime"];                 // какую дату считаем основной

  }
 }
 if (!isset($data)) $data = array();

  // ----- завершение первой стадии -----------------------

  $m4 = $debug->_getmicrotime(); // ???(DBG)
  $ms1 = $m2-$m1;
  $ms2 = $m3-$m2;
  $ms3 = $m4-$m3;
  // $_datetime." ".
  $tpl->Assign("Milestone:Debug", $params["debug"]);
  $tpl->Assign("Milestone:Feed_Groups", sprintf("[%0.4f] ",$ms1));
  $tpl->Assign("Milestone:Feed_Super",  sprintf("[%0.4f] ",$ms2));
  $tpl->Assign("Milestone:Feed_Bodies", sprintf("[%0.4f] ",$ms3));

  if ($params["debug"] == 2) $debug->Error("Debug haltpoint");

  // преформат полей и в RSS сразу (стадия 2,3) -----------
  foreach ($data as $k=>$item)
  {
    $cache->Store( "npj",    $item["supertag"], 2, &$data[$k] );
    $cache->Store( "record", $item["record_id"], 2, &$data[$k] );
    $data[$k] = $object->_PreparseArray( &$data[$k] );

    if ($rh->rss) $rh->rss->AddEntry( &$data[$k], RSS_FEED );
  }

  // улёт в дайджест
  if ($is_digest)
  {
    $tpl->Assign("Preparsed:DIGEST", &$data );
    return "";
  }

  // вызов алгоритма вывода (стадия 4) ----
  $params["subject"] = 1; // у постов нет тагов, поэтому выводить надо всегда сабжекты, чтобы ни случилось
  if ($mode=="Before")
  {
    if (!isset($params["show_past"]))
    if (!$reach_bottom) $params["show_past"] = 1;
    else                $params["show_past"] = 0;
    if (!isset($params["show_future"])) $params["show_future"] = ($before != "");
    $deeper = str_replace(" ","",str_replace("-","",str_replace(":","",
                                ($data[ sizeof($data)-1 ][$_datetime]))));
  }
  else
  {
    if (!isset($params["show_future"]))
    if (!$reach_bottom) $params["show_future"] = 1;
    else                $params["show_future"] = 0;
    if (!isset($params["show_past"])) $params["show_past"] = 1;
    $deeper = str_replace(" ","",str_replace("-","",str_replace(":","",
                                ($data[ 0 ][$_datetime]))));
  }

  $tpl->Assign( "show_past",     $params["show_past"]     );
  $tpl->Assign( "show_future",   $params["show_future"]   );
  $tpl->Assign( "show_timeline",        $params["show_future"] || $params["show_past"] );
  $tpl->Assign( "show_past_and_future", $params["show_future"] && $params["show_past"] );

  // assigning Before/After
  $tpl->Assign( $mode,  $deeper );
  $tpl->Assign( $rmode, $rvalue );
  
  // ------------------------ для after нужно перевернуть список
  if ($mode != "Before")
    $data = array_reverse( $data );

  if (sizeof($data) == 0)
  {
    // кажется, здесь нужно придумать, что бы такого выводить, когда пусто
    // !!! -> messageset
    return "";
  }
  else
  return $object->_ActionOutput( &$data, &$params, "feed" );

?>