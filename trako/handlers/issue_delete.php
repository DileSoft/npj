<?php

  // "DELETE" handler
  $tpl->Assign("Preparsed:TITLE", "�������� �������"); // !!! to msgset
  $trako = &$this;
  $TE = &$this->GenerateTemplateEngine( $this->config["template_engine"] );

  $section = &new NpjObject( &$rh, $this->object->npj_account.":".$this->object->subspace );
  if ($section->Load(2) == NOT_EXIST)
  {
    $section = &new NpjObject( &$rh, $this->object->npj_account.":" );
    $section->Load(2);
  }
  $account = &new NpjObject( &$rh, $this->object->npj_account );
  $account->Load(2);

  $issue = &$this->LoadIssue( &$account, $this->params["issue_no"], 4 ); // 4 -- for edit
  if ($issue == NOT_EXIST) return $account->NotFound("Trako.IssueNotFound");
  $this->current_issue = $issue;

  // =================================================================================
  //  ���� 1. �������� ���� �������
  if (!$this->HasAccess( &$principal, &$account, $issue, "delete")) 
     return $account->Forbidden("Trako.DeniedByRank");

  // =================================================================================
  //  ���� 2. �������� ��������� �����
  //
  // >>>>>>>>>>>>>>>>> include <<<<<<<<<<<<<<<<<<
  include( dirname(__FILE__)."/!form_issue_delete.php" );    

  // =================================================================================
  //  ���� 3. ���� ��� ���� ����� � POST-�������, �� ������������� ��������� ��������
  //
  if (!isset($_POST["__form_present"])) 
  { 
     $form->ResetSession(); // �������� ���������� ���������
  }

  // =================================================================================
  //  ���� 4. ������ ������ �����. ��� ������������ �, ��������� ����.
  //
  $tpl->theme = $rh->theme;
  $result= $form->Handle();
  $tpl->theme = $rh->skin;
  if ($result !== false) 
  {
    $result = $TE->Parse("interface.html:2").$result;
    $tpl->Assign("Preparsed:CONTENT", "<a name=\"form\"></a>".$result);
  }

  $tpl->Assign("Preparsed:TIGHT", 1);
  $tpl->Assign("Preparsed:TIGHT_COMMENTS", 1);

  // =================================================================================
  //  ���� 5. ���� ����� �� ������ ������������, � ������������ �������,
  //          �������� � ����������. �� ���� ������������.
  //          ��� ���� ������� ����� �������, ��������� �� �������
  //          � ���� ��, ��� ���������.
  if ($form->success)
  {
    // ������� ���� � "������������"
    //  ������ ���������� ����� � ������������. ------------------------
    //  ��������, ����� ����� ������ �����������.
    //���������� ����������
    $formatting = $principal->data["_formatting"];

    //�������� ����������� �������� ����
    if ($formatting=="wacko")    $reason     = $form->hash["body_wikiedit"]->data;
    if ($formatting=="simplebr") $reason     = $form->hash["body_simpleedit"]->data;
    if ($formatting=="rawhtml")  $reason     = $form->hash["body_richedit"]->data;

    //�������������-������
    $reason = $this->object->Format($reason, $formatting, "after");

    // ����� ����� � ����? ---------------------------------------------
    $issue_no = $issue["issue_no"];

    // ����� ������� � ����� �������� ---------------------------------------------
    $event_data = array(
                          "record_id"  => $issue["RECORD"]["record_id"],
                          "issue_no"   => $issue_no,
                          "project_id" => $issue["RECORD"]["user_id"],
                          "event_name" => "issue_delete",
                          "issue"      => $issue,
                          "details"    => $reason,
                       );
    $this->Handler( "_logger", $event_data, &$principal );
  
    //  ������� ���-���� ---------------------------------------------
    $sql = "delete from ".$rh->db_prefix."trako_issues ".
           "where record_id = ".$db->Quote($issue["record_id"]);
    $db->Execute( $sql );

    //  ������� ������ � records ---------------------------------------------
    // � ����� ����, ��� �������.
    $record = &new NpjObject( &$rh, $issue["RECORD"]["supertag"] );
    $record->class= "record";
    $params = array();
    $record->Handler("_delete", $params, &$principal );

    //  �������� � ������ ------------------------------
    $rh->Redirect( $account->Href($account->npj_object_address.":trako", NPJ_ABSOLUTE, STATE_IGNORE), STATE_IGNORE);

  }//�������� if (form->success)

  
  return GRANTED;

?>