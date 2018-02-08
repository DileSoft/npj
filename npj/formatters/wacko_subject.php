<?php


$rh->UseClass("SimpleWackoFormatter", $rh->formatters_classes_dir);

$text = str_replace("\r", "", $text);

$parser = &new SimpleWackoFormatter( &$object );

$text = preg_replace_callback($parser->LONGREGEXP, array( &$parser, "wacko2callback"), $text);

$text = preg_replace("/<br \/>$/", "", $text);

print($text);


?>