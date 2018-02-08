<?php
/*
   !!!!!! document
   this is base class for SiteWysiwyg formatter class
   for punishing wysiwyg-authored content
  ----
*/

class FormatterWysiwyg {
  var $tpl;

  function FormatterWysiwyg( &$tpl )
  {
    $this->tpl = &$tpl;
  }
  function _Format( $text ) { }

  function Img( $matches )
  {
    if (!$this->tpl->config->pictures) return $matches[0];
    return $this->tpl->config->pictures->FormatImg( $matches[0] );
  }
  function ImgA( $matches )
  {
    if (!$this->tpl->config->pictures) return $matches[0];
    return $this->tpl->config->pictures->FormatA( $matches[0] );
  }

  function Format( $text )
  { $tpl = &$this->tpl;

    $tpl->Assign("Wysiwyg", 1);
    $ignore = "/<noformat>.*?<\/noformat>/ims";
  
    if (!preg_match("/<p/i", $text)) $text = "<p>".$text."</p>";

    // -2. игнорируем ещЄ регексп
    $ignored = array();
    {
      $total = preg_match_all($ignore, $text, $matches);
      $text = preg_replace($ignore, "\201", $text);
      for ($i=0;$i<$total;$i++)
      {
        $ignored[] = $matches[0][$i];
      }
    }

    $text = $this->_Format( $text );

    // Ѕ≈— ќЌ≈„Ќќ—“№-2. вставл€ем ещЄ сигнорированный регексп
    {
      $text .= " ";
      $a = explode( "\201", $text );
      if ($a)
      {
        $text = $a[0];
        $size = count($a);
        for ($i=1; $i<$size; $i++)
        {
         $text= $text.$ignored[$i-1].$a[$i];
        }
      }
    }

    $tpl->Assign("Wysiwyg", 0);
  
    return "<!--noformat-->".$text."<!--/noformat-->";
  }


}

?>