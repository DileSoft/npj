<?php

  // "ADD" handler

  $section = &new NpjObject( &$rh, $this->object->npj_account.":".$this->object->subspace );
  if ($section->Load(2) == NOT_EXIST)
  {
    $section = &new NpjObject( &$rh, $this->object->npj_account.":" );
    $section->Load(2);
  }
  $account = &new NpjObject( &$rh, $this->object->npj_account );
  $account->Load(2);

  $issue = array(); // ��� ��� �����.
  
  // =================================================================================
  //  ���� 1. �������� ���� �������
  if (!$section->HasAccess( $principal, "acl", "actions" ))
    return $section->Forbidden( "Trako.DeniedByActionsAcl" );

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
  }

  // =================================================================================
  //  ���� 4. ������ ������ �����. ��� ������������ �, ��������� ����.
  //
  $tpl->theme = $rh->theme;
  $result= $form->Handle();
  $tpl->theme = $rh->skin;
  if ($result !== false) $tpl->Assign("Preparsed:CONTENT", "<a name=\"form\"></a>".$result);

  $tpl->Assign("Preparsed:TIGHT", 1);
  $tpl->Assign("Preparsed:TITLE", "���������� �������"); // !!! to msgset

  // =================================================================================
  //  ���� 5. ���� ����� �� ������ ������������, � ������������ �������,
  //          �������� � ����������. �� ���� ������������.
  //          ��� ���� ������� ����� �������, ��������� �� �������
  //          � ���� ��, ��� ���������.
  if ($form->success)
  {
    // ��������� ������ �� ���� �����  -------------------------------
    $issue_data = array();
    include( dirname(__FILE__)."/__include_issue_save.php" );

    // �������� ����� ��� ���� ������.
    $sql = "select max(issue_no) as last_issue from ".$rh->db_prefix."trako_issues ".
           " where project_id = ".$db->Quote($account->data["user_id"]);
    $rs  = $db->Execute( $sql );
    $a   = $rs->GetArray();
    if (sizeof($a) == 0) $issue_no = 1;
    else                 $issue_no = 1* $a[0]["last_issue"] + 1;

    //  ������ �������� ��� ������ � ��������� � ---------------------------------------------
    $tag   =  $this->config["subspace"]."/".$issue_no;
    $issue_record =& new NpjObject(&$rh, $account->npj_object_address.":".$tag );
    $issue_record->class = "record";
    $issue_record->data = $issue_data;
    $issue_record->data["issue_no"]      = $issue_no;
    $issue_record->data["tag__leave_as_is"] = true;
    $issue_record->data["tag"]           = $tag;
    $issue_record->data["user_id"]       = $section->data["user_id"];
    $issue_record->data["type"]          = RECORD_POST;
    $issue_record->data["disallow_syndicate"]     = 1;
    $issue_record->data["user_datetime"] = date("Y-m-d H:i:s");
    $issue_record->data["group1"]        = ACCESS_GROUP_TRAKO;
    $issue_record->data["group2"]        = ACCESS_GROUP_TRAKO;
    $issue_record->data["group3"]        = ACCESS_GROUP_TRAKO;
    $issue_record->data["group4"]        = ACCESS_GROUP_TRAKO;

    $issue_record->owner = &$account;
    $issue_helper = &new HelperTrakoIssue( &$rh, &$issue_record );
    $issue_record->helper = &$issue_helper;
    $issue_record->Save();

    // �� ��!
    $event_data = array(
                          "record_id"  => $issue_record->data["record_id"],
                          "issue_no"   => $issue_no,
                          "project_id" => $issue_record->data["user_id"],
                          "event_name" => "issue_add",
                          "new_issue"  => $issue_data,
                       );
    $this->Handler( "_logger", $event_data, &$principal );
  
    //  �������� �� ���������� ������ ------------------------------
    $rh->Redirect( $this->object->Href($issue_record->data["supertag"], NPJ_ABSOLUTE, STATE_IGNORE), STATE_IGNORE);

  }//�������� if (form->success)

  
  return GRANTED;

?>