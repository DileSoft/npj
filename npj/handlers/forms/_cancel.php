<?php

// calling from Form::Load( &$data )

  $this->ResetSession();

  if (isset($this->form_config["redirect"]))
    $this->rh->Redirect( $this->rh->Href( $this->form_config["redirect"] ) );

?>