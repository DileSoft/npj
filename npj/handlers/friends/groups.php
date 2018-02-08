<?php

 // получаем данные аккаунта и тестируем доступ
 $account = $rh->account;
 $data = $account->Load(2);
// if (!$account->HasAccess( &$principal, "owner" )) return $this->Forbidden("FriendsEdit");

 if ($data["account_type"] > 0) return $this->Forbidden("CommunityNotSupport");

 if ($this->params[0] == "edit")
 {
   include($rh->handlers_dir."friends/_groups_edit.php");
   return GRANTED;
 }

 // получить список групп "репортёров"
 $rs = $db->Execute( "select is_default, is_system, group_id, group_name, group_rank from ".$rh->db_prefix."groups where user_id=".
                      $db->Quote($data["user_id"])." and group_rank=".$db->Quote(GROUPS_REPORTERS)." order by pos" );
 // рассортировать его по массиву $groups[ is_system ][ group_id ]
 $a = $rs->GetArray(); $groups = array( 0 => array(), 1 => array() );
 foreach( $a as $item )
 {
   $groups[$item["is_system"]][ $item["group_id"] ] = array(
        "href"  => $item["group_id"],
        "text"  => $item["group_name"],
        "title" => ($item["is_default"]?"CHECKED":""),
           );
 }

 // основные параметры для последующих парсингов
 $tpl->LoadDomain( array(
    "Form:Filter"    => $state->FormStart( MSS_GET, $this->_NpjAddressToUrl( $account->name.":friends/filter" )),
    "/Form"          => $state->FormEnd(),
                 )      );

 // парсинг
 $tpl->theme = $rh->theme;

   // Парсинг групп групп
   foreach( $groups[1] as $item )
    $tpl->LoadDomain( array( "All.ID" => $item["href"], 
                             "All.Name" => $item["text"], 
                             "All.Rank" => $rh->group_ranks[$data["account_type"]][GROUPS_REPORTERS],
                             "All.Checked" => $item["title"] ) );
   $list = &new ListSimple( &$rh, &$groups[0] ); 
   $list->Parse( "friends.groups.html:Groups", "List" );
   // Основной парсинг
   $tpl->Parse( "friends.groups.html:Main", "Preparsed:CONTENT" );
   $tpl->Assign( "Preparsed:TITLE", "Выборочный просмотр ленты корреспондентов" ); // !!! to messageset

 $tpl->theme = $rh->skin;

?>