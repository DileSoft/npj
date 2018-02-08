<?php

if ($text == "") return;

$rh->UseClass("typografica", $rh->formatters_classes_dir);

$typo = &new typografica( &$this->config );

// kuso@npj: since dashglued cause rendering bugs in Firefox, this option is now turned off.
$typo->settings["dashglue"] = false; 

print $typo->correct($text, false);


?>