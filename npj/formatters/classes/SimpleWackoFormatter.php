<?php
class SimpleWackoFormatter
{
  var $object;
  var $oldIndentLevel = 0;
  var $indentClosers = array();
  var $tdoldIndentLevel = 0;
  var $tdindentClosers = array();
  var $br = 1;
  var $intable = 0;
  var $intablebr = 0;
  var $cols = 0;

  function SimpleWackoFormatter( &$object )
  { 
    $this->object = &$object; 
    $this->LONGREGEXP = 
"/(".
"\"\".*?\"\"|".
"\[\[(\S+?)([ \t]+([^\n]+?))?\]\]|\(\((\S+?)([ \t]+([^\n]+?))?\)\)|".
"\/\/([^\n]*?".
 "(".
 "\[\[(\S+?)([ \t]+([^\n]+?))?\]\]|\(\((\S+?)([ \t]+([^\n]+?))?\)\)".
 ")".
 "[^\n]*?)+\/\/|".
"\^\^\S*?\^\^|vv\S*?vv|".
"\b[[:alpha:]]+:\/\/\S+|mailto\:[[:alnum:]\-\_\.]+\@[[:alnum:]\-\_\.]+|".
"\\\\\\\\[".ALPHANUM_P."\-\_\\\!\.]+|".
"\*\*[^\n]*?\*\*|\#\#[^\n]*?\#\#|__[^\n]*?__|".
"\/\/[^\n]*?\/\/|".
"<|>|--\S--|--(\S.*?[^- \t\n\r])--|".
"\b[[:alnum:]]+[:]".ALPHANUM."[".ALPHANUM_P."\-\_\.\+\&\=]+|".
"~([^ \t\"\n]+)|".
($object->GetConfigValue("disable_tikilinks")==1?"":"\b(".UPPER.LOWER.ALPHANUM."*\.".ALPHA.ALPHANUM."+)\b|").
($object->GetConfigValue("disable_wikilinks")==1?"":"(~?)(?<=[^\.".ALPHANUM_P."]|^)(((\.\.|!)?\/)?".UPPER.LOWER."+".UPPER.ALPHANUM."*)\b|").
"(~?)".ALPHANUM_L."+\@".ALPHA_L."*(?!".ALPHANUM."*\.".ALPHANUM."+)(\:".ALPHANUM."*)?|".ALPHANUM_L."+\:\:".ALPHANUM."+|".
"\n)/sm";
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
    // bold
    else if (preg_match("/^\*\*(.*?)\*\*$/", $thing, $matches))
    {
      return "<strong>".preg_replace_callback($this->LONGREGEXP, $callback, $matches[1])."</strong>";
    }
    // italic
    else if (preg_match("/^\/\/(.*?)\/\/$/", $thing, $matches))
    {
      return "<em>".preg_replace_callback($this->LONGREGEXP, $callback, $matches[1])."</em>";
    }
    // underlinue
    else if (preg_match("/^__(.*?)__$/", $thing, $matches))
    {
      return "<u>".preg_replace_callback($this->LONGREGEXP, $callback, $matches[1])."</u>";
    }
    // monospace
    else if (preg_match("/^\#\#(.*?)\#\#$/", $thing, $matches))
    {
      return "<tt>".preg_replace_callback($this->LONGREGEXP, $callback, $matches[1])."</tt>";
    }
    // urls
    else if (preg_match("/^([[:alpha:]]+:\/\/\S+?|mailto\:[[:alnum:]\-\_\.]+\@[[:alnum:]\-\.\_]+?)([^[:alnum:]^\/\-\_\=]?)$/", $thing, $matches)) {
      $url = strtolower($matches[1]);
      if (substr($url,-4)==".jpg" || substr($url,-4)==".gif" || substr($url,-4)==".png" || substr($url,-4)==".jpe"
      || substr($url,-5)==".jpeg") return "<img src=\"".$matches[1]."\" />".$matches[2];
      else return $wacko->PreLink($matches[1]).$matches[2];
    }
    // lan path
    else if (preg_match("/^(\\\\[".ALPHANUM_P."\\\!\.\-\_]+)$/", $thing, $matches)) {//[[:alnum:]\\\!\.\_\-]+\\
      return "<a href=\"".$matches[1]."\">".$matches[1]."</a>";
    }
    // super
    else if (preg_match("/^\^\^(.*)\^\^$/", $thing, $matches))
    {
      return "<sup>".preg_replace_callback($this->LONGREGEXP, $callback, $matches[1])."</sup>";
    }
    // sub
    else if (preg_match("/^vv(.*)vv$/", $thing, $matches))
    {
      return "<sub>".preg_replace_callback($this->LONGREGEXP, $callback, $matches[1])."</sub>";
    }
    // strike
    else if (preg_match("/^--((\S.*?\S)|(\S))--$/s", $thing, $matches))    //NB: wrong
    {
      return "<s>".preg_replace_callback($this->LONGREGEXP, $callback, $matches[1])."</s>";
    }
    // forced links ((link link == desc desc))
    else if ((preg_match("/^\[\[(.+)(==|\|)(.*)\]\]$/", $thing, $matches)) || 
             (preg_match("/^\(\((.+)(==|\|)(.*)\)\)$/", $thing, $matches)) )
    {
      list (, $url, ,$text) = $matches;
      if ($url)
      {
        if ($url!=($url=(preg_replace("/\xA4\xA4|__||\[\[|\(\(/","",$url)))) $result="</span>";
        if ($text == "") $text = $url;
        $url = str_replace( " ", "", $url );
        $text=preg_replace("/\xA4\xA4|__|\[\[|\(\(/","",$text);
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
        if ($url!=($url=(preg_replace("/\xA4\xA4|\xA3\xA3|\[\[|\(\(/","",$url)))) $result="</span>";
        if (!$text) $text = $url;
        $text=preg_replace("/\xA4\xA4|\xA3\xA3|\[\[|\(\(/","",$text);
        return $result.$wacko->PreLink($url, $text);
      }
      else
      {
        return "";
      }
    }
    // interwiki links
    else if (preg_match("/^([[:alnum:]]+[:]".ALPHANUM."[".ALPHANUM_P."\-\_\.\+\&\=]+?)([^[:alnum:]^\/\-\_\=]?)$/s", $thing, $matches))
    {
      return $wacko->PreLink($matches[1]).$matches[2];
    }
    // tikiwiki links
    else if ((!$wacko->_formatter_noautolinks) && $wacko->GetConfigValue("disable_tikilinks")!=1 &&
             (preg_match("/^(".UPPER.LOWER.ALPHANUM."*\.".ALPHA.ALPHANUM."+)$/s", $thing, $matches)))
    {
      return $wacko->PreLink($thing);
    }
    // npj links
    else if ((!$wacko->_formatter_noautolinks) &&
             (preg_match("/^(~?)(".ALPHANUM_L."+\@".ALPHA_L."*(\:".ALPHANUM."*)?|".ALPHANUM_L."+\:\:".ALPHANUM."+)$/s", $thing, $matches)))
    {
      if ($matches[1]=="~")
       return $matches[2];
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
    if (($thing[0] == "~") && ($thing[1] != "~")) $thing=ltrim($thing, "~");
    if (($thing[0] == "~") && ($thing[1] == "~")) return "~".preg_replace_callback($this->LONGREGEXP, $callback, substr($thing,2));
    // if we reach this point, it must have been an accident.
    return htmlspecialchars($thing);
  }
}

?>