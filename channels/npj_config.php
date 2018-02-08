<?php

//  from ../config_modules.php
//  $this === $rh

  $this->modules["channels"] = array( 

        // this module is subspace module                         
        "subspace"          => "channels", // must be strict equal to modules[<SUBSPACE>] subspace
        "subspace_root_only"=> true, // uncomment, if you want to use only module-demo@npj

        "name"              => "Агрегатор (RSS, File, Mailbox)",
        "version"           => "v.0.8",
        "module_dir"        => "channels/",
        "messageset_prefix" => "Channels",

        "classname"         => "ModuleChannels",

        "account_preset_users"    => array(
                                   "domain_type" => 1, /* DOMAIN_DIR_ONLY */
                                   // здесь можно указать и другие умолчательные настройки для таблицы "users"
                                          ),

        "body_post_formatter" => "non_empty_500", // применяется ко всем абстрактам, если указан

        "channels_per_aggregate" => 100,
        "aggregate_timeout"      => 25, // in seconds
        // start-up only:
        "aggregate_cron"         => "1 * * * *",  // each hour, in xx:01
        "aggregate_cron_error"   => "22 4 * * *", // each day,  in 04:22
        "block_startup"          => 0, // set this to "1" after startup


        "security_acl"             => "*", // independent security models
        "security_account_classes" => array( "team" => 1, ),

                                  ); 

  // helping module instance for invasion on node-level basis
  $this->modules["channels-integration"] = $this->modules["channels"];
  $this->modules["channels-integration"]["as_foreign"] = array( "rss", "mailbox", "file" );
  unset( $this->modules["channels-integration"]["subspace"] );

?>
