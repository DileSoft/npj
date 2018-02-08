<?php

// function IsGrantedTo( $method, $object_class, $object_id, $options="" )

// GRANTED --> только если ЕСТЬ В СПИСКЕ ДОСТУПА

// $options == "user1 user2 user3" logins


   $rh->debug->Milestone( "Security [ACL] launched." );

   $acl = explode(" ", $options);

   $patterns[] = "*";
   $patterns[] = "@".$this->data["node_id"];
   $patterns[] = $this->data["login"]."@".$this->data["node_id"];
   $patterns[] = $this->data["login"];

   $roles = explode( " ", $this->data["__roles"] );
   foreach( $roles as $item )
    $patterns[] = "~".$item;

   $result = DENIED;
   foreach ($patterns as $p)
   {
     if (in_array( $p, $acl )) $result = GRANTED;
     if (in_array( "!".$p, $acl )) { $result = DENIED; break; }
   }
   $rh->debug->Milestone( "Security [ACL] @ deep end." );
   return $result;

?>