<?php

// function IsGrantedTo( $method, $object_class, $object_id, $options="" )

// GRANTED --> только если principal состоит в одной из групп object`a, ранг которой Ќ≈ ћ≈Ќ№Ў≈ $options

// пробует достать из кэша "_ranks_*"
// если не получаетс€, то берЄт доступные пользователю ранки из таблицы групп объекта
   $maxrank = $cache->Restore( "maxrank_".$this->data["user_id"], $object_id, 1 );
   if ($maxrank === false)
   {
     $ranks = $cache->Restore( "_ranks_".$object_class, $object_id, 1 );
     if ($ranks === false)
     {
       $rs = $db->Execute( "select distinct group_rank from ".$rh->db_prefix."groups as g, ".$rh->db_prefix."user_groups as ug ".
                           "where g.user_id=".$db->Quote($object_id).
                           " and g.group_id=ug.group_id ".
                           " and ug.user_id=".$db->Quote($this->data["user_id"]).
                           " order by group_rank desc" );
       $a = $rs->GetArray();
       $ranks = array();
       foreach($a as $item) $ranks[ $item["group_rank"] ] = $item["group_rank"];
       $cache->Store( "_ranks_".$object_class, $object_id, 1, $ranks );
     }
     foreach ($ranks as $rank )
      if ($rank >= $options) return GRANTED;
   }
   else if ($maxrank >= $options) return GRANTED;

   return DENIED;

?>