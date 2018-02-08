<?php

  // для "нашего" и "чужих" узлов управление разное! совсем!
  if ($this->npj_node == $rh->node_name) 
  {
    // это -- НАШ узел------------------------------------------------------

    if (!$principal->IsGrantedTo("acl_text", NULL, NULL, $rh->node_admins))
      return $this->Forbidden("NotAnAdmin"); 
    
    // уход в подфункцию
    if (($object->params[0] == "users") || ($object->params[0] == "communities"))
      return $object->Handler( "_manage_users", $params, &$principal );

    if (($object->params[0] == "nns"))
      return $object->Handler( "_manage_nns", $params, &$principal );


    $tpl->theme = $rh->theme;
    $tpl->Parse( "manage.html:Node", "Preparsed:CONTENT" );
    $tpl->Assign( "Preparsed:TITLE", "Администрирование узла" ); // !!! to messageset
    $tpl->theme = $rh->skin;


    return GRANTED;

  }
  else 
  {
    // это -- ЧУЖОЙ узел------------------------------------------------------
    return $this->Forbidden("NotImplemented"); // !!! для чужих пока не сделал
  }
  

  return $this->Forbidden("NotImplemented");


?>
