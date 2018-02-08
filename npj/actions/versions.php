<?php
/*
    {{versions page/for/param0="Kuso@NPJ:PageAddress"
        [style="simple|DIFF"]
        [tag="R"] 
        [author="kuso@npj"]
        [skipsame="1"]
        [limit="20"]
        [no_current="1"]
        [show="VERSIONS|announces|all"]        
        [announced="sandbox@npj progress@npj"] 
      }}
*/
//  $debug->Trace_R( $params );
//  $debug->Error( $script_name );

  $rh->UseClass( "ListObject", $rh->core_dir );

  if (!isset($params[0])) $params[0] = $this->npj_object_address;

  // 1. unwrap
  $supertag = $this->_UnwrapNpjAddress( $params[0] );
  if (strpos( $supertag, ":" ) === false) $supertag.=":";
  $record = &new NpjObject( &$rh, $supertag );
  $data = $record->Load( 2 ); // запись не надо показывать
  if (!is_array($data)) return $this->Action( "_404", &$params, &$principal );

  // 1+. проверяем, есть ли доступ
  if (($data["type"] != 2) || (!$record->HasAccess( &$principal, "acl" )))
    return $this->Action( "_404", array("forbidden"=>1), &$principal );

  // 1++. загрузим аккаунт
  if (!$params["skipsame"])
  {
    $record->account = &new NpjObject( &$rh, $record->npj_account );
    $record->account->Load(3);
    if ($record->account->data["advanced_options"]["group_versions"] > 0) 
     $params["skipsame"]=1;
  }

  $templates = array( "simple", "diff" );
  if (!isset($params["style"])) $params["style"] = "diff";
  if (!isset($params["limit"])) 
    if (!isset($params["skipsame"]))  $params["limit"] = 20;
    else                              $params["limit"] = 100;


  // = 0 ==== ПОЛУЧЕНИЕ ВЕРСИЙ И АНОНСОВ СРАЗУ ============================================================
  if ($params["show"] == "all")
  {
    // #1 получить версии
    $v_params = $params;
    $v_params["_noForm"] = 1;
    $v_params["show"] = "versions";
    $v_params["wrapper"] = "none";
    $versions = $object->Action("versions", $v_params, &$principal );
    // #2 получить анонсы
    $a_params = $params;
    $a_params["_noForm"] = 1;
    $a_params["show"] = "announces";
    $a_params["wrapper"] = "none";
    $a_params["no_current"] = 1;
    $announces = $object->Action("versions", $a_params, &$principal );

    $tpl->Assign( "Versions",  $versions );
    $tpl->Assign( "Announces", $announces );
    $tpl->Assign( "is_diff", $params["style"]=="diff" );

  }

  // = 1 ==== ПОЛУЧЕНИЕ ВЕРСИЙ ============================================================
  else if ($params["show"] != "announces")
  {
    $tpl->Assign("Action:TITLE", $tpl->message_set["Actions"]["pageversions"]);
    // 2. донаворачиваем селект
    $wheremore = "";
    if (isset($params["tag"]))   $wheremore.= "version_tag LIKE ".$db->Quote($params["tag"]."%")." AND "; 
    if (isset($params["author"])) $wheremore.= "CONCAT(edited_user_login,".$db->Quote("@").
                                                     ",edited_user_node_id) =".$db->Quote(strtolower($params["author"])).
                                                     " AND "; 
     
    // 3. получаем список версий
    $sql = "select version_id, edited_user_login, edited_user_name, edited_user_node_id, edited_datetime, version_tag, body_r, formatting from ".
           $rh->db_prefix."record_versions where ".$wheremore." record_id=".$db->Quote($data["record_id"])." order by edited_datetime desc";
    $limit = $params["limit"];
    $rs = $db->SelectLimit( $sql, $limit );
    $a = $rs->GetArray();
  
    // форматируем их
    $b = array(); $c = 0;
    array_unshift($a, $data);
    $prev = -1;
    foreach ($a as $key=>$item)
    if ($key==0 && $params["no_current"]) ; // skip current
    else
    if (!$params["skipsame"] || ($item["version_id"]==1) || ($item["edited_user_login"]."@".$item["edited_user_node_id"] != $prev))
    {
      $prev = $item["edited_user_login"]."@".$item["edited_user_node_id"];
      $b[] = array(
         "supertag"   => $record->_UnwrapNpjAddress( "!/versions/".$item["version_id"] ),
         "checked_a"   => $c==0?"checked":"",
         "checked_b"   => ($c==1 && !$params["_noForm"])?"checked":"",
         "datetime"     => $item["edited_datetime"],
         "Link:author"  => $this->Link($item["edited_user_login"]."@".$item["edited_user_node_id"], "", 
                                       $item["edited_user_name"]),
         "version_tag"  => $item["version_tag"],
         "is_record"    => !$key,
         "is_first"     => $item["version_id"]==1,
         "is_version"   => 1,
         "href" =>  $key?$record->Href( "!/versions/".$item["version_id"], NPJ_RELATIVE,STATE_IGNORE)
                        :$record->Href( $record->npj_object_address,       NPJ_ABSOLUTE,STATE_IGNORE),
        );
       $c++;
    }
    $bb = &$b;
  }
  else
  // = 2 ==== ПОЛУЧЕНИЕ АНОНСОВ ============================================================
  { 
    $tpl->Assign("Action:TITLE", $tpl->message_set["Actions"]["pageannounces"]);
    // 2. наворачиваем селект
    if ($params["announced"])
    {
      $feeds = explode(" ", $params["announced"]); $_feeds = array();
      foreach($feeds as $v)
        $_feeds[] = "r.supertag=".$db->Quote($v);
      $wheremore = " AND (".implode(" OR ", $_feeds).")";
    }
    // 3. получаем список анонсов
    $limit = $params["limit"];
    $sql = "select distinct r1.supertag, r1.tag, r1.subject, r1.record_id, ref.server_datetime, ". // параметры анонса
                 " u.user_id, u.login, u.user_name, u.node_id from ".  // параметры журнала
                            $rh->db_prefix."records as r1, ".
                            $rh->db_prefix."records as r, ".$rh->db_prefix."records_ref  as ref, ".
                            $rh->db_prefix."users as u, ".  $rh->db_prefix."records_rare as rare ".
                            " where ref.record_id = rare.record_id and r.user_id = u.user_id and ".
                                  " r1.record_id  = rare.record_id and ".
                                  " r1.type = ".RECORD_POST." and ".
                                  " r.record_id = ref.keyword_id ".$wheremore.
                                  " and rare.announced_id = ". $db->Quote($data["record_id"]).
                            " order by ref.server_datetime desc";
    $debug->Trace($sql);
    $rs = $db->SelectLimit( $sql , $limit );
    $a = $rs->GetArray();

    // 4. форматируем их
    $b = array(); $c = 0;
    array_unshift($a, $data);
    $data["edited_user_login"] = $data["login"];
    $data["edited_user_name"] = $data["user_name"];
    $data["edited_user_node_id"] = $data["node_id"];
    $prev = -1;
    $journals = array();
    foreach ($a as $key=>$item)
    if ($key==0 && $params["no_current"]) if ($params["_noForm"]) $c++; else; // skip current
    else
//    if (!$params["skipsame"] || ($item["record_id"] != $prev))
    {
      $journals[ $item["record_id"] ][] = $this->Link($item["login"]."@".$item["node_id"], "", 
                                                      $item["user_name"]);
      $prev = $item["record_id"];
      $b[] = array(
         "supertag"   => $item["supertag"],
         "checked_a"   => ($c==0)?"checked":"",
         "checked_b"   => ($c==1)?"checked":"",
         "datetime"            => $item["server_datetime"],
         "edited_datetime"     => $item["edited_datetime"],
         "is_record"    => !$key,
         "is_version"   => 0,
         "href" =>  $record->Href( $item["supertag"], NPJ_RELATIVE,STATE_IGNORE),
         "Link:announce" => $record->Link( $item["supertag"] ),
         "Link:journal"  => &$journals[ $item["record_id"] ],
         "supertag"   => $item["supertag"],
         "record_id" => $item["record_id"],
        );
       $c++;
    }
    // слепляем вместе
    foreach ($b as $k=>$v)
     if (is_array($v["Link:journal"]))
     {
       $b[$k]["is_journals"] = sizeof( $v["Link:journal"] ) >1;
       $b[$k]["Link:journal"] = implode(", ",$v["Link:journal"]);
     }
    // выбрасываем дупы
    $dupes = array(); $bb = array();
    foreach ($b as $k=>$v)
     if (!isset($dupes[$v["record_id"]]))
     { 
       $dupes[$v["record_id"]]=1;
       $bb[] = &$b[$k];
     }
  }

  // = 3 ==== ПАРСИНГ =================================================================

  $tpl->Assign("skipsame", $params["skipsame"] );
  if ($params["style"] == "diff")
  {
    $tpl->LoadDomain( array(
          "is_diff" => 1,
          "Form:Diff"    => $params["_noForm"]?""
                                              :$state->FormStart(MSS_GET, 
                                               $record->_NpjAddressToUrl($record->npj_object_address,0)."/diff"),
          "/Form:Diff"   => $params["_noForm"]?"":$state->FormEnd(),
          )   );
  }
  else
    $tpl->LoadDomain( array( "is_diff"=>1,  "Form:Diff"    => "", "/Form:Diff"   => "" ) );

  // 5. parse title & body
  if (!$params["_noForm"])
   if ($record->data["id"] != $object->data["id"])
    $tpl->Append("Action:TITLE", " ".$record->Link( rtrim($record->npj_object_address, ":/"), "", $record->data["subject"]) );

  if ($params["show"] == "all") return $tpl->Parse("actions/versions.html:Both"); 
  else
  {
    $list = &new ListObject( &$rh, &$bb );
    return $list->Parse( "actions/versions.html:List_diff" );
  }


?>