<?php
/*
    HelperEvent( &$rh, &$obj ) -- Хелпер для формы редактирования анонсов событий
      * у $obj:
          $obj->helper
          $obj->owner

  ---------
   - добавляет группу "анонс"
   - добавляет в группу "анонс" поле "анонс-ин"
   - дополняет $this->ref[...] данными из поля "анонс-ин"

=============================================================== v.1 (Kuso)
*/

class HelperEvent extends HelperPost
{

  // -----------------------------------------------------------------
  // - добавим группу announces 
  // - добавим в группу announes поле announce_in
  function &TweakForm( &$form_fields, &$group_state, $edit=false )
  {
    $rh = &$this->rh; $db = &$this->rh->db; $obj = &$this->obj; //  RH, DB, OBJ
    $debug = &$rh->debug;
    $_new_groups = &HelperPost::TweakForm( &$form_fields, &$group_state, $edit ) ;

    // NB: неоптимально сейчас -- потому что два раза получает список сообществ, 
    //     где опубликовано. !!! - refactor mark

    // 1. посмотрим, в каких сообществах опубликовано
    // 1.1. Получим список всех сообществ, в которых уже состоит пользователь
    $sql= "SELECT u.user_id, u.login FROM ".
            $rh->db_prefix."groups as g, ".
            $rh->db_prefix."user_groups as gu, ".
            $rh->db_prefix."profiles as p, ".
            $rh->db_prefix."users as u WHERE ".
            "p.user_id = u.user_id and p.announce_membership <= g.group_rank and ".
            "gu.group_id = g.group_id AND g.user_id = u.user_id AND ".
            "gu.user_id = ".$db->Quote($rh->account->data["user_id"])." AND ".
            "u.owner_user_id <> 0 AND g.group_rank >= ".GROUPS_LIGHTMEMBERS." AND ".
            "g.group_rank < ".GROUPS_SELF." and g.is_system=1;"; 
    $rs = $db->Execute($sql);
    $a=$rs->GetArray();

    // 1.1.1. Добавим туда и публичные сообщества
    $_communities_member = $a;
    $sql= "SELECT u.user_id, u.login FROM ".
            $rh->db_prefix."profiles as p, ".
            $rh->db_prefix."users as u WHERE ".
            "p.security_type = ".$db->Quote(COMMUNITY_PUBLIC)." and ".
            "p.user_id = u.user_id AND u.alive = 1"; // is alive
    $rs = $db->Execute($sql);
    $_communities_public = $rs->GetArray();
    $communities = array_merge( (array)$_communities_member, (array)$_communities_public );
    // 1.1.2. пересортируем список
    // ----------------------------------------- inner sort function begin
                                                 if (!function_exists( "_HelperEvent_community_sort_func"))
                                                 {
                                                   function _HelperEvent_community_sort_func($a, $b)
                                                   {
                                                        if ($a["login"] == $b["login"]) return 0; 
                                                        return ($a["login"] < $b["login"]) ? -1 : 1; 
                                                   }
                                                 }
    // ----------------------------------------< inner sort function end
    usort( $communities, "_HelperEvent_community_sort_func" );

    // 1bis    -- добавляем свой журнал в левый список
    $by_login = array(); // communities[login] = account_id
    $data4form=array();  // communities[account_id] = login
    $in=array();         // my_communities[] = account_id
    $data4form[$obj->owner->data["user_id"]] = $obj->npj_account;

    
    if (sizeof($communities) > 0)
    { 
      // 1.2. подготавливаем списки "логин-идшник"
      foreach ($communities as $item) { $by_login[$item["login"]] = $item["user_id"]; 
                              $data4form[$item["user_id"]] = $item["login"]; $c++; }
      // 1.3. если редактируем, то заполняем эти поля из массива нашего
      if ($edit)
      {
        $rs= $db->Execute( "select keyword_user_id from ".$rh->db_prefix."records_ref ".
                           "where announce>0 and record_id=".$db->Quote($obj->data["record_id"]) );
        $a = $rs->GetArray();
        foreach($a as $item) $in[] = $item["keyword_user_id"];
      }
      // 1.4. если добавляем, то надо откуда-то прочитать , ???
      else
      if (is_array( $obj->data["announce_in"] ) )
      {
        foreach( $obj->data["announce_in"] as $name=>$value )
         if (($value != "announce") && ($value != "post") && isset($by_login[ strtolower($value) ])) 
          $in[] = $by_login[ strtolower($value) ];
         else
         if ($value."@".$obj->npj_node == $data4form[$obj->owner->data["user_id"]])
          $in[] = $obj->owner->data["user_id"];

      }
    }
    if (sizeof($in) == 0) $in = 0;
               
    // 2. добавим группу (после body)
    $new_groups = array(); $gs; $c=0;
    foreach($_new_groups as $k=>$v)
    {
      if ($k == "body") 
      {
        $new_groups["announces"] = array();
        $gs.= "0";
      }
      $new_groups[$k] = &$_new_groups[$k];
      $gs.= $group_state{$c++};
    }
    $group_state = $gs;
    // 3. добавим поле
    $new_groups["announces"][] = &new FieldMultiple( &$rh, array(
                           "field" => "announce_in",
                           "maxsize" => 5,
                           "data_plain" => 1,
                           "default" => $in,
                           "data" => $data4form,
                           "db_ignore" => 1,
                           "tpl_data" => "field_multiple_announce.html:Plain",
//                           "tpl_row"  => "form.html:Row_Span",
                           "size" => 5,
                           "size_all" => 7,
                            ) ); 

    $this->rh->debug->Trace("Form tweaked");
    return $new_groups;
  }

  // -----------------------------------------------------------------
  //  - сохранение нужных рефов по сообществам
  //  NB: уникальность мы будем проверять потом и на кроликах
  //      пока надо просто анврапнуть, и всё. 

  // !!!!. -- см. про HelperPost
  // -----------------------------------------------------------------
  function &PreSave( &$data, &$principal, $is_new=false ) 
  { 
    $syndicate= $data["disallow_syndicate"]?0:1;
    if (!$data["is_announce"]) $data["is_announce"] = 1; //plain event announce

    $rh = &$this->rh; $db = &$this->rh->db; $obj = &$this->obj; //  RH, DB, OBJ
    $debug = &$rh->debug;
    $debug->Trace("ANNOUNCE HELPER NOT WASTED");

    if (!isset($data["server_datetime"])) $data["server_datetime"] = date("Y-m-d H:i:s");

    $owner = $obj->owner; // пространство, в котором находимся -- возможно, неправильное!
                          // должно быть загружено

    // 1. перекачаем из $data["announce_in"] в $this->ref с восстановлением правильного нпж-адреса
     if (!is_array($data["announce_in"])) $data["announce_in"] = explode("|",$data["announce_in"]);
     if (!is_array($data["announce_in"])) $data["announce_in"] = array();
     foreach( $data["announce_in"] as $user_id )
     {
        $need_moderation=0; $skip=0;

        $debug->Trace( $user_id );

        if ($user_id != $owner->data["user_id"])
        {
          // 1.1. Проверяем, есть ли у принципала права постить в сообщество
          // для каждого сообщества нужно проверить его тип и если что, статус.
          $rs = $db->SelectLimit("select default_membership, security_type, ".
                                        "post_membership, announce_membership from ".$rh->db_prefix."profiles ".
                                 " where user_id=".$db->Quote($user_id), 1);

            $announce = $rs->fields["announce_membership"];
          $mod = $rs->fields["post_membership"];
          $security_type      = $rs->fields["security_type"];
          $default_membership = $rs->fields["default_membership"];
          
          $rs = $db->SelectLimit("select group_rank from ".$rh->db_prefix."groups as g, ".$rh->db_prefix."user_groups as ug ".
                                 " where g.group_id = ug.group_id and g.user_id = ".$db->Quote($user_id).
                                 " and ug.user_id = ". $db->Quote($principal->data["user_id"])." order by group_rank desc ", 1);
          if (($rs->RecordCount() > 0) && ($rs->fields["group_rank"] > 0)) 
          { 
            if ($rs->fields["group_rank"] >= $mod) $need_moderation = 0;
            else $need_moderation = 1; // здесь мы устанавливаем флаг необходимости в модерации в этом сообществе
            if ($rs->fields["group_rank"] >= $announce) $can_announce = 1;
            else $can_announce = 0; // здесь мы сбрасываем флаг анонса в этом сообществе
          }
          else 
            if ($security_type == COMMUNITY_PUBLIC)
            {
             if ($default_membership >= $mod) $need_moderation = 0;
             else $need_moderation = 1; // здесь мы устанавливаем     флаг необходимости в модерации в этом сообществе
             if ($default_membership >= $announce) $can_announce = 1;
             else $need_moderation = 0; // здесь мы сбрасываем        флаг анонса в этом сообществе
            }
           else $skip = 1; // мы не имеем права постить в это сообщество
        }
        // 1.2. Получаем супертаг и заносим его в список $this->ref
        if ($can_announce || $user_id == $owner->data["user_id"])
        {
          $debug->Trace( "u could post!" );
          $rs = $db->Execute("select r.supertag, r.record_id, r.user_id from ".$rh->db_prefix."records as r, ".$rh->db_prefix."users as u ".
                 " where r.supertag = CONCAT(u.login,".$db->Quote("@").
                 ($rh->account->data["node_id"]==$rh->node_name?
                      ",u.node_id,":
                      ",u.node_id,".$db->Quote("/".$rh->node_name).",")
                 .$db->Quote(":").
                 ") and u.user_id=".$db->Quote( $user_id )." and r.user_id=u.user_id" );


          if (($rs->RecordCount() > 0))
          {
            $supertag1 = $rs->fields["supertag"];
            $debug->Trace( "publishing to: ".$supertag1 );
            if (!isset($this->ref[ $supertag1 ]))
              $this->ref[ $supertag1 ] = array( "announce" => $data["is_announce"], 
                  "syndicate" => $syndicate,
                  "group1" => 1*$data["group1"], "group2" => 1*$data["group2"], 
                  "group3" => 1*$data["group3"], "group4" => 1*$data["group4"], 
                  "server_datetime" => $data["server_datetime"],
                  "user_datetime"   => $data["user_datetime"],
                  "keyword" => "", // пока в рубрики даже не пытаемся
                  "need_moderation" => $need_moderation );
            $debug->Trace( $supertag1. " announcing! " );
          }
        }
     }

    // 2. вызовем родительский PreSave( d,p )
    return HelperPost::PreSave( &$data, &$principal, $is_new );
  }

// EOC { HelperEvent }
}


?>