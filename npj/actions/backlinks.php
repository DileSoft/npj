<?php

// {{backlinks [for="WackoWiki"] [style="br|ol|ul"] [filter="posts|documents|both"] 
//         [order="tag|subject"] }}
//
//  $debug->Trace_R( $params );
//  $debug->Error( $script_name );
  $rh->UseClass( "ListObject", $rh->core_dir );

  $templates = array( "br", "ul", "ol" );
  if (!isset($params["style"])) $params["style"] = "ul";
  if (!isset($params[0])) $params[0] = $this->npj_object_address;

  if ($params["order"] == "tag") $order = "tag ASC"; else
  { $params["subject"]=1; $order = "subject ASC"; }

  if ($params["filter"] == "posts") $filter = "=1"; else
  if ($params["filter"] == "documents") $filter = "=2"; else
                                        $filter = ">0";
  if ($params["topic"] == "1" || $params["title"] == "1") $title=1;

 // 1. compose & run sql
 $cur_data = $this->_Load($this->npj_object_address, 1);

 $sql = "SELECT r.type, r.edited_datetime, r.edited_user_login, r.edited_user_name, r.edited_user_node_id, ".
        "r.depth, r.record_id as id, r.record_id, r.subject, r.tag, r.supertag, r.user_id, r.version_tag FROM ".
        $rh->db_prefix."records as r, ".$rh->db_prefix."links as l  WHERE r.type".$filter." AND ".
        "l.to_id=".$db->Quote($cur_data["record_id"])." AND l.from_id=r.record_id".
        " ORDER BY ".$order;
 //$debug->Error("sql:".$sql);
 $rs = $db->Execute( $sql );

 // 2. filter them out
 $found = 0; $data = array(); $hash = array();
 $letters = array();
 while (!$rs->EOF)
 {
    $cache->Store( "record", $rs->fields["record_id"], 1, $rs->fields ); 
      if ($principal->IsGrantedTo(  $this->security_handlers[$rs->fields["type"]], 
                                    "record", $rs->fields["record_id"]))
   {  $found++;
      $debug->Trace_R( $rs->fields );
      $rs->fields["Link:tag"]    = $this->Href( $this->GetFullTag( $rs->fields["tag"], $rs->fields["supertag"] ), NPJ_RELATIVE  );
      if ($params["subject"]) $rs->fields["title"] = $rs->fields["subject"];
      else $rs->fields["title"] = $rs->fields["tag"];
      if ($rs->fields["title"] == "") $rs->fields["title"] = $rs->fields["supertag"];
      $data[] = $rs->fields;
   }
   $rs->MoveNext();
 }

 // 3. choose template
 foreach( $templates as $k=>$v )
  if (($params["style"] == $k) || ($params["style"] == $v))
   $tplt = "List_".$v; 

  // 4. parse 
  
  $tpl->Assign("Childs", "");
  $list = &new ListObject( &$rh, &$data );
  return $list->Parse("actions/search.html:".$tplt);

?>