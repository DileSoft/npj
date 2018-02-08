<?php

  
 $tpl->Assign("Action:NoWrap", 1);


  $sql = "SELECT count( * ) as totals ".
         "FROM ".$rh->db_prefix."users AS u, ".
                 $rh->db_prefix."profiles AS p ".
         "WHERE alive = 1 AND account_type = 0 AND u.user_id = p.user_id ".
         " AND DATE_ADD( last_login_datetime, INTERVAL 30 DAY) > NOW() ".
         " AND DATE_SUB( last_login_datetime, INTERVAL 7 DAY) > creation_date ".
         "";

         // живые пользователи, заходившие в течение месяца
         // но последний раз заходившие позже чем через 15 дней после регистрации
         // 

         //$debug->Error($sql);

  $rs = $db->Execute( $sql );

  return 1*$rs->fields["totals"];

?>