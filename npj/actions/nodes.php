<?php
/*
   {{nodes
   }}

*/

//  $debug->Trace_R( $params );
//  $debug->Error( $script_name );
  $rh->UseClass( "ListObject", $rh->core_dir );

// 1. select nodes query
 $sql = "SELECT
          title,
              node_id,
              url,
              is_local,
              created_datetime
        FROM ".
          $rh->db_prefix."nodes WHERE 1";
 $rs = $db->Execute( $sql );

$data =$rs->GetArray();
//$debug->Error_R($data);

// 2. prepare result
foreach ($data as $k=>$v)
{
  //npj-style link
  $data[$k]["Link:node"] = $this->Link("@".$data[$k]["node_id"]);

  // date formatting
  // tested with real value '1999-01-05 12:05' except '0000-00-00 00:00';
  $data[$k]["timestamp"] = strtotime($data[$k]["created_datetime"]);
  $data[$k]["created_datetime"] =  date('H:i', $data[$k]["timestamp"]).
     ' <strong>'.date('d.m.Y', $data[$k]["timestamp"]).'</strong>';

  // drop trail-slash
  $thisURL= $data[$k]["url"];
  $data[$k]["_url"] = (substr($thisURL, strlen($thisURL)-1, 1)=='/')?substr($thisURL, 0, strlen($thisURL)-1):$thisURL;
}
// $debug->Error_R($data);

// 3. choose template
   $tplt = "List_td";

   // 4. parse
  $list = &new ListObject( &$rh, &$data );
//  $tpl->GetValue("Action:Name");
  return $list->Parse("actions/nodes.html:".$tplt);

?>