<?php
/*
   {{TOC 
          [page|for="!/SubTag"]
          [from="h2"]
          [to  ="h4"]
          [numerate="0|1|..."]
          [p   ="p|1|right|left|before|after"]
   }}
*/

// дл€ Ќѕ∆ нам надо выдавить параметры из массива $params
 extract( $params, EXTR_SKIP );

 // при просмотре версий и других шн€г с выкрученным боди даже не пытатьс€ строить TOC
 if ($object->wrong_body)
 {
    $_params = array( "error" => "Error.TocInVersion");
    return $this->Action( "_404", &$_params, &$principal );
 }

// 1. check for first param (for what TOC is built)
if ($for) $page=$for;
if ($page) 
{
  $page = $object->_UnwrapNpjAddress($page);
  $context = $page;
  
  // 2. load
  $inside = &new NpjObject( &$rh, $page );
  $idata = $inside->Load(3);
  if (!is_array($idata)) return $this->Action( "_404", &$params, &$principal );
  // 2+. провер€ем, есть ли доступ
  if (!$inside->HasAccess( &$principal, $this->security_handlers[$idata["type"]] )) 
    return $this->Action( "_404", array("forbidden"=>1), &$principal );

  if (!$title) $title = $inside->data["subject"];
  $link = $inside->Href( $page, NPJ_ABSOLUTE, STATE_IGNORE );
}
else 
{
  $page = ""; 
  $context = $object->npj_object_address;
  $inside = &$object;
  $idata  = &$inside->data;
  if ($object->method == "action")
  {
   $link = $inside->Href( $context, NPJ_ABSOLUTE, STATE_IGNORE );
   if (!$title) $title = $inside->data["subject"];
  }
  else
   $link = "";
}

if (!$from) $from = "h2";
if (!$to)   $to   = "h9";

$start_depth = $from{1};
$end_depth   = $to{1};


// 3. output
$uactn = &$rh->UtilityAction(); // actions теперь живут в отдельном классе. << max@jetstyle 2004-11-18 >>
$toc = $uactn->BuildToc( $context, $start_depth, $end_depth, $numerate, $link, 
                         &$this );

{ // ---------------------- toc numeration ------------------------
  // вы€сн€ем, какие номера где сто€т
  $toc_len = sizeof($toc);
  $numbers = array(); $depth = 0;
  for($i=0;$i<$toc_len;$i++)
   if ($toc[$i][2] < 66666)
   { // нормировали глубину погружени€
     $toc[$i][4] = $toc[$i][2]-$start_depth+1;
     if ($numerate)
     {
       // если погрузились глубже, обнулили счЄтчик дл€ новой глубины
       if ($toc[$i][2] > $depth) $numbers[ $toc[$i][2] ] =0; 
       // если вышли уровнем меньше, ничего делать не надо.
       // запоминаем глубину и увеличиваем счЄтчик пункта
       $depth = $toc[$i][2];
       $numbers[ $depth ]++;
       // собираем нумерацию по массиву $numbers от начала до текущей глубины, пропуска€ нулевые
       $num="";
       for($j=1;$j<=$depth; $j++)
        if ($numbers[$j] > 0) $num.=$numbers[$j].".";
       // правим содержимое TOC
       $toc[$i][1] = $num." ".$toc[$i][1];
     }
   }
   // неплохо б в кэш записать подобновлЄнную версию
   // ??? возможно, это и лишнее, потому что приводит к повторной нумерации тока
   // ??? если нет -- то: галочка (кэш), что мы уже нумеровали этот ток
   // ??? предварительна€ проверка показала, что всЄ вроде работает =)
   // $uactn->tocs[ $context ] = &$toc;

   // теперь надо поставить флажок о том, что неплохо бы искурочить в пост-ваке 
   // исходник странички, добавив туда цыфирки
   if (!$page) // работает только если TOC генерировали дл€ “≈ ”ў≈… —“–јЌ»÷џ 
   { 
     $object->post_wacko_toc = &$toc; 
     $object->post_wacko_action["toc"] = 1; 
   }
} // --------------------------------------------------------------
// отображаем!
$s="";
foreach( $toc as $v )
if ($v[4])
{
  $s.= '<div class="toc'.$v[4].'">';
   $s.= '<a href="'.$v[3].'#'.$v[0].'">'.strip_tags($v[1]).'</a>';
  $s.= '</div>';
}

// ---------------- {{P}} calling routine
 if ($params["p"]) 
 { 
  // 5a. store action params
    $nowrap = $tpl->GetValue("Action:NoWrap");
    $none = $tpl->GetValue("Action:NONE");
  // 5b. get
   $params["style"]=$params["p"];
   include( $rh->actions_dir."p.php" );
  // 5c. restore
    $tpl->Assign("Action:NoWrap", $nowrap);
    $tpl->Assign("Action:NONE",   $none);
 }

// --------------- заполн€ем Action:TITLE
 if ($title != "")
   $tpl->Append("Action:TITLE", " ".$object->Link($context, "", $title) );

return $s;




?>
