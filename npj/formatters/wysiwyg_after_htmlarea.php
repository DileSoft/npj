<?php

if (preg_match("/\<body[^\>]*\>(.*)\<\/body\>/is",$text,$matches))
 echo $matches[1];
else 
 echo $text;

?>