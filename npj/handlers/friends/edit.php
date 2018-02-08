<?php
 function friends_edit_sort ($a, $b) { if ($a["npj"] == $b["npj"]) return 0;  return ($a["npj"] < $b["npj"]) ? -1 : 1; }


 $account = &$rh->account;
 if (!$account->HasAccess( &$principal, "owner" ))
  if (!$account->HasAccess( &$principal, "rank_greater", GROUPS_MODERATORS )) 
   if (!$account->HasAccess( &$principal, "acl_text", $rh->node_admins ) || ($account->npj_account != $rh->node_user))
    $params["readonly"] = 1;

 $tplt = "friends.edit.html";
 if ($params["readonly"] == 1)
 {  $tplt = "friends.view.html";
    $readonly = 1; 
    $tpl->Assign("is_moderator", 1);
    if (!$account->HasAccess( &$principal, "owner" ))
     if (!$account->HasAccess( &$principal, "rank_greater", GROUPS_MODERATORS )) 
      $tpl->Assign("is_moderator", 0);
 }


 // получаем данные аккаунта и тестируем доступ
 $account = $rh->account;
 $data = $account->Load(2);
// if (!$account->HasAccess( &$principal, "owner" )) return $this->Forbidden("FriendsEdit");

 // получить список системных групп
 $rs = $db->Execute( "select is_default, is_system, group_id, group_name, group_rank from ".$rh->db_prefix."groups where user_id=".
                      $db->Quote($data["user_id"])." and is_system=1 and group_rank<".$db->Quote(GROUPS_SELF).
                      ($readonly?" and group_rank>0 ":"").
                      " order by group_rank" );
 // рассортировать его по массиву $groups[ group_rank ][ group_id ]
 $a = $rs->GetArray(); $groups = array();

 $group_ranks = $rh->group_ranks[ $data["account_type"] ];
 $rank_names2 = array();
 foreach( $rh->group_ranks[ $data["account_type"] ] as $rank=>$nick)
   $rank_names2[$rank] = $tpl->message_set["FriendsNames.Short"][$nick];
 if ($readonly && ($account->data["account_type"] > 0))
 { unset($rank_names2[0]); }

 foreach( $a as $item )
 if (isset($rank_names2[$item["group_rank"]]))
 {
   if (isset($groups[$item["group_rank"]])) $groups[$item["group_rank"]] = array();
   $groups[$item["group_rank"]][$item["group_id"]] = $item;
   $groups[$item["group_rank"]]["text"] = $rank_names2[$item["group_rank"]];
 }

 $debug->Trace_R( $rank_names2 );
 $debug->Trace_R( $groups );

 // изменение вхождений в друзь€
 if ($params[0] == "done")
   return include( $rh->handlers_dir."friends/_edit_ok.php" );

 // получить вхождение пользователей в каждую группу, поставить пометки
 $users = array(); $c=0;
 foreach ($groups as $rank=>$group_rank )
 foreach ($group_rank as $id=>$group)
 if (is_numeric($id))
 { 
   $rs = $db->Execute( "select u.user_name, u.login, u.node_id, u.alive, u.user_id from ".$rh->db_prefix."users as u,".
            $rh->db_prefix."user_groups as ug where u.user_id=ug.user_id and ug.group_id=".$db->Quote($id));
   $a = $rs->GetArray();
   $debug->Trace( "<b>$id, $rank</b> = ");
   foreach($a as $item)
   {
     if (!isset($users[ $item["user_id"] ]))
     {
       $debug->Trace( $rank. ", ". $group_ranks[$rank]. ", ". $item["user_name"] );
       $users[ $item["user_id"] ] =  
         array(
          "id" => $item["user_id"],
          "npj" => $item["login"]."@".$item["node_id"],
          "default" => $rank,
          "user_name" => $item["user_name"],
          "Link:user" => $this->Link( $item["login"]."@".$item["node_id"] ),
              );
       foreach ($rank_names2 as $_rank=>$_name)
         $users[ $item["user_id"] ][ "state.".$_rank ] = "";
       $users[ $item["user_id"] ][ "state0" ] = "";
       $users[ $item["user_id"] ][ "state1" ] = "";
       $users[ $item["user_id"] ][ "set0" ] = "";
       $users[ $item["user_id"] ][ "set1" ] = "";
     }

     $users[ $item["user_id"] ][ "set".$c ] = "on";
     $users[ $item["user_id"] ][ "state".$c ] = "CHECKED";
     $users[ $item["user_id"] ][ "state.".$rank] = "CHECKED";
     $users[ $item["user_id"] ][ "show" ] = $users[ $item["user_id"] ][ "show" ] ||
          ($params[0]?(($group_ranks[$rank] == $params[0])?1:0):1);

   }
   $c++;
 }

// $debug->Trace_R( $users );
// $debug->Trace_R( $group_ranks );
// $debug->Error( $params[0].", ".$rank );
 // обработка действи€
 if ($_POST["do"])
   return include( $rh->handlers_dir."friends/_edit_save.php" );
 
 // пересортировать уродцев по логину
 $sorted = array();
 foreach($users as $user) $sorted[] = $user;
 usort ($sorted, "friends_edit_sort"); 

// $debug->Trace_R( $groups );
// $debug->Trace_R( $users );
// $debug->Error("here");


 // основные параметры дл€ последующих парсингов
 $tpl->LoadDomain( array(
    "ButtonColSpan"  => $c+1*(!$readonly)-($account->data["account_type"]*$readonly),
    "Form:Groups"    => $state->FormStart( MSS_POST, $this->_NpjAddressToUrl( $account->name.":friends/edit"), "name=\"the_form\""),
    "/Form"          => $state->FormEnd(),
                 )      );

 // дл€ парсинга нам необходимо:
 // 0. ListSimple-список типов групп
 //    * это уже получилс€ $groups
 // 1. ListObject-список пользователей, €вл€ющихс€ членами хот€ бы одной системной группы
 //    * npj, href, user_name, "CHECKED"/"" дл€ каждой системной группы
 //    * это уже получилс€ $users
 // 2. в дальнейшем ещЄ что-то подобное по сообществам !!!!

 // парсинг
 $tpl->theme = $rh->theme;
    
   // GroupSelect -- фильтр
   if (!$readonly) 
   {
     $rank_names = array( "" => $tpl->message_set["FriendsNames"]["all"] );
     foreach ($group_ranks as $group_nick) $rank_names[$group_nick] = $tpl->message_set["FriendsNames"][$group_nick];
     $ranks = &new ListCurrent( &$rh, &$rank_names, NULL, $params[0] );
     $ranks->Parse( $tplt.":Rank", "GroupRanks" );
   }

   // списки
   $title = &new ListSimple( &$rh, &$groups );
   $title->Parse( $tplt.":Title", "TitleTD" );
   $rh->UseClass( "ListObject", $rh->core_dir );
   $userlist = &new ListObject( &$rh, &$sorted );
   $userlist->Parse( $tplt.":UserList".$data["account_type"], "UserList" );

   // ќсновной парсинг
   $tpl->Parse( $tplt.":Main".$data["account_type"], "Preparsed:CONTENT" );
   $tpl->Assign( "Preparsed:TITLE", $tpl->message_set["Title.Friends/Edit".$data["account_type"]] );

 $tpl->theme = $rh->skin;

?>