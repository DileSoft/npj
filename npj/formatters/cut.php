<?php

if (!class_exists("findcut"))
{
 class FindCut
 {
  function FindCut( $obj, $supertag ) 
  { 
    $this->object = &$obj; 
    $this->supertag = $supertag; 
  }
  function Callback( $matches )
  {
    $text = $this->object->rh->tpl->message_set["CutDefault"];
    if ($matches[4] != "") $text = trim($matches[4], '"');
    $this->object->rh->tpl->Skin( $this->object->rh->theme );
     $this->object->rh->tpl->Assign("Href:cut_text", $this->object->Href($this->supertag, NPJ_ABSOLUTE, STATE_IGNORE));
     $this->object->rh->tpl->Assign("cut_text", $text);
     $result = $this->object->rh->tpl->Parse("cut.html");
    $this->object->rh->tpl->UnSkin();
    return $result;
  }
 }
}

  $fc = &new FindCut( &$object, $options["supertag"] );
  // 1. normal <cut>....</cut>
  $text = preg_replace_callback( 
        "/(<cut(\s*)(\s".ALPHANUM."+=(([^>\"]+)|(\"[^\"]*\")))?>)(.*?)(<\/cut>)/si", 
        array( &$fc, "Callback"), $text );

  echo $text;

?>