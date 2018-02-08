<?php

  $TE = &$this->GenerateTemplateEngine( $this->config["template_engine"] );

  // "DEFAULT" handler

  $section = &new NpjObject( &$rh, $this->object->npj_account.":".$this->object->subspace );
  if ($section->Load(2) == NOT_EXIST)
  {
    $section = &new NpjObject( &$rh, $this->object->npj_account.":" );
    $section->Load(2);
  }

  $account = &new NpjObject( &$rh, $this->object->npj_account );
  $account->Load(2);


  // =================================================================================
  //  ФАЗА 1. Проверка прав доступа
  /*
      Access control is "action_panel" driven.

  if (!$section->HasAccess( $principal, "acl", "actions" ))
    return $section->Forbidden( "Trako.DeniedByActionsAcl" );
  */

  // =================================================================================
  // Вызов действия
  $params = array("show_panel"=>2);
  $action = $this->Action("filterpanel", $params, &$principal );

  // =================================================================================
  // Парсинг
  $tpl->Assign("Html:TITLE",        $this->module_name.": ".$tpl->GetValue("Preparsed:TITLE") );
  $tpl->Assign("Preparsed:TITLE",   "" );
  $tpl->Assign("Preparsed:CONTENT", $tpl->GetValue("Preparsed:CONTENT") );
  $tpl->Assign("Preparsed:TIGHT", 1);
  $tpl->Assign("Preparsed:TIGHT_COMMENTS", 1);

  return GRANTED;

?>