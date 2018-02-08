<?php
  // Net Project Redistributable Configuration

  $this->npj_version         = "0.98";                          // ������ �������� ���
  
  $this->principal_profiles  = "profiles"; // ������ �������� ������� ������� �������������
  // directories tuning
  $this->user_pictures_dir = "images/userpics/";
  $this->npj_classes_dir     = "npj/classes/";
  $this->classes_dir         = "npj/classes/";
  $this->principal_dir       = "npj/";
  $this->security_dir        = "npj/security/";
  $this->formatters_dir      = "npj/formatters/";
  $this->formatters_classes_dir = "npj/formatters/classes/";
  $this->handlers_dir        = "npj/handlers/";
  $this->templates_cache_dir = "_templates/";
  $this->messagesets_dir     = "npj/messagesets/";
  $this->npj_actions_dir     = "npj/actions/";
  $this->themes_dir          = "npj/themes/";
     $this->templates_dir       = "templates/";
     $this->templates_magic_dir = "tpl_magic/";
     $this->themes_www_dir      = "npj/themes/";
  $this->db_al_dir           = "ADODBLite";

  //$this->custom_handlers_dir     = "npj/handlers/";
  //$this->custom_actions_dir      = "npj/actions/";

  $this->tpl_magic   = array( "@" => "default", "#" => "custom", 
                              "$" => "safe_edit",
                              "!" => "i18n", "?" => "skip_advanced",
                              "~" => "npj_link",
                              "`" => "pass_thru",
                              "&" => "_manifesto_style", 
                              "=" => "_manifesto_texts",
                               ); // set =NULL if do not want to.

  $this->critical_forms = true; // ��� ����� � ��� -- ��������� � ���� � ��� �� ������ ������ ���������.

  $this->theme_tunings = array(); // ��������� ������� ���/����


  // ��������� �������� ������ ���������, ��������� (refactor !!!)
  $this->default_node_homepage = "homepage";
  $this->default_node_welcome  = "welcome";
  $this->default_content = array( "user", "community", "workgroup" ); // ��������� ��������� � ���������� �����������
  $this->default_acls    = array(
                              // USER 
                              0 =>    array( "banlist" => "",
                                             "read" => "*", "write" => "", "comment" => "*",
                                             "source" => "&", "actions" => "*",
                                             "add" => "", "acl_read" => "", "acl_write" => "", 
                                             "meta_write" => "", "remove" => "" ),
                              // COMMUNITY
                              1 =>    array( "banlist" => "",
                                             "read" => "*", "write" => "&����������", "comment" => "*",
                                             "source" => "&", "actions" => "*",
                                             "add" => "&����������", 
                                             "acl_read" => "&����������", "acl_write" => "&����������", 
                                             "meta_write" => "&����������", "remove" => "&����������" ),
                              // WORKGROUP 
                              2 =>    array( "banlist" => "",
                                             "read" => "*", "write" => "&�������", "comment" => "*",
                                             "source" => "&", "actions" => "*",
                                             "add" => "&�������", 
                                             "acl_read" => "*", "acl_write" => "&���������", 
                                             "meta_write" => "&�������", "remove" => "&���������" ),
                                 );
  $this->groups_presets = array( 2,3,4 );

  // ������������� ��������� ���
  $this->registration_mode       = 2;  // 0 / 1 / 2 (no, pre, free)
  $this->community_creation_mode = 2;  // 0 / 1 / 2 (no, pre, free)
  $this->workgroup_creation_mode = 2;  // 0 / 1 / 2 (no, pre, free)
  $this->recent_changes_limit = 70; // ������� ��������� ��� ��������
  $this->facet_limit   = 150; // ������� ��������� ��� ��������
  $this->feed_dt_limit = 150; // ��� ����� �������� ������������� � ����� "�����������"?
  $this->group_ranks = array( 
    0 => array(                       // for user
              0  => "correspondents",
              10 => "confidents"    ,
              ),
    1 => array(                       // for community
               0 => "requests"       ,
               5 => "lightmembers"   ,
              10 => "powermembers"   ,
              20 => "moderators"     ,
              ),
    2 => array(                       // for workgroup
               0 => "requests"       , 
               5 => "beholders"      , 
              10 => "members"        , 
              20 => "managers"       , 
              ),
                            );

  $this->user_pictures_big_x = 100;
  $this->user_pictures_big_y = 100;
  $this->user_pictures_small_x = 48;
  $this->user_pictures_small_y = 48;

  $this->message_set  = "std";      // ����� ��������� (��� i18n)
  $this->default_theme = "_common"; // "�����" ��-��������� (��� ������ ��, ��� �� ������� � ������� �����

  $this->cookie_prefix = "npj_";                    // ������� ����� � ���� ������/�������
  $this->cookie_expire_days = 365;                  // ������� ������� ���� �������
  $this->rewrite_mode=1;                            // ������������ ��  dirty-urls / mod_rewrite / 404 {0,1,2 �����.}
  
  $this->single_account = 0; // ���� ������ -- �� �������, ��� �� ���� ���� ���� ������ ���� �������.
  
  $this->NPJ_ACTIONS =     
    'directory|users|communities|workgroups|'.
    'tree|keywordstree|clustertree|digesttree|'.
    'changes|recentchanges|journalchanges|clusterchanges|nodechanges|'.
    'recentcomments|recentcommented|noderecentcomments|noderecentcommented|'.
    'index|clusterindex|journalindex|'.
    'toc|tableofcontents|'.
    'keywords|clusterfacet|backlinks|feed|modfeed|moderatefeed|'.
    'search|nodesearch|feedsearch|nodefeedsearch|'.
    'facetfilter|goto|'.
    'pageversions|pageannounces|'.
    'commonusagestats|'.
    'source|'.
    'subscribers|emailsubscribers|'.
    'digest|journaldigest|digests|import|nodes';

  $this->NPJ_FUNCTIONS =     '('.
    $this->NPJ_ACTIONS.'|'.
    'announce|subscribe|'.
    'add_friend|ban|join|filter|groups|show|add|edit|delete|rights|post|diff|manage|profile|settings|info|'.
    'login|registration|forgot|auth|repsend|notify|your|automate|'.
    'skin|print|freeze|unfreeze'.

    '|commentsan'.  // ��� ������ �� ����� � ������������
    '|service'.     // ������ ��� ����������/�����


    '|xport'.     // experimental handler

                             ')';
  $this->NPJ_SPACES         = '(friends|comments|versions|polls|mail|replication|nns)';
  $this->NPJ_ROOT_SPACES    = array();
  $this->NPJ_QUASI_NODES    = array();
  $this->REGEX_NPJ_FUNCTIONS = '/^(.*?)\/'.$this->NPJ_FUNCTIONS.'\/(.*)$/i';
  $this->REGEX_NPJ_SPACES    = '/^(.*)\/'.$this->NPJ_SPACES.'\/(.*?)$/i';

  // ���� ������� �������, ����� ������
  $this->RECORD_ACLS = array(  array( "read", "write", "comment" ),
                               array( "source", "actions" ),
                               array( "meta_write", "add", "remove" ),
                               array( "acl_read", "acl_write"    ),
                               );
  $this->ACLS_PARENT = array ( "source" => "read", "actions"=>"read" ); // ����� acl ������������, ���� ��� �������

  $this->ACLS_ACTIONS_PARAMS = array( "toc"     => array("page", "for"),
                                      "feed"    => array("for"),
                                      /*"facet"   => array("keywords"),*/
                                      "tree"    => array("for", 0),
                                      "changes" => array("for", 0),
                                    );

  // ������������� ������ � ������
  $this->post_access_default = -1; // GROUP_ACCESS_PUBLIC = -1


  $this->keep_alive = 0; // ����� � �������������, ������� ������ �� ��������� ������ ����� ���-�����������.
                         // ���� -- ������ ��������� ��� ����.


  $this->context_params = array(
                        "clusterfacet"   => array( "limit" => 10, ),
                        "clustertree"    => array( ),
                        "clusterchanges" => array( "limit" => 10, ),
                        "toc"            => array( ),
                        "search"         => array( "form" => 1, ),
                        "backlinks"      => array( "limit" => 10, ),
                        "journalchanges"  => array( "limit" => 10, ),

                        );
  $this->action_wrappers = array( "default" => "fieldset",  // default
                                  "div" => "div", "fieldset"=>"fieldset", "include" => "include",
                                  "menu" => "menu" );

  $this->friends_templates = array( "friends", "full", "members", "poloskuns" );

  $this->use_htcron = 1;
  $this->method_mailsend = "mail";

  $this->mail_comment_parent_maxsize = 3; // ������ � �� ������������� HTML ��� ���������, ������� 
                                           // ����� ������� ����������.
                                           // ����� ����� ������� �������
  $this->rss_comment_parent_maxsize = $this->mail_comment_parent_maxsize; 
                                           // ��� �� ����� ������ � ��� RSS

  $this->records_rare = array( "announced_id", "announced_supertag", "announced_title", 
                               "announced_comments", "announced_disallow_comments",
                               "rep_node_id", "replicator_user_id", "rep_original_id",
                               "digest_dtfrom", "digest_dtto", "digest_filter",
                              );
  $this->_records_rare = implode(",", $this->records_rare);

  $this->record_delete_comments = true; // ��� �������� ������ ������� � ����������� � ���
  $this->comments_show_depth    = 3;    // �� ����� ������ ����������� �������� ����������� ����������� � ���������
                                        // � ������, ����� ������� ����������� ��� ���������� ����������
                                        // =0 -- ������ ��������� ����������

  $this->community_filter = true; // in/devnews/by/kuso@npj -> filtering

  $this->workgroups_access   = 20; // 0 -- off, 20 -- managers are owners
  $this->globalgroups_security_rank = 10; // ������� �������� >= rank --  ����� ��� ���� ������ ��������� "������ ��� ���������"

  $this->alert_npjnet = 1; // ���������� � ���, ��� ���� �� �������� ������ ����

  $this->no_actions_in_posts = false; 
  $this->admins_only_console = false; 
  $this->admins_only_documents = false; 
  $this->disable_wikilinks = false;

  $this->admins_delete_records = false;  // ������ ���� ����� ������� ������!

  $this->global_accessgroup_class = ""; // �������� �������� "�������-������", ����� ������������ �����.
                                        // ��� ��� �� �����, ���� �������-������� � ��� ���.

  // ����� ����������� ������������� ������. 
  $this->interface_classification = array(
                                            0=>  "rubrika_tree", // default setting
                                            1=>  "plain",
                                            2=>  "rubrika_tree",
                                            3=>  "rubrika_facet",
                                         );
  $this->keywords_auto_params = array(
            "clusterfacet"        => array( "subject" => 1, "filter" => "both",
                                            "wrapper" => "none", "hide"  => 1, "fullwidth" => 1,
                                            "order" => "subject", "style" => "ul" ),
            "_"                   => array( "wrapper" => "none", "fullwidth"=>1 ),
                                     );

  $this->allow_rawhtml = 1;
  $this->use_htmlarea_as_richedit = false;

?>