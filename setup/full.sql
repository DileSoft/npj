# phpMyAdmin MySQL-Dump
# version 2.2.6
# http://phpwizard.net/phpMyAdmin/
# http://www.phpmyadmin.net/ (download page)
#
# Хост: localhost
# Время создания: Янв 24 2004 г., 15:25
# Версия сервера: 3.23.32
# Версия PHP: 4.2.3
# БД : `npj`
# --------------------------------------------------------

#
# Структура таблицы `%%NODE_PREF%%acls`
#

CREATE TABLE %%NODE_PREF%%acls (
  object_id int(11) NOT NULL default '0',
  object_type varchar(20) NOT NULL default '',
  object_right varchar(20) NOT NULL default '',
  acl text NOT NULL,
  PRIMARY KEY (object_id,object_type,object_right)
) TYPE=MyISAM;
# %%@%%

#
# Дамп данных таблицы `%%NODE_PREF%%acls`
#

INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (1, 'account', 'banlist', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (2, 'account', 'banlist', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (1, 'record', 'read', '*');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (1, 'record', 'write', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (1, 'record', 'comment', '*');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (1, 'record', 'meta_write', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (1, 'record', 'add', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (1, 'record', 'remove', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (1, 'record', 'acl_read', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (1, 'record', 'acl_write', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (2, 'record', 'read', '*');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (2, 'record', 'write', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (2, 'record', 'comment', '*');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (2, 'record', 'meta_write', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (2, 'record', 'add', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (2, 'record', 'remove', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (2, 'record', 'acl_read', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (2, 'record', 'acl_write', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (3, 'record', 'read', '*');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (3, 'record', 'write', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (3, 'record', 'comment', '*');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (3, 'record', 'meta_write', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (3, 'record', 'add', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (3, 'record', 'remove', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (3, 'record', 'acl_read', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (3, 'record', 'acl_write', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (4, 'record', 'read', '*');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (4, 'record', 'write', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (4, 'record', 'comment', '*');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (4, 'record', 'meta_write', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (4, 'record', 'add', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (4, 'record', 'remove', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (4, 'record', 'acl_read', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (4, 'record', 'acl_write', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (5, 'record', 'read', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (5, 'record', 'write', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (5, 'record', 'comment', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (5, 'record', 'meta_write', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (5, 'record', 'add', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (5, 'record', 'remove', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (5, 'record', 'acl_read', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (5, 'record', 'acl_write', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (6, 'record', 'read', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (6, 'record', 'write', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (6, 'record', 'comment', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (6, 'record', 'meta_write', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (6, 'record', 'add', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (6, 'record', 'remove', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (6, 'record', 'acl_read', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (6, 'record', 'acl_write', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (7, 'record', 'read', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (7, 'record', 'write', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (7, 'record', 'comment', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (7, 'record', 'meta_write', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (7, 'record', 'add', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (7, 'record', 'remove', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (7, 'record', 'acl_read', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (7, 'record', 'acl_write', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (8, 'record', 'read', '*');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (8, 'record', 'write', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (8, 'record', 'comment', '*');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (8, 'record', 'meta_write', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (8, 'record', 'add', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (8, 'record', 'remove', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (8, 'record', 'acl_read', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (8, 'record', 'acl_write', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (9, 'record', 'read', '*');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (9, 'record', 'write', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (9, 'record', 'comment', '*');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (9, 'record', 'meta_write', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (9, 'record', 'add', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (9, 'record', 'remove', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (9, 'record', 'acl_read', '');
# %%@%%
INSERT INTO %%NODE_PREF%%acls (object_id, object_type, object_right, acl) VALUES (9, 'record', 'acl_write', '');
# %%@%%
# --------------------------------------------------------

#
# Структура таблицы `%%NODE_PREF%%comments`
#

CREATE TABLE %%NODE_PREF%%comments (
  comment_id int(11) NOT NULL auto_increment,
  active int(11) NOT NULL default '1',
  pic_id int(11) NOT NULL default '0',
  subject varchar(250) NOT NULL default '',
  body_post text NOT NULL,
  user_id int(11) NOT NULL default '0',
  user_login varchar(20) NOT NULL default '',
  user_name varchar(250) NOT NULL default '',
  user_node_id varchar(20) NOT NULL default '',
  created_datetime datetime NOT NULL default '0000-00-00 00:00:00',
  ip_xff varchar(250) NOT NULL default '',
  record_id int(11) NOT NULL default '0',
  parent_id int(11) NOT NULL default '0',
  lft_id int(11) NOT NULL default '0',
  rgt_id int(11) NOT NULL default '0',
  rep_original_id int(11) NOT NULL default '0',
  rep_node_id varchar(20) NOT NULL default '',
  replicator_user_id int(11) NOT NULL default '0',
  disallow_replicate tinyint(1) NOT NULL default '0',
  PRIMARY KEY (comment_id),
  KEY record_id(record_id,parent_id),
  KEY record_id_2(record_id,lft_id),
  KEY record_id_3(record_id,rgt_id)
) TYPE=MyISAM;
# %%@%%

#
# Дамп данных таблицы `%%NODE_PREF%%comments`
#

# --------------------------------------------------------

#
# Структура таблицы `%%NODE_PREF%%comments_replicas`
#

CREATE TABLE %%NODE_PREF%%comments_replicas (
  comment_id int(11) NOT NULL default '0',
  replicated_to_node_id varchar(20) NOT NULL default '',
  replicated_to_comment_id int(11) NOT NULL default '0',
  replicated_datetime datetime NOT NULL default '0000-00-00 00:00:00'
) TYPE=MyISAM;
# %%@%%

#
# Дамп данных таблицы `%%NODE_PREF%%comments_replicas`
#

# --------------------------------------------------------

#
# Структура таблицы `%%NODE_PREF%%csa`
#

CREATE TABLE %%NODE_PREF%%csa (
  id int(11) NOT NULL auto_increment,
  csa varchar(50) NOT NULL default '',
  expire timestamp(14) NOT NULL,
  PRIMARY KEY (id)
) TYPE=MyISAM;
# %%@%%

#
# Дамп данных таблицы `%%NODE_PREF%%csa`
#

# --------------------------------------------------------

#
# Структура таблицы `%%NODE_PREF%%groups`
#

CREATE TABLE %%NODE_PREF%%groups (
  group_id int(11) NOT NULL auto_increment,
  group_name varchar(100) NOT NULL default '',
  user_id int(11) NOT NULL default '0',
  group_rank int(11) NOT NULL default '0',
  is_system tinyint(4) NOT NULL default '0',
  group_type int(11) NOT NULL default '2',
  pos int(11) NOT NULL default '0',
  is_default tinyint(4) NOT NULL default '0',
  PRIMARY KEY (group_id),
  KEY user_id(user_id,is_system,pos),
  KEY user_id_2(user_id,is_default),
  KEY user_id_3(user_id,group_rank)
) TYPE=MyISAM;
# %%@%%

#
# Дамп данных таблицы `%%NODE_PREF%%groups`
#

INSERT INTO %%NODE_PREF%%groups (group_id, group_name, user_id, group_rank, is_system, group_type, pos, is_default) VALUES (1, 'Никто', 1, 100, 1, 2, 2, 0);
# %%@%%
INSERT INTO %%NODE_PREF%%groups (group_id, group_name, user_id, group_rank, is_system, group_type, pos, is_default) VALUES (2, 'Все конфиденты', 1, 10, 1, 2, 0, 0);
# %%@%%
INSERT INTO %%NODE_PREF%%groups (group_id, group_name, user_id, group_rank, is_system, group_type, pos, is_default) VALUES (3, 'Все корреспонденты', 1, 0, 1, 2, 0, 1);
# %%@%%
INSERT INTO %%NODE_PREF%%groups (group_id, group_name, user_id, group_rank, is_system, group_type, pos, is_default) VALUES (76, 'Никто', 2, 100, 1, 2, 0, 0);
# %%@%%
INSERT INTO %%NODE_PREF%%groups (group_id, group_name, user_id, group_rank, is_system, group_type, pos, is_default) VALUES (77, 'Заявки на членство', 2, 0, 1, 2, 0, 0);
# %%@%%
INSERT INTO %%NODE_PREF%%groups (group_id, group_name, user_id, group_rank, is_system, group_type, pos, is_default) VALUES (7, 'Никто', 2, 100, 2, 2, 2, 0);
# %%@%%
INSERT INTO %%NODE_PREF%%groups (group_id, group_name, user_id, group_rank, is_system, group_type, pos, is_default) VALUES (8, 'Все конфиденты', 2, 10, 2, 2, 0, 0);
# %%@%%
INSERT INTO %%NODE_PREF%%groups (group_id, group_name, user_id, group_rank, is_system, group_type, pos, is_default) VALUES (9, 'Все корреспонденты', 2, 0, 2, 2, 0, 1);
# %%@%%
INSERT INTO %%NODE_PREF%%groups (group_id, group_name, user_id, group_rank, is_system, group_type, pos, is_default) VALUES (10, 'Никто', 2, 100, 3, 2, 2, 0);
# %%@%%
INSERT INTO %%NODE_PREF%%groups (group_id, group_name, user_id, group_rank, is_system, group_type, pos, is_default) VALUES (11, 'Заявки на членство', 2, 0, 3, 2, 0, 0);
# %%@%%
INSERT INTO %%NODE_PREF%%groups (group_id, group_name, user_id, group_rank, is_system, group_type, pos, is_default) VALUES (12, 'Члены сообщества', 2, 5, 3, 2, 0, 0);
# %%@%%
INSERT INTO %%NODE_PREF%%groups (group_id, group_name, user_id, group_rank, is_system, group_type, pos, is_default) VALUES (13, 'Привилегированные члены', 2, 10, 3, 2, 0, 0);
# %%@%%
INSERT INTO %%NODE_PREF%%groups (group_id, group_name, user_id, group_rank, is_system, group_type, pos, is_default) VALUES (14, 'Модераторы сообщества', 2, 20, 3, 2, 0, 0);
# %%@%%
INSERT INTO %%NODE_PREF%%groups (group_id, group_name, user_id, group_rank, is_system, group_type, pos, is_default) VALUES (15, 'Никто', 2, 100, 4, 2, 2, 0);
# %%@%%
INSERT INTO %%NODE_PREF%%groups (group_id, group_name, user_id, group_rank, is_system, group_type, pos, is_default) VALUES (16, 'Заявки на членство', 2, 0, 4, 2, 0, 0);
# %%@%%
INSERT INTO %%NODE_PREF%%groups (group_id, group_name, user_id, group_rank, is_system, group_type, pos, is_default) VALUES (17, 'Наблюдатели', 2, 5, 4, 2, 0, 0);
# %%@%%
INSERT INTO %%NODE_PREF%%groups (group_id, group_name, user_id, group_rank, is_system, group_type, pos, is_default) VALUES (18, 'Команда', 2, 10, 4, 2, 0, 0);
# %%@%%
INSERT INTO %%NODE_PREF%%groups (group_id, group_name, user_id, group_rank, is_system, group_type, pos, is_default) VALUES (19, 'Менеджеры', 2, 20, 4, 2, 0, 0);
# %%@%%
INSERT INTO %%NODE_PREF%%groups (group_id, group_name, user_id, group_rank, is_system, group_type, pos, is_default) VALUES (78, 'Наблюдатели', 2, 5, 1, 2, 0, 0);
# %%@%%
INSERT INTO %%NODE_PREF%%groups (group_id, group_name, user_id, group_rank, is_system, group_type, pos, is_default) VALUES (79, 'Команда', 2, 10, 1, 2, 0, 0);
# %%@%%
INSERT INTO %%NODE_PREF%%groups (group_id, group_name, user_id, group_rank, is_system, group_type, pos, is_default) VALUES (80, 'Менеджеры', 2, 20, 1, 2, 0, 0);
# %%@%%
INSERT INTO %%NODE_PREF%%groups (group_id, group_name, user_id, group_rank, is_system, group_type, pos, is_default) VALUES (81, 'Сообщества, куда я вхожу', 2, 9, 2, 2, 0, 0);
# %%@%%
INSERT INTO %%NODE_PREF%%groups (group_id, group_name, user_id, group_rank, is_system, group_type, pos, is_default) VALUES (82, 'Сообщества, куда я вхожу', 1, 9, 1, 2, 0, 0);
# %%@%%
# --------------------------------------------------------

#
# Структура таблицы `%%NODE_PREF%%links`
#

CREATE TABLE %%NODE_PREF%%links (
  link_id int(11) NOT NULL auto_increment,
  from_user_id int(11) NOT NULL default '0',
  from_id int(11) NOT NULL default '0',
  to_user_id int(11) NOT NULL default '0',
  to_id int(11) NOT NULL default '0',
  to_supertag varchar(250) NOT NULL default '',
  to_tag varchar(250) binary NOT NULL default '',
  link_text varchar(250) binary NOT NULL default '',
  PRIMARY KEY (link_id),
  KEY from_id(from_id),
  KEY to_id(to_id),
  KEY to_supertag(to_supertag),
  KEY to_user_id(to_user_id),
  KEY from_user_id(from_user_id),
  KEY from_user_id_2(from_user_id,from_id),
  KEY to_user_id_2(to_user_id,to_id)
) TYPE=MyISAM;
# %%@%%

#
# Дамп данных таблицы `%%NODE_PREF%%links`
#

# --------------------------------------------------------

#
# Структура таблицы `%%NODE_PREF%%maildebug`
#

CREATE TABLE %%NODE_PREF%%maildebug (
  mail_id int(11) NOT NULL auto_increment,
  body text NOT NULL,
  error varchar(250) NOT NULL default '',
  datetime datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (mail_id)
) TYPE=MyISAM;
# %%@%%

#
# Дамп данных таблицы `%%NODE_PREF%%maildebug`
#

# --------------------------------------------------------

#
# Структура таблицы `%%NODE_PREF%%nodes`
#

CREATE TABLE %%NODE_PREF%%nodes (
  title varchar(250) NOT NULL default '',
  node_id varchar(20) NOT NULL default '',
  url varchar(250) NOT NULL default '',
  is_local int(11) NOT NULL default '0',
  is_nns int(11) NOT NULL default '0',
  created_datetime datetime NOT NULL default '0000-00-00 00:00:00',
  user_pictures_dir VARCHAR( 50 ) NOT NULL,
  passwd varchar(20) NOT NULL default '',
  can_nns int(11) NOT NULL default '0',
  email varchar(100) NOT NULL default '',
  ip varchar(100) NOT NULL default '',
  alternate_ip VARCHAR(20) NOT NULL default '',
  PRIMARY KEY (node_id)
) TYPE=MyISAM;
# %%@%%

#
# Дамп данных таблицы `%%NODE_PREF%%nodes`
#

INSERT INTO %%NODE_PREF%%nodes VALUES ('', 'npj', 'http://www.npj.ru/', 0, 1, '0000-00-00 00:00:00', '', '', 0, '', '', '');
# %%@%%
INSERT INTO %%NODE_PREF%%nodes VALUES ('%%NODE_TITLE%%', '%%NODE_ID%%', '%%NODE_URL%%', 1, 0, '0000-00-00 00:00:00', '', '', 0, '%%NODE_MAIL%%', '', '');
# %%@%%
# --------------------------------------------------------

#
# Структура таблицы `%%NODE_PREF%%npz`
#

CREATE TABLE %%NODE_PREF%%npz (
  id int(11) NOT NULL auto_increment,
  spec varchar(250) NOT NULL default '',
  command varchar(250) NOT NULL default '',
  last varchar(100) NOT NULL default '1',
  chunk varchar(200) NOT NULL default '-1',
  time_last_chunk varchar(100) NOT NULL default '',
  state int(11) NOT NULL default '0',
  param varchar(50) NOT NULL default '',
  PRIMARY KEY (id)
) TYPE=MyISAM;
# %%@%%

#
# Дамп данных таблицы `%%NODE_PREF%%npz`
#

INSERT INTO %%NODE_PREF%%npz (id, spec, command, last, chunk, time_last_chunk, state, param) VALUES (1, '* * * * *', '%%NODE_URL%%node/mail', '1061125081', '-1', '', 0, '');
# %%@%%
INSERT INTO %%NODE_PREF%%npz (id, spec, command, last, chunk, time_last_chunk, state, param) VALUES (2, '22 4 * * 2', '%%NODE_URL%%notify/nnsnet', '1061125081', '-1', '', 0, '');
# %%@%%
# --------------------------------------------------------

#
# Структура таблицы `%%NODE_PREF%%profiles`
#


CREATE TABLE %%NODE_PREF%%profiles (
  user_id int(11) NOT NULL default '0',
  lft_id INT NOT NULL,
  rgt_id INT NOT NULL,    
  parent_id INT NOT NULL,
  total_posted int(11) NOT NULL default '0',
  security_type int(11) NOT NULL default '0',
  account_template VARCHAR( 20 ) NOT NULL,
  friends_template VARCHAR( 20 ) NOT NULL,
  default_membership int(11) NOT NULL default '0',
  post_membership int(11) NOT NULL default '0',
  announce_membership int(11) NOT NULL default '0',
  owner_membership INT NOT NULL,
  creation_date datetime NOT NULL default '0000-00-00 00:00:00',
  last_updated datetime NOT NULL default '0000-00-00 00:00:00',
  email varchar(250) NOT NULL default '',
  email_confirm varchar(100) NOT NULL default 'do not allowed',
  icq_uin varchar(20) NOT NULL default '',
  website_url varchar(250) NOT NULL default '',
  website_name varchar(250) NOT NULL default '',
  journal_name varchar(250) NOT NULL default '',
  journal_desc text NOT NULL,
  bio text NOT NULL,
  interests text NOT NULL,
  sex int(11) NOT NULL default '0',
  city varchar(100) NOT NULL default '',
  region varchar(100) NOT NULL default '',
  country varchar(100) NOT NULL default '',
  birthday datetime NOT NULL default '0000-00-00 00:00:00',
  _notify_comments tinyint(1) NOT NULL default '1',
  _replication_allowed tinyint(1) NOT NULL default '1',
  _friends_page_size int(11) NOT NULL default '20',
  _personal_page_size int(11) NOT NULL default '20',
  _recentchanges_size int(11) NOT NULL default '30',
  temporary_password varchar(32) NOT NULL default '',
  temporary_password_created datetime NOT NULL default '0000-00-00 00:00:00',
  number_friends int(11) NOT NULL default '0',
  number_friendof int(11) NOT NULL default '0',
  skin varchar(20) NOT NULL default 'criba',
  advanced text NOT NULL,
  template_announce varchar(250) NOT NULL default 'Анонс документа: {subject}',
  template_digest varchar(250) NOT NULL default 'Дайджест {npj} за период с {from} по {to}',
  PRIMARY KEY (user_id)
) TYPE=MyISAM;
# %%@%%

#
# Дамп данных таблицы `%%NODE_PREF%%profiles`
#

INSERT INTO %%NODE_PREF%%profiles (user_id, total_posted, security_type, default_membership, post_membership, announce_membership, creation_date, last_updated, email, email_confirm, icq_uin, website_url, website_name, journal_name, journal_desc, bio, interests, sex, city, region, country, birthday, _notify_comments, _replication_allowed, _friends_page_size, _personal_page_size, _recentchanges_size, temporary_password, temporary_password_created, number_friends, number_friendof, skin, advanced, template_announce, template_digest) VALUES (1, 0, 0, 0, 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', 'none', '', '', '', 'Гостевой журнал', 'Журнал, в который никто никогда не попадёт', '', '', 0, '', '', '', '2003-08-18 05:42:00', 1, 1, 20, 20, 30, '', '0000-00-00 00:00:00', 0, 0, 'criba', '', 'Анонс документа: {subject}', 'Дайджест {npj} за период с {from} по {to}');
# %%@%%
INSERT INTO %%NODE_PREF%%profiles (user_id, total_posted, security_type, default_membership, post_membership, announce_membership, creation_date, last_updated, email, email_confirm, icq_uin, website_url, website_name, journal_name, journal_desc, bio, interests, sex, city, region, country, birthday, _notify_comments, _replication_allowed, _friends_page_size, _personal_page_size, _recentchanges_size, temporary_password, temporary_password_created, number_friends, number_friendof, skin, advanced, template_announce, template_digest) VALUES (2, 0, 2, 5, 10, 20, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', 'none', '', '', '', 'Узел системы', 'Вы находитесь в журнале нашего узла. Этот журнал принадлежит всем владельцам узла и содержит информацию о самом узле. Это не тот журнал, в который Вам следует писать.', '', '', 0, '', '', '', '2003-08-18 05:42:00', 1, 1, 20, 20, 30, '', '0000-00-00 00:00:00', 0, 0, 'criba', '', 'Анонс документа: {subject}', 'Дайджест {npj} за период с {from} по {to}');
# %%@%%
# --------------------------------------------------------

#
# Структура таблицы `%%NODE_PREF%%record_versions`
#

CREATE TABLE %%NODE_PREF%%record_versions (
  record_id int(11) NOT NULL default '0',
  version_id int(11) NOT NULL default '0',
  body text NOT NULL,
  body_r text NOT NULL,
  formatting varchar(20) NOT NULL default '',
  edited_datetime datetime NOT NULL default '0000-00-00 00:00:00',
  version_tag varchar(100) NOT NULL default '',
  edited_user_login varchar(20) NOT NULL default '',
  edited_user_name varchar(250) NOT NULL default '',
  edited_user_node_id varchar(20) NOT NULL default '',
  PRIMARY KEY (record_id,version_id)
) TYPE=MyISAM;
# %%@%%

#
# Дамп данных таблицы `%%NODE_PREF%%record_versions`
#

# --------------------------------------------------------

#
# Структура таблицы `%%NODE_PREF%%records`
#

CREATE TABLE %%NODE_PREF%%records (
  record_id int(11) NOT NULL auto_increment,
  type int(11) NOT NULL default '1',
  user_id int(11) NOT NULL default '0',
  author_id int(11) NOT NULL default '0',
  subject varchar(250) binary NOT NULL default '',
  subject_r varchar(255) NOT NULL default '',
  subject_post varchar(255) NOT NULL default '',
  tag varchar(250) binary NOT NULL default '',
  supertag varchar(250) NOT NULL default '',
  depth int(11) NOT NULL default '0',
  is_parent int(11) NOT NULL default '0',
  default_show_parameter varchar(20) NOT NULL default '',
  default_show_parameter_param varchar(50) NOT NULL default '',
  default_show_parameter_add int(11) NOT NULL default '0',
  default_show_parameter_more varchar(20) NOT NULL default '',
  default_show_parameter_more_param varchar(50) NOT NULL default '',
  body text NOT NULL,
  body_r text NOT NULL,
  body_post text NOT NULL,
  body_toc text NOT NULL,
  body_options varchar(250) NOT NULL default '',
  formatting varchar(20) NOT NULL default 'wacko',
  version_tag varchar(100) NOT NULL default '',
  pic_id int(11) NOT NULL default '0',
  user_datetime datetime NOT NULL default '0000-00-00 00:00:00',
  created_datetime datetime NOT NULL default '0000-00-00 00:00:00',
  edited_datetime datetime NOT NULL default '0000-00-00 00:00:00',
  disallow_comments tinyint(1) NOT NULL default '0',
  disallow_syndicate tinyint(1) NOT NULL default '0',
  disallow_replicate tinyint(1) NOT NULL default '0',
  disallow_notify_comments tinyint(1) NOT NULL default '0',
  number_comments int(11) NOT NULL default '0',
  is_digest tinyint(1) NOT NULL default '0',
  is_announce tinyint(1) NOT NULL default '0',
  is_keyword tinyint(1) NOT NULL default '0',
  template VARCHAR( 20 ) NOT NULL,
  group_versions TINYINT( 1 ) NOT NULL,
  group1 int(11) NOT NULL default '0',
  group2 int(11) NOT NULL default '0',
  group3 int(11) NOT NULL default '0',
  group4 int(11) NOT NULL default '0',
  keywords text NOT NULL,
  crossposted text NOT NULL,
  edited_user_login varchar(20) NOT NULL default '',
  edited_user_name varchar(250) NOT NULL default '',
  edited_user_node_id varchar(20) NOT NULL default '',
  PRIMARY KEY (record_id),
  FULLTEXT KEY body(body),
  KEY supertag(supertag),
  KEY depth(depth),
  KEY is_parent(is_parent),
  KEY type(type),
  KEY user_id(user_id),
  KEY author_id(author_id),
  KEY is_keyword(is_keyword)
) TYPE=MyISAM;
# %%@%%

#
# Дамп данных таблицы `%%NODE_PREF%%records`
#

INSERT INTO %%NODE_PREF%%records  VALUES (1, 2, 1, 0, '', '', '', '', 'guest@%%NODE_ID%%:', 0, 0, '', '', 0, '', '', 'Журнал гостевого пользователя', 'Журнал гостевого пользователя', '', '', '', 'wacko', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, '', '', 'node', 'NPJ', '%%NODE_ID%%');
# %%@%%
INSERT INTO %%NODE_PREF%%records  VALUES (2, 2, 2, 0, '', '', '', '', 'node@%%NODE_ID%%:', 0, 0, '', '', 0, '', '', '{{Feed style=authors}}', 'ЎЎFeed style=authorsЎЎ\n', '', '', '', 'wacko', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, '', '', 'node', 'NPJ', '%%NODE_ID%%');
# %%@%%
INSERT INTO %%NODE_PREF%%records  VALUES (3, 2, 2, 0, '', '', '', 'Welcome', 'node@%%NODE_ID%%:welcome', 1, 0, '', '', 0, '', '', '', '', '', '', '', 'wacko', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, '', '', 'node', 'NPJ', '%%NODE_ID%%');
# %%@%%
INSERT INTO %%NODE_PREF%%records  VALUES (4, 2, 2, 0, '', '', '', 'HomePage', 'node@%%NODE_ID%%:homepage', 1, 0, '', '', 0, '', '', 'Вы находитесь на корневой странице нашего узла.\r\n\r\nУ нашего узла, как и у всякого другого, есть:\r\n  * ((users Пользователи))\r\n  * ((communities Сообщества))\r\n\r\nДля того, чтобы получить различные возможности, предоставляемые нашим сервисом, Вам следует ((registration@ зарегистрироваться)) в системе, а если Вы это однажды уже делали, то просто ((login@ войти в неё)).\r\n\r\nСпасибо за внимание.', 'Вы находитесь на корневой странице нашего узла.<br />\n<br />\nУ нашего узла, как и у всякого другого, есть:<br />\n<ul><li> <!--notypo-->ўўusers == ПользователиЇЇ<!--/notypo-->\n</li><li> <!--notypo-->ўўcommunities == СообществаЇЇ<!--/notypo--></li></ul>\n<br />\nДля того, чтобы получить различные возможности, предоставляемые нашим сервисом, Вам следует <!--notypo-->ўўregistration@ == зарегистрироватьсяЇЇ<!--/notypo--> в системе, а если Вы это однажды уже делали, то просто <!--notypo-->ўўlogin@ == войти в неёЇЇ<!--/notypo-->.<br />\n<br />\nСпасибо за внимание.\n', '', '', '', 'wacko', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, '', '', 'node', 'NPJ', '%%NODE_ID%%');
# %%@%%
INSERT INTO %%NODE_PREF%%records  VALUES (5, 2, 2, 0, '', '', '', 'User', 'node@%%NODE_ID%%:user', 1, 0, '', '', 0, '', '', 'Смотрите в журнале:\r\n**((JournalIndex Каталог документов))** | **((JournalChanges Последние изменения))** | **((Feed Лента сообщений))** | **((KeywordsTree Дерево рубрик))**\r\n----\r\n==== Последние сообщения в журнале ====\r\n----\r\n{{Feed}}', 'Смотрите в журнале:<br />\n<strong><!--notypo-->ўўJournalIndex == Каталог документовЇЇ<!--/notypo--></strong> | <strong><!--notypo-->ўўJournalChanges == Последние измененияЇЇ<!--/notypo--></strong> | <strong><!--notypo-->ўўFeed == Лента сообщенийЇЇ<!--/notypo--></strong> | <strong><!--notypo-->ўўKeywordsTree == Дерево рубрикЇЇ<!--/notypo--></strong><br />\n<hr noshade="noshade" size="1" /><a name="TOC_1"></a><h3> Последние сообщения в журнале </h3>\n<hr noshade="noshade" size="1" />\nЎЎFeedЎЎ\n', '', '', '', 'wacko', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, '', '', 'node', 'NPJ', '%%NODE_ID%%');
# %%@%%
INSERT INTO %%NODE_PREF%%records  VALUES (6, 2, 2, 0, '', '', '', 'Community', 'node@%%NODE_ID%%:community', 1, 0, '', '', 0, '', '', '{{Feed style=authors}}', 'ЎЎFeed style=authorsЎЎ\n', '', '', '', 'wacko', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, '', '', 'node', 'NPJ', '%%NODE_ID%%');
# %%@%%
INSERT INTO %%NODE_PREF%%records  VALUES (7, 2, 2, 0, '', '', '', 'WorkGroup', 'node@%%NODE_ID%%:workgroup', 1, 0, '', '', 0, '', '', '{{Feed style=authors}}', 'ЎЎFeed style=authorsЎЎ\n', '', '', '', 'wacko', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, '', '', 'node', 'NPJ', '%%NODE_ID%%');
# %%@%%
INSERT INTO %%NODE_PREF%%records  VALUES (8, 2, 2, 0, 'Что можно зарегистрировать на нашем узле', '', '', 'Регистрация', 'node@%%NODE_ID%%:registracija', 1, 0, '', '', 0, '', '', 'На нашем узле вы можете:\n\n  * **((registration@%%NODE_ID%% Зарегистрироваться сами))** -- создать учётную запись пользователя. Это даст вам доступ ко многим сервисам системы, в частности, у вас появится собственный, личный, только ваш Журнал.\n\n  * **((registration@%%NODE_ID%%:community Создать сообщество))** по интересам -- если вы чем-то увлекаетесь, вы можете создать сообщество, которое объединит ваши интересы с интересами других пользователей узла. Вы сможете публиковать свои сообщения в этом сообществе, это же смогут делать и другие его члены. //Прежде чем создавать сообщество, посмотрите, может кто-то уже создал такое, какое вам нужно -- ((Communities Существующие сообщества))//\n\n  * **((registration@%%NODE_ID%%:workgroup Создать рабочую группу))**, предназначенную для совместной работы в этой среде. В журнале этой группы вы сможете создавать документы и работать над ними совместно с другими пользователями системы. Скоро у нас появятся ещё дополнительные средства для рабочих групп, такие как туду-листы, редакторский цикл, средства анонсирования документов.\n\nНадеемся, вы к нам надолго.', '<p class="auto">На нашем узле вы&nbsp;можете:</p><br /><ul><li> <strong><!--notypo-->ўўregistration@%%NODE_ID%% == Зарегистрироваться самиЇЇ<!--/notypo--></strong> &#151; создать учётную запись пользователя. Это&nbsp;даст вам&nbsp;доступ ко&nbsp;многим сервисам системы, в&nbsp;частности, у&nbsp;вас появится собственный, личный, только ваш&nbsp;Журнал.</li></ul><br /><ul><li> <strong><!--notypo-->ўўregistration@%%NODE_ID%%:community == Создать сообществоЇЇ<!--/notypo--></strong> по&nbsp;интересам &#151; если вы&nbsp;<nobr>чем-то</nobr> увлекаетесь, вы&nbsp;можете создать сообщество, которое объединит ваши интересы с&nbsp;интересами других пользователей узла. Вы&nbsp;сможете публиковать свои сообщения в&nbsp;этом сообществе, это&nbsp;же&nbsp;смогут делать и&nbsp;другие его&nbsp;члены. <em>Прежде чем&nbsp;создавать сообщество, посмотрите, может <nobr>кто-то</nobr> уже&nbsp;создал такое, какое вам&nbsp;нужно &#151; <!--notypo-->ўўCommunities == Существующие сообществаЇЇ<!--/notypo--></em></li></ul><br /><ul><li> <strong><!--notypo-->ўўregistration@%%NODE_ID%%:workgroup == Создать рабочую группуЇЇ<!--/notypo--></strong>, предназначенную для&nbsp;совместной работы в&nbsp;этой среде. В&nbsp;журнале этой группы вы&nbsp;сможете создавать документы и&nbsp;работать над&nbsp;ними совместно с&nbsp;другими пользователями системы. Скоро у&nbsp;нас появятся ещё дополнительные средства для&nbsp;рабочих групп, такие как&nbsp;<nobr>туду-листы</nobr>, редакторский цикл, средства анонсирования документов.</li></ul><br /><p class="auto">Надеемся, вы&nbsp;к нам&nbsp;надолго.</p>', '', '', '', 'wacko', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, '', '', 'node', 'NPJ', '%%NODE_ID%%');
# %%@%%
INSERT INTO %%NODE_PREF%%records  VALUES (9, 2, 2, 3, 'Вновь прибывшие пользователи', 'Вновь прибывшие пользователи', '', 'FreshDirectory', 'node@%%NODE_ID%%:freshdirectory', 1, 0, '', '', 0, '0', '', '{{directory show="users" order="creation" style="td" wrapper="none"}}\r\n\r\n==== Сообщества ====\r\n{{directory show="communities" order="creation" style="td" wrapper="none"}}\r\n\r\n==== Рабочие группы ====\r\n{{directory show="workgroups" order="creation" style="td" wrapper="none"}}\r\n', '<a name="p49188-1"></a><p class="auto" id="p49188-1"><!--notypo-->ЎЎdirectory show="users" order="creation" style="td" wrapper="none"ЎЎ<!--/notypo--></p><a name="h49188-1"></a><h3> Сообщества </h3><a name="p49188-2"></a><p class="auto" id="p49188-2">\n<!--notypo-->ЎЎdirectory show="communities" order="creation" style="td" wrapper="none"ЎЎ<!--/notypo--></p><a name="h49188-2"></a><h3> Рабочие группы </h3><a name="p49188-3"></a><p class="auto" id="p49188-3">\n<!--notypo-->ЎЎdirectory show="workgroups" order="creation" style="td" wrapper="none"ЎЎ<!--/notypo--></p>', '', 'p49188-1<poloskuns,col>(p)<poloskuns,col>77777<poloskuns,row>h49188-1<poloskuns,col> Сообщества <poloskuns,col>3<poloskuns,row>p49188-2<poloskuns,col>(p)<poloskuns,col>77777<poloskuns,row>h49188-2<poloskuns,col> Рабочие группы <poloskuns,col>3<poloskuns,row>p49188-3<poloskuns,col>(p)<poloskuns,col>77777', '', 'wacko', '', 0, '2004-04-03 20:38:00', '2004-04-03 20:39:16', '2004-04-03 20:39:16', 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, '', '!', 'node', 'NPJ', '%%NODE_ID%%');
# %%@%%

    
# --------------------------------------------------------

#
# Структура таблицы `%%NODE_PREF%%records_rare`
#

CREATE TABLE %%NODE_PREF%%records_rare (
  record_id int(11) NOT NULL default '0',
  announced_id int(11) NOT NULL default '0',
  announced_supertag varchar(250) NOT NULL default '',
  announced_title varchar(250) NOT NULL default '',
  announced_comments int(11) NOT NULL default '0',
  announced_disallow_comments int(11) NOT NULL default '0',
  digest_dtfrom datetime NOT NULL default '0000-00-00 00:00:00',
  digest_dtto datetime NOT NULL default '0000-00-00 00:00:00',
  digest_filter int(11) NOT NULL default '0',
  rep_node_id varchar(20) NOT NULL default '',
  replicator_user_id int(11) NOT NULL default '0',
  rep_original_id int(11) NOT NULL default '0',
  PRIMARY KEY (record_id),
  KEY announced_supertag(announced_supertag),
  KEY announced_id(announced_id)
) TYPE=MyISAM;
# %%@%%

#
# Дамп данных таблицы `%%NODE_PREF%%records_rare`
#

# --------------------------------------------------------

#
# Структура таблицы `%%NODE_PREF%%records_ref`
#

CREATE TABLE %%NODE_PREF%%records_ref (
  ref_id int(11) NOT NULL auto_increment,
  record_id int(11) NOT NULL default '0',
  owner_id int(11) NOT NULL default '0',
  keyword_id int(11) NOT NULL default '0',
  keyword_user_id int(11) NOT NULL default '0',
  group1 int(11) NOT NULL default '0',
  group2 int(11) NOT NULL default '0',
  group3 int(11) NOT NULL default '0',
  group4 int(11) NOT NULL default '0',
  server_datetime datetime NOT NULL default '0000-00-00 00:00:00',
  priority int(11) NOT NULL default '0',
  need_moderation int(11) NOT NULL default '0',
  syndicate int(11) NOT NULL default '0',
  announce int(11) NOT NULL default '0',
  PRIMARY KEY (ref_id),
  UNIQUE KEY idx_unique_subspaces(record_id,keyword_id),
  KEY record_id(record_id),
  KEY owner_id(owner_id),
  KEY keyword_id(keyword_id),
  KEY keyword_user_id(keyword_user_id),
  KEY group1(group1),
  KEY group2(group2),
  KEY group3(group3),
  KEY group4(group4),
  KEY server_datetime(server_datetime),
  KEY priority(priority),
  KEY need_moderation(need_moderation),
  KEY syndicate(syndicate),
  KEY server_datetime_2(server_datetime,record_id),
  KEY syndicate_2(syndicate,keyword_id),
  KEY group2_2(group2,keyword_user_id)
) TYPE=MyISAM;
# %%@%%

#
# Дамп данных таблицы `%%NODE_PREF%%records_ref`
#

# --------------------------------------------------------

#
# Структура таблицы `%%NODE_PREF%%records_replicas`
#

CREATE TABLE %%NODE_PREF%%records_replicas (
  record_id int(11) NOT NULL default '0',
  replicated_to_node_id varchar(20) NOT NULL default '',
  replicated_to_record_id int(11) NOT NULL default '0',
  replicated_datetime datetime NOT NULL default '0000-00-00 00:00:00'
) TYPE=MyISAM;
# %%@%%

#
# Дамп данных таблицы `%%NODE_PREF%%records_replicas`
#

# --------------------------------------------------------

#
# Структура таблицы `%%NODE_PREF%%replica_dest_rules`
#

CREATE TABLE %%NODE_PREF%%replica_dest_rules (
  dest_rule_id int(11) NOT NULL auto_increment,
  rep_rule_id int(11) NOT NULL default '0',
  node_id varchar(20) NOT NULL default '',
  owner_id int(11) NOT NULL default '0',
  dest_id int(11) NOT NULL default '0',
  record_id int(11) NOT NULL default '0',
  PRIMARY KEY (dest_rule_id),
  UNIQUE KEY rep_rule_id(rep_rule_id,node_id)
) TYPE=MyISAM;
# %%@%%

#
# Дамп данных таблицы `%%NODE_PREF%%replica_dest_rules`
#

# --------------------------------------------------------

#
# Структура таблицы `%%NODE_PREF%%replica_dests`
#

CREATE TABLE %%NODE_PREF%%replica_dests (
  dest_rule_id int(11) NOT NULL default '0',
  keyword_id int(11) NOT NULL default '0',
  PRIMARY KEY (dest_rule_id,keyword_id)
) TYPE=MyISAM;
# %%@%%

#
# Дамп данных таблицы `%%NODE_PREF%%replica_dests`
#

# --------------------------------------------------------

#
# Структура таблицы `%%NODE_PREF%%replica_queue`
#

CREATE TABLE %%NODE_PREF%%replica_queue (
  id int(11) NOT NULL auto_increment,
  rep_rule_id int(11) NOT NULL default '0',
  object_id int(11) NOT NULL default '0',
  object_class varchar(20) NOT NULL default '',
  node_id varchar(20) NOT NULL default '',
  datetime datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (id)
) TYPE=MyISAM;
# %%@%%

#
# Дамп данных таблицы `%%NODE_PREF%%replica_queue`
#

# --------------------------------------------------------

#
# Структура таблицы `%%NODE_PREF%%replica_rules`
#

CREATE TABLE %%NODE_PREF%%replica_rules (
  rep_rule_id int(11) NOT NULL auto_increment,
  owner_id int(11) NOT NULL default '0',
  node_id varchar(20) NOT NULL default '',
  record_id int(11) NOT NULL default '0',
  date_from datetime NOT NULL default '0000-00-00 00:00:00',
  date_to datetime NOT NULL default '0000-00-00 00:00:00',
  dont_doublereplicate int(11) NOT NULL default '0',
  maxperday int(11) NOT NULL default '0',
  maxdepth int(11) NOT NULL default '0',
  authors_white text NOT NULL,
  authors_black text NOT NULL,
  topic_white text NOT NULL,
  topic_black text NOT NULL,
  todaycount int(11) NOT NULL default '0',
  last datetime NOT NULL default '0000-00-00 00:00:00',
  facet_white text NOT NULL,
  facet_black text NOT NULL,
  valid tinyint(4) NOT NULL default '0',
  reptype tinyint(4) NOT NULL default '0',
  PRIMARY KEY (rep_rule_id),
  KEY valid(valid)
) TYPE=MyISAM;
# %%@%%

#
# Дамп данных таблицы `%%NODE_PREF%%replica_rules`
#

# --------------------------------------------------------

#
# Структура таблицы `%%NODE_PREF%%subscription`
#

CREATE TABLE %%NODE_PREF%%subscription (
  object_class varchar(20) NOT NULL default 'record',
  object_id int(11) NOT NULL default '0',
  object_method varchar(20) NOT NULL default '',
  method_option int(11) NOT NULL default '0',
  user_id int(11) NOT NULL default '0',
  KEY object_class(object_class,object_id,object_method)
) TYPE=MyISAM;
# %%@%%

#
# Дамп данных таблицы `%%NODE_PREF%%subscription`
#

# --------------------------------------------------------

#
# Структура таблицы `%%NODE_PREF%%usage_stats`
#

CREATE TABLE %%NODE_PREF%%usage_stats (
  _id int(11) NOT NULL auto_increment,
  event varchar(20) NOT NULL default '',
  principal_user_id int(11) NOT NULL default '0',
  object_id int(11) NOT NULL default '0',
  object_address varchar(250) NOT NULL default '',
  object_class varchar(20) NOT NULL default '',
  object_method varchar(50) NOT NULL default '',
  object_params varchar(50) NOT NULL default '',
  server_datetime datetime NOT NULL default '0000-00-00 00:00:00',
  already_processed int(11) NOT NULL default '0',
  PRIMARY KEY (_id)
) TYPE=MyISAM;
# %%@%%

#
# Дамп данных таблицы `%%NODE_PREF%%usage_stats`
#

# --------------------------------------------------------

#
# Структура таблицы `%%NODE_PREF%%user_groups`
#

CREATE TABLE %%NODE_PREF%%user_groups (
  ug_id int(11) NOT NULL auto_increment,
  group_id int(11) NOT NULL default '0',
  user_id int(11) NOT NULL default '0',
  keyword_id int(11) NOT NULL default '0',
  PRIMARY KEY (ug_id),
  KEY group_id(group_id),
  KEY user_id(user_id),
  KEY keyword_id(keyword_id),
  KEY group_id_2(group_id,user_id),
  KEY user_id_2(user_id,group_id)
) TYPE=MyISAM;
# %%@%%

#
# Дамп данных таблицы `%%NODE_PREF%%user_groups`
#

INSERT INTO %%NODE_PREF%%user_groups (ug_id, group_id, user_id, keyword_id) VALUES (1, 1, 1, 1);
# %%@%%
INSERT INTO %%NODE_PREF%%user_groups (ug_id, group_id, user_id, keyword_id) VALUES (2, 2, 2, 2);
# %%@%%
INSERT INTO %%NODE_PREF%%user_groups (ug_id, group_id, user_id, keyword_id) VALUES (3, 82, 1, 1);
# %%@%%
INSERT INTO %%NODE_PREF%%user_groups (ug_id, group_id, user_id, keyword_id) VALUES (4, 81, 2, 2);
# %%@%%
# --------------------------------------------------------

#
# Структура таблицы `%%NODE_PREF%%user_menu`
#

CREATE TABLE %%NODE_PREF%%user_menu (
  item_id int(11) NOT NULL auto_increment,
  user_id int(11) NOT NULL default '0',
  npj_address varchar(250) NOT NULL default '',
  title varchar(250) NOT NULL default '',
  pos int(11) NOT NULL default '0',
  PRIMARY KEY (item_id),
  KEY user_id(user_id,pos)
) TYPE=MyISAM;
# %%@%%

#
# Дамп данных таблицы `%%NODE_PREF%%user_menu`
#

INSERT INTO %%NODE_PREF%%user_menu (item_id, user_id, npj_address, title, pos) VALUES (1, 2, '(!)', 'Мой журнал', 1);
# %%@%%
INSERT INTO %%NODE_PREF%%user_menu (item_id, user_id, npj_address, title, pos) VALUES (2, 2, '(!):/RecentChanges', 'Мои последние изменения', 2);
# %%@%%
INSERT INTO %%NODE_PREF%%user_menu (item_id, user_id, npj_address, title, pos) VALUES (3, 2, '(!):/JournalIndex', 'Мои документы', 3);
# %%@%%
INSERT INTO %%NODE_PREF%%user_menu (item_id, user_id, npj_address, title, pos) VALUES (4, 2, '(!):/Feed', 'Моя лента', 4);
# %%@%%
INSERT INTO %%NODE_PREF%%user_menu (item_id, user_id, npj_address, title, pos) VALUES (5, 2, '(!):/Manage', 'Управление журналом', 5);
# %%@%%
# --------------------------------------------------------

#
# Структура таблицы `%%NODE_PREF%%userpics`
#

CREATE TABLE %%NODE_PREF%%userpics (
  pic_id int(11) NOT NULL auto_increment,
  user_id int(11) NOT NULL default '0',
  description varchar(50) NOT NULL default '',
  have_big varchar(5) NOT NULL default '',
  have_small varchar(5) NOT NULL default '',
  PRIMARY KEY (pic_id),
  KEY user_id(user_id)
) TYPE=MyISAM;
# %%@%%

#
# Дамп данных таблицы `%%NODE_PREF%%userpics`
#

# --------------------------------------------------------

#
# Структура таблицы `%%NODE_PREF%%users`
#

CREATE TABLE %%NODE_PREF%%users (
  user_id int(11) NOT NULL auto_increment,
  root_record_id int(11) NOT NULL default '0',
  login varchar(20) NOT NULL default '',
  user_name varchar(250) NOT NULL default '',
  user_login VARCHAR( 20 ) NOT NULL,
  node_id varchar(20) NOT NULL default 'local',
  owner_user_id int(11) NOT NULL default '0',
  account_type int(11) NOT NULL default '0',
  populate_type INT(11) NOT NULL,
  alive int(11) NOT NULL default '1',
  password varchar(32) NOT NULL default '',
  _formatting varchar(20) NOT NULL default 'wacko',
  __roles varchar(250) NOT NULL default 'user',
  last_login_datetime datetime NOT NULL default '0000-00-00 00:00:00',
  last_logout_datetime datetime NOT NULL default '0000-00-00 00:00:00',
  login_cookie varchar(32) NOT NULL default '',
  _pic_id int(11) NOT NULL default '0',
  theme varchar(20) NOT NULL default 'absent',
  skin_override varchar(20) NOT NULL default '',
  group_versions_override INT  NOT NULL DEFAULT '-1',
  lang varchar(20) NOT NULL default 'std',
  more TEXT NOT NULL,
  csa varchar(50) NOT NULL default '',
  email varchar(250) NOT NULL default '',
  original_user_id INT NOT NULL,
  PRIMARY KEY (user_id),
  KEY login(login,node_id),
  KEY node_id(node_id,login),
  KEY account_type(account_type)
) TYPE=MyISAM;
# %%@%%

#
# Дамп данных таблицы `%%NODE_PREF%%users`
#

INSERT INTO %%NODE_PREF%%users VALUES (1, 1, 'guest', 'Неизвестный посетитель', 'guest', '%%NODE_ID%%', 3, 0, 0, 1, 'none', 'wacko', 'user', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', 1, 'absent', '', '-1', 'std', 'double_click=1', '', '', 0);
# %%@%%
INSERT INTO %%NODE_PREF%%users VALUES (2, 2, 'node', 'Журнал узла', 'node', '%%NODE_ID%%', 3, 2, 0, 1, 'none', 'wacko', 'user', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', 1, 'absent', '', '-1', 'std', 'double_click=1', '', '', 0);
# %%@%%

CREATE TABLE %%NODE_PREF%%records_ref_rules (
  id int(11) NOT NULL auto_increment,
  keyword_id int(11) NOT NULL default '0',
  field varchar(250) NOT NULL default '',
  value varchar(250) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY keyword_id (keyword_id)
) TYPE=MyISAM;
# %%@%%

ALTER TABLE %%NODE_PREF%%records_ref ADD user_datetime DATETIME NOT NULL AFTER server_datetime;
# %%@%%

CREATE TABLE %%NODE_PREF%%ban_ip (
id INT NOT NULL AUTO_INCREMENT,
ip VARCHAR( 16 ) NOT NULL ,
iplong INT NOT NULL ,
banned_datetime DATETIME NOT NULL ,
PRIMARY KEY ( id ) ,
INDEX ( iplong )
);
# %%@%%