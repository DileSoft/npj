<?php

//  from ../config_modules.php
//  $this === $rh

  $this->modules["authorize"] = array( 

        "name"              => "������ ������� �����������",
        "module_dir"        => "authorize/",
        "classname"         => "-", // ������ ���!

        "principal" => "ConfigPrincipal",

        // � ����� ���� ������ ������� ������?
        "node_id" => "yournode",

        // ��������� ����� �������, ���� �� ����� ������������?
        "autoregistration" => true,

        // ����� �������� � ������ (��� �������������� �����������)
        "email_postfix" => "@npj.ru",

        // ��� ConfigPrincipal ����� ������
        "config_user_passwords" => array(
                        // login => md5(pwd)
                        "admin"  => md5("pwd"),
                                        ),

        // ��� HackPrincipal ����� �����, �� �������� ���������� HTTP-�����������
        "hack_principal_host" => "youdomain.ru",
        "hack_principal_port" => "80",
        "hack_principal_path" => "/auth/test",
 
        // ��� DbmsMysqlPrincipal ���������� ��������� ����������� � ���� ������
        "dbms_principal_hostname" => "localhost",
        "dbms_principal_port"     =>  "3306",
        "dbms_principal_username" => "root",
        "dbms_principal_password" => "",
        "dbms_principal_database" => "rad",
        "dbms_principal_table"      => "users",    // �������� ������� �
        "dbms_principal_user_field" => "name",     // ����, � ������� ����� ����� � ������
        "dbms_principal_pass_field" => "password",
        "dbms_principal_encrypt_method" => "md5",  // �������, ����������� � ������ ��� ���������� ��� � ��

        "dbms_principal_hide_user"=> true // �� ������ ���������� ������������ ���-����������

                                  ); // end of module Authorize




?>