<?php

  $this->message_set = array(
     "Form.edited_datetime.RegexpHelp" =>
        "УВЕДОМЛЕНИЕ О ПЕРЕЗАПИСИ:<br />\n".
        "Эта страница была изменена кем-то еще, пока вы редактировали ее.".
        "<br />\nПожалуйста, скопируйте Ваши изменения и отредактируйте страницу повторно.",

     "Form.subject"       => "Заголовок",
     "Form.subject.Desc"  => "Если вы оставите поле пустым, заголовок сформируется из тага автоматически",
     "Form.tag"            => "Адрес",
     "Form.tag.Exist"      => "Документ с таким адресом уже существует, придумайте другой, получше",
     "Form.tag.RegexpHelp" => "Должно начинаться с буквы и содержать только буквы и цифры.",
     "Form.body"          => "Текст",
//     "Form.body_simpleedit" => "Текст в формате &laquo;Только переносы строк&raquo;",
//     "Form.body_wikiedit" => "Текст в формате &laquo;Вики/вака-разметка&raquo;",
//     "Form.body_richedit" => "Текст в наглядном редакторе в стиле MSWord",

     "Form._none"          => "",
     "Form._none.Desc"     => "Поля заголовка и тела записи блокированы.",

     "Form.formatting"             => "Предпочитаемый редактор текста",
     "Form.formatting.Data" => array( "wacko"=>"Вики/вака-разметка", 
                                      "simplebr"=>"Только переносы строк", 
                                      "rawhtml"=>"Наглядный редактор в стиле MSWord" ),
     "Form.pic_id"          => "Ваш аватар",
     "Form.pic_id.Desc"     => (($this->config->principal->data["node_id"] == $this->config->node_name )
                                  ?("Вы можете изменить ".$this->config->Link(
                                    $this->config->principal->data["login"]."/profile/pictures",
                                    "список ваших картинок")):""),
     "Form.user_datetime"   => "Датировать запись",
     "Form.disallow"        => "Ограничения <br />другим пользователям",
     "Form.disallow_comments"         => "Запретить комментирование",
     "Form.disallow_notify_comments"  => "Не оповещать о поступающих комментариях",
     "Form.disallow_syndicate"        => "Не встраивать в ленты корреспондентов",
     "Form.disallow_replicate"        => "Не позволять переносить на другой узел (реплицировать)",
     "Form.specials"        => "Специальный статус сообщения",
     "Form.is_digest"                  => "Пометить как дайджест",
     "Form.is_keyword"                 => "Включить в список доступных ключевых слов",
     "Form.is_announce"                => "Пометить как анонс",

     "Form.keywords"                => "Ключевые слова / рубрики",
     "Form.keywords.Preface"        => "или добавьте из списка: ",
     "Form.emptylist"   => "ключевых слов не создано",

     "Form.communities"          => "Опубликовать:",
     "Form.communities.LeftSubject"      => "Публиковать:",
     "Form.communities.RightSubject"      => "Не публиковать:",
     "Form.communities.Presets"          => array( 0  => "Только в своём журнале",
                                                   -10 => "Также в сообществах...",
                                              ), 

     "Form.groups"          => "Разрешить доступ",
     "Form.groups.LeftSubject"      => "Можно смотреть:",
     "Form.groups.RightSubject"      => "Нельзя смотреть:",
     "Form.groups.Presets"          => array( -1  => "Всем (публичное сообщение)", 
                                               0  => "Никому (приватное)",
                                              -2  => "Всем моим конфидентам",
                                              -3  => "Только в сообществах",
                                              -10 => "Отдельным группам...",
                                              ), 
     "Form.groups.RadioPreset" => -3,

     "Form.read"              => "На чтение",
     "Form.read.Desc"         => "Этот список также ограничивает все остальные",
     "Form.write"             => "На запись",
     "Form.comment"           => "На комментирование",

     "Form._Name"             => "Запись журнала",
     "Form._Group.body"          => "Тело записи", 
     "Form._Group.ref"          => "Классификация записи",
     "Form._Group.options"          => "Опции и настройки",
     "Form._Group.panels"          => "Панели с дополнительной информацией",
     "Form._Group.access"          => "Управление доступом",

     "Form._Group.announces"          => "Параметры анонса",
     "Form.announce_after"            => "После сохранения перейти к созданию анонса",
     "Form.announced_title"           => "Подпись на ссылке",
     "Form.announced_supertag"        => "Анонсируемый документ",
     "Form.announce_in"               => "Анонсировать в журналах:",
     "Form.announce_in.LeftSubject"      => "Анонсировать:",
     "Form.announce_in.RightSubject"      => "Не анонсировать:",
     "Form.announce_in.Presets"          => array( 0  => "Только в своём журнале",
                                                   -10 => "Также в сообществах...",
                                              ), 

   
     "Form.default_show_parameter"       => 
                                            "<div style='color:#999999; font-weight:normal'>Отображаются<br /> справа от записи,<br /> под панелью <br />контекстного меню</div>",
     "Form.default_show_parameter.Desc"  => "Это для очень, очень крутых",
     "Form.default_show_parameter.AddData" => array("автор может установить своё действие", 
                                                    "автор может заменить действие владельца",
                                                    "автор ничего не может с&nbsp;этим поделать"),
     "Form.default_show_parameter.Data"    => array(
                        "(по умолчанию)",
                        "clusterfacet"   => "Как ключслово",
                        "clustertree"    => "Поддерево документа",
                        "clusterchanges" => "Изменения в кластере",
                        "toc"            => "Оглавление документа",
                        "search"         => "Форма поиска",
                        "backlinks"      => "Ссылки на этот документ",
                        "journalchanges" => "Journal Recent Changes",
                        "calendar"       => "Календарь текущего месяца",
                                                   ),

     "Form.Digest.Subject" => "Заголовок",                                               
     "Form.Digest.DT"      => "Время создания",                                               
     "Form.Digest.Author"  => "Автор",                                               
     "Form.Digest.Body"    => "Текст",                                               

   
   );

?>
