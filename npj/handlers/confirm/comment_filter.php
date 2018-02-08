<?php

  // 1. ����� ����������� � comments/filter
  // 2. ������������ ����� ����������� � ConfirmForm

    $rh = &$this->rh;
    $db = &$this->rh->db;
    $debug = &$this->rh->debug;

    // ����� �������� ������_�� � �������_��.
    $data = $rh->object->Load( 2 );
    if ($data == false) { $this->success = false; return; }

    $record_id = $data["record_id"];
    $comment_id = $data["comment_id"];

    // ������ � �������
    $cf = $rh->community_filter && ($rh->object->npj_filter != "");
    if (!$cf) return; 

    $filter_object = &new NpjObject( &$rh, $rh->object->npj_filter."@".$rh->node_name );
    $filter_data   = $filter_object->Load(2);
    if (!is_array($filter_data)) return; 

    // ����� ������, ���������� �� ����
    $sql = "select * from ".$rh->db_prefix."comments_filtered where ".
           " comment_id=".$db->Quote( $comment_id ).
           " and filter_user_id = ".$db->Quote( $filter_data["user_id"] );
    $rs  = $db->Execute($sql);
    $a   = $rs->GetArray();
    if (sizeof($a)) $reset = 1;
    else            $reset = 0;

    // ����� �������� ��� �������� ���� �� ������
    $sql = "select comment_id from ".$rh->db_prefix."comments ".
           "where lft_id  >=".$db->Quote($data["lft_id"]).
           " and  rgt_id  <=".$db->Quote($data["rgt_id"]).
           " and record_id = ".$db->Quote($data["record_id"]);
    $rs  = $db->Execute($sql);
    $a   = $rs->GetArray();
    $comments = array();
    foreach($a as $k=>$v)
      $comments[] = $db->Quote($v["comment_id"]);

    // ������ ������� ������ �� ��������� ��� ����������� ��
    if (sizeof($comments))
    {
      if ($reset)
        $sql = "delete from ".$rh->db_prefix."comments_filtered where ".
               " comment_id in (".implode(",", $comments).")";
      else
      {
        $pid = $db->Quote( $rh->principal->data["user_id"] );
        $dt  = $db->Quote( date("Y:m:d H:i:s") );
        foreach($comments as $k=>$v)
          $comments[$k] = "(".$v.",".$filter_data["user_id"].",".$pid.",".$dt.")"; 
        $sql = "insert into ".$rh->db_prefix."comments_filtered ".
                "(comment_id, filter_user_id, moderator_id, created_datetime)".
                " values ". implode(",", $comments);
      }
      $db->Execute( $sql );
    }

    // �������-����� ������ �� �����!

    $this->success = true;
?>