<?php

// calling from Form::Load( &$data )

  // ��������� �� �������� ���������
  $this->DoUPDATE( $this->rh->state->Get("id") );

  // � ����� �������� � ������
  include( $__dir."_cancel.php" );

?>