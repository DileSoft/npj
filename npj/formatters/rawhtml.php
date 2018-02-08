<?php

$rh->UseClass("RawHtmlFormatter", $rh->formatters_classes_dir);

$parser = &new RawHtmlFormatter( &$object );

$text = preg_replace_callback("/(<format [^>]*?>.*?<\/format>|<a [^>]*>)/ism", array( &$parser, "process"), $text);

//$text = $this->Format($text, "safehtml");
//print($text);
include($rh->formatters_dir."safehtml.php");


?>