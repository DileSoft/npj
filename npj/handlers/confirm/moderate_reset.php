<?php

  // 1. ����� ����������� � account/manage
  // 2. ������������ ����� ����������� � ConfirmForm

  $rh = &$this->rh;
  $db = &$this->rh->db;
  $debug = &$this->rh->debug;

  // ����� �������� ������_��, account_id
  $record_id = $tpl->GetValue( "Confirm.RecordId" );
  $account_id = $tpl->GetValue( "Confirm.AccountId" );

  // ������� ��� ��������� ������ � ������ ��������
  $query = "update ".$rh->db_prefix."records_ref set need_moderation=1 ".
           " where record_id=".$db->Quote($record_id)." and ".
           " keyword_user_id=".$db->Quote($account_id);
  $db->Execute( $query );

  $this->success = true;

?>