<?php
/*
    Principal( &$rh )  -- производит авторизацию пользователя и осуществляет контроль над ней на протяжении сессии.
      - $rh -- ссылка на RequestHandler, в котором лежит конфигурация проекта
      - сделан по технологии ((Нпж.БлокРЧ))

  ---------
  * AssignById( $id = 0 ) -- принудительно воплотить принципала в пользователя с данным $id
      - $id -- номер учётной записи (число) 

  * AssignByLogin( $login="guest", $node_id=""  ) -- принудительно воплотить принципала в пользователя с данным $login
      - $login -- логин пользователя (case-sensitive)
      - !!! redocument

  * MaskById( $id = 0 ) -- притвориться другим пользователем, не забывая своё текущее воплощение
  * MaskByLogin( $login = "guest", $node_id="" ) -- притвориться другим пользователем, не забывая своё текущее воплощение
      - !!! redocument

  * Unmask( $all=0 ) -- вернуться на шаг назад в стеке "масок"
      - $all -- если установлен, то возврат делается сразу к исходному воплощению

  * Store() -- сохранить информацию о текущем состоянии воплощения и масок в сессию

  * Identify( $try_login = 1 ) -- попытаться восстановить состояние принципала из сессии (true, если удачно)
      - $try_login -- если установлен, то в случае неудачи вызывается Login

  * LoginCookie( $login, $pwd, $strict=0 ) -- вернуть значение кук, если в $_POST есть [_save_password]
      - перезаписывает куку

  * Logout( $redirect = 1, $strict=0 ) -- уничтожить запись в сессии о данном принципале
      - $redirect -- после уничтожения выполнить редирект
      - $strict   -- если установлен в ноль, то логаут выполняется, только есть в _REQUEST есть [_logout]=yes,
                     а если в единицу, то всегда

  * Login( $redirect = 1, $strict_login=NULL, $strict_pwd=NULL ) -- авторизовать пользователя, 
                                                                    воплотить этого принципала в него и Store()
                                                                    false - если не вышло
      - $redirect                  -- в случае успеха авторизации выполнить редирект на ту же страницу
      - $strict_login, $strict_pwd -- если установлены, то информация о целесообразности логина не ищется в _POST

  * IsGrantedTo( $method, $object_class="", $object_id=0, $options="" ) -- true, если у принципала есть 
                                                                           такие права на такой объект
      - $method       -- метод проверки прав, соответствует security/method.php - обработчику
      - $object_class -- строка-псевдокласс объекта, например "page"
      - $object_id    -- идентификатор (желательно численный) объекта, например, "/products/ak74"
      - $options      -- опциональные параметры для метода, произвольного формата. рекомендуется array( key=>value )

  // абстрактные методы для override в потомках:
  * &_GetByID( $id )         -- получить хэш-массив параметров пользователя с id=$id (число)
  * &_GetByLogin( $login, $node_id, $_halt_if_fail=1 )   -- получить хэш-массив параметров пользователя с login=$login (case-sensitive)
      - !!! redocument
  * &_Login( $login, $pwd, $cookie, $node_id )  -- вернуть false, если кривой логин или пароль, или хэш-массив параметров пользователя
      - !!! redocument
  * _Logout()               -- сделать где-то пометку о логауте, или, вернув false, запретить логаут

  // свойства:
  * $this->cheat_mode -- режим, когда не нужно вводить паролей вообще
  * $this->login_cookie_mode -- способ, как делается автологин: либо ближайший год, либо до перезапуска браузера.
  * $this->logout_referer -- после логаута делать редирект на реферера? умолчание -- ДА
  * $this->data        -- хэш-массив с описанием пользователя или текущей маски принципала. как правило содержит
                           * id = user_id -- идентификатор пользователя
                           * login        -- его логин
                           * user_name    -- имя пользователя "нормальным языком"
  * $this->genuine     -- счётчик числа надетых масок
  * $this->genuines[0] -- всегда содержит описание текущего воплощения
  * $this->state       -- состояние принципала:
                           * PRINCIPAL_UNKNOWN     -- неизвестное
                           * PRINCIPAL_WRONG_LOGIN -- неизвестный логин
                           * PRINCIPAL_WRONG_PWD   -- неправильный пароль для этого логина
                           * PRINCIPAL_NOT_ALIVE   -- "мёртвый"/забаненый пользователь
                           * PRINCIPAL_AUTH        -- авторизован успешно
                           * PRINCIPAL_RESTORED    -- взят из сессии, авторизация не производилась

=============================================================== v.5npj (Kuso)
*/
define ("GRANTED",   1);
define ("DENIED",    0);  // NEVER set to false. it becomes dangerous for acl cache
define ("TRY_LOGIN", 1);
// states
define ("PRINCIPAL_UNKNOWN", 0);
define ("PRINCIPAL_WRONG_LOGIN", 1);
define ("PRINCIPAL_WRONG_PWD", 2);
define ("PRINCIPAL_NOT_ALIVE", 3);
define ("PRINCIPAL_AUTH", 4);
define ("PRINCIPAL_RESTORED", 5);
//login_cookie_mode
define ("PRINCIPAL_PERSISTENT", 0);
define ("PRINCIPAL_SESSION", 1);



class Principal
{
   var $rh;
   var $config;
   var $data;
   var $genuines;
   var $genuine;
   var $logout_referer = true;
   var $cheat_mode = false;

   var $state;
   var $_security_time = 0; // ???(DBG)
   var $_security_matching = 0; // ???(DBG)

   function Principal( &$rh )
   {
     $this->rh = &$rh;
     $this->config = &$rh;
     $this->data = array();
     $this->genuines = array();
     $this->genuine = 0;
     $this->state = PRINCIPAL_UNKNOWN;
   }

   // присвоение принципала (забываем, кто он был до того)
   function AssignById( $id = 0 )
   { $this->data = $this->_GetByID( $id );
     $this->rh->debug->Trace("Assigning by id=$id; == ".$this->data["login"]);
     $this->genuines[0] = $this->data;
     $this->genuine=0;
   }
   function AssignByLogin( $login="guest", $node_id="" )
   { $this->data = $this->_GetByLogin( $login, $node_id );
     $this->genuines[0] = $this->data;
     $this->genuine=0;
   }
   // принципал надевает маску, но помнит, кто он был до того.
   function MaskById( $id = 0 )
   { $this->genuines[$this->genuine++] = $this->data;
     $this->data = $this->_GetByID( $id );
   }
   function MaskByLogin( $login = "guest", $node_id="" )
   { $this->genuines[$this->genuine++] = $this->data;
     $this->data = $this->_GetByLogin( $login, $node_id );
   }
   function Unmask( $all=0 )
   {
     if (count($this->genuines) == 0) return false;
     if ($all) { $this->data=$this->genuines[0]; $this->genuine=0; }
     else { $this->data=$this->genuines[$this->genuine-1]; $this->genuine--; }
     return true;
   }

   // авторизация и логин
   function Store()
   {
      $_SESSION[$this->rh->cookie_prefix."user"] = $this->data;
      $_SESSION[$this->rh->cookie_prefix."user_genuinecount"] = $this->genuine;
      $_SESSION[$this->rh->cookie_prefix."user_genuines"] = $this->genuines;
   }
   function Identify( $try_login = 1 )
   {
     if ($_COOKIE[$this->rh->cookie_prefix."login"]) $this->cookie_login = $_COOKIE[$this->rh->cookie_prefix."login"];
     if ($try_login) if ($this->Login()) return true; 
                     else $identify=1;
     else $identify=1;
     if ($identify)
     if (isset($_SESSION[$this->rh->cookie_prefix."user"]) && isset($_SESSION[$this->rh->cookie_prefix."user"]["login"])) 
     { 
        $this->data = $_SESSION[$this->rh->cookie_prefix."user"];
        $this->genuine = $_SESSION[$this->rh->cookie_prefix."user_genuinecount"];
        $this->genuines = $_SESSION[$this->rh->cookie_prefix."user_genuines"];
        $this->rh->debug->Trace( "Principal: Restoring from session: <b>".$this->data["login"]."</b>");
        $this->state = PRINCIPAL_RESTORED;

        // БлокРЧ.4 (мерж)
        
        if ($_POST["_logout_at"] == "cookie") // renew cookie
          $this->LoginCookie( $this->data["login"], "", false, $this->data["login_cookie"] );
        if ($_POST["_logout_at"] == "session") 
          setcookie( $this->rh->cookie_prefix."login_cookie", "", time()-3600, "/", $this->rh->cookie_domain ); // remove cookie
        if (($_POST["_logout_at"] == "cookie") || ($_POST["_logout_at"] == "session"))
        { // редирект туда же после успешного ништяка (перезаписи куки)
          //$this->rh->state->Free( session_name() );
          $this->rh->Redirect( $this->rh->Href($this->rh->url, STATE_USE), STATE_IGNORE ); 
        }
        
        return true;
     } else return false;
   }
   function LoginCookie( $login, $pwd, $strict = 0, $magic="nomagic" )
   {
     if ($strict || $_POST["_logout_at"] == "cookie") 
     {
       $value = md5($magic.$login);                       
       if ($strict == 2) return $value; // just generate, do not store
       
       setcookie( $this->rh->cookie_prefix."login", $login, $this->login_cookie_mode?0:time()+$this->rh->cookie_expire_days*24*3600, "/", $this->rh->cookie_domain );
       setcookie( $this->rh->cookie_prefix."login_cookie", $value, $this->login_cookie_mode?0:time()+$this->rh->cookie_expire_days*24*3600, "/", $this->rh->cookie_domain );
       return $value;
     }
     return false;
   }
   function Logout( $redirect = 1, $strict=0 )
   {
     if (!$strict && !($_REQUEST["_logout"] == "yes")) return false; 
     if (!$this->_Logout()) return false;
     session_destroy();
     setcookie( $this->rh->cookie_prefix."login_cookie", "", time()-3600, "/", $this->rh->cookie_domain ); // remove cookie
     unset($_GET[session_name()]);
     setcookie(session_name(),"",0,"/", $this->rh->cookie_domain);
     $this->rh->state->Free( session_name() );
//     $this->rh->debug->Error( $_REQUEST[ session_name()] );
     if ($this->logout_referer)
       $this->rh->Redirect( $_SERVER["HTTP_REFERER"], 0 ); // уходим обратно, уходим
     return true;
   }
   function Login( $redirect = 1, $strict_login=NULL, $strict_pwd=NULL )
   { $debug = &$this->rh->debug;
     if (!$this->cheat_mode &&  // в чит-моде не работает логин через куки
         $_COOKIE[$this->rh->cookie_prefix."login_cookie"] && !$_SESSION['up']) 
     {  
       // renew cookie
       setcookie( $this->rh->cookie_prefix."login",        $_COOKIE[$this->rh->cookie_prefix."login"], 
                  $this->login_cookie_mode?0:time()+$this->rh->cookie_expire_days*24*3600, "/", $this->rh->cookie_domain );
       setcookie( $this->rh->cookie_prefix."login_cookie", $_COOKIE[$this->rh->cookie_prefix."login_cookie"], 
                  $this->login_cookie_mode?0:time()+$this->rh->cookie_expire_days*24*3600, "/", $this->rh->cookie_domain );
       // set login to strict
       $_strict_login = $strict_login;
       $strict_login = $_COOKIE[$this->rh->cookie_prefix."login"];
       $strict_pwd   = "";
       $cookie_mode  = true;
     }

     if (!$strict_login && !isset($_POST["_flogin"])) return false; 
     if ($strict_login)
     { $login = $strict_login;
       $pwd   = $strict_pwd;
     }
     else
     { $login = $_POST["_flogin"];
       $pwd   = $_POST["_fpassword"];
     }

     $user = &$this->_Login($login, $pwd, $cookie_mode?$_COOKIE[$this->rh->cookie_prefix."login_cookie"]:"");

     if ($cookie_mode)
      if ($user===false) if (!isset($_POST["_fpassword"]))      $this->state = PRINCIPAL_UNKNOWN;
                         else
                         { 
                           $login = $_POST["_flogin"];
                           $pwd   = $_POST["_fpassword"];
                           $user = &$this->_Login($login, $pwd);
                         }
      else // если есть пользователь ура.
      {
        if (!$_SESSION['up']) 
        {
          //Куки бровзер отдает? Значит подавляем автодополнение ссылок.
          ini_set("session.use_trans_sid",0);
          session_start();

          // БлокРЧ.Кусо.2 -- здесь бы записать $_POST[] в сессию
          $_SESSION['NPJ_POST'] = $_POST;

          $_SESSION['up']=1;
          $_SESSION['ip']=getenv('REMOTE_ADDR');
        }
      }

     if ($user === false)
     {
        // БлокРЧ.5.2.мерж
        if ($_SESSION['up']) session_destroy();
        return false;
     }
     $this->state = PRINCIPAL_NOT_ALIVE;
     if ($user["alive"] <> 1) // логин можно только живым пользователям
     {
        // БлокРЧ.5.2.мерж
        if ($_SESSION['up']) session_destroy();
        return false;
     }

     // БлокРЧ.5.2.мерж
     if (!$_SESSION['up']) 
     {
        session_start();
        $_SESSION['up']=1;
        $_SESSION['ip']=$_SERVER['REMOTE_ADDR'];
     }
     //Начинаются извраты со скрытием СИДА для тех, у кого куки работают. Запоминаем, куда шел.    
     $_SESSION['uri']=$this->rh->Href($this->rh->url, STATE_USE); //$_SERVER['REQUEST_URI'];

     $this->cookie_login = $user["login"];
     if ($_POST["_save_login"] || ($_POST["_logout_at"] == "cookie") || $this->login_cookie_mode)
       setcookie( $this->rh->cookie_prefix."login", $this->cookie_login, $this->login_cookie_mode?0:time()+$this->rh->cookie_expire_days*24*3600, "/", $this->rh->cookie_domain );
     $this->AssignById($user["user_id"]);
     $this->data["inline_password"] = $pwd;
     $this->Store();
     $this->state = PRINCIPAL_AUTH;

     // here we need to renew cookie
     if ($_POST["_logout_at"] == "cookie") // renew cookie
       $this->LoginCookie( $this->data["login"], "", false, $this->data["login_cookie"] );

     if ($redirect && !$cookie_mode) 
     {  // БлокРЧ.5.3.мерж
        // !!! подозрение на отсутствие реврайт-мода
        // $this->rh->state->Free();
        $this->rh->state->Set("cookietest", 1);
        $this->rh->state->Set(session_name(), session_id());
        $this->rh->Redirect( $this->rh->Href($this->rh->url, STATE_USE), STATE_IGNORE ); 
        //header("Location: $PHP_SELF?cookietest=1&".session_name()."=".session_id());
        //exit; 
     }
      
      //$this->rh->Redirect( $this->rh->Href($this->rh->url), 0 ); // same page, but use StateSet.
     return true;
   }

   // проверка security
   function IsGrantedTo( $method, $object_class="", $object_id=0, $options="" )
   {
    $this->_security_matching++; // ???(DBG)
    $m1 = $this->rh->debug->_getmicrotime(); // ???(DBG)

    $state     = &$this->rh->state;
    $rh        = &$this->rh;
    $cache     = &$this->rh->cache;
    $tpl       = &$this->rh->tpl;
    $db        = &$this->rh->db;
    $debug     = &$this->rh->debug;
    $principal = &$this;

    // sorry, для is_granted_to нет $object. Потому что это далеко не обязательная фича
    
    $rh = $this->rh;
    $__fullfilename = $this->config->security_dir.$method.".php";
    $this->config->debug->Trace("Security: ".$__fullfilename);
    if (!file_exists($__fullfilename)) $this->config->debug->Error("Principal: security handler <b>'$method'</b> not found.");

    $output = include($__fullfilename);
    if ($output===false) { $debug->Error("Problems (file: ".__FILE__.", line: ".__LINE__."): ".ob_get_contents()); $output=DENIED; }

    $m2 = $this->rh->debug->_getmicrotime(); // ???(DBG)
    $this->_security_matching--; // ???(DBG)
    if ($this->_security_matching == 0) $this->_security_time+= $m2-$m1; // ???(DBG)

    return $output;
   }

   // abstract:
   function &_GetByID( $id )         
   { return array( "user_id" => 1, "alive" => 1, "id" => 0, "login" => "guest", "node_id"=>"local" ); }
   function &_GetByLogin( $login, $node_id="", $_halt_if_fail=1 )
   { return $this->_GetByID($login);                }
   function &_Login( $login, $pwd, $cookie="", $node_id="" )  
   { return $this->_GetByID($login);                }
   function _Logout()               
   { return true;                                   }


// EOC{ Principal } 
}



?>
