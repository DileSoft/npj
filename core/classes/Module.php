<?php
/*
    Module( &$rh, $base_href, $message_set, $section_id=0, $handlers_dir="", $messageset_dir="" ) -- Абстрактный модуль
      - $message_set -- какой присоединить набор с сообщениями для вывода?
      - $section_id -- идентификатор гигантского раздела сайта (не группы внутри модуля)
      - $handlers_dir, $messageset_dir -- в замену стандартным из $rh->..

  ---------
  - Handle( $rel_url ) -- весь рабочий цикл в одном вызове
  - Error( $msg )      -- отметиться, что ошибка
  - isError()          -- true, если где-то был вызван Error

  - Init( $rel_url )   -- понять, в каком состоянии мы пришли сюда, найти обработчик
  - Handler( $handler, $params, &$principal ) -- осуществить обработку одного из handlers, распихав результат по:
      - Preparsed:TITLE
      - Preparsed:CONTENT
      - Preparsed:ERROR -- заполняется в случае ошибки
  - Action( $handler, $params, &$principal )  -- специальные обработчики-сервисы, возвращающие результат строкой

  - &GenerateTemplateEngine( $te_profile ) -- сделать новый TE по профилю.
      - $te_profile -- содержит новые "themes_dir", "skins", "skin", "cache_prefix"

  // Свойства модуля
  * message_set
  * section_id
  * handlers_dir -- заимствуется из $rh->handlers_dir, если что -- можно перезадать

  // Настройки модуля
  * method -- на какой метод настроен модуль при ините
  * params[...]
  - pageno, pagesize -- ненулевые значения включают листалку

=============================================================== v.2 (NikolaiIaremko)
*/

class Module
{
  var $section_id = 0;
  var $message_set = "std";
  var $handlers_dir;
  var $module_name = "abstract";

  function Module( &$rh, $base_href, $message_set="empty", $section_id=0, $handlers_dir="", $messageset_dir="" )
  {
    $this->rh = &$rh;
    $this->tpl = &$rh->tpl;
    $this->message_set = $message_set;
    $this->messagesets_dir = $messageset_dir;
    $this->section_id  = $section_id;
    $this->base_href = $base_href;
    if ($handlers_dir == "") $this->handlers_dir = $rh->handlers_dir;
    else                     $this->handlers_dir = $handlers_dir;

    $rh->tpl->MergeMessageSet( $message_set, $messageset_dir );
  }

  function Error( $msg ) 
  { 
    $this->tpl->Assign( "Preparsed:ERROR", $msg ); 
    $this->rh->debug->Error( "Module *".$this->module_name."*: $msg" );
  }
  function isError()     { return ( $this->tpl->GetValue("Preparsed:ERROR")? true:false ); }

  function Handle( $rel_url )
  {
    $this->Init( $rel_url );
    if (!$this->tpl->GetValue("Preparsed:ERROR"))
     return $this->Handler( $this->method, &$this->params, &$this->rh->principal );
  }

  function Init( $rel_url )
  {
    $this->method = "abstract";
    $this->params = array();
  }

  function TplDomain()
  { }
  function Handler( $method, $params, &$principal )
  { 
    $this->TplDomain();
    $method = strtolower($method);
    $__fullfilename = $this->handlers_dir.$this->handlers_prefix.$method.".php";
    if (!file_exists($__fullfilename)) // если всё ещё не угадали, то нам поплохело...
     return $this->Error("UnknownHandler &mdash; ".$__fullfilename);
    return $this->IncludeBuffered( &$principal, $this->handlers_dir.$this->handlers_prefix, $method, $params ); 
  } 
  function Action( $method, $params, &$principal )
  { 
    $method = strtolower($method);
    $value = $this->IncludeBuffered( &$principal, $this->handlers_dir.$this->handlers_prefix, "action_".$method, $params );
    $this->rh->tpl->Assign("Action:CONTENT", $value);
    return $value;
  }

  // ИНКЛУДЫ ХАНДЛЕРОВ И ПРОЧИХ. ------------------------------------------------
  // общий способ проводить инклуды
  function IncludeBuffered( &$principal, $dir, $script_name, $params="" )
  {
    $state     = &$this->rh->state;
    $rh        = &$this->rh;
    $cache     = &$this->rh->cache;
    $tpl       = &$this->rh->tpl;
    $db        = &$this->rh->db;
    $debug     = &$this->rh->debug;
    $object    = &$this;

    $__fullfilename = $dir.$script_name.".php";
    $this->rh->debug->Trace("Launching handler: ".$__fullfilename);
    if (!file_exists($__fullfilename)) 
    {
      $this->rh->debug->Trace("Unknown method handler! (file: ".__FILE__.", line: ".__LINE__.")");
      $__fullfilename = $dir."/_404.php";
      if (!file_exists($__fullfilename)) 
        $this->rh->debug->Error("404 method handler not supplied! (file: ".__FILE__.", line: ".__LINE__.")", 3);
    }

    ob_start();
    $_somedata = include($__fullfilename);
    if ($_somedata===false) $this->rh->debug->Error("Problems (file: ".__FILE__.", line: ".__LINE__."): ".ob_get_contents());
    if (!$_somedata) $_somedata = ob_get_contents(); 
    ob_end_clean();

    return $_somedata;
  }

  function &GenerateTemplateEngine( $te_profile )
  {
    if (isset($this->template_engine)) return $this->template_engine;

    $tpl = &new TemplateEngine( &$this->rh );

    $theme = $this->rh->tpl->theme;
    if (!in_array($theme, $te_profile["skins"]))
     $theme = $te_profile["skin"];

    $tpl->theme = $theme;
    $tpl->theme_stack[$tpl->theme_depth-1] = $theme;
    $tpl->theme_path = $te_profile["themes_dir"];
    $this->rh->debug->Trace("TemplateEngine::REBUILD -> ".$tpl->theme." (".$tpl->theme_path.")" );

    $tpl->cache_path .= $te_profile["cache_prefix"];

    $tpl->Assign("Module:/", $this->rh->Href( $this->base_href."/", STATE_IGNORE ) );

    $this->template_engine = &$tpl;
    return $tpl;
  }


// EOC { Module }
}


?>