<?php

  $TE = &$this->GenerateTemplateEngine();

  // "ANOTHER" handler for Subspace module

  // =================================================================================
  // Вызов действия
  // пока никакой

  // =================================================================================
  // Парсинг
  $tpl->Assign("Html:TITLE",        $this->module_name );
  $tpl->Assign("Preparsed:TITLE",   "" );
  $tpl->Assign("Preparsed:CONTENT", $TE->Parse("another.html") );
  $tpl->Assign("Preparsed:TIGHT", 1);
  $tpl->Assign("Preparsed:TIGHT_COMMENTS", 1);

  return GRANTED;

?>