<?php

  $this->message_set = array(
    "Form.login"         => "Адресное имя РГ",
    "Form.login.Desc"    => "оно станет основной частью его Нпж-адреса",
    "Form.login.Help"    => "Адресное имя может состоять только из английских букв в нижнем регистре и цифр",
    "Form.password"      => "Пароль (?)",

    "Form.account_class" => "Вид рабочей группы",
    "Form.parent_id"     => "Принадлежит группе",

    "Form.file_url_prefix"       => "Путь к файлам группы",
    "Form.file_url_prefix.Desc"  => "URL Вашего сайта (или каталога на нём), где Ваша группа планирует размещать изображения",

    "Form.user_name"     => "Название рабочей группы",
    "Form.user_name.Help"    => "Название не может включать в себя точку-с-запятой. Извините.",
    "Form.bio"           => "Описание тематики и задач РГ",
    "Form.website_url"   => "URL официального сайта",
    "Form.website_name"  => "И его название",
    "Form.email"         => "Официальный E-mail",
    "Form.icq_uin"       => "Контактный ICQ №",
    "Form.icq.Help"      => "Номер ICQ &#151; это набор цифр, вы не знали?",
    "Form.birthday"      => "День основания",
    "Form.interests"     => "Аспекты деятельности РГ",
    "Form.interests.Desc"=> "Укажите область деятельности группы, разделяя тезисы запятыми или переводами строки. <br />".
                            "Если Вы собрались написать: <b>Наша группа будет бороться за свободу енотов-луддитов</b>, то лучше это написать в <i>Описание тематики и задач</i>.<br />".
                            "Сюда же больше подойдёт что-то вроде: <b>еноты, биология, зоология</b>",
    "Form.country"       => "Страна",
    "Form.region"        => "Регион/область",
    "Form.city"          => "Город",

    "Form.default_membership"    => "Статус вступающих в РГ",
    "Form.default_membership.Data" => array(
                                 GROUPS_LIGHTMEMBERS  => "наблюдатели",
                                 GROUPS_POWERMEMBERS  => "полноправные члены",
                                   ),
    "Form.post_membership"    => "Кто может публиковать <br />без предварительной модерации",
    "Form.post_membership.Data" => array(
                                 GROUPS_LIGHTMEMBERS  => "наблюдатели",
                                 GROUPS_POWERMEMBERS  => "полноправные члены",
                                 GROUPS_MODERATORS    => "только менеджеры",
                                   ),
    "Form.announce_membership"    => "Кто может публиковать анонсы",
    "Form.announce_membership.Data" => array(
                                 GROUPS_LIGHTMEMBERS  => "наблюдатели ?!",
                                 GROUPS_POWERMEMBERS  => "полноправные члены",
                                 GROUPS_MODERATORS    => "только менеджеры",
                                   ),

    "Form.security_type"    => "Тип рабочей группы",
    "Form.security_type.Data"    => array(
                                 COMMUNITY_OPEN    => "<b>открытого типа</b> (любой может в любой момент вступить в группу)",
                                 COMMUNITY_LIMITED => "<b>ограниченного типа</b> (членом можно стать после утверждения заявки модераторами)",
                                 COMMUNITY_CLOSED  => "закрытая группа (только модератор может добавлять членов)",
                                 COMMUNITY_SECRET  => "секретная группа (страницы рабочей группы никому, кроме его членов, не доступны)",
                                 COMMUNITY_PUBLIC  => "группа публичного типа (не нужно вступать в рабочую группу для публикации сообщений)",
                                         ),

    "Form.journal_name"     => "Заглавие журнала",
    "Form.journal_desc"     => "Девиз журнала <br /> (краткое описание)",

    "Form.advanced_options"   => "Различные настройки",
    "Form.typografica"   => "Корректировать типографику во&nbsp;всех документах журнала",
    "Form.hide_email"    => "Показывать email РГ только команде",
    "Form.group_versions"     => "Группировать версии документов по авторам",

    "Form.advanced_post_options"   => "Формат URL у сообщений",
    "Form.post_supertag"   => "включать заголовок сообщения транслитом",
    "Form.post_date"      => "показывать дату сообщения в URL",

    "Form.template_announce"   => "Шаблон заголовка анонса",
    "Form.template_digest"     => "Шаблон заголовка дайджеста",


    "Form._Name"      => "Создание новой рабочей группы",
    "Form._Group.Users"  => "Адрес и тип рабочей группы",
    "Form._Group.0" => "Статус членов рабочей группы",
    "Form._Group.1"   => "Общие данные",
    "Form._Group.2" => "Информация о журнале группы",
    "Form._Group.3" => "Электронный контакт",
    "Form._Group.4" => "Прочая информация о группе",
    "Form._Group.5"  => "Местонахождение",
    "Form__Group_Users"       => "Адрес штаба группы",
   );

?>