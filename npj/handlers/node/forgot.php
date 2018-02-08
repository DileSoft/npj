<?php
 // если вы забыли пароль, вам сюда!


 // что нам нужно дать конфирм-форме, чтобы она сработала:
 // if ($_POST[ $this->prefix."confirm" ] == $this->handler)
 // $this->prefix  = ""
 // $this->handler = "password_reset"
 // итого, нам надо дать 


// ОБРАБОТКА ФОРМЫ ----------------------------------------------------------------
 if ($_POST["_flogin"])
 {
   $logins = explode("@", $_POST["_flogin"] );
   $node = $logins[1];
   if (($node != "") && ($node != $rh->node_name)) // какой-то чудак с другого узла
   {
     $nodeobj = &new NpjObject( &$rh, "show@".$node );
     $nodedata = $nodeobj->Load(2);
     $tpl->Assign("Login.Login", $logins[0] );
     $tpl->Assign("Login.MyNodeURL", $nodedata["url"]);
     $tpl->Assign("Login.MyNodeName", $nodedata["title"]);
     $tpl->Assign("Login.MyNodeID", $nodedata["node_id"]);
     $tpl->theme = $rh->theme;
       $tpl->Parse( "forgot.html:Alert", "Preparsed:CONTENT" );
     $tpl->theme = $rh->skin;
     return GRANTED;
   }
   else $_POST["_flogin"] = $logins[0];

      
   $account = &new NpjObject( &$rh, $_POST["_flogin"]."@". $rh->node_name );
   $account->Load(2);

//   $debug->Error_R( $account->data );
   if ($account->data["account_type"] != ACCOUNT_USER)
    return $this->Forbidden( "CommunityNotSupport" );

   $_POST[ "confirm" ] = "password_reset";
   $rh->object = &$account;
   $rh->url = $account->Href( $account->npj_object_address.":settings/password/reset",
                              NPJ_ABSOLUTE, STATE_IGNORE );
   return $account->Handler( "settings", array( "password", "reset" ), &$principal );
 }

// ЕСЛИ УЖЕ ЗАЛОГИНЕНЫ, ФОРМА НЕ НУЖНА, РЕДИРЕКТ ---------------------------------------
 if ($principal->IsGrantedTo( "noguests" ))
 {
   $account = &new NpjObject( &$rh, $principal->data["login"]."@".$principal->data["node_id"] );
   $account->Load(2);
   $rh->Redirect( $account->Href( $account->npj_object_address.":settings/password/reset", 
                                  NPJ_ABSOLUTE, STATE_IGNORE ) );
 }

// ОТОБРАЖЕНИЕ ФОРМЫ, ДЕЛАТЬ-ТО НЕЧЕГО
  $logged_domain = array(
        "Login"          => $params[0],
        "Form"           => $state->FormStart( MSS_POST,$rh->url, "id=loginForm name=loginForm" ),
        "/Form"          => $state->FormEnd(),
        "FocusToID"      => "loginForm._flogin",
                       );
  $tpl->LoadDomain( $logged_domain );

   $tpl->theme = $rh->theme;
     $tpl->Parse( "forgot.html:Form", "Preparsed:CONTENT" );
     $tpl->Assign( "Preparsed:TITLE", $tpl->message_set["ForgotPassword"] );
   $tpl->theme = $rh->skin;

?>