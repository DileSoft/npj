<?php
//??? ����� �������� ��� ����������� :-)

$rh->UseClass("post_wacko", $rh->formatters_classes_dir);

$parser = &new post_wacko( &$object, &$options );

$text = preg_replace_callback("/(\�\�(\S+?)([^\n]*?)==([^\n]*?)\�\�|\�\�[^\n]+?\�\�)/sm",
           array( &$parser, "postcallback"), $text);
    
if ($options["stripnotypo"]) {
  $text = str_replace("<!--notypo-->", "", $text);
  $text = str_replace("<!--/notypo-->", "", $text);
    }

print($text);

?>