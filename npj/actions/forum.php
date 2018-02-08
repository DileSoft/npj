<?php
/*

  {{Forum  for=kuso@npj:WackoWiki
           [order ="server|user|commented|number_comments|number_hits"]
           [style ="tplt"]
           [mode  ="feed|list|forum"]

           [pagesize=20]
           [_keyword_ids=Array] <- php only
           [dtfrom="2003-09-15"]           
           [dtto  ="2003-09-23"]           
        }}


*/
//  $debug->Trace_R( $params );
//  $debug->Trace_R( $object->data );
//  $debug->Error( $object->class );
  $rh->UseClass( "ListObject", $rh->core_dir );
  $rh->UseClass( "Arrows", $rh->core_dir );


  // 1. default params
  $orders = array( "server"    => "r.server_datetime",
                   "user"      => "r.user_datetime",
                   "commented" => "(r.commented_datetime+ r.server_datetime - r.server_datetime*SIGN(last_comment_id))",
                   "number_comments" => "r.number_comments",
                 );
  if (isset($orders[$params["order"]])) $order = $orders[$params["order"]];
  else                                  $order = $orders[   "commented"  ];

  $page_size       = 20;    // по двадцать на странице
  $page_frame_size = false; // нет фреймов

  if (isset($params["pagesize"])) $page_size = $params["pagesize"];
  if (isset($params["page_size"])) $page_size = $params["page_size"];

  // 1.0. get keyword data
  $keyword = false;
  if ($params["for"])
  {
    $supertag = $this->_UnwrapNpjAddress( $params["for"] );
    $supertag = $this->RipMethods( $supertag, RIP_STRONG );     // свести до ближайшей записи
    $keyword = &new NpjObject( &$rh, $supertag );
    $data   = $keyword->Load(2);
    if (!is_array($data)) $keyword = false;
  }
  if ($keyword === false) $keyword = &$object;
  $keyword->Load(2);

  // -. Need Moderation
  $need_moderation = 0;
  if ($params["type"] == "mod")
  {
    if ($principal->IsGrantedTo("rank_greater", "account", $roubric->data["user_id"], GROUPS_MODERATORS))
     $need_moderation = 1;
  }

  // keyword_ids
  $keyword_ids = array();
  if ($params["_keyword_ids"]) $keyword_ids = $params["_keyword_ids"];
  else                         $keyword_ids[] = $keyword->data["record_id"];
  foreach( $keyword_ids as $k=>$v )
    $keyword_ids[$k] = $db->Quote($v);

  // dt filtering
  $filter_dt = "";
  if ($params["dtto"])
    $filter_dt = " AND r.user_datetime <= ".$db->Quote($params["dtto"]);
  if ($params["dtfrom"])
    $filter_dt .= " AND r.user_datetime >= ".$db->Quote($params["dtfrom"]);

  // 2. construct SQL parts
  $where =
         " keyword_id in (".implode(",", $keyword_ids).") and ".
         " syndicate >= 0 and ".
         " group1=0 and group2=0 and group3=0 and group4=0 and ". // public
         " need_moderation = ".$db->Quote($need_moderation).
         $filter_dt;
  $table = "records_ref as r";

  // 3. init Arrows
  $arrows = &new Arrows( &$state, $where, $table, $page_size, $page_frame_size );
  $arrows->Parse( "actions/_arrows.html", "FORUM-ARROWS"  );

  // 4. go SQL
  $sql = "select distinct record_id".
         " from ".$rh->db_prefix.$table." where ".$where." order by ". $order." desc";

  $debug->Trace( $sql );

  $rs = $db->SelectLimit( $sql, $arrows->GetSqlLimit(), $arrows->GetSqlOffset() );
  $a = $rs->GetArray();

  // квотили здесь... а будем квотить в ActionUtility::GetRecordBodies, хотя не уверен что это нужно всегда
  // kuso@ saz: you`ll need it in every case
  $record_ids = array();
  foreach( $a as $k=>$v ) $record_ids[] = $v["record_id"];

  if (sizeof($record_ids) > 0)
  {

    // 5. get bodies & stuff
    // << max@jetstyle 2004-11-24 >> "вынос тела"
    $order_issue = str_replace("server_datetime", "created_datetime", $order)." desc";
    $uactn = &$this->rh->UtilityAction(); // actions теперь живут в отдельном классе.
    $data = $uactn->GetRecordBodies ( &$record_ids, $params, 1, false, $order_issue );

    // 5. preparse / rss
    foreach ($data as $k=>$item)
    {
      $cache->Store( "npj", $item["supertag"], 2, &$data[$k] );
      $data[$k] = $object->_PreparseArray( &$data[$k] );
      $data[$k]["even"] = $k%2;
      $data[$k]["non_empty_abstract"] = $this->rh->tpl->Format($item["body_post"], "non_empty_abstract");

      if ($rh->rss) $rh->rss->AddEntry( &$data[$k], RSS_FORUM );
    }
  } else $data = array();

  // 6. output
  if (sizeof($data) == 0)
  {
    // кажется, здесь нужно придумать, что бы такого выводить, когда пусто
    // !!! -> messageset
    return "";
  }
  else
  return $object->_ActionOutput( &$data, &$params, "forum" );


?>