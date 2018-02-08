<?php

 $tpl->Assign("Action:NoWrap", 1);

   // Param name
  if($params[0]) {

    $href = $params[0];

    $text = '';
    if($params['text']) {
      if(strpos($params['text'], "~") !== false) {
        $params['text'] = str_replace("~", $href, $params['text']);
      }
      $text = htmlspecialchars($params['text']);
    }

    $title = '';
    if($params['title']) {
      $title = htmlspecialchars($params['title']);
    }

    $href = htmlspecialchars($href);
    return "<a name=\"$href\" href=\"#$href\" title=\"$title\">$text</a>\n";
  }
?>