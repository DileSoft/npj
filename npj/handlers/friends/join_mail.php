<?php

  // �������� ����������� � ����� ����� � ������� �� ������
  // ??? ���� ��� �� � �������.
  // ���������: 
  //   * user_id -- ���, ��� ���������

  // 1. �������� �� ��� �����
  $account = &new NpjObject( &$rh, $this->npj_account );
  $data = $account->Load(2);

  // 2. �������� ������ �������
  //    * �������� ���������� � ����������� email ���������� ???
  $user_ids = array( $data["owner_id"], $data["user_id"] );
  //    * ���������� ����������
  $sql = "select ug.user_id from ".$rh->db_prefix."groups as g, ".$rh->db_prefix."user_groups as ug where ug.group_id = g.group_id and ".
         $db->Quote( $data["user_id"] )." = g.user_id and g.group_rank >= ".GROUPS_MODERATORS;
  $rs = $db->Execute( $sql ); $a = $rs->GetArray();
  foreach($a as $item ) $user_ids[] = $item["user_id"];

  // 3. ??? ���������� � ������� // ��������
  return $this->Handler("join_mail_send", 
                        array("to"=>$user_ids, "user_id"=>$params["user_id"]),
                        &$principal);

  return GRANTED;  

?>