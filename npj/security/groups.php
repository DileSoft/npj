<?php

// function IsGrantedTo( $method, $object_class, $object_id, $options="" )
// GRANTED --> только если …‘’њ ‚ ѓђ“ЏЏ…

// !!!! ничего не кэширует, так как нет никакой уверенности, что кэширование нужно
// !!! не учитывает ситуации чего-то там про группу "members" -  http://wiki.oversite.ru/npzhzaprosversija2

// !!! код не оттестирован как следует

   $rh->debug->Milestone( "Security [groups] launched." );

   if ($object_class!="record") 
     // MODULE invasion
     if ($rh->modules[$object_class] &&
         $rh->modules[$object_class]["is_granted_to"]   &&
         $rh->modules[$object_class]["is_granted_to"][$method])
         return $rh->object->PassToModule( $object_class, "is_granted_to", 
                                           array( "method"       => $method,
                                                  "object_class" => $object_class,
                                                  "object_id"    => $object_id,
                                                  "options"      => $options,
                                                ), &$this );
     else
       $rh->debug->Error( "Security[ groups ]: Cannot work with object of class $object_class {$object_id}", 3 );

   // а вот почему бы тут не разрешить владельцу сразу?
   if ($this->IsGrantedTo( "owner", $object_class, $object_id, $options )) return GRANTED;


   $obj = $rh->cache->Restore( $object_class, $object_id, 1 );
   if ($obj === false) $rh->debug->Error( "Security[ groups ]: object [$object_class, $object_id] was not cached. but should be.", 2 );
   if (!($object_id == $obj["id"])) $rh->debug->Error( "Security[ groups ]: Where my ID?", 3 );

   if ($obj["by_module"])
     // MODULE invasion
     if ($rh->modules[$obj["by_module"]] &&
         $rh->modules[$obj["by_module"]]["is_granted_to"]   &&
         $rh->modules[$obj["by_module"]]["is_granted_to"][$method])
         return $rh->object->PassToModule( $obj["by_module"], "is_granted_to", 
                                           array( "method"       => $method,
                                                  "object_class" => $obj["by_module"],
                                                  "object_id"    => $object_id,
                                                  "options"      => $options,
                                                ), &$this );


/*   $acl = $rh->cache->Restore( $object_class."_acl_".$options, $object_id, 2 );
   if ($acl === false)
   {
*/
   // Все аккаунты, находящиеся в реферированных группах
   $sql = "select ug.user_id from ".$rh->db_prefix."user_groups as ug, ".
      $rh->db_prefix."groups as g, ".$rh->db_prefix."records as r where ".
      "(r.group1 = g.group_id OR r.group2 = g.group_id OR ".
      "(r.group3 = g.group_id AND r.group2>=0) OR ".
      " r.group4 = g.group_id) ".
      "AND ug.group_id=g.group_id ".
      "AND g.group_rank > 0 ".
      "AND r.record_id = ".$rh->db->Quote($object_id);
     $rs = $rh->db->Execute( $sql );
     $rh->debug->Trace( $sql );
     if (!$rs) $rh->debug->Error("Security [ group ] query is bad" );
     if ($rs->RecordCount() > 0)
     {
       $__m = $rs->GetArray();
       $__u = array();
       foreach( $__m as $k=>$v )
         $__u[] = $v["user_id"];
       $sql = "select u.user_id, u.account_type from ".$rh->db_prefix."users as u where user_id in (".
              implode( ",", $__u ).")";
       $rs = $rh->db->Execute( $sql );
     }

     // ??? тщательно обдумать, всё ли верно в следующей строке
     if ($rs->RecordCount() === 0) 
      if ($obj["group1"] == 0)        return GRANTED;
      else                            return DENIED;

     $members = $rs->GetArray();
     $memberlist =array();
     $groupings = array();

     // первый проход -- щучим сообщества

     // оставляем только не-пользователей
     foreach ($members as $member)
      if ($member["account_type"] != ACCOUNT_USER) $groupings[] = $member["user_id"];

     // если не пусто, то выбираем из них тех, кто имеет отношение к записи
     if (sizeof($groupings) > 0)
     {
       $sql2 = "select distinct ug.user_id from ".
                $rh->db_prefix."user_groups as ug, ".
                $rh->db_prefix."records_ref as ref, ".
                $rh->db_prefix."groups as g ".
                " where ".
                " ug.group_id=g.group_id and g.group_rank > 0 and ".
                " g.group_rank >= ".$db->Quote($rh->globalgroups_security_rank)." and ".
                " ref.keyword_user_id = g.user_id and ".
                " ref.record_id = ".$db->Quote( $object_id )." and ".
                " (ref.keyword_user_id in (".implode(",",$groupings).") )";
       $debug->Trace("MEMBERS: ".$sql2 );
       $rs2 = $rh->db->Execute( $sql2 );
       $a   = $rs2->GetArray();
       foreach ($a as $member) 
        $memberlist[$member["user_id"]] = GRANTED;
     }

//     if ($debug->kuso)
//     $debug->Error_R( $memberlist );

     // второй проход -- личные ништяки пользователей
     foreach ($members as $member) 
      $memberlist[$member["user_id"]] = GRANTED;

     // третий проход -- "глобальные группы доступа"
     if ($obj["group2"] == ACCESS_GROUP_COMMUNITIES)
       if ($obj["group3"] > 0)
       {
         // проверить, есть ли принципал в РГ с юзеридом, лежащим в group3
         $rh->object->CacheGroups( &$this, $obj["group3"] );
         $maxrank = $cache->Restore( "maxrank_".$this->data["user_id"], $obj["group3"], 1 );
         if ($maxrank >= $rh->globalgroups_security_rank) 
           $memberlist[ $this->data["user_id"] ] = GRANTED;
       }

//     $debug->Error_R( $maxrank );

/*     
     $rh->cache->Store( $object_class."_acl_".$options, $object_id, 2, $acl );

   }
*/

//   $debug->Trace_R( $memberlist );

   if ($memberlist[ $this->data["user_id"] ] == GRANTED) return GRANTED;
   return $this->IsGrantedTo("owner", $object_class, $object_id);


?>