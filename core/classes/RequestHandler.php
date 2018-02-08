<?php
/*
    RequestHandler( $config_path="config/default.php"  )  -- ОСНОВНОЙ обработчик запроса к серверу 
      - $config-path -- путь к конфигурационному файлу (присоединяет его в себя в конструкторе)

  ---------
  * Output( $what ) -- вывод чего-то в поток вывода
  * End()           -- штатное завершение работы над запросом, с выводом, если надо, лога для отладки

  * HandleRequest( $url=NULL, $state=NULL, &$principal=NULL ) -- обработчик какого-то запроса
      - $url       -- локальный url, если не указан - то берётся из реального запроса
      - $state     -- набор-состояние StateSet, если не указан, то он формируется на основе реального запроса
      - $principal -- принципал, которым будут проверяться права, если не указан, см. выше =)
      - вызывает все _Preprocess*

  * UseClass($class_name, $class_dir="", $file_name="" ) -- подключить файл с описанием класса
      - $class_name -- имя класса 
      - $class_dir  -- каталог, где лежит файл (если не указан, то берётся из конфигурации)
      - $file_name  -- наименование файла (без ".php"), если файл называется так же как и класс, то можно не указывать

  * Redirect( $abs_url="", $ignore_state=1 ) -- сделать редирект по данному абсолютному адресу
      - $abs_url      -- адрес, если нет, то редирект осуществляется на текущую страницу
      - $ignore_state -- игнорировать текущее состояние $this->state

  * Href( $rel_url, $ignore_state=0 ) -- возвращает абсолютную ссылку по относительной. следует всегда использовать
      - $rel_url      -- относительная ссылка относительно проекта
      - $ignore_state -- игнорировать текущее состояние $this->state, в особенности, если вы его уже вручную включили в ссылку

  * Link( $href, $text="", $title="", $bonus="", $parse=1, $ignore_state=0 ) -- формирует <a href=... title=...>...</a>
      - $href  -- значение атрибута <A HREF=
      - $text  -- текст, который будет внутри <A>..</A>
      - $title -- значение атрибута <A TITLE=
      - $bonus -- что-то ещё, что записать внутрь <A ..>, например "class='text'"
      - $ignore_state -- игнорировать текущее состояние $this->state, в особенности, если вы его уже вручную включили в ссылку

  * _FuckQuotes(&$a) -- для внутреннего пользования
  * _PreprocessState( $url=NULL, $state=NULL ) -- формирует url и state, если такие ему не предоставлены, на основе
                                                  _GET & _POST. url извлекает из _GET["page"]

  // абстрактные методы для override в потомках (вызываются из HandleRequest)
  * _HandleRequest()                    -- непосредственно обработка запроса
  * _PreprocessUrl( $url )              -- разбор URL на $this->obj* (см. ниже)
  * _PreprocessPrincipal( $as="guest" ) -- формирование принципала на основе реального запроса
    - $as -- логин, под которым автоматически авторизуется принципал первый раз                                                  

  // свойства:
  * $this->dbal  -- объект класса ((DBAL)), не используется
  * $this->db    -- connection этого объекта, в формате ADODBConnection
  * $this->tpl   -- объект ((TemplateEngine))
  * $this->state -- текущее состояние обрабатываемого запроса ((StateSet))
  * $this->url   -- url обрабатываемого запроса
  * $this->debug -- объект класса ((Debug)), который используется всеми для записей в лог запроса
  * $this->principal -- объект класса-наследника ((Principal))

  * $this->obj        -- хэш-массив объекта, соответствующего обрабатываемому запросу. 
                         Создаётся только, если вы его сами создадите. в _HandlerRequest, например
  * $this->obj_class  -- строка-имя псевдо-класса объекта, например "page"
  * $this->obj_name   -- имя объекта (возможно, его идентификатор), например, "/products/ak74" или "wakkatodo"
  * $this->obj_method -- метод (handler), который запрашивается от объекта, например, "showdiff"
  * $this->obj_params -- параметры метода, почерпнутые из url, например array( 7, 12 )
  * $this->obj_path   -- массив из хэш-массивов, соответствующим объектам надлежащих инфопространств
                         это могут быть надкаталоги в обычном сайте или журнал в Нпж, например.

=============================================================== v.4.1npj (Kuso)
*/
define( "STATE_USE"   , 0 );
define( "STATE_IGNORE", 1 );

class RequestHandler
{
  /*** objects ***/
  var $dbal;
  var $db;
  var $url;
  var $obj_class, $obj_name, $obj_path, $obj_method, $obj_params;
  var $tpl;
  var $state;
  var $debug;
  var $principal;

  var $css = array();
  var $javascripts = array();
  var $javascripts_inline = array();
  var $javascripts_onload = array();

  function RequestHandler( $config_path="config/default.php" )
  {
    $this->site_dir = "";

    // вставляем конфиг
    if(!@is_readable($config_path)) die("Cannot read local configuration.");
    require($config_path);

    // автоматизируем некоторые параметры конфига
    $this->base_host = $_SERVER["HTTP_HOST"];
    $this->base_host_prot = $this->scheme."://".$_SERVER["HTTP_HOST"]; 
    $this->base_full = $this->scheme."://".$this->base_host."/".$this->base_url;
    $this->base_dir = $_SERVER["DOCUMENT_ROOT"]."/".$this->base_url;
    $this->qs = $_SERVER["QUERY_STRING"];

    // избавляемся от долбанных квотов. Все говорят, что они долбанные, и это так и есть.
    if (get_magic_quotes_gpc()){
      $this->_FuckQuotes($_POST);
      $this->_FuckQuotes($_GET);
      $this->_FuckQuotes($_COOKIE);
      $this->_FuckQuotes($_REQUEST);
    }    

    // основные модули
    $this->UseClass( "Debug", $this->core_dir );
    if ($this->db_al != "none")
     $this->UseClass( "DBAL", $this->core_dir, "DBAL_".$this->db_al );
    $this->UseClass( "StateSet", $this->core_dir );
    $this->UseClass( "Principal", $this->core_dir );
    $this->UseClass( "TemplateEngine", $this->core_dir );
    $this->UseClass( "ObjectCache", $this->core_dir );

    // вспомогательные модули

    // инициализация 
    $this->debug = &new Debug( $this->halt_level, $this->debug_to_file );
    $this->debug->Milestone( "DBAL started." );
    if ($this->db_al != "none")
    {
     $this->dbal = &new DBAL( &$this, $this->db_al_type );
     $this->debug->Milestone( "DBAL created -- !!! sic! how long" );
     $this->db = &$this->dbal->conn;
    }
    else
    {
      $this->dbal = &$this->debug;
      $this->db = &$this->debug;
      $this->debug->Milestone( "no DBAL is created " );
    }
    $this->tpl = &new TemplateEngine( &$this );
    $this->cache = &new ObjectCache( &$this );

    $this->debug->Milestone( "RH::Constructor done. Workspace built" );

  }

  // вывод в поток вывода, точно
  function Output( $what )
  {
    echo trim($what); 
  }

  // деструктор, завершает работу
  function End()
  {
    if ($this->db_al != "none")
     $this->dbal->Close();
    if (($this->debug_level < 0) || ($this->debug->IsError($this->debug_level)))
    {
      $this->debug->Milestone( "RH->Close()" );
      $this->debug->Flush();
    } 
    exit;
  }

  // обработчик запроса определённого пользователя к определённому url НА САЙТЕ
  function HandleRequest( $url=NULL, $state=NULL, $principal=NULL )
  {
    $this->debug->Milestone( "RH::_Preprocessing State, than Url..." );

    $this->_PreprocessUrl( $this->_PreprocessState( $url, &$state ) );

    $this->debug->Milestone( "RH::_Preprocessing Principal" );

    if (!$principal) $principal = &$this->_PreprocessPrincipal();
    if (!$principal) $this->debug->Error( "Principal absent" );
    $this->principal = &$principal;

    $this->debug->Milestone( "RH::_HandleRequest" );
    return $this->_HandleRequest();
  }

  // подключение файла с классом "на лету"
  function UseClass($class_name, $class_dir="", $file_name="" )
  {
    if(!class_exists($class_name))
    {
      if ($file_name == "") $file_name=$class_name;
      if ($class_dir == "") $class_dir=$this->classes_dir;
      $class_file = $class_dir.$file_name.".php";

      if (!@is_readable($class_file)) 
      if ($this->debug)
       $this->debug->Error("Cannot load class ".$class_name."  from ". $class_file, 4);
      else
       die("Cannot load class ".$class_name."  from ". $class_file. " (".$class_dir.")");
      else require_once($class_file);
    } 
  }

  // added 11.11.2003 by kukutz
  function UseLib($library_name, $library_dir="", $file_name="", $die_on_error=1)
  {
    if(!class_exists($library_name))
    {
      if ($file_name    == "") $file_name = $library_name;
      if ($library_dir == "")  $library_dir = $library_name;
      $class_file = $this->libraries_dir.$library_dir."/".$file_name.".php";

      if (!@is_readable($class_file)) 
       if (!$die_on_error) return false;
      else
        if ($this->debug)
         $this->debug->Error("Cannot load library ".$library_name."  from ". $class_file, 4);
        else
         die("Cannot load library ".$library_name."  from ". $class_file. " (".$library_dir.")");
        else require_once($class_file);
    } 
    return true;
  }

  // "магические" квоты
  function _FuckQuotes(&$a)
  {
   if(is_array($a))
    foreach($a as $k => $v)
     if(is_array($v)) $this->_FuckQuotes($a[$k]);
                 else $a[$k] = stripslashes($v);
  }
  // просто тупой редирект по урлу, не проверяя, нормальный он или относительный может быть.
  // относительные направлять как $rh->Redirect($rh->Href( $rel_url, 0|1 ) )
  function Redirect( $abs_url="", $ignore_state=1 )
  {
    if ($abs_url == "") $abs_url = "/".$this->base_url.$this->url; // ??? refactor -- trim "/"
    if ($abs_url[0] == "?") $abs_url = "/".$this->base_url.$this->url.$abs_url; // ??? refactor -- trim "/"
    $abs_url = preg_replace( "/^\/\//", "/", $abs_url);

    if ((substr($abs_url, 0, 7) == "http://") ||
        (substr($abs_url, 0, 8) == "https://"))
    { /* do nothing */ }
    else
    {
      // для локальных ссылок, если PHPSESSID пришёл постом или гетом, надо его доклеить к урлу
      if (isset($_GET[session_name()]) || isset($_POST[session_name()]))
      {
        $qpos = strpos( $abs_url, "?" );
        if ($qpos === false) $abs_url.="?"; else $abs_url.="&"; 
        $abs_url.=session_name()."=".session_id();
      }

      $abs_url = $this->base_host_prot.$abs_url;
    }

    header("Location: $abs_url"); 
    exit;
  }


  // ==================================================================================
  // избавляемся от возможно отсутствующего Rewrite mode !!!! нужна помощь.
  function _PreprocessState( $url=NULL, $state=NULL )
  {
    // !!!! надо помочь разобраться, что написать здесь, чтобы всё прозрачно работало.
    // * $this->state может принимать $q="&" в реврайт-моде

    if ($this->rss)
    {
      $url = $this->rss->url;
      $query404 = $this->rss->query;
    }
    else if ($this->rewrite_mode == 2 && $_SERVER["REQUEST_METHOD"]!="POST" 
        && strpos($_SERVER["REQUEST_URI"],"/".$this->base_url)===0) 
    {
      $url = substr($_SERVER["REQUEST_URI"], strlen("/".$this->base_url));
      if (strpos($url,"?")!==false) 
      {
        $_url = explode("?", $url);
        $url = $_url[0];
      }
      $query404 = $_url[1];
    }

    if (!$state) 
    {
      $this->state = &new StateSet( &$this, "?", "&" );
      $this->state->Load( $_POST ); // POST first
      $this->state->LoadWeak( $_GET ); // GET second
      if ($query404) $this->state->Unpack($query404);
      $this->state->Free("page"); // free "page", from where we receive nisht.
    }
    else $this->state = &$state;

// !!!! GET without _-fields
    if ($query404) $_GET = $this->state->values;

    if (!$url) $url = $_REQUEST["page"];
    $this->url = $url;

    return $this->url;
  }
  function Href( $rel_url, $ignore_state=0 ) // !!! код толком не отлажен
  {
    if ((substr($rel_url, 0, 7) == "http://") ||
        (substr($rel_url, 0, 8) == "https://"))
    {
      if ($ignore_state) $qs = "";
                    else $qs = $this->state->Pack(MSS_GET, $bonus);
      return $rel_url.$qs;
    }
    $bonus = "";
    if ($this->rewrite_mode == 0)  { $bonus = "page=".$rel_url; $rel_url = "index.php"; } // "dirty urls"

    if ($ignore_state) $qs = "";
                  else $qs = $this->state->Pack(MSS_GET, $bonus);
    return "/".$this->base_url.$rel_url.$qs;
  }
  // ==================================================================================

  // абстрактные:
  function _HandleRequest() { return "abstract RH->_HandleRequest should be overriden."; }
  function _PreprocessUrl( $url ) { $this->obj_path=array(); $this->obj_class="default"; $this->obj_name=$url;
                                    $this->obj_method="default"; $this->obj_params=array(); }
  function &_PreprocessPrincipal( $as="guest" ) 
                                  { $p = &new Principal(&$this); 
                                    if (!$p->Identify()) { $p->AssignByLogin($as); $p->Store(); }
                                    return $p; 
                                  }

  // -----------------------------------------------------------
  // [not yet] complicated Link
  // ForR1 -- $text = "Вот это <A>ссылка</A>"
  function Link( $href, $text="", $title="", $bonus="", $parse=1, $ignore_state=0 )
  {
    if ($text === "") $text = $href;

    if ($parse) 
    {

      // parsing for icon
      $icon="";// !!! сделать определение внешних ссылок, ссылок на email
           if (preg_match("/^(http|https|ftp):\/\/([^\s\"<>]+)$/", $href)) 
           {
            $icon=$this->tpl->message_set["IconForeignLink"]; // http://... link
             $title.= $this->tpl->message_set["ForeignLink"];
           }
      else if (preg_match("/^(mailto:)?[a-z0-9_\-\.]+\@[a-z0-9_\-]+\.[a-z0-9_\-\.]+$/i", $href, $matches))         
      {
            if ($matches[1] != "mailto:") $href= "mailto:".$href;
            $title.= $this->tpl->message_set["MailtoLink"];
            $icon=$this->tpl->message_set["IconMailto"]; // mailto:... link
      }
      else $href = $this->Href($href, $ignore_state);

      if ($href == $this->Href($this->url, 0)) return "<span class=\"current\">$text</span>"; // если ссылка приведёт нас туда же, ага

    } else $href = $this->Href($href, $ignore_state);
    return "<a href='".$href."' title='".$title."' ".$bonus.">".$icon.$text."</a>";
  }

// EOC{ RequestHandler } 
}



?>