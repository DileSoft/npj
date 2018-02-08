<?php
/*
  {{DirectoryChilds
           [parent=portal@npj|@]
           [type  =user|community|workgroup]
           [class =<account_class>]

           [security="public|open|limited|closed|secret"]

           [order ="name|login|registration|lastlogin"]
           [style ="tplt"]
           [mode  ="accounts"]
           [pagesize = "20"]
           [framesize= "20"]
           [pageshide = false]
           
        }}
*/
//  $debug->Trace_R( $params );
//  $debug->Trace_R( $object->data );
//  $debug->Error( $object->class );
  $rh->UseClass( "ListObject", $rh->core_dir );
  $rh->UseClass( "Arrows", $rh->core_dir );

  // 1. default params
  $orders = array( "name"         => "u.user_name asc", 
                   "login"        => "u.login asc", 
                   "registration" => "u.created_datetime desc",
                   "lastlogin"    => "u.last_login_datetime desc",
                 );
  if (isset($orders[$params["order"]])) $order = $orders[$params["order"]];
  else                                  $order = $orders[   "login"      ];

  $show_all = false;
  if ($params["parent"] == "@") $show_all = true;

  $page_size       = 20;    // по двадцать на странице
  $page_frame_size = false; // нет фреймов

  if (is_numeric($params["pagesize"])) $page_size = 1*$params["pagesize"];
  if (is_numeric($params["framesize"])) $page_frame_size = 1*$params["framesize"];


  // 1.5. доп. фильтры
  $more_conditions = "";

  $account_types = array( "user" => 0, "community" => 1, "workgroup" => 2 );
  if (isset($account_types[$params["type"]]))
   $more_conditions .= " and u.account_type = ".$db->Quote($account_types[$params["type"]]);

  $account_classes= $rh->account_classes;
  if (isset($account_classes[$params["class"]]))
   $more_conditions .= " and u.account_class = ".$db->Quote($params["class"]);

  $security_types = array( "public"  => COMMUNITY_PUBLIC,  "open"   => COMMUNITY_OPEN,
                           "limited" => COMMUNITY_LIMITED, "closed" => COMMUNITY_CLOSED,
                           "secret"  => COMMUNITY_SECRET );
  if (isset($security_types[$params["security"]]))
   $more_conditions .= " and p.security_type = ".$db->Quote($security_types[$params["security"]]);
  else 
   $more_conditions .= " and p.security_type <> ".$db->Quote(COMMUNITY_SECRET);
  if ($security_types[$params["security"]] == COMMUNITY_SECRET)
    if (!$principal->IsGrantedTo("node_admins"))
    {
      $more_tables = $rh->db_prefix."groups as g, ".$rh->db_prefix."user_groups as ug, ";
      $more_conditions .= " and u.user_id = g.user_id and g.group_id = ug.group_id ".
                          " and ug.user_id = ".$db->Quote($principal->data["user_id"]).
                          " and g.group_rank >0 ".
                          // исключаем дубль из-за владельца
                          " and (ug.user_id <> u.owner_user_id or g.group_rank = ".$db->Quote(GROUPS_SELF).")";
    }


  // 2. get parent data
  $parent = false;
  if ($params["parent"])
  {
    $supertag = $this->_UnwrapNpjAddress( $params["parent"] );
    $supertag = $this->RipMethods( $supertag, RIP_STRONG );     // свести до ближайшей записи
    $supertag = preg_replace( "/:.*$/", "", $supertag );
    $parent = &new NpjObject( &$rh, $supertag );
    $data   = $parent->Load(3);
    if (!is_array($data)) $parent = false;
  }
  if ($parent === false) 
  {
    $supertag = preg_replace( "/:.*$/", "", $object->npj_object_address );
    $parent = &new NpjObject( &$rh, $supertag );
    $data   = $parent->Load(3);
  }

  // 3. construct SQL parts
  $table = "profiles as p, ".$more_tables.$rh->db_prefix."users as u ";
  $where = " u.user_id = p.user_id and u.alive=1 and u.user_id != 1".
           ($show_all?"":(" and p.parent_id=". $db->Quote( $parent->data["id"] )))
           .$more_conditions;

  // 4. init Arrows
  $arrows = &new Arrows( &$state, $where, $table, $page_size, $page_frame_size );
  $arrows->Parse( "actions/_arrows.html", "FORUM-ARROWS"  );

  if ($params["pageshide"]) $tpl->Assign( "FORUM-ARROWS", "" );


  // 5. go SQL
  $sql = "select u.user_id, u.root_record_id, u.user_name, u.login, u.node_id, p.bio ".
         " from ".$rh->db_prefix.$table.
         " where ".$where.
         " order by ". $order;
  $debug->Trace( $sql );
//  $debug->Error(11);

  $rs = $db->SelectLimit( $sql, $arrows->GetSqlLimit(), $arrows->GetSqlOffset() );
  $account_cache = $rs->GetArray();

  // 5.5 -- создать кэш из аккаунтов, чтобы потом препарсить тела
  $data = array();
  if (sizeof($account_cache) > 0)
  {
    // 6. get bodies & stuff
    include( dirname(__FILE__)."/__db_record.php" );
    foreach( $account_cache as $k=>$acc )
    {
       $record_o = array( "r.server_datetime desc", "r.commented_datetime desc");
       $body_prefix = array( "", "Commented>" );
       $bodies   = array();
       foreach( $record_o as $sort_mode )
       {
         $sql = "select ".$__db_record_fields." from ".$__db_record_tables.
                   ", ".$rh->db_prefix."records_ref as ref ".
                " where ".
                " ref.syndicate >= 0 and ".
                " ref.keyword_user_id = ".$db->Quote($acc["user_id"]).
                " and ref.record_id = r.record_id ".  
                " and ref.need_moderation=0 ".
                " order by ".$sort_mode;
         $debug->Trace( "BODY <br />".$sql );
         $rs = $db->SelectLimit( $sql, 1 );  
         $a = $rs->GetArray();
  
         if (sizeof($a)) { $item = $a[0]; $item = $object->_PreparseArray( &$item ); }
         else            { $item = array( "last_comment_id" => 0, "record_id" => 0); }
         

         $bodies[] = $item;
       }

       $acc = $object->_PreparseAccount( &$acc );

       // get number of public messages
       $sql = "select count(record_id) as msg_count from ".$rh->db_prefix."records_ref ".
              " where syndicate>=0 and need_moderation=0 and keyword_id=". $db->Quote( $acc["root_record_id"] );
       $rs  = $db->Execute( $sql );
       $a   = $rs->GetArray();
       if (sizeof($a) == 0) $acc["number_public"] = 0;
       else                 $acc["number_public"] = $a[0]["msg_count"];

       // merge!
       $data[$k] = $bodies[0];

       for( $i=1; $i<sizeof($bodies); $i++)
        foreach( $bodies[$i] as $kk=>$vv )
         if (!is_numeric($kk))
          $data[$k][$body_prefix[$i].$kk] = $vv;

       foreach( $acc as $kk=>$vv )
        if (!is_numeric($kk))
         $data[$k]["Account>".$kk] = $vv;

       $data[$k]["even"] = $k%2;
       $data[$k]["non_empty_abstract"] = $this->rh->tpl->Format($item["body_post"], "non_empty_abstract");

       // add to rss
       if ($rh->rss) $rh->rss->AddEntry( &$data[$k], RSS_ACCOUNTS_LAST );
    }
  }

  // 8. output
  if (sizeof($data) == 0)
  {
    // кажется, здесь нужно придумать, что бы такого выводить, когда пусто
    // !!! -> messageset
    return "";
  }
  else
  return $object->_ActionOutput( &$data, &$params, "accounts" );



?>