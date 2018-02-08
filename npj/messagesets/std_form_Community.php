<?php

  $this->message_set = array(
    "Form.login"         => "Адресное имя сообщества",
    "Form.login.Desc"    => "оно станет основной частью его Нпж-адреса",
    "Form.login.Help"    => "Адресное имя может состоять только из английских букв в нижнем регистре и цифр",
    "Form.password"      => "Пароль (?)",

    "Form.account_class" => "Вид сообщества",
    "Form.parent_id"     => "Принадлежит группе",

    "Form.file_url_prefix"       => "Путь к файлам сообщества",
    "Form.file_url_prefix.Desc"  => "URL Вашего сайта (или каталога на нём), где Вы планируете размещать изображения",

    "Form.user_name"     => "Название сообщества",
    "Form.user_name.Help"    => "Название не может включать в себя точку-с-запятой. Извините.",
    "Form.bio"           => "Описание тематики",

    "Form.website_url"   => "URL официального сайта",
    "Form.website_name"  => "И его название",
    "Form.email"         => "Официальный E-mail",
    "Form.icq_uin"       => "Контактный ICQ №",
    "Form.icq.Help"      => "Номер ICQ &#151; это набор цифр, вы не знали?",
    "Form.birthday"      => "День основания",
    "Form.interests"     => "Интересы сообщества",
    "Form.interests.Desc"=> "Укажите область интересов сообщества, разделяя тезисы запятыми или переводами строки. <br />".
                            "Если Вы собрались написать: <b>Мы любим оладьи с кленовым сиропом и янтарные проблески душ</b>, то лучше это написать в <i>Описание тематики</i>.<br />".
                            "Сюда же больше подойдёт что-то вроде: <b>тёлки, кефир, любить любовью крепкой</b>",
    "Form.country"       => "Страна",
    "Form.region"        => "Регион/область",
    "Form.city"          => "Город",

    "Form.default_membership"    => "Статус новых членов сообщества",
    "Form.default_membership.Data" => array(
                                 GROUPS_LIGHTMEMBERS  => "ограниченные члены",
                                 GROUPS_POWERMEMBERS  => "полноправные члены",
                                   ),
    "Form.post_membership"    => "Кто может публиковать без предварительной модерации",
    "Form.post_membership.Data" => array(
                                 GROUPS_LIGHTMEMBERS  => "ограниченные члены",
                                 GROUPS_POWERMEMBERS  => "полноправные члены",
                                 GROUPS_MODERATORS    => "только модераторы",
                                   ),
    "Form.announce_membership"    => "Кто может публиковать анонсы",
    "Form.announce_membership.Data" => array(
                                 GROUPS_LIGHTMEMBERS  => "ограниченные члены",
                                 GROUPS_POWERMEMBERS  => "полноправные члены",
                                 GROUPS_MODERATORS    => "только модераторы",
                                   ),

    "Form.security_type"    => "Тип сообщества",
    "Form.security_type.Data"    => array(
                                 COMMUNITY_OPEN    => "<b>открытого типа</b> (любой может в любой момент вступить в сообщество)",
                                 COMMUNITY_LIMITED => "<b>ограниченного типа</b> (членом можно стать после утверждения заявки модераторами)",
                                 COMMUNITY_CLOSED  => "закрытое сообщество (только модератор может добавлять членов)",
                                 COMMUNITY_SECRET  => "секретное сообщество (страница сообщества никому, кроме его членов, не доступна)",
                                 COMMUNITY_PUBLIC  => "сообщество публичного типа (не нужно вступать в сообщество для публикации)",
                                         ),

    "Form.journal_name"     => "Заглавие журнала",
    "Form.journal_desc"     => "Девиз журнала / краткое описание",

    "Form.advanced_options"   => "Различные настройки",
    "Form.typografica"   => "Корректировать типографику во&nbsp;всех документах журнала",
    "Form.hide_email"    => "Показывать email сообщества только полноправным членам",
    "Form.group_versions"     => "Группировать версии документов по авторам",

    "Form.advanced_post_options"   => "Формат URL у сообщений",
    "Form.post_supertag"   => "включать заголовок сообщения транслитом",
    "Form.post_date"      => "показывать дату сообщения в URL",

    "Form.template_announce"   => "Шаблон заголовка анонса",
    "Form.template_digest"     => "Шаблон заголовка дайджеста",


    "Form._Name"      => "Создание нового сообщества",
    "Form._Group.Users"  => "Адрес и тип сообщества",
    "Form._Group.0" => "Статус членов сообщества",
    "Form._Group.1"   => "Общие данные",
    "Form._Group.2" => "Информация о журнале сообщества",
    "Form._Group.3" => "Электронный контакт",
    "Form._Group.4" => "Прочая информация о сообществе",
    "Form._Group.5"  => "Местонахождение",
    "Form__Group_Users"       => "Адрес сообщества",
   );

?>