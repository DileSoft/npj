<?php

  // "EDIT" handler
  $tpl->Assign("Preparsed:TITLE", "������ �������"); // !!! to msgset
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
  if (!$this->HasAccess( &$principal, &$account, $issue, "edit")) 
     return $account->Forbidden("Trako.DeniedByRank");

  // =================================================================================
  //  ���� 2. �������� ��������� �����
  //
  // >>>>>>>>>>>>>>>>> include <<<<<<<<<<<<<<<<<<
  include( dirname(__FILE__)."/!form_issue.php" );    

  // =================================================================================
  //  ���� 3. ���� ��� ���� ����� � POST-�������, �� ������������� ��������� ��������
  //
  if (!isset($_POST["__form_present"])) 
  { 
     $form->ResetSession(); // �������� ���������� ���������
     $form->DoSelect( $issue["record_id"] ); // ��������� �� ��, ���� ������ ��� ����
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
    // ��������� ������ �� ���� �����  -------------------------------
    foreach( $form->hash as $k=>$v )
    {                                                                                     
      // ������ db_data �� ������ data (����� ��� ������� �����, ���� ����� ����� ����� � ���) 
      $form->hash[$k]->_StoreToDb(); 
      $issue_data[$k] = $form->hash[$k]->db_data;
      if (isset($form->hash[$k]->db_groups_data))
        $issue_data[ $form->hash[$k]->config["groups_field"] ] = $form->hash[$k]->db_groups_data;
      if (is_array($form->hash[$k]->db_data))
      {
        //$debug->Trace_R( $form->hash[$k]->db_data );
        foreach ($form->hash[$k]->db_data as $field=>$value)
          $issue_data[ $form->hash[$k]->config["fields"][$field] ] = $value;
      }
    }
    // ��������� ������ �� ���� �����  -------------------------------
    $issue_data = array();
    include( dirname(__FILE__)."/__include_issue_save.php" );

    //  ������ ���������� ����� � ������������. ------------------------
    //  ��������, ����� ����� ������ �����������.
    //���������� ����������
    if ($issue["RECORD"]["formatting"]) $issue_data["formatting"] = $issue["RECORD"]["formatting"];
    else                                $issue_data["formatting"] = $principal->data["_formatting"];


    //�������� ����������� �������� ����
    if ($issue_data["formatting"]=="wacko")    $issue_data["body"] = $issue_data["body_wikiedit"];
    if ($issue_data["formatting"]=="simplebr") $issue_data["body"] = $issue_data["body_simpleedit"];
    if ($issue_data["formatting"]=="rawhtml")  $issue_data["body"] = $issue_data["body_richedit"];

    //�������������-������
    $issue_data["body"] = $this->object->Format($issue_data["body"], $issue_data["formatting"], "after");

    // ����� ����� � ����? ---------------------------------------------
    $issue_no = $issue["issue_no"];
    $issue_data["issue_no"] = $issue_no;
    //$debug->Error_R( $issue_data );

    //  ������ �������� ��� ������ � ��������� � ---------------------------------------------
    $tag   =  $this->config["subspace"]."/".$issue_no;
    $issue_record =& new NpjObject(&$rh, $account->npj_object_address.":".$tag );
    $issue_record->class = "record";
    $issue_record->Load( 4 ); // load for edit;
    foreach( $issue_data as $k=>$v )
      $issue_record->data[$k] = $v;
    $issue_record->data["user_datetime"] = date("Y-m-d H:i:s");
    $issue_record->data["&issue"]    = &$issue; // prev. state
    $issue_record->owner = &$account;

    $issue_helper = &new HelperTrakoIssue( &$rh, &$issue_record );
    $issue_record->helper = &$issue_helper;
    $issue_record->Save();

    // �� ��!
    $event_data = array(
                          "record_id"  => $issue_record->data["record_id"],
                          "issue_no"   => $issue_no,
                          "project_id" => $issue_record->data["user_id"],
                          "event_name" => "issue_edit",
                          "issue"      => $issue,
                          "new_issue"  => $issue_data,
                       );
    $this->Handler( "_logger", $event_data, &$principal );
  
    //  �������� �� ���������� ������ ------------------------------
    $rh->Redirect( $this->object->Href($issue_record->data["supertag"], NPJ_ABSOLUTE, STATE_IGNORE), STATE_IGNORE);

  }//�������� if (form->success)

  
  return GRANTED;

?>