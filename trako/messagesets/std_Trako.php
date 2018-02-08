<?php

  $this->message_set = array(
        
         "Trako.version" => "v.1.pre3",

         "Trako.priority_default"       => 1,
         "Trako.priorities"   => array(
                                        "низкий",
                                        "нормальный",
                                        "высокий",
                                        10 => "критический", 
                                    ),
         "Trako.priority_symbols"   => array(
                                        "&darr;",
                                        "&ndash;",
                                        "&uarr;",
                                        10 => "&uarr;&uarr;", 
                                    ),

         "Trako.consistency"  => array(
                                        "попыток не было",
                                        "не удаётся воспроизвести",
                                        "случайно",
                                        "иногда",
                                        "почти всегда",
                                        "всегда",
                                     ),

         "Trako.severity_classes"  => array(
                                        "any"       => "не определено",
                                        "feature"   => "Предложение",
                                        "incident"  => "Инцидент",
                                        "bug"       => "Ошибка",
                                     ),

         "Trako.severity_values"  => array(
                                        "feature" => array(
                                                 1001 => "идея или мечта",
                                                 2001 => "дополнительное удобство",
                                                 3001 => "дополнительная функция",
                                                10001 => "критично для использования",
                                                      ),
                                        "incident" => array(
                                                 1002 => "идея или мечта",
                                                 2002 => "конкретное предложение",
                                                 3002 => "затрудняет работу",
                                                 4002 => "останавливает работу",
                                                10002 => "блокирует работу многих",
                                                20002 => "опасность для жизни!",
                                                      ),
                                        "bug" => array(
                                                 1003 => "опечатка",
                                                 2003 => "небольшая",
                                                 3003 => "заметная",
                                                 4003 => "серьёзная",
                                                10003 => "всё сломалось",
                                                      ),
                                     ),

         "Trako.access_ranks" => array(
                                        -1 => "Репортёр",
                                        0  => "без ограничения доступа",
                                        GROUPS_LIGHTMEMBERS  => "всем разработчикам",
                                        GROUPS_POWERMEMBERS  => "главным разработчикам",
                                        GROUPS_MODERATORS    => "только менеджерам",
                                     ),

     // forbiddens:
     "Forbidden.Trako.DeniedByActionsAcl" => "Вам запрещён доступ на осуществление действий с этой рубрикой трекера",
     "Forbidden.Trako.DeniedByRank"       => "Ваш ранг в группе недостаточен для произведения операции",
     // notfounds:
     "404.Trako.IssueNotFound" => "В базе данных этого проекта нет записи с указанными номером",

     // actions l12n
     "Trako.actions" => array(
            "issue_log"    => "Лог",
            "issue_view"   => "Просмотр",
            "issue_edit"    => "Правка",
            "issue_edit_"   => "Редактировать запись",
            "issue_delete" => "Удалить",
            "issue_state"  => "Смена состояния",
            "issue_status" => "Смена статуса",
            "issue_assign_self"  => "Назначить себе",
            "issue_assign_to"    => "Назначить...",
            "issue_state_to_opened"  => "Открыть",
            "issue_state_to_solved"  => "&raquo; Решить",
            "issue_state_to_closed"  => "&raquo; Закрыть",
            "issue_state_to_reopened"  => "Открыть повторно",
            "issue_subscribe"    => "Следить",
            "issue_unsubscribe"    => "Не следить",
                             ),
      // panel modes (то, что написано в заголовке таблицы и по чему можно сортировать)
      "Trako.panel_dirs" => array(
            "asc"  => "&uarr;",
            "desc" => "&darr;",
                                  ),
      "Trako.panel_orders" => array(
            "reported" => "Создан",
            "touched"  => "Обновлён",
            "priority" => "P",
            "severity" => "Важность",
            "status"   => "Статус",
            "no"       => "Рапорт",
                                    ),

      "Trako.filter_none" => "(не учитывать)",
      "Trako.filter_developer_none" => "не назначен",
      "Trako.filter_hide" => array(
                                      "opened"   => "открытые рапорты",
                                      "reopened" => "вновь открытые",
                                      "solved"   => "решённые рапорты",
                                      "closed"   => "закрытые рапорты",
                                  ),
      "severity_classes" => array( "bug", "feature", "incident" ),  // allowed classes of severity

   );
?>