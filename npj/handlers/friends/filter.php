<?php


  // вызвать {{Feed type="corr" groups="groups"}}

  $groups = array();
  foreach( $_GET as $k=>$v )
  if (strpos($k, "group_") === 0)
   $groups[] = 1*substr($k,6);
  $groups = implode(",", $groups);

  if ($_GET["_default"] && ($groups != ""))
  {
    $db->Execute("update ".$rh->db_prefix."groups set is_default=0 where user_id=".
                 $db->Quote($rh->account->data["user_id"]));
    $db->Execute("update ".$rh->db_prefix."groups set is_default=1 where group_id in (".$groups.")");
  }

  $_params = array(
             "type"   => "corr",
             "groups" => $groups,
             "style"  => "friends",
                 );

   $result = $this->Action( "feed", &$_params, &$principal );

   $tpl->Assign("Preparsed:CONTENT",  $result );
   $tpl->Assign("Preparsed:TITLE",    "Лента корреспондентов, выборочно" ); // !!! to messageset
   $tpl->Assign("Preparsed:COMMENTS", ""); 

   $tpl->Assign("NoRecordStats", 2);


?>