<?php

  // 1. права проверяются в account/manage
  // 2. соответствие формы проверяется в ConfirmForm

  $rh = &$this->rh;
  $db = &$this->rh->db;
  $debug = &$this->rh->debug;

  // нужно получить рекорд_ид, account_id
  $record_id = $tpl->GetValue( "Confirm.RecordId" );
  $account_id = $tpl->GetValue( "Confirm.AccountId" );

  // Удалять все связанные записи в прочих таблицах
  $query = "update ".$rh->db_prefix."records_ref set need_moderation=1 ".
           " where record_id=".$db->Quote($record_id)." and ".
           " keyword_user_id=".$db->Quote($account_id);
  $db->Execute( $query );

  $this->success = true;

?>