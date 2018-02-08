<?php
/*
    {{facet 
       [keywords="WackoWiki Test WebDesign"] 
       [style="br|ol|ul|indent"] 
       [operator="or|and"] 
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

  $templates = array( "br", "ul", "ol", "indent" );
  if (!isset($params["style"])) $params["style"] = "ul";
  if (!isset($params[0])) $params[0] = $this->npj_object_address;
  if ($params["operator"] == "or") { $op = " OR "; $op1 = "0"; }
                              else { $op = " AND ";$op1 = "1"; }
  if ($params["filter"] == "announces") $filter = "=1 and is_announce>0"; else
  if ($params["filter"] == "announces-documents") $filter = "=1 and is_announce=2"; else
  if ($params["filter"] == "events") $filter = "=1 and is_announce=1"; else
  if ($params["filter"] == "posts") $filter = "=1"; else
  if ($params["filter"] == "documents") $filter = "=2"; else
                                        $filter = ">0";

  if ($params["order"] == "tag") $order = "tag ASC"; else
  if ($params["order"] == "subject") { $params["subject"]=1; $order = "subject ASC"; } else
  if ($params["order"] == "user")     $order = "user_datetime DESC";     else
  if ($params["order"] == "server")   $order = "created_datetime DESC";  else
  if ($params["order"] == "created")  $order = "created_datetime DESC";  else
                                      $order = "edited_datetime DESC"; 
  if (!isset($params["subject"])) $params["subject"] = 1;

  if (!isset($params["limit"])) $params["limit"] = $principal->data["_recentchanges_size"];
  if (!isset($params["max"])) $params["max"] = $params["limit"];

  // 1. split & unwrap keywords ==================================================================
  $tablecount=0; $fields = ""; $tables = "";
  $keywords = explode(" ", preg_replace("/[\s\n,\.;\t]+/", " ", $params[0]));
  $unique = array(); $sql = ""; $f=0; 
  foreach( $keywords as $keyword )
  if (!isset($unique[$keyword]))
  {
    $unique[$keyword] = 1; $supertag1 = $rh->account->_UnwrapNpjAddress( $keyword );
    $rs = $db->Execute("select record_id, type from ".$rh->db_prefix."records where is_keyword=1 and type=2 and supertag=".$db->Quote($supertag1));
    if ($rs->RecordCount() > 0)
    {
      if (($op1 == "1") ||
          ($tablecount==0)) 
      { $tables.= ", ".$rh->db_prefix."records_ref as ref".$tablecount;
        $_tablecount = $tablecount;
      }

      $fields.= $op." (r.record_id=ref".$_tablecount.".record_id and ref".
                                        $_tablecount.".keyword_id=".$db->Quote( $rs->fields["record_id"] ).") ";
      if ($op1 == "1")  $tablecount++;
      else $tablecount = 1;
    }
  }
  if ($tablecount == 0) return $this->Action( "_404", array("forbidden"=>1), &$principal );

  // 2. compose & run sql =======================================================================
  //    * ??? need copy-paste patch from CHANGES or FEED
  $sql = "SELECT distinct ".
         " r.record_id, r.record_id as id, r.subject, r.tag, r.supertag, ".
         " r.user_id, r.edited_user_name, r.edited_user_login, r.edited_user_node_id, ".
         " r.created_datetime, r.edited_datetime, r.user_datetime, ".
         " r.body_post, ".
         " r.number_comments, r.disallow_comments, ".
         " r.group1, r.group2, r.group3, r.group4, ".
         " r.type, r.is_digest, ".
         " r.version_tag, r.is_parent, r.depth, r.disallow_replicate, ".
         " r.pic_id ".
         " FROM ".
         $rh->db_prefix."records as r".$tables." WHERE r.type".$filter." AND (".$op1." ".$fields.")".
         " ORDER BY ".$order;
//  if ($op1 == "0") $debug->Error( $sql );
  $rs = $db->Execute( $sql );

  // 3. filter them out =========================================================================
  $found = 0; $data = array();
  while (!$rs->EOF)
  {
    $fields = $rs->fields;
    $cache->Store( "record", $fields["record_id"], 1, &$fields ); 
      if ($principal->IsGrantedTo(  $this->security_handlers[$fields["type"]], 
                                    "record", $fields["record_id"]))
    {  $found++;
       if ($fields["tag"] == "") 
         $fields["tag"] = $tpl->message_set["JournalHomePage"];
       $fields["datetime"] = $fields["edited_datetime"]; // наша главная дата -- которая едитед
       $data[] = $fields;
       if ($found >= $params["max"]) break;
    }
    $rs->MoveNext();
  }

  // ----- завершение первой стадии -----------------------

  // преформат полей и в RSS сразу (стадия 2,3) -----------
  foreach ($data as $k=>$item)
  {
    $data[$k] = $object->_PreparseArray( &$data[$k] );
    if ($rh->rss) $rh->rss->AddEntry( &$data[$k], RSS_FACET );
  }
  
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