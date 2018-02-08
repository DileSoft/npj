<?php
class post_wacko {
  var $object;
  function post_wacko( &$object, &$options )
  { 
    $this->object  = &$object; 
    $this->options =  &$options;
    if ($this->options["diff"]) $this->options["feed"] = 1;
      $this->actions = explode(", ", ACTIONS4FEED);
  }

  function postcallback($things)
  {
    $thing = $things[1];

    $wacko = &$this->object;

    $debug = &$this->object->rh->debug;
    
    // forced links ((link link == desc desc))
    if (preg_match("/^\xA2\xA2([^\n]+)==([^\n]*)\xAF\xAF$/", $thing, $matches))
    {
      list (, $url, $text) = $matches;
      if ($url)
      {
        $url = str_replace(" ", "", $url);
        $text=trim(preg_replace("/\xA4\xA4|__|\[\[|\(\(/","",$text));
        return $wacko->Link($url, &$this->options, $text);
      }
      else
        return "";
    }
    // actions
    else if (preg_match("/^\xA1\xA1\s*([^\n]+?)\xA1\xA1$/s", $thing, $matches))
    {
      if ($matches[1] && (!$this->options["feed"] || in_array(strtolower($matches[1]),$this->actions)))
      {
      // check for action' parameters 
        $sep = strpos( $matches[1], " " );
        if ($sep === false)
        {
          $action = $matches[1];
          $params = array();
        }
        else
        {
          $action = substr( $matches[1], 0, $sep );
          $p = " ".substr( $matches[1], $sep )." ";
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
        }
        return $wacko->Action($action, &$params, &$this->object->rh->principal);
      }
      else if ($this->options["diff"]) 
        return "{{".$matches[1]."}}";
      else if ($this->options["feed"]) 
        return "";
      else
        return "{{}}";
    }
    // if we reach this point, it must have been an accident.
    return $thing;
  }
}

?>