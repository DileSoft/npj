<?php
  /*
     NOTICE: this is Magick formatter.
     
     PURPOSE: Conditional skipping of template`s parts
     SYNTAX:
              {{?IsBig}}  next text to be shown if IsBig != 0
                          there could be a lot of text
              {{?!IsBig}} else this text would be shown
                          if is IsBig == 0. 
                          You could use only {?!...} combination
                          w/o first part.
              {{?/IsBig}} 

            This example suppose $tpl->Assign("IsBig", 1 or 0)

     NB: No recursive including for "skip_simple" is allowed. Link "skip_advanced" to "?", if you need it.
  */ 

  if ($what{0} == "/") { $this->skip_tag = false; return; }

  if ($what{0} == "!") { $what = substr($what,1); $inverse=true; }

  if (($this->GetValue( $what ) && $inverse) || (!$this->GetValue( $what ) && !$inverse)) $this->skip_tag=true;
  else $this->skip_tag=false;

?>