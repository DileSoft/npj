<?php

  // Фильтр-конфигуратор багтрекера
  //
  // Кроме того, распознаёт из QS и заполняет params:
  /*
         (*) [for="project@npj:Category1 project@npj:Category1"
         (*)     [method="or|and|facet"]
             ]
         (+) [page_size=20]
         (+) [time_limit=6]
         (+) [severity=<value>]
         (+) [status=<status>]
         (+) [priority=<status>]
         (+) [reporter=kuso@npj]
         (+) [developer=kuso@npj]
         (+) [hide_solved=1] -- and [hide_*] all other states

         (-) [show_filter=1] -- сразу раскрывает фильтр

         legend:
         (#) -- строит форму
         (*) -- умеет и распознавать
         (+) -- panel умеет строить
  */
  //$debug->Trace_R( $params );
  $rh->UseClass( "ListObject", $rh->core_dir );
  $rh->UseClass( "Arrows", $rh->core_dir );

  // ===========================================================
  // Параметры по-умолчанию
  if (!$params["page_size"]) $params["page_size"] = 20;

  $qs_params = array();
  // =================================================================================
  // Распознание параметров
  // -- pagesize  --
  $qs_params[] = "page_size";
  if (1*$state->Get("page_size") > 0)
    $params["page_size"] = 1*$state->Get("page_size");
  else
    $params["page_size"] = 20;
  // -- timelimit --
  $qs_params[] = "time_limit";
  if (1*$state->Get("time_limit") > 0)
    $params["time_limit"] = 1*$state->Get("time_limit");
  // -- status --
  $qs_params[] = "status";
  if ($state->Get("status") != "")
    $params["status"] = $state->Get("status");
  // -- hide --
  foreach ($this->config["hide_state_filter"] as $v)
  {
    $qs_params[] = "hide_".$v;
    if ($state->Get("hide_".$v) != "")
      $params["hide_".$v] = $state->Get("hide_".$v);
  }
  // -- priority --
  $qs_params[] = "priority";
  if ($state->Get("priority") != "")
    $params["priority"] = $state->Get("priority");
  // -- severity --
  $qs_params[] = "severity";
  if ($state->Get("severity") != "")
    $params["severity"] = $state->Get("severity");
  // -- reporter/developer --
  $qs_params[] = "reporter";
  if ($state->Get("reporter") != "")
    $params["reporter"] = $state->Get("reporter");
  $qs_params[] = "developer";
  if ($state->Get("developer") != "")
    $params["developer"] = $state->Get("developer");
  // -- kwds --
  $qs_params[] = "for";
  if ($state->Get("for") != "")
    $params["for"] = $state->Get("for");
  $qs_params[] = "method";
  if ($state->Get("method") != "")
    $params["method"] = $state->Get("method");

  // ===========================================================
  // Данные о категории и об аккаунте
  $account = &new NpjObject( &$rh, $this->object->npj_account );
  $account->Load(2);

  $supertag = $this->object->_UnwrapNpjAddress( $params["for"] );
  $supertag = $this->object->RipMethods( $supertag, RIP_STRONG );     // свести до ближайшей записи
  $category = &new NpjObject( &$rh, $supertag );
  if ($category->Load(2) == NOT_EXIST)
  {
    $category = &new NpjObject( &$rh, $this->object->npj_account.":" );
    $category->Load(2);
  }
  if (!$params["for"]) $params["for"] = $category->npj_object_address; 

  // =================================================================================
  //  Проверка прав доступа
  if (!$category->HasAccess( $principal, "acl", "actions" ))
    return $category->Forbidden( "Trako.DeniedByActionsAcl" );

  // ===========================================================
  // Формирование дерева категорий
  $sql = "select supertag, tag from ".$rh->db_prefix."records where ".
          "is_keyword=1 and user_id = ".$db->Quote($account->data["user_id"]).
          " order by tag";
  $rs  = $db->Execute( $sql );
  $a   = $rs->GetArray();
  $keywords_all = array();
  $keywords = array();
  $params["for"] = str_replace(";", " ", $params["for"]);
  $_keywords = explode( " ", $params["for"] );
  foreach($_keywords as $k=>$v ) $_keywords[$k] = $account->_UnwrapNpjAddress($v);
  foreach($a as $k=>$v)
  {
    $keywords_all[] = $v["tag"];
    $st1 = $account->_UnwrapNpjAddress($v["tag"]);
    if (in_array( $st1, $_keywords )) $keywords[] = $v["tag"];
  }
  $params["_for"] = $keywords_all;

  // ===========================================================
  // Формирование других кустомных параметров
  // -- status --
  $status_filter = $this->config["status_filter"];
  $statuses = array( "*"=> $tpl->message_set["Trako.filter_none"] );
  foreach( $status_filter as $sf )
  {
    $s = "";
    $parts = explode( "-", $sf);
    if ($parts[0] != "*") $s .= $this->config["states"][$parts[0]]["name"];
    if (($parts[0] != "*") && ($parts[1] != "*")) $s .= "-";
    if ($parts[1] != "*") $s .= $this->config["statuses"][$parts[1]];

    $statuses[$sf]=$s;
  }
  if (!isset($statuses[$params["status"]])) $params["status"]="*";
  foreach( $statuses as $sf=>$s )
  {
    $statuses[$sf] = array(
                             "status_name"  => $s,
                             "status_value" => $sf,
                             "is_selected"  => $sf == $params["status"], 
                          );
  }
  // -- hide_* --
  $hide_filter = $this->config["hide_state_filter"];
  $hide_states = array();
  foreach($hide_filter as $v)
    $hide_states[] = array(
                             "state_name"  => $tpl->message_set["Trako.filter_hide"][$v],
                             "state_value" => "hide_".$v,
                             "is_selected" => $params["hide_".$v]?1:0,
                          );
  // -- priority --
  $prio = array( "*" => $tpl->message_set["Trako.filter_none"] );
  foreach( $tpl->message_set["Trako.priorities"] as $p=>$v )
   $prio[$p]=$v;
  if (!isset($prio[$params["priority"]])) $params["priority"]="*";
  foreach($prio as $p=>$v)
  {
    $prio[$p] = array(
                             "prio_name"  => $v,
                             "prio_value" => $p,
                             "is_selected"  => "&".$p == "&".$params["priority"], 
                     );
  }
  // -- severity --
  $severities = array( "*" => $tpl->message_set["Trako.filter_none"] );
  foreach( $tpl->message_set["Trako.severity_values"] as $k=>$v )
   foreach( $tpl->message_set["Trako.severity_values"][$k] as $kk=>$vv )
    $severities[$kk]=$tpl->message_set["Trako.severity_classes"][$k]." &mdash; ".$vv;
  if (!isset($severities[$params["severity"]])) $params["severity"]="*";
  foreach($severities as $p=>$v)
  {
    $severities[$p] = array(
                             "severity_name"  => $v,
                             "severity_value" => $p,
                             "is_selected"  => "&".$p == "&".$params["severity"], 
                     );
  }
  // -- reporter --
  $sql = "select distinct reporter_id from ".$rh->db_prefix."trako_issues ".
         " where project_id=".$db->Quote( $account->data["user_id"] );
  $rs  = $db->Execute( $sql );
  $a   = $rs->GetArray();
  $userids = array();
  foreach( $a as $k=>$v )
    $userids[] = $db->Quote($v["reporter_id"]);
  $reporters = array( "*" => array( "user_id"=>"*",
                                    "is_empty"=>1 ), );
  if (sizeof($userids))
  {
    $sql = "select login, node_id, user_id, user_name from ".$rh->db_prefix."users ".
           " where user_id in (". implode(",", $userids).") order by login, node_id ";
    $rs  = $db->Execute( $sql );
    $a   = $rs->GetArray();
    foreach($a as $k=>$v)
    {
      $v["is_empty"]    = 0;
      $v["is_selected"] = "&".$v["user_id"] == "&".$params["reporter"];
      $reporters[] = $v;
    }
  }
  // -- developer --
  $developer_data = array(  "*" => $tpl->message_set["Trako.filter_none"],
                            0   => $tpl->message_set["Trako.filter_developer_none"] );
  $sql = "select u.user_id, u.user_name, u.login, u.node_id from ".
               $rh->db_prefix."user_groups as ug, ".
               $rh->db_prefix."groups as g, ".
               $rh->db_prefix."users as u ".
         " where ug.group_id = g.group_id and u.user_id = ug.user_id".
         " and g.group_rank >= ".$db->Quote($this->config["security"]["developer"]).
         " and g.user_id = ".$db->Quote( $account->data["user_id"] ).
         " and u.account_type = ".$db->Quote( ACCOUNT_USER ).
         " order by u.login asc";
  $rs  = $this->rh->db->Execute( $sql );
  $a   = $rs->GetArray();
  foreach($a as $k=>$v)
    $developer_data[ 1*$v["user_id"] ] = $v["login"]."@".$v["node_id"];
  if (!isset($developer_data[$params["developer"]])) $params["developer"]="*";
  $developers = array();
  foreach($developer_data as $p=>$v)
  {
    $developers[$p] = array(
                             "dev_name"  => $v,
                             "dev_value" => $p,
                             "is_selected"  => "&".$p == "&".$params["developer"], 
                      );
  }
//  $debug->Error_R($developers);



  // ===========================================================
  // Парсинг формы
  // 0. spawn TE
  $TE = &$this->GenerateTemplateEngine( $this->config["template_engine"] );

  // 1. заполнение параметров
  $TE->LoadDomain( $params );
  $TE->message_set = $tpl->message_set;  
  $TE->Assign( "images", $tpl->GetValue("images"));
  $TE->Assign( "theme",  $tpl->GetValue("theme"));

  // 1+ парсинг списков разных: статус
  $list = &new ListObject( &$rh, $statuses );
  $list->tpl = &$TE;
  $list->Parse( "filter.html:Statuses", "Rendered:status" );
  // 1+ парсинг списков разных: состояния
  $list = &new ListObject( &$rh, $hide_states );
  $list->tpl = &$TE;
  $list->Parse( "filter.html:HideStates", "Rendered:hide_states" );
  // 1+ парсинг списков разных: приоритеты
  $list = &new ListObject( &$rh, $prio );
  $list->tpl = &$TE;
  $list->Parse( "filter.html:Priorities", "Rendered:priorities" );
  // 1+ парсинг списков разных: важность
  $list = &new ListObject( &$rh, $severities );
  $list->tpl = &$TE;
  $list->Parse( "filter.html:Severity", "Rendered:severities" );
  // 1+ парсинг списков разных: репортёры
  $list = &new ListObject( &$rh, $reporters );
  $list->tpl = &$TE;
  $list->Parse( "filter.html:Reporters", "Rendered:reporters" );
  // 1+ парсинг списков разных: девелоперы
  $list = &new ListObject( &$rh, $developers );
  $list->tpl = &$TE;
  $list->Parse( "filter.html:Developers", "Rendered:developers" );

  // 1++ для дерева
  $TE->Assign("for_selected",     implode(";",$keywords));
  $TE->Assign("for_all", implode(";",$keywords_all));
  // 1++ для метода
  $methods = array( "and"=>1, "or"=>1, "facet"=>1 );
  if (!$methods[$params["method"]]) $params["method"] = "or";
  foreach($methods as $method=>$v)
  {
    $TE->Assign("method_selected_".$method, $params["method"] == $method);
  }
  $TE->Assign("method", $params["method"]);

  // 2. для формы
  $_v = $state->values;
  foreach( $qs_params as $v ) $state->Free( $v );
  
  $url = $account->_NpjAddressToUrl( $account->npj_object_address.":".
                                     $this->config["subspace"]."/filter" );
  $TE->Assign( "Form:Filter", $state->FormStart( MSS_GET, $url ) );
  $TE->Assign( "show_filter", $params["show_filter"]?1:0 );

  $state->values = $_v;

  $result = $TE->Parse( "filter.html:Body" );

  // ============================================================
  // Сохранение результата
  $tpl->Assign("Preparsed:TITLE", "Выборка из рапортов");
  $tpl->Assign("Preparsed:CONTENT", $result);
  $tpl->Assign("Action:PARAMS", $params);

  return GRANTED;

?>