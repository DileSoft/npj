<?php
/*
    {{subscribers 
            param0=kuso@npj:todo
            [totals=1]
            [only_email=1]
            показывать можно не всем
      нереализовано пока:
      (-)   показывать и тех, кто подписан где-то в глуби дискуссии
      (-)   [recurse=0] -- на дальнее будущее
    }}
*/

  $rh->UseClass( "Arrows", $rh->core_dir );
  $rh->UseClass( "ListObject", $rh->core_dir );
  $tpl->MergeMessageSet( $rh->message_set."_action_subscribers" );

  // -1. default params
  $page_size       = 20;    // по двадцать на странице
  $page_frame_size = false; // нет фреймов

  $orders = array( "name"         => "u.user_name asc", 
                   "login"        => "u.login asc", 
                   "registration" => "u.created_datetime desc",
                 );
  if (isset($orders[$params["order"]])) $order = $orders[$params["order"]];
  else                                  $order = $orders[   "login"      ];

  // 0. unwrap 
  if ($params[0] == "") $params[0] = $this->npj_object_address;
  $supertag = $this->_UnwrapNpjAddress( $params[0] );
  $supertag = $this->RipMethods( $supertag, RIP_STRONG );     // свести до ближайшей записи

  $dataobject = &new NpjObject( &$rh, $supertag );
  $dataobject_account = &new NpjObject( &$rh, $dataobject->npj_account );
  $data = $dataobject->Load(2);
  $dataobject_account->Load(2);

  if (!is_array($data)) 
  {
    return $this->Action("_404", &$params);
  }

  $record_id = $data["record_id"];
  $user_id   = $data["user_id"];

  // ----- проверка прав -----
  $forbidden=false;
  if (!$dataobject->HasAccess( &$principal, "owner" )) 
    $forbidden=true;
  if ($dataobject_account->HasAccess( &$principal, "rank_greater", GROUPS_MODERATORS ))
    $forbidden=false;

  if ($forbidden) $params["totals"] = 1;

  // ----- работа с БД -----
  $s_modes = $tpl->message_set["Subscribers.subscription_modes"];
  $totals      = array();
  $subscribers = array();
  $user_data = array();

  // 1. get subscribers on object-record
  $sub_classes = array( $db->Quote("facet"), 
                        $db->Quote("record"),
                        $db->Quote("cluster"), );
  $sql = "select * from ".$rh->db_prefix."subscription where ".
         "object_id = ".$db->Quote($record_id)." and object_class in (".implode(",",$sub_classes).")";
  $rs  = $db->Execute( $sql );
  $a   = $rs->GetArray();
  foreach ($a as $k=>$v)
  {
    $user_data[ $v["user_id"] ]["in_subscribers"][] = $v;
    $mode = $v["object_class"]."_".$v["object_method"];
    if (!isset( $user_data[ $v["user_id"] ][$mode]))
    $totals[$mode] ++;

    $user_data[ $v["user_id"] ][$mode] = $v;
  }

  // 1a. get frienders on keyword
  if (!$params["only_email"])
  {
    $sql = "select g.user_id from ".$rh->db_prefix."user_groups as ug, ".
                                    $rh->db_prefix."groups as g, ".
                                    $rh->db_prefix."users as u ".
            " where g.group_rank=".$db->Quote( GROUPS_REPORTERS )." and g.is_system=1 and ".
            " u.user_id = g.user_id and u.account_type=".$db->Quote( ACCOUNT_USER )." and ".
            " ug.group_id=g.group_id and ug.keyword_id=".$db->Quote($record_id);
    $rs  = $db->Execute( $sql );
    $a   = $rs->GetArray();
    foreach ($a as $k=>$v)
      $user_data[ $v["user_id"] ]["in_friends"] = true;
  }
  
  // fill all fields
  foreach ($user_data as $k=>$v)
    $user_data[$k]["user_id"] = $k;

  if ((sizeof($user_data) > 0) && !$params["totals"])
  {
    // 2. get userinfo
    $users_q = array();
    foreach($user_data as $user_id=>$v)
      $users_q[] = $db->Quote($user_id);

    // 3. where/when
    $where = " u.user_id = p.user_id and u.alive=1 and u.user_id != 1".
             " and u.user_id in (".implode(",",$users_q).")";
    $table = "profiles as p, ".$rh->db_prefix."users as u ";


    // 4. init Arrows
    $arrows = &new Arrows( &$state, $where, $table, $page_size, $page_frame_size );
    $arrows->Parse( "actions/_arrows.html", "SUB-ARROWS"  );

    $sql = "select u.user_id, u.root_record_id, u.user_name, u.login, u.node_id, p.bio ".
           " from ".$rh->db_prefix.$table.
           " where ".$where.
           " order by ". $order;
    $rs = $db->SelectLimit( $sql, $arrows->GetSqlLimit(), $arrows->GetSqlOffset() );
    $a   = $rs->GetArray();

    $subscribers = $a;
    // 4. preparse in users
    foreach( $subscribers as $k=>$v )
    {
       $subscribers[$k] = $object->_PreparseAccount( &$subscribers[$k] );

       $subscribers[$k]["even"] = $k%2;
       $subscribers[$k]["in_subscribers"] = is_array($user_data[$v["user_id"]]["in_subscribers"]);
       $subscribers[$k]["in_friends"]     = 1* $user_data[$v["user_id"]]["in_friends"];

       $subscribers[$k]["modes"] = array();
       foreach($s_modes as $kk=>$vv)
        if (isset($user_data[$v["user_id"]][$kk]))
        {
          $subscribers[$k]["modes"]["mode:".$kk] = $vv;
        }
    } 

  }

  // --------------------- ПАРСИНГ -----------------------------------------
  if ($params["totals"])
  {
    // 5. parse total
    foreach( $totals as $k=>$v )
    if (isset($s_modes[$k]))
      $totals[$k] = array(
                      "mode"      => $k,
                      "mode_name" => $s_modes[$k],
                      "total"     => $v,
                         );
  
    $list = &new ListObject( &$rh, $totals );
    $result = $list->Parse( "actions/subscribers.html:Totals", "TOTALS" );
  }
  else
  {
    $tpl->Assign("only_email", 1*$params["only_email"]);
    // 6. parse users
    foreach( $subscribers as $k=>$v )
    if (sizeof($v["modes"]) > 0)
    {
      $list = &new ListSimple( &$rh, $v["modes"] );
      $subscribers[$k]["in_subscribers"] = $list->Parse("actions/subscribers.html:Modes");
    }
    $list = &new ListObject( &$rh, $subscribers );
    $result = $list->Parse( "actions/subscribers.html:List", "RESULT" );
  }

  // mod title
  if ($object->npj_object_address != $dataobject->npj_object_address)
   $tpl->Append("Action:TITLE", ": ".$object->Link($dataobject->npj_account.":".$dataobject->data["tag"]) );
  if (!$params["totals"]) $tpl->Assign("Action:NoWrap", true);

  return $result;

?>