<?php

  $this->message_set = array(
     "Form.edited_datetime.RegexpHelp" =>
        "����������� � ����������:<br />\n".
        "��� �������� ���� �������� ���-�� ���, ���� �� ������������� ��.".
        "<br />\n����������, ���������� ���� ��������� � �������������� �������� ��������.",

     "Form.subject"       => "���������",
     "Form.subject.Desc"  => "���� �� �������� ���� ������, ��������� ������������ �� ���� �������������",
     "Form.tag"            => "�����",
     "Form.tag.Exist"      => "�������� � ����� ������� ��� ����������, ���������� ������, �������",
     "Form.tag.RegexpHelp" => "������ ���������� � ����� � ��������� ������ ����� � �����.",
     "Form.body"          => "�����",
//     "Form.body_simpleedit" => "����� � ������� &laquo;������ �������� �����&raquo;",
//     "Form.body_wikiedit" => "����� � ������� &laquo;����/����-��������&raquo;",
//     "Form.body_richedit" => "����� � ��������� ��������� � ����� MSWord",

     "Form._none"          => "",
     "Form._none.Desc"     => "���� ��������� � ���� ������ �����������.",

     "Form.formatting"             => "�������������� �������� ������",
     "Form.formatting.Data" => array( "wacko"=>"����/����-��������", 
                                      "simplebr"=>"������ �������� �����", 
                                      "rawhtml"=>"��������� �������� � ����� MSWord" ),
     "Form.pic_id"          => "��� ������",
     "Form.pic_id.Desc"     => (($this->config->principal->data["node_id"] == $this->config->node_name )
                                  ?("�� ������ �������� ".$this->config->Link(
                                    $this->config->principal->data["login"]."/profile/pictures",
                                    "������ ����� ��������")):""),
     "Form.user_datetime"   => "���������� ������",
     "Form.disallow"        => "����������� <br />������ �������������",
     "Form.disallow_comments"         => "��������� ���������������",
     "Form.disallow_notify_comments"  => "�� ��������� � ����������� ������������",
     "Form.disallow_syndicate"        => "�� ���������� � ����� ���������������",
     "Form.disallow_replicate"        => "�� ��������� ���������� �� ������ ���� (�������������)",
     "Form.specials"        => "����������� ������ ���������",
     "Form.is_digest"                  => "�������� ��� ��������",
     "Form.is_keyword"                 => "�������� � ������ ��������� �������� ����",
     "Form.is_announce"                => "�������� ��� �����",

     "Form.keywords"                => "�������� ����� / �������",
     "Form.keywords.Preface"        => "��� �������� �� ������: ",
     "Form.emptylist"   => "�������� ���� �� �������",

     "Form.communities"          => "������������:",
     "Form.communities.LeftSubject"      => "�����������:",
     "Form.communities.RightSubject"      => "�� �����������:",
     "Form.communities.Presets"          => array( 0  => "������ � ���� �������",
                                                   -10 => "����� � �����������...",
                                              ), 

     "Form.groups"          => "��������� ������",
     "Form.groups.LeftSubject"      => "����� ��������:",
     "Form.groups.RightSubject"      => "������ ��������:",
     "Form.groups.Presets"          => array( -1  => "���� (��������� ���������)", 
                                               0  => "������ (���������)",
                                              -2  => "���� ���� �����������",
                                              -3  => "������ � �����������",
                                              -10 => "��������� �������...",
                                              ), 
     "Form.groups.RadioPreset" => -3,

     "Form.read"              => "�� ������",
     "Form.read.Desc"         => "���� ������ ����� ������������ ��� ���������",
     "Form.write"             => "�� ������",
     "Form.comment"           => "�� ���������������",

     "Form._Name"             => "������ �������",
     "Form._Group.body"          => "���� ������", 
     "Form._Group.ref"          => "������������� ������",
     "Form._Group.options"          => "����� � ���������",
     "Form._Group.panels"          => "������ � �������������� �����������",
     "Form._Group.access"          => "���������� ��������",

     "Form._Group.announces"          => "��������� ������",
     "Form.announce_after"            => "����� ���������� ������� � �������� ������",
     "Form.announced_title"           => "������� �� ������",
     "Form.announced_supertag"        => "������������ ��������",
     "Form.announce_in"               => "������������ � ��������:",
     "Form.announce_in.LeftSubject"      => "������������:",
     "Form.announce_in.RightSubject"      => "�� ������������:",
     "Form.announce_in.Presets"          => array( 0  => "������ � ���� �������",
                                                   -10 => "����� � �����������...",
                                              ), 

   
     "Form.default_show_parameter"       => 
                                            "<div style='color:#999999; font-weight:normal'>������������<br /> ������ �� ������,<br /> ��� ������� <br />������������ ����</div>",
     "Form.default_show_parameter.Desc"  => "��� ��� �����, ����� ������",
     "Form.default_show_parameter.AddData" => array("����� ����� ���������� ��� ��������", 
                                                    "����� ����� �������� �������� ���������",
                                                    "����� ������ �� ����� �&nbsp;���� ��������"),
     "Form.default_show_parameter.Data"    => array(
                        "(�� ���������)",
                        "clusterfacet"   => "��� ���������",
                        "clustertree"    => "��������� ���������",
                        "clusterchanges" => "��������� � ��������",
                        "toc"            => "���������� ���������",
                        "search"         => "����� ������",
                        "backlinks"      => "������ �� ���� ��������",
                        "journalchanges" => "Journal Recent Changes",
                        "calendar"       => "��������� �������� ������",
                                                   ),

     "Form.Digest.Subject" => "���������",                                               
     "Form.Digest.DT"      => "����� ��������",                                               
     "Form.Digest.Author"  => "�����",                                               
     "Form.Digest.Body"    => "�����",                                               

   
   );

?>
