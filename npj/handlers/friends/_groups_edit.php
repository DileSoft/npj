<?php

 // подчасть groups.php

 // есть $this->params[0] = "edit"
 // может быть $this->params[1] = $rh->group_rank[ xx ] -- какой тип групп редактируем
 // есть $account -- аккаунт того, чьи группы изменяем
 // есть $data -- данные аккаунта того, чьи группы изменяем

 // какой тип групп?

 if ($data["account_type"] > 0) return $this->Forbidden("NotImplemented");

 if (!$account->HasAccess( &$principal, "owner" ))
   if (!$account->HasAccess( &$principal, "acl_text", $rh->node_admins ) || ($account->npj_account != $rh->node_user))
    return $this->Forbidden("YouDontOwnThisAccount");

 $group_ranks = $rh->group_ranks[ $data["account_type"] ];
 $rank_flipped = array_flip( $group_ranks );
 $rank = 0;
 if ($this->params[1])
  if (isset( $rank_flipped[$this->params[1]] ))
   $rank = $rank_flipped[$this->params[1]];

 // ok page
 if ($params[1] == "ok")
 { $tpl->theme = $rh->theme;
   $tpl->Parse( "friends.groups.edit.html:Done", "Preparsed:CONTENT" );
   $tpl->Assign( "Preparsed:TITLE", "Группы сохранены успешно" ); // !!! to message_set
   $tpl->theme = $rh->skin;
   return GRANTED;
 }

 // получить список групп "репортёров" или кого там просят
 $rs = $db->Execute( "select is_default, is_system, group_id, group_name, group_rank from ".$rh->db_prefix."groups where user_id=".
                      $db->Quote($data["user_id"])." and group_rank=".$db->Quote($rank)." order by pos" );
 // рассортировать его по массиву $groups[ is_system ][ group_id ]
 $a = $rs->GetArray(); $groups = array( 0 => array(), 1 => array() );
 foreach( $a as $item )
 {
   $groups[$item["is_system"]][ $item["group_id"] ] = array(
        "href"  => $item["group_id"],
        "text"  => $item["group_name"],
           );
 }

 // сохранение
 if ($_POST["_do"])
 {
   include( $rh->handlers_dir."friends/_groups_edit_save.php" );
   return GRANTED;
 }


 $tpl->theme = $rh->theme;
 // для парсинга нам понадобится:
 // 1. {{GroupSelect}}   список вида <select size=XX> из всех групп
 // 2. {{GroupContents}} текущее состояние заполненности групп пользователями, в формате:
 //                       * group1|user1,user2,user3|group2||group3|user2|group4|user1,user3,user4
 //                       * groupX -- group_id
 //                       * userX  -- NpjAddress (kuso@npj)
 // 3. {{AllUsers}}      список всех пользователей 
 // 4. {{GroupRanks}}    переключатель по всем типам групп (конфиденты, репортёры)
 // 5. {{GroupNames}} список групп вида id|name|id|name

 // 1. {{GroupSelect}}   список вида <select size=XX> из всех групп
 $select = &new ListSimple( &$rh, &$groups[0] );
 $select->Parse( "friends.groups.edit.html:Select", "GroupSelect" );
 // 2. {{GroupContents}} текущее состояние заполненности групп пользователями, в формате:
 $group_contents = ""; 
 foreach( $groups[0] as $id=>$group ) 
 {
   $rs = $db->Execute( "select u.login, u.node_id from ".$rh->db_prefix."users as u, ".$rh->db_prefix.
          "user_groups as ug where ug.user_id = u.user_id and ug.group_id=".$db->Quote($id) );
   $a = $rs->GetArray();
   $group_contents .= $id."|"; $f=0;
   foreach ($a as $user)
   { if ($f) $group_contents.=","; else $f=1;
     $group_contents.= $user["login"]."@".$user["node_id"]; }
   $group_contents .= "|"; 
 }
 if ($group_contents != "") $group_contents = substr( $group_contents, 0, strlen($group_contents) -1 );
 $tpl->Assign( "GroupContents", $group_contents );
 // 3. {{AllUsers}}      список всех пользователей 
 foreach( $groups[1] as $group ) $all =  $group;
 $rs = $db->Execute( "select u.login, u.node_id from ".$rh->db_prefix."users as u, ".$rh->db_prefix.
        "user_groups as ug where ug.user_id = u.user_id and ug.group_id=".$db->Quote($all["href"]) );
 $a = $rs->GetArray();
 $all_users = ""; $f=0;
 foreach ($a as $user)
 { if ($f) $all_users.=","; else $f=1;
   $all_users.= $user["login"]."@".$user["node_id"]; }
 $tpl->Assign( "AllUsers", $all_users );
 // 4. {{GroupRanks}}    переключатель по всем типам групп (конфиденты, репортёры)
 $rh->UseClass( "ListCurrent", $rh->core_dir );
 $rank_names = array();
 foreach ($group_ranks as $group_nick) $rank_names[$group_nick] = $tpl->message_set["FriendsNames"][$group_nick];
 $ranks = &new ListCurrent( &$rh, &$rank_names, NULL, $group_ranks[$rank] );
 $ranks->Parse( "friends.groups.edit.html:Rank", "GroupRanks" );
 // 5. {{GroupNames}} 
 $grp = "";
 foreach( $groups[0] as $id=>$group ) 
 {
  $grp = $grp.$id."|".$group["text"]."|";
 }
 $tpl->Assign( "GroupNames", rtrim($grp,"|") );

 // основные параметры для последующих парсингов
 $tpl->LoadDomain( array(
    "Form:Edit" => $state->FormStart( MSS_POST, $this->_NpjAddressToUrl( $account->name.":friends/groups/edit/".$group_ranks[$rank] ),
                                      " name=fg "),
    "/Form"     => $state->FormEnd(),
                 )      );
 // парсинг
   // Основной парсинг
   $tpl->Parse( "friends.groups.edit.html:Main", "Preparsed:CONTENT" );
   $tpl->Assign( "Preparsed:TITLE", "Редактирование групп" ); // !!! to messageset

 
 $tpl->theme = $rh->skin;

?>