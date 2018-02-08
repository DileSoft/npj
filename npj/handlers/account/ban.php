<?php

 // получаем данные аккаунта и тестируем доступ
 $account = $rh->account;
 $data = $account->Load(2);
 if (!is_array($data)) return $this->NotFound("AccountNotFound");

 if ($account->HasAccess( &$principal, "owner" ) || 
     (($data["account_type"] == ACCOUNT_COMMUNITY) && 
       $account->HasAccess( &$principal, "rank_greater", GROUPS_MODERATORS ))) ;
    else return $this->Forbidden("YouDontOwnThisAccount");

 // смотрим, можем ли мы заполнить форму
 if ($this->params[0])
 if ($this->params[0] !="edit")
 {
   if (strpos($this->params[0], "@") === false) 
     if ($this->params[1]) $this->params[0].="@".$this->params[1];
     else                  $this->params[0].="@".$rh->node_name;

   if ($this->params[2])  
   {
     $tpl->theme = $rh->theme;
      $tpl->Assign( "Link:Friend", $account->Link($this->params[0]) );
      $tpl->Parse( "account.ban.html:Done", "Preparsed:CONTENT" );
      $tpl->Assign( "Preparsed:TITLE", "ќпераци€ завершена" ); // !!! to messageset
     $tpl->theme = $rh->skin;
     return GRANTED;
   }
 }
 else
 {
   if ($this->params[1])  
   {
     $tpl->theme = $rh->theme;
      $tpl->Parse( "account.ban.html:EditDone", "Preparsed:CONTENT" );
      $tpl->Assign( "Preparsed:TITLE", "ќпераци€ завершена" ); // !!! to messageset
     $tpl->theme = $rh->skin;
     return GRANTED;
   }
 }

 if ($_POST["_user"])
 {
   if (strpos($_POST["_user"], "@") === false) $_POST["_user"].="@".$rh->node_name;
   $this->params[0] = $_POST["_user"];
 }

 // получаем банлист
 $acl = $rh->cache->Restore( "account_acl_banlist", $data["user_id"], 2 );
 if ($acl === false)
 {
    $rs = $db->Execute( "select acl from ".$rh->db_prefix."acls where ".
                        "object_type = ".$db->Quote("account")." and ".
                        "object_id   = ".$db->Quote($data["user_id"])." and ".
                        "object_right= ".$db->Quote("banlist") );
    if ($rs->RecordCount() > 0) $acl = $rs->fields["acl"];
    else $acl = "";
 }
 
 // основные параметры дл€ последующих парсингов
 $tpl->LoadDomain( array(
    "Form:Add"       => $state->FormStart( MSS_POST, $this->_NpjAddressToUrl( $account->name.":ban" )),
    "Form:Edit"      => $state->FormStart( MSS_POST, $this->_NpjAddressToUrl( $account->name.":ban/edit" )),
    "/Form"          => $state->FormEnd(),
    "Npj:Friend"     => $this->params[0], 
                 )      );

 // проверить, существует ли тот пользователь, которого нам предложили
 if ($this->params[0])
 if ($this->params[0] !="edit")
 {
   $user = &new NpjObject( &$rh, $this->params[0] );
   $udata = $user->Load(2);
 }

 // дописываем в конец банлиста
 if ($_POST["_user"] && ($this->params[0] != ""))
 {
   $acl.= ($acl==""?"":"\n").$this->params[0];
   $db->Execute( "delete from ".$rh->db_prefix."acls where ".
                 "object_type = ".$db->Quote("account")." and ".
                 "object_id   = ".$db->Quote($data["user_id"])." and ".
                 "object_right= ".$db->Quote("banlist") );
   $db->Execute( "insert into ".$rh->db_prefix."acls (object_type, object_id, object_right, acl) VALUES ".
                 "(".$db->Quote("account").", ".
                     $db->Quote($data["user_id"]).", ".
                     $db->Quote("banlist").", ".
                     $db->Quote($acl).")" );
   $rh->Redirect( $rh->Href( $this->_NpjAddressToUrl( $account->name.":ban/".$this->params[0]."/ban/done" )
                            , IGNORE_STATE ) , IGNORE_STATE );
 }
 if ($_POST["_do"])
 {
   $acl = $_POST["acl"];
   $db->Execute( "delete from ".$rh->db_prefix."acls where ".
                 "object_type = ".$db->Quote("account")." and ".
                 "object_id   = ".$db->Quote($data["user_id"])." and ".
                 "object_right= ".$db->Quote("banlist") );
   $db->Execute( "insert into ".$rh->db_prefix."acls (object_type, object_id, object_right, acl) VALUES ".
                 "(".$db->Quote("account").", ".
                     $db->Quote($data["user_id"]).", ".
                     $db->Quote("banlist").", ".
                     $db->Quote($acl).")" );
   $rh->Redirect( $rh->Href( $this->_NpjAddressToUrl( $account->name.":ban/edit/done" )
                            , IGNORE_STATE ) , IGNORE_STATE );
 }

 // парсинг
 $tpl->theme = $rh->theme;
 if ($this->params[0] != "edit")
 {
   if ($this->params[0] && !is_array($udata)) $tpl->Parse("account.ban.html:Error","ERROR");
   $tpl->Parse( "account.ban.html:Main", "Preparsed:CONTENT" );
   $tpl->Assign( "Preparsed:TITLE", "ƒобавить в&nbsp;бан-лист" ); // !!! to messageset
 }
 else
 {
   $tpl->Assign( "ACL", $acl ); 
   $tpl->Parse( "account.ban.html:Edit", "Preparsed:CONTENT" );
   $tpl->Assign( "Preparsed:TITLE", "–едактирование бан-листа" ); // !!! to messageset
 }
 $tpl->theme = $rh->skin;

?>