<?php

  // ��� "������" � "�����" ����� ���������� ������! ������!
  if ($this->npj_node == $rh->node_name) 
  {
    // ��� -- ��� ����------------------------------------------------------

    if (!$principal->IsGrantedTo("acl_text", NULL, NULL, $rh->node_admins))
      return $this->Forbidden("NotAnAdmin"); 
    
    // ���� � ����������
    if (($object->params[0] == "users") || ($object->params[0] == "communities"))
      return $object->Handler( "_manage_users", $params, &$principal );

    if (($object->params[0] == "nns"))
      return $object->Handler( "_manage_nns", $params, &$principal );


    $tpl->theme = $rh->theme;
    $tpl->Parse( "manage.html:Node", "Preparsed:CONTENT" );
    $tpl->Assign( "Preparsed:TITLE", "����������������� ����" ); // !!! to messageset
    $tpl->theme = $rh->skin;


    return GRANTED;

  }
  else 
  {
    // ��� -- ����� ����------------------------------------------------------
    return $this->Forbidden("NotImplemented"); // !!! ��� ����� ���� �� ������
  }
  

  return $this->Forbidden("NotImplemented");


?>
