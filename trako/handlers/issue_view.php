<?php

  $TE = &$this->GenerateTemplateEngine( $this->config["template_engine"] );

  // "VIEW" handler
  // $params["issue_no"] = 1*issue_no
  $issue_no = $params["issue_no"];

  $account = &new NpjObject( &$rh, $this->object->npj_account );
  $account->Load(2);

  // =================================================================================
  //  ФАЗА 0. Загрузить из БД всё, что можно и препарсить
  $issue = $this->LoadIssue( &$account, $issue_no );
  if ($issue == NOT_EXIST) return $account->NotFound("Trako.IssueNotFound");
  $this->current_issue = $issue;

  // =================================================================================
  //  ФАЗА 1. Проверка прав доступа
  if (!$this->HasAccess( &$principal, &$account, $issue, "view")) 
     return $account->Forbidden("Trako.DeniedByRank");

  // =================================================================================
  // Парсинг
  $TE->LoadDomain( $issue );


  // Doubleclick
  $edit = $account->Href($this->config["subspace"]."/".$issue_no."/edit")."#form";
  $themeurl = $tpl->GetValue("theme");
  $dclick = '<script type="text/javascript" language="javascript">var edit = "'.$edit.'";</script>'.
            '<script type="text/javascript" language="javascript" src="'.$themeurl.'/js/dclick.js"></script>';
  if ($this->HasAccess( &$principal, &$account, $issue, "edit"))
    if ($principal->data["options"]["double_click"]) $tpl->Assign( "DCLICK", $dclick);


  $tpl->Assign("Html:TITLE",        "#".$issue["issue_no"].": ".$issue["RECORD"]["subject_post"] );
  $tpl->Assign("Preparsed:TITLE",   $TE->Parse( "issue.html:TITLE" ) );
  $tpl->Assign("Preparsed:CONTENT", $TE->Parse( "issue.html:BODY" ) );
  $tpl->Assign("Preparsed:TIGHT", 1);
  $tpl->Assign("Preparsed:TIGHT_COMMENTS", 1);

  // Комментарии
  $data = $issue["RECORD"];
  {
    if ($data["number_comments"] == 0)
     $tpl->Parse( "comments.html:HiddenNone", "Preparsed:COMMENTS" );
    else
    {
     $tpl->Assign( "CommentCount", $data["number_comments"] );
     $tpl->Parse( "comments.html:Hidden", "Preparsed:COMMENTS" );
    }
  }

  
  return GRANTED;

?>