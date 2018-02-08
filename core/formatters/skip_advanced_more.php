<?php
  /*
     NOTICE: this is Magick formatter.
     
     PURPOSE: Conditional skipping of template`s parts
     SYNTAX:
              {{??IsBig}}  next text to be shown if IsBig != 0
                          there could be a lot of text
              {{??/IsBig}} 

              OR

              {{??!IsBig}}  next text to be shown if IsBig == 0
                          there could be a lot of text
              {{??/IsBig}} 

            This example suppose $tpl->Assign("IsBig", 1 or 0)

            This formatter supports recursion.

     NB:  {{??Active}}...{{??!Active}}....{{??/Active}} use is NOT allowed. Use {{?...}} instead
  */ 

  if (!isset($this->skip_stack)) $this->skip_stack = array(false);

  if ($what{1} == "/") 
  { 
    $this->skip_tag = array_pop($this->skip_stack); 
    return; 
  }

  if ($what{1} == "!") { $what = substr($what,2); $inverse=true; }
  else { $what = substr($what,1); $inverse=false; }

  array_push( $this->skip_stack, $this->skip_tag );

  if (($this->GetValue( $what ) && $inverse) || (!$this->GetValue( $what ) && !$inverse)) 
    $this->skip_tag=true;

?>