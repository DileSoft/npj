<?php

  // �������� �� �������
  //
  // * ������������� �� ������ �����������
  // * $params["by_script"] -> ����� ����� ������
  // * $params["comments"] -> �������� �� ��� ����������� � ���� ������������
  // * $params["post"] -> �������� �� ��� ����� ��������� � ���� ������������
  //   - ��� "���� �������������" ��������������� ����� ����� ����������.
  //     ��� �������� ������������� �� ����������.
  // * $params["keyword_id"] -> ����� ����������� �� �� �������� ������, � �� ����� ����������
  //   - ���� �����������, ���� �� �� ��������. ��� ���������.
  // * $params["user_id"] = ����-�� ���� ���������. ���� �����������, ���� ��������

  if (!$params["by_script"])
  {
    return DENIED;
  }

  if (!$params["user_id"]) $params["user_id"] = $principal->data["user_id"];
  if (!$params["keyword_id"]) 
  {
    $record = &new NpjObject( &$rh, $this->npj_account.":" );
    $rdata = &$record->Load(1);
    $params["keyword_id"] = $rdata["record_id"];
  }

  if ($params["user_id"] < 3) return DENIED; // !!! change to Forbidden

  $data = $this->Load(1);
  $class = $db->Quote("facet");
  $methods = array();
  if ($params["post"])     $methods[] = $db->Quote("post");
  if ($params["comments"]) $methods[] = $db->Quote("comments");
  if (sizeof($methods) == 0) return GRANTED;

  $account_id = $db->Quote($data["user_id"]);
  $keyword_id = $db->Quote($params["keyword_id"]);
  $user_id    = $db->Quote($params["user_id"]);

  $sql = ""; $f=0;
  foreach($methods as $method)
  { if ($f) $sql.=", "; else $f=1;
    $sql.= "($class, $keyword_id, $method, 0, $user_id)";
  }

  $db->Execute( "insert into ".$rh->db_prefix."subscription ".
                "(object_class, object_id,   object_method, method_option,  user_id) values ".
                $sql );

  return GRANTED;

?>