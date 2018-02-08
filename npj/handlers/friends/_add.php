<?php

 // получаем данные аккаунта и тестируем доступ
 $account = &new NpjObject( &$this->rh, $this->npj_account );
 $data = $account->Load(2);
// if (!$account->HasAccess( &$principal, "owner" )) return $this->Forbidden("FriendsEdit");

 // смотрим, можем ли мы заполнить форму
 if ($this->params[0])
 {
   $this->params[0] = rtrim($this->params[0] , "@");
   if ($this->params[1]) $this->params[0].="@".$this->params[1];
   else $this->params[0].="@".$rh->node_name;

   if ($this->params[2])  return $object->Handler( "_add_ok", &$params, &$principal );
   {
     $user = &new NpjObject( &$rh, $this->params[0] );
     $data = $user->Load(2); 
     if (!is_array($data)) $this->Forbidden( "NoSuchUser" );
     $tpl->Assign( "Friend", $data["user_name"] );
     $tpl->theme = $rh->theme;
       $tpl->Parse( "friends.add.html:Done", "Preparsed:CONTENT" );
       $tpl->Assign( "Preparsed:TITLE", "Операция завершена" ); // !!! to messageset
     $tpl->theme = $rh->skin;
     return GRANTED;
   }
 }

 // получить список групп "конфидентов"
 $rs = $db->Execute( "select group_id as href, group_name as text from ".$rh->db_prefix."groups where user_id=".
                      $db->Quote($data["user_id"])." order by group_rank, pos" );

 $tpl->Assign( "AllFriends.ID",   $rsa1->fields["href"] );
 $tpl->Assign( "AllFriends.Name", $rsa1->fields["text"] );
 $tpl->Assign( "AllReporters.ID",   $rsb1->fields["href"] );
 $tpl->Assign( "AllReporters.Name", $rsb1->fields["text"] );

 if ($_POST["_user"])
 {
   $_POST["_user"] = rtrim($_POST["_user"] , "@");
   if (strpos($_POST["_user"], "@") === false) $_POST["_user"].="@".$rh->node_name;
   $groups = array();
   if ($_POST["_groups_".$rsb1->fields["href"]]) $groups[] = $rsb1->fields["href"];
   if ($_POST["_groups_".$rsa1->fields["href"]]) $groups[] = $rsa1->fields["href"];
   foreach($a as $item)
     if ($_POST["_groups_".$item["href"]]) $groups[] = $item["href"];
   foreach($b as $item)
     if ($_POST["_groups_".$item["href"]]) $groups[] = $item["href"];

   // hmm. а есть ли такой пользователь?
   $user = &new NpjObject( &$rh, $_POST["_user"] );
   $data = $user->Load(2); 
   if (!is_array($data)) 
   {
     $tpl->Assign("ERROR", "<div class='error'>Нет такого пользователя</div>" ); // !!! to messageset
   }
   else
   {
     $record = &new NpjObject( &$rh, $_POST["_user"].":" );
     $data2 = $record->Load(2);

     if (!is_array($data2)) //$debug->Error("{!!!} friend of an external user not implemnted yet.",3);
       $tpl->Assign("ERROR", "<div class='error'>Нет такого пользователя</div>" ); // !!! to messageset
     else
     {


     $sql = "insert into ".$rh->db_prefix."user_groups (group_id, user_id, keyword_id) VALUES "; $f=0;
     foreach ($groups as $group)
     { if ($f) $sql.=", "; else $f=1;
       $sql.="(".$db->Quote($group).", ".$db->Quote($data["user_id"]).", ".$db->Quote($data2["record_id"]).")";
     }
     $db->Execute( $sql );
     $debug->Error( $sql );
     $rh->Redirect( $rh->Href( $this->_NpjAddressToUrl( $account->name.":friends/add/".$data["login"]."/".$data["node_id"]."/done" )
                                , IGNORE_STATE ) , IGNORE_STATE );
   }                
 }
 }


 // парсинг
 $tpl->theme = $rh->theme;
   $tpl->LoadDomain( array(
    "Form:Add"       => $state->FormStart( MSS_POST, $this->_NpjAddressToUrl( $account->name.":friends/add" )),
    "/Form"          => $state->FormEnd(),
    "Npj:Friend" => $this->params[0], 
                   )   );
   $friends->  Parse( "friends.add.html:Groups", "List:Friends"   );
   $reporters->Parse( "friends.add.html:Groups", "List:Reporters" );
   $tpl->Parse( "friends.add.html:Main", "Preparsed:CONTENT" );
   $tpl->Assign( "Preparsed:TITLE", "Добавить в&nbsp;списки конфидентов/корреспондентов" ); // !!! to messageset
 $tpl->theme = $rh->skin;

?>