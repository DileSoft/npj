<?php
/* ѕараметры:
   * year  --- параметры календар€                               (default = current)
   * month --/                                                   (default = current)
   * day ------------------- день, который следует выбрать       (default = today day number)
     - days -- массив, если таких дней несколько                 (no default)
   * today -- день, который следует пометить как сегодн€
           -- если -1, то помечать не нужно, если unset, вз€ть из date()
           --                                               
                                                                 (default = date())

   * week_start, week_end -- можно получить календарь на произвольное кол-во недель
                                                                 (no default)

   ? как-то нужно передавать счЄтчики, чувак. как-то нужно. ¬еришь?

   ----------------------------------------------------------------------- */


  // 0. ≈сли параметров нет, то надо найти самую последнюю новость и вз€ть year/month от неЄ
  if (!$params["year"] || !$params["month"])
  {
    $rs = $db->SelectLimit( "select user_datetime from ". $this->rh->db_prefix.$this->table_prefix."items WHERE ".
                            "active=1 and user_datetime <= NOW() order by user_datetime DESC", 1);
    if ($rs->RecordCount() == 0) return;
    $dt = strtotime( $rs->fields["user_datetime"] );
    if (!$params["year"])  $params["year"]  = date("Y", $dt);
    if (!$params["month"]) $params["month"] = date("m", $dt);
  }

  // 1. ≈сли не указан today, надо его автоматом заполнить
  if (!$params["today"]) 
   if ((date("Y") == $params["year"]) && (date("m") == $params["month"]))
    $params["today"] = date("d");

  // 2. Ќаходим недели, которые нас интересуют
  if (!$params["week_start"] || !$params["week_end"])
  { 
    $dt_start = mktime (0,0,0,1*$params["month"],1,$params["year"]); 
    $dt_end   = mktime (0,0,0,1*$params["month"]+1,0,$params["year"]); 
    if (!$params["week_start"])  $params["week_start"]  = date("W", $dt_start);
    if (!$params["week_end"])    $params["week_end"]    = date("W", $dt_end);

  }

  // 3. ѕолучить счЄтчики по дн€м за эти недели
  $days = array(); // [month][day]
  $rs = $db->SelectLimit( "select DAYOFMONTH(user_datetime) as day, MONTH(user_datetime) as month, id from ". $this->rh->db_prefix.$this->table_prefix."items WHERE ".
                          "active=1 and ".
                          "(WEEK(user_datetime,3) >= ".$db->Quote(1*$params["week_start"]).") AND ".
                          "(WEEK(user_datetime,3) <= ".$db->Quote(1*$params["week_end"]).") ".
                          "", 10000 );
  if ($rs->RecordCount() == 1000) $bonus=$tpl->message_set["News:AndEvenMore"];
  $a = $rs->GetArray();
  foreach( $a as $item ) $days[ $item["month"] ][ $item["day"] ]++;

  // 4. ѕолучить счЄтчики по мес€цам за соседние
  $months = array(); // [month]
  $rs = $db->SelectLimit( "select MONTH(user_datetime) as month, COUNT(id) as no from ". $this->rh->db_prefix.$this->table_prefix."items WHERE ".
                          "active=1 and ".
                          "(YEAR(user_datetime) = ".$db->Quote(1*$params["year"]).") AND ".
                          "(ABS(MONTH(user_datetime)-".$db->Quote(1*$params["month"]).")<2) ".
                          "GROUP BY MONTH(user_datetime)", 10000 );
  $a = $rs->GetArray();
  foreach( $a as $item ) $months[ $item["month"] ] = $item["no"];


  // 5. ‘ормируем календарь -- мес€чна€ зона
  // -- текущий
   $_month = $params["month"]; $_year = $params["year"];
   $tpl->Assign( "month_no",     $_month );   $tpl->Assign( "month_year",   $_year );
   $tpl->Assign( "month_name", $tpl->message_set["News:Months1"][1*$_month] );
   if ($months[1*$_month] == 0) $tpl->Assign("month_count", $tpl->message_set["News:HowMany0"] );
   else $tpl->Assign("month_count", $tpl->message_set["News:HowMany"].$months[1*$_month]);
   $tpl->Assign( "Href:month", $this->Href( array( "year"=>$_year, "month"=>$_month, )));
  // -- следующий
   $_month = $params["month"]+1; $_year = $params["year"];
   if ($_month > 12) { $month=1; $_year++; }
   $tpl->Assign( "NEXTmonth_no",     $_month );   $tpl->Assign( "NEXTmonth_year",   $_year );
   $tpl->Assign( "NEXTmonth_name", $tpl->message_set["News:Months1"][1*$_month] );
   if ($months[1*$_month] == 0) $tpl->Assign("NEXTmonth_count", $tpl->message_set["News:HowMany0"] );
   else $tpl->Assign("NEXTmonth_count", $tpl->message_set["News:HowMany"].$months[1*$_month]);
   $tpl->Assign( "Href:NEXTmonth", $this->Href( array( "year"=>$params["year"], "month"=>$_month, )));
  // -- предыдущий
   $_month = $params["month"]-1; $_year = $params["year"];
   if ($_month < 1) { $month=12; $_year--; }
   $tpl->Assign( "PREVmonth_no",     $_month );   $tpl->Assign( "PREVmonth_year",   $_year );
   $tpl->Assign( "PREVmonth_name", $tpl->message_set["News:Months1"][1*$_month] );
   $tpl->Assign( "PREVmonth_count", $months[1*$_month] );
   if ($months[1*$_month] == 0) $tpl->Assign("PREVmonth_count", $tpl->message_set["News:HowMany0"] );
   else $tpl->Assign("PREVmonth_count", $tpl->message_set["News:HowMany"].$months[1*$_month]);
   $tpl->Assign( "Href:PREVmonth", $this->Href( array( "year"=>$params["year"], "month"=>$_month, )));
  // -- парсинг
  $tpl->Parse($this->tpl_prefix."grid.html:MonthZone", "month_zone");


  // 6. ‘ормируем календарь -- недели решЄтки
  $weeks = array();
  $dt_start  = mktime (0,0,0,1,1,$params["year"]);  // 1st Jan YYYY
  $day_tweak = date( "w", $dt_start );
  for ( $week = $params["week_start"]; $week <= $params["week_end"]; $week++ )
  { 
    $day_start = $week*7 -7 -$day_tweak; 
    $cc=0;
    for ($day = 1; $day <= 7; $day++ )
    {
      // -- получить день, мес€ц и год
      $dt = $dt_start + ($day+$day_start)*24*60*60;
      $_year  = date("Y", $dt);
      $_month = date("m", $dt);
      $_day   = date("d", $dt);
      // -- пон€ть, какой стиль у дн€  
      $sep = "_";
      $day_style = "";
      //    * ссылка или нет (есть новости)
      if ($this->grid_subclasses["Link"])
      {
        $day_style .= $sep;
        if ($days[$_month][$_day] > 0) $day_style.="Link"; else $day_style.="Text"; 
      }
      //    * сегодн€ или нет
      //    * в будущем или прошлом
      if ($this->grid_subclasses["Today"])
      {
        $day_style .= $sep;
        if ((date("Y") == $_year) && (date("m") == $_month) && (date("d") == $_day))
         $day_style.="Today"; else 
         if ($dt < mktime(0,0,0)) $day_style.="Past"; else $day_style.="Future"; 
      }
      //    * в текущем мес€це или за его пределами
      if ($this->grid_subclasses["Current"])
      {
        $day_style .= $sep;
        if (($params["year"] == $_year) && ($params["month"] == $_month)) $day_style.="Current"; else $day_style.="Offtopic"; 
      }
      //    * выходной/праздник или нет (!!! праздник пока не реализован)
      if ($this->grid_subclasses["Holiday"])
      {
        $day_style .= $sep;
        if ($this->grid_weekends[$day]) $day_style.="Holiday"; else $day_style.="Workday"; 
      }
      //    * выбран или нет
      if ($this->grid_subclasses["Selected"])
      {
        if (($params["year"] == $_year) && ($params["month"] == $_month))
        if ((1*$_day == 1*$params["day"]) || (is_array($params["days"]) && in_array(1*$_day,1*$params["days"]))) 
           $day_style.=$sep."Selected";
      }
      //    - варианты (_Link_Today_Current_Holiday) / (_Text_Future_Offtopic_Workday) 
      //                                             / (_Link_Past_Offtopic_Workday_Selected)
      //    - кол-во шаблонов = 2х3х2х2x2 = 48 шаблонов, вау.
      // -- заполнение домена и парсинг
      $tpl->Assign("day_class",    $day_style);
      $tpl->Assign("day_no",    $_day);
      $tpl->Assign("day_N",     1*$_day);
      $tpl->Assign("day_i",     $day);
      $tpl->Assign("day_count_N", $days[1*$_month][1*$_day]);
      if ($days[1*$_month][1*$_day] == 0) $tpl->Assign("day_count", $tpl->message_set["News:HowMany0"] );
      else $tpl->Assign("day_count", $tpl->message_set["News:HowMany"].$days[1*$_month][1*$_day]);
      $tpl->Assign("Href:day", $this->Href(array("year"=>$_year, "month"=>$_month, "day"=>$_day)));
      if (!$this->grid_style_templates) $day_style="";
      $tpl->Parse($this->tpl_prefix."grid.html:Day".$daystyle, "day_".$day);
    }
    $tpl->Assign("week_no", $week );
    $weeks[$week] = array( "week_no" => $week,
                           "days" => $tpl->Parse($this->tpl_prefix."grid.html:Week"));
  }

  $debug->Trace_R($days);
  $debug->Trace_R($params);
  $debug->Trace( "first:" .date( "d m Y", $dt_start ));
  $debug->Trace( "tweak:" .$day_tweak);
  //$debug->Error("here");
  // 7. –аспарсиваем готовый календарь
  $list = &new ListObject( &$rh, $weeks );
  return $list->Parse( $this->tpl_prefix."grid.html:WeekList" );

?>