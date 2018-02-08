<?php

  $rh = &$this->rh;
  $db = &$this->rh->db;
  $debug = &$this->rh->debug;

  $sql = "select have_big, have_small from ".$rh->db_prefix."userpics where pic_id=".(1*$rh->object->params[2]);
  $rs = $db->Execute( $sql );
  if ($rs->RecordCount() > 0)
  {
    // нужно теперь ещё удалить картинку к чертям
    $dir = $_SERVER["DOCUMENT_ROOT"].$rh->user_pictures_dir;
    $filename1 = $dir.$rh->object->data["user_id"]."_big_".(1*$rh->object->params[2]).$rs->fields["have_big"];
    $filename2 = $dir.$rh->object->data["user_id"]."_small_".(1*$rh->object->params[2]).$rs->fields["have_small"];
    $res = 1;
    if ($rs->fields["have_big"]) $res &= unlink( $filename1 );
    if ($rs->fields["have_small"]) $res &= unlink( $filename2 );

    if ($res) // только если картинки удалились, можно идти дальше
    {
      $sql = "delete from ".$rh->db_prefix."userpics where pic_id=".(1*$rh->object->params[2]);
      $db->Execute( $sql );
    }

    $this->success = $res;
  
  }

?>