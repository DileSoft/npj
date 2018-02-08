<?php

  $tpl->Assign("Preparsed:TITLE", "Слежение за ходом работы над рапортом"); // !!! to msgset
  $trako = &$this;
  $account = &new NpjObject( &$rh, $this->object->npj_account );
  $account->Load(2);
  $TE = &$this->GenerateTemplateEngine( $this->config["template_engine"] );

  // =================================================================================
  //  ФАЗА 0. Загрузить из БД всё, что можно и препарсить
  $issue_no = $params["issue_no"];
  $issue = $this->LoadIssue( &$account, $issue_no );
  if ($issue == NOT_EXIST) return $account->NotFound("Trako.IssueNotFound");
  $this->current_issue = $issue;

  // ----------------------------------------------------------------
  // загрузить текущее состояние подписки
  $sql = "select object_id from ".$rh->db_prefix."subscription ".
         " where object_class=".$db->Quote("record").
         " and   object_method=".$db->Quote("comments").
         " and   method_option=".$db->Quote("0").
         " and   user_id=".$db->Quote($rh->principal->data["user_id"]).
         " and   object_id=".$db->Quote( $issue["record_id"] );
  $rs  = $db->Execute( $sql );
  $a   = $rs->GetArray();

  // ---------- ЕСЛИ ПОДПИСАН -- ОТПИСЫВАЕМ ---------------
  if (sizeof($a) != 0)
  {
    $sql = "delete from ".$rh->db_prefix."subscription ".
           " where object_class=".$db->Quote("record").
           " and   object_method=".$db->Quote("comments").
           " and   method_option=".$db->Quote("0").
           " and   user_id=".$db->Quote($rh->principal->data["user_id"]).
           " and   object_id=".$db->Quote( $issue["record_id"] );
    $db->Execute( $sql );
  }
  // ---------- ЕСЛИ НЕ ПОДПИСАН -- ПОДПИСЫВАЕМ ---------------
  if (sizeof($a) == 0)
  {
    $sql = "insert into ".$rh->db_prefix."subscription ".
           "(object_class, object_id, object_method, method_option, user_id) ".
           "VALUES (".$db->Quote("record").",".
                      $db->Quote($issue["record_id"]).",".
                      $db->Quote("comments").",".
                      $db->Quote("0").",".
                      $db->Quote($rh->principal->data["user_id"]).
                  ")"; 
    $db->Execute( $sql );
  }

  // redirect to the bug
  $rh->Redirect( $this->object->Href( $account->npj_object_address.":".
                                      $this->config["subspace"]."/".$issue_no,
                                      NPJ_ABSOLUTE, STATE_IGNORE), 
                 STATE_IGNORE );

?>