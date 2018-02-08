<?php
//сохраняет письмо в отладочных целях, вместе с причиной ошибки.

$db->Execute("INSERT INTO ".$rh->db_prefix."maildebug (body, error, datetime) VALUES (".
           $db->Quote($this->data["body"]).",".$db->Quote($this->data["error"]).",".$db->Quote(date("Y-m-d H:i:s")).
           ")");

?>