<?php

  $this->message_set = array(
        
         "Trako.version" => "v.1.pre3",

         "Trako.priority_default"       => 1,
         "Trako.priorities"   => array(
                                        "������",
                                        "����������",
                                        "�������",
                                        10 => "�����������", 
                                    ),
         "Trako.priority_symbols"   => array(
                                        "&darr;",
                                        "&ndash;",
                                        "&uarr;",
                                        10 => "&uarr;&uarr;", 
                                    ),

         "Trako.consistency"  => array(
                                        "������� �� ����",
                                        "�� ������ �������������",
                                        "��������",
                                        "������",
                                        "����� ������",
                                        "������",
                                     ),

         "Trako.severity_classes"  => array(
                                        "any"       => "�� ����������",
                                        "feature"   => "�����������",
                                        "incident"  => "��������",
                                        "bug"       => "������",
                                     ),

         "Trako.severity_values"  => array(
                                        "feature" => array(
                                                 1001 => "���� ��� �����",
                                                 2001 => "�������������� ��������",
                                                 3001 => "�������������� �������",
                                                10001 => "�������� ��� �������������",
                                                      ),
                                        "incident" => array(
                                                 1002 => "���� ��� �����",
                                                 2002 => "���������� �����������",
                                                 3002 => "���������� ������",
                                                 4002 => "������������� ������",
                                                10002 => "��������� ������ ������",
                                                20002 => "��������� ��� �����!",
                                                      ),
                                        "bug" => array(
                                                 1003 => "��������",
                                                 2003 => "���������",
                                                 3003 => "��������",
                                                 4003 => "���������",
                                                10003 => "�� ���������",
                                                      ),
                                     ),

         "Trako.access_ranks" => array(
                                        -1 => "�������",
                                        0  => "��� ����������� �������",
                                        GROUPS_LIGHTMEMBERS  => "���� �������������",
                                        GROUPS_POWERMEMBERS  => "������� �������������",
                                        GROUPS_MODERATORS    => "������ ����������",
                                     ),

     // forbiddens:
     "Forbidden.Trako.DeniedByActionsAcl" => "��� �������� ������ �� ������������� �������� � ���� �������� �������",
     "Forbidden.Trako.DeniedByRank"       => "��� ���� � ������ ������������ ��� ������������ ��������",
     // notfounds:
     "404.Trako.IssueNotFound" => "� ���� ������ ����� ������� ��� ������ � ���������� �������",

     // actions l12n
     "Trako.actions" => array(
            "issue_log"    => "���",
            "issue_view"   => "��������",
            "issue_edit"    => "������",
            "issue_edit_"   => "������������� ������",
            "issue_delete" => "�������",
            "issue_state"  => "����� ���������",
            "issue_status" => "����� �������",
            "issue_assign_self"  => "��������� ����",
            "issue_assign_to"    => "���������...",
            "issue_state_to_opened"  => "�������",
            "issue_state_to_solved"  => "&raquo; ������",
            "issue_state_to_closed"  => "&raquo; �������",
            "issue_state_to_reopened"  => "������� ��������",
            "issue_subscribe"    => "�������",
            "issue_unsubscribe"    => "�� �������",
                             ),
      // panel modes (��, ��� �������� � ��������� ������� � �� ���� ����� �����������)
      "Trako.panel_dirs" => array(
            "asc"  => "&uarr;",
            "desc" => "&darr;",
                                  ),
      "Trako.panel_orders" => array(
            "reported" => "������",
            "touched"  => "�������",
            "priority" => "P",
            "severity" => "��������",
            "status"   => "������",
            "no"       => "������",
                                    ),

      "Trako.filter_none" => "(�� ���������)",
      "Trako.filter_developer_none" => "�� ��������",
      "Trako.filter_hide" => array(
                                      "opened"   => "�������� �������",
                                      "reopened" => "����� ��������",
                                      "solved"   => "�������� �������",
                                      "closed"   => "�������� �������",
                                  ),
      "severity_classes" => array( "bug", "feature", "incident" ),  // allowed classes of severity

   );
?>