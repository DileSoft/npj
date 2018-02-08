<?php
  // CORE configuration (there is no changes almost)

  // http/https switch
  $this->scheme = "http";


  //misc
  $this->core_version = "0.3.2";                        // ������ ���� (��������� ����-������)
  $this->debug_level  = 1;                              // �� ����� ������ ������ ������� ���.
  $this->halt_level   = 1;                              // �� ����� ������ ������ ���������� ������
  $this->alert_admin_mail   = "mendokusee@yandex.ru";   // default email maintainer`a ����

  $this->method_mailsend = "mail"; // ��� �������� �����

  // template_engine config
  $this->tpl_no_cache = 0;                          // ���� =1, �� ��� ������ ������� ���������� ����������� ������� � ��������
  $this->tpl_markup_level = 0;                      // ������� �������� �������� ��� �� ������
  $this->tpl_justfix = "#?%!@#";                   // ����������� �������� 
  $this->tpl_prefix  = "{{";                        // ������ ������-����
  $this->tpl_postfix = "}}";                        // ����� ������-����
  $this->tpl_magic   = array( "@" => "default", "#" => "custom",
                              "&" => "_manifesto_style", 
                              "!" => "i18n", "?" => "skip_simple" ); // set =NULL if do not want to.
  
  //engine directories
  $this->engine_dir          = "core/";
  $this->principal_dir       = "core/";
  $this->core_dir            = "core/classes/";
  $this->classes_dir         = "core/classes/";
  $this->security_dir        = "core/security/";
  $this->formatters_dir      = "core/formatters/";
  $this->handlers_dir        = "core/handlers/";
  $this->templates_dir       = "core/templates/";
  $this->templates_magic_dir = "core/tpl_magic/";
  $this->templates_cache_dir = "core/_templates/";
  $this->libraries_dir       = "lib/";
  $this->db_al_dir           = "ADODBLite";

?>