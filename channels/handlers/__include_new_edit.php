<?php

  // ���������� �� new.php & edit.php

  // =================================================================================
  //  ���� 2. �������� � �������� ��������� �����
  include( dirname(__FILE__)."/!form_channel.php" );

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

  
  // =================================================================================
  //  ���� 5. ���� ����� �� ������ ������������, � ������������ �������,
  //          �������� � ����������. �� ���� ������������.
  //          ��� ���� ������� ����� �������, ��������� �� �������
  //          � ���� ��, ��� ���������.
  if ($form->success)
  {
    $login = $form->hash["login"]->data;
    $channel = &$params["&channel"];
    if ($channel->data["login"]) $login = $channel->data["login"];

    // 5.1. ���� � ��� "add", �� ���������� ������� �������
    if ($params["mode"]=="add")
      $user_id = $channel->CreateAccount( $login );
    else
      $user_id = NULL;
    
    // 5.2. ��������� � �� ���������� ������ ������/��������
    $channel->SaveFromForm( &$form, $user_id );

    // 5.3. �������� �� �������.
    $rh->Redirect( $this->object->Href( $login."@".$channel->type."/".$rh->node_name,
                                        NPJ_ABSOLUTE, STATE_IGNORE), STATE_IGNORE);
  }



?>