<?php

  // Панель багтрекера
  //
  // params:
  /*
         (+) [for="project@npj:Category"]
         (+) [page_size=20]
         (+) [hide_pages=1]

         (+) [for="project@npj:Category1 project@npj:Category1"
                [method="or|and|facet"]
             ]

         (+) [order="reported|touched|priority|severity|status|no]
         (+) [dir="asc|desc"]
         (+) [style="default|todo"]
         (+) [split_1="Отложено" split_2="Обязательно"]

         filters:
         (+) [severity="<value>"]
         (+) [status="<status>"]
         (+) [priority="<priority>"]
         (+) [reporter="kuso@npj"]
         (+) [developer="kuso@npj"]
         (+) [hide_solved=1] -- and [hide_*] all other states
         (+) [time_limit=6]

         querystring juggling:
         (+) order/dir
    
  */
  //$debug->Trace_R( $params );
  $rh->UseClass( "ListObject", $rh->core_dir );
  $rh->UseClass( "Arrows", $rh->core_dir );

  // ===========================================================
  // Параметры по-умолчанию
  if (!$params["for"]) $params["for"] = $this->object->npj_object_address; 
  // style
  $styles = array( "default", "todo", "more" );
  if (!in_array($params["style"], $styles)) $params["style"] = "default";

  // -------------------- style-based overriding ---------------
  if ($params["style"] == "todo")
  {
    $params["order"]     = "priority";
  }


  $fresh_seconds = 60*60*12;

  $page_size=20;
  $page_frame_size = false;
  if (1*$params["page_size"]>0) $page_size = 1*$params["page_size"];

  $filter = "";
  $filter_facet_table = "";
  $filter_facet_where = "";
  // method
  $methods = array( "or", "and", "facet" );
  if (!in_array($params["method"], $methods)) $params["method"] = "or";
  // status + state
  if (isset($params["status"]))
  if ($params["status"] != "*")
  {
    $parts = explode( "-", $params["status"]);
    if ($parts[0] != "*")
      if (isset($this->config["states"][$parts[0]])) $filter.=" and i.state=".$db->Quote($parts[0]);
    if ($parts[1] != "*")
      if (isset($this->config["statuses"][$parts[1]])) $filter.=" and i.state_status=".$db->Quote($parts[1]);
  }
  // hide_
  foreach( $this->config["hide_state_filter"] as $v )
    if ($params["hide_".$v])
      $filter.=" and i.state <> ".$db->Quote($v);
  // prio
  if (isset($params["priority"]))
  if ($params["priority"] != "*")
    $filter.=" and i.priority=".$db->Quote(1*$params["priority"]);
  // severity
  if (isset($params["severity"]))
  if ($params["severity"] != "*")
    $filter.=" and i.severity_value=".$db->Quote(1*$params["severity"]);
  // reporter
  if (isset($params["reporter"]))
  if ($params["reporter"] != "*")
    $filter.=" and i.reporter_id=".$db->Quote(1*$params["reporter"]);
  // developer
  if (isset($params["developer"]))
  if ($params["developer"] != "*")
    $filter.=" and i.developer_id=".$db->Quote(1*$params["developer"]);
  // time_limit
  if (isset($params["time_limit"]))
    $filter.=" and NOW() < ADDDATE(r.user_datetime, INTERVAL ".(1*$params["time_limit"])." HOUR)";

  // Замена из QUERYSTRING:
  // ?trako_order=XX&_trako_dir=YY
  if ($_GET["trako_order"]) $params["order"] = $_GET["trako_order"];
  if ($_GET["_trako_dir"])   $params["dir"]   = $_GET["_trako_dir"];
  
  $orders = array( "reported" => "r.server_datetime ",
                   "touched"  => "r.user_datetime   ",
                   "priority" => "i.priority        ",
                   "severity" => "i.severity_value  ",
                   "status"   => "i.state_sort      ",
                   "no"       => "i.issue_no        ",
                 );
  if (isset($orders[$params["order"]])) { $order = $orders[$params["order"]]; $_order = $params["order"]; }
  else                                  { $order = $orders[   "touched"    ]; $_order = "touched";        }
  $dirs = array( "asc", "desc" );
  if (in_array( $params["dir"], $dirs )) $_dir = $params["dir"]; 
  else                                   $_dir = "desc";
  $order.=" ".$_dir;
  
  // ===========================================================
  // Данные об аккаунте
  $account = &new NpjObject( &$rh, $this->object->npj_account );
  $account->Load(2);

  // =================================================================================
  //  Преобразовать данные о категориях в НПЖ-объекты
  $account_category =  &new NpjObject( &$rh, $this->object->npj_account.":" );
  $account_category -> Load(2);
  $params["for"] = str_replace(";", " ", $params["for"]);
  $kwds = explode( " ", $params["for"] );
  $keywords = array();
  foreach( $kwds as $k=>$v )
  {
    $supertag = $this->object->_UnwrapNpjAddress( $v );
    $supertag = $this->object->RipMethods( $supertag, RIP_STRONG );     // свести до ближайшей записи
    $category = &new NpjObject( &$rh, $supertag );
    if ($category->npj_account != $account->npj_account)
    {
      $account = &new NpjObject( &$rh, $category->npj_account );
      $account->Load(2);
    }
    if (($category->Load(2) == NOT_EXIST) && !isset($keywords[$this->object->npj_account.":"]))
    {
      $category = &$account_category;
      $supertag = $this->object->npj_account.":";
      
    }
    //  Проверка прав доступа
    if ($category->HasAccess( $principal, "acl", "actions" ))
    {
      $keywords[ $supertag ] = &$category;
      $debug->Trace("Category -> ".$supertag );
    }
  }
  if (sizeof($keywords) == 0) return $category->Forbidden( "Trako.DeniedByActionsAcl" );
  if (sizeof($keywords) == 1) { foreach($keywords as $k=>$v) { $category = &$keywords[$k]; break; } }
  else                        $category = &$account_category;

  // =================================================================================
  //  Составить запрос по модели OR или AND
  $keyword_field = "r.keyword_id";
  if (($params["method"] == "or") || ($params["method"] == "and"))
  {
    $op = " ".$params["method"]." ";
    $result = ""; $f=0;
    foreach( $keywords as $k=>$v )
    {
      $debug->Trace("KEYWORD: ".$k );
      if ($f) $result.=$op; else $f=1;
      $result.=$keyword_field." = ".$db->Quote($v->data["record_id"]); 
    }
    $filter .= " and (".$result.")";
  }

  // =================================================================================
  //  Составить запрос по модели FACET
  if ($params["method"] == "facet")
  {
    // получить "keywords_all"
    if (!isset($params["_for"]))
    {
       $sql = "select supertag, tag from ".$rh->db_prefix."records where ".
               "is_keyword=1 and user_id = ".$db->Quote($account->data["user_id"]).
               " order by tag";
       $rs  = $db->Execute( $sql );
       $a   = $rs->GetArray();
       $facet_keywords_all = array();
       foreach($a as $k=>$v)
         $facet_keywords_all[] = $v["tag"];
       $params["_for"] = $facet_keywords_all;
    }
    $facet_keywords_all = $params["_for"];
    foreach( $facet_keywords_all as $k=>$v )
     $facet_keywords_all[$k] = $account->NpjTranslit($v);
    // find "facet" part
    foreach( $facet_keywords_all as $k=>$v )
    {
      $facet_keywords_all[$k] = array(
          "facet"     => preg_replace("/\/.*$/","",$v),
          "supertag"  => $v,
                                      );
    }
    // legal facets
    $groups     = array();
    $grouplings = array();
    foreach ($facet_keywords_all as $k=>$v)
    {
      if ($grouplings[$v["facet"]])
        $groups[ $v["facet"] ][] = $v;
      else
        $grouplings[$v["supertag"]] = $v;
    }
    // "etc." facet
    foreach( $grouplings as $supertag=>$v )
     if (!$groups[$supertag])
       $groups["_"][] = $v;

//    $debug->Error_R( $groups );
    // побить $keywords по фасетам
    $faceted_keywords = array();
    foreach($keywords as $k=>$v)
    {
      $facet = preg_replace("/^[^:]+:(.*?)\/.*$/","$1",$v->data["supertag"]);
      $debug->Trace("KEYWORD ".$v->data["supertag"]." FACET: ".$facet );
      if (!$groups[$facet]) $facet = "_";
      $faceted_keywords[$facet][] = &$keywords[$k];
    }
    $fno="";
    // составить OR часть запроса
    foreach($faceted_keywords as $name => $facet)
    {
      $keyword_field = "r".$fno.".keyword_id";
      if ($fno=="") $fno=1; else 
      {
        $filter_facet_table.= ", ".$rh->db_prefix."records_ref AS r".$fno;
        $filter_facet_where.= " r".$fno.".record_id = i.record_id and ";
        $fno++;
      }
      $op = " or ";
      $result = ""; $f=0;
      foreach( $facet as $k=>$v )
      {
        $debug->Trace("KEYWORD: ".$v->data["supertag"]. " { ".$v->data["record_id"]." }" );
        if ($f) $result.=$op; else $f=1;
        $result.=$keyword_field." = ".$db->Quote($v->data["record_id"]); 
      }
      $faceted_keywords[$name] = "(".$result.")";
      //  -
    }
    // составить AND часть запроса
    $result = ""; $f=0;
    foreach( $faceted_keywords as $k=>$v )
    {
      $op = " and ";
      if ($f) $result.=$op; else $f=1;
      $result.=$v; 
    }
    $filter .= " and (".$result.")";
    //$debug->Error( $filter );
  }

  // ===========================================================
  // Понимаем уровень доступа принципала в этой группе
  $access_rank = $this->rh->cache->ReStore( "maxrank_". $principal->data["user_id"], $account->data["user_id"]);
  if ($access_rank < 0) $access_rank=0;

  // ===========================================================
  // "Стрелочки" 
  $where = $filter_facet_where."i.record_id = r.record_id and r.group1 = ".$db->Quote(ACCESS_GROUP_TRAKO).
           $filter."  and ".
           "(".
             "(i.access_rank <= ".$db->Quote($access_rank)." and i.access_rank >= 0)".
               " or ".
             ($access_rank >= $this->config["security"]["private"]?
               ("(i.access_rank < 0 and ".$db->Quote($access_rank)." and i.access_rank >= 0) or "):"").
             "(i.access_rank=-1 and i.reporter_id=".$db->Quote($principal->data["user_id"]).")".
           ")";
  $table = "trako_issues as i, ".$rh->db_prefix."records_ref as r ".$filter_facet_table;

  $arrows = &new Arrows( &$state, $where, $table, $page_size, $page_frame_size );

  // ===========================================================
  // Строим запрос на баги и получаем их
  $sql = "select i.* from ".$rh->db_prefix.$table.
         " where ".$where.
         " order by ".$order.", r.user_datetime desc";

//  $debug->Error( $sql );

  $rs  = $db->SelectLimit( $sql, $arrows->GetSqlLimit(), $arrows->GetSqlOffset() );
  $a   = $rs->GetArray();
  $record_ids_q = array();
  $issues       = array();
  foreach( $a as $k=>$v ) 
  {
    $issues[ $v["record_id"] ] = $a[$k];
    $record_ids_q[] = $db->Quote( $v["record_id"] );
  }

  // ===========================================================
  // Получаем тела записей, соответствующих багам
  if (sizeof($record_ids_q) > 0)
  {
    // 5. get bodies & stuff
    include( $rh->npj_actions_dir."__db_record.php" );
    $sql = "select".$__db_record_fields." from ".$__db_record_tables.
              ", ".$rh->db_prefix."trako_issues as i ". // ----------------- trakopatch
           " where r.record_id in (".implode(",",$record_ids_q).") ".
              " and r.record_id = i.record_id ". // ------------------------ trakopatch
           " order by ".str_replace("server_datetime", "created_datetime",$order);
    $debug->Trace( "BODIES <br />".$sql );
    $rs = $db->Execute( $sql );  
    $data = $rs->GetArray();

  // ===========================================================
  // Препарсинг и RSS
    foreach ($data as $k=>$item)
    {
      $data[$k]["_post_supertag_cancel"] = true;
      $data[$k]["_post_date_cancel"]     = true;
      $cache->Store( "npj", $item["supertag"], 2, &$data[$k] ); 

      $issue = &$issues[$item["record_id"]];
      $issue["&account"] = &$account;
      $issue["RECORD" ]  = &$data[$k];

      $issue = &$this->PreparseIssue( $issue );
      $issue["even"] = $k%2;
      $issue["non_empty_abstract"] = $tpl->Format( $issue["RECORD"]["body_post"], "non_empty_abstract" );

      // (-) add "freshness"
      if (time() - strtotime($item["user_datetime"]) > $fresh_seconds) $issue["is_fresh"] =0;
      else                                                             $issue["is_fresh"] =1;

      if ($rh->rss) $rh->rss->AddEntry( &$issue, RSS_TRAKO_ISSUES );
    }

  } else $issues = array();

  // ===========================================================
  // Парсинг результата
  // 0. spawn TE
  $TE = &$this->GenerateTemplateEngine( $this->config["template_engine"] );

  // 1. parse Arrows
  $arrows->tpl = &$TE;
  if (!$params["hide_pages"]) $arrows->Parse( "arrows.html", "ARROWS"  );
  else $TE->Assign("ARROWS", "");

  // 2. parse additional links
  $TE->Assign("can_edit", $this->HasAccess( &$principal, &$account, array(), "edit" ));

  $TE->Assign( "Href:Trako",     $account->Href( $account->npj_object_address )  ."/". $this->config["subspace"] );
  if ($category->data["tag"] != "")
   $TE->Assign( "Href:Trako/sub", $account->Href( $category->npj_object_address ) ."/". $this->config["subspace"] );
  else
   $TE->Assign( "Href:Trako/sub", "" );

  // 3. prepare sortmodes
  foreach( $orders as $k=>$v )
  {
    $order_name = $tpl->message_set["Trako.panel_orders"][$k];
    $dir_name   = $tpl->message_set["Trako.panel_dirs"][$_dir];
    $selected   = $k == $_order;
    $TE->Assign("is_selected", $selected);
    $TE->Assign("order_name", $order_name);
    $TE->Assign("order", $k);
    $TE->Assign("dir_name",   $selected?$dir_name:"");
    $TE->Assign("dir",   $selected?$_dir:"");

    if ($selected) // change sort order
      $TE->Assign("Href:sort", $state->Plus("_trako_dir", $_dir=="asc"?"desc":"asc") );
    else
      $TE->Assign("Href:sort", $state->Plus("trako_order", $k ) );

    $TE->Parse("panel.html:SortButton", "Trako.ORDER:".$k );
  }

  //$debug->Error_R( $state->values );
  $TE->Assign( "PanelOrderSplit:order",   $_order );
  $TE->Assign( "PanelOrderSplit:current", "-" );
  $TE->Assign( "PanelOrderSplit:params",  $params );

  // 4. parse feed
  $list = &new ListObject( &$rh, &$issues );
  $list->tpl = &$TE;
  $result = "<!--notoc-->".$list->Parse( "panel_".$params["style"].".html:Grid" )."<!--/notoc-->";

  // ============================================================
  // Сохранение результата
  $tpl->Assign("Preparsed:TITLE", "Перечень рапортов");
  $tpl->Assign("Preparsed:CONTENT", $result);


  return GRANTED;

?>