<?php

 $text = $this->Format($text, "after_dedit");

 $text = str_replace("\n", "", $text);
 $text = str_replace("\r", " ", $text);
 $text = str_replace("</div>", "\n", $text);

 $nohtml = preg_replace("/<br.*?>/i", "\n", 
           preg_replace("/<hr.*?>/i", "\n\r----------------------------\n\r", 
           preg_replace("/^\s+/im", "", 
           preg_replace("/\s+/i", " ", 
           str_replace('<li>', '<br>  *  ', 
           preg_replace( '/<style>.*?<\/style>/i', '', 
             $text 
           ))))));

 $nohtml = strip_tags($nohtml);
 $nohtml = strtr($nohtml, array_flip(get_html_translation_table(HTML_SPECIALCHARS))); 

 echo $nohtml;

?>