<?php

  // 1. ����� ����������� � comments/delete
  // 2. ������������ ����� ����������� � ConfirmForm

    $rh = &$this->rh;
    $db = &$this->rh->db;
    $debug = &$this->rh->debug;

    // ����� �������� ������_�� � �������_��.
    $data = $rh->object->Load( 2 );
    if ($data == false) { $this->success = false; return; }

    $record_id = $data["record_id"];
    $comment_id = $data["comment_id"];

    // ����� ����������� ���� "frozen".
    $frozen = $data["frozen"];
    if ($frozen == 1) $frozen = 0;
    else
    if ($frozen == 0) $frozen = 1;

    // ������ ��������� ��� � ���� �����.
    $sql = "update ".$rh->db_prefix."comments set frozen = ".$db->Quote($frozen).
           "where lft_id>=".$db->Quote($data["lft_id"]).
           " and  rgt_id<=".$db->Quote($data["rgt_id"]).
           " and record_id = ".$db->Quote($data["record_id"]);
    $db->Execute( $sql );

    // �������-����� ������ �� �����!

    $this->success = true;
?>