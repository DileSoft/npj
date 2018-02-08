<?php

define("PREREGEXP", "/(\"\".*?\"\"|::(\S)?::)/sm");

    
if (!class_exists("preformatter"))
{
class preformatter {

  var $object;

  var $macroses = array(
     ":::::"=>"UserColon",
     "::::" =>"User",
     "::@::"=>"UserDate",
     "::+::"=>"Date",
     "::!::"=>"FullUserName",
  );

  function preformatter( &$object )
  { 
    $this->object = &$object; 
  }


  function precallback($things)
  {
    $wacko = &$this->object;

    $thing = $things[1];

    if (preg_match("/^\"\"(.*)\"\"$/s", $thing, $matches))
    {                                    
      return "\"\"".$matches[1]."\"\"";
    }
    else if (isset($this->macroses[$thing]))
    {                                    
      $_t = $wacko->rh->tpl->theme;
      $wacko->rh->tpl->theme = $wacko->rh->theme;
      $wacko->rh->tpl->Assign("_Account", $wacko->rh->principal->data["login"]."@".$wacko->rh->principal->data["node_id"]);
      $wacko->rh->tpl->Assign("_Date", date("d.m.Y H:i"));
      $result = $wacko->rh->tpl->Parse("macros.wacko:".$this->macroses[$thing]);
      $wacko->rh->tpl->theme = $_t;
      return $result;
    }
    return $thing;
  }
}
}

$parser = new preformatter(&$object);

$text = preg_replace_callback(PREREGEXP, array( &$parser, "precallback"), $text);

print($text);



?>