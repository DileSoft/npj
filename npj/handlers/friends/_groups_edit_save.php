<?php


  // already have:   $groups[ is_system ][ group_id ]

 // #1. find new groups, groups to delete, update names
 $grps = explode("|", $_POST["groups_names"]);
 $new_groups = array();
 $del_groups = $groups[0];
 for( $i=0; $i<sizeof($grps); $i+=2 )
 if ($i+1 == sizeof($grps)) break; else
 if ($grps[$i+1] != "")
 {
   $name = htmlspecialchars($grps[$i+1]);
   if ($grps[$i] == "") $new_groups[] = $name;
   else
   {
     unset($del_groups[ $grps[$i] ]);
     $groups[0][ $grps[$i] ]["_present"] = 1;
     if ($groups[0][ $grps[$i] ]["text"] != $name)
     {
       $groups[0][ $grps[$i] ]["text"] = $name;
       $groups[0][ $grps[$i] ]["_rename"] = 1;
     }
   }
 }
 // #1a. hash users
 $usrs = explode("|", $_POST["users_groups"]);
 $hash = array();
 for( $i=0; $i<sizeof($usrs); $i+=2 )
 if ($i+1 == sizeof($usrs)) break; else
 if ($usrs[$i+1])
 {
   $list = explode(",",trim($usrs[$i+1],","));
   if (sizeof($list) > 0)
     $hash[$usrs[$i]] = $list;
 }
 // #2. insert new groups, fill $groups
 if (sizeof($new_groups) > 0)
 {
   foreach($new_groups as $g)
   { 
     $db->Execute("insert into ".$rh->db_prefix."groups ".
        "( group_name, user_id, group_rank, is_system ) ".
        " VALUES ".
        "( ".$db->Quote($g).", ".
             $db->Quote($data["user_id"]).", ".
             $db->Quote($rank).", 0)");
     $id = $db->Insert_ID();
     $groups[0][ $id ] = array( "href"=>$id, "text"=>$g );
     if (isset($hash[ "newg_".$g ])) 
     { $hash[ $id ] = $hash[ "newg_".$g ];
       unset( $hash[ "newg_".$g ] );
     }
   }
 }
 // #3. remove groups
 if (sizeof($del_groups) > 0)
 { $list = array(); 
   foreach( $del_groups as $g ) { $list[] = $db->Quote($g["href"]); unset($hash[$g["href"]]); }
   $list = implode(",", $list);
   $db->Execute("delete from ".$rh->db_prefix."user_groups where group_id in (".$list.")");
   $db->Execute("delete from ".$rh->db_prefix."groups where group_id in (".$list.")");
 }
 // #4. rename groups
 { $list = array(); 
   foreach( $groups[0] as $g ) 
    if ($g["_rename"]) 
   {
     $db->Execute("update ".$rh->db_prefix."groups set group_name=".
                  $db->Quote($g["text"])." where group_id=".$db->Quote($g["href"]));
   }
 }
 // #5. remove users of "_present" groups
 { $_list = array(); foreach( $groups[0] as $g )  $_list[] = $db->Quote($g["href"]); 
   $list = implode(",", $_list);
   if (sizeof($_list) > 0)
   $db->Execute("delete from ".$rh->db_prefix."user_groups where group_id in (".$list.")");
 }
 // #6. insert new users
 {
   $sql=""; $f=0;
   foreach ($hash as $g=>$list)
    foreach($list as $user)
    { 
      $rs = $db->SelectLimit("select user_id, record_id from ".$rh->db_prefix."records where ".
                           $db->Quote($user.":")." = supertag", 1);
      if ($rs->RecordCount() > 0)
      { if ($f) $sql.=","; else $f=1;
        $sql.="(".$db->Quote($g).",".$db->Quote($rs->fields["user_id"]).",".$db->Quote($rs->fields["record_id"]).")";
      }
    }
   if ($sql != "")
    $db->Execute("insert into ".$rh->db_prefix."user_groups (group_id, user_id, keyword_id) VALUES ".$sql);
 }

 $rh->Redirect( $rh->Href( $this->_NpjAddressToUrl( $account->name.":friends/groups/edit/ok" )
                            , IGNORE_STATE ) , IGNORE_STATE );

?>