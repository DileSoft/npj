<?php

  if ($what{0} == "?") { return $this->Format($what, "skip_advanced_more"); }

  if ($what{0} == "/") { $this->skip_tag = false; return; }

  if ($what{0} == "!") { $what = substr($what,1); $inverse=true; }

  if (($this->GetValue( $what ) && $inverse) || (!$this->GetValue( $what ) && !$inverse)) $this->skip_tag=true;
  else $this->skip_tag=false;

?>