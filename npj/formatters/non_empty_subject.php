<?php

  /// Этот форматтер пытается создать "заголовок" на базе "текста"
  /// пока что он просто тримает таги и возвращает результат.

  $text = strip_tags($text);
//  $text = $tpl->Format($text, "html2text");
  $text = substr($text,0,200);
  $text = preg_replace( "/[\s\n]+/", " ", $text );
  $text = preg_replace( "/(.{100,200}[(\.!?;])$/", "$1", $text );
  $text = preg_replace( "/(.)[&\.!?;,\(].*?$/", "$1", $text );

//  if ($debug->kuso) $debug->Error( $text );

  echo $text." &rarr;";

?>