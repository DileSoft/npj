<?php

  $TE = &$this->GenerateTemplateEngine();
  $TE->message_set = $tpl->message_set;

  // "STARTUP" handler
  if ($this->config["block_startup"]) return $this->rh->account->Forbidden( "Channels.StartupBlocked" );

  // =================================================================================
  //  ФАЗА 1. Проверка прав доступа
  if (!$principal->IsGrantedTo( "node_admins" ))
    return $rh->account->Forbidden( "Channels.Startup" );

  // =================================================================================

  // 1. delete/build tables & htcron
  $sql = implode("", file( dirname(__FILE__)."/../startup.sql" ));

  $sql = str_replace( "%%PREFIX%%",   $rh->db_prefix, $sql );
  $sql = str_replace( "%%NODE_URL%%", $rh->base_full, $sql );

  $sql = str_replace( "%%KEYCODE%%", $this->npz_keycode, $sql );

  $sql = str_replace( "%%SPEC%%", $this->config["aggregate_cron"], $sql );
  $sql = str_replace( "%%SPEC_ERROR%%", $this->config["aggregate_cron_error"], $sql );

  $sqls = explode( "# %%@%%", $sql );

  //$debug->Trace( $sql );
  foreach( $sqls as $_sql )
  {
    $s = rtrim($_sql, "\n\r ;");
    if (strlen($s)) $db->Execute($s);
  }


  // =================================================================================
  // Парсинг
  $tpl->Assign("Html:TITLE",        $this->module_name );
  $tpl->Assign("Preparsed:TITLE",   "" );
  $tpl->Assign("Preparsed:CONTENT", $TE->Parse("startup.html") );
  $tpl->Assign("Preparsed:TIGHT", 1);
  $tpl->Assign("Preparsed:TIGHT_COMMENTS", 1);

  return GRANTED;

?>