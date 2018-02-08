<?php
  //runs if
  //  * unauthorized + $authto
  //  * unauthorized + $_COOKIE["aftercsa"]
  //  * requested as /auth?for
  //  * requested as /auth?back

  if ($rh->node->data["created_datetime"]=="0000-00-00 00:00:00")
    return DENIED;

  //если мы залогинены, нам не нужна donotcsa и все остальные безумные куки
  if ($principal->IsGrantedTo( "noguests" )) 
  {
    setcookie ($rh->cookie_prefix."donotcsa",    "", time()-60*60*24*30*12, "/", $rh->cookie_domain);
    setcookie ($rh->cookie_prefix."aftercsa",    "", time()-60*60*24*30*12, "/", $rh->cookie_domain);
    setcookie ($rh->cookie_prefix."aftercsareq", "", time()-60*60*24*30*12, "/", $rh->cookie_domain);
  }

  if ($_GET["authto"]) $authto = $_GET["authto"];
  else $authto = $_COOKIE[$rh->cookie_prefix."authto"];

  //при наличии $_GET["for"] запускать процесс trackback 
  if ($_GET["for"])
  {
   //on success, show image
   while (ob_get_level()) {
    ob_end_clean();
   }
   ob_start("ob_gzhandler");
   header("Content-Type: image/gif");
   header("Content-Disposition: inline;filename=z.gif");
//   echo "123";

   if ($principal->data["node_id"]==$rh->node_name && $principal->IsGrantedTo( "noguests" )) 
   {
//   echo $_SERVER["REMOTE_ADDR"];
    $rh->UseLib("Net_Socket", "PEAR");
    $rh->UseLib("Net_URL", "PEAR");
    $rh->UseLib("HTTP_Request", "PEAR");

/*    //MAY BE DANGEROUS, THINK ABOUT THIS!!!
    $nodeobject = &new NpjObject( &$rh, "show@".$_GET["node"] );
    $nodeobject->Load(1);

    //HTTP-Request
    $req = &new HTTP_Request($nodeobject->data["url"]."auth");
*/
//    $rs = $db->Execute("SELECT * FROM ".$rh->db_prefix."nodes where ip=".$db->Quote($_SERVER["REMOTE_ADDR"]));
    $rs = $db->Execute("SELECT * FROM ".$rh->db_prefix."nodes where node_id=".$db->Quote($_GET["node"]));
    if ($rs->RecordCount()>0)
    {
     $req = &new HTTP_Request($rs->fields["url"]."auth");

     $req->setMethod(HTTP_REQUEST_METHOD_POST);
     $req->addPostData("back", $_GET["for"]);
     $req->addPostData("user[login]",        $principal->data["login"]);
     //!!!! dangerous
     $req->addPostData("user[node_id]",      $principal->data["node_id"]);
     $req->addPostData("user[user_name]",    $principal->data["user_name"]);
     $req->addPostData("user[alive]",        $principal->data["alive"]);
     $req->addPostData("user[_formatting]",  $principal->data["_formatting"]);
     $req->addPostData("user[_pic_id]",      $principal->data["_pic_id"]);
     $req->addPostData("user[theme]",        $principal->data["theme"]);
     $req->addPostData("user[lang]",         $principal->data["lang"]);
     $req->addPostData("user[more]",         $principal->data["more"]);
     $req->addPostData("user[email]",        $principal->data["email"]);
     $req->addPostData("user[id]",           $principal->data["id"]);
     $req->sendRequest();
     $response = $req->getResponseBody();

     //green
     echo base64_decode("R0lGODlhAQABAIAAADP/M////yH5BAAAAP8ALAAAAAABAAEAAAICRAEAOw"); 
    }
    echo $response;
   }
   else
   {
    //red
    echo base64_decode("R0lGODlhAQABAIAAAP8AAP///yH5BAAAAP8ALAAAAAABAAEAAAICRAEAOw"); 
   }
   flush();
   exit();
  }

  //при наличии $_POST["back"] запускать процесс приёма trackback-а 
  if ($_POST["back"])
  {
   $u = $_POST["user"];


//   $rs0 = $db->Execute("SELECT * FROM ".$rh->db_prefix."nodes where ip=".$db->Quote($_SERVER["REMOTE_ADDR"]));
//   if ($rs0->RecordCount()>0 && $rs0->fields["node_id"]==$u["node_id"])
   $rs0 = $db->Execute("SELECT * FROM ".$rh->db_prefix."nodes where node_id=".$db->Quote($u["node_id"]));

   if ($rs0->RecordCount()>0 && 
       (
        $rs0->fields["ip"]==$_SERVER["REMOTE_ADDR"] ||
        (
         $rs0->fields["alternate_ip"] && $rs0->fields["alternate_ip"]==$_SERVER["REMOTE_ADDR"]
        )
       )
      )
   {

    $rs = $db->Execute("SELECT * FROM ".$rh->db_prefix."users where login=".$db->Quote($u["login"])." AND node_id=".$db->Quote($u["node_id"]));
    if ($rs->RecordCount()==0)
    {
     $sql = "INSERT INTO ".$rh->db_prefix."users (login,node_id,user_name,alive,_formatting,_pic_id,theme,lang,more,email,csa,original_user_id) VALUES (".
       $db->Quote($u["login"]).", ".$db->Quote($u["node_id"]).", ".$db->Quote($u["user_name"]).", ".
       $db->Quote($u["alive"]).", ".$db->Quote($u["_formatting"]).", ".$db->Quote($u["_pic_id"]).", ".
       $db->Quote($u["theme"]).", ".$db->Quote($u["lang"]).", ".$db->Quote($u["more"]).", ".
       $db->Quote($u["email"]).", ".$db->Quote($_POST["back"]).", ".$db->Quote($u["id"]).")";
     $db->Execute($sql);

    //популяция
     $account = &new NpjObject( &$rh, $u["login"]."@".$u["node_id"] );
     $node_principal = &new NpjPrincipal( &$rh );
     $rh->principal->MaskById(2);
     $account->Handler( "populate", array("foreign"=>1,), &$node_principal );
     $rh->principal->UnMask();

    }
    else
    {
     $sql = "UPDATE ".$rh->db_prefix."users SET user_name=".$db->Quote($u["user_name"]).
       ", alive=".$db->Quote($u["alive"]).",     _formatting=".$db->Quote($u["_formatting"]).
       ", _pic_id=".$db->Quote($u["_pic_id"]).", theme=".$db->Quote($u["theme"]).
       ", lang=".$db->Quote($u["lang"]).",       more=".$db->Quote($u["more"]).
       ", email=".$db->Quote($u["email"]).",     csa=".$db->Quote($_POST["back"]).
       ", original_user_id=".$db->Quote($u["id"]).
       " WHERE node_id=".$db->Quote($u["node_id"])." AND login=".$db->Quote($u["login"]);
     $db->Execute($sql);
    }

    while (ob_get_level()) {
     ob_end_clean();
    }
    //header("HTTP/1.0 204 No Content");
    ob_start("ob_gzhandler");
    echo "Ok: ".$sql;
   }
   flush();
   exit();
  }

  if ($_COOKIE[$rh->cookie_prefix."aftercsareq"] && $_COOKIE[$rh->cookie_prefix."aftercsareq"]!=$_SERVER["REQUEST_URI"]) return DENIED;

  //при наличии $_COOKIE["aftercsa"] пытаться залогинить
  if ($_COOKIE[$rh->cookie_prefix."aftercsa"])
  {
    //проверить, есть ли юзер в users
    $sql = "SELECT * FROM ".$rh->db_prefix."users where csa=".$db->Quote($_COOKIE[$rh->cookie_prefix."aftercsa"]);
    $rs = $db->Execute($sql);
    if ($rs->RecordCount()!=0)
    {
     $supertag = $rs->fields["supertag"];
     //проверить, не протухла ли aftercsa
     $rs2 = $db->Execute("SELECT * FROM ".$rh->db_prefix."csa where csa=".$db->Quote($_COOKIE[$rh->cookie_prefix."aftercsa"]));
     if ($rs->RecordCount()!=0)
      if ($rs2->fields["expire"]>date("YmdHis"))
      {
        //снести aftercsa из БД
        $db->Execute("DELETE FROM ".$rh->db_prefix."csa where expire<".$db->Quote(date("YmdHis")));
        //снести aftercsa
        setcookie ($rh->cookie_prefix."aftercsa", "", time()-60*60*24*30*12, "/", $rh->cookie_domain);
        setcookie ($rh->cookie_prefix."aftercsareq", "", time()-60*60*24*30*12, "/", $rh->cookie_domain);

        $principal->cheat_mode = true;
        $principal->login_cookie_mode = PRINCIPAL_SESSION;
        $principal->LoginCookie( $rs->fields["login"], "", 1);
        $principal->Login( 1, $rs->fields["login"]."@".$rs->fields["node_id"] );
      }
    }
    else 
    {
      //если нету, поставить сеансовый флаг, шобы больше не запускался CSA-механизм
      setcookie ($rh->cookie_prefix."donotcsa", "yes", 0, "/", $rh->cookie_domain);
    }
    //снести aftercsa из БД
    $db->Execute("DELETE FROM ".$rh->db_prefix."csa where expire<".$db->Quote(date("YmdHis")));
    //снести aftercsa
    setcookie ($rh->cookie_prefix."aftercsa", "", time()-60*60*24*30*12, "/", $rh->cookie_domain);
    setcookie ($rh->cookie_prefix."aftercsareq", "", time()-60*60*24*30*12, "/", $rh->cookie_domain);
    return DENIED;
  }
  

  //set permanent cookie
  if ($_GET["authto"]) setcookie ($rh->cookie_prefix."authto", $_GET["authto"], time()+60*60*24*90, "/", $rh->cookie_domain);

//$debug->Error("p:".$principal->IsGrantedTo( "noguests" ).";r=".$authto.";c=".$_COOKIE["donotcsa"]);
  //при неавторизованном пользователе и наличии $authto должен запускать процесс авторизации
  if (!$principal->IsGrantedTo( "noguests" ) && $authto  && !$_COOKIE[$rh->cookie_prefix."donotcsa"]) 
  {
   $tempid = rand(100000, 9999999);
   $nodeobject = &new NpjObject( &$rh, "show@".$authto );
   $nodeobject->Load(1);

   setcookie ($rh->cookie_prefix."aftercsa", $tempid, 0, "/", $rh->cookie_domain);
   setcookie ($rh->cookie_prefix."aftercsareq", $_SERVER["REQUEST_URI"], 0, "/", $rh->cookie_domain);
   header("Refresh: 10");

   //положить aftercsa в БД
   $db->Execute("INSERT INTO ".$rh->db_prefix."csa (csa, expire) VALUES (".
               $db->Quote($tempid).", ".$db->Quote(date("YmdHis", time()+60*2)).")");


   $csa_domain = array(
         "Link:AuthNode"  => $nodeobject->Link("node@".$authto, "", "@".$authto),
         "AuthTo"    => $nodeobject->data["url"]."auth?for=".$tempid."&node=".$rh->node_name, //url of authto node
         "GoTo"      => $rh->scheme."://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"],
         "Form"      => $state->FormStart( MSS_POST,$rh->url, "id=loginForm name=loginForm" ),
         "/Form"     => $state->FormEnd(),
         "CookieLogin"    => ($object->params[0]?$object->params[0]:
                             ($principal->cookie_login?$principal->cookie_login:"")),
   );
   $tpl->LoadDomain( $csa_domain );

   // tags: FormStart, FormEnd, CookieLogin, FocusToID {_login, _password}

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
   $tpl->Parse( "auth.html", "Preparsed:CONTENT" );
  $tpl->theme = $rh->skin;

  return GRANTED;

  }

 return DENIED;

?>