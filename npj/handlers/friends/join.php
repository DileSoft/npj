<?php

 // получаем данные аккаунта и тестируем доступ
 $account = $rh->account;
 $data = $account->Load(2);

 // проверки, можно ли здесь такое
 if ($data["account_type"] == ACCOUNT_USER) return $this->Forbidden("AccountNotSupport");
 if (!$account->HasAccess( &$principal, "noguests" )) return $this->Forbidden("JoinNoGuests");

 // проверка на банлист
 if (!$rh->account->HasAccess( &$principal, "not_acl", "banlist" )) return $this->Forbidden("YouAreInBanlist");

 // ============= ПРОЦЕССИНГ ФОРМЫ ==========
 if ($_POST["do"])
 {
   // добавление в сообщество
   if ($account->HasAccess( &$principal, "rank_greater", GROUPS_REQUESTS ))
   {
     $rs = $db->Execute("select group_id from ".$rh->db_prefix."groups where user_id=".$db->Quote($data["user_id"]));
     $a = $rs->GetArray(); $groups = array();
     foreach( $a as $item )  $groups[] = $item["group_id"];
     // удалить из групп сообщества
     if (sizeof($groups) > 0)
     $db->Execute( "delete from ".$rh->db_prefix."user_groups where user_id=".$db->Quote($principal->data["user_id"]).
                   " and group_id in (".implode(",",$groups).")");
     // [???] удалить сообщество из группы "Я состою в"
     $rs = $db->Execute( "select group_id from ".$rh->db_prefix."groups where user_id=".$db->Quote($principal->data["user_id"]).
                         " and group_rank = ".$db->Quote(GROUPS_COMMUNITIES));
     $a = $rs->GetArray(); $g = array();
     foreach($a as $v) $g[] = $v["group_id"];
     $db->Execute( "delete from ".$rh->db_prefix."user_groups where user_id=".$db->Quote($data["user_id"]).
                   " and group_id in (".implode(",",$g).")");
   }
   else
   if ($data["security_type"]%10 < COMMUNITY_CLOSED)
   {
     // подписка на всякие опционы
     $p = array( "by_script" => 1 );
     if ($_POST["subscribe_comments"]) $p["comments"] = 1;
     if ($_POST["subscribe_post"]) $p["post"] = 1;
     if ($_POST["subscribe_comments"] || $_POST["subscribe_post"])
      $account->Handler("_subscribe", &$p, &$principal);

     // послать уведомление, если оно того стоит
     $this->Handler( "join_mail", array("user_id"=>$principal->data["user_id"]), &$principal );

     // добавить в нужную группу
     $rs = $db->Execute("select group_id from ".$rh->db_prefix."groups where user_id=".$db->Quote($data["user_id"]).
                        " and group_rank = ".$db->Quote(
                        ($data["security_type"]<COMMUNITY_LIMITED?$data["default_membership"]:GROUPS_REQUESTS)));
     if ($rs->RecordCount() > 0)
     {
       $principal_local_account = $principal->data["login"]."@".$principal->data["node_id"];
       // -- для другого узла надо дописать локаль.
       if ($principal->data["node_id"] != $rh->node_name)
         $principal_local_account.="/".$rh->node_name;

       $record = &new NpjObject( &$rh, $principal_local_account.":" );
       $data2 = $record->Load(2);
       if (!is_array($data2)) $debug->Error("{!!!!!} how strange, i feel straaaaaaaange",5);
       $group_id = $rs->fields["group_id"];
       $db->Execute("insert into ".$rh->db_prefix."user_groups (group_id, user_id, keyword_id) VALUES ".
                    "(".$db->Quote($group_id).", ".$db->Quote($principal->data["user_id"]).", ".
                    $db->Quote($data2["record_id"]).")");
     }
     // добавить сообщество в корреспонденты/конфиденты/группу для сообществ
     // добавляем, только если там его уже нет
     $rs = $db->Execute( "select  ug.group_id from ".$rh->db_prefix."groups as g, ".$rh->db_prefix."user_groups as ug where ".
                         " ug.group_id=g.group_id and g.user_id=".$db->Quote($principal->data["user_id"]).
                         " and g.group_rank < ".$db->Quote(GROUPS_SELF)." and g.is_system = 1 ".
                         " and ug.user_id = ".$db->Quote($data["user_id"]) );
     $a = $rs->GetArray(); $g = array(-185);
     foreach($a as $v) $g[] = $v["group_id"];
     $rs = $db->Execute("select g.group_id from ".$rh->db_prefix."groups as g ".
                        " where g.user_id=".$db->Quote($principal->data["user_id"]).
                        " and g.group_id not in (".implode(",",$g).") and g.group_rank < ".$db->Quote(GROUPS_SELF)." and g.is_system = 1 " );
     if ($rs->RecordCount() > 0)
     {
       $record = &new NpjObject( &$rh, $account->npj_account.":" );
       $data2 = $record->Load(2);
       $a = $rs->GetArray(); $sql=""; $f=0;
       foreach ($a as $item)
       { if ($f) $sql.=", "; else $f=1;
         $sql.= "(".$db->Quote($item["group_id"]).
                ", ".$db->Quote($data2["user_id"]).
                ", ".$db->Quote($data2["record_id"]).")";
       }
       $db->Execute("insert into ".$rh->db_prefix."user_groups (group_id, user_id, keyword_id) VALUES ".$sql);
     }


   }
   $this->Handler("_count_friends", array(), &$principal );
   // редиректимся на "ок"
   $rh->Redirect( $rh->Href( $this->_NpjAddressToUrl( $account->name.":friends/join/ok" ), IGNORE_STATE ) , IGNORE_STATE );
 }

 // ============== ПАРСИНГ ==================

 $tpl->theme = $rh->theme;
 if ($params[0])  // .../join/ok
 {
   $tpl->Assign( "Preparsed:TITLE", "Операция завершена" ); // !!! to messageset
   $tpl->Parse(  "friends.join.html:Done", "Preparsed:CONTENT" ); 
   // надо показать, что всё успешно
 }
 else
 if ($account->HasAccess( &$principal, "rank_greater", GROUPS_LIGHTMEMBERS ))
 {
   // нужно показать форму "до свиданья"
   $tpl->Assign( "Preparsed:TITLE", "Выход из сообщества" ); // !!! to messageset
   $tpl->Assign( "FormState", "Вы уже являетесь членом этого сообщества." ); // !!! to messageset
   $tpl->Assign( "FormAction", "Для того, чтобы выйти из сообщества, нажмите на эту кнопку:" ); // !!! to messageset
   $tpl->Assign( "FormButton", "Покинуть сообщество" ); // !!! to messageset
   $tpl->Assign( "IsFriendAlready", "1" ); // !!! to messageset
 }
 else
 if ($account->HasAccess( &$principal, "rank_greater", GROUPS_REQUESTS ))
 {
   $tpl->Assign( "Preparsed:TITLE", "Вступление в сообщество" ); // !!! to messageset
   $tpl->Assign( "Preparsed:CONTENT", "Заявка уже находится на рассмотрении модераторами сообщества. Ждите..." ); 
   // !!! to messageset
 }
 else
 if ($data["security_type"]%10 > 1)
 {
   // нужно извиниться, потому что сообщество закрытое
   $tpl->Assign( "Preparsed:TITLE", "Вступление в сообщество невозможно" ); // !!! to messageset
   $tpl->Assign( "Preparsed:CONTENT", "Извините, но это сообщество закрытого типа. Обратитесь непосредственно к модератору, чтобы он включил вас в члены." ); 
 }
 else
 {
   // нужно показать форму "вступайте в наши ряды"
   $tpl->Assign( "Preparsed:TITLE", "Вступление в сообщество" ); // !!! to messageset
   if ($data["security_type"] > 0)
     $tpl->Assign( "FormState", "Вы можете стать членом этого сообщества после подтверждения заявки модератором" ); // !!! to messageset
   else
     $tpl->Assign( "FormState", "Вы можете стать членом этого сообщества сразу после отправки заявки" ); // !!! to messageset
   $tpl->Assign( "FormAction", "Для того, чтобы отправить заявку на членство, нажмите на эту кнопку" ); // !!! to messageset
   $tpl->Assign( "FormButton", "Войти в сообщество" ); // !!! to messageset
 }

 // Основной парсинг
 if (!$tpl->GetValue("Preparsed:CONTENT")) 
 {
   $tpl->LoadDomain( array(
          "Form:Join" => $state->FormStart( MSS_POST, $this->_NpjAddressToUrl( $this->npj_address )),
          "/Form"     => $state->FormEnd(),  
                   )      );
   $tpl->Parse( "friends.join.html:Form", "Preparsed:CONTENT" ); 
 }
 $tpl->theme = $rh->skin;

?>