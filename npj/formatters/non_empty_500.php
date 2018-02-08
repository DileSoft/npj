<?php

  /// Этот форматтер пытается создать "краткое ревью" на базе "текста"
  /// пока что он просто тримает таги и возвращает результат.

  $text = strip_tags($text);
//  $text = $tpl->Format($text, "html2text");
  $text = substr($text,0,500);
  $text = preg_replace( "/[\s\n]+/", " ", $text );
  $text = preg_replace( "/(.{100,150}[(\.!?;])$/", "$1", $text );
  $text = preg_replace( "/(.+)[\.!&?;,\(].*?$/", "$1", $text );
  $text = preg_replace( "/(.+)&[a-z0-9#]*?$/", "$1", $text );

//  if ($debug->kuso) $debug->Error( $text );

  echo $text." &rarr;";

?>