<?php

 $this->Load(3);

 if ($this->data["account_type"] == 0)
 {
   // запускаем нормальный поц.
   $obj = &new NpjObject( &$rh, $this->name.":/post" );
   $this->record = &$obj;
   return 
   $obj->Handler( $this->method, &$params, &$principal );
 }
 else
  $rh->Redirect( $this->Href( $principal->data["login"]."@".$principal->data["node_id"].":/post/".
                              ($params[0] == "announce"?"announce/":"").
                              $this->data["login"], NPJ_ABSOLUTE, IGNORE_STATE ), IGNORE_STATE);

?>