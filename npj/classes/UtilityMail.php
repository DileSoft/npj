<?php
/*
    UtilityMail( &$rh )  -- Вспомогательные процедуры для работы с электропочтой
    ---------
      - пилотная версия, нуждается в последующем рефакторинге

  * LoadSubsriberEmails( $users ) -- загружает всё необходимое для отправки почты
                                     email, login, node_id, user_name
                                     должно использоваться в майл-интеграции

  // рефакторинг:
  ! перенести сюда prepMail и что-тоещё-Mail from NpjObject
  ? перенести сюда получение подписчиков?

=============================================================== v.0 (Kuso)
*/

class UtilityMail
{
  function UtilityMail( &$rh )
  {
    $this->rh = &$rh;
  }

  function LoadSubsriberEmails( $users )
  {
    $rh = &$this->rh;
    $db = &$this->rh->db;

    foreach( $users as $i=>$v ) $users[$i] = 1*$users[$i];
    $sql = "select u.user_name, u.node_id, u.login, u.user_id, u.email as users_email, p.email from ".
           $rh->db_prefix."users as u left join ".$rh->db_prefix."profiles as p on p.user_id=u.user_id ".
           " where (p.email_confirm IS NULL and u.email <> ".$db->Quote("").
                    " or p.email_confirm=".$db->Quote("").")".
           " and u.user_id in (".implode(",", $users).")";
    $rs = $db->Execute( $sql );
    $a = $rs->GetArray();

    foreach($a as $k=>$v)
      if ($v["email"] == "") $a[$k]["email"] = $v["users_email"];

    return $a;
  }



} // EOC { UtilityMail }

?>