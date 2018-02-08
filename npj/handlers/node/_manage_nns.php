<?php

  /*
  Что тут бывает?
    * кнопка присоединиться/обновить
    * (потом) кнопка удалить
    * галочка "да, уведомлять NNS-сервер об изменении свойств узла"
  */

  $tpl->MergeMessageSet($rh->message_set."_nns");

  // обработчик формы
  include( $dir."/!form_nns.php" );
  if (!isset($_POST["__form_present"])) 
  { 
    $form->ResetSession();
  }

  $debug->Milestone( "Starting form handler" );

  $tpl->theme = $rh->theme;
  $result= $form->Handle();
  $tpl->theme = $rh->skin;
  if ($form->success)
  {
   $res = include( $rh->handlers_dir."nns/join.php" );
   if ($res)
   {
    if ($form->hash["options"]->data[0])
    {
     $rs = $db->Execute("SELECT * FROM ".$rh->db_prefix."npz WHERE command='".$rh->base_full."nns/join'");
     if ($rs->RecordCount()<=0)
     {
//      $rs = $db->Execute("DELETE FROM ".$rh->db_prefix."npz WHERE command='".$rh->base_full."nns/join'");
      $rs = $db->Execute("INSERT INTO ".$rh->db_prefix."npz (spec, command, last, chunk, time_last_chunk, state, param) ".
                                                     "VALUES ('30 23 * * *', '".$rh->base_full."nns/join', '1073299946', '-1', '', 0, '')");

      $rs = $db->Execute("DELETE FROM ".$rh->db_prefix."npz WHERE command='".$rh->base_full."repsend'");

      $rs = $db->Execute("INSERT INTO ".$rh->db_prefix."npz (spec, command, last, chunk, time_last_chunk, state, param) VALUES ('* * * * *', '".$rh->base_full."repsend', '1073299946', '-1', '', 0, '1')");

     }
     $tpl->Assign("Preparsed:CONTENT", "Всё прошло успешно.<br /><a href='/".$rh->base_url."manage'>Вернуться</a>");
    }
   }
   else
   {
     $tpl->Assign("Preparsed:CONTENT", "<b>Произошла ошибка.</b><br />".$tpl->message_set[$this->nns_error]."<br /><a href='/".$rh->base_url."manage'>Вернуться</a>");
   }
  }
  else
  if ($result !== false) $tpl->Assign("Preparsed:CONTENT", $result);

?>