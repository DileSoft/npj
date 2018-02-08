<?php
//!!! много простору для оптимизации :-)

$text = preg_replace ('/ {2, }/', ' ', $text);


$trans = array (
        "\r" => '', 
        "\n" => '<br />',
        '|' => '&#124;'
         );

$text = strtr ($text, $trans);
if (!$options["no<p>"])
  $text = str_replace ('<br /><br />', '<p>', $text );

include($rh->formatters_dir."rawhtml.php");
//echo $this->Format($text, "rawhtml");

?>