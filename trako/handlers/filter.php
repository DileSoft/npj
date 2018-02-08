<?php

  $TE = &$this->GenerateTemplateEngine( $this->config["template_engine"] );

  // "FILTER" handler

  $section = &new NpjObject( &$rh, $this->object->npj_account.":".$this->object->subspace );
  if ($section->Load(2) == NOT_EXIST)
  {
    $section = &new NpjObject( &$rh, $this->object->npj_account.":" );
    $section->Load(2);
  }

  $account = &new NpjObject( &$rh, $this->object->npj_account );
  $account->Load(2);

  // =================================================================================
  // Вызов действий
  $params   = array( "show_filter" => 1, "show_panel"=>1 );
  $action1  = $this->Action("filterpanel", $params, &$principal );

  // =================================================================================
  // Парсинг
  $tpl->Assign("Html:TITLE",        $this->module_name.": ".$tpl->GetValue("Preparsed:TITLE") );
  $tpl->Assign("Preparsed:TITLE",   "" );
  $tpl->Assign("Preparsed:TIGHT", 1);
  $tpl->Assign("Preparsed:TIGHT_COMMENTS", 1);

  return GRANTED;

?>