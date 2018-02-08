<?php

  $TE = &$this->GenerateTemplateEngine( $this->config["template_engine"] );

  // "LOG" handler
  // $params["issue_no"] = 1*issue_no
  $issue_no = $params["issue_no"];

  $account = &new NpjObject( &$rh, $this->object->npj_account );
  $account->Load(2);

  $result = $this->Handler( "issue_view", $params, &$principal );
  if ($result == DENIED) return $result;
  $issue = &$this->current_issue;

  // =================================================================================
  // Парсинг

  $tpl->Assign("Html:TITLE",        $tpl->message_set["Trako.actions"]["log"]." ".
                                    $tpl->GetValue( "Html:TITLE" ));
  // Сам лог
  {
    $sql = "select subject, body_post, created_datetime, ".
                  "user_login, user_node_id, user_name ".
           " from ".$rh->db_prefix."comments ".
           " where parent_id>0 and active=0 and frozen=".$db->Quote(TRAKO_LOGGER_COMMENT).
           " and record_id = ".$db->Quote($issue["record_id"]).
           " order by created_datetime asc";
    $rs  = $db->Execute( $sql );
    $a   = $rs->GetArray();
    foreach( $a as $k=>$v )
    {
      $dt = strtotime( $v["created_datetime"] );
      $a[$k]["even"] = $k%2;
      $a[$k]["date"] = date( "d.m.Y", $dt );
      $a[$k]["time"] = date( "H:i", $dt );
      $a[$k]["Link:npj"] = $this->object->Link( $v["user_login"]."@".$v["user_node_id"] );
    }

    $list = &new ListObject(&$rh, $a);
    $list->tpl = &$TE;
    $list->Parse( "log.html:List", "log_data" );


    $tpl->Assign( "Preparsed:COMMENTS", $TE->Parse("log.html:Body") );
  }

  
  return GRANTED;

?>