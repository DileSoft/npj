DROP TABLE IF EXISTS %%PREFIX%%channels;
# %%@%%
DROP TABLE IF EXISTS %%PREFIX%%channels_items;
# %%@%%


CREATE TABLE %%PREFIX%%channels (
  channel_id int(11) NOT NULL auto_increment,
  user_id int(11) NOT NULL default '0',
  channel_type varchar(20) NOT NULL default '',
  source varchar(250) NOT NULL default '',
  formatting varchar(20) NOT NULL default '',
  access_login varchar(250) NOT NULL default '',
  access_pwd varchar(250) NOT NULL default '',
  access_more text NOT NULL,
  checked_datetime datetime NOT NULL default '0000-00-00 00:00:00',
  state int(11) NOT NULL default '0',
  state_verbose varchar(250) NOT NULL default '',
  managing_user_id int(11) NOT NULL default '0',
  template_subject text NOT NULL,
  template_body text NOT NULL,
  template_body_post text NOT NULL,
  PRIMARY KEY  (channel_id),
  KEY managing_user_id (managing_user_id),
  KEY state (state,checked_datetime)
) TYPE=MyISAM COMMENT='RSS+Mail Aggregator channels (1-to-1 with r1_users)';
# %%@%%


CREATE TABLE %%PREFIX%%channels_items (
  record_id int(11) NOT NULL default '0',
  author varchar(250) NOT NULL default '',
  guid_hash varchar(32) NOT NULL default '',
  channel_id int(11) NOT NULL default '0',
  PRIMARY KEY  (record_id),
  KEY guid_hash (guid_hash),
  KEY channel_id (channel_id,guid_hash)
) TYPE=MyISAM;
# %%@%%

CREATE TABLE %%PREFIX%%channels_items_rss (
  record_id int(11) NOT NULL default '0',
  link varchar(250) NOT NULL default '',
  comments varchar(250) NOT NULL default '',
  channel_id int(11) NOT NULL default '0',
  PRIMARY KEY  (record_id),
  KEY channel_id (channel_id)
) TYPE=MyISAM;
# %%@%%




/* NPZ entries */

DELETE FROM %%PREFIX%%npz WHERE param = '%%KEYCODE%%';
# %%@%%

INSERT INTO %%PREFIX%%npz 
(spec, command, last, chunk, time_last_chunk, state, param) 
VALUES 
('%%SPEC%%', '%%NODE_URL%%channels/aggregate', '0', '-1', '', 0, '%%KEYCODE%%');
# %%@%%


INSERT INTO %%PREFIX%%npz 
(spec, command, last, chunk, time_last_chunk, state, param) 
VALUES 
('%%SPEC_ERROR%%', '%%NODE_URL%%channels/aggregate/error', '0', '-1', '', 0, '%%KEYCODE%%');
# %%@%%

