<?php
//!!! ����� �������� ��� ����������� :-)

define("SMALLREGEXP", "/(\�\�(\S+?)([^\n]*?)==([^\n]*?)\�\�|\�\�[^\n]+?\�\�)/sm");

if (!class_exists("post_links"))
{
class post_links {
  var $object;
  function post_links( &$object )
  { 
    $this->object = &$object; 
  }

  function postcallback($things)
  {
    $thing = $things[1];

    $wacko = &$this->object;
    
    // forced links ((link link == desc desc))
    if (preg_match("/^\�\�([^\n]+)==([^\n]*)\�\�$/", $thing, $matches))
    {
      list (, $url, $text) = $matches;
      if ($url)
      {
        $url = str_replace(" ", "", $url);
        $text=trim(preg_replace("/��|__|\[\[|\(\(/","",$text));
        return $wacko->Link($url, "", $text);
      }
      else
      {
        return "";
      }
    }
    // actions
    else if (preg_match("/^\�\�([^\n]+?)\�\�$/s", $thing, $matches))
    {
      if ($matches[1])
        return $wacko->Action($matches[1]);
      else
        return "{{}}";
    }
    // if we reach this point, it must have been an accident.
    return $thing;
  }
}
}


$parser = new post_links( &$object);

$text = preg_replace_callback(SMALLREGEXP, array( &$parser, "postcallback"), $text);

print($text);


?>