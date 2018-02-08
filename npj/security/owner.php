<?php

// function IsGrantedTo( $method, $object_class, $object_id, $options="" )

// options["cache"] = array( "owner_user_id", ... ) -- значит нужно кэшировать группы для owner_user_id

// GRANTED --> только если ЭТО ВЛАДЕЛЕЦ ОБЪЕКТА

// пробует достать объект из кэша с уровнем детализации 1 (ключи)
// (там обязательно должен присутствовать user_id)
// после чего сравнивает user_id с тем, что есть у принципала

   //$debug->Trace($method."|".$object_class."|".$object_id."|".$options);

   $user_ids = array( "user_id", "owner_user_id", "owner_id", "author_id" );

   $obj = $cache->Restore( $object_class, $object_id, 1 );
   if ($obj === false) 
   {
     $debug->Trace_R( $cache->data );
     $debug->Error( "Security[ OWNER ]: object ($object_class, $object_id) was not cached. but should be." );
   }

   foreach ($user_ids as $user_id)
    if (isset($obj[$user_id]))
     if ($obj[$user_id] == $this->data["user_id"]) return GRANTED;

   if (is_array($options) && is_array($options["cache"]))
     foreach($options["cache"] as $field)
       if ($obj[$field] > 0)
         $rh->object->CacheGroups( &$this, $obj[$field] );

   foreach ($user_ids as $user_id)
    if (isset($obj[$user_id]))
    {
      $maxrank = $cache->Restore( "maxrank_".$this->data["user_id"], $obj[$user_id], 1 );
      $debug->Trace("maxrank [ ".$obj[$user_id]." ] = ".$maxrank);
      if ($maxrank >= $rh->workgroups_access) return GRANTED;
    }

   if ($object_class == "record")
     if ($cache->Restore( "account", $obj["user_id"], 1 ))
     {
       $debug->Trace( "go deeper -> " );
       return $this->IsGrantedTo( $method, "account", $obj["user_id"], 
                                  array("cache"=>array("owner_user_id") ) );
     }
   return DENIED;

?>