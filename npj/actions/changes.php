<?php
/*
    {{changes page="Kuso@NPJ:PageAddress"
              [mode=, style= -- по общей спецификации]
              [digests=1]
              [order="CHANGES|commented|comments"]
              [filter="documents|posts|announces|all"]
              [limit=20 / max=20]
    }}
*/
//  $debug->Trace_R( $params );
//  $debug->Error( $script_name );

  $tpl->Assign( "show_past",     $params["show_past"]     );
  $tpl->Assign( "show_future",   $params["show_future"]   );
  $tpl->Assign( "show_timeline",        $params["show_future"] || $params["show_past"] );
  $tpl->Assign( "show_past_and_future", $params["show_future"] && $params["show_past"] );

  $rh->UseClass( "ListObject", $rh->core_dir );

  if (!isset($params[0])) $params[0] = $this->npj_object_address;
  if (!isset($params["limit"])) $params["limit"] = $principal->data["_recentchanges_size"];
  if (!isset($params["max"])) $params["max"] = $params["limit"];

  if (!isset($params["order"])) $params["order"] = "changes";

  if (isset($params["digests"])) $is_digests =1;
  else                           $is_digests =0;


  // "subject"
  if (!isset($params["subject"]))      $params["subject"] = 0;
  if ($params["subject"] === "subject") $params["subject"] = 1;
  if ($params["subject"] === "tag")     $params["subject"] = 0;

  // 1. unwrap
  $supertag = $this->_UnwrapNpjAddress( $params[0] );
  $supertag = $this->RipMethods( $supertag, RIP_STRONG );     // свести до ближайшей записи

  if ($is_digests)
  {
    $supertag_issue = " AND r.record_id = rr.record_id AND r.is_digest>0";
    $join_issue     = ", ".$rh->db_prefix."records_rare as rr";
    if ($supertag[0] != "@") $supertag_issue .= " AND rr.announced_supertag LIKE ".$db->Quote( $supertag."%" );
    $order_issue = "rr.digest_dtto DESC";
    $select_issue = "rr.digest_dtto, ";
  }
  else
  {
    if ($supertag[0] != "@") $supertag_issue = " AND supertag LIKE ".$db->Quote( $supertag."%" );
    else                     $supertag_issue = "";
    $join_issue = "";
    $order_issue = "edited_datetime DESC";
    $select_issue = "";
  }

  if ($supertag[0] == "@")
  {
    $join_issue.=", ".$rh->db_prefix."profiles as p, ".$rh->db_prefix."users as u ";
    $supertag_issue.=" AND u.user_id = p.user_id AND p.user_id = r.user_id AND p.security_type < ".$db->Quote(COMMUNITY_SECRET);
    $select_issue.=" u.login, u.node_id, ";
  }

  if ($params["order"] == "commented")
  {
    $join_issue.=", ".$rh->db_prefix."comments as c ";
    $supertag_issue .= " AND c.comment_id = r.last_comment_id AND c.record_id = r.record_id AND c.active=1 ";
    $order_issue = "c.created_datetime DESC";

    $select_issue = $select_issue." c.created_datetime as comment_datetime, ".
         " c.user_id as comment_user_id, c.user_name as comment_user_name, c.ip_xff as comment_ip_xff, c.comment_id,".
         " c.user_login as comment_user_login, c.user_node_id as comment_user_node_id, ";
  }
/*
<< 2004-11-29 D E P R E C A T E D by kuso+max. liable to refactoring at individual ACTION >>
  if ($params["order"] == "comments")
  {
    $join_issue.=", ".$rh->db_prefix."comments as c ";
    $supertag_issue .= " AND c.record_id = r.record_id AND c.active=1 ";
    $order_issue = "c.created_datetime DESC";
    $select_issue = $select_issue." c.created_datetime as comment_datetime, ".
         " c.subject as comment_subject, c.body_post as comment_body_post, ".
         " c.user_id as comment_user_id, c.user_name as comment_user_name, c.ip_xff as comment_ip_xff, c.comment_id,".
         " c.user_login as comment_user_login, c.user_node_id as comment_user_node_id, ";
  } else
<< 2004-11-29 end of D E P R E C A T E D />>
*/
  if (!isset($params["filter"])) 
  {
    if ($params["order"] != "commented") $params["filter"] = "documents";
  }


  if ($params["filter"] == "posts")     $supertag_issue.=" AND r.type=".$db->Quote(RECORD_POST);
  if ($params["filter"] == "documents") $supertag_issue.=" AND r.type=".$db->Quote(RECORD_DOCUMENT);
  if ($params["filter"] == "announces") $supertag_issue.=" AND r.is_announce>0 AND r.type=".$db->Quote(RECORD_POST);

  // 2. load recent by subtree
  // fields must present:
  // type, group1, group2, group3, group4, record_id. record_id as id, user_id - is
  $sql = "SELECT ". $select_issue.
         " 0 as is_empty, "." 0 as is_empty1, ". // ??? а нам оно надо? -- seems to be (kuso@)
         " r.tag as tag1, ".
         " r.record_id, r.record_id as id, ".
         " r.user_id, ".
         " r.group1, r.group2, r.group3, r.group4, ".
         " r.type ".
         " FROM ".
         $rh->db_prefix."records as r ".$join_issue." WHERE 1=1 ".$supertag_issue.
         " ORDER BY ".$order_issue;

  $rs = $db->SelectLimit( $sql, $rh->recent_changes_limit );
  $q_data = $rs->GetArray();

  // 3. filter them
  $commented_hash=array();
  $found = 0; $data = array();

  foreach ($q_data as $fields)
  {
    $cache->Store( "record", $fields["record_id"], 1, $fields );
    if ($principal->IsGrantedTo(  $this->security_handlers[$fields["type"]],
                                    "record", $fields["record_id"]))
    {
       $record_ids[] = $fields["record_id"];
       $filtered[$fields["record_id"]] = $fields; // нужно для мёржа с массивом "тел" -- $qb_data
       if (sizeof($record_ids) > $params["max"]) break;
    }
  }
  //if ($debug->kuso) $debug->Error_R($filtered);

  // а теперь получаем бодисы  << max@ >>
  //  $order_issue присутствует, но такой ли он нам нужен? считаем, что такой же поскольку раньше он и использовался
  $pagesize = $rh->recent_changes_limit; //и здесь - такой ли ?
  $uactn = &$rh->UtilityAction(); // actions теперь живут в отдельном классе.
  $qb_data = $uactn->GetRecordBodies ( &$record_ids, $params, 0, false, $order_issue, $params["limit"] ); 

  $qf_data = array();
  foreach ($qb_data as $v)
    $qf_data[] = array_merge( (array)$filtered[$v["record_id"]], (array)$v);

  // formatting
  foreach ($qf_data as $fields)
  {
     $found++;
       if ($fields["tag"] == "")
       {
         $fields["tag"] = $tpl->message_set["JournalHomePage"];
         $fields["_empty_tag"] = 1;
         if ($supertag_issue == "") $fields["tag"].= " ". rtrim($fields["supertag"],":");
       }
       if ($supertag[0] == "@")
       {
         $fields["tag"] = $fields["login"]."@".$fields["node_id"].":".$fields["tag"];
         $fields["_empty_tag"] = 1;
       }

       if ($is_digests)
         $fields["datetime"] = $fields["digest_dtto"]; // для дайджестов -- дата завершения периода дайджеста
       else
         $fields["datetime"] = $fields["edited_datetime"]; // наша главная дата -- которая едитед

       if (($params["order"] != "commented") || (!isset($commented_hash[$fields["record_id"]])))
        $data[] = $fields;
       else
        $found--;
       $commented_hash[$fields["record_id"]] = 1;

       if ($found >= $params["max"]) break;
  }

  // ----- завершение первой стадии -----------------------

  // RECENT_COMMENTED replacements:
  if ($params["order"] == "commented")
  {
    foreach( $data as $k=>$v )
    {
      // 1. заменить адрес на ссылку на комментарии
      $data[$k]["supertag"] = $data[$k]["supertag"]."/comments#comment".$data[$k]["comment_id"];
      // 2. заменить информацию об изменявшем
      $data[$k]["edited_user_id"] = $data[$k]["comment_user_id"];
      $data[$k]["edited_user_login"] = $data[$k]["comment_user_login"];
      $data[$k]["edited_user_name"] = $data[$k]["comment_user_name"];
      $data[$k]["edited_user_node_id"] = $data[$k]["comment_user_node_id"];
      // 3. изменить datetime
      $data[$k]["datetime"] = $data[$k]["comment_datetime"];
    }
  }
  //  if ($debug->kuso) $debug->Error_R($data);
  /*
     D E P R E C A T E D. Could not be deleted, should be moved to another action
  // RECENT_COMMENTS replacements:
  if ($params["order"] == "comments")
  {
    foreach( $data as $k=>$v )
    {
      // 1. заменить адрес на ссылку на комментарий
      $data[$k]["supertag"] = $data[$k]["supertag"]."/comments/".$data[$k]["comment_id"]."#comments";
      // 2. заменить информацию об изменявшем
      $data[$k]["edited_user_id"] = $data[$k]["comment_user_id"];
      $data[$k]["edited_user_login"] = $data[$k]["comment_user_login"];
      $data[$k]["edited_user_name"] = $data[$k]["comment_user_name"];
      $data[$k]["edited_user_node_id"] = $data[$k]["comment_user_node_id"];
      // 3. изменить datetime
      $data[$k]["datetime"] = $data[$k]["comment_datetime"];
      // 4. изменить заголовок и текст
      $data[$k]["body_post"] = $data[$k]["comment_body_post"];
      $data[$k]["subject"]   = $data[$k]["comment_subject"];
      $data[$k]["tag"]       = $data[$k]["tag"]."/Comments/".$data[$k]["comment_id"];
    }
  }
  */

  foreach( $data as $k=>$v )
  {
    $data[$k]["tag1"] = $data[$k]["tag"];
    $data[$k]["tag2"] = $data[$k]["tag"];
  }


  // преформат полей и в RSS сразу (стадия 2,3) -----------
  foreach ($data as $k=>$item)
  {
    $data[$k] = $object->_PreparseArray( &$data[$k] );
    if ($rh->rss) $rh->rss->AddEntry( &$data[$k], RSS_CHANGES );
  }

  if (($params["order"] == "comments") || ($params["order"] == "commented"))
  {
    foreach ($data as $k=>$item) $data[$k]["Href:versions_target"] = "";
  }

//  if ($debug->kuso) $debug->Error(  "-" );
  // --------------- заполняем Action:TITLE
   if ($title == "")
   {
     $rdata = $object->_Load( rtrim($supertag, "/"), 2);
     if (is_array($rdata))
      if ($rdata["tag"] == "") $title = substr($rdata["supertag"],0,strpos($rdata["supertag"],":"));
      else  $title = substr($rdata["supertag"],0,strpos($rdata["supertag"],":")).":".$rdata["tag"];
   }
   $tpl->Append("Action:TITLE", " ".$object->Link(rtrim($supertag, ":/"), "", $title) );

  // вызов алгоритма вывода (стадия 4) ----
  if (sizeof($data) == 0)
  {
    // кажется, здесь нужно придумать, что бы такого выводить, когда пусто
    // !!! -> messageset
    return "Изменений давно не было";
  }
  else
  return $object->_ActionOutput( &$data, &$params, "periodical" );

?>