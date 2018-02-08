<?php
  // Супер-форматтер для быстрого подключения ключевых CSS-файлов
  /*
     ПОДКЛЮЧЕНИЕ:

     {{&css:news}}
     {{&js:news}}
     {{&script:template.js}}
     {{&script_:template.js}}   -- after js block
     {{&onload:template.js:Onload}}

     alternate way to use:
      * $rh->css["news"] = 1;
      * $rh->javascripts["news"] = 1;
      * $rh->javascripts_inline["template.js:Super"] = 1;
      * $rh->javascripts_inline_["template.js:Super"] = 1;
      * $rh->javascripts_onload["template.js:Onload"] = 1;

     ВЫВОД:
    
     {{&header}}
     {{&onload}}

  */

  // ------------------------------------- вывод заголовка
  if ($text == "header")
  {
    if (is_array($rh->css)) 
    foreach( $rh->css as $_=>$v )
    {
      $tpl->Assign("_", $_ );
      echo $tpl->Parse( "_/css.html" );
    }
    if (is_array($rh->javascripts_inline)) 
    foreach( $rh->javascripts_inline as $_=>$v )
    {
      $tpl->Parse( $_, "_" );
      echo $tpl->Parse( "_/js.html:Inline" );
    }
    if (is_array($rh->javascripts)) 
    foreach( $rh->javascripts as $_=>$v )
    {
      $tpl->Assign("_", $_ );
      echo $tpl->Parse( "_/js.html:File" );
    }
    if (is_array($rh->javascripts_inline_)) 
    foreach( $rh->javascripts_inline_ as $_=>$v )
    {
      $tpl->Parse( $_, "_" );
      echo $tpl->Parse( "_/js.html:Inline" );
    }
    return;
  }

  // ------------------------------------- вывод в онлоаде
  if ($text == "onload")
  {
    if (is_array($rh->javascripts_onload)) 
    foreach( $rh->javascripts_onload as $_=>$v )
     echo ";".$tpl->Parse( $_ );
    return;
  }

  // ------------------------------------- добавление ништяков

  $text = explode(":", $text);
  $text[1] = implode(":", array_slice($text,1));
  if ($text[0] == "css")
   $rh->css[ $text[1] ] = 1;
  if ($text[0] == "js")
   $rh->javascripts[ $text[1] ] = 1;
  if ($text[0] == "onload")
   $rh->javascripts_onload[ $text[1] ] = 1;
  if ($text[0] == "script")
   $rh->javascripts_inline[ $text[1] ] = 1;
  if ($text[0] == "script_")
   $rh->javascripts_inline_[ $text[1] ] = 1;

?>