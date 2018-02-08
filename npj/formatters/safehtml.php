<?php

 define("XML_HTMLSAX3", $rh->libraries_dir."HTMLSax3/");
 $rh->UseLib("safehtml");

 $safehtml =& new safehtml();
 echo ($safehtml->parse($text));

?>