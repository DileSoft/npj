<?php
/*
    {{Calendar
                year ="2004"
                month="05 06"
                current = "2004-05-29"
                style="default"
                no_year_href="1"
                ...
                +feed params like: for,type,minrank,groups,order,filter
                -but not thatones: dt, dtfrom, dtto 
    }}
*/

// fill defaults
if (!isset($params["style"])) $params["style"] = "default";
if (!isset($params["current"]))  $params["current"]  = date("Y-m-d 00:00:00");
if (!isset($params["year"]))  $params["year"]  = date("Y");
if (!isset($params["month"])) $params["month"] = date("m");

// параметры дат
$dt = $params["current"];
$dt = strtotime($dt);
$d = date("d", $dt);
$m = date("m", $dt);
$y = date("Y", $dt);
$year  = 1*$params["year"];
$months = explode(" ",$params["month"]);
if (!is_array($months)) $months = array(0);

$result = "";
foreach( $months as $_m )
{
   // month to show
   $startdt = mktime(0,0,0, $_m, 1, $year);
   $_m = date("m", $startdt);
   $_y = date("Y", $startdt);

   // загружаем итемы из БД через feed
   $params["dt"] = "month";
   $params["dtfrom"] = date("Y-m-d 00:00:00", mktime(0,0,0,$_m,  1,$_y));
   $params["dtto"]   = date("Y-m-d 23:59:59", mktime(0,0,0,$_m+1,0,$_y));
   $params["mode"]   = "calendar"; // не возвращать результат а только отдать id-шники

   $object->Action("feed", $params, &$principal);
   $data = $tpl->GetValue("Preparsed:CALENDAR");
   if (!is_array($data)) $data = array();

   // раскидываем их по дням
   $daily_items = array();
   $daily = array();
   foreach($data as $v) 
   { 
     $v["day"] = 1*date("d",strtotime($v["datetime"]));
     $daily[$v["day"]]++;            // сколько в этот день
     $daily_items[$v["day"]] = $v;   // ссылка на последний зачем-то
   }
   //$debug->Trace_R($daily);
   //$debug->Error(sizeof($data));
   $daily_href = array();
   foreach($daily as $k=>$v)
    $daily_href[$k] = $object->Href( "/".$_y."/".$_m."/".str_pad($k,2,"0", STR_PAD_LEFT), NPJ_RELATIVE, STATE_IGNORE );

  // весёлый вывод
  // -----------------------------------------------------
  // заполняем массив $days числами (-4...36)
  $_day = date("w", $startdt)-1;
  if ($_day==-1) $_day = 6;
  $_day--;
  $_d_max1 = date("d",mktime(0,0,0, $_m+1, 0, $_y));
  $total = $_d_max1+$_day;
  $_d_max = $_d_max1+ 7-($total)%7;
  $total = $_d_max+$_day;
  $days = array();
  for ($i=-$_day; $i<=$_d_max; $i++)
   $days[] = date("d",mktime(0,0,0, $_m, $i, $_y));
  // считаем строки
  $row_count = $total/7;
  $rows = array();
  for ($i=0; $i<$row_count; $i++)
  {
    $row = "<tr>";
    for ($j=0; $j<7; $j++)
    {
      $_class=""; $out = 0; $curr=0;
      if ($j > 4) $_class[] = "h-";
      if (($i*7 + $j) < $_day+1) { $out=1; $_class[] = "out-"; }
      if (($i*7 + $j) > $_day+$_d_max1) { $out=1; $_class[] = "out-"; }
      if (!$out && (1*$days[$i*7 + $j]==1*$d) && (1*$_m == 1*$m) && (1*$_y==1*$y)) { $curr=1; $_class[] = "current-"; }
      if ($_class) $_class = " class=\"".implode(" ",$_class)."\"";
      $td = "<td".$_class.">";
      if (!$curr && !$out && $daily[1*$days[$i*7 + $j]])
        $td.= "<a href=\"".$daily_href[1*$days[$i*7 + $j]]."\">".$days[$i*7 + $j]."</a>";
      else
        $td.= $days[$i*7 + $j];
      $td.= "</td>";
      $row.=$td;
    }
    $rows[] = $row."</tr>";
  }
  // hint:
  //  class="out-"      -- за пределами месяца
  //  class="h-"        -- выходной
  //  class="current-"  -- текущий день
  // выводим строки
  for ($i=0; $i<7; $i++)
   $tpl->Assign("row".$i, $rows[$i]);
  $tpl->Assign("year", $_y);
  if ($params["no_year_href"])
    $tpl->Assign("Href:year", "");
  else
    $tpl->Assign("Href:year", $object->Href("/".$_y, NPJ_RELATIVE, STATE_IGNORE));
  $tpl->Assign("month", $tpl->message_set["Months"][1*$_m]);

  $result[] = $tpl->Parse("actions/calendar.html:".$params["style"]."_Month");
  // -----------------------------------------------------
}

foreach( $result as $k=>$v )
  $tpl->Assign("Calendar:Contents[".$k."]", $v);

$tpl->Assign("Calendar:Contents", implode("", $result));
 
return $tpl->Parse("actions/calendar.html:".$params["style"]);

?>
