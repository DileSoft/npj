<?php

// calling from Form::Load( &$data )


  // ��������� �� �������� ���������
  $data_id = $this->DoINSERT( );

  if ($data_id === false)
  $this->rh->debug->Error("&laquo;Unsuccessful insert failed&raquo; alert not implemented",5);
  else
  $this->rh->state->Set("id", $data_id );

  $this->data_id = $data_id;

  // � ����� "��������" � ������
  include( $__dir."_cancel.php" );

?>