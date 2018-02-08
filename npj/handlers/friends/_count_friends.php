<?php

 // * внутренний такой хандлер.
 /*
    вызывается при джойне, адде, едите и 
     а) выкидывает "лишние" записи о друзьях
     б) обновляет счётчики друзей
 */

// if (!$debug->kuso)
// return;
 /* загрузить данные об */
 $account = &new NpjObject( &$rh, $this->npj_account );
 $data = &$account->Load(2);

 /* загрузить перечень групп */
 $rs = $db->Execute("select group_id, group_rank, is_system from ".$rh->db_prefix."groups where user_id = ".$db->Quote($data["user_id"])) ;
 $a  = $rs->GetArray();
 $groups = array(); $ranks = array(); $is_system = array();
 foreach($a as $v)
 { 
   $groups[] = $v["group_id"];
   $ranks[$v["group_id"]] = $v["group_rank"];
   $is_system[$v["group_id"]] = $v["is_system"];
 }

 if (!sizeof($groups)) return GRANTED;

 /* загрузить все записи о друзьях */
 $rs = $db->Execute("select * from ".$rh->db_prefix."user_groups where group_id in (".implode(",",$groups).")");
 $a  = $rs->GetArray();
 $fields = array();
 foreach($a[0] as $k=>$v)
  if (!is_numeric($k) && ($k!="ug_id")) $fields[] = $k;

 $friends  = array();
 $keywords = array();
 foreach($a as $k=>$v)
 {
   $keywords[$v["group_id"]."--".$v["user_id"]."--".$v["keyword_id"]] = $a[$k];
   $friends [$v["group_id"]."--".$v["user_id"]] = $a[$k];
 }

 /*
 $debug->Trace_R( $keywords );
 $debug->Trace_R( $friends );
 $debug->Trace_R( $fields );
 $debug->Error( $fields );
 */
 /* удалить все записи */
 $db->Execute("delete from ".$rh->db_prefix."user_groups where group_id in (".implode(",",$groups).")");

 /* записать заново из массива ключслов */
 $outkeywords = array();
 foreach($keywords as $k=>$v)
 {
   $s=array();
   foreach($fields as $fv) $s[]=$db->Quote($v[$fv]);
   $outkeywords[] = "(".implode(",", $s).")";
 }
 $db->Execute("insert into ".$rh->db_prefix."user_groups (". implode(",",$fields) .") values ".
              implode(",",$outkeywords));

 /* счётчик друзей в профиле посчитать */
 $no_friends =0;
 if ($data["account_type"] == ACCOUNT_USER)
 {
   foreach( $friends as $f )
   if ($is_system[ $f["group_id"] ])
     if ($ranks[$f["group_id"]] == GROUPS_REPORTERS) $no_friends++;
 }
 else
 {
   foreach( $friends as $f )
   if ($is_system[ $f["group_id"] ])
     if (($ranks[$f["group_id"]] > GROUPS_REQUESTS) && ($ranks[$f["group_id"]] < GROUPS_SELF)) $no_friends++;
 }

 /* счётчик френдов посчитать в профиле */
 $rs=$db->Execute("select distinct u.user_id from ".$rh->db_prefix."groups as g, ".$rh->db_prefix."users as u, ".$rh->db_prefix."user_groups as ug ".
                  "where u.user_id = g.user_id and g.group_id = ug.group_id and ug.user_id = ".$db->Quote($data["user_id"]).
                  " and u.account_type = ".$db->Quote(ACCOUNT_USER));
 $a = $rs->GetArray();
 $no_friendof = sizeof($a);

// if ($debug->kuso) $debug->Error( $no_friendof );

 /* обновить счётчики в БД */
 $db->Execute("update ".$rh->db_prefix."profiles set ".
              "number_friends =".$db->Quote($no_friends).", ".
              "number_friendof=".$db->Quote($no_friendof)." ".
              " where user_id = ".$db->Quote($data["user_id"]));




?>