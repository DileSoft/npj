<?php

  $this->message_set = array(
     "Form.formatting"             => "������ ������",
     "Form.formatting.Data" => array( "wacko"=>"����/����-��������", 
                                       "simplebr"=>"������ �������� �����", 
                                       "rawhtml"=>"����� HTML" ),

     "Form.user_name" => "����� �����������",
     "Form.subject"   => "���������",
     "Form.body" => "����� �����������",

     "Form.pic_id"          => "��� ������",
     "Form.pic_id.Desc"     => (($this->config->principal->data["node_id"] == $this->config->node_name )
                                  ?("�� ������ �������� ".$this->config->Link(
                                    $this->config->principal->data["login"]."/profile/pictures",
                                    "������ ����� ��������")):""),

     "Form.subscription"   => "����������� ��:",
     "Form.subscription_tree"      => "��� ����������� ����������� �&nbsp;���� ������",
     "Form.subscription_childs"    => "��� ���������, ���������� ������ �����������",
     "Form.subscription.Desc"   => "��� ������ ����� �� ��� ����������� � ��� ������ ��� �� ����� ".
                                   "(���� �� � ������� � �������, �������)",

     "Form._Name"             => "�������� �����������",
     "Form._Group.0"          => "���������� � ��� (�������, �������)", 
     "Form._Group.1"          => "�����������",
   );

?>
