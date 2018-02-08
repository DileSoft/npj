<?php

  /// Этот форматтер пытается создать "краткое HTML-ревью" на базе "HTML"
  /// чуть более интеллектуальным способом, нежели тримы тагов.

  /* Метод работы:
     1. отрезать первые N байтов HTML
     2. если внутри попалась таблица, отрезать всё до её начала
       * повторять, пока таблицы не кончатся =)
     3. если таблиц не попадалось -- отрезать до последнего встретившегося </p>
     
  */

  $maxsize  = $options["default"];
  $supertag = $options["supertag"];

  $original_size = strlen($text);

  $text = substr( $text, 0, $maxsize );

  $firsts = array( "<table",  );
  $lasts  = array( "p" => ">p\/<", "ul" => ">lu\/<", "ol" => ">lo\/<" );

  foreach( $firsts as $k=>$v )
    if (preg_match("/".$v."/i", $text))
      $text = preg_replace( "/".$v.".*$/is", "", $text);

  $variants = array();
  $min  = $text;
  $max  = "";
  $textlen = strlen($text);
  $textflip = strrev($text);
  foreach( $lasts as $k=>$v )
  {
    $variants[$k] = preg_replace( "/^.*?(".$v.")/si", "$1", $textflip);
    $len = strlen($variants[$k]);
    if (strlen($min) > $len)
      $min = $variants[$k];
    if ((strlen($max) < $len) && ($textlen > $len))
      $max = $variants[$k];
    //$debug->Trace( $variants[$k] );
  }

  $max = strrev($max);
  $text = $this->Format( $max, "safehtml" );

    $cut_text = $object->rh->tpl->message_set["CutDefault"]." &mdash; ". 
                ceil($original_size/1024)."&nbsp;".$object->rh->tpl->message_set["Kb"];
    $object->rh->tpl->Skin( $object->rh->theme );
     $object->rh->tpl->Assign("Href:cut_text", 
            $object->Href($supertag , NPJ_ABSOLUTE, STATE_IGNORE));
     $object->rh->tpl->Assign("cut_text", $cut_text);
     $result = $object->rh->tpl->Parse("cut.html");
    $object->rh->tpl->UnSkin();
    
  echo $text.$result;

?>