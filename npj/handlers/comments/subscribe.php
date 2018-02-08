<?php

  // �������� �� �����������
  //
  // * ������������� �� ������ �����������
  // * $params["by_script"] -> ����� ����� ������
  // * $params["option"] = "tree" -> �������� �� �� ������ ���������, ����� ������ �� ���������
  // * $params["user_id"] = ����-�� ���� ���������. ���� �����������, ���� ��������

  if (!$params["by_script"])
  {
    return DENIED;
  }

  if (!$params["user_id"]) $params["user_id"] = $principal->data["user_id"];

  if ($params["user_id"] < 3) return DENIED; // !!! change to Forbidden

  $data = $this->Load(1);
  $class = $db->Quote("comments");
  $method=$db->Quote("");

  $comment_id = $data["comment_id"];
  if ($params["option"] == "tree") $comment_id = 0;
  $comment_id = $db->Quote($comment_id);
  $option = $db->Quote($data["record_id"]);
  $user_id = $db->Quote($params["user_id"]);

  $db->Execute( "insert into ".$rh->db_prefix."subscription ".
                "(object_class, object_id,   object_method, method_option,  user_id) values ".
                "($class,       $comment_id, $method,       $option,        $user_id)" );

  return GRANTED;

?>