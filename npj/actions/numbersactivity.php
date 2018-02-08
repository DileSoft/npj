<?php
/*
    {{KusoMonth for="user@npj" date="23.09.1979"}}
*/

  $me = $principal->data["login"]."@".$principal->data["node_id"];

  // -1. проверить права
  if (!$this->HasAccess( &$principal, "acl_text", $rh->node_admins ))
   {
    $params = array( "for"=>$me, "wrapper"=>$params["wrapper"],
                     "action_as_handler" => $params["action_as_handler"]  );
   }
  else
  {
  }

  // 0. default show
  if (!isset($params["for"])) $params["for"] = $me;
  if (!isset($params["date"])) $params["date"] = strtotime(date("Y-m-d 00:00:00"));
  else $params["date"] = strtotime($params["date"]);

  if (strpos($params["for"], "@") === false) $params["for"].="@".$rh->node->data["node_id"];

  $params["end"] = $params["date"]- 60*60*24*30; // one month
  $dtstart = $params["date"];
  $dtend   = $params["end"];

  $params["date"] = date("Y-m-d 23:59:59",$params["date"]);
  $params["end"]  = date("Y-m-d 23:59:59",$params["end"]);

  $time_step = 60*30; // 0,5 hour step

  $temp = $dtend."==";

  // 1. get user
  $user = &new NpjObject( &$rh, $params["for"] );
  $data = $user->Load(3);
  if (!is_array($data)) return $this->Action( "_404", &$params, &$principal );

  // 2. получить все метки статов за период
  $sql = "select server_datetime from ".$rh->db_prefix."usage_stats where principal_user_id = ".
         $db->Quote($data["user_id"]).
         " and server_datetime > ".$db->Quote($params["end"]).
         " and server_datetime <= ".$db->Quote($params["date"]);
  $rs = $db->Execute($sql);
  $time_line = array();
  $temp = $sql;
  while (!$rs->EOF)
  {
    $time_pos = strtotime($rs->fields["server_datetime"]) - $dtend;
    $time_line[ floor($time_pos / $time_step) ] = 1; // alive in that very hour
    $rs->MoveNext();
  }

  // 3. сформировать массив галочек
  $columns = array();
  $days = ceil(($dtstart-$dtend)/(60*60*24));
  $intervals = ceil(60*60*24/$time_step);
  for( $i=0; $i<$intervals; $i++)
  {
    $columns[$i] = array();
    for ($j=0; $j<$days; $j++)
    {
      $columns[$i][$j] = 1*$time_line[ $j*$intervals + $i ];
      if ($columns[$i][$j]) $totals++;
    }
  }

  // 4. парсинг радости
  $sunday = date("w", $dtend)-1;
  $dummy1 = "<div class=\"sep-\">".$tpl->Parse("dummy.html")."</div>";
  $dummy2 = "<div>".$tpl->Parse("dummy.html")."</div>";
  for ($i=0; $i<$intervals; $i++)
  {
    if ($i*$time_step%3600 == 0)
      $time = str_pad($i*$time_step/3600, 2, "00", STR_PAD_LEFT).":00";
    else
      $time = "<b class=\"k-m0\">00:00</b>";
    $r = $time;
    foreach($columns[$i] as $k=>$v)
     $r.="<b class=\"k-m".($v?$v:(($k+$sunday)%7?0:2))."\">0000</b>&nbsp;&nbsp;";
    $r.=$time;
    if ($i%4 == 3)
     $r.=$dummy1;
    else
     $r.=$dummy2;

    if ($r=="") unset($columns[$i]);
    else $columns[$i] = $r;

  }

  $style="<style>".
         "b.k-m0 { font-weight:normal; background:#eeeeee; color:#eeeeee }".
         "b.k-m2 { font-weight:normal; background:#cccccc; color:#cccccc }".
         "b.k-m1 { font-weight:normal; background:#888888; color:#888888 }".
         ".backet { float:left; font-family:Tahoma; font-size:8px; line-height:10px; padding:5px; background:#eeeeee } ".
         ".backet .sep- { background:#cccccc } ".
         "</style>";

   $tpl->Assign("Action:NoWrap", 1);

  $time = "<b class=\"k-m0\">00:00</b>";
  $name="<div style='padding: 0 0 5px 0px; font-weight:bold;font-size:12px'>".date("d.m.Y", $dtend).
              "&nbsp;&nbsp;&#151;&nbsp;&nbsp;".
              $object->Link($data["login"]."@".$data["node_id"]).
              " (".$totals.")</div>";
  $zerocolumn=$time;
  for($j=0; $j<$days; $j++)
  {
    $_time = $dtend+$j*24*60*60;
    $day  = date("d",$_time);
    $zerocolumn .= "<b class=\"k-m0\">0</b>".$day."<b class=\"k-m0\">0</b>&nbsp;&nbsp;";
    
  }
  $zerocolumn.=$time;

  return "<div class=\"backet\"><nobr>".$style.$name.
          $zerocolumn.
          $dummy1.
          implode("", $columns).
          $zerocolumn.
          "</nobr></div><br clear=\"all\" />";


?>