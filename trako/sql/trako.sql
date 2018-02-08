# Время создания: Сен 20 2004 г., 00:36

CREATE TABLE {prefix}trako_issues (
  record_id int(11) NOT NULL default '0',
  project_id int(11) NOT NULL default '0',
  issue_no int(11) NOT NULL default '0',
  reporter_id int(11) NOT NULL default '0',
  developer_id int(11) NOT NULL default '0',
  access_rank int(11) NOT NULL default '0',
  severity_class varchar(20) NOT NULL default '',
  severity_value int(11) NOT NULL default '0',
  priority int(11) NOT NULL default '0',
  consistency int(11) NOT NULL default '0',
  state varchar(20) NOT NULL default '',
  state_status varchar(20) NOT NULL default '',
  state_sort int(11) NOT NULL default '0',
  PRIMARY KEY  (record_id),
  KEY project_id (project_id,issue_no,priority)
) TYPE=MyISAM COMMENT='Trako -- issue tracker for NPJ.Issues';
