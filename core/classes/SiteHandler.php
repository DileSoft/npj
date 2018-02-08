<?php

class SiteHandler extends RequestHandler
{
  function _HandleRequest() 
  { 
    $this->UseClass( "FormatterWysiwyg", $this->core_dir );
    $this->UseClass( "SiteWysiwyg" );

    $this->UseClass("Helper", $this->core_dir );
    $this->helper = &new Helper( &$this);

    // конфигураци€ сайта
    $a = $this->helper->Db( "select * from ".$this->db_prefix."config where id=1" );
    $this->configuration = $a[0];
    $this->admin_mail = $this->configuration["admin_email"];

    // дл€ показа и предобработки картинок из любого места
    $this->UseClass("Module", $this->core_dir);
    $this->UseClass("SiteModule");
    $this->UseClass("SiteModule_pictures");
    $this->pictures = &new SiteModule_pictures( &$this, "picture", $this->pictures_settings );
    $this->pictures->min_width = 0;
    $this->pictures->title_height = 0;
    $this->pictures->border_width = 0;

    // подписка
    if (!$this->notify)
    {
      $this->UseClass("Module",       $this->core_dir);
      $this->UseClass("ModuleNotify", $this->core_dir);
      $this->notify = &new ModuleNotify( &$this, "notify" );
    }

    $this->tpl->LoadDomain($this->GetConfigDomain());

    // јдминска€ панель
    if ($this->principal->IsGrantedTo("noguests"))
    {
      $this->tpl->Assign("PrincipalName", $this->principal->data["user_name"] );
      $this->tpl->Parse("_/auth.html", "AUTH.PANEL");
    }
    else 
    {
      $this->tpl->Assign("AUTH.PANEL", "");
      if ( $this->principal->state == 0 )
        $this->principal->Login(1, "guest", "");
    }

    if ($_GET["print"])  $this->tpl->Assign("PrintVersion", 1);

    $this->Handle( $this->handler ); 

    $tpl = &$this->tpl; 
    if ($tpl->GetValue("html_body")) ; else
    if ($this->objectpath == "")
     $tpl->Parse( "homepage.html", "html_body" );
    else
     $tpl->Parse( "inner.html:Body", "html_body" );


    $data = $this->tpl->GetValue("HTML");
    if (!$data) 
    if ($this->print_version && $_GET["print"])  $data = $this->tpl->Parse("html_print.html");
    else                                         $data = $this->tpl->Parse( "html.html" );
    return $data;
  }

  // подключение стандартного, не модульного хандлера
  function Handle($handler, $return=0)
  {
    $rh    = &$this;
    $state = &$this->state;
    $tpl   = &$this->tpl;
    $db    = &$this->db;
    $cache = &$this->cache;
    $debug = &$this->debug;
    $principal = &$this->principal;

    $rh->UseClass("ListSimple",  $rh->core_dir);
    $rh->UseClass("ListCurrent", $rh->core_dir);
    $rh->UseClass("ListObject",  $rh->core_dir);
    $rh->UseClass("ListRotator", $rh->core_dir);
    $rh->UseClass("Module",      $rh->core_dir);

    ob_start();
      $__fullfilename = $this->handlers_dir.$handler.".php";
      $this->debug->Trace("Handler: ".$__fullfilename);
      if (!file_exists($__fullfilename))
       if ($handler != "404") 
        return $this->Handle( "404", $return );  else 
        $this->debug->Error("No handler for <b>".$handler."</b> !", 3 );
      $result = include($__fullfilename);
    $contents = ob_get_contents();
    ob_end_clean;
    if ($return) return $result;
    return $contents;
  }


  function _PreprocessUrl( $url ) 
  { 
    $this->handler = "404";
    $url = trim( $url, "/" );

    // распознавание английской версии
    if (strpos($url, "eng") === 0) $this->Redirect( $this->Href("en/".substr($url,4), STATE_IGNORE) );
    if (strpos($url, "en")  === 0) 
    { $this->en = true; $this->lang="en";  $this->tpl->Assign("EN", 1);
      $this->menu = $this->en_menu; 
      $this->project_name = $this->en_project_name;
      $this->tpl->MergeMessageset( "en" );
    }

    if ($url == "") 
    {
      $this->objectpath  = "";
      $this->paramstring = "";
      $this->handler = $this->handler_default;
      $this->params = array();
    }
    else
    {
      foreach ( $this->handler_map as $key => $value )
       if (strpos( $url, $key ) === 0) 
        $this->objectpath  = $key;

      if ($this->objectpath)
      {
        $this->paramstring = substr( $url, strlen($this->objectpath)+1 );
        $this->handler = $this->handler_map[ $this->objectpath ];
        if ($this->paramstring == "")
         $this->params = array();
        else
         $this->params = explode("/", $this->paramstring);
      }
    }

    $this->url  = $url;
    $this->_url = "/".$url."/";
  }

  // заполн€ем домен значени€ми по-умолчанию
  function GetConfigDomain()
  {
    $a = array(
    
    "M:cms"          => ($this->cms_url && $this->helper->roles["editor"]),
    "M:cms/"         => "/".$this->cms_url,
    "M:cms.Show"     => $this->helper->hide_edit?0:1,
    "M:cms.Hidden"   => $this->helper->hide_hidden?0:1,

    "css"          => $this->css_dir,
    "js"           => $this->js_dir,
    "images"       => $this->images_dir,
    "project_name" => $this->project_name,
    "/"            => "/".$this->base_url,
    "CurrentURL"   => "/".$this->base_url.$this->url,
    "IsPortal"     => "",
    "TopDir"       => $this->top_dir,
    "SubDir"       => $this->sub_dir,
    "AdminMail"    => $this->admin_mail,
    "FormSearch"   => '<FORM ID="searchForm" NAME="searchForm" METHOD="get" ACTION="/cgi-bin/yandsearch">',
    "/Form"        => "</FORM>",

    "NoFlash"      => $_SESSION["flash_mode"],
              );
    return $a;
  }

  function &_PreprocessPrincipal( $as="guest" ) 
  { 
    $this->UseClass( "DbPrincipal", $this->core_dir );
    $this->UseClass( "SitePrincipal" );
    $p = &new SitePrincipal(&$this); 
//    $this->debug->Error_R( $_SESSION[$this->cookie_prefix."user"] );

    if (!$p->Identify( 0 )) 
    { 
      $this->debug->Trace("loggin in as $as");
      $p->AssignByLogin($as); $p->Store(); 
    }

    $this->debug->Trace("CmsRH->_PrePrincipal: identified as: <b>".$p->data["login"]."</b> { ".$p->data["user_name"]." }");

    return $p; 
  }

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
      htcCycle(&$this->db, $this->db_prefix."htcron");
    }
    $this->dbal->Close();
    exit;
  }

  
}

?>