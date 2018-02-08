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

    /*
    // ���� �� � ���� �����?
    $sql = "select count(*) as result from ".$rh->db_prefix."comments where record_id=".
           $db->Quote( $record_id )." and parent_id=".
           $db->Quote( $comment_id );
    $rs = $db->Execute( $sql );
    if ($rs->fields["result"] == 0)
      $sql = "delete from ".$rh->db_prefix."comments where comment_id=".$db->Quote( $comment_id );
    else
    */
    // �� ������� �� ������� �����������. ����� �� ����. !!!
      $sql = "update ".$rh->db_prefix."comments set active=0 where comment_id=".$db->Quote( $comment_id );

    $db->Execute( $sql );

    // ����� �������� �������-�����
    $sql = "select count(*) as result from ".$rh->db_prefix."comments where active=1 and record_id=".
           $db->Quote( $record_id );
    $rs = $db->Execute( $sql );
    $sql = "update ".$rh->db_prefix."records set number_comments=".
           $db->Quote( $rs->fields["result"] )." where record_id=".
           $db->Quote( $record_id );
    $db->Execute( $sql );

    $this->success = true;
?>