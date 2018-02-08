<?php

// function IsGrantedTo( $method, $object_class, $object_id, $options="" )

// GRANTED --> только если ЕСТЬ В СПИСКЕ ДОСТУПА

// пробует загрузить ACL из кэша, если не получается, то загружает его из БД
// после чего проверяет, есть ли логин принципала в этом ACL

// но сначала загружает из кэша объект, чтобы узнать его id

   $rh->debug->Milestone( "Security [ACL] launched." );

   if ($options == "") $options = "read";

   // а вот почему бы тут не разрешить владельцу сразу?
   if ($this->IsGrantedTo( "owner", $object_class, $object_id, $options )) $own=true;

   // проверить на "read"
   if (($options != "read") && ($options != "actions" ) && ($object_class == "record"))
    if (!$this->IsGrantedTo( "acl", $object_class, $object_id, "read")) return DENIED;

   $obj = $rh->cache->Restore( $object_class, $object_id, 1 );
   if ($obj === false) $rh->debug->Error( "Security[ ACL ]: object [$object_class, $object_id] was not cached. but should be.", 2 );
   if (!($object_id = $obj["id"])) 
   {
//r     $debug->Trace_R($obj);
     $rh->debug->Trace( "Security[ ACL ]: Where my ID? ($object_class,$object_id)");
     return DENIED;
   }

   $debug->Trace("<b>get some aclist for $object_class, $object_id, $options</b>");
   $acl = $rh->cache->Restore( $object_class."_acl_".$options, $object_id, 2 );
   if ($acl === false)
   {
     $debug->Trace("not in cache");
     $rs = $db->Execute( "select acl from ".$rh->db_prefix."acls where ".
                         "object_type = ".$db->Quote($object_class)." and ".
                         "object_id   = ".$db->Quote($object_id)." and ".
                         "object_right= ".$db->Quote($options) );
     if ($rs->RecordCount() === 0)
     {
       // нужного ACL в базе нет
       $debug->Trace("not in DB, going to parent");

       if ($rh->ACLS_PARENT[$options])
        return $this->IsGrantedTo( "acl", $object_class, $object_id, $rh->ACLS_PARENT[$options] );
       else
        return DENIED; 
     } 

     $acl = &$rs->fields;
     $rh->cache->Store( $object_class."_acl_".$options, $object_id, 2, $acl );
   }

   // а вот почему бы тут не разрешить владельцу сразу?
   if ($own) return GRANTED;

   $acl["acl"] = strtolower($acl["acl"]);
   $acl["acl"] = str_replace(" ", "\n", str_replace("\r", "", $acl["acl"])); 
   // added by Kuso for spaced ACLS compliance
   
   $acl_exploded = explode("\n", $acl["acl"]);
   if ($invert) $acl_exploded[] = "!*";

   if ($invert) // инвертирование acl (added by Kuso)
    foreach( $acl_exploded as $k=>$v)
     if ($v == "") $acl_exploded[$k] = "*";
     else
     if ($v{0} == "!") $acl_exploded[$k] = substr($v,1);
     else $acl_exploded[$k] = "!".$v;

//r   $debug->Trace_R( $acl_exploded );

   // работа с группами пользователя тут! надо залить в кэш группы того аккаунта, которому принадлежит данный объект
   if ($obj["user_id"])
   {
     $rh->object->CacheGroups( &$this, $obj["user_id"] );
     $maxrank          = $cache->Restore("maxrank_".$this->data["user_id"], $obj["user_id"]);
     $ingroups         = $cache->Restore("ingroups_".$this->data["user_id"], $obj["user_id"]);
     $debug->Trace("maxrank=$maxrank, ingroups=");
//r     $debug->Trace_R($ingroups);
     
     // для рабочих групп
     if (($rh->workgroups_access == WORKGROUPS_MANAGED) && ($maxrank >= WORKGROUPS_MANAGED))
      return GRANTED; // этот чувак уже крут

     foreach( $acl_exploded as $k=>$acline )
      if ($acline == "&")
       if ($maxrank > GROUPS_REQUESTS) 
       { 
//r         $debug->Trace("we are in [==&==]");
         $acl_exploded[$k] = "*";
       } else;
      else
      if ($acline{0} == "&")
      {
        $flag=1;
        $group_name = substr($acline,1);
        $group = $cache->Restore("groups", $group_name);
//r        $debug->Trace( "=>" . $group_name ); 
//r        $debug->Trace_R( $group ); 
//r        $debug->Trace_R($ingroups[$group["group_id"]]);
        if ($group !== false)
          if (($group["group_rank"] < $maxrank) || isset($ingroups[$group["group_id"]]))
          {
//r            $debug->Trace("we are in [".$group_name."]");
            $acl_exploded[$k] = "*";
          }
      }
      // not groups (copy-paste)
     foreach( $acl_exploded as $k=>$acline )
      if ($acline{0} == "!")
      if ($acline == "!&")
       if ($maxrank > GROUPS_REQUESTS) 
       { 
//r         $debug->Trace("we are in [==&==] BUTT");
         $acl_exploded[$k] = "!*";
       } else;
      else
      if ($acline{1} == "&")
      {
        $group_name = substr($acline,2);
        $group = $cache->Restore("groups", $group_name);
//r        $debug->Trace( $group_name ); 
//r        $debug->Trace_R( $group ); 
        if ($group !== false)
          if (($group["group_rank"] < $maxrank) || isset($ingroups[$group["group_id"]]))
          {
//r            $debug->Trace("we are in [".$group_name."] BUTT");
            $acl_exploded[$k] = "!*";
          }
      }
      // --end (copy-paste)
/*
     $debug->Trace("maxrank = $maxrank"); 
     $debug->Trace_R( $ingroups ); 
     $debug->Error("waita");
*/
   }

   foreach( $acl_exploded as $k=>$v )
   if ($v != "")
   if (($v != "*") && ($v != "!*"))
   { $pos = strpos($v, "@");
    if ($pos === false) $acl_exploded[$k].="@".$rh->node_name;
    if ($pos === strlen($v)-1) $acl_exploded[$k].=$rh->node_name;
   }

//   $debug->Trace( "ACL [".$acl["acl"]."]" );
//   if ($options == "banlist")


   $debug->Trace( "Maximum exploded" );
//r   $debug->Trace_R( $acl_exploded );

   $patterns[] = "*";
   $patterns[] = "@".$this->data["node_id"];
   $patterns[] = $this->data["login"]."@".$this->data["node_id"];

   // поддержка глобальных групп
   if (is_array($this->data["global_membership"]))
     foreach( $this->data["global_membership"] as $__npj=>$__v )
      $patterns[] = $__npj;

   if ($this->data["user_id"] == 1) $patterns = array("*");

   $result = DENIED;
   foreach ($patterns as $p)
   {
     if (in_array( $p, $acl_exploded )) $result = GRANTED;
     if (in_array( "!".$p, $acl_exploded )) { $result = DENIED; $denied=1; break; }
   }
   if (!$denied)
   {
     // 2nd cycle
     // team   => team
     // team@  => team
     // team@npj => team
     // --team@hui--
     foreach( $acl_exploded as $npj_addr)
     { 
       // !pusik@npj
       // team
      //if ($community_hash[$npj_addr]) $result = GRANTED
     }

   }
   // special guest access
   if (!$this->IsGrantedTo("noguests"))
   {
     if ( in_array( "guest@".$rh->node_name, $acl_exploded) &&
         !in_array( "!guest@".$rh->node_name, $acl_exploded) )
     {
       $denied = 0;
       $result = GRANTED;
     }
   }

   $rh->debug->Milestone( "Security [ACL] @ deep end." );
   return $result;

?>