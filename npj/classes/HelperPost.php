<?php
/*
    HelperPost( &$rh, &$obj ) -- Хелпер для формы редактирования постов -- сообщений
      * у $obj:
          $obj->helper
          $obj->owner

  ---------
   * добавляет в группу "реф" поле "коммунитис"
   * дополняет $this->ref[...] данными из поля "коммунитис"

=============================================================== v.1 (Kuso)
*/

class HelperPost extends HelperRecord
{

  // -----------------------------------------------------------------
  // - добавим в группу ref поле communities
  function &TweakForm( &$form_fields, &$group_state, $edit=false )
  {
    $rh = &$this->rh; $db = &$this->rh->db; $obj = &$this->obj; //  RH, DB, OBJ
    $debug = &$rh->debug;
    $new_groups = &HelperRecord::TweakForm( &$form_fields, &$group_state, $edit ) ;

    // 1. посмотрим, в каких сообществах опубликовано
    $access_group_class = $this->rh->global_accessgroup_class;
    // 1.1. Получим список всех сообществ, в которых уже состоит пользователь
    $sql= "SELECT u.user_id, u.login FROM ".
            $rh->db_prefix."groups as g, ".
            $rh->db_prefix."user_groups as gu, ".
            $rh->db_prefix."users as u WHERE ".
            "gu.group_id = g.group_id AND g.user_id = u.user_id AND ".
            "gu.user_id = ".$db->Quote($rh->account->data["user_id"])." AND ".
            "u.owner_user_id <> 0 AND g.group_rank >= ".GROUPS_LIGHTMEMBERS." AND ".
            ($access_group_class?("u. account_class!=".$db->Quote($access_group_class)." AND "):"").
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
                                                 if (!function_exists( "_HelperPost_community_sort_func"))
                                                 {
                                                   function _HelperPost_community_sort_func($a, $b)
                                                   {
                                                        if ($a["login"] == $b["login"]) return 0; 
                                                        return ($a["login"] < $b["login"]) ? -1 : 1; 
                                                   }
                                                 }
    // ----------------------------------------< inner sort function end
    usort( $communities, "_HelperPost_community_sort_func" );

    $by_login = array(); // communities[login] = account_id
    $data4form=array();  // communities[account_id] = login
    $in=array();         // my_communities[] = account_id
    if (sizeof($communities) > 0)
    { 
      // 1.2. подготавливаем списки "логин-идшник"
      foreach ($communities as $item) { $by_login[$item["login"]] = $item["user_id"]; 
                              $data4form[$item["user_id"]] = $item["login"]; $c++; }
      // 1.3. если редактируем, то заполняем эти поля из массива нашего
      if ($edit)
      {
        $rs= $db->Execute( "select keyword_user_id from ".$rh->db_prefix."records_ref ".
                           "where keyword_user_id <> owner_id and record_id=".$db->Quote($obj->data["record_id"]) );
        $a = $rs->GetArray();
        foreach($a as $item) $in[] = $item["keyword_user_id"];
      }
      // 1.4. если добавляем, то надо откуда-то прочитать , ???
      else
      if (is_array( $obj->data["communities"] ))
      {
        foreach( $obj->data["communities"] as $name=>$value )
        {
          if (strpos($value, "@".$data["node_id"]) !== false)
           $value = substr($value, 0, strpos($value, "@".$data["node_id"]));
          if (($value != "post") && isset($by_login[ strtolower($value) ])) 
           $in[] = $by_login[ strtolower($value) ];
        }
      }
    }
    if (sizeof($in) == 0) $in = 0;

    // 2. добавим поле
    $new_groups["ref"][] = &new FieldMultiple( &$rh, array(
                           "field" => "communities",
                           "maxsize" => 5,
                           "data_plain" => 1,
                           "default" => $in,
                           "data" => $data4form,
                           "db_ignore" => 1,
                           "tpl_data" => "field_multiple_post.html:Plain",
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

  // !!!.. постинг на другие узлы пока нереализован.
  //       хм. в коммьюнити можно связывать только сообщения.
  // !!!.. и пока только в корень при публикации.
  // !!!.. и ещё надо придумать, как в дальнейшем коммьюнити рубрицировать.
  // !!!!. не всё понятно с перемодерацией пересохранённого сообщения
  // !!!!. возможно, придётся перенести все проверки прав постов в HelperPost->_UpdateRef
  //       и там же сделать так, чтобы неизменённые параметры не пересохранялись
  // -----------------------------------------------------------------
  function &PreSave( &$data, &$principal, $is_new=false ) 
  { 
    $syndicate= $data["disallow_syndicate"]?0:1;

    $rh = &$this->rh; $db = &$this->rh->db; $obj = &$this->obj; //  RH, DB, OBJ
    $debug = &$rh->debug;

    $owner = $obj->owner; // пространство, в котором находимся -- возможно, неправильное!
                          // должно быть загружено

    if (!isset($data["server_datetime"])) $data["server_datetime"] = date("Y-m-d H:i:s");

    $debug->Trace("We at PreSave Helper Post!");
    $debug->Trace_R( $data );
    // 1. перекачаем из $data["communities"] в $this->ref с восстановлением правильного нпж-адреса
     if (!is_array($data["communities"])) $data["communities"] = explode("|",$data["communities"]);
     if (!is_array($data["communities"])) $data["communities"] = array();
     if ($data["rare"]["replicator_user_id"]) $poster_id = $data["rare"]["replicator_user_id"];
                                         else $poster_id = $principal->data["user_id"];
     foreach( $data["communities"] as $user_id )
     {
        $need_moderation=0; $skip=0;

        $debug->Trace( $user_id );

        // 1.1. Проверяем, есть ли у принципала права постить в сообщество
        // для каждого сообщества нужно проверить его тип и если что, статус.
        $rs = $db->SelectLimit("select security_type, default_membership, ".
                                      "post_membership, announce_membership from ".$rh->db_prefix."profiles ".
                               " where user_id=".$db->Quote($user_id), 1);
        $announce = $rs->fields["announce_membership"];
        $mod      = $rs->fields["post_membership"];
        $security_type      = $rs->fields["security_type"];
        $default_membership = $rs->fields["default_membership"];
        $debug->Trace("ranks needed: $announce, $mod");
        $rs = $db->SelectLimit("select group_rank from ".$rh->db_prefix."groups as g, ".$rh->db_prefix."user_groups as ug ".
                               " where g.group_id = ug.group_id and g.user_id = ".$db->Quote($user_id).
                               " and ug.user_id = ". $db->Quote($poster_id)." order by group_rank desc ", 1);
        $debug->Trace("ranks gathered: ".$rs->fields["group_rank"]." at ".$rs->RecordCount());
        $debug->Trace("gather params: $user_id, $poster_id");
        if (($rs->RecordCount() > 0) && ($rs->fields["group_rank"] > 0)) 
        { 
          if ($rs->fields["group_rank"] >= $mod) $need_moderation = 0;
          else $need_moderation = 1; // здесь мы устанавливаем флаг необходимости в модерации в этом сообществе
        }
         else 
         if ($security_type == COMMUNITY_PUBLIC)
         {
          if ($default_membership >= $mod) $need_moderation = 0;
          else $need_moderation = 1; // здесь мы устанавливаем     флаг необходимости в модерации в этом сообществе
        }
         else $skip = 1; // мы не имеем права постить в это сообщество

        // 1.2. Получаем супертаг и заносим его в список $this->ref
        if (!$skip)
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
              $this->ref[ $supertag1 ] = array( "announce" => 0, "syndicate" => $syndicate,
                  "group1" => 1*$data["group1"], "group2" => 1*$data["group2"], 
                  "group3" => 1*$data["group3"], "group4" => 1*$data["group4"], 
                  "server_datetime" => $data["server_datetime"],
                  "user_datetime"   => $data["user_datetime"],
                  "keyword" => "", // пока в рубрики даже не пытаемся
                  "need_moderation" => $need_moderation );
          }
        } else $debug->Trace("sorry, skipping");
     }

    // 2. вызовем родительский PreSave( d,p )
    return HelperRecord::PreSave( &$data, &$principal, $is_new );
  }

  // -----------------------------------------------------------------
  function &_Automate( &$data, &$principal )
  {
    $rh = &$this->rh; $db = &$this->rh->db; $obj = &$this->obj; //  RH, DB, OBJ
    $debug = &$rh->debug;

    $data = &HelperRecord::_Automate( &$data, &$principal );
    $rules = $this->ref_rules;

    $groups = array();
    //   2.2.1. сформировать правила доступа к сообщению
    //  04.01.2005 -- patch by kuso@npj: хочется, чтобы они не наследовались -- брались всегда первые
    //                                   и переписывались в жёсткую
    if (sizeof($rules["_groups"]) > 0)
    {
      $nobody = 0; $confidents = 0; $communities = 0;
      
      foreach( $rules["_groups"] as $_rule )
      {
        $rule = $_rule; break;
      }

        $numbers = explode(",", $rule);
      // переписываем ВСЕГДА
      // следующее условие мною не понято. Кусо, 04.01.2005
        if ($numbers[1] == -1) 
        { $numbers[0] = $rh->account->group_nobody;
          $nobody=$numbers; } // nobody (private) & stop looking

      if ($numbers[1] == ACCESS_GROUP_CONFIDENTS) 
      { $numbers[0] = $rh->account->group_friends;
        $confidents =$numbers;  
      } // confidents

      if ($numbers[1] == ACCESS_GROUP_COMMUNITIES) 
      { $numbers[0] = $rh->account->group_communities;
        if ($rh->account->group_communities == 0)
          $debug->Error("<h1>rh->account->group_communities == 0</h1>");
        $communities=$numbers;  
      } // communities
         
      // 2.2.2. применить правила доступа к сообщению   
      $result = 0;
      if ($data["group2"] == -1) ; // user setting to NOBODY prevails
      else
      if (is_array($nobody)) $result = $nobody; // automate to NOBODY
      else
      if (is_array($communities)) $result = $communities; // automate to COMMUNITIES
      else
      if (is_array($confidents)) $result = $confidents; // automate to CONFIDENTS
      else
      if (sizeof($numbers) > 0) { $result = $numbers; } // automate to CUSTOM CONF.
      // else leave as it is.

      if (is_array($result))
      { $data["group1"] = $result[0]; $data["group2"] = $result[1];
        $data["group3"] = $result[2]; $data["group4"] = $result[3];
      }

      /// пройтись по готовым рефам и прописать доступ
      foreach( $this->unwrapped_refs as $k=>$v )
      {
        $this->unwrapped_refs[$k]["group1"] = 1*$data["group1"];
        $this->unwrapped_refs[$k]["group2"] = 1*$data["group2"];
        $this->unwrapped_refs[$k]["group3"] = 1*$data["group3"];
        $this->unwrapped_refs[$k]["group4"] = 1*$data["group4"];
      }
    }

    //   2.3. сформировать и применить правила перепубликации
    //        в $data["communities"] СЕЙЧАС нужно положить нужные юзер-иды
    if (!$this->__kuso_recursion_flag)
    if (sizeof($rules["_communities"]) > 0)
    { $records = array(); 
      foreach($rules["_communities"] as $rule)
      { $numbers = explode(",", $rule);
        foreach($numbers as $number) $records[] = $db->Quote($number);
      }
      $rs = $db->Execute("select user_id from ".$rh->db_prefix."users where root_record_id in (".
                         implode(",",$records). ")");
      $a = $rs->GetArray();
      if (!is_array($data["communities"])) $data["communities"] = explode("|",$data["communities"]);
      if ($data["communities"][0] == 0) $data["communities"] = array();
      foreach($a as $item) $data["communities"][] = $item["user_id"];

      $debug->Trace("COMM");
      $debug->Trace_R( $data["communities"] );

      // у нас изменился $data["communities"], блин. 
      // Корявость архитектуры обязывает нас пойти и скопировать кусок кода или пойти в рекурсию.
      $this->__kuso_recursion_flag=1;
      unset($this->ref_rules);
      //unset($this->ref);
      return $this->PreSave( &$data, &$principal, $is_new );
    }

    //$debug->Error("done");

    return $data;
  }

  // one migrated from handlers/record/!form_record
  function &CreateAccessFields( &$access_group, &$record, $is_new, $automate=NULL, $selgroups = NULL )
  {
     $rh = &$this->rh; $db = &$rh->db; $debug = &$rh->debug;
     $rh->UseClass("FieldMultiple", $rh->core_dir );
     $rh->UseClass("FieldMultiplePlus", $rh->core_dir );

     // accessgroups
     if ($rh->global_accessgroup_class)
     {
       $sql = "select u.user_name, u.login, u.node_id, u.user_id from ".$rh->db_prefix."users as u, ".
                                                                        $rh->db_prefix."user_groups as ug".
              " where u.account_class = ".$db->Quote($rh->global_accessgroup_class).
              (!($automate && ($rh->account->data["account_type"] != ACCOUNT_USER))?
                " and ug.group_id = ".$db->Quote($rh->account->group_communities)
                :""). // только те, в которых состоит автор
              " and ug.user_id = u.user_id ".
              " order by u.login asc";
       $rs  = $db->Execute( $sql );
       $a   = $rs->GetArray();
       $global_accessgroups = array( 0 => "Только членам тех сообществ, где опубликовано" ); // !!!! -> to msgset
       foreach($a as $k=>$v)
         $global_accessgroups[ $v["user_id"] ] = "Только членам группы &laquo;<b>".$v["user_name"]."</b>&raquo;"; // !!!! -> to msgset
       $global_accessgroups_default = 0;
     }

    $rs = $db->Execute("SELECT group_id, group_name FROM ".$rh->db_prefix."groups WHERE user_id=".
          $db->Quote($rh->account->data["user_id"])
          ." and group_rank=".GROUPS_FRIENDS." and is_system=0;"); $a=$rs->GetArray();
    $data4form=array(); $seldata4form=array();
    if (sizeof($a)==0) ; // ???refactor -- нужно показывать кустомное сообщение вместо селектора 
    else foreach ($a as $item) $data4form[$item["group_id"]]=$item["group_name"];

    if ($automate) // automate-mode
    {
      $_selgroups = $selgroups;
      $selgroups = explode(",",$selgroups);
      //$debug->Error( $_selgroups );
      $seldata4form=-1;
      if ($selgroups[0] == $rh->account->group_nobody) $seldata4form=0;
      else
      if ($selgroups[0] == $rh->account->group_friends) $seldata4form=-2;
      else
      if ($selgroups[0] == $rh->account->group_communities) 
      {
        $seldata4form=-3;
        $global_accessgroups_default = $selgroups[2]; // в третьей группе будем хранить глобальную группу доступа
      }
      else
       $seldata4form = $selgroups; 
      if ((sizeof($seldata4form) == 0) || !isset($_selgroups)) $seldata4form = -1;
    }
    else // normal-mode
    { 
      if (!$is_new) 
      {
        if ($record->data["group1"] == 0) $seldata4form=-1;
        else
        if ($record->data["group1"] == $rh->account->group_nobody) $seldata4form=0;
        else
        if ($record->data["group1"] == $rh->account->group_friends) $seldata4form=-2;
        else
        if ($record->data["group1"] == $rh->account->group_communities) 
        {
          $seldata4form=-3;
          $global_accessgroups_default = $record->data["group3"]; // в третьей группе будем хранить глобальную группу доступа
        }
        else
        {
          if ($record->data["group1"]) $seldata4form[] = $record->data["group1"];
          if ($record->data["group2"]) $seldata4form[] = $record->data["group2"];
          if ($record->data["group3"]) $seldata4form[] = $record->data["group3"];
          if ($record->data["group4"]) $seldata4form[] = $record->data["group4"];
        }
      }
      else
      {
        $seldata4form = $this->rh->post_access_default;
      }
    
    }

    $post_access_field_config = array(
                          "field" => ($automate?"_":"")."groups",
                          "maxsize" => 4,
                          "data_plain" => 1,
                          "default" => $seldata4form,
                          "data" => $data4form,

                          "radio_data"    => $global_accessgroups,
                          "radio_default" => $global_accessgroups_default,

                          "db_ignore" => 1,
                                     );
    // надо бы отключить кое-что у сообществ
    if ($automate && ($rh->account->data["account_type"] != ACCOUNT_USER))
    {
       $post_access_field_config["presets_block"] = array( ACCESS_GROUP_CONFIDENTS, ACCESS_GROUP_PRIVATE, -10 );
    }

    if (sizeof($global_accessgroups) > 1)
    {
      $access_group[] = &new FieldMultiplePlus( &$rh, $post_access_field_config );
    }
    else
    {
      $access_group[] = &new FieldMultiple( &$rh, $post_access_field_config );
    }
    return $access_group;
  }

// EOC { HelperPost }
}


?>