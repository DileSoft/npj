<?php
class SafeHtml_simple {
  var $object;
  function SafeHtml_simple( &$object, &$options )
  { 
    $this->object  = &$object; 
    $this->options =  &$options;
  }

  function SafeHref($arg)
  {
   $arg = preg_replace("/j\s*a\s*v\s*a\s*s\s*c\s*r\s*i\s*p\s*t\s*:/i", "", $arg);
   $arg = preg_replace("/v\s*b\s*s\s*c\s*r\s*i\s*p\s*t\s*:/i", "", $arg);
   $arg = preg_replace("/a\s*b\s*o\s*u\s*t\s*:/i", "", $arg);
   return stripslashes($arg);
  }
}


?>
