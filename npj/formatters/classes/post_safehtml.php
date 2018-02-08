<?php

class post_safehtml {
  var $object;
  function post_safehtml( &$object, &$options )
  { 
    $this->object  = &$object; 
    $this->options =  &$options;
    if ($this->options["feed"]) 
      $this->actions = explode(", ", ACTIONS4FEED);
  }

  function postcallback($things)
  {
    $thing = $things[1];
    $wacko = &$this->object;
    
    // forced links ((link link == desc desc))
    if (preg_match("/^\xA2\xA2([^\n]+)==([^\n]*)\xAF\xAF$/", $thing, $matches))
    {
/*
      list (, $url, $text) = $matches;
      if ($url)
      {
        $url = str_replace(" ", "", $url);
        $text=trim(preg_replace("/\xA4\xA4|__|\[\[|\(\(/","",$text));
        return $wacko->Link($url, ($this->options["feed"]?"no404":""), $text);
      }
      else
        return "";
*/
    }
    // actions
    else if (preg_match("/^<action ([^>]*?)>$/s", $thing, $matches))
    {
      // разборка на параметры
      $p = " ".$matches[1]." ";
      $paramcount = preg_match_all( "/(([^\s=]+)(\=((\"(.*?)\")|([^\"\s]+)))?)\s/", $p, 
                                    $matches, PREG_SET_ORDER );
      $params = array();  $c=0;
      foreach( $matches as $m )
      {
        $value = $m[3]?($m[5]?$m[6]:$m[7]):"1";
        $params[$c] = $value;
        $params[ $m[2] ] = $value;
        $c++;
      }

      $action = $params["name"];

      if ($action && (!$this->options["feed"] || in_array(strtolower($action),$this->actions)))
      {
        if ($action==$params[0])
          $params[0]=$params[1];
        return $wacko->Action($action, &$params, &$this->object->rh->principal);
      }
      else if ($this->options["feed"]) 
        return "";
      else
        return $thing;
    }
    // if we reach this point, it must have been an accident.
    return $thing;
  }
}

?>