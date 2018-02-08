<?php

// function IsGrantedTo( $method, $object_class, $object_id, $options="" )
//
// возвращает GRANTED, если выполн€ютс€ оба услови€:
//  * в кэше есть такой объект с уровнем детализации не менее второго
//  * у этого объекта есть поле "active" и оно установлено в true/единицу
// иначе DENIED

   $obj = $this->config->cache->Lookup( $object_class, $object_id, 2 );
   if ($obj === false) return DENIED;
   if ($obj["active"]) return GRANTED;
                  else return DENIED;

?>