INSERT INTO `%%NODE_PREF%%acls` VALUES (10, 'record', 'read', '*');
# %%@%%
INSERT INTO `%%NODE_PREF%%acls` VALUES (10, 'record', 'write', '');
# %%@%%
INSERT INTO `%%NODE_PREF%%acls` VALUES (10, 'record', 'comment', '*');
# %%@%%
INSERT INTO `%%NODE_PREF%%acls` VALUES (10, 'record', 'acl_read', '');
# %%@%%
INSERT INTO `%%NODE_PREF%%acls` VALUES (10, 'record', 'acl_write', '');
# %%@%%
INSERT INTO `%%NODE_PREF%%acls` VALUES (10, 'record', 'add', '');
# %%@%%
INSERT INTO `%%NODE_PREF%%acls` VALUES (10, 'record', 'meta_write', '');
# %%@%%
INSERT INTO `%%NODE_PREF%%acls` VALUES (10, 'record', 'remove', '');
# %%@%%
INSERT INTO `%%NODE_PREF%%acls` VALUES (3, 'account', 'banlist', '');
# %%@%%

INSERT INTO `%%NODE_PREF%%groups` VALUES (83, '�����', 3, 100, 1, 2, 0, 0);
# %%@%%
INSERT INTO `%%NODE_PREF%%groups` VALUES (84, '��� ����������', 3, 10, 1, 2, 0, 0);
# %%@%%
INSERT INTO `%%NODE_PREF%%groups` VALUES (85, '��� ��������������', 3, 0, 1, 2, 0, 0);
# %%@%%
INSERT INTO `%%NODE_PREF%%groups` VALUES (86, '����������, ���� � �����', 3, 9, 1, 2, 0, 0);
# %%@%%


INSERT INTO `%%NODE_PREF%%profiles` VALUES (3, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, '2004-03-09 00:24:53', '0000-00-00 00:00:00', '%%ADMIN_EMAIL%%', '', '', '', '', '', '', '', '', 0, '', '', '', '2004-03-09 00:00:00', 1, 1, 20, 20, 30, '', '0000-00-00 00:00:00', 0, 0, 'criba', 'typografica=1', '����� ���������: {subject}', '�������� {npj} �� ������ � {from} �� {to}');
# %%@%%

INSERT INTO `%%NODE_PREF%%records` VALUES (10, 2, 3, 3, '', '', '', '', '%%ADMIN_NAME%%@%%NODE_ID%%:', 0, 0, '', '', 0, '', '', '�������� � �������:\r\n**((JournalIndex ������� ����������))** | **((JournalChanges ��������� ���������))** | **((Feed ����� ���������))** | **((KeywordsTree ������ ������))**\r\n----\r\n==== ��������� ��������� � ������� ====\r\n----\r\n{{Feed}}', '<a name="p5-1"></a><p class="auto" id="p5-1">�������� �&nbsp;�������:<br />\n<strong><!--notypo-->��JournalIndex == ������� ���������⯯<!--/notypo--></strong> | <strong><!--notypo-->��JournalChanges == ��������� �����������<!--/notypo--></strong> | <strong><!--notypo-->��Feed == ����� ��������鯯<!--/notypo--></strong> | <strong><!--notypo-->��KeywordsTree == ������ �����꯯<!--/notypo--></strong></p><hr noshade="noshade" size="1" /><a name="h5-1"></a><h3> ��������� ��������� �&nbsp;������� </h3>\n<hr noshade="noshade" size="1" /><a name="p5-2"></a><p class="auto" id="p5-2">\n<!--notypo-->��Feed��<!--/notypo-->\n</p>', '', 'p5-1<poloskuns,col>(p)<poloskuns,col>77777<poloskuns,row>h5-1<poloskuns,col> ��������� ��������� � ������� <poloskuns,col>3<poloskuns,row>p5-2<poloskuns,col>(p)<poloskuns,col>77777', '', 'wacko', '', 0, '0000-00-00 00:00:00', '2004-03-09 00:24:53', '2004-03-09 00:24:53', 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, '', '!', '%%ADMIN_NAME%%', '�������������', '%%NODE_ID%%');
# %%@%%


INSERT INTO `%%NODE_PREF%%user_groups` VALUES (5, 86, 3, 10);
# %%@%%
INSERT INTO `%%NODE_PREF%%user_groups` VALUES (6, 83, 3, 10);
# %%@%%
INSERT INTO `%%NODE_PREF%%user_groups` VALUES (7, 78, 3, 10);
# %%@%%


INSERT INTO `%%NODE_PREF%%user_menu` VALUES (6, 3,  '%%ADMIN_NAME%%@%%NODE_ID%%', '��� ������', 1);
# %%@%%
INSERT INTO `%%NODE_PREF%%user_menu` VALUES (7, 3,  '%%ADMIN_NAME%%@%%NODE_ID%%:/RecentChanges', '��� ��������� ���������', 2);
# %%@%%
INSERT INTO `%%NODE_PREF%%user_menu` VALUES (8, 3,  '%%ADMIN_NAME%%@%%NODE_ID%%:/JournalIndex', '��� ���������', 3);
# %%@%%
INSERT INTO `%%NODE_PREF%%user_menu` VALUES (9, 3,  '%%ADMIN_NAME%%@%%NODE_ID%%:/Feed', '��� �����', 4);
# %%@%%
INSERT INTO `%%NODE_PREF%%user_menu` VALUES (10, 3, '%%ADMIN_NAME%%@%%NODE_ID%%:/Manage', '���������� ��������', 5);
# %%@%%

INSERT INTO `%%NODE_PREF%%users` VALUES (3, 10, '%%ADMIN_NAME%%', '�������������', '%%ADMIN_NAME%%', '%%NODE_ID%%', 0, 0, 0, 1, '%%ADMIN_PASSWORD%%', 'wacko', 'user', '2004-03-09 00:24:54', '0000-00-00 00:00:00', '', 0, 'absent', '', -1, 'std', 'double_click=1\nedit_simple=1\nrecord_stats=0\ncomments=0', '', '', 0);
# %%@%%

ALTER TABLE %%NODE_PREF%%profiles ADD file_url_prefix VARCHAR(250) NOT NULL AFTER website_name;
# %%@%%

ALTER TABLE %%NODE_PREF%%users ADD account_class VARCHAR(250) NOT NULL AFTER account_type;
# %%@%%
ALTER TABLE %%NODE_PREF%%users ADD INDEX (account_class); 
# %%@%%

ALTER TABLE %%NODE_PREF%%users ADD domain_type INT DEFAULT '5' NOT NULL AFTER populate_type ;
# %%@%%

ALTER TABLE %%NODE_PREF%%records ADD commented_datetime DATETIME NOT NULL AFTER edited_datetime ,
ADD last_comment_id INT NOT NULL AFTER commented_datetime ;
# %%@%%


ALTER TABLE %%NODE_PREF%%records_ref ADD commented_datetime DATETIME NOT NULL AFTER user_datetime ,
ADD last_comment_id INT NOT NULL AFTER user_datetime ;
# %%@%%

ALTER TABLE %%NODE_PREF%%nodes ADD npj_version VARCHAR( 20 ) NOT NULL AFTER created_datetime ;
# %%@%%

UPDATE %%NODE_PREF%%nodes SET npj_version='R1.9' where is_local='1';
# %%@%%

ALTER TABLE %%NODE_PREF%%users ADD INDEX (alive); 
# %%@%%

CREATE TABLE %%NODE_PREF%%comments_filtered 
(
  _id int(11) NOT NULL auto_increment,
  comment_id int(11) NOT NULL default '0',
  filter_user_id int(11) NOT NULL default '0',
  moderator_id int(11) NOT NULL default '0',
  created_datetime datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (_id),
  UNIQUE KEY filter_user_id (filter_user_id,comment_id),
  KEY comment_id (comment_id,filter_user_id)
) TYPE=MyISAM COMMENT='Community-filter part. So called &quot;Shtorki dlja kommentariev&quot;';
# %%@%%

ALTER TABLE %%NODE_PREF%%records ADD `filter` TEXT NOT NULL AFTER `crossposted`;
# %%@%%

ALTER TABLE %%NODE_PREF%%records ADD `by_module` varchar(50) NOT NULL AFTER `template`;
# %%@%%

ALTER TABLE %%NODE_PREF%%comments ADD frozen INT NOT NULL AFTER active;
# %%@%%
