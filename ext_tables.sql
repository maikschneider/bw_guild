CREATE TABLE fe_users (
	short_name varchar(255) DEFAULT '' NOT NULL,
	mobile varchar(255) DEFAULT '' NOT NULL,
	member_nr varchar(255) DEFAULT '' NOT NULL,
	offers varchar(11) DEFAULT 0 NOT NULL,
	logo varchar(11) DEFAULT 0 NOT NULL,
	shared_offers int(11) unsigned DEFAULT '0' NOT NULL,
	sorting_field varchar(255) DEFAULT '' NOT NULL,
	sorting_text varchar(255) DEFAULT '' NOT NULL,
	slug varchar(255) DEFAULT '' NOT NULL,
	public_profile tinyint(4) DEFAULT 1 NOT NULL,

	bookmarks varchar(255) DEFAULT '' NOT NULL,
	company varchar(255) DEFAULT '' NOT NULL,
	name varchar(255) DEFAULT '' NOT NULL,
	categories int(11) DEFAULT 0 NOT NULL,
	latitude decimal(10, 8) DEFAULT 0 NOT NULL,
	longitude decimal(11, 8) DEFAULT 0 NOT NULL
);

CREATE TABLE tx_bwguild_domain_model_offer (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumtext,
	l10n_source int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,

	record_type varchar(255) DEFAULT '' NOT NULL,
	title varchar(255) DEFAULT '' NOT NULL,
	slug varchar(255) DEFAULT '' NOT NULL,
	address varchar(255) DEFAULT '' NOT NULL,
	zip varchar(255) DEFAULT '' NOT NULL,
	city varchar(255) DEFAULT '' NOT NULL,
	country varchar(255) DEFAULT '' NOT NULL,
	description mediumtext,
	start_date varchar(255) DEFAULT '' NOT NULL,
	latitude decimal(10, 8) DEFAULT 0 NOT NULL,
	longitude decimal(11, 8) DEFAULT 0 NOT NULL,
	fe_user int(11) DEFAULT 0 NOT NULL,
	fe_users int(11) unsigned DEFAULT '0' NOT NULL,
	conditions mediumtext,
	possibilities mediumtext,
	contact_person varchar(255) DEFAULT '' NOT NULL,
	contact_mail varchar(255) DEFAULT '' NOT NULL,
	contact_phone varchar(255) DEFAULT '' NOT NULL,
	categories int(11) DEFAULT 0 NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY sys_language_uid_l10n_parent (sys_language_uid,l10n_parent),
);

CREATE TABLE tx_bwguild_offer_feuser_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(11) DEFAULT '0' NOT NULL,
	sorting_foreign int(11) DEFAULT '0' NOT NULL,

	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);

