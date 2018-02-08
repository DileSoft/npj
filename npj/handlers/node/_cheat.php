<?php

  // tags: FormStart, FormEnd, CookieLogin, FocusToID {_login, _password}

  if ($_POST["_cheatlogin"])
  {
    $principal->cheat_mode = true;
    $principal->Login( 1, $_POST["_cheatlogin"] );
  }

  $login_domain = array(
        "Form"      => $state->FormStart( MSS_POST,$rh->url, "id=loginForm name=loginForm" ),
        "/Form"     => $state->FormEnd(),
        "CookieLogin"    => ($object->params[0]?$object->params[0]:
                             ($principal->cookie_login?$principal->cookie_login:"")),
                       );
  $tpl->LoadDomain( $login_domain );

  $tpl->Assign( "Error", "<br />");
  // previous attempt failed:
  if ($principal->state == PRINCIPAL_NOT_ALIVE)
  {
    $tpl->Assign( "Error", $tpl->message_set["LoginNotAlive"] );
    $tpl->Assign( "CookieLogin", $_POST["_cheatlogin"] );
  }
  if (($principal->state == PRINCIPAL_WRONG_LOGIN)
      &&
      ($_POST["_flogin"] != ""))
  {
    $tpl->Assign( "Error", $tpl->message_set["WrongLogin"] );
    $tpl->Assign( "CookieLogin", $_POST["_cheatlogin"] );
  }

 $tpl->theme = $rh->theme;
  $tpl->Parse( "cheat.html", "Preparsed:CONTENT" );
 $tpl->theme = $rh->skin;

?>
