<?php

//  from ../config_modules.php

  $this->modules["trako"] = array(

        "subspace"          => "trako", // connecting as a subspace

        "name"              => "Bug/issue/incident tracker TRAKO",
        "module_dir"        => "trako/",
        "messageset_prefix" => "Trako",
        "classname"         => "ModuleTrako",

        "is_granted_to"     => array( "groups" => 1, ),

        // Trako custom configuration:

        "statuses"          => array(
                                   // opened
                                   "new"      => "Новый",
                                   "info"     => "Информация",
                                   "agreed"   => "Признан",
                                   "assigned" => "Назначен",
                                   "next"     => "Ближайшие&nbsp;планы",

                                   // frozen
                                   "solved"   => "Решён",
                                   "closed"   => "Закрыт",
                                   "notabug"  => "Не ошибка, а фича",
                                   "wontfix"  => "Не планируется исправлять",
                                   "dupe"     => "Дубликат",
                                    ),

        "status_filter"         => array(
                                          "*-new", "*-info", "*-agreed", "*-assigned", "*-next",
                                          "solved-*", "closed-*",
                                        ),
        "hide_state_filter"     => array( "solved", "closed" ),

        "statuses_sort_weight"  => array(           // sort_weight = (state.sort_weight + status.sort_weight)
                                  "new"      => 5,
                                  "info"     => 4,
                                  "agreed"   => 3,
                                  "assigned" => 2,
                                  "next"     => 1,
                                    ),

        "security"          => array(
                                  "view"        => 0,  //  numbers values:
                                  "comments"    => 0,  //  0=anyone
                                  "private"     => 10, //  5=GROUPS_LIGHTMEMBERS lesser developer
                                  "edit"        => 5,  // 10=GROUPS_POWERMEMBERS greater developer
                                  "priority"    => 10, // 20=GROUPS_MODERATORS   manager
                                  "assign_self" => 5, 
                                  "assign_to"   => 10,
                                  "status"      => 10,
                                  "delete"      => 10,
                                  "subscribe"   => 0,
                                  "developer"   => 5,   // can be assigned to
                                    ),
        "security_for_reporter"    => array( "edit"    => 1, // yes, they can.
                                           ),

        "default_state"     => "opened",
        "states"            => array(
                 "opened"   => array( // statuses for state must not include statuses named as any of other state. 
                                      // But they could include status named exactly as current state.
                                      "name"      => "Открыт",
                                      "sort_weight" => 500,
                                      "statuses"  => array( "new", "info", "agreed", "assigned", 
                                                            "next", ),
                                      "default_status"  => "new",
                                      "assigned_status" => "assigned",
                                      "to"        => array(
                                                        "solved" => array( 5 ),      // lesser developer+
                                                        "closed" => array( -1, 20 ), // managers, reporter
                                                          ),
                                    ),
                 "reopened" => array(
                                      "name"      => "Открыт повторно",
                                      "sort_weight" => 500,
                                      "statuses"  => array( "new", "info", "agreed", "assigned", 
                                                            "next", ),
                                      "default_status"  => "info",
                                      "assigned_status" => "assigned",
                                      "to"        => array(
                                                        "solved" => array( 5 ),      // lesser developer+
                                                        "closed" => array( -1, 20 ), // managers, reporter
                                                          ),
                                    ),
                 "solved"   => array(
                                      "name"      => "Решён",
                                      "sort_weight" => 200,
                                      "statuses"  => array( "solved", "notabug", "wontfix", "dupe" ),
                                      "default_status" => "solved",
                                      "auto_assign"    => 2, // STRONG
                                      "block"     => "*",
                                      "to"        => array(
                                                        "reopened" => array( -1, 5 ),  // lesser developer+, reporter
                                                        "closed"   => array( -1, 20 ), // managers, reporter
                                                          ),
                                    ),
                 "closed"   => array(
                                      "name"      => "Закрыт",
                                      "sort_weight" => 100,
                                      "statuses"  => array( "closed", "notabug", "wontfix", "dupe" ),
                                      "default_status" => "closed",
                                      "auto_assign"    => 1, // WEAK
                                      "block"     => "*", // means everything. anything else is not implemented yet.
                                      "to"        => array(
                                                        "reopened" => array( -1, 5 ),      // lesser developer+, reporter
                                                          ),
                                    ),
                                    ), // end of all states

      "severity_classes" => array( "bug", "feature", "incident" ),  // allowed classes of severity
                                  
                                  ); // end of module "trako"






?>