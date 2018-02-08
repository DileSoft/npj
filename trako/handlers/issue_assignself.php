<?php

  // "ASSIGN SELF" handler
  $tpl->Assign("Preparsed:TITLE", "���������� ������� ����"); // !!! to msgset
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
  if (!$this->HasAccess( &$principal, &$account, $issue, "assign_self")) 
     return $account->Forbidden("Trako.DeniedByRank");

  // =================================================================================
  //  ���� 2. �������� ��������� �����
  //
  // >>>>>>>>>>>>>>>>> include <<<<<<<<<<<<<<<<<<
  include( dirname(__FILE__)."/!form_issue_assign_self.php" );    

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
    // �������� �� ����� ������������. �� ���, �� ����������!
    $developer_id = $principal->data["user_id"];

    // ���� �� �������� ������. ��� ��� ��������� ������ ������
    $target_status = $issue["state_status"];
    if (isset( $this->config["states"][ $issue["state"] ]["assigned_status"]))
      $target_status = $this->config["states"][ $issue["state"] ]["assigned_status"];

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

    //  ��������� ��������� � �� ---------------------------------------------
    $this->rh->UseClass("HelperAbstract");
    $this->rh->UseClass("HelperRecord");
    $this->rh->UseClass("HelperTrakoIssue", $this->classes_dir);
    //  ������ �������� ��� ������ � ��������� � ---------------------------------------------
    $tag   =  $this->config["subspace"]."/".$issue_no;
    $issue_record =& new NpjObject(&$rh, $account->npj_object_address.":".$tag );
    $issue_record->class = "record";
    $issue_record->Load( 4 ); // load for edit;
    unset($issue_record->data["keywords"]);
    $issue_record->data["issue_no"]             = $issue_no;

    // new developer
    // new state
    $issue_record->data["issue_developer_id"]   = $developer_id;
    $issue_record->data["issue_state_status"]   = $target_status;
    $issue_record->data["issue_assign_self"]    = true;

    $issue_record->data["user_datetime"] = date("Y-m-d H:i:s");
    $issue_record->data["&issue"]    = &$issue; // prev. state
    $issue_record->owner = &$account;
    $issue_helper = &new HelperTrakoIssue( &$rh, &$issue_record );
    $issue_record->helper = &$issue_helper;
    $issue_record->Save();

    // ����� ������� � ����� �������� ---------------------------------------------
    $event_data = array(
                          "record_id"  => $issue["RECORD"]["record_id"],
                          "issue_no"   => $issue_no,
                          "project_id" => $issue["RECORD"]["user_id"],
                          "event_name" => "issue_assign_self",
                          "issue"      => $issue,
                          "details"    => $reason,
                       );
    $this->Handler( "_logger", $event_data, &$principal );
  
    //  �������� � ��� �� ��� ------------------------------
    $rh->Redirect( $this->object->Href($issue["RECORD"]["supertag"], NPJ_ABSOLUTE, STATE_IGNORE), STATE_IGNORE);

  }//�������� if (form->success)

  
  return GRANTED;

?>