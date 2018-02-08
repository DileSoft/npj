<?php

  $rh = &$this->rh;
  $db = &$this->rh->db;
  $debug = &$this->rh->debug;

  $sql = "update ".$rh->db_prefix."users set _pic_id=".(1*$rh->object->params[2]).
         " where user_id=".$rh->account->data["user_id"];
  $db->Execute($sql);

  $this->success = true;

  // нужно ли все комменты и все записи мен€ть картинку?
?>