<?php

// calling from Form::Load( &$data )

  // ��������� �� �������� ���������
  $hoorray = $this->DoSELECT( $this->rh->state->Get("id") );

  if ($hoorray === false)
  $this->rh->debug->Error("&laquo;Unsuccessful select failed&raquo; alert not implemented",5);

  // � ����� ������� ����������.
?>