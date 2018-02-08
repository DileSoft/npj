<?php

  // этот хандлер перенаправляет запрос в журнал текущего принципала

  $npj_address = $principal->data["login"]."@".$principal->data["node_id"].":".
                 implode("/", $params);

  $rh->Redirect( $this->Href( $npj_address, NPJ_ABSOLUTE, STATE_USE ), STATE_IGNORE );

?>