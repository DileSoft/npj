<?php

// function IsGrantedTo( $method, $object_class, $object_id, $options="" )
// возвращает GRANTED всегда

   if (!isset($this->data["user_id"]) || ($this->data["user_id"] == 1)) return DENIED;

   return GRANTED;

?>