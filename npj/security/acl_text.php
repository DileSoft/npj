<?php

// function IsGrantedTo( $method, $object_class, $object_id, $options="" )

// GRANTED --> только если ЕСТЬ В СПИСКЕ ДОСТУПА

// !nb: не работает с группами, конечно!
// !nb: зато работает с ролями

// $options == "user1 user2 user3" logins



   $rh->debug->Milestone( "Security [ACL] launched." );

   $acl = explode(" ", strtolower($options));

   $acl_exploded = $acl;
   foreach( $acl_exploded as $k=>$v )
   if ($v != "")
   if (($v != "*") && ($v != "!*"))
   { $pos = strpos($v, "@");
    if ($pos === false) $acl_exploded[$k].="@".$rh->node_name;
    if ($pos === strlen($v)-1) $acl_exploded[$k].=$rh->node_name;
   }
   $acl = $acl_exploded;

   $patterns[] = "*";
   $patterns[] = "@".$this->data["node_id"];
   $patterns[] = $this->data["login"]."@".$this->data["node_id"];
   $patterns[] = $this->data["login"];

   // поддержка глобальных групп
   if (is_array($this->data["global_membership"]))
     foreach( $this->data["global_membership"] as $__npj=>$__v )
      $patterns[] = $__npj;

   // поддержка ролей
   $roles = explode(" ", $this->data["__roles"]);
   foreach($roles as $role) $patterns[] = "~".$role;

   $result = DENIED;
   foreach ($patterns as $p)
   {
     if (in_array( $p, $acl )) $result = GRANTED;
     if (in_array( "!".$p, $acl )) { $result = DENIED; break; }
   }
   $rh->debug->Milestone( "Security [ACL] @ deep end." );
   return $result;

?>