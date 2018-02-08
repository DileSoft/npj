<?php

$rh->UseClass("post_safehtml", $rh->formatters_classes_dir);

$parser = new post_safehtml( &$object, &$options );

$text = preg_replace_callback("/(<action [^>]*?>)/sm", array( &$parser, "postcallback"), $text);

echo($text);
?>