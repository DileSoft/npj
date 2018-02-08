<?php

  $this->message_set = array(

    "Form.login" => "НПЖ-имя канала",
    "Form.login.Desc"    => "оно станет основной частью его Нпж-адреса",
    "Form.login.Help"    => "Адресное имя может состоять только из английских букв в нижнем регистре и цифр (плюс дефис)",
   
    "Form.user_name" => "Название канала",
    "Form.bio"       => "Краткое описание",

    "Form.template_subject"   => "Шаблон заголовка",
    "Form.template_body"      => "Шаблон тела страницы",
    "Form.template_body_post" => "Шаблон для лент",
    "Form.template_body.Desc"      => "При оформлении шаблонов пользуйтесь <br /><b>".
                                      "{subject}, {body}, {author}<br />".
                                      "{dt}, {d}, {m}, {y}, {h}, {i}, {s}, <br />".
                                      "</b> ".
                                      "(и другими: {description}, {original}, {filename}, {from}, {cc}, etc.)",
    "Form.template_body_post.Desc" => "Если оставите пустым, то в лентах будет всё то же, что и при просмотре одной записи",

    "Form.account_class" => "",

    "Form._Name"      => "Создание/редактирование нового канала",
    "Form._Group.common"  => "Параметры аккаунта канала",
    "Form._Group.custom"  => "Параметры источника канала",
    "Form._Group.tpls"    => "Шаблоны вывода",

    // ---------------------- FILE CHANNEL
    "Form.file_rel_dir"       => "Каталог, откуда брать файлы",
    "Form.file_rel_dir.Desc"  => "Укажите каталог относительно &laquo;корня&raquo; проекта ".
                                 "(т.е. того места, где лежит config_tunes.php).".
                                 "<br />Этот агрегатор будет брать файлы из этого каталога, ".
                                 "размещать их в канал и затем удалять.",

    "Form.file_separator"            => "Разделитель заголовка",
    "Form.file_separator.Desc"       => "Разделителем считается первая строка, в которой встретится фрагмент, указанный здесь вами.",

    "Form.file_format"               => "Формат файлов",
    "Form.file_format.Data" => array( "wacko"     => "Вики/вака-разметка", 
                                      "simplebr"  => "Только переносы строк", 
                                      "rawhtml"   => "Полноценный HTML" ),

    // ---------------------- RSS CHANNEL
    "Form.rss_url"       => "URL-адрес RSS-потока",
    "Form.rss_url.Desc"  => "Укажите адрес того RSS-потока, который будет транслироваться в этот канал.",

    "Form.rss_login"     => "Имя пользователя",
    "Form.rss_pwd"       => "Пароль",
    "Form.rss_pwd.Desc"  => "Эти три поля нужно заполнять только в том случае, если RSS-поток требует HTTP-авторизации.",

   );
?>