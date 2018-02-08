<?php

  $output = "";


  // -------------------------------------------------------------------------------------
  if (!function_exists( "formatter_source_callback"))
  {
    function formatter_source_callback($matches)
    {
      $m = $matches[1];
      $nbsp = "&nbsp;";
      $result = "";
      for( $i=strlen($m); $i>0; $i-- )
       $result.=$nbsp;

      return $result;
    }
  }
  // -------------------------------------------------------------------------------------

  if ($options["default"] == "wacko") 
  {
    // вырезать комментарии
    $text = preg_replace( "/(\n?)%%\((c|comments)\).*?%%([\n\r]*)/ims", "", $text );

    // вставить про источник
    $text = $text.="\n\n----\n".$this->message_set["SourceFrom"]."((".$options["source"]."))";

    // подготовить текст к выводу 
    $output = htmlspecialchars( $text );
    $output = preg_replace_callback("/^( +)/mi", "formatter_source_callback", $output);
    $output = $this->Format( $output, "simplebr", NULL, 0, array("no<p>"=>1) );
  }
  else
  if ($options["default"] == "rawhtml") 
  {
    // вставить про источник
    $rh->absolute_urls = 1;
    $text = $text.="\n\n<br /><br /><hr />\n\n<p>".
            $this->message_set["SourceFrom"].
            $rh->object->Link($options["source"], "", $this->message_set["SourceFromLink"] ).
            "</p>";
    $rh->absolute_urls = 0;

    $output = htmlspecialchars( $text );
    $output = str_replace("\n", "<br />", $output);
  }
  else
  if ($options["default"] == "simplebr")
  { 
    // вставить про источник
    $rh->absolute_urls = 1;
    $text = $text.="\n\n<hr />\n".$this->message_set["SourceFrom"].
    $rh->object->Href($options["source"]);
    $rh->absolute_urls = 0;

    $output = htmlspecialchars( $text );
  }

  $div_id = "document_source_".md5($options["source"]);
  echo "<!--no"."typo-->";
  // copy to clipboard is implemented only for MSIE for now
  if ($options["copy_button"])
  {
    echo "<button id=\"button_$div_id\" style=\"margin:5px\" onclick=\"
        ta  = document.getElementById('textarea_$div_id');
        div = document.getElementById('$div_id');
        ta.value = div.innerText;
        range = ta.createTextRange();
        range.execCommand('Copy');
        this.style.backgroundColor='#ffffdd';
        setTimeout('document.getElementById(\''+this.id+'\').style.backgroundColor = \'#dddddd\';', 100);
        \">". $tpl->message_set["SourceCopyToClipboard"]."</button>";
    echo "<textarea style=\"display:none\" id=\"textarea_$div_id\" ></textarea>";
  }
  echo "<div id=\"$div_id\" class=\"code\" style='padding:5px'>";
    echo $output;
  echo "</div>";
  
  echo "<!--/no"."typo-->";

?>