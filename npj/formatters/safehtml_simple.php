<?php

$rh->UseClass("SafeHtml_simple", $rh->formatters_classes_dir);

$text = str_replace("\xa5", "", $text);
$text = preg_replace("/<(\/?)(i|u|a|b|s|sup|sub|em|tt|strong)>/i","\xa5$1$2\xa6", $text);
$text = preg_replace("/<a[^>]*(href\=[^> ]*)[^>]*>/ie","'\xa5a '.SafeHtml_simple::SafeHref('$1').'\xa6'", $text);

$text = strip_tags($text);

$text = str_replace("\xa5", "<", $text);
$text = str_replace("\xa6", ">", $text);

echo $text;
?>
