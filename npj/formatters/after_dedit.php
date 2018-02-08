<?php
/*$i = strpos($text,"<BODY>");                                                                               
$j = strpos($text,"</BODY>");                                                                              
echo substr($text,($i+6),($j-$i-6));
*/
if (preg_match("/\<body[^\>]*\>(.*)\<\/body\>/is",$text,$matches))
 echo $matches[1];
else 
 echo $text;
?>