<?php
/*
    {{rubrikator 

       [for="project@npj:Category1 project@npj:Category1"
            [method="or|and|facet"]
       ]

       [style="br|ol|ul|indent"] 
       [mode="feed|list|periodical|forum"]

       [filter="posts|documents|both|announces|events|announces-documents"] 

       [order="user|server|edited|tag|subject"] 
       [subject="0|1"] 
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
  if (!$params["for"]) $params["for"] = $params[0];

  // method
  $methods = array( "or", "and", "facet" );
  if (!in_array($params["method"], $methods)) $params["method"] = "or";

  
  // filter
  if ($params["filter"] == "announces") $filter = "=1 and is_announce>0"; else
  if ($params["filter"] == "announces-documents") $filter = "=1 and is_announce=2"; else
  if ($params["filter"] == "events") $filter = "=1 and is_announce=1"; else
  if ($params["filter"] == "posts") $filter = "=1"; else
  if ($params["filter"] == "documents") $filter = "=2"; else
                                        $filter = ">0";

  // order
  if ($params["order"] == "tag") $order = "tag ASC"; else
  if ($params["order"] == "subject") { $params["subject"]=1; $order = "subject ASC"; } else
  if ($params["order"] == "user")     $order = "user_datetime DESC";     else
  if ($params["order"] == "server")   $order = "created_datetime DESC";  else
  if ($params["order"] == "created")  $order = "created_datetime DESC";  else
                                      $order = "edited_datetime DESC"; 
  // tweaks-1
  if (!isset($params["subject"])) $params["subject"] = 1;
  // tweaks-2
  if (!isset($params["limit"])) $params["limit"] = $principal->data["_recentchanges_size"];
  if (!isset($params["max"])) $params["max"] = $params["limit"];

  $object_account = &new NpjObject( &$rh, $object->npj_account );

  // 0.5. Преобразовать данные о категориях в НПЖ-объекты
  $uaction = &$rh->UtilityAction(); // actions теперь живут в отдельном классе.
  $keywords = $uaction->NpjToRecords( &$object_account, &$principal, $params["for"] );

  // 0.6. Составить запрос по модели OR или AND
  $filter_data = $uaction->ComposeRefQueryPart( &$object_account, &$keywords, "r.record_id", 
                                            $params["method"], $params["_for"] );  

  // 2. compose & run sql =======================================================================
  //    * ??? need copy-paste patch from CHANGES or FEED
  $debug->Trace( "time mark1" );
  $sql = "SELECT  r.tag, r.subject_post as subject, ".
         " r.record_id, r.record_id as id, r.supertag, ".
         " r.user_id, ".
         " r.group1, r.group2, r.group3, r.group4, ".
         " r.type ".
         " FROM ".$rh->db_prefix."records as r ".
                  $filter_data["filter_table"].
         " WHERE r.type ".
                 $filter.
                 $filter_data["filter_where"].
                 $filter_data["filter"].
         " ORDER BY ".$order;
  $rs = $db->SelectLimit( $sql, $rh->facet_limit );
  $a  = $rs->GetArray();
  $debug->Trace( "time mark2" );


  // 3. filter them out =========================================================================
  $found = 0; $data = array();
  foreach($a as $fields)
  {
    $cache->Store( "record", $fields["record_id"], 1, &$fields ); 
      if ($principal->IsGrantedTo(  $this->security_handlers[$fields["type"]], 
                                    "record", $fields["record_id"]))
    {  
       if ($fields["tag"] == "") 
         $fields["tag"] = $tpl->message_set["JournalHomePage"];
       $fields["datetime"] = $fields["edited_datetime"]; // наша главная дата -- которая едитед
       $data[ $fields["record_id"] ] = $fields;
       if (sizeof($data) >= $params["max"]) break;
    }
  }
  $debug->Trace( "time mark3" );

  // 4. get bodies, man! ==========================================================================
  $record_ids = array();
  foreach( $data as $k=>$v ) $record_ids[] = $v["record_id"];
  if (sizeof($record_ids) > 0)
  {
    // 5. get bodies & stuff
    $bodies = $uaction->GetRecordBodies ( &$record_ids, $params, 1, false, $order );
  } else $bodies = array();
  foreach( $bodies as $k=>$v )
  {
    $data[ $v["record_id"] ] = array_merge( $v, $data[$v["record_id"]] );
  }
  $debug->Trace( "time mark4" );


  // ----- завершение первой стадии -----------------------

  // преформат полей и в RSS сразу (стадия 2,3) -----------
  foreach ($data as $k=>$item)
  {
    $data[$k] = $object->_PreparseArray( &$data[$k] );
    if ($rh->rss) $rh->rss->AddEntry( &$data[$k], RSS_FACET );
  }
  $debug->Trace( "time mark5" );
  
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
    return ""; // "Записей к данной рубрике (данным рубрикам) нет";
  }
  else
  return $object->_ActionOutput( &$data, &$params, "list" );


?>