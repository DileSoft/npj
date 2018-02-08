<?php

  $rh->UseClass("FormatterWysiwyg", $rh->core_dir );

if (!class_exists("SiteWysiwyg")) {
class SiteWysiwyg extends FormatterWysiwyg
{
  function H( $matches )
  {
    return "<h2 style='font-size:130%'>".$matches[1]."</h2>";
  }

  function _Format( $text )
  {
    $text = preg_replace_callback( "/<h2>(.*?)<\/h2>/i", 
                                   array(&$this, "H"), $text );
    if ($this->tpl->config->pictures)
    {
      $text = preg_replace_callback( "/<img [^>]*?class=\"pictures_[0-9]+\".*?>/i", 
                                     array(&$this, "Img"), $text );
      $text = preg_replace_callback( "/<a href=\"[^\"]+_pictures\/.*?<\/a>/i", 
                                     array(&$this, "ImgA"), $text );
    }
    return $text;
  }
}

}

  $obj = &new SiteWysiwyg( &$tpl );
  echo $obj->Format( $text );

?>