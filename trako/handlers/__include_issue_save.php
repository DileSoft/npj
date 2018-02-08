<?php
    // ���������� �� "issue_add.php" & "issue_edit.php"

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
      else
      if (is_array($form->hash[$k]->config["fields"]))
       foreach($form->hash[$k]->config["fields"] as $kk=>$vv)
       {
         $form->hash[$k]->config["fields"][$kk]->_StoreToDb(); 
         $issue_data[ $vv->config["field"] ] = $form->hash[$k]->config["fields"][$kk]->db_data;
       }
    }
    //  ������ ���������� ����� � ������������. ------------------------
    //  ��������, ����� ����� ������ �����������.
    //���������� ����������
    if (!$issue_data["formatting"]) $issue_data["formatting"] = $principal->data["_formatting"];

    //�������� ����������� �������� ����
    if ($issue_data["formatting"]=="wacko")    $issue_data["body"] = $issue_data["body_wikiedit"];
    if ($issue_data["formatting"]=="simplebr") $issue_data["body"] = $issue_data["body_simpleedit"];
    if ($issue_data["formatting"]=="rawhtml")  $issue_data["body"] = $issue_data["body_richedit"];

    //�������������-������
    $issue_data["body"] = $this->object->Format($issue_data["body"], $issue_data["formatting"], "after");


?>