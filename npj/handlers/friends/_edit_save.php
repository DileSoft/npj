<?php

 // подчасть edit.php

 // ДЛЯ АККАУНТА:
 // есть $_POST["add_0_XX"],  где ХХ -- идшники пользователей, для включения в конфиденты
 // есть $_POST["add_1_XX"],  где ХХ -- идшники пользователей, для включения в корреспонденты
 // есть $_POST["delete_XX"], где ХХ -- идшники пользователей, для исключения из групп
 // есть $groups[ group_rank ][ group_id ], где есть список системных групп
 //  в число системных групп не входит GROUPS_COMMUNITIES, значит её пучить для аккаунта не надо
 // есть $account -- тот, кому правим друзей
 // есть $data -- данные соответственно юзера и аккаунта

 // ДЛЯ СООБЩЕСТВА:
 // есть $_POST["set_XX"],  где ХХ -- идшники пользователей, для выбора ранга группы
 //         = -1 -- значит удалять.
 // есть $groups[ group_rank ][ group_id ], где есть список системных групп
 // есть $account -- тот, кому правим друзей
 // есть $data -- данные соответственно юзера и аккаунта

 // шаг 1. удаляем всё старое

 $old = array();
 foreach($groups as $group_rank) foreach($group_rank as $id=>$group) 
  if (is_numeric($id)) $old[] = $id;
 if (sizeof($old) > 0)
  $db->Execute( "delete from ".$rh->db_prefix."user_groups where group_id in (".implode(",", $old).")" );

 // присоединить нужные сообщения
 $tpl->MergeMessageSet( $rh->message_set."_member_state" );

 // шаг 2. записываем всё новое в БД
 $sql = "insert into ".$rh->db_prefix."user_groups (group_id, user_id, keyword_id) VALUES "; $f=0;
 $f=0;
 foreach( $_POST as $key=>$value )
 if ($value != "")
 {
  // ДЛЯ АККАУНТА:
  if (preg_match( "/^add_([0-1])_([0-9]+)$/i", $key, $matches ))
   if (!isset( $_POST["delete_".$matches[2]] ))
   {
    if ($f) $sql.=", "; else $f=1;
    $rs = $db->SelectLimit("select u.user_name, u.login, u.node_id, ".
                           "record_id from ".$rh->db_prefix."records as r, ".$rh->db_prefix."users as u where ".
                           "r.user_id=u.user_id and ".
                 "r.supertag = CONCAT(u.login,".$db->Quote("@").
                 ($rh->account->data["node_id"]==$rh->node_name?
                      ",u.node_id,":
                      ",u.node_id,".$db->Quote("/".$rh->node_name).",")
                 .$db->Quote(":").
                 ") and u.user_id=".$db->Quote($matches[2]),   1);
    $sql.="(".$old[$matches[1]].", ".$db->Quote($matches[2]).", ".
          $db->Quote($rs->fields["record_id"]?$rs->fields["record_id"]:0).")";
   }
  // ДЛЯ СООБЩЕСТВ:
  if (preg_match( "/^set_([0-9]+)$/i", $key, $matches ))
   if ($_POST[$matches[0]] >= 0 )
   {
    if ($f) $sql.=", "; else $f=1;

    foreach($groups[$_POST[$matches[0]]] as $group_id=>$group_val) break;

    $rs = $db->SelectLimit("select u.user_id, u.login as login, u.node_id as node_id, ".
                           "record_id from ".$rh->db_prefix."records as r, ".$rh->db_prefix."users as u where ".
                           "r.user_id=u.user_id and ".
                 "r.supertag = CONCAT(u.login,".$db->Quote("@").
                 ($rh->account->data["node_id"]==$rh->node_name?
                      ",u.node_id,":
                      ",u.node_id,".$db->Quote("/".$rh->node_name).",")
                 .$db->Quote(":").
                 ") and u.user_id=".$db->Quote($matches[1]),   1);
    $sql.="(".$db->Quote($group_id).", ".$db->Quote($matches[1]).", ".
          $db->Quote($rs->fields["record_id"]?$rs->fields["record_id"]:0).")";

    $_rank = $_POST[$matches[0]];

    if (!$users[ $rs->fields["user_id"] ][ "state.".$_POST[$matches[0]] ])
     $this->Handler("_community_add_notify", 
                    array($_rank,  $rs->fields["login"]."@".$rs->fields["node_id"] ), &$principal );
    $users[ $rs->fields["user_id"] ][ "done" ] = 1;
    $users[ $rs->fields["user_id"] ][ "new_rank" ] = $_rank;
  }
  // --------------
 }
 if ($f) // записываем, только если есть повод
 $db->Execute($sql);

 // 3. удаляем все записи о пользователях, записанные в несистемные группы, если его нет в системных
 //  ??? subject to refactor
 foreach( $users as $user )
 {
   $rs = $db->Execute("select group_rank from ".$rh->db_prefix."user_groups as ug, ".$rh->db_prefix."groups as g ".
                          "where g.group_id = ug.group_id and is_system=1 ".
                          " and ug.user_id = ".$db->Quote( $user["id"] ).
                          " and g.user_id = ".$db->Quote( $data["user_id"] ) );
   $a = $rs->GetArray(); $gr = array(-185);
   foreach( $a as $item ) $gr[] = $item["group_rank"];
   $rs = $db->Execute("select ug_id from ".$rh->db_prefix."user_groups as ug, ".$rh->db_prefix."groups as g ".
                          "where g.group_id = ug.group_id and is_system=0 ".
                          " and ug.user_id = ".$db->Quote( $user["id"] ).
                          " and group_rank not in (". implode(",",$gr) .")".
                          " and g.user_id = ".$db->Quote( $data["user_id"] ) );
   $a = $rs->GetArray(); $gr2 = array(-185);
   foreach( $a as $item ) $gr2[] = $item["ug_id"];
   $db->Execute("delete from ".$rh->db_prefix."user_groups where ug_id in (". implode(",",$gr2) .")");

   // Работа с группой "Я состою в сообществах:" ------------------------------------------------------------------------------------------
   if (($user["new_rank"] > 0) && ($user["done"]))
   {
     // у пользователя какой-то весомый ранг в сообществе, нужно записать что он состоит в сообществе в спецгруппу-9
     $rs = $db->Execute( "select  ug.group_id from ".$rh->db_prefix."groups as g, ".$rh->db_prefix."user_groups as ug where ".
                         " ug.group_id=g.group_id and g.user_id=".$db->Quote( $user["id"] ).
                         " and g.group_rank = ".$db->Quote(GROUPS_COMMUNITIES).
                         " and ug.user_id = ".$db->Quote($data["user_id"]) );
     $a = $rs->GetArray(); $g = array(-185);
     foreach($a as $v) $g[] = $v["group_id"];
     $rs = $db->Execute("select g.group_id from ".$rh->db_prefix."groups as g ".
                        " where g.user_id=".$db->Quote( $user["id"] ).
                        " and g.group_id not in (".implode(",",$g).") and g.group_rank = ".$db->Quote(GROUPS_COMMUNITIES) );
     if ($rs->RecordCount() > 0) // записываем, если он ещё туда не попал
     {
       $group_communities = 1*$rs->fields["group_id"];
       $rs = $db->Execute( "select record_id from ".$rh->db_prefix."records where user_id = ".$db->Quote($data["user_id"]) .
                           " and supertag = ".$db->Quote($data["login"]."@".$data["node_id"].":") );
       $root_id = 1*$rs->fields["record_id"];
       $debug->Trace(" filling for $group_communities, record = $root_id " );
       $db->Execute( "insert into ".$rh->db_prefix."user_groups (group_id, user_id, keyword_id ) VALUES (".
                      $db->Quote($group_communities).", ".
                      $db->Quote($account->data["user_id"]).", ".
                      $db->Quote($root_id).")"                    );
     }
   }
   else
   {
     // мы удалили этого пользователя, надо почистить ему внутреннюю группу "сообщества"
     $rs = $db->Execute( "select group_id from ".$rh->db_prefix."groups where is_system=1 and group_rank=".GROUPS_COMMUNITIES.
                         " and user_id=".$db->Quote( $user["id"] ) );
     $group_communities = 1*$rs->fields["group_id"];
     $db->Execute( "delete from ".$rh->db_prefix."user_groups where user_id = ".$db->Quote($account->data["user_id"]).
                 " and group_id = ".$db->Quote($group_communities) );
   }
   // ========================================== ------------------------------------------------------------------------------------------
 }

 // оповещаем также и всех удалённых
 if ($data["account_type"] > ACCOUNT_USER) 
 foreach( $users as $item )
  if (!$item["done"])
   $this->Handler("_community_add_notify", 
                  array(-1,  $rs->fields["login"]."@".$rs->fields["node_id"] ), &$principal );

 $rh->Redirect( $rh->Href( $this->_NpjAddressToUrl( $account->name.":friends/edit/done" ) 
                            , IGNORE_STATE ) , IGNORE_STATE );
?>
