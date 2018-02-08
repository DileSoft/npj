<?php

// Usage Stats
/*
   {{UsageStats
      [show="handlers,actions,objects,principals"]
      [users="kuso kukutz" or not_users="kuso"]
      [npj="kuso@pokemono:" or not_npj="kuso@pokemono:"]
      [class="record account" or not_class="account"]
      [stage=1 or stage="1-20" or stage="-20" or stage="2-"]
      [limit=20 / max=20]
   }}
*/
  // -1. default show
  if ($params["show"] == "") $params["show"] = "handlers,actions,objects,principals";
  if (!isset($params["max"])) $params["max"] = $params["limit"];
  if (!isset($params["max"])) $params["max"] = 20;

  // 0. проверить права
  if ($params["public"])
  {
    $params = array( "show" => "handlers, actions, principals", "max"=>20, "wrapper"=>$params["wrapper"],
                     "action_as_handler" => $params["action_as_handler"]  );
  }
  else
   if (!$this->HasAccess( &$principal, "acl_text", $rh->node_admins ))
   {
     $params["forbidden"] = 1;
     return $this->Action( "_404", &$params, &$principal );
   }

  // 1. превратить параметры в sql-настройки
  $types = array("users", "npj", "class");
  $typenames = array("principal_user_id", "object_address", "object_class");
  $where = "";
  foreach($types as $type)
  {
    if ($params["not_".$type]) 
    { $params[$type] = $params["not_".$type]; $invert[$type] = 1; }
    $params[$type] = explode(" ", $params[$type]);
    foreach($params[$type] as $k=>$v)
     if ($v != "")
      $params["_".$type][] = $db->Quote($v);
    if (is_array($params["_".$type]) && (sizeof($params["_".$type]) > 0))
     $params["__".$type] = "(".implode(",",$params["_".$type]).")";
    else
     $params["__".$type] = "";
  }
  // + convert user_logins to user_ids
  if ($params["__users"])
  {
    $users = array();
    foreach($params["_users"] as $user)
    {
      $u = explode($user,"@");
      if ($u[1] == "") $u[1] = $rh->node_name;
      $users[] = implode("@",$u);
    }
    foreach($users as $k=>$v)
     $users[k] = $db->Quote($v);
    $rs = $db->Execute("select user_id from ".$rh->db_prefix."users where CONCAT(login,".$db->Quote("@").
                       ",node_id) IN (".implode(",",$users).")");
    $a = $rs->GetArray();
    if (sizeof($a) == 0) $params["__users"] = "";
    else
    {
      $users = array();
      foreach($a as $item) $users[] = $item["user_id"];
      $params["__users"] = "(".implode(",",$users).")";
    }
  }

  // 2. пон€ть, за какие этапы собираем
  if (!isset($params["stage"])) $params["stage"] = "0-"; // from zeropoint up to end
  if ($params["stage"] == "-")  $params["stage"] = "0-";
  if ($params["stage"] == "0")  $params["stage"] = "0-";
  if (strpos($params["stage"], "-") === false) $params["stage"] = ($params["stage"]-1)."-".$params["stage"];
  $stages = explode("-",$params["stage"]);
  $rs = $db->SelectLimit("select already_processed from ".$rh->db_prefix."usage_stats order by already_processed desc",1);
  $a = $rs->GetArray(); if (sizeof($a) == 0) $zeropoint = 0; else $zeropoint = $a[0]["already_processed"];

  if ($stages[0] === "") $stages[0] = $zeropoint;
  else                   $stages[0] = $zeropoint - $stages[0];
  if ($stages[1] === "") $stages[1] = -1;
  else                   $stages[0] = $zeropoint - $stages[1];

  // 3. разобрать show на конкретные элементы
  $show = explode(",",$params["show"]);
  foreach($show as $k=>$v)
   $show[$k] = strtolower(trim($v));

  // 4. в цикле пройтись по всем конкретным элементам статов
  $tpl->MergeMessageSet( $rh->message_set."_action_usagestats" );
  if (sizeof($show) == 1)
  {
    $tpl->Assign("Action:TITLE", $tpl->message_set["UsageStats.".$show[0]] );
    $notitle=1;
  }
  //    [show="handlers,actions,objects,principals"]
  $what = array(
    "handlers"    => "object_method as name, count(object_method) as value",
    "actions"     => "object_method as name, count(object_method) as value",
    "objects"     => "object_address as name, count(object_address) as value",
    "principals"  => "principal_user_id as name, count(principal_user_id) as value",
                  );
  $where = array(
    "handlers"    => "event = 'handler' ",
    "actions"     => "event = 'action' ",
    "objects"     => "event != 'nothing' ",
    "principals"  => "event = 'handler' ",
                  );
  $group = array(
    "handlers"    => "object_method",
    "actions"     => "object_method",
    "objects"     => "object_address",
    "principals"  => "principal_user_id",
                  );
  foreach ($show as $query)
   if (!isset($what[$query]))
   {
     $tpl->Assign("query");
     $result[] = $tpl->Parse("actions/usagestats.html:NotRecognized");
   }
   else
   {
    // #1. compose SQL
    $sql = "select ".$what[$query]." from ".$rh->db_prefix."usage_stats where ".$where[$query].
           " and (already_processed <= ".$db->Quote($stages[0]).") ".
           " and (already_processed >  ".$db->Quote($stages[1]).") ";
    foreach($types as $type)
     if ($params["__".$type])
      $sql.=" and (".$typenames[$type]." ".($invert[$type]?"not":"")."in ". $params["__".$type].") ";
    $sql.=" group by ".$group[$query];
    $sql.=" order by value desc";
    $debug->Trace($sql);
    // #2. launch SQL
    $limit = $params["max"]?($params["max"]):10;
    $rs = $db->SelectLimit($sql, $limit);
    $data = $rs->GetArray();

    // #3. beautify users
    if (($query == "principals") && (sizeof($data) > 0))
    {
      $users = array(); $up = array();
      foreach($data as $k=>$v) { $users[] = $v["name"]; $up[$v["name"]] = $k; }
      $rs = $db->Execute("select user_id, login, node_id from ".$rh->db_prefix."users where user_id in (".
                         implode(",",$users).")");
      $a = $rs->GetArray();
      foreach($a as $k=>$v) $data[$up[$v["user_id"]]]["name"] = $object->Link($v["login"]."@".$v["node_id"]);
    }
    // #3bis. beautify objects
    if (($query == "objects") && (sizeof($data) > 0))
     foreach($data as $k=>$v) $data[$k]["name"] = $object->Link($v["name"]);
    // #3bis2. beautify actions
    if (($query == "actions") && (sizeof($data) > 0))
     foreach($data as $k=>$v) $data[$k]["name"] = $object->Link("node@npj:ƒокументаци€ѕользовател€/Actions#".strtolower($v["name"]),"",$v["name"]);

    // #4pre. stylize even rows
    foreach($data as $k=>$v) $data[$k]["even"] = $k%2;
    // #4. output
    if (!$notitle) $tpl->Assign("title", $tpl->message_set["UsageStats.".$query] );
    $rh->UseClass( "ListObject", $rh->core_dir );
    $list = &new ListObject(&$rh, &$data);
    $result[] = $list->Parse("actions/usagestats.html:List");
   }

  // 5. overrall output
  $sep = $tpl->Parse("actions/usagestats.html:Separator");
  return implode($sep, $result);
?>