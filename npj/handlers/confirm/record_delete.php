<?php

  // 1. права проверяются в record/delete
  // 2. соответствие формы проверяется в ConfirmForm

  $rh = &$this->rh;
  $db = &$this->rh->db;
  $debug = &$this->rh->debug;
  $principal = &$this->rh->principal;


  // нужно получить рекорд_ид и коммент_ид.
  $data = $rh->object->Load( 2 );
  if (($data == false) || ($data == "empty")) { $this->success = false; return; }

  $params = array();
  $rh->object->Handler( "_delete", $params, &$principal );

  $this->success = true;

?>