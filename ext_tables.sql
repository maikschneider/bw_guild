CREATE TABLE fe_users (
	short_name varchar(255) DEFAULT '' NOT NULL,
	mobile varchar(255) DEFAULT '' NOT NULL,
	member_nr varchar(255) DEFAULT '' NOT NULL,
	jobs int(11) DEFAULT 0 NOT NULL,

	company varchar(255) DEFAULT '' NOT NULL,
	name varchar(255) DEFAULT '' NOT NULL,
);

CREATE TABLE tx_bwguild_domain_model_job (
	title varchar(255) DEFAULT '' NOT NULL,
	address varchar(255) DEFAULT '' NOT NULL,
	zip varchar(255) DEFAULT '' NOT NULL,
	country varchar(255) DEFAULT '' NOT NULL,
	description varchar(255) DEFAULT '' NOT NULL,
	start_date int(11) DEFAULT '0' NOT NULL,
	geo_lat decimal(10, 8) DEFAULT 0 NOT NULL,
	geo_long decimal(11, 8) DEFAULT 0 NOT NULL,
	fe_user int(11) DEFAULT 0 NOT NULL
);
