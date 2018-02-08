<?php
/*
   {{P style="before|after|left|RIGHT"                 
                                                       // стили ищи в /wakka.php
       align="left|right" // дл€ тех, кто путаетс€ со стилем
       name="absolute|toc-relative|DOCUMENT-RELATIVE"  // не реализовано пока "toc-relative"
   }}
*/
  $context = $object->npj_object_address;
  $link = "";

  // при просмотре версий и других шн€г с выкрученным боди даже не пытатьс€ строить P
  if ($object->wrong_body) return;

  // параметры в Ќѕ∆ хран€тс€ в массиве $params[ xxx ]
  if (!$params["name"])  $params["name"]  = "document-relative";
  if (!$params["style"]) $params["style"] = $params["align"];
  if (!$params["style"]) $params["style"] = "right";

// отображени€ здесь нет, только предобработка
{
  if ($object->post_wacko_toc) 
    $toc = &$object->post_wacko_toc; // если уже осталс€ готовый ток
  else
  {
    $uactn = &$rh->UtilityAction(); // actions теперь живут в отдельном классе. << max@jetstyle 2004-11-18 >>
    $toc = $uactn->BuildToc( $context, $params["start_depth"], $params["end_depth"], $params["numerate"], $link, 
                             &$this );
  }

  { // ---------------------- p numeration ------------------------
    // вы€сн€ем, какие номера где сто€т
    $toc_p   = array();
    $toc_len = sizeof($toc);
    $numbers = array(); $depth = 0; $pnum=0;
    for($i=0;$i<$toc_len;$i++)
     if ($toc[$i][2] > 66666)
     { // нормировали глубину погружени€
       $pnum++;
       if ($params["name"] == "document-relative") $num = $pnum;
       else                              $num = str_replace("-", "&#0150;&sect;",
                                                str_replace("p", "є", $toc[$i][0] ));
       // правим содержимое TOC @66
       $toc_p[ $toc[$i][0] ] = $num;
     }
     // неплохо б в кэш записать подобновлЄнную версию
     // ??? возможно, это и лишнее, потому что приводит к повторной нумерации тока
     // ??? если нет -- то: галочка (кэш), что мы уже нумеровали этот ток
     // ??? предварительна€ проверка показала, что всЄ вроде работает =)
     // $uactn->tocs[ $context ] = &$toc;

     // теперь надо поставить флажок о том, что неплохо бы искурочить в пост-ваке 
     // исходник странички, добавив туда цыфирки
     $object->post_wacko_toc_p = &$toc_p; 
     $object->post_wacko_action["p"] = $params["style"];
     $object->post_wacko_maxp = $pnum; 
  } // --------------------------------------------------------------
}

$tpl->Assign("Action:NoWrap", 1);
return "";

?>