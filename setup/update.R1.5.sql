CREATE TABLE %%NODE_PREF%%comments_filtered (
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

ALTER TABLE %%NODE_PREF%%records ADD `filter` TEXT NOT NULL AFTER crossposted;
# %%@%%

ALTER TABLE %%NODE_PREF%%records ADD by_module varchar(50) NOT NULL AFTER template;
# %%@%%

ALTER TABLE %%NODE_PREF%%comments ADD frozen INT NOT NULL AFTER active;
# %%@%%

UPDATE %%NODE_PREF%%nodes SET npj_version='R1.9' where is_local='1';
# %%@%%
