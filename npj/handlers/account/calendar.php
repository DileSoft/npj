<?php
  // календарь
  // params[] = { year, month, day }
  if (sizeof($params) > 0) $year= 1*$params[0];
  if (sizeof($params) > 1) $month=1*$params[1];
  if (sizeof($params) > 2) $day=  1*$params[2];

  // отображение ленты за год
  if ($day)
  {
    $params = array(
                     "year" => $year, "month" => $month, 
                     "current" => date("Y-m-d 00:00:00", mktime(0,0,0,$month,$day,$year)),
                   );
    $result0= $object->Action("calendar", &$params, &$principal);
    $params = array( 0=>"feed", 
                     "dtfrom"=>date("Y-m-d 00:00:00", mktime(0,0,0,$month,$day,$year)),
                     "dtto"  =>date("Y-m-d 23:59:59", mktime(0,0,0,$month,$day,$year)),
                   );
    $result = $object->Handler("action", &$params, &$principal);
    $tpl->Append("Preparsed:TITLE", " за ".$day." ".$tpl->message_set["ByMonths"][$month-1]." ".$year);
    $tpl->Assign("Preparsed:CONTENT", $result0.$tpl->GetValue("Preparsed:CONTENT"));
    return $result;
  }
  else
  if ($month)
  {
    $params = array( 0=>"feed", 
                     "dtfrom"=>date("Y-m-d 00:00:00", mktime(0,0,0,$month  ,1,$year)),
                     "dtto"  =>date("Y-m-d 23:59:59", mktime(0,0,0,$month+1,0,$year)),
                   );
    $result = $object->Handler("action", &$params, &$principal);
    $tpl->Append("Preparsed:TITLE", " за ".$tpl->message_set["Months"][$month]." ".$year);
    return $result;
  }
  else
  if ($year)
  {
    $params = array( 0=>"calendar", 
                     "year"=>$year,
                     "month"=> "1 2 3 4 5 6 7 8 9 10 11 12",
                     "no_year_href" => 1,
                   );
    $result = $object->Handler("action", &$params, &$principal);
    $tpl->Append("Preparsed:TITLE", " за ".$year." год");
    return $result;
  }



?>