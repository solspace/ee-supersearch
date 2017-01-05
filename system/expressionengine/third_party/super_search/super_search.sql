CREATE TABLE IF NOT EXISTS `exp_super_search_cache` (
`cache_id` 		int(15) 		unsigned NOT NULL AUTO_INCREMENT,
`site_id` 		int(10) 		unsigned NOT NULL DEFAULT 1,
`type` 			char(1) 		NOT NULL DEFAULT 'q',
`date` 			int(10) 		unsigned NOT NULL,
`results` 		smallint(7) 	unsigned NOT NULL DEFAULT 0,
`hash` 			varchar(32) 	NOT NULL DEFAULT '',
`ids` 			mediumtext,
`query` 		mediumtext,
PRIMARY KEY (`cache_id`),
KEY `site_id` (`site_id`),
KEY `type` (`type`),
KEY `hash` (`hash`)
) CHARACTER SET utf8 COLLATE utf8_general_ci ;;

CREATE TABLE IF NOT EXISTS `exp_super_search_history` (
`history_id` 	int(15) 		unsigned NOT NULL AUTO_INCREMENT,
`cache_id` 		int(10) 		unsigned NOT NULL DEFAULT 0,
`member_id` 	int(10) 		unsigned NOT NULL DEFAULT 0,
`cookie_id` 	int(10) 		unsigned NOT NULL DEFAULT 0,
`ip_address` 	varchar(16) 	NOT NULL DEFAULT '',
`site_id` 		int(10) 		unsigned NOT NULL DEFAULT 1,
`results` 		smallint(7) 	unsigned NOT NULL DEFAULT 0,
`search_name` 	varchar(250) 	NOT NULL DEFAULT '',
`search_date` 	int(10) 		unsigned NOT NULL DEFAULT 0,
`saved` 		char(1) 		NOT NULL DEFAULT 'n',
`historical` 	char(1) 		NOT NULL DEFAULT 'n',
`hash` 			varchar(32)		NOT NULL DEFAULT '',
`query` 		mediumtext,
`term` 			varchar(200) 	NOT NULL DEFAULT '',
PRIMARY KEY (`history_id`),
UNIQUE KEY `search_key` (`member_id`,`cookie_id`,`site_id`,`search_name`,`saved`),
KEY `cache_id` (`cache_id`),
KEY `member_id` (`member_id`),
KEY `site_id` (`site_id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci ;;

CREATE TABLE IF NOT EXISTS `exp_super_search_refresh_rules` (
`rule_id` 			int(10) 	unsigned NOT NULL auto_increment,
`site_id` 			int(10) 	unsigned NOT NULL DEFAULT 1,
`date` 				int(10) 	unsigned NOT NULL DEFAULT 0,
`refresh` 			smallint(5) unsigned NOT NULL DEFAULT 0,
`template_id` 		int(10) 	unsigned NOT NULL DEFAULT 0,
`channel_id` 		int(10) 	unsigned NOT NULL DEFAULT 0,
`category_group_id` int(10) 	unsigned NOT NULL DEFAULT 0,
`member_id` 		int(10) 	unsigned NOT NULL DEFAULT 0,
PRIMARY KEY (rule_id),
KEY `site_id` (site_id),
KEY `template_id` (template_id),
KEY `channel_id` (channel_id),
KEY `category_group_id` (category_group_id)
) CHARACTER SET utf8 COLLATE utf8_general_ci ;;


CREATE TABLE IF NOT EXISTS `exp_super_search_log` (
`log_id` 		int(15) 		unsigned NOT NULL AUTO_INCREMENT,
`site_id` 		varchar(20)		NOT NULL DEFAULT 1,
`results` 		smallint(7) 	unsigned NOT NULL DEFAULT 0,
`search_date` 	int(10) 		unsigned NOT NULL DEFAULT 0,
`term` 			varchar(200) 	NOT NULL DEFAULT '',
`query` 		mediumtext,
PRIMARY KEY (`log_id`),
KEY `site_id` (`site_id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci ;;


CREATE TABLE IF NOT EXISTS `exp_super_search_terms` (
`term_id` 		int(15) 		unsigned NOT NULL AUTO_INCREMENT,
`site_id` 		varchar(10)		NOT NULL DEFAULT 1,
`term`			varchar(200)	NOT NULL DEFAULT '',
`term_soundex`  varchar(200)	NOT NULL DEFAULT '',
`term_length`	int(10)			unsigned NOT NULL DEFAULT 0,
`first_seen` 	int(10)			unsigned NOT NULL DEFAULT 0,
`last_seen`		int(10)			unsigned NOT NULL DEFAULT 0,	
`count`			int(10)			unsigned NOT NULL DEFAULT 0,
`entry_count` 	int(10)			unsigned NOT NULL DEFAULT 0,
`suggestions`	varchar(255) 	NOT NULL DEFAULT '',
PRIMARY KEY (`term`),
KEY `term_id` (`term_id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci ;;


CREATE TABLE IF NOT EXISTS `exp_super_search_lexicon_log` (
`lexicon_id`	int(15) 		unsigned NOT NULL AUTO_INCREMENT,
`type`			varchar(10)		NOT NULL DEFAULT '',
`entry_ids`		varchar(200)	NOT NULL DEFAULT '',
`member_id`		int(10)			unsigned NOT NULL DEFAULT 0,
`origin`		varchar(200)	NOT NULL DEFAULT '',
`action_date` 	int(10)			unsigned NOT NULL DEFAULT 0,
PRIMARY KEY (`lexicon_id`),
KEY `type` (`type`),
KEY `member_id` (`member_id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci ;;


CREATE TABLE IF NOT EXISTS `exp_super_search_indexes` (
`entry_id` 		int(10)			unsigned NOT NULL DEFAULT 0,
`site_id`		int(10)			unsigned NOT NULL DEFAULT 1,
`index_date`	int(10)			unsigned NOT NULL DEFAULT 0,
PRIMARY KEY (`entry_id`),
KEY `site_id` (`site_id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci ;;
