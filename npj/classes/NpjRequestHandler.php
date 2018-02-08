<?php
/*
    NpjRequestHandler( $config )  -- Обработка запроса и request-related core features
    ---------

  * _HandleRequest()                      -- holy crap. здесь собственно и происходит Npj-specific обработка запроса
  * GetConfigDomain()                     -- заполнение домена TemplateEngine базовыми значениями
  * &_PreprocessUrl( $url )               -- разбор урла на составляющие (с помощью [[NpjObject]])
  * &_PreprocessPrincipal( $as="guest" )  --

  // спец-свойства
  * $this->object
  * $this->account
  * $this->node

  * $this->utility[] = { "skin" }
  * $this->UtilityRef() -> ref utility
  * $this->UtilityAction() -> action-stuff utility
  * $this->UtilityMail() -> mail-integration utility

=============================================================== v.3 (Kuso)
*/

class NpjRequestHandler extends RequestHandler
{

  function NpjRequestHandler( $config )
  {
    RequestHandler::RequestHandler( $config );

    $this->_SetDomains();
    $this->_RCHCookie();

    $this->UseClass( "ListSimple",  $this->core_dir );
    $this->UseClass( "ListCurrent", $this->core_dir );
    $this->UseClass( "Module",      $this->core_dir);
    $this->UseClass( "NpjModule" );
    $this->debug->Milestone("UseClass: ListSimple, ListCurrent");
  }

  function _SetDomains()
  {
   if (!$this->base_domain)
     $this->base_domain    = preg_replace("/^www\./i", "", $_SERVER["SERVER_NAME"]);
   
   if (!$this->current_domain)
     $this->current_domain = preg_replace("/^www\./i", "", $_SERVER["HTTP_HOST"]);
   
   if (!$this->cookie_domain)
     $this->cookie_domain = ".".$this->base_domain;

   if ($this->cookie_domain{0}!=".") $this->cookie_domain = ".".$this->cookie_domain;

   $_domains = explode(".", $this->cookie_domain);

   if (count($_domains)<=2 || (count($_domains)==3  && strlen($_domains[1])<=2))
     $this->cookie_domain = "";
   
   session_set_cookie_params(0, "/", $this->cookie_domain);

   session_name("PHPNPJID");
   $this->cookie_prefix = "r15_".$this->cookie_prefix;

   if ($this->base_url!="") $this->ignore_domain_type = 1;
  }

  function _RCHCookie()
  {
   // БлокРЧ.1 -- Если к нам пришел ид сессии, то мы ее стартуем. А если не пришел - то не стартуем!
   unset($_SESSION);
   if (isset($_REQUEST[session_name()])) session_start(); 


   // БлокРЧ.2 -- Вторая фаза скрытия СИД из ссылок
   if ($_GET['cookietest'])  
   {
     // ??? чуть меньшие подозрения на реврайт-моде
     $uri=$_SESSION['uri'];
     unset($_SESSION['uri']);
     if (strpos($uri,"?")) $sign="&"; else $sign="?"; 
     if ($_COOKIE[session_name()]==session_id()) $param=""; else $param=$sign.session_name()."=".session_id();
     session_unregister("uri");
     header("Location: ".$this->scheme."://".$_SERVER["HTTP_HOST"].$uri.$param); 
     exit;
   }
   else
   // БлокРЧ.Кусо.1 -- Если у нас в сессии был записан _POST, переписать его обратно
   if ((sizeof($_POST) == 0) && isset($_SESSION['NPJ_POST'])) 
   { 
     $_POST = $_SESSION['NPJ_POST']; 
     unset( $_SESSION['NPJ_POST'] ); 
   }

   // БлокРЧ.3 -- проверка на IP. Если сессия поднята если ИД пришел гетом, и если IP не совпадает 
   //             или eсли сид пришел, но сессии такой нету. Зачем нам таскать ее за собой? 
   if (($_SESSION['up'] && isset($_GET[session_name()]) && (getenv('REMOTE_ADDR') != $_SESSION['ip'])) 
        || (isset($_REQUEST[session_name()]) && !$_SESSION['up'])) 
   {
     // !!! подозрения на отсутствие реврайт-мода
     //убиваем СИД из квери стринг
     unset($_GET[session_name()]);

     //Стираем куку
     setcookie(session_name(),"",0,"/", $this->cookie_domain);
     setcookie(session_name(),"",0,"/"); // ??? seems to be harmless. Mostly.

     //и перенаправляем, куда шел, но без сессии.
     header( "Location: ".$this->scheme."://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"] ); 
     exit; 
   }
  }

  function &UtilityRef()
  {
    if ($this->utility["ref"]) return $this->utility["ref"];

    $this->UseClass("UtilityRef");
    $this->utility["ref"] = &new UtilityRef( &$this );
    return $this->utility["ref"];
  }
  function &UtilityMail()
  {
    if ($this->utility["mail"]) return $this->utility["mail"];

    $this->UseClass("UtilityMail");
    $this->utility["mail"] = &new UtilityMail( &$this );
    return $this->utility["mail"];
  }
  function &UtilityAction()
  {
    if ($this->utility["action"]) return $this->utility["action"];

    $this->UseClass("UtilityAction");
    $this->utility["action"] = &new UtilityAction( &$this );
    return $this->utility["action"];
  }

  function _HandleRequest()            
  { 
    $data = GRANTED;

    // проверка, не забанен ли данный юзер
    $rs = $this->db->Execute( "select banned_datetime from ".
                             $this->db_prefix."ban_ip where iplong=".$this->db->Quote(ip2long($_SERVER["REMOTE_ADDR"])));
    $result = $rs->fields;
    if ($result && (strtotime($result["banned_datetime"])+24*60*60>time()))
      die("Cannot connect to mysql database.");
      //Fake error message for banned users

    // подключение ХЕЛПЕРА ДЛЯ САЙТА (а не для записи)
    $this->UseClass("Helper", $this->core_dir );
    $this->helper = &new Helper( &$this);

    //node_user must contain @node_name
    $nu = explode("@",$this->node_user);
    $this->node_user = $nu[0]."@".$this->node_name;

    // Тестируем урл-схему
    // include("npj/classes/testcases.php");

    // загрузить всякую мелочь в домен для комфортного парсинга
    $this->tpl->LoadDomain($this->GetConfigDomain());
    $this->tpl->LoadDomain($this->GetConfigDomain2());

    // нужно получить (и сохранить в кэше) профиль пространства, в котором находимся
    if ($this->object->class == "node")
    {
      $this->account = &new NpjObject( &$this, $this->node_user );
      $_data= $this->account->Load( 3 );
      $this->debug->Milestone( "load done -- профиль пространства, в котором находимся" );
      if (is_array($_data))
      {
        $this->tpl->Skin( $this->account->data["skin"] );
        $this->skin = $this->tpl->theme;
        $this->tpl->LoadDomain( array ( 
            "skin"        => "/".$this->base_url.$this->themes_www_dir.$this->skin,
            "images"       => "/".$this->base_url.$this->themes_www_dir.$this->skin."/images/",
            "Href:Account" => $this->Href($this->account->_NpjAddressToUrl($this->account->npj_account, NPJ_ABSOLUTE), 
                                          IGNORE_STATE),
            "Href:Mail.Account" => $this->base_host_prot.$this->Href($this->account->_NpjAddressToUrl($this->account->npj_account, NPJ_ABSOLUTE), 
                                          IGNORE_STATE),
            "Link:Account" => $this->account->Link( $this->account->data["login"]."@".$this->account->data["node_id"] ),
            "Npj:Account" => $this->account->data["login"]."@".$this->account->data["node_id"] ,
                              )       );
      }
    }
    else
    {
      if ($this->object->class != "account")
        $this->account = &new NpjObject( &$this, $this->object->npj_account );
      else
        $this->account = &$this->object;

      $_data= $this->account->Load( 3 );

      if (!is_array($_data))
      {
        if ($this->account->npj_account != $this->node_user)
        {
          $acc = explode("@",$this->node_user);
          if ($acc[1] != $this->node_name) $more_url = preg_replace( "/^foreign\/[^\/]*\/[^\/]*(\/+)/", "", $this->url."/" );
          else                             $more_url = $this->url;
          $this->_PreprocessUrl( $acc[0]."/".$more_url );
          return $this->_HandleRequest();
        }
        else $this->debug->Error("Узел как пздц!");
      }
      
        $this->tpl->Skin( $this->account->data["skin"] );
        $this->skin = $this->account->data["skin"];
        $this->tpl->LoadDomain( array ( 
            "skin"        => "/".$this->base_url.$this->themes_www_dir.$this->skin,
            "images"       => "/".$this->base_url.$this->themes_www_dir.$this->skin."/images/",
            "Href:Account" => $this->Href($this->account->_NpjAddressToUrl($this->object->npj_account, NPJ_ABSOLUTE), 
                                          IGNORE_STATE),
            "Href:Mail.Account" => $this->base_host_prot.$this->Href($this->account->_NpjAddressToUrl($this->account->npj_account, NPJ_ABSOLUTE), 
                                          IGNORE_STATE),
            "Link:Account" => $this->account->Link( $this->account->data["login"]."@".$this->account->data["node_id"] ),
            "Npj:Account" => $this->account->data["login"]."@".$this->account->data["node_id"] ,
                              )       );

      if (!($this->account->HasAccess( &$this->principal, "acl_text", $this->node_admins )))
      {
        if ($this->account->data["alive"] == 0) { $data =2;  $this->account->Forbidden("AccountNotApproved"); }
        if (($this->account->data["alive"] == 2) && 
            !(($this->object->method == "manage") && ($this->object->params[0] == "unfreeze"))
           )
        { $data =2;  $this->account->Forbidden("AccountFrozen");      }
        if ($this->account->data["alive"] == 3) { $data =2;  $this->account->Forbidden("AccountSuspended");   }
      }
    }
    if ($this->account->data["user_id"] == 1) 
    {
      if ( // админам узла можно редактировать юзерменю и аватары для гостя
            (strpos( $this->object->npj_address , $this->guest_user.":profile/pictures") !== false)
           &&
            ($this->account->HasAccess( &$this->principal, "acl_text", $this->node_admins ))
          ) ;
       else { $this->Redirect( "/".$this->base_url ); }
    }

    // кроме того, если мы загрузили аккаунт, надо загрузить к нему ещё и группы. Надо.
    $this->account->CacheGroups( &$this->principal );

    // если данный аккаунт типа COMMUNITY_SECRET, и принципал не имеет отношения, то 403
    if ($this->account->data["security_type"] == COMMUNITY_SECRET)
    {
      $maxrank = $this->cache->Restore( "maxrank_". $this->principal->data["user_id"], $this->account->data["id"], 1 );
      if ($maxrank <= 0) 
      // useful hack: show secret comms to admins
      // if (!$this->account->HasAccess( &$this->principal, "owner") && !$this->account->HasAccess( &$this->principal, "node_admins" ))
      if (!$this->account->HasAccess( &$this->principal, "owner"))
      {
        $this->object = $this->account;
        $this->object->method = "_secret";
      }
    }

    if ($this->admins_only_console)
     $this->tpl->Assign ("AllowConsole", $this->object->HasAccess( &$this->principal, "acl_text", $this->node_admins));

    // если встроены куда-нибудь, то хандлер не пускаем
    if ($this->embed) return;

    // запускаем на обработку МУА (межузловую авторизацию)
    $csadata = DENIED;
    if (($_GET["authto"] || $_COOKIE[$this->cookie_prefix."authto"] || $_COOKIE[$this->cookie_prefix."aftercsa"]) 
        && !$this->principal->IsGrantedTo( "noguests" ) && $this->node->data["created_datetime"]!="0000-00-00 00:00:00")
    {
      $node = &new NpjObject( &$this, "auth@".$this->node_name );
      $csadata = $node->Handler( "auth", array(), &$this->principal );
    }

    // создаём Скин Утилити
    $this->UseClass("UtilitySkin");
    $utility = &new UtilitySkin( &$this );
    $this->utility["skin"] = &$utility;

    // запускаем на обработку хандлер
    if (($data == GRANTED) && !$csadata)
    {
      $this->debug->Trace("---------------- handler (".$this->object->class." :: ".$this->object->method.") start ---------------------");
      // if ($this->principal->data["login"] == "kuso") $this->debug->kuso=1;
      $data = $this->object->Handler( $this->object->method, $this->object->params, &$this->principal );
      $this->debug->Trace("---------------- handler done ---------------------");
    }

    if ($data == 2) $data = GRANTED;

    // вывод RSS
    if ($this->rss && $this->object->method != "_secret") 
    {
      $this->account->data["object_tag"] = $this->object->data["tag"];

      // для комментария свой поток
      if ($this->object->class == "comments") 
        $rss_head = &new NpjObject( &$this, $this->object->npj_account.":".$this->object->npj_context );
      else
        $rss_head = &$this->account;

      $data = $this->rss->Compile( &$rss_head, $this->principal->IsGrantedTo( "noguests" )?false:true );
      $this->rss->Output();
      return $data;
    }

    // инициализируем Скин Утилити
    if ($this->object->class == "account")
    {
      $utility->InitContextMenu( &$this->object->record );
      $utility->InitPanel      ( &$this->object->record );
    }
    else
    {
      $utility->InitContextMenu( &$this->object );
      $utility->InitPanel      ( &$this->object );
    }

    if ($data && $data != GRANTED) return $data;
    else 
    {
      if ($this->tpl->GetValue("Html:HTML")) return $this->tpl->GetValue("Html:HTML");

      // у хандлеров узла не бывает комментариев (???) -------------------------------------------------
      if ($this->object->class=="node") $this->tpl->Assign("Preparsed:COMMENTS", "");
      // формирование корректных заголовков 
      $title_prefix = $this->account->npj_account.": ";
      if (!$this->tpl->GetValue("Html:TITLE"))  
        $this->tpl->Assign( "Html:TITLE", $title_prefix.$this->tpl->GetValue("Preparsed:TITLE") );
      else
        $this->tpl->Assign( "Html:TITLE", $title_prefix.$this->tpl->GetValue("Html:TITLE") );

      if ($this->tpl->GetValue("Html:TITLE") == $title_prefix)
      {
        $t = "Something";
        switch ($this->object->class)
        {
          case "node":       $t = "Node"; break;
          case "comments":   $t = "Comments"; break;
          case "versions":   $t = "Versions"; break;
          case "friends":
          case "account":    $t = "Журнал"; break;
          case "record":     $t = $this->object->GetType()==RECORD_POST?"Post":"Document"; break;
        }
        $this->tpl->Assign( "Html:TITLE", $title_prefix.$this->tpl->message_set["Title".$t] );

      }

      $this->tpl->Assign( "Html:TITLE", $this->tpl->Format($this->tpl->GetValue("Html:TITLE"), "html2text", NULL, 0, array("nolinks"=>1)) );
  
      // парсинг подходящего шаблона, если у нас ещё нет готового чего-нибудь
      if ($data == GRANTED) 
      if ($this->account != false)
      {
        // !!! здесь нужно перезаполнить некоторые переменные, ага
        // и если парсим профиль, архив, ещё что-то -- выбрать другие шаблоны
        if (($this->object->class=="friends") && ($this->object->method=="default"))
          $this->tpl->Parse( "friends.html", "html_body" );
        else
        $this->tpl->Parse( "record.html", "html_body" );
      }
      else
      { 
        // !!! в зависимости от шаблонов по узлу парсить что-нибудь другое
        $this->tpl->Parse( "common.html", "html_body" );
      }
    }

    return $this->tpl->Parse("html.html");
  }

  // заполняем домен значениями по-умолчанию
  function GetConfigDomain()
  {
    $a = array(
    "theme"        => "/".$this->base_url.$this->themes_www_dir.$this->theme,
    "images"       => "/".$this->base_url.$this->themes_www_dir.$this->theme."/images/",
    "theme_images" => "/".$this->base_url.$this->themes_www_dir.$this->theme."/images/",
    "NodeName"     => $this->node_title, 
    "BaseHost"     => $this->base_host_prot,

    "M:cms"          => ($this->cms_url && $this->helper->roles["editor"]),
    "M:cms/"         => "/".ltrim($this->cms_url,"/"),
    "M:cms.Show"     => $this->helper->hide_edit?0:1,
    "M:cms.Hidden"   => $this->helper->hide_hidden?0:1,
              );
    return $a;
  }
  function GetConfigDomain2()
  {
    $a = array(
    "Node"         => $this->node_name, 
    "NodeName"     => $this->node_title, 
    "Link:Logout"  => $this->object->Href("login@"), // !!! refactor
    "/"            => ($this->base_domain==$this->current_domain?"/".$this->base_url:
                      $this->scheme."://".preg_replace("/:.*$/","",$this->base_domain). "/".$this->base_url), 
    "Host"         => $this->scheme."://".preg_replace("/:.*$/","",$this->base_domain). "/".$this->base_url,  
    "BaseHost"     => $this->scheme."://".preg_replace("/:.*$/","",$this->base_domain), 
    "Npj:Current"  => $this->object->npj_address,//??? refactor. russian letters. method.
    "Href:Current" => $this->Href($this->object->_NpjAddressToUrl( $this->object->npj_address, 
                                                                     NPJ_ABSOLUTE), IGNORE_STATE ),
    "Npj:Object"  => $this->object->npj_object_address,
    "Href:Object" => $this->Href($this->object->_NpjAddressToUrl( $this->object->npj_object_address, 
                                                                     NPJ_ABSOLUTE), IGNORE_STATE ),
    "Npj:Principal" => $this->principal->data["login"]."@".$this->principal->data["node_id"],
    "Link:Principal" => $this->object->Link( $this->principal->data["login"]."@".$this->principal->data["node_id"] ),
    "Href:Principal" => 
      $this->Href($this->object->_NpjAddressToUrl( $this->principal->data["login"]."@".
      $this->principal->data["node_id"].($this->principal->data["node_id"]==$this->node_name?"":"/".$this->node_name), 
                                                                     NPJ_ABSOLUTE), IGNORE_STATE ),
              );
    $a["Link:Host"] = "<a href=\"".$a["Host"]."\">".trim($a["Host"],"/")."</a>";

    $a["Link:Node"] = $this->tpl->Format( $this->object->Link("@".$this->node_name), "absurl" );

    $a["/!"] = $this->object->Href( $this->object->RipMethods( $this->object->npj_address ) );
    return $a;
  }

  // работаем с npj-принципалом уже
  function &_PreprocessPrincipal( $as="guest" ) 
  { 
    $this->UseClass( "DbPrincipal", $this->core_dir );
    $this->UseClass( "NpjPrincipal", $this->npj_classes_dir  );
    $this->debug->MileStone( "UseClass: DbPrincipal, NpjPrincipal done" );

    if ($this->modules["authorize"]) // override principal model
    {
      $this->UseClass( "NpjCustomPrincipalSuper", 
                       $this->modules_dir.$this->modules["authorize"]["module_dir"]."classes/",
                       "SuperPrincipal" );
      $this->UseClass( "NpjCustomPrincipal", 
                       $this->modules_dir.$this->modules["authorize"]["module_dir"]."classes/",
                       $this->modules["authorize"]["principal"] );
      $p = &new NpjCustomPrincipal(&$this); 
    }
    else $p = &new NpjPrincipal(&$this); 

    $this->principal = &$p;

    if ($this->embed || !$p->Identify()) 
    {  // вот это дело пытается читать гостевого принципала из БД. Это я и закоменчу.
       //$p->AssignByLogin($as); $p->Store(); 
       include( $this->principal_dir."principal_".$as.".php" );
       $pp = &$p;

       if (isset($this->guest_override))
       {
         foreach($this->guest_override as $k=>$v)
           $pp->data[$k] = $v;
       }
    }
    else
    {
      $pp = &$p;
  
      if ($this->modules["authorize"]) // override principal model
        $p = &new NpjCustomPrincipal(&$this); 
      else
      $p = &new NpjPrincipal( &$this );
      include( $this->principal_dir."principal_".$as.".php" );
      $pp->data["options"] = array_merge( $p->data["options"], $pp->data["options"] );
      $pp->data = array_merge( $p->data, $pp->data );
    }

    $this->debug->Trace("RH->_PrePrincipal: identified as: <b>".$pp->data["login"]."</b> { ".$pp->data["user_name"]." }");

    return $pp; 
  }


// --------------------- разбор ссылок ------------------------------------
  // разбор Нпж-урла на составляющие
  function &_PreprocessUrl( $url )
  {
    $this->UseClass("NpjObject", $this->npj_classes_dir );
    $this->debug->Milestone( "UseClass: NpjObject done" );

    // создаём $rh->node и грузим его
    // НЕКРАСИВЫЙ код Кукуца. Кусо said: shoo, shoo, dirty kukutz!
    $rs = $this->db->Execute( "select *, title as subject, node_id as id from ".
                        $this->db_prefix."nodes where is_local=1 limit 1");
    $data = $rs->fields;
    if (!$data)
      $this->debug->Error("Cannot load local node. Check MySQL connection, then check in table <tt>".$this->db_prefix."nodes</tt> that you have node with <tt>is_local=1</tt>");
    $this->node_name  = $data["node_id"];
    $this->node_title = $data["title"];
    $this->node = &new NpjObject( &$this, "show@".$this->node_name );
//    $this->node->Load(2);
    $this->node->data = &$data;
    $this->cache->Store( "npj", "show@".$this->node_name, 2, &$this->node->data );
    $this->cache->Store( "node", $this->node_name, 2, &$this->node->data );

    if ($data["npj_version"]!=$this->npj_version)
    {
      //UPDATER v1
      $this->node->Handler("_update_node", array("node_version"=>$data["npj_version"]), $this->principal);
    }
    // /НЕКРАСИВЫЙ код Кукуца.

    $path = $url;
    $this->debug->Trace( "Preprocessing Url = ".$url );
    $this->object = &new NpjObject( &$this );
    $npj_address = $this->object->_UrlToNpjAddress( $url );
    $this->debug->Trace( "Npj Address = <b>".$npj_address."</b>" );
    $this->object->_Init( $npj_address );

    $this->object->_Trace( "NpjObject READY:");

    return $url;
  }
// ===================== разбор полётов ====================================

// added by kukutz @ 15082003 1000, changed @ 11112003
  function End()
  {
    if (($this->debug_level < 0) || ($this->debug->IsError($this->debug_level)))
    {
      $this->debug->Milestone( "RH->Close()" );
      $this->debug->Flush();
    } 
    ob_end_flush();
    flush();
    if ($this->use_htcron)
    {
      $this->UseLib("htCron");
      htcCycle(&$this->db, $this->db_prefix."npz");
    }
    $this->dbal->Close();
    exit();
  }

}

?>