<?php
/*
   {{search
      [for="WackoWiki"]
      [topic|title="1"]
      [form="0|1|YES|no|none"]
      [order="RELEVANCE|tag|subject|time"]
      [where="JOURNAL|node"]
      [filter="posts|documents|ALL|announces|comments|records"] *
      [limit/max="20"]
      [subject="tag|1|short"]
      [mode, style]
      [show_future, show_past, show_timeline]
   }}

* "all" means all inluding comments
  "records" means all but comments
   
*/
//
//  $debug->Trace_R( $params );
//  $debug->Error( $script_name );
// $page_size = 25;

  $rh->UseClass( "ListObject", $rh->core_dir );

  // part 0. default parameters
  if ($_REQUEST["_where"]) $params["where"]="node";

  // "limit", "max"
  if (!isset($params["max"]))
   if (!isset($params["limit"]))
    $params["max"] = $params["limit"];

  // "subject"
  if (!isset($params["subject"])) $params["subject"] = 1;
  if ($params["subject"] == "tag") $params["subject"] = 0;
  if ($params["subject"] == "short") $params["subject"] = 1;

  // "show_*"
  $tpl->Assign( "show_past",     $params["show_past"]     );
  $tpl->Assign( "show_future",   $params["show_future"]   );
  $tpl->Assign( "show_timeline",        $params["show_future"] || $params["show_past"] );
  $tpl->Assign( "show_past_and_future", $params["show_future"] && $params["show_past"] );

  // "order"
  if ($params["order"] == "tag") $order = "tag ASC"; else
  if ($params["order"] == "subject") { $params["subject"]=1; $order = "subject ASC"; } else
  if ($params["order"] == "time") $order = "created_datetime DESC"; else
  $order = "relev DESC";

  // "topic", "title"
  if ($params["topic"] == "1" || $params["title"] == "1") $title=1;
  if ($_REQUEST["_topic"] == "on") $title=1;

  // "filter"
  if (!isset($params["filter"])) $params["filter"] = "all";

  // "form"
  $form = 1;
  if (isset($params["form"]) &&
      ($params["form"] == "0")
       || (strtolower($params["form"]) == "none")
       || (strtolower($params["form"]) == "no")) $form = 0;

  // "for"
  if ($params["action_as_handler"] && !isset($params["for"])) $params["for"] = $params[0];
  if ($_REQUEST["_phrase"]) $params["for"]=$_REQUEST["_phrase"];

  // do search
  if (isset($params["for"]))
  {
    $do_search = 1;
    $phrase = $params["for"];
    $debug->Trace("<h1>PHRASE</h1>".$phrase);
    $records = array();
    $record_ids = array();

    if ($params["where"] == "node") $where = ""; else
    {
      $data = &$this->Load(2);
      $where = " AND r.user_id=".$db->Quote($data["user_id"]);
    }

    if ($params["filter"] == "posts")     $where.=" AND r.type=".$db->Quote(RECORD_POST);
    if ($params["filter"] == "documents") $where.=" AND r.type=".$db->Quote(RECORD_DOCUMENT);
    if ($params["filter"] == "announces") $where.=" AND r.is_announce>0 AND r.type=".$db->Quote(RECORD_POST);
        
    // alpha. check if we can use "IN BOOLEAN MODE"
    $rs = $db->Execute("SELECT VERSION() as v");
    if ($rs->fields["v"]{0}=="4") $extended = true;
    else $extended = false;
    
   //we do not need to search in records if filter==comments
   if ($params["filter"] != "comments") 
   {
     // 1. compose & run sql
     if (!$title)
     {
       $sql = "SELECT record_id, ".
              "match(body) against (".$db->Quote($phrase).($extended?" IN BOOLEAN MODE":"").") as relev ".
              " FROM ".
               $rh->db_prefix."records as r".
              " WHERE (".
              "match(body) against (".$db->Quote($phrase).($extended?" IN BOOLEAN MODE":"").")) ".
               $where.
              " ORDER BY ".$order;

      $debug->Trace($sql);
      $rs = $db->Execute( $sql );
      $a = $rs->GetArray();
     }

     // 1a. compose & run sql2
     $title_phrase = trim($phrase);
     $title_phrase = preg_replace("/[~\%\+\-\"><\(\)\*]/", "", $title_phrase);

     $sql = "SELECT record_id, 1 as relev FROM ".$rh->db_prefix."records as r".
            " WHERE ( lower(tag) like lower(".
             $db->Quote("%".$title_phrase."%").") or lower(subject) like lower(".
             $db->Quote("%".$title_phrase."%")."))".
             $where.
            " ORDER BY ".$order;

    $debug->Trace($sql);
    $rs = $db->Execute( $sql );
    $b = $rs->GetArray();

    if (is_array($b))
     foreach( $b as $k=>$v ) $records[$v["record_id"]] = $db->Quote( $v["record_id"] );
    if (is_array($a))
     foreach( $a as $k=>$v ) $records[$v["record_id"]] = $db->Quote( $v["record_id"] );
   
     unset($a); unset($b);  

   } //end of records search

   // OMG! We need to search in comments
   if ($params["filter"] == "all" || $params["filter"] == "comments")
   {
     if ($params["order"] == "tag") $_order = "subject ASC"; else
     $_order = $order;

     // 1. compose & run sql
     if (!$title)
     {
       $sql = "SELECT comment_id, r.record_id, ".
              "match(c.body_post) against (".$db->Quote($phrase).($extended?" IN BOOLEAN MODE":"").") as relev ".
              " FROM ".
               $rh->db_prefix."comments as c, ".$rh->db_prefix."records as r".
              " WHERE (".
              "match(c.body_post) against (".$db->Quote($phrase).($extended?" IN BOOLEAN MODE":"").")) ".
              " AND r.record_id = c.record_id ".
               $where.
              " ORDER BY ".$_order;

      $debug->Trace($sql);
      $rs = $db->Execute( $sql );
      $a = $rs->GetArray();
     }

     // 1a. compose & run sql2
     $title_phrase = trim($phrase);
     $title_phrase = preg_replace("/[~\%\+\-\"><\(\)\*]/", "", $title_phrase);

     $sql = "SELECT comment_id, r.record_id, 1 as relev FROM ".
               $rh->db_prefix."comments as c, ".$rh->db_prefix."records as r".
            " WHERE ( lower(c.subject) like lower(".
             $db->Quote("%".$title_phrase."%").")) ".
            " AND r.record_id = c.record_id ".
             $where.
            " ORDER BY ".$_order;
    
     $debug->Trace($sql);
     $rs = $db->Execute( $sql );
     $b = $rs->GetArray();

     if (is_array($b))
      foreach( $b as $k=>$v ) 
      {
        if (!$records[$v["record_id"]]) $comments[$v["record_id"]] = $v["comment_id"];
        $records[$v["record_id"]] = $db->Quote( $v["record_id"] );
      }
     if (is_array($a))
      foreach( $a as $k=>$v ) 
      {
        if (!$records[$v["record_id"]]) $comments[$v["record_id"]] = $v["comment_id"];
        $records[$v["record_id"]] = $db->Quote( $v["record_id"] );
      }
      
   }//end of comments search

  $debug->Trace_R($ids);

  if (sizeof($records) > 0)
  {
    // 2. get bodies & stuff
    $sql = "select type, group1, group2, group3, group4, record_id, record_id as id, user_id ".
           " from ".$rh->db_prefix."records ".
           " where record_id in (".implode(",",$records).") ";
    $rs = $db->Execute( $sql );
    $data = $rs->GetArray();

    // 3. filter them
    $found = 0; $resdata = array();
    foreach ($data as $fields)
    {
      $debug->Trace(" found ". $fields["record_id"] );
      $cache->Store( "record", $fields["record_id"], 1, $fields );

      if ((sizeof($record_ids)<50) 
          &&
          ($principal->IsGrantedTo(  $this->security_handlers[$fields["type"]],
                                    "record", $fields["record_id"]))
         )
           $record_ids[] = $fields["record_id"];
      else unset($records[$fields["id"]]);

    }

    // -- теперь у нас в record_ids -- ВЕСЬ список доступных записей
    $rh->UseClass("Arrows", $rh->core_dir);
    $arrows = &new Arrows( &$state, sizeof($record_ids), "", $page_size, $page_frame_size );
    $arrows->Parse( "actions/_arrows.html", "MVC-ARROWS"  );
    $offset = $arrows->GetSqlOffset(); if ($offset<0) $offset = 0;
    $limit  = $arrows->GetSqlLimit();  if ($limit <0) $limit  = sizeof($record_ids);
    $record_ids = array_slice( $record_ids, $offset, $limit );

    // а теперь получаем бодисы
    $order_issue =  "record_id";
    $pagesize    =  $limit;
    $uactn = &$this->rh->UtilityAction(); // actions теперь живут в отдельном классе.
    $data = $uactn->GetRecordBodies ( &$record_ids, $params, 0, false, $order_issue, $pagesize ); // suspicious ?????

    // нам нужно их пересортировать!
    foreach ($data as $k=>$v)
      if (isset($records[$v["record_id"]]))
        $records[$v["record_id"]] = $v;


    // cпецифические ништяки
    foreach($records as $k=>$fields)
    {
      $fields["_empty_tag"] = 0;
      // empty tag
      if ($fields["tag"] == "")
      {
        $fields["_empty_tag"] = 1;
        $fields["subject"] = $tpl->message_set["JournalHomePage"]." ".rtrim($fields["supertag"],":");
      }
      $fields["datetime"] = $fields["user_datetime"]; // наша главная дата -- которая пользователем задана
      if ($comments[$k]) $fields["comment_href"] = "/comments#comment".$comments[$k];
      $records[$k] = $fields;
    }
  }

  $data = $records;


  // ----- завершение первой стадии -----------------------

  // преформат полей и в RSS сразу (стадия 2,3) -----------
  foreach ($data as $k=>$item)
  {
    $data[$k] = $object->_PreparseArray( &$data[$k] );
    if ($rh->rss) $rh->rss->AddEntry( &$data[$k], RSS_SEARCH );
  }

  // вызов алгоритма вывода (стадия 4) ----
  if (sizeof($data) == 0)
  {
    // кажется, здесь нужно придумать, что бы такого выводить, когда пусто
    // !!! -> messageset
    $result = "Ничего не найдено";
  }
  else
  $result = $object->_ActionOutput( &$data, &$params, "list" );

  //$debug->Trace_R( $data );
  //$debug->Error( $result );
 }

 // 4. parse FORM (продолжение стадии 4)
 $tpl->LoadDomain( array(
    "checked2" => (($params["where"] == "node")?"checked":""),
    "checked" => ($title?"checked":""),
    "phrase" => htmlspecialchars($phrase),
    "Form:Search"    => $state->FormStart( MSS_GET, $this->_NpjAddressToUrl( $object->npj_object_address )."/".$script_name , "name=\"search_form\""),
    "/Form"          => $state->FormEnd(),
 ));


  if ($params["style"] == "context") $form_add = "_context";
  if ($params["style"] == "context") if ($form) $do_search=0;


  return ($form?$tpl->Parse( "actions/search.html:Form".$form_add):"").
         ($do_search?$result:"");

?>