<?php

 // подчасть add.php

 // есть $this->params[0], где НпжАдрес пользователя
 // есть $_POST["_group_XX"], где ХХ -- идшники групп, куда надо включить
 // есть $groups[ is_system ][ group_rank ][ group_id ], где есть список групп
 // есть $user -- тот, кого добавляем
 // есть $account -- тот, кому добавляем
 // есть $udata, $data -- данные соответственно юзера и аккаунта

 // шаг 1. помечаем всё старое и валидатим всё новое
 $old = array(); $new = array(); $ranks = array();
 for ($i=1; $i>=0; $i--)
  foreach ($groups[$i] as $r=>$group_rank)
   foreach ($group_rank as $id=>$group)
   {
     if ($i == 1) $ranks[$r]=1; 
     if ($ranks[$r])
      if ($_POST["_group_".$id]) $new[] = $id; 
   }

 // шаг 2. удаляем всё старое
 if (sizeof($old) > 0)
  $db->Execute( "delete from ".$rh->db_prefix."user_groups where user_id = ".$db->Quote($udata["user_id"]).
                " and group_id in (".implode(",", $old).")" );

 // шаг 3. записываем всё новое в БД
 if (sizeof($new) > 0)
 {
   $record = &new NpjObject( &$rh, $this->params[0].":" );
   $data2 = $record->Load(2);

   if (!is_array($data2)) //$debug->Error("{!!!} friend of an external user not implemnted yet.",3);
     $tpl->Assign("ERROR", "<div class='error'>Нет такого пользователя</div>" ); // !!! to messageset
   else
   {

   $sql = "insert into ".$rh->db_prefix."user_groups (group_id, user_id, keyword_id) VALUES "; $f=0;
   foreach ($new as $group)
   { if ($f) $sql.=", "; else $f=1;
     $sql.="(".$db->Quote($group).", ".$db->Quote($udata["user_id"]).", ".$db->Quote($data2["record_id"]).")";
   }
   $db->Execute( $sql );

    $rh->Redirect( $rh->Href( $this->_NpjAddressToUrl( $account->name.":friends/add/".$udata["login"]."/".$udata["node_id"]."/done" )
                               , IGNORE_STATE ) , IGNORE_STATE );
   }
 }
 else
  $rh->Redirect( $rh->Href( $this->_NpjAddressToUrl( $account->name.":friends/add/".$udata["login"]."/".$udata["node_id"]."/done" )
                            , IGNORE_STATE ) , IGNORE_STATE );

?>
