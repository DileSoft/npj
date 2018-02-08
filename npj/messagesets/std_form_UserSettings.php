<?php

  $this->message_set = array(

     "Form.more_options" =>   "Прочие настройки",
     "Form.user_menu"    =>   "Отображать Ваше персональное меню",
     "Form.novice_panel" =>   "Отображать панель с&nbsp;подробным описанием функций",
     "Form.double_click" =>   "Редактирование документов двойным щелчком мыши",
     "Form.edit_simple"  =>   "Редактирование документов в упрощённой форме",
     "Form.sodynamic_off"  =>   "Не давать выбирать редактор при создании записи",

     "Form.keywords_auto"         =>    "Как показывать содержимое рубрик",
     "Form.keywords_auto.Preface" =>    "Лучше всего выбрать из списка:",
     "Form.keywords_auto.Data" => array (
                                            "Nothing"             => "Ничего не показывать",
                                            "Feed"                => "Лента сообщений, касающихся этой рубрики",
                                            "ClusterFacet"        => "Список сообщений и документов, касающихся рубрики",
                                            "KeywordsClusterTree" => "Все подрубрики, в которых есть сообщения/документы", 
                                        ),

     "Form.classification" =>   "Интерфейс рубрикации",
     "Form.classification.Data" =>   array(
                                          0 => "по умолчанию",
                                          1 => "текстовое поле + список ключевых слов",
                                          2 => "дерево с чекбоксами (Rubrika powered)",
                                          3 => "фасетная классификация (Rubrika powered)",
                                          ),
     
     "Form.skin_override"                  => "Оформление чужих журналов",
     "Form.group_versions_override"        => "Принудительная группировка версий",
     "Form.group_versions_override.Data"   => array( -1 => "по умолчанию", 0=> "принудительно полный список", 1=> "принудительно группировать по авторам" ),

     "Form.post_supertag_override"        => "Заголовки в URL сообщений",
     "Form.post_supertag_override.Data"   => array( -1 => "по умолчанию", 0=> "принудительно игнорировать", 1=> "принудительно включать транслитом в URL" ),
     "Form.post_date_override"        => "Дата в URL сообщений",
     "Form.post_date_override.Data"   => array( -1 => "по умолчанию", 0=> "принудительно игнорировать", 1=> "принудительно включать 2004/09/23/.. в URL" ),

     "Form.record_stats"                  => "Панель свойств записи",
     "Form.record_stats.Data"             => array( "где-то видна, где-то нет", "всегда видна", "всегда скрыта" ),

     "Form.comments_more"               => "",
     "Form.comments_always"             => "обсуждаемые записи показывать сразу с комментариями",

     "Form.comments"                  => "Показывать комментарии",
     "Form.comments.Data"             => array( COMMENTS_TREE  => "деревом ограниченной глубины", 
                                                COMMENTS_FULL  => "деревом с развёрнутыми ветвями", 
                                                COMMENTS_PLAIN => "лентой по времени поступления" ),


     "Form._formatting"             => "Предпочитаемый редактор текста",
     "Form._formatting.Data" => array( "wacko"=>"Вики/вака-разметка", 
                                       "simplebr"=>"Только переносы строк", 
                                       "rawhtml"=>"Наглядный редактор в стиле MSWord" ),

     "Form._notify_comments"       => "Комментирование ваших записей",
     "Form._notify_comments.Data"  => array(  "запрещено", "разрешено",
                                              "разрешено и вы оповещаетесь о новых комментариях", ),

     "Form._replication_allowed"            => "Настройки репликации",
     "Form._replication_allowed.Data"       => array(
                                             "Разрешать копировать ваши записи на другие сервера",
                                             "Запретить такую репликацию",
                                                   ),

     "Form._personal_page_size"           => "Записей в странице журнала",
     "Form._friends_page_size"            => "Записей в странице ленты друзей",
     "Form._recentchanges_size"           => "Строк в Последних Изменениях",

     "Form.password" => "Сменить пароль",
     "Form.freeze"   => "Заморозить",

     "Form._Name"             => "Настройки пользователя",
     "Form._Group.0"          => "Настройки вашего журнала", 
     "Form._Group.1"          => "Интерфейс журналов в целом", 
     "Form._Group.2"          => "Управление Вашим доступом",
     "Form._Group.3"          => "Количество элементов на одной странице",
   );

?>
