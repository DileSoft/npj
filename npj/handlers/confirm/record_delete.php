<?php

  // 1. ����� ����������� � record/delete
  // 2. ������������ ����� ����������� � ConfirmForm

  $rh = &$this->rh;
  $db = &$this->rh->db;
  $debug = &$this->rh->debug;
  $principal = &$this->rh->principal;


  // ����� �������� ������_�� � �������_��.
  $data = $rh->object->Load( 2 );
  if (($data == false) || ($data == "empty")) { $this->success = false; return; }

  $params = array();
  $rh->object->Handler( "_delete", $params, &$principal );

  $this->success = true;

?>