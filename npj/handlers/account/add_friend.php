<?php

  $rh->Redirect
//  $debug->Error
  ($object->Href
    ($this->_NpjAddressToUrl
      ($principal->data["login"]."@".$principal->data["node_id"].
                  ($principal->data["node_id"]==$rh->node_name?"":"/".$rh->node_name).
                  ":friends/add/".$object->name, 
                 NPJ_ABSOLUTE), IGNORE_STATE)
//                               );
   );

?>