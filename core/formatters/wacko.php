<?php

// This may look a bit strange, but all possible formatting tags have to be in a single regular expression for this to work correctly. Yup!

define("LONGREGEXP",
"/(\%\%.*?\%\%|".
"(~?)(?<=[^\.".ALPHANUM_P."]|^)(((\.\.|!)?\/)?".UPPER.LOWER."+".UPPERNUM.ALPHANUM."*)\b|".
"\"\".*?\"\"|".($this->GetConfigValue("allow_rawhtml")==1?"\<\#.*?\#\>|":"").
"\[\[(\S+?)([ \t]+([^\n]+?))?\]\]|\(\((\S+?)([ \t]+([^\n]+?))?\)\)|".    // #1
"\/\/([^\n]*?".
 "(".
 "\[\[(\S+?)([ \t]+([^\n]+?))?\]\]|\(\((\S+?)([ \t]+([^\n]+?))?\)\)".    // = #1
 ")".
 "[^\n]*?)+\/\/|".
"\^\^\S*?\^\^|vv\S*?vv|".
">>.*?<<|".
"\b[a-z]+:\/\/\S+|\?\?\S\?\?|\?\?(\S.*?\S)\?\?|".
"\*\*[^\n]*?\*\*|\#\#[^\n]*?\#\#|\'\'.*?\'\'|\!\!\S\!\!|\!\!(\S.*?\S)\!\!|__[^\n]*?__|".
"中\S中|ㄒ\Sㄒ|中(\S.*?\S)中|ㄒ(\S.*?\S)ㄒ|\#\|\||\#\||\|\|\#|\|\#|\|\|.*?\|\||".
"<|>|\/\/[^\n]*?\/\/|".
/*
\/\/[^\n]*?(?<!(\(\[){2}[a-z]{3}:)(?<!(\(\[){2}[a-z]{4}:)(?<!(\(\[){2}[a-z]{5}:)\/\/
*/
"\n[ \t]*=======.*?=======|\n[ \t]*======.*?======|\n[ \t]*=====.*?=====|\n[ \t]*====.*?====|\n[ \t]*===.*?===|\n[ \t]*==.*?==|".
"----|---|--\S--|--(\S.*?[^- \t\n\r])--|".
"\n(\t+|([ ]{2})+)(-|\*|[0-9,a-z,A-Z]+[\.\)])?|".
"\{\{.*?\}\}|".
"\b[A-Z][A-Z,a-z]+[:](".ALPHANUM."*)\b|".
"\n)/sm");

    

if (!class_exists("wacko")) {
class Wacko
{
  var $object;
  var $oldIndentLevel = 0;
  var $indentClosers = array();
  var $tdoldIndentLevel = 0;
  var $tdindentClosers = array();
  var $br = 1;
  var $intable = 0;
  var $cols = 0;

  function wacko( &$object )
  { 
    $this->object = &$object; 
  }

  function indentClose() 
  {
   if ($this->intable) $Closers = &$this->tdindentClosers;
   else $Closers = &$this->indentClosers;
   $c = count($Closers);
   for ($i = 0; $i < $c; $i++)
     $result .= array_pop($Closers);
   if ($this->intable) $this->tdoldIndentLevel = 0;
   else $this->oldIndentLevel = 0;
   return $result;
  }

  function wacko2callback($things)
  {
    $thing = $things[1];

    $wacko = &$this->object;
    $callback = array( &$this, "wacko2callback");
    
    // convert HTML thingies
    if ($thing == "<")
      return "&lt;";
    else if ($thing == ">")
      return "&gt;";
    // escaped text
    else if (preg_match("/^\"\"(.*)\"\"$/s", $thing, $matches))
    {                                    
      return "<!--notypo-->".str_replace("\n","<br />",htmlspecialchars($matches[1]))."<!--/notypo-->";
    }
    // escaped html
    else if (preg_match("/^\<\#(.*)\#\>$/s", $thing, $matches))
    {                                    
      return "<!--notypo-->".$matches[1]."<!--/notypo-->";
    }
    // code text
    else if (preg_match("/^\%\%(.*)\%\%$/s", $thing, $matches))
    {
      // check if a language has been specified
      $code = $matches[1];
      if (preg_match("/^\((.+?)\)(.*)$/s", $code, $matches))
      {
        list(, $language, $code) = $matches;
      }
      switch ($language)
      {
      case "php":
        $formatter = "php";
        break;
      case "noautolinks":
        $formatter = "noautolinks";
        break;
      default:
        $formatter = "code";
      }

      //$output = "<div class=\"code\">";
      $output .= $wacko->Format(trim($code), $formatter);
      //$output .= "</div>";

      return $output;
    }
    //table begin
    else if ($thing == "#||")
    {
      $this->br = 0;
      $this->cols = 0;
      $this->intable = true;
      return $result."<table class=\"dtable\" border=\"0\">";
    }
    //table end
    else if ($thing == "||#")
    {
      $this->br = 0;
      $this->intable = false;
      return "</table>";
    }
    else if ($thing == "#|")
    {
      $this->br = 0;
      $this->cols = 0;
      $this->intable = true;
      return $result."<table class=\"usertable\" border=\"1\">";
    }
    //table end
    else if ($thing == "|#")
    {
      $this->br = 0;
      $this->intable = false;
      return "</table>";
    }
    //
    else if (preg_match("/\|\|(.*?)\|\|/s", $thing, $matches))
    {
      $this->br = 0;
      

      $output = "<tr class=\"userrow\">";
      $cells = split("\|", $matches[1]);
      $count = count($cells);
      $count--;
      
      for ($i=0; $i<$count;$i++)
      {
        $this->tdoldIndentLevel = 0;
        $this->tdindentClosers = array();
        $output .= "<td class=\"usercell\">".preg_replace_callback(LONGREGEXP, $callback, $cells[$i]);
        $output .= $this->indentClose();
        $output .= "</td>";
      }
      if (($this->cols <> 0) and ($count<$this->cols))
      {
        $this->tdoldIndentLevel = 0;
        $this->tdindentClosers = array();
        $output .= "<td class=\"usercell\" colspan=".($this->cols-$count+1).">".preg_replace_callback(LONGREGEXP, $callback, $cells[$count]);
        $output .= $this->indentClose();
        $output .= "</td>";
      }
      else
      { 
        $this->tdoldIndentLevel = 0;
        $this->tdindentClosers = array();
        $output .= "<td  class=\"usercell\">".preg_replace_callback(LONGREGEXP, $callback, $cells[$count]);
        $output .= $this->indentClose();
        $output .= "</td>";
      }
      $output .= "</tr>";
      
      if ($this->cols == 0)
      {
        $this->cols = $count;
      }
      return $output;
    }
    // Deleted 
    else if (preg_match("/中((\S.*?\S)|(\S))中/s", $thing, $matches))
    {
      $this->br = 0;
      return "<span class=\"del\">".preg_replace_callback(LONGREGEXP, $callback, $matches[1])."</span>";
    }
    // Inserted
    else if (preg_match("/ㄒ((\S.*?\S)|(\S))ㄒ/s", $thing, $matches))
    {
      $this->br = 0;
      return "<span class=\"add\">".preg_replace_callback(LONGREGEXP, $callback, $matches[1])."</span>";
    }
    // bold
    else if (preg_match("/^\*\*(.*?)\*\*$/", $thing, $matches))
    {
      return "<strong>".preg_replace_callback(LONGREGEXP, $callback, $matches[1])."</strong>";
    }
    // italic
    else if (preg_match("/^\/\/(.*?)\/\/$/", $thing, $matches))
    {
      return "<em>".preg_replace_callback(LONGREGEXP, $callback, $matches[1])."</em>";
    }
    // underlinue
    else if (preg_match("/^__(.*?)__$/", $thing, $matches))
    {
      return "<u>".preg_replace_callback(LONGREGEXP, $callback, $matches[1])."</u>";
    }
    // monospace
    else if (preg_match("/^\#\#(.*?)\#\#$/", $thing, $matches))
    {
      return "<tt>".preg_replace_callback(LONGREGEXP, $callback, $matches[1])."</tt>";
    }
    // strike
    else if (preg_match("/^--((\S.*?\S)|(\S))--$/s", $thing, $matches))    //NB: wrong
    {
      return "<s>".preg_replace_callback(LONGREGEXP, $callback, $matches[1])."</s>";
    }
    // cite
    else if (preg_match("/\'\'(.*?)\'\'/s", $thing, $matches) ||
    preg_match("/\!\!((\S.*?\S)|(\S))\!\!/s", $thing, $matches))
    {
      $this->br = 1;
      return "<span class=\"cite\">".preg_replace_callback(LONGREGEXP, $callback, $matches[1])."</span>";
    }
    else if (preg_match("/\?\?((\S.*?\S)|(\S))\?\?/s", $thing, $matches))
    {
      $this->br = 1;
      return "<span class=\"mark\">".preg_replace_callback(LONGREGEXP, $callback, $matches[1])."</span>";
    }
    // urls
    else if (preg_match("/^([a-z]+:\/\/\S+?)([^[:alnum:]^\/])?$/", $thing, $matches)) {
      $url = $matches[1];
      if (substr($url,-4)==".jpg" || substr($url,-4)==".gif" || substr($url,-4)==".png" || substr($url,-4)==".jpe"
      || substr($url,-5)==".jpeg") return "<img src=\"$url\">".$matches[2];
      else return $wacko->PreLink($url).$matches[2];
    }
    // centered
    else if (preg_match("/^>>(.*)<<$/", $thing, $matches))
    {
      return "<div class=\"center\">".preg_replace_callback(LONGREGEXP, $callback, $matches[1])."</div>";
    }
    // super
    else if (preg_match("/^\^\^(.*)\^\^$/", $thing, $matches))
    {
      return "<sup>".preg_replace_callback(LONGREGEXP, $callback, $matches[1])."</sup>";
    }
    // sub
    else if (preg_match("/^vv(.*)vv$/", $thing, $matches))
    {
      return "<sub>".preg_replace_callback(LONGREGEXP, $callback, $matches[1])."</sub>";
    }
    // headers
    else if (preg_match("/\n[ \t]*=======(.*)=======$/", $thing, $matches))
    {
      $result = $this->indentClose();
      $this->br = 0; $wacko->headerCount++;
      return $result."<a name=\"TOC_".$wacko->tocRecursionTag.$wacko->headerCount."\"></a><h6>".$matches[1]."</h6>";
    }
    else if (preg_match("/\n[ \t]*======(.*)======$/", $thing, $matches))
    {
      $result = $this->indentClose();
      $this->br = 0; $wacko->headerCount++;
      return $result."<a name=\"TOC_".$wacko->tocRecursionTag.$wacko->headerCount."\"></a><h5>".$matches[1]."</h5>";
    }
    else if (preg_match("/\n[ \t]*=====(.*)=====$/", $thing, $matches))
    {
      $result = $this->indentClose();
      $this->br = 0; $wacko->headerCount++;
      return $result."<a name=\"TOC_".$wacko->tocRecursionTag.$wacko->headerCount."\"></a><h4>".$matches[1]."</h4>";
    }
    else if (preg_match("/\n[ \t]*====(.*)====$/", $thing, $matches))
    {
      $result = $this->indentClose();
      $this->br = 0; $wacko->headerCount++;
      return $result."<a name=\"TOC_".$wacko->tocRecursionTag.$wacko->headerCount."\"></a><h3>".$matches[1]."</h3>";
    }
    else if (preg_match("/\n[ \t]*===(.*)===$/", $thing, $matches))
    {
      $result = $this->indentClose();
      $this->br = 0; $wacko->headerCount++;
      return $result."<a name=\"TOC_".$wacko->tocRecursionTag.$wacko->headerCount."\"></a><h2>".$matches[1]."</h2>";
    }
    else if (preg_match("/\n[ \t]*==(.*)==$/", $thing, $matches))
    {
      $result = $this->indentClose();
      $this->br = 0; $wacko->headerCount++;
      return $result."<a name=\"TOC_".$wacko->tocRecursionTag.$wacko->headerCount."\"></a><h1>".$matches[1]."</h1>";
    }
    // separators
    else if ($thing == "----")
    {
      // TODO: This could probably be improved for situations where someone puts text on the same line as a separator.
      //       Which is a stupid thing to do anyway! HAW HAW! Ahem.
      $this->br = 0;
      return "<hr noshade size=\"1\" />";
    }
    // forced line breaks
    else if ($thing == "---")
    {
      return "<br />";
    }
    // forced links ((link link == desc desc))
    else if ((preg_match("/^\[\[(.+)(==|\|)(.*)\]\]$/", $thing, $matches)) || 
             (preg_match("/^\(\((.+)(==|\|)(.*)\)\)$/", $thing, $matches)) )
    {
      list (, $url, ,$text) = $matches;
      if ($url)
      {
        if ($url!=($url=(preg_replace("/中|__||\[\[|\(\(/","",$url)))) $result="</span>";
        if ($text == "") $text = $url;
        $url = str_replace( " ", "", $url );
        $text=preg_replace("/中|__|\[\[|\(\(/","",$text);
        return $result.$wacko->PreLink($url, $text);
      }
      else
      {
        return "";
      }
    }
    // forced links
    else if ((preg_match("/^\[\[(\S+)(\s+(.+))?\]\]$/", $thing, $matches)) ||
             (preg_match("/^\(\((\S+)(\s+(.+))?\)\)$/", $thing, $matches)))
    {
      list (, $url, , $text) = $matches;
      if ($url)
      {
        if ($url!=($url=(preg_replace("/中|ㄒ||\[\[|\(\(/","",$url))))$result="</span>";
        if (!$text) $text = $url;
        $text=preg_replace("/中|ㄒ|\[\[|\(\(/","",$text);
        return $result.$wacko->PreLink($url, $text);
      }
      else
      {
        return "";
      }
    }
    // indented text
  else if (preg_match("/(\n)(\t+|(?:[ ]{2})+)(-|\*|([0-9,a-z,A-Z]+)[\.\)])?(\n|$)/s", $thing, $matches))
    {
      // new line
      $result .= ($this->br ? "<br />\n" : "\n");
      //intable or not?
      if ($this->intable) 
      {
       $Closers = &$this->tdindentClosers;
       $oldlevel = &$this->tdoldIndentLevel;
      }
      else
      {
       $Closers = &$this->indentClosers;
       $oldlevel = &$this->oldIndentLevel;
      }

      // we definitely want no line break in this one.
      $this->br = 0;

      // find out which indent type we want
      $newIndentType = $matches[3][0];
      if (!$newIndentType) { $opener = "<div class=\"indent\">"; $closer = "</div>"; $this->br = 1; }
      else if ($newIndentType == "-" || $newIndentType == "*") { $opener = "<ul><li>"; $closer = "</li></ul>"; $li = 1; }
      else { $opener = "<ol type=\"".$newIndentType."\"><li>"; $closer = "</li></ol>"; $li = 1; }

      // get new indent level
      if ($matches[2][0]==" ") 
       $newIndentLevel = (int) (strlen($matches[2])/2);
      else 
      $newIndentLevel = strlen($matches[2]);
      if ($newIndentLevel > $oldlevel)
      {
        for ($i = 0; $i < $newIndentLevel - $oldlevel; $i++)
        {
          $result .= $opener;
          array_push($Closers, $closer);
        }
      }
      else if ($newIndentLevel < $oldlevel)
      {
        for ($i = 0; $i < $oldlevel - $newIndentLevel; $i++)
        {
          $result .= array_pop($Closers);
        }
      }

      $oldlevel = $newIndentLevel;

      if ($li && !preg_match("/".str_replace(")", "\)", $opener)."$/", $result))
      {
        $result .= "</li><li>";
      }

      return $result;
    }
    // new lines
    else if ($thing == "\n" && !$this->intable)
    {
      // if we got here, there was no tab in the next line; this means that we can close all open indents.
      $result = $this->indentClose();
      if ($result) $this->br = 0;

      $result .= $this->br ? "<br />\n" : "\n";
      $this->br = 1;
      return $result;
    }
    // events
    else if (preg_match("/^\{\{(.*?)\}\}$/s", $thing, $matches))
    {
      return "﹛".$matches[1]."﹛";
    }
    // interwiki links!
    else if (preg_match("/^[A-Z][A-Z,a-z]+[:](".ALPHANUM."*)$/s", $thing))
    {
      return $wacko->PreLink($thing);
    }
    // wacko links!
    else if ((!$wacko->_formatter_noautolinks) &&
             (preg_match("/^(((\.\.)|!)?\/?|~)?(".UPPER.LOWER."+".UPPERNUM.ALPHANUM."*)$/s", $thing, $matches)))
    {
      if ($matches[1]=="~")
       return $matches[4];
      return $wacko->PreLink($thing); 
    }
    // if we reach this point, it must have been an accident.
    return $thing;
  }
}
}

$text = str_replace("\r", "", $text);
$text = "\177\n".$text."\n";

$parser = new Wacko( &$object );

$text = preg_replace_callback(LONGREGEXP, array( &$parser, "wacko2callback"), $text);
$text = str_replace("\177"."<br />\n","",$text);
$text = str_replace("\177"."","",$text);

// we're cutting the last <br>
$text = preg_replace("/<br \/>$/", "", $text);//trim($text));

print($text);


?>