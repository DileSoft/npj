<?php

  $TE = &$this->GenerateTemplateEngine();

  // "ACCOUNTEE" handler for Account module

  // =================================================================================
  // Вызов действия
  // пока никакой

  // =================================================================================
  // Парсинг
  $tpl->Assign("Html:TITLE",        $this->module_name );
  $tpl->Assign("Preparsed:TITLE",   "" );
  $tpl->Assign("Preparsed:CONTENT", $TE->Parse("accountee.html") );
  $tpl->Assign("Preparsed:TIGHT", 1);
  $tpl->Assign("Preparsed:TIGHT_COMMENTS", 1);

  return GRANTED;

?>