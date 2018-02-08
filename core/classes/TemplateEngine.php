<?php
/*
    TemplateEngine( &$config, $domain=array() )   -- ����������� ������ ��������
      - $config -- ������ ((RequestHandler)) � ������������� �������
      - $domain -- ������������ ��������, ��������� ���� �� ���� ����� ����������� ����� ����������
      - � ��������� �� ����������� ��� �� ((StateSet)), ����� �������� ������������� ������� � �������������, 
        ��������� � ���, ��� ������� ������������
      - ??? � ���������� �������� ������ � ��������� ��� ������

  ---------
  * LoadTpl( $tpl_name, $only_cached=0 ) -- ��������� �������� �� �����
      - $tpl_name    -- ��� ��������, �������� "front.html:Menu"
      - $only_cached -- ���� ��������������� ���� ��������, �� LoadTpl �� �������� �������������� ������ � ���������� ������ �����

  * _CacheTpl( $tpl_name, $content ) -- ��� ����������� �����������, �������� ������������ ��������
      - $tpl_name    -- ��� ��������, �������� "front.html:Menu"
      - $content     -- ���������� ��������

  * LoadTplMap( $file_names ) -- ��������� ���� �������� �����. � �������� ��� �������
      - $file_names -- ������ � ������� ������ ��������

  * LoadDomain( $domain, $reset=0 ) -- ��������� �����(�����) ����������
      - $domain -- ����������� ���-������
      - $reset  -- ���� ���������� ����, �� ���������� ������ ������, ����� ��� merge

  * Assign( $key, $value=1 ) -- ���������� �������� ����������(����) � ������
      - $key   -- ��� ���������� (����)
      - $value -- ��������
  * _Assign( $key, &$value) -- ���������� �������� ���� �������
  * Append( $key, $value ) -- �������� � ����� �������� ������������� ���� � ������

  * &GetValue( $key ) -- �������� ������ �� ���������� ������
      - $key   -- ��� ���������� (����)

  * Reset( $key="" ) -- ������� ������ ��� unset ����������
      - $key   -- ��� ���������� (����), ���� ������, �� ��������� ���� �����

  * Parse( $tpl_name, $store_to="", $append=0, $dummy="" ) -- ������� ������� � ����������� ����������. ���������� ������-���������
      - $tpl_name    -- ��� ��������, �������� "front.html:Menu"
      - $store_to    -- ���� �����������, �� ��������� ����� ����������� � ���������� ������ � ����� ������
      - $append      -- ���� �������� $store_to, �� ��������� �� ������� �������� ����������, � ������������ � �����

  * _Parse( $content, $dummy="" ) -- ��� ����������� �����������. ������� ������, ���������� ������-���������
      - $content -- ���������� �������, ������� � ��������
      - $dummy -- ������, �� ������� �������� ����� ����� �����, ������� ����������� � ������

  * &_Custom( $name ) -- ����� "�����������" ����������� �� tpl_functions/$name.php. ���� ������ ��� ���, ������������ ������� ������� $name
      - $name -- ��� ����� ��� ".php", ������� ����� � tpl_functions/
      - ������� "����������" ������������ ������������ � ���������������� �����

  * &Format( $what, $formatter="wiki", $store_to=NULL, $append=0, $options="" ) -- ����� ���������� (�������������� ������ ����������� �������), 
                                                                      ���������� ����������������� �����
      - $what        -- �����, ������� ����� ���������������
      - $formatter   -- ����� ���������. ��� ����� ��� ".php", ������� ����� � formatters/
      - $store_to    -- ���� �����������, �� ��������� ����� ����������� � ���������� ������ � ����� ������
      - $append      -- ���� �������� $store_to, �� ��������� �� ������� �������� ����������, � ������������ � �����
  * &FormatConvert( $what, $from="wacko", $to="rawhtml" ) -- ������� �� ������ ������� � ������

  * MergeMessageSet( $messageset_name, $messageset_dir="" ) -- ��������� ��� message_set (��������� ��������� ���� �� ������ ������)
      - $messageset_name -- ��� ������ � �����. ��������, �������� "ru_forms"
      - $messageset_dir  -- ������� � ������ ������������ ($rh->...)

  // ������������ ���
  * Skin( $theme="" ) -- "������ ���� ���� ������ ������", ����� ����� ������ � ������ "�����"
  * Unskin()          -- ������ � ������ "����� ������� ����"

  // ��� ������������� � ancient formatters (������ � ForR4)
  * GetConfigValue( $key ) -- ���������� $this->config->$key; ��� ������������� � wacko_*
  * Link( $tag, $method="", $desc ) -- ���� ��� ������������� � wacko_*
  * !!! ���������, ��� ��� ����� ������?

  // ����� ������������ properties
  * $this->message_set -- ���-������, ���������� �������������� ����� ��������� (i18n)
  * $this->theme       -- ������� ����

=============================================================== v.9 (Kuso)
*/
define("TPL_APPEND", 1 );

class TemplateEngine
{
  var $prefix, $postfix, $justfix, $markup_level;
  var $tpl_path, $cache_path;
  var $domain;
  var $templates;
  var $magic;
  var $config;
  var $skip_tag = false;
  var $message_set, $theme;
  var $theme_stack = array(""); var $theme_depth = 0;
  var $_total_time =0; // ???(DBG)
  var $_total_time_load =0; // ???(DBG)
  var $_total_time_c =0; // ???(DBG)
  var $_formatter_time =0; // ???(DBG)
  var $_formatter_time_c =0; // ???(DBG)

  function TemplateEngine( &$config, $domain=array() )  
  {
    $this->config = &$config;
    $this->domain = $domain;

    $this->prefix  = $this->config->tpl_prefix;
    $this->postfix = $this->config->tpl_postfix;
    $this->_prefix  = preg_quote($this->prefix);
    $this->_postfix = preg_quote($this->postfix);
    $this->justfix = $this->config->tpl_justfix;
    $this->markup_level = $this->config->tpl_markup_level;
    $this->tpl_path = $this->config->templates_dir;
    $this->cache_path = $this->config->templates_cache_dir;
    $this->magic = $this->config->tpl_magic;

    if (isset($this->config->theme))
    {
      $this->theme_stack[++$this->theme_depth] = $this->config->theme;
      $this->theme_path = $this->config->themes_dir;
      $this->config->debug->Trace("TemplateEngine::Build -> ".$this->theme." (".$this->theme_path.")" );
    } else 
    $this->theme = $this->theme_stack[$this->theme_depth];
    $this->theme_no_slashes = $this->_Deslash( $this->theme );

    if ($this->config->default_theme)
    { 
      $this->default_theme = $this->config->default_theme;
      $this->default_theme_no_slashes = $this->_Deslash( $this->default_theme );
    }

    if (isset($this->config->messagesets_dir))
    {
      $__fullfilename = $this->config->messagesets_dir.$this->config->message_set.".php";
      if (!file_exists($__fullfilename)) $this->config->debug->Trace("TemplateEngine: message_set <b>'{$this->config->message_set}.php'</b> not found.");
      include($__fullfilename);
      $__fullfilename = $this->config->messagesets_dir."all.php";
      if (!file_exists($__fullfilename)) $this->config->debug->Trace("TemplateEngine: global message_set <b>'all.php'</b> not found.");
      include($__fullfilename);
    }

    $this->templates = array();
  }

  function _Deslash( $theme )
  {
    $theme_no_slashes = str_replace( "/", "_", $theme );
    $theme_no_slashes = str_replace( ".", "_", $theme_no_slashes );
    return $theme_no_slashes;
  }
  // ��������������� ������
  function Skin( $theme = "" )
  {
    $this->theme_stack[++$this->theme_depth] = $theme;
    $this->theme = $this->theme_stack[$this->theme_depth];
    if ($this->theme != "")
      $this->theme_path = $this->config->themes_dir;
    else
      $this->theme_path = $this->config->site_dir;
    $this->config->debug->Trace("TemplateEngine::Skin -> ".$this->theme." (".$this->theme_path.")" );
    $this->theme_no_slashes = $this->_Deslash( $this->theme );
  }
  function Unskin()
  {
    $this->theme_depth--;
    $this->theme = $this->theme_stack[$this->theme_depth];
    if ($this->theme != "")
      $this->theme_path = $this->config->themes_dir;
    else
      $this->theme_path = $this->config->site_dir;
    $this->config->debug->Trace("TemplateEngine::Unskin -> ".$this->theme." (".$this->theme_path.")" );
    $this->theme_no_slashes = $this->_Deslash( $this->theme );
  }

  // ��������� message set
  function MergeMessageSet( $messageset_name, $messageset_dir="" )
  {
    $dir = $this->config->messagesets_dir;
    if ($messageset_dir != "") $dir = $messageset_dir;
    if (isset($dir))
    {
      $_ms = $this->message_set;

      $__fullfilename = $dir.$messageset_name.".php";
      if (!file_exists($__fullfilename)) $this->config->debug->Trace("TemplateEngine: message_set <b>'{$messageset_name}.php'</b> not found.");
      else include($__fullfilename);
      foreach ($this->message_set as $k=>$v)
        $_ms[ $k ] = $v;
      $this->message_set = &$_ms;
    } else $this->config->debug->Trace("�� ���������� rh->messagesets_dir, ����� ������ message sets!");
  }

  // ��������� � ������������ ������ �� �����
  function LoadTpl( $tpl_name, $only_cached=0, $strict_theme=NULL, $strict_no_slashes=NULL )
  {
    // ������ ������
    if (gettype($tpl_name) != "string") $this->config->debug->Error( "TPL: argument for LoadTpl() must be a string.");

    // �� ��������� ������
    if ($tpl_name[0] == "@") $tpl_name = substr( $tpl_name, 1 );
    if (isset($this->templates[$tpl_name])) return;

    if ($strict_theme == NULL) $strict_theme = $this->theme;
    if ($strict_no_slashes == NULL) $strict_no_slashes = $this->theme_no_slashes;
    $default_tpl_path = $this->theme_path.$this->default_theme."/".$this->tpl_path;
    // ��������� ���
    if ($this->theme != "") 
    {
      $tpl_path = $this->theme_path.$strict_theme."/".$this->tpl_path;
    }
    else
    {
      $tpl_path = $this->theme_path.$this->tpl_path;
    }

    // ���������, ���� �� ������������� ��������
    $arr = explode( ":", $tpl_name ); 
    if (is_array($this->magic)) 
    { 
       $tpl_name = "@".$tpl_name; $_arr0 = "@".$arr[0]; 
    } 
    else 
    { 
      $_arr0 = $arr[0]; 
    }
    $tpl_name_no_slashes = strtr($tpl_name, "/:", "_.");
    $no_slashes          = $strict_no_slashes;

    $f=0;
    $theme_default = NULL;
    do
    {
      $_file_cached = $this->cache_path.$no_slashes.$tpl_name_no_slashes; 
      $_file_original = $tpl_path.$arr[0];

      //$this->config->debug->Trace( "cached -> ".$_file_cached);
      //$this->config->debug->Trace( "original -> ".$_file_original);

    if ( (!$this->config->tpl_no_cache || $only_cached)
           && file_exists($_file_cached) && 
           (!file_exists($_file_original) || (filemtime($_file_cached) >= filemtime($_file_original))) )
    { // ������� ����� ��������
      $result = implode("",file($_file_cached)); 
      $this->templates[$tpl_name]=$result; 
      return $result; 
    } 

      if (!file_exists($tpl_path.$arr[0]) && $this->default_theme) 
      {
        $tpl_path = $default_tpl_path;
        $no_slashes = $this->default_theme_no_slashes;
        $theme_default = $this->default_theme_no_slashes;
        $theme_default_strict = $this->default_theme;
        //$this->config->debug->Trace( "deeper -> ".$this->default_theme_no_slashes);
      }

      $f++;
    } while (!file_exists($tpl_path.$arr[0]) && $this->default_theme && ($f < 2));

    $this->config->debug->Trace( "TPL->LoadTpl($tpl_name) parses this template" );
    // ����� ������� � ���������, ���.
    if( !file_exists($tpl_path.$arr[0]) ) 
      $this->config->debug->Error( "TPL: can't read template file <b>".$tpl_path.$arr[0]."</b>");
    else
    {
      //$this->config->debug->Trace( "get file-> ".$tpl_path.$arr[0]);
      $data = implode("",file($tpl_path.$arr[0])); 

      if (preg_match( "/".$this->_prefix."\/TEMPLATE".$this->_postfix."/si", $data, $matches))
        $this->config->debug->Error("TPL->: [MOO DUCK ALERT] {{/TEMPLATE}} found!", 5);
      if (preg_match( "/".$this->_prefix."\/\?(.*?)".$this->_postfix."/si", $data, $matches))
        $this->config->debug->Error("TPL->: [MOO DUCK ALERT] {{/?".$matches[1]."}} found -- change to {{?/".$matches[1]."}}!", 5);

      if ($this->markup_level == 0)
        $data = preg_replace("/\s*<!--.*?-->\s*/ims", "", $data);

      $stack     = array( $data );
      $stackname = array( $_arr0 );
      $stackpos = 0;
      while ($stackpos < sizeof($stack) )
      { 
        $data = $stack[$stackpos];

        // �������� ��������� ��������� (���� ���� ��� �� � �������� ��������, 
        // ���� � ��� ����� ����� � ������ ������� ����������, ��� � .NET)
        $c =preg_match_all( "/".$this->_prefix."TEMPLATE:([A-Za\.-z0-9_]+)".$this->_postfix."(.*?)".
                            $this->_prefix."\/TEMPLATE:\\1".$this->_postfix."/si",                     
                            $data, $matches, PREG_SET_ORDER  );
        foreach( $matches as $match )
        {
          $match[1] = $_arr0.":".$match[1];
          $data = str_replace( $match[0], $this->prefix.$match[1].$this->postfix,    $data );
    
          $stack[] = $match[2];
          $stackname[] = $match[1];
        } 

        // �������� ��������� (������ ����������� � �������� ���������� �����!!... 
        $this->_CacheTpl( $stackname[$stackpos], $data, $theme_default );
        $this->_CacheTpl( $stackname[$stackpos], $data, $this->theme_no_slashes );
        $stackpos++;
      }

      // ���������� ��������� ������ ���������. ������ �� ����� � ����
            if ($only_cached>1) return $this->config->debug->Error("TPL->Miss: $theme_default <b>$tpl_name</b>: <br />".
              "file_exists: ".file_exists($_file_cached). "<br />".
              "dated: ".filemtime($_file_cached)." >= ".filemtime($_file_original)."<br />".
              "tpl_no_cache=".$this->config->tpl_no_cache);


      return $this->LoadTpl( $tpl_name, $only_cached+1, $theme_default_strict, $theme_default );
    }

  }

  // ������ ������������ ������. ����� �� ������� ����� �����������.
  function _CacheTpl( $tpl_name, $content, $custom_theme_name=NULL )
  {
    // ��������� ��� ��� �����������
    if (isset($custom_theme_name))
      $tpl_name = $custom_theme_name.$tpl_name;
    else
      $tpl_name = $this->theme_no_slashes.$tpl_name;

    $this->config->debug->Trace( "TPL->CacheTpl($tpl_name)" );
    // ������ ����� ��������� �� {{var}} ����������
    $content = str_replace( $this->postfix, $this->justfix, $content );
    $content = str_replace( $this->prefix,  $this->justfix, $content );

    // ����� ����� � � ���� ���������
    if (file_exists($this->cache_path.strtr($tpl_name,"/:","_.") ) && !is_writable( $this->cache_path.strtr($tpl_name,"/:","_.") ) )
     $this->config->debug->Error( "No access to: ". $this->cache_path.strtr($tpl_name,"/:","_.") );

    if (!file_exists( $this->cache_path.strtr($tpl_name,"/:","_.")) && 
        !is_writable( preg_replace("/\/[^\/]*$/","",$this->cache_path) ))
     $this->config->debug->Error( "No access to entire dir: ". $this->cache_path.strtr($tpl_name,"/:","_.") );
    
    $fp = fopen( $this->cache_path.strtr($tpl_name,"/:","_.") ,"w");
    fputs($fp,$content);
    fclose($fp);
  }

  // ��������� ����� ���� ��������, ������ �������� �����, ��� ��� �����������
  function LoadTplMap( $file_names )
  {
    if( gettype($file_names) != "array" ) $this->LoadTpl($file_names);
    else for ($i=0; $i<count($file_names); $i++) $this->LoadTpl($file_names[$i]);
  }


  // ��������� � ����� ����������. ���� $reset=1, �� ����� ��������� ����� ��������� 
  function LoadDomain( $domain, $reset=0 )
  {
    if ($reset) $this->Reset();
    $this->domain = array_merge( (array)$this->domain, (array)$domain );
  }

  // ��������� ���������� ��������
  function Append( $key, $value )
  { 
    $this->domain[ $key ] .= $value; 
  }
  function Assign( $key, $value=1 )
  { 
    if (gettype($key) == "array") return $this->LoadDomain( $key, 1 );
    $this->domain[ $key ] = $value; 
  }
  function _Assign( $key, &$value )
  { 
    if (gettype($key) == "array") return $this->LoadDomain( $key, 1 );
    $this->domain[ $key ] = &$value; 
  }

  // �������� ��������
  function &GetValue( $key )
  { 
    if (isset($this->domain[$key])) return $this->domain[$key];
    else { $this->config->debug->Trace( "TPL->GetValue: domain key <b>$key</b> not found."); return false; }
  }

  // ������ ������� ������, ��� ������ ������ ��� ����� 
  function Reset( $key="" ) 
  {
    if ($key) unset( $this->domain[$key] );
    else $this->domain = array();
  }

  // ������ ������ ��������������� ������� -- ������ ������ � ��������� ������ � ���������� �������� ����� 
  function Parse( $tpl_name, $store_to="", $append=0, $dummy="" ) 
  {
     $this->_total_time_c++; // ???(DBG)
     $m1 = $this->config->debug->_getmicrotime(); // ???(DBG)
     // $this->config->debug->Trace( "TPL->Parse($tpl_name) begins { ".$this->theme." }" );

      switch( $this->markup_level ){ // !!! ��� �� ����������, ������ ��� ������������ ��� �������� � ������������ �������
        case 1:
          $mark = "\n<!-- TEMPLATE: ".$tpl_name." -->\n";
          $_mark = "\n<!-- / TEMPLATE: ".$tpl_name." -->\n";
        break;
        case 2:
          $mark = "\n<b>TEMPLATE: ".$tpl_name."</b>\n";
          $_mark = "\n<b> / TEMPLATE: ".$tpl_name."</b>\n";
        break;
        default:
          $mark = $_mark = "";
        break;
      }

     $m11 = $this->config->debug->_getmicrotime(); // ???(DBG)
      $tpl = &$this->LoadTpl( $tpl_name );
     $m12 = $this->config->debug->_getmicrotime(); // ???(DBG)
     $data = $mark.$this->_Parse( $tpl, $dummy ).$_mark;
     if ($store_to) 
      if ($append) $this->domain[ $store_to ].= $data;
      else         $this->domain[ $store_to ] = $data;

     $m2 = $this->config->debug->_getmicrotime(); // ???(DBG)
     $this->_total_time_c--; // ???(DBG)
     if ($this->_total_time_c == 0) // ???(DBG)
     { // ???(DBG)
       $this->_total_time+= $m2-$m1; // ???(DBG)
       $this->_total_time_load+= $m12-$m11; // ???(DBG)
     } // ???(DBG)

     return $data;
  }

  // ���������� ��������� �������
  function _Parse( $content, $dummy="" )
  {
    // 1. explode
    $pieces = explode( $this->justfix, $content );
    ob_start();
    if (sizeof($pieces)%2 == 0) 
    { $this->config->debug->Error("TPL->_Parse: somewhere missed closing templating pseudotag", 0); $pieces[] = ""; }
    // 2. cycle thru
    $s = sizeof($pieces);
    for ($i=0; $i<$s; $i++)
    {
      if ($i%2)
      if (isset($this->domain[ $pieces[$i] ])) $pieces[$i] = $this->domain[ $pieces[$i] ];
      else
      {
        if (is_array($this->magic) && isset($this->magic[ $pieces[$i][0] ]))
        {
          $method = $this->magic[ $pieces[$i][0] ];
          if ($method == "i18n") $pieces[$i] = $this->message_set[substr($pieces[$i],1)]; 
          else
          if ($this->skip_tag)
           if (substr($method,0,4) == "skip") $pieces[$i] = $this->Format( substr($pieces[$i],1), $method ); else;
          else
          {
            if ($method == "default") $pieces[$i] = $this->Parse( $pieces[$i] ); 
            else
            if ($method == "custom")  $pieces[$i] = $this->_Custom( $pieces[$i] );
            else $pieces[$i] = $this->Format( substr($pieces[$i],1), $method );
          }
        } else 
        {
          if ($dummy) $pieces[$i]=$dummy; else
          if ($this->config->tpl_markup_level == 0) $pieces[$i]="";
        }
      }
      if (!$this->skip_tag) echo $pieces[$i];
    }
    // 3. implode & return
    $result = ob_get_contents();
    ob_end_clean();
    return $result;

  }

  // ����� ������������ "�����������" �����������
  function &_Custom( $name )
  {
    $_name = substr($name,1);
    $__dir = $this->config->templates_magic_dir;
    $__fullfilename_ = $__dir.$_name.".php";

    $target_theme = $this->theme;
    $f=0;
    do
    {
      // $this->config->debug->Trace( "custom -> ". $target_theme);
      // ��������� ���
      if ($target_theme != "") 
        $__fullfilename = $this->theme_path.$target_theme."/".$__fullfilename_;
      else
        $__fullfilename = $this->theme_path.$__fullfilename_;

      if (!file_exists($__fullfilename) && $this->default_theme)
        $target_theme = $this->default_theme;

      $f++;
    } while (!file_exists($__fullfilename) && $this->default_theme && ($f < 2));

    $this->config->debug->Trace("Custom template handler: ".$__fullfilename);
    if (!file_exists($__fullfilename)) return $this->Parse( $_name );

    $state     = &$this->config->state;
    $rh        = &$this->config;
    $cache     = &$this->config->cache;
    $tpl       = &$this->config->tpl;
    $db        = &$this->config->db;
    $debug     = &$this->config->debug;
    $object    = &$this->config->object;

    ob_start();
    include($__fullfilename);
    $output = ob_get_contents();
    if ($output===false) $debug->Error("Problems (file: ".__FILE__.", line: ".__LINE__."): ".ob_get_contents());
    ob_end_clean();
    return $output;
  }

  function &FormatConvert( $what, $from="wacko", $to="rawhtml" )
  {
    if ($from == $to) return $what;
    else return $this->Format( $what, "convert/".$from."2".$to );
  }
  // ����� ����������
  function &Format( $what, $formatter="wiki", $store_to=NULL, $append=0, $options="" )
  {
     $this->_formatter_time_c++; // ???(DBG)
     $m1 = $this->config->debug->_getmicrotime(); // ???(DBG)

    $__fullfilename = $this->config->formatters_dir.$formatter.".php";
    $this->config->debug->Trace("Formatter: ".$__fullfilename);
    if (!file_exists($__fullfilename)) 
      return "no such formatter '".$formatter."'";

    $state     = &$this->config->state;
    $rh        = &$this->config;
    $cache     = &$this->config->cache;
    $tpl       = &$this->config->tpl;
    $db        = &$this->config->db;
    $debug     = &$this->config->debug;
    $object    = &$this->config->object;
    $text      = &$what;

    ob_start();
    include($__fullfilename);
    $output = ob_get_contents();
    if ($output===false) $debug->Error("Problems (file: ".__FILE__.", line: ".__LINE__."): ".ob_get_contents());
    ob_end_clean();

    if ($store_to) 
     if ($append) $this->domain[ $store_to ].= $output;
     else         $this->domain[ $store_to ] = $output;

     $this->_formatter_time_c--; // ???(DBG)
     $m2 = $this->config->debug->_getmicrotime(); // ???(DBG)
     if ($this->_formatter_time_c == 0) // ???(DBG)
      $this->_formatter_time+= $m2-$m1; // ???(DBG)

    return $output;
  }

  // ��� ������������� �� ������ �����-�����������
  function GetConfigValue( $key )
  { 
    $a = get_object_vars ( $this->config );
    return $a[$key]; 
  }
  function Link( $tag, $method="", $desc ) 
  { if (!$desc) $desc = $tag;
    return $this->config->Link( $tag, $desc );
  }

// EOC{ TemplateEngine } 
}



?>
