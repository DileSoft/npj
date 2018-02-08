<?php

 if (!$_REQUEST["freturn2"])
 {
   unset($_REQUEST["freturn"]);
   unset($_GET    ["freturn"]);
   unset($_POST   ["freturn"]);
 }

 $principal->logout_referer = false;
 if ($principal->Logout( 0 )) 
 {
   // закомментировать удаление сеансового флага
   // if ($_COOKIE[$rh->cookie_prefix."donotcsa"]) setcookie ($rh->cookie_prefix."donotcsa", "", time()-60*60*24*30*12, "/", $rh->cookie_domain);
   // поставить сеансовый флаг, шобы больше не запускался CSA-механизм
   setcookie ($rh->cookie_prefix."donotcsa", "yes", 0, "/", $rh->cookie_domain);

   $rh->Redirect( $_SERVER["HTTP_REFERER"], 0 ); // уходим обратно, уходим

   $tpl->Skin( $rh->theme );
   $tpl->Parse( "logout_ok.html", "Preparsed:CONTENT" );
   $tpl->UnSkin();
   return GRANTED;
 }

 // отрезка @CurrentNode, или алёрт, если там не куррент ноде
 if (strpos($_POST["_flogin"], "@") !== false)
 { $logins = explode("@", $_POST["_flogin"] );
   $node = $logins[1];
   if ($node != $rh->node_name) // какой-то чудак с другого узла
   {
     $nodeobj = &new NpjObject( &$rh, "show@".$node );
     $nodedata = $nodeobj->Load(2);
     $tpl->Assign("Login.Login", $logins[0] );
     $tpl->Assign("Login.MyNodeURL", $nodedata["url"]);
     $tpl->Assign("Login.MyNodeName", $nodedata["title"]);
     $tpl->Assign("Login.MyNodeID", $nodedata["node_id"]);
     $tpl->theme = $rh->theme;
       $tpl->Parse( "login_alert.html", "Preparsed:CONTENT" );
     $tpl->theme = $rh->skin;
     return GRANTED;
   }
   else $_POST["_flogin"] = $logins[0];
 }

 if (!$principal->IsGrantedTo( "noguests" ) && !$principal->Login())  
 {
  // tags: FormStart, FormEnd, CookieLogin, FocusToID {_login, _password}
  $focus = "loginForm._fpassword";
  if ($principal->state == PRINCIPAL_WRONG_LOGIN) $focus = "loginForm._flogin";
  if (!$principal->cookie_login) $focus = "loginForm._flogin";
  
  $login_domain = array(
        "Form"      => $state->FormStart( MSS_POST,$rh->url, "id=loginForm name=loginForm" ),
        "/Form"     => $state->FormEnd(),
        "CookieLogin"    => ($object->params[0]?$object->params[0]:
                             ($principal->cookie_login?$principal->cookie_login:"")),
        "FocusToID"      => $focus,
                       );
  $tpl->LoadDomain( $login_domain );

  $tpl->Assign( "Error", "<br />");
  // previous attempt failed:
  if ($principal->state == PRINCIPAL_NOT_ALIVE)
  {
    $tpl->Assign( "Error", $tpl->message_set["LoginNotAlive"] );
    $tpl->Assign( "CookieLogin", $_POST["_flogin"] );
  }
  if (($principal->state == PRINCIPAL_WRONG_LOGIN)
      &&
      ($_POST["_flogin"] != ""))
  {
    $tpl->Assign( "Error", $tpl->message_set["WrongLogin"] );
    $tpl->Assign( "CookieLogin", $_POST["_flogin"] );
  }
  if ($principal->state == PRINCIPAL_WRONG_PWD)
  {
    $tpl->Assign( "Error", $tpl->message_set["WrongPassword"] );
    $tpl->Assign( "CookieLogin", $_POST["_flogin"] );
  }


 $tpl->theme = $rh->theme;
  $tpl->Parse( "login.html", "Preparsed:CONTENT" );
 $tpl->theme = $rh->skin;
 }
 else
 {

  // если есть спецполе для возврата -- возвращаемся
  if ($_REQUEST["freturn"])
   $rh->Redirect( $_REQUEST["freturn"] );

  $welcome = &new NpjObject( &$rh, $rh->node_user.":".$rh->default_node_welcome );
  $data = $welcome->Load(3);

  $logged_domain = array(
        "Link:UserLogin" => $welcome->Link( $principal->data["login"]."@".$principal->data["node_id"] ),
        "UserName"   => str_replace(" ", "&nbsp;", $principal->data["user_name"]),
        "Form"      => $state->FormStart( MSS_POST,$rh->url, "id=loginForm name=loginForm" ),
        "/Form"     => $state->FormEnd(),
        "checked1"  => (!isset($_COOKIE[$rh->cookie_prefix."login_cookie"]))?"checked":"",
        "checked2"  => isset($_COOKIE[$rh->cookie_prefix."login_cookie"])?"checked":"",
                       );
  $tpl->LoadDomain( $logged_domain );

   $tpl->theme = $rh->theme;
     if (is_array($data) &&
         in_array("welcome", $params))
     {  $tpl->theme = $rh->skin;
        $welcome->Handler("show", array(), &$principal ); 
        $tpl->theme = $rh->theme;
        $result = $tpl->GetValue("Preparsed:CONTENT"); 
     }  else $result="";
     $tpl->Assign( "Preparsed:CONTENT", $result );
     $tpl->Parse( "login_ok.html", "Preparsed:CONTENT", TPL_APPEND );
     $tpl->Append( "Preparsed:TITLE", $tpl->message_set["Welcome"].str_replace(" ", "&nbsp;", $principal->data["user_name"])."!" );
   $tpl->theme = $rh->skin;
 }

?>
