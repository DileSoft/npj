<?php

// calling from Form::Load( &$data )


  // вставл€ем по текущему состо€нию
  $data_id = $this->DoINSERT( );

  if ($data_id === false)
  $this->rh->debug->Error("&laquo;Unsuccessful insert failed&raquo; alert not implemented",5);
  else
  $this->rh->state->Set("id", $data_id );

  $this->data_id = $data_id;

  // а потом "отмен€ем" к черт€м
  include( $__dir."_cancel.php" );

?>