<?php

  // 1. права проверяются в comments/delete
  // 2. соответствие формы проверяется в ConfirmForm

    $rh = &$this->rh;
    $db = &$this->rh->db;
    $debug = &$this->rh->debug;

    // нужно получить рекорд_ид и коммент_ид.
    $data = $rh->object->Load( 2 );
    if ($data == false) { $this->success = false; return; }

    $record_id = $data["record_id"];
    $comment_id = $data["comment_id"];

    // нужно перевернуть флаг "frozen".
    $frozen = $data["frozen"];
    if ($frozen == 1) $frozen = 0;
    else
    if ($frozen == 0) $frozen = 1;

    // теперь выставить его у всех детей.
    $sql = "update ".$rh->db_prefix."comments set frozen = ".$db->Quote($frozen).
           "where lft_id>=".$db->Quote($data["lft_id"]).
           " and  rgt_id<=".$db->Quote($data["rgt_id"]).
           " and record_id = ".$db->Quote($data["record_id"]);
    $db->Execute( $sql );

    // коммент-каунт менять не нужно!

    $this->success = true;
?>