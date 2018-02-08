<?php
/*
    ModuleNotify_*( &$notify_iteration ) -- Нотифиер

  ---------

  * SendLetter( $event ) -- отправить письмо по случаю события. 
                            письмо содержит важные ключи:
                             * "letter_subject"    \
                             * "letter_abstract"    } - три степени описания события
                             * "letter_body"       / 
                             * "letter_event"      -> очеловеченный "event"
                             * "letter_prefix"     -> немного про то, что за тип события
                             * "letter_subscriber" -> структура подписчика
                             * "letter_files"      -> массив вида { name => file } or {file}
                             * "letter_raw"        -> не парсить шаблон тела письма,
                                                      а просто послать то, что в этом поле
                                                      (в том числе для высылки уведомлений)
                             * "event"
                             * "created_datetime"

  // Свойства и константы

=============================================================== v.0 (KusoMendokusee)
*/
class ModuleNotify_email extends ModuleNotify_log
{
  function _SendLetter( $event )
  {
    $subscriber = $event["letter_subscriber"];
    $rh = &$this->rh;
    $tpl = &$rh->tpl;

    // 1. Создаём объект почты
    $this->rh->UseLib("HtmlMimeMail2");

    $mail = &new HtmlMimeMail2();
    $mail->setCrlf("\n");

    // 2. От кого отправляем и кому
    $from = $rh->project_name." <".$rh->admin_mail.">";
    $to   = "<".$subscriber["email"].">";

    // 3. Какой у нас код конфирма?
    $confirm = md5( $subscriber["email"]. $rh->magic_word );
    $email   = str_replace("@", "%40",$subscriber["email"]);

    // 4. патчим все body && prefix
    // -- вроде не нужно

    // 5. Прикрепляем файлы, если есть
    if ($event["letter_files"])
    foreach( $event["letter_files"] as $name=>$file )
    {
      if ((int)$name == $name) $name = preg_replace("/^.*[\/\\\\](.*?)$/i", "$1", $file);

      $file_name = $_SERVER["DOCUMENT_ROOT"]."/".$this->rh->base_url.$file;
      $fp = fopen( $file_name, "rb");
      while(!feof($fp))
      {
          $cont.= fread($fp,1024);
      }
      fclose( $fp );
      $mail->addAttachment($cont, $name);
    }

    // 6. Строим шапку и тело письма
     $tpl->LoadDomain( $event );
     $tpl->Assign("Notify:Email",   $email   );
     $tpl->Assign("Notify:Confirm", $confirm );
     $subject = str_replace("\n","",$tpl->Format($event["letter_prefix"], "html2text"));
     if (!$event["letter_raw"])
        $raw    = $tpl->Parse( $this->notify->tpl_prefix."notifier_email.html:Raw" );
     else
     {
        $raw    = str_replace("[email]", $email, 
                  str_replace("[confirm]", $confirm, 
                    $event["letter_raw"] ) );
     }

    // 7. Отправляем
     $_html = $raw; 
     $_text = $tpl->Format( $_html, "html2text" );
     $mail->setHtml($_html, $_text);
     $mail->setFrom($from);
     $mail->setSubject($subject);
     $mail->buildMessage($tpl->message_set["Encodings"]);
     $result = $mail->send(array($to), "mail"); 

    return true;

  }

// EOC { ModuleNotify_email }
}


?>