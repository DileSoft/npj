<?php
// function IsGrantedTo( $method, $object_class, $object_id, $options="" )

// GRANTED --> только если ЕСТЬ В СПИСКЕ $rh->node_admins

// вызывает "acl_text"

  return $this->IsGrantedTo( "acl_text", $object_class, $object_id, $rh->node_admins );


?>