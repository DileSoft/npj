<?php

  // проверка на банлист
  if (!$rh->account->HasAccess( &$principal, "not_acl", "banlist" )) return $this->Forbidden("YouAreInBanlist");

  // уход в подфункцию 
  if ($object->params[0] == "ok")           return $object->Handler( "_add_ok", &$params, &$principal );

  $debug->Trace_R($params);
  $record = $params[0].":".$params[1];
  if (!$record) $record = $this->npj_context;

   // обработчик формы
   include( $dir."/!form_first.php" );
   if (!isset($_POST["__form_present"])) 
   { 
     $form->ResetSession();
   }

   $debug->Milestone( "Starting form handler" );

   $tpl->theme = $rh->theme;
   $result= $form->Handle();
   $tpl->theme = $rh->skin;

  if ($form->success)
  {
    //hack ???
    $_POST = array();
    return $this->Handler( "edit", array("rule",$form->hash["replica"]->data, $params[0], $params[1]), &$principal );
/*
    $rh->Redirect( 
      $rh->Href(
        $this->_NpjAddressToUrl( 
          $this->npj_object_address."/edit/rule/".$form->hash["replica"]->data."/".$params[0]."/".$params[1], 
          NPJ_RELATIVE
        ), 1
      ) 
    );
*/
//    $rh->Redirect( $rh->Href($object->_NpjAddressToUrl( $this->npj_address."/ok/".$new_comment->name, 1 ),1) );
  } 
   if ($result !== false) $tpl->Assign("Preparsed:CONTENT", $result);
   $tpl->Assign("Preparsed:TITLE", $tpl->message_set["Form._Name"]);

//    $debug->Error(1);
?>
