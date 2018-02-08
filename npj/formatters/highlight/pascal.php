<?php

$rh->UseClass("DelphiHightlighter", $rh->formatters_classes_dir);

$DH = &new DelphiHightlighter();
echo "<!--no"."typo-->";
echo "<div class=\"code\"><pre>";
echo $DH->analysecode($text);
echo "</pre></div>";
echo "<!--/no"."typo-->";
unset($DH);

?>