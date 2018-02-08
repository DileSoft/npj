<?php
/*
  ----

  * ConvertDate( $dt, $no_year=false ) -- преобразователь из "2004-05-20 23:37:20" => "20 мая 2004"

  * Sql( $sql )        -- дополнительные преобразования sql-строк, в частности:
                          * отрезание "active=1", если принципал -- админ

  * Db( $sql, $limit=false, $offset=false ) -- запрос к БД через $rh->db

  - Edit1Click(...)    -- Создание кода для вывода попап-ссылки на быстрое редактирование объекта
                          * можно записать в $tpl "Edit1Click:BODY", тогда в ряде шаблонов оно всунется внутрь

  ----
  * $this->roles["admin"] -- в каких ролях может выступать человек
  * $this->is_editor      -- хотя бы редактор


*/


class Helper
{
  var $months = array("января", "февраля", "марта", "апреля", "мая",  "июня", 
                      "июля", "августа", "сентября", "октября", "ноября", "декабря");
  var $months1 = array("Январь", "Февраль", "Март", "Апрель", "Май",  "Июнь", 
                       "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь");
  var $weekdays = array("воскресенье", "понедельник", "вторник", "среда", 
                    "четверг",  "пятница", "суббота", );

                      
  function Helper( &$rh, $for_role = "editor" )
  {
    $this->role = $for_role;

    $this->rh = &$rh;
    $r = explode(" ", $rh->principal->data["__roles"]);
    $this->roles = array();
    foreach( $r as $rr ) $this->roles[$rr] = 1;
    $this->is   = $this->roles[$this->role];

    //
    if ($_REQUEST["_cms"])
    {
      if ($_REQUEST["_cms"] == "show") $value=1;
      if ($_REQUEST["_cms"] == "hide") $value=0;
      setcookie( $rh->cookie_prefix."_cms", $value, time()+$rh->cookie_expire_days*24*3600, "/" );
      $_COOKIE[ $rh->cookie_prefix."_cms" ] = $value;
    }
    if ($_REQUEST["_cms_hidden"])
    {
      if ($_REQUEST["_cms_hidden"] == "show") $value=1;
      if ($_REQUEST["_cms_hidden"] == "hide") $value=0;
      setcookie( $rh->cookie_prefix."_cms_hidden", $value, time()+$rh->cookie_expire_days*24*3600, "/" );
      $_COOKIE[ $rh->cookie_prefix."_cms_hidden" ] = $value;
    }

    //
    $this->hide_edit   = !$this->is || !$_COOKIE[ $rh->cookie_prefix."_cms" ];
    $this->hide_hidden = !$this->is || !$_COOKIE[ $rh->cookie_prefix."_cms_hidden" ];
    $this->hide_edit_   = $this->hide_edit;
    $this->hide_hidden_ = $this->hide_hidden;

  }

  function ConvertDate( $dt, $no_year=false )
  {
    $dt = explode(" ",$dt);
    $d  = explode("-",$dt[0]);
    return ltrim($d[2],"0")."&nbsp;".$this->months[$d[1]-1].((!$no_year)?"&nbsp;".$d[0]:"");
  }
  function ConvertInterval( $dt1, $dt2, $no_year=false )
  {
    if ($dt2 < $dt1) { $t=$dt1; $dt1=$dt2; $dt2=$t; }
    $m1 = 1*date("m",$dt1); $m2 = 1*date("m",$dt2);
    $y1 = "&nbsp;".date("Y",$dt1); $y2 = "&nbsp;".date("Y",$dt2);
    $month1 = "&nbsp;".$this->months[$m1-1];
    $month2 = "&nbsp;".$this->months[$m2-1];
    if ($m1 == $m2) $month1="";// same month
    if ($y1 == $y2) $y1="";// same month
    if ($no_year) { $y1=""; $y2=""; }
    $result = (1*date("d",$dt1)).$month1.$y1." &mdash; ".(1*date("d",$dt2)).$month2.$y2;
    return $result;
  }
  // мытарства с неделями
  function WeekNo( $year, $month, $day )
  {
    $dt = mktime( 0,0,0, $month, $day, $year );
    //$this->rh->debug->Trace( "weekno - ".date("Y-m-d -- W", $dt));
    return date( "W", $dt ); // is 1-based.
  }
  function WeekStartEnd( $week_no, $year )
  {
    $january1 = mktime(0,0,0,1,1,$year);
    $january1w = date( "w", $january1 );
    if ($january1w == 0) $january1w = 7;
    $january1w --; // monday=0
    $addendum = 7-$january1w; // how many days in week in new year?
    if ($addendum < 4) // this week was 53th
      $addendum+=7;

    $start_dt = mktime(0,0,0,1,($week_no-1)*7 +$addendum-7+1, $year );
    $end_dt   = $start_dt + 60*60*24*7-1;
    $result = array(
        "from" => date("Y-m-d H:i:s",$start_dt),
        "to"   => date("Y-m-d H:i:s",$end_dt),
                   );
    //$this->rh->debug->Trace( "$january1w; week_no = $week_no AND ".$addendum );
    //$this->rh->debug->Error_R( $result );
    return $result;
  }

  function Sql( $sql )
  {
    if ($this->hide_hidden) return $sql;

    // active=1
    $sql = str_replace("active=1", "active=active", $sql);
    //
    return $sql;
  }

  function Db( $sql, $limit=false, $offset=false )
  {
    $sql = $this->Sql($sql);
    if ($limit === false) $rs = $this->rh->db->Execute( $sql );
    else                  $rs = $this->rh->db->SelectLimit( $sql , $limit, $offset );
    if (!$rs) $this->rh->debug->Error( "incorrect SQL:<br />".$sql );
    return $rs->GetArray();
  }

  function Edit1Click( $module, $item_id, $module_type="item", // "order", "list"
                       $tplt="default", $custom_text="редактировать",
                       $w=600, $h=420, $scroll="yes", $resizable="yes",
                       $mode="edit", $add_text="", $no_form=0,
                       $modal = 0,
                       $tplt_base = "_/1click.html:"
                     )
  {
    if ($this->hide_edit) return "";

    if ($this->rh->theme) $this->rh->tpl->Skin($this->rh->theme);

    $name = ($module_type=="item")?"id":"parent";
    switch ($module_type)
    { 
      case "item":  $strategy="form"; break;
      case "order": $strategy="list"; break;
      default:      $strategy="module";
    }
    if ($mode!="edit") $name = "parent";
    $mode = ($mode=="edit")?"_edit=1".
                            ($add_text?(($no_form?"":"&__form_present=1&_supertag=").$add_text):"")."&"
                           :"_add=1".
                            ($add_text?(($no_form?"":"&__form_present=1&_supertag=").$add_text):"")."&";

    $this->rh->tpl->Assign("1:text", $custom_text);
    if ($modal)
      $this->rh->tpl->Assign("1:js", "window.showModalDialog(this.href,'','resizable:$resizable;border:".
          ($resizable=="yes"?"thick":"thin").";status:no;statusbar:no;help:no;dialogWidth:$w;dialogHeight:$h;minimize:$resizable;maximize:$resizable');return false");
    else
      $this->rh->tpl->Assign("1:js", "NewWindow(this.href,'".(str_replace("/","_",str_replace("-","_",$module."_".$item_id)))."','$w','$h','$scroll', '$resizable');return false;");
    $this->rh->tpl->Assign("1:href", 
          "/".ltrim($this->rh->cms_url, "/").$strategy."/".$module."?".$mode.$name."=".$item_id."&close=1"
                          );

    $result = $this->rh->tpl->Parse($tplt_base.$tplt);
    if ($this->rh->theme) $this->rh->tpl->UnSkin();
    return $result;
  }


}

/*
  USAGE PATTERNS

  $tpl->Assign("1click", $rh->helper->Edit1Click($this->config["cms"], $this->config["id"], "item",
                                                 "default", "Редактировать текст раздела") );


*/

?>