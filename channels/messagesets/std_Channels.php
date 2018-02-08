<?php

  $this->message_set = array(

   "Channels.StartupDone"     => "Агрегатор успешно инициализирован",
   "Channels.StartupDoneMore" => "Это означает, что все сервисы агрегатора готовы к работе и он настроил узел для автоматической периодической агрегации соответственно конфигурации",

   "Channels.Title:New"  => "Создание нового канала",
   "Channels.Title:Edit" => "Редактирование настроек канала",

   "Channels.types" => array(
          "file"    => "Агрегатор потока файлов",
          "rss"     => "Канал из потока RSS",
          "mailbox" => "Сборщик писем E-mail",
                            ),

   // Forbiddens
   "Forbidden.Channels.Startup" => "Только администраторы узла могут инициализировать модуль",
   "Forbidden.Channels.StartupBlocked" => "Повторная инициализация модуля запрещена",
   "Forbidden.Channels.New"     => "У вас недостаточно прав для создания каналов агрегации",
   "Forbidden.Channels.Edit"    => "Редактировать настройки канала может только &laquo;управляющий каналом&raquo;",

   // 404
   "404.Channels.NotFound" => "Что-то не найдено",

   );
?>