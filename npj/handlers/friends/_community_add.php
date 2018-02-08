<?php

 // подчасть add.php

 // есть $this->params[0], где НпжАдрес пользователя
 // есть $_POST["_group_XX"], где ХХ -- идшники групп, куда надо включить
 // ща сделаем $groups[ group_id ], где есть список групп
 // ща сделаем $user -- тот, кого добавляем
 // есть $account -- тот, кому добавляем
 // почти есть $udata, $data -- данные соответственно юзера и аккаунта

 if (!$account->HasAccess( &$principal, "owner" ))
 if (!$account->HasAccess( &$principal, "rank_greater", GROUPS_MODERATORS )) 
   return $object->Handler( "join", &$params, &$principal );

 // смотрим, можем ли мы заполнить форму
 if ($this->params[0])
 {
   $this->params[0] = rtrim($this->params[0] , "@");
   if ($this->params[1]) $this->params[0].="@".$this->params[1];
   if (strpos($this->params[0], "@") === false) $this->params[0].="@".$rh->node_name;

   if ($this->params[2])  
   {
     if($this->params[2] == "done")
       include( $rh->handlers_dir."friends/_community_add_ok.php" );
     else
       include( $rh->handlers_dir."friends/_community_add_removeok.php" );
     return GRANTED;
   }
 }
 if ($_POST["_user"])
 {
   $_POST["_user"] = rtrim($_POST["_user"] , "@");
   if (strpos($_POST["_user"], "@") === false) $_POST["_user"].="@".$rh->node_name;

   // -- для другого узла надо дописать локаль.
   $parts = explode("@", $_POST["_user"] );
   if ($parts[1] != $rh->node_name)
    $parts[1].="/".$rh->node_name;
   $_POST["_user"] = implode("@", $parts);

   $this->params[0] = $_POST["_user"];
 }

 // получить список системных групп 
 $rs = $db->Execute( "select group_id, group_name, group_rank from ".$rh->db_prefix."groups where user_id=".
                      $db->Quote($data["user_id"])." and is_system=1 and group_rank<".$db->Quote(GROUPS_SELF)." order by group_rank, pos" );
 // рассортировать его по массиву $groups[ group_id ]
 $a = $rs->GetArray(); $groups = array();
 foreach( $a as $item )
 {
   if ($data["default_membership"] == $item["group_rank"]) $data["moderation_group_id"] = $item["group_id"];
   if (($data["owner_user_id"] == $principal->data["user_id"]) ||
       ($item["group_rank"] < 20))
   $groups[ $item["group_id"] ] = array(
        "href"  => $item["group_id"],
        "text"  => $item["group_name"],
        "rank"  => $item["group_rank"],
        "title" => "",
           );
 }

 // основные параметры для последующих парсингов
 $tpl->LoadDomain( array(
    "Form:Add"       => $state->FormStart( MSS_POST, $this->_NpjAddressToUrl( $account->name.":friends/add" )),
    "/Form"          => $state->FormEnd(),
    "Npj:Friend"     => $this->params[0], 
                 )      );


$tpl->Skin($rh->theme);
 // проверить, существует ли тот пользователь, которого нам предложили
 if ($this->params[0])
 {
   $user = &new NpjObject( &$rh, $this->params[0] );
   $udata = $user->Load(2);
   if (!is_array($udata)) $this->params[0] = ""; 
   // <<max@jetstyle 2004-11-17 : не добавлять замороженных >>
   elseif ($udata["alive"]!= "1")
   {
     $tpl->Parse("friends.c.add.html:UserFrozen","ERROR");
     $this->params[0] = "";
   }
   // <<max@jetstyle 2004-11-17 //>>
 }

 // сообщение об ошибке
 if ($_POST["_user"]) 
 {
   $parts = explode("@", $_POST["_user"] );
   if ($parts[1] == $rh->node_name) $tpl->Assign("IsForeign", 0);
   else
   {
     $tpl->Assign("IsForeign", 1);
     $rh->absolute_urls=1;
     $tpl->Assign("Href:authto", $rh->base_host_prot.$rh->Href( 
          $state->Plus("authto", preg_replace("/\/.*$/i", "", $parts[1])), STATE_IGNORE ));
     $rh->absolute_urls=0;
   }
  if (!$tpl->GetValue("ERROR")) $tpl->Parse("friends.c.add.html:Error","ERROR");
 }

 // а нет ли уже этого пользователя в сообществе? считывание из БД
 if ($this->params[0])
 { 
   if (is_array($udata))
   {
    $rs = $db->SelectLimit( "select g.group_id ".
                           " from ".$rh->db_prefix."user_groups as ug, ".$rh->db_prefix."groups as g ".
                           " where ug.group_id = g.group_id and g.user_id=".$db->Quote($data["user_id"]).
                           " and g.is_system=1 and ug.user_id = ".$db->Quote($udata["user_id"]).
                           " order by group_rank desc", 1 );
    $a = $rs->GetArray();
    if (sizeof($a) > 0) 
    {
      $udata["_group_id"] = $a[0]["group_id"];
      $groups[ $a[0]["group_id"] ]["title"]="CHECKED";
      $groups[ -1 ] = array(
                          "href" => -1,
                          "title" => "",
                          "rank" => -1,
                          "text" => $tpl->message_set["FriendsRemoveFrom"],
                            );
    }
 } }
 if (!isset($udata["_group_id"])) $udata["_group_id"]=$data["moderation_group_id"];

 // запись в друзья
 if ($_POST["_user"] && ($this->params[0] != ""))
   return include( $rh->handlers_dir."friends/_community_add_save.php" );

 // парсинг
  $list = &new ListCurrent( &$rh, &$groups, "href", $udata["_group_id"] ); $c=0;
  $list->Parse( "friends.c.add.html:Groups", "Groups" );
  $tpl->Parse( "friends.c.add.html:Main".$data["account_type"], "Preparsed:CONTENT" );
  $tpl->Assign( "Preparsed:TITLE", $tpl->message_set["MemberAdd". $data["account_type"]] );

  $tpl->Unskin();
?>