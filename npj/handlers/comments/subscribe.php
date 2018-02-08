<?php

  // подписка на комментарий
  //
  // * подписываемся на данный комментарий
  // * $params["by_script"] -> вызов через скрипт
  // * $params["option"] = "tree" -> подписка на всё дерево комментов, иначе только на поддерево
  // * $params["user_id"] = юзер-ид кого подписать. если отсутствует, берём текущего

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