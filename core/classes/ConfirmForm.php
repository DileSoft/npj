<?php
/*
    ConfirmForm( &$rh, $handler, $message_set_name="", $tpl_filename="confirm_form.html", $prefix="" )
      - &$rh              -- объект класса RequestHandler, в котором есть всё, что нужно
      - $message_set_name -- имя мессаджсета для мержания перед парсингом конфирмоформы
      - $handler          -- название обработчика формы (хранится в handlers/confirm/$handler.php)
      - $tpl_filename     -- имя файла в каталоге шаблонов, где лежит постановка формы
      - $prefix           -- префикс GET-параметров

  ---------

  * CreateLink( &$state, $method=MSS_GET ) -- создаёт URL для помещения в <a href=...> для вызова формы подтверждения
      - $state  -- состояние, которое должно быть сохранено при переходе
      - $method -- способ, которым создаётся ссылка (??? MSS_POST пока не реализован)
  * ParseConfirm() -- парсинг шаблона формы подтверждения в строку для вывода
  * ParseResult( $result = "" ) -- парсинг шаблона результата подтверждения
      - $result -- если установлен в true/1 -- действие совершено успешно, 
                   иначе по какой-то причине не вышло
      - если в message_set есть ConfirmForm.TplBonus, то парсит этот бонус и присовокупивает к результату
  * Handle() -- обработчик событий -- в том числе вызывает Parse*
      - на событие нажатой кнопки в форме вызывает скрипт 
        в каталоге handlers/confirm/$handler.php
  
  // Основные свойства
  * $this->success = true -- успешность подтверждения, 
                             устанавливается в скрипте-обработчике

=============================================================== v.3 (Kuso)
*/

class ConfirmForm
{
   var $rh;
   var $handler;
   var $tpl_file_name;
   var $message_set_name;
   var $prefix;
   var $success;

   function ConfirmForm( &$rh, $handler, $message_set_name="", $tpl_filename="confirm_form.html",  $prefix="" )
   {
     $this->rh = &$rh;
     $this->handler = $handler;
     $this->tpl_filename = &$tpl_filename;
     $this->message_set_name = $message_set_name;
     $this->prefix = $prefix;
     $this->success = true;
   }

   function CreateLink( &$state, $method=MSS_GET )
   {
     if ($method == MSS_POST) $this->rh->debug->Error( "ConfirmForm: Quickpostlings not implemented yet" );
     return $state->Plus( $this->prefix."confirm", $this->handler, $method );
   }

   function ParseConfirm()
   {
     $tpl = &$this->rh->tpl;
     $rh  = &$this->rh;

     $tpl->LoadDomain( array(
            "Form"  => $this->rh->state->FormStart( MSS_POST, $this->rh->url, 
                                                    "><input type=\"hidden\" name=\"confirm\" value=\"".$this->handler."\" /" ), // !!!! checkidout
            "/Form" => $this->rh->state->FormEnd(),
                     )      );

     if ($this->message_set_name) $tpl->MergeMessageSet( $this->message_set_name );
     if ($rh->skin) $tpl->theme = $rh->theme;
     $res = $tpl->Parse( $this->tpl_filename.":Confirm" );
     if ($rh->skin) $tpl->theme = $rh->skin;
     return $res;
   }

   function ParseResult( $result = "" )
   {
     $tpl = &$this->rh->tpl;
     $rh  = &$this->rh;
     if ($this->message_set_name) $tpl->MergeMessageSet( $this->message_set_name );
     if (!$result) $result = $this->success?"Granted":"Denied";
     if ($rh->skin) $tpl->theme = $rh->theme;
     $res = $tpl->Parse( $this->tpl_filename.":".$result );
     if ($tpl->message_set["ConfirmForm.TplBonus"])
      $res.= $tpl->Parse( $tpl->message_set["ConfirmForm.TplBonus"] );
     if ($rh->skin) $tpl->theme = $rh->skin;
     return $res;
   }

   function Handle()
   {
     if ($_POST[ $this->prefix."confirm" ] == $this->handler)
     {
      $tpl = &$this->rh->tpl;
      $rh  = &$this->rh;
      if ($this->message_set_name) $tpl->MergeMessageSet( $this->message_set_name );
      if ($rh->skin) $tpl->theme = $rh->theme;
        $this->success = false;

        $__dir          = $this->rh->handlers_dir."confirm/";
        $__fullfilename = $__dir.$this->handler.".php";
        $this->rh->debug->Trace("Launching confirmation handler: ".$__fullfilename);
        if (!file_exists($__fullfilename)) $this->rh->debug->Error("Unknown confirmation handler {".$__fullfilename."}!", 3);
        $output = include($__fullfilename);
        if ($output===false) $debug->Error("Problems (file: ".__FILE__.", line: ".__LINE__."): ".ob_get_contents());
       if ($rh->skin) $tpl->theme = $rh->skin;

        $this->rh->state->Set( $this->prefix."confirm" , $this->handler.
                               ($this->success?"_granted":"_denied") );   // ??? kuso: it seems suspicious.
        $this->rh->Redirect( $this->rh->Href($this->rh->url) );
     }
     else
     if ($_GET[ $this->prefix."confirm" ] == $this->handler."_granted")
        return $this->ParseResult( "Granted" );
     else
     if ($_GET[ $this->prefix."confirm" ] == $this->handler."_denied")
        return $this->ParseResult( "Denied" );
     else
     if ($_GET[ $this->prefix."confirm" ] == $this->handler)
        return $this->ParseConfirm();
     else return false;
   }

// EOC{ ConfirmForm } 
}



?>