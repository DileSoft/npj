<?php

// Инициализация структуры принципала вместо того, чтобы загружать её из БД
  $rh = &$this;
  $ms = &$this->tpl->message_set;

  $p->data = array(
        "user_id" => 1,
        "id"      => 1,
        "login" => "guest",
        "node_id" => $rh->node_name,
        "user_name" => $ms["Guest.UserName"],
        "owner_user_id" => 0,
        "account_type" => 0,
        "alive" => 1,
        "password" => "",
        "_formatting" => "wacko",
        "__roles" => "user",
        "last_login_datetime" => date( "Y-m-d H:i:s" ),
        "last_logout_datetime" => date( "Y-m-d H:i:s" ),
        "login_cookie" => "",
        "_pic_id" => 0,
        "theme" => $rh->theme,
        "lang"  => $rh->lang,

        // defaults for more & advanced
        "more"  => "double_click=1\nedit_simple=1\nrecord_stats=0\ncomments=0\ncomments_always=1",
        "advanced" => "typografica=1",

        "template_announce" => "Анонс документа: {subject}",                        // avail: tag
        "template_digest"   => "Дайджест {npj} за период с {from} по {to}", // avail: subject, tag


        "_recentchanges_size" => 25,
        "_notify_comments"    => 0,
        "_replication_allowed"=> 0,
        "_friends_page_size"  => 25,
        "_personal_page_size" => 25,
                  );
  $p->data["options"] = $p->DeComposeOptions( $p->data["more"] );

  $p->data["user_menu"] = array(
      array( "user_id" => 1, "item_id" => 0, "pos" => 0,
             "title" => "Регистрация",
             "npj_address" => "registration@",
           ),
      array( "user_id" => 1, "item_id" => 0, "pos" => 0,
             "title" => "Все пользователи",
             "npj_address" => "node@:users",
           ),
      array( "user_id" => 1, "item_id" => 0, "pos" => 0,
             "title" => "Реестр сообществ",
             "npj_address" => "node@:communities",
           ),
                                );

?>