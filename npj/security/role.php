<?php

// function IsGrantedTo( $method, $object_class, $object_id, $options="" )

// GRANTED --> òîëüêî åñëè ÅÑÒÜ ÒÀÊÀß ÐÎËÜ

// $object_class = "editor"


   $rh->debug->Milestone( "Security [Roles] launched . ".$object_class );

   $roles = explode(" ", $this->data["__roles"]);

   foreach ($roles as $role)
    if ($object_class == $role) return GRANTED;

   return DENIED;

?>
