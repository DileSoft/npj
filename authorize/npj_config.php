<?php

//  from ../config_modules.php
//  $this === $rh

  $this->modules["authorize"] = array( 

        "name"              => "Модуль внешней авторизации",
        "module_dir"        => "authorize/",
        "classname"         => "-", // класса нет!

        "principal" => "ConfigPrincipal",

        // в каком узле искать учётные записи?
        "node_id" => "yournode",

        // создавать новый аккаунт, если не нашли существующий?
        "autoregistration" => true,

        // какой постфикс у емайла (для корпоративного пользования)
        "email_postfix" => "@npj.ru",

        // для ConfigPrincipal нужны пароли
        "config_user_passwords" => array(
                        // login => md5(pwd)
                        "admin"  => md5("pwd"),
                                        ),

        // для HackPrincipal нужен адрес, по которому происходит HTTP-авторизация
        "hack_principal_host" => "youdomain.ru",
        "hack_principal_port" => "80",
        "hack_principal_path" => "/auth/test",
 
        // для DbmsMysqlPrincipal необходимы параметры подключения к базе данных
        "dbms_principal_hostname" => "localhost",
        "dbms_principal_port"     =>  "3306",
        "dbms_principal_username" => "root",
        "dbms_principal_password" => "",
        "dbms_principal_database" => "rad",
        "dbms_principal_table"      => "users",    // название таблицы и
        "dbms_principal_user_field" => "name",     // поля, в которых лежат логин и пароль
        "dbms_principal_pass_field" => "password",
        "dbms_principal_encrypt_method" => "md5",  // функция, применяемая к паролю при сохранении его в БД

        "dbms_principal_hide_user"=> true // не давать логиниться стандартными НПЖ-средствами

                                  ); // end of module Authorize




?>