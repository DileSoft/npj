<?php

  // $params["foreign"] - чужак, популировать только корневую запись и проч.

  // насыщение аккаунта ништяками
  // из запросов не вызывается

  // 1. какой кластер нужен, получить идшники записей кластера
  $data = $this->Load(3);
  $debug->Trace("account info");
  $debug->Trace_R($data);
  $_theme =  $tpl->theme;
  $tpl->theme = $data["skin"];

  $supertag = $rh->node_user.":".$rh->default_content[ $data["account_type"] ];
  $crop = strlen( $supertag )+1;

  $rs = $db->Execute("select supertag from ".$rh->db_prefix."records where supertag like ".
                     $db->Quote($supertag.($params["foreign"]?"":"%"))." order by supertag asc" );
  $records = $rs->GetArray();
  $debug->Trace( $supertag );

  //
  $crops     = array();
  $supertags = array();
  $recordss  = array();


  // 1a. популяция по классу из кластера node@npj:Accounts/CLASS
  if (isset($rh->account_classes[$data["account_class"]]))
  {
    $target_class = $rh->account_classes[$data["account_class"]];
    $supertag_2   = $rh->node_user.":accounts/".strtolower($data["account_class"]);
    $crop_2       = strlen( $supertag_2 )+1;
    $rs = $db->Execute("select supertag from ".$rh->db_prefix."records where supertag like ".
                       $db->Quote($supertag_2.($params["foreign"]?"":"%"))." order by supertag asc" );
    $records_2 = $rs->GetArray();

    $crops[] = $crop_2;
    $supertags[] = $supertag_2;
    $recordss[]  = $records_2;
  }

  $crops[] = $crop;
  $supertags[] = $supertag;
  $recordss[]  = $records;

  // 2. методично переписываем их
  $r_ids = array();
  $first_root = true;

  foreach( $crops as $no=>$crop )
  {
    $supertag = $supertags[$no];
    $records  = $recordss[$no];

  foreach( $records as $record)
  {
    $page = &new NpjObject( &$rh, $record["supertag"] );
    $page->Load( 4 );
    $page->npj_account         = $data["login"]."@".$data["node_id"];
    $page->npj_object_address  = $data["login"]."@".$data["node_id"].($params["foreign"]?"/".$rh->node_name:"").":".substr($record["supertag"], $crop);
    $page->data["supertag"]  = ""; //$data["login"]."@".$data["node_id"].":".substr($record["supertag"], $crop);
    $page->data["tag"]       = substr($record["supertag"], $crop);
    $is_root = ($page->data["tag"] == ""); ///////////////////////////// помечаем корневую запись
    $page->data["user_id"]   = $data["user_id"];
    $page->data["depth"]   --;
    $page->data["edited_user_login"]  = $data["login"];
    $page->data["edited_user_name"]   = $data["user_name"];
    $page->data["edited_user_node_id"]  = $data["node_id"];
    $page->Save();
    if ($record["supertag"] == $supertag) $new_record = $page->data["record_id"];
    $r_ids[] = $page->data["record_id"];

      // если корневая запись, то надо обновить поле root_record_id в тельце
      if ($is_root && $first_root)
      {
        $db->Execute( "update ".$rh->db_prefix."users set root_record_id = ".$db->Quote( $page->data["record_id"] ).
                      " where user_id = ". $db->Quote( $data["user_id"] ));
        $first_root = false;
        $this->data["root_record_id"] = $page->data["record_id"];
      }

    }
  }
  $tpl->theme = $_theme;

  // 3. вставляем группы
  $is_system = $rh->groups_presets[ $data["account_type"] ]; 
  $rs = $db->Execute("select group_name, group_rank from ".$rh->db_prefix."groups where is_system=".$db->Quote($is_system) );
  $a = $rs->GetArray();
  $sql = "insert into ".$rh->db_prefix."groups (group_name, user_id, group_rank, group_type, is_system) VALUES ";
  $f=0;
  foreach ($a as $item) 
  {
    if ($f) $sql.=", "; else $f=1;
    $sql.= "(".$db->Quote($item["group_name"]).", ".$db->Quote($data["user_id"]).", ".
               $item["group_rank"].", ".$db->Quote($is_system).", 1)";
  }
  if ($f) $db->Execute( $sql );

  // 4. вставляем самого себя в группы (GROUPS_SELF, GROUPS_COMMUNITIES)
  $rs = $db->Execute( "select group_id, group_rank from ".$rh->db_prefix."groups where is_system=1 ".
                      " and group_rank in (".GROUPS_SELF.", ".GROUPS_COMMUNITIES.") ".
                      " and user_id=". $db->Quote( $data["user_id"] ) );
  $a = $rs->GetArray();
  foreach ($a as $k=>$v)
  {
    $group_nobody = 1*$v["group_id"];
    $db->Execute( "insert into ".$rh->db_prefix."user_groups (group_id, user_id, keyword_id) VALUES (".
                  $db->Quote($group_nobody).",".$db->Quote($data["user_id"]).", ".$db->Quote($new_record).")");

    if (($v["group_rank"] == GROUPS_SELF) && ($params["_p_record_id"]))
     $db->Execute( "insert into ".$rh->db_prefix."user_groups (group_id, user_id, keyword_id) VALUES (".
                   $db->Quote($group_nobody).",".$db->Quote($params["_p_user_id"]).", ".$db->Quote($params["_p_record_id"]).")");
  }
  
  // для групповых аккаунтов (сообщества, РГ)
  // 4-бета. Если пришёл принципал в полях "_p_record_id"
  //         или если у аккаунта есть "предок", то наполняем GROUPS_MODERATORS 
  //         "принципалом" и "модераторами" предка
  $auto_moderators = array();
  if ($this->data["parent_id"] > 0)
  {
      $parent_id   = $this->data["parent_id"];
      $parent_data = $this->rh->account->_LoadById( $parent_id );
      if ($parent_data != NOT_EXIST)
      if ($parent_data["alive"] == 1)
      {
        // получить всех модераторов ТОГО аккаунта
        $sql = "select ug.user_id, ug.keyword_id from ".
               $rh->db_prefix."user_groups as ug,".
               $rh->db_prefix."groups as g where ug.group_id = g.group_id ".
               " and g.user_id = ".$db->Quote( $parent_id ).
               " and g.group_rank = ". $db->Quote( GROUPS_MODERATORS );
        $rs  = $db->Execute( $sql );
        $_a   = $rs->GetArray();
        foreach( $_a as $k=>$v )
         $auto_moderators[ $v["user_id"] ] = array( "keyword_id" => $v["keyword_id"],
                                                    "user_id"    => $v["user_id"],
                                                  );
      }
  } 
  if ($params["_p_record_id"]) 
    $auto_moderators[$params["_p_user_id"]] = array( "keyword_id" => $params["_p_record_id"],
                                                     "user_id"    => $params["_p_user_id"],
                                                   );
  if (sizeof($auto_moderators) > 0)
  {
     // готовим квоченные детали
     $auto_moderators_ids_q = array();
     foreach( $auto_moderators as $kk=>$vv )
       $auto_moderators_ids_q[] =  $db->Quote($vv["user_id"]);

     // добавляем их в группу МОДЕРАТОРЫ
     $rs = $db->Execute( "select group_id, group_rank from ".$rh->db_prefix."groups where is_system=1 ".
                         " and group_rank = ".$db->Quote(GROUPS_MODERATORS).
                         " and user_id=". $db->Quote( $this->data["user_id"] ) );
     $a = $rs->GetArray();
     foreach( $a as $k=>$v )
     {
       // ещё квотки
       $auto_moderators_q = array();
       foreach( $auto_moderators as $kk=>$vv )
         $auto_moderators_q[] = "(". $db->Quote($v["group_id"]).", ". 
                                     $db->Quote($vv["user_id"]).", ". 
                                     $db->Quote($vv["keyword_id"]).
                                ")";
       $db->Execute( "insert into ".$rh->db_prefix."user_groups (group_id, user_id, keyword_id) VALUES ".
                      implode(",",$auto_moderators_q)  );
     }

     // ещё нужно добавить в спец-группу "для сообществ" для каждого модератора
     // но если мы создаём пользователей, то так делать не стоит.
     if ($this->data["account_type"] != ACCOUNT_USER)
     {
       $rs = $db->Execute("select user_id, group_id from ".$rh->db_prefix."groups where group_rank=".GROUPS_COMMUNITIES.
                          " and is_system=1 and user_id in (".implode(",",$auto_moderators_ids_q).")");
       $a  = $rs->GetArray();
       foreach( $a as $k=>$v )
       {
         $db->Execute( "insert into ".$rh->db_prefix."user_groups (group_id, user_id, keyword_id) VALUES ".
                        "(".
                            $db->Quote( $v["group_id"] ).", ".
                            $db->Quote( $this->data["user_id"] ).", ".
                            $db->Quote( $this->data["root_record_id"] ).
                        ")"  );
       }
     }
  }


        
  // 4-штрих. вставляем пользователя, от которого действуем (если это не гость) туда же. Он тоже владелец
  /* ??? kuso saz: совершенно мистический фрагмент кода, который писал я. 
                   Он закомментирован уже четыре века и никто не знает, какова вообще
                   была цель его написания.
  if ($principal->data["keyword_id"]) ;
  /*
  // ??? DO THIS STUFF ??? 
  $db->Execute( "insert into ".$rh->db_prefix."user_groups (group_id, user_id, keyword_id) VALUES (".
                $group_nobody.",".$principal->data["user_id"].", ".$principal->data["keyword_id"].")");
  */


  // 5. банлист
    $sql = ""; $f=0;
    $acls = $rh->default_acls[$this->data["account_type"]];
    // not tested patch ---
    if (isset($rh->account_classes[$this->data["account_class"]]))
    {
      $target_class = $rh->account_classes[$this->data["account_class"]];
      if (isset($target_class["acls"])) $acls = $target_class["acls"];
    }
    // ---
    foreach($acls as $acl=>$value)
    if ($acl == "banlist")
    { if ($f) $sql.=","; else $f=1;
      $sql.= "(".$db->Quote($data["user_id"]).", ".$db->Quote("account").", ".
                 $db->Quote($acl).", ".$db->Quote( $value ).")";
    } 
    /* ??? Kuso Happy-New-Year-Patch: похоже, это теперь так */
    /*
    else
    foreach ($r_ids as $rid)
    { if ($f) $sql.=","; else $f=1;
      $sql.= "(".$db->Quote($rid).", ".$db->Quote("record").", ".
                 $db->Quote($acl).", ".$db->Quote( $value ).")";
    } 
    */
    $db->Execute("insert into ".$rh->db_prefix."acls (object_id, object_type, object_right, acl) VALUES ".$sql);

  if (!$params["foreign"])
  {
  // 6. перепопуляция меню
  $rs = $db->Execute( "select npj_address, title, pos from ".$rh->db_prefix."user_menu where user_id=2" );
  $a = $rs->GetArray();
  $sql=""; $f=0;
  foreach ($a as $item)
  { if ($f) $sql.=","; $f=1;
    $item["npj_address"] = str_replace("(!)", $data["login"]."@".$data["node_id"], $item["npj_address"] );
    $sql.="(".$db->Quote($data["user_id"]).", ".$db->Quote($item["npj_address"])
           .", ".$db->Quote($item["title"]).", ".$db->Quote($item["pos"]).")";
  }
  if ($sql != "")
   $db->Execute("insert into ".$rh->db_prefix."user_menu (user_id, npj_address, title, pos) VALUES ".$sql);

  // 7. вставка в фиф (автоматически ставим каждого 
  $rs = $db->Execute( "select group_id from ".$rh->db_prefix."groups where group_rank=".GROUPS_LIGHTMEMBERS." and is_system=1 and user_id=2");
  $a = $rs->GetArray();
  $sql=""; $f=0;
  foreach ($a as $item)
  { if ($f) $sql.=","; $f=1;
    $sql.="(".$db->Quote($item["group_id"]).", ".$db->Quote($data["user_id"]).", ".$db->Quote($new_record).")";
  }
  if ($sql != "")
   $db->Execute("insert into ".$rh->db_prefix."user_groups (group_id, user_id, keyword_id) VALUES ".$sql);
  }

  return GRANTED;
?>