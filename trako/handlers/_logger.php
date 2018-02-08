<?php

  /*
     ЧТО МОЖНО УЛУЧШИТЬ:
     * compilation of body -> add/edit
          + написать кому назначен
          + написать приоритет
          + написать важность
          + написать категории
     * refactor SQL to class ModuleTrako
  */

  // Это внутренний метод, он дописывает в специальный лог (на базе комментов)
  // записи о том, что происходило с багами.

  // синтаксис $params:
  //
  //    record_id
  //    issue_no
  //    project_id
  //    event_name
  //    details
  //    _principal_id -- если не стоит, то этот метод сам его придумывает 
  //    _datetime     -- если не стоит, то этот метод сам его придумывает
  //
  //    (?) issue        -- запись Issue (старая)
  //    (?) new_issue    -- запись Issue (новая)
  //    (?) state_params -- пара State/Status, куда перешло Issue
  // details должно содержать причину того действия, что произошло.

  $trako = &$this;
  $account = &new NpjObject( &$rh, $this->object->npj_account );
  $account->Load(2);
  $TE = &$this->GenerateTemplateEngine( $this->config["template_engine"] );

  $rh->tpl->MergeMessageset( $rh->message_set."_Trako_logger", $this->messagesets_dir );
  $ms = &$this->rh->tpl->message_set;
  
  // =================================================================================
  //  ФАЗА 0. Загрузить из БД всё, что можно и препарсить
  $issue_no = $params["issue_no"];
  $issue = $this->LoadIssue( &$account, $issue_no );
  if ($issue == NOT_EXIST) return;

  $record = &new NpjObject( &$rh, $issue["RECORD"]["supertag"] );
  $record->Load(3);

  $comment = &new NpjObject(&$rh, $issue["RECORD"]["supertag"]."/comments");

  $logger_root = $this->LoadLoggerRootId( $record->data["record_id"] );

  // ===========================
  // Подготавливаем тело
  $body_post = "";

  if ($params["details"] == "")
    if ($params["issue"])
      $params["details"] = $params["issue"]["_logger_reason"];

  $TE->Assign("reason", $params["details"]);
  if ($params["issue"])
  {
    $TE->Assign("from_state",  $this->config["states"][ $params["issue"]["state"] ]["name"] );
    $TE->Assign("from_status", $this->config["statuses"][ $params["issue"]["state_status"] ] );
    foreach( $params["issue"] as $k=>$v )
     if ($k{0} != "&") $TE->Assign("old:".$k, $v);
  }
  if ($params["new_issue"])
  {
    foreach( $params["new_issue"] as $k=>$v )
     if ($k{0} != "&") $TE->Assign("new:".$k, $v);
  }
  if ($params["state_params"])
  {
    $TE->Assign("to_state",  $this->config["states"]  [ $params["state_params"]["state"] ]["name"] );
    $TE->Assign("to_status", $this->config["statuses"][ $params["state_params"]["status"] ] );
  }
  switch( $params["event_name"] )
  { 
     /* not implemented yet
     case "issue_add":    $body_post = $TE->Parse("logger.html:Add");
                          break;
     case "issue_edit":   $body_post = $TE->Parse("logger.html:Edit");
                          break;
     */
     case "issue_state":  $body_post = $TE->Parse("logger.html:State");
                          break;
     case "issue_status": $body_post = $TE->Parse("logger.html:Status");
                          break;
     default:             $body_post = $TE->Parse("logger.html:Default");
  }
  // ===========================
  // Подготавливаем заголовок
  $subject = $ms["Trako.logger_subjects"][$params["event_name"]];
  switch( $params["event_name"] )
  { 
     case "issue_state":  $subject .= strtoupper($TE->GetValue("to_state"));
                          break;
     case "issue_status": $subject .= strtoupper($TE->GetValue("to_status"));
                          break;
  }
  $subject = str_replace("&NBSP;", " ", $subject);
  $subject = $tpl->Format( $subject, "html2text" );

  // ===========================

  if (!$params["_principal_id"]) $params["_principal_id"] = $principal->data["user_id"];
  $principal->MaskById($params["_principal_id"]);

    $comment->data["active"] = 0;
    $comment->data["frozen"] = TRAKO_LOGGER_COMMENT;
    $comment->data["subject"]   = $subject;
    $comment->data["body_post"] = $body_post; // !!!!!!! temp solution
  
    $comment->data["user_id"]      = $principal->data["user_id"];
    $comment->data["user_login"]   = $principal->data["login"];
    $comment->data["user_name"]    = $principal->data["user_name"];
    $comment->data["user_node_id"] = $principal->data["node_id"];
  
    if ($params["_datetime"])
      $comment->data["created_datetime"] = date("Y-m-d H:i:s", strtotime($params["_datetime"]));
    else
      $comment->data["created_datetime"] = date("Y-m-d H:i:s");
    $comment->data["record_id"] = $record->data["record_id"];
    $comment->data["parent_id"] = $logger_root; 
    $comment->data["lft_id"] = 0;
    $comment->data["rgt_id"] = 0;

    $comment->Save();
  $principal->UnMask();

?>