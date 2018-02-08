
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

ALTER TABLE %%NODE_PREF%%users ADD INDEX (alive); 
# %%@%%

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
