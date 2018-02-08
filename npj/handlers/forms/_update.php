<?php

// calling from Form::Load( &$data )

  // обновл€ем по текущему состо€нию
  $this->DoUPDATE( $this->rh->state->Get("id") );

  // а потом отмен€ем к черт€м
  include( $__dir."_cancel.php" );

?>