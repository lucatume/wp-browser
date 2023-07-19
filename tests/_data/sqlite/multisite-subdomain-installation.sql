CREATE TABLE wp_termmeta (
	meta_id  integer   NOT NULL  PRIMARY KEY AUTOINCREMENT ,
	term_id  integer   NOT NULL default '0',
	meta_key   text default NULL,
	meta_value  text 
);

CREATE TABLE wp_terms (
 term_id  integer   NOT NULL  PRIMARY KEY AUTOINCREMENT ,
 name   text NOT NULL default '',
 slug   text NOT NULL default '',
 term_group  integer NOT NULL default 0
);

INSERT INTO wp_terms (term_id,name,slug,term_group) VALUES
('1','Uncategorized','uncategorized','0');

CREATE TABLE wp_term_taxonomy (
 term_taxonomy_id  integer   NOT NULL  PRIMARY KEY AUTOINCREMENT ,
 term_id  integer   NOT NULL default 0,
 taxonomy   text NOT NULL default '',
 description  text NOT NULL,
 parent  integer   NOT NULL default 0,
 count  integer NOT NULL default 0);

INSERT INTO wp_term_taxonomy (term_taxonomy_id,term_id,taxonomy,description,parent,count) VALUES
('1','1','category','','0','1');

CREATE TABLE wp_term_relationships (
 object_id  integer   NOT NULL default 0,
 term_taxonomy_id  integer   NOT NULL default 0,
 term_order   integer NOT NULL default 0
);

INSERT INTO wp_term_relationships (object_id,term_taxonomy_id,term_order) VALUES
('1','1','0');

CREATE TABLE wp_commentmeta (
	meta_id  integer   NOT NULL  PRIMARY KEY AUTOINCREMENT ,
	comment_id  integer   NOT NULL default '0',
	meta_key   text default NULL,
	meta_value  text 
);

CREATE TABLE wp_comments (
	comment_ID  integer   NOT NULL  PRIMARY KEY AUTOINCREMENT ,
	comment_post_ID  integer   NOT NULL default '0',
	comment_author   text NOT NULL,
	comment_author_email   text NOT NULL default '',
	comment_author_url   text NOT NULL default '',
	comment_author_IP   text NOT NULL default '',
	comment_date   text NOT NULL default '0000-00-00 00:00:00',
	comment_date_gmt   text NOT NULL default '0000-00-00 00:00:00',
	comment_content  text NOT NULL,
	comment_karma   integer NOT NULL default '0',
	comment_approved   text NOT NULL default '1',
	comment_agent   text NOT NULL default '',
	comment_type   text NOT NULL default 'comment',
	comment_parent  integer   NOT NULL default '0',
	user_id  integer   NOT NULL default '0'
);

INSERT INTO wp_comments (comment_ID,comment_post_ID,comment_author,comment_author_email,comment_author_url,comment_author_IP,comment_date,comment_date_gmt,comment_content,comment_karma,comment_approved,comment_agent,comment_type,comment_parent,user_id) VALUES
('1','1','A WordPress Commenter','wapuu@wordpress.example','https://wordpress.org/','','2023-07-21 15:25:56','2023-07-21 15:25:56','Hi, this is a comment.' || char(10) || 'To get started with moderating, editing, and deleting comments, please visit the Comments screen in the dashboard.' || char(10) || 'Commenter avatars come from <a href="https://en.gravatar.com/">Gravatar</a>.','0','1','','comment','0','0');

CREATE TABLE wp_links (
	link_id  integer   NOT NULL  PRIMARY KEY AUTOINCREMENT ,
	link_url   text NOT NULL default '',
	link_name   text NOT NULL default '',
	link_image   text NOT NULL default '',
	link_target   text NOT NULL default '',
	link_description   text NOT NULL default '',
	link_visible   text NOT NULL default 'Y',
	link_owner  integer   NOT NULL default '1',
	link_rating   integer NOT NULL default '0',
	link_updated   text NOT NULL default '0000-00-00 00:00:00',
	link_rel   text NOT NULL default '',
	link_notes  text NOT NULL,
	link_rss   text NOT NULL default ''
);

CREATE TABLE wp_options (
	option_id  integer   NOT NULL  PRIMARY KEY AUTOINCREMENT ,
	option_name   text NOT NULL default '',
	option_value  text NOT NULL,
	autoload   text NOT NULL default 'yes');

INSERT INTO wp_options (option_id,option_name,option_value,autoload) VALUES
('1','siteurl','http://example.com','yes'),
('2','home','http://example.com','yes'),
('3','blogname','Test','yes'),
('4','blogdescription','','yes'),
('5','users_can_register','0','yes'),
('6','admin_email','admin@example.com','yes'),
('7','start_of_week','1','yes'),
('8','use_balanceTags','0','yes'),
('9','use_smilies','1','yes'),
('10','require_name_email','1','yes'),
('11','comments_notify','1','yes'),
('12','posts_per_rss','10','yes'),
('13','rss_use_excerpt','0','yes'),
('14','mailserver_url','mail.example.com','yes'),
('15','mailserver_login','login@example.com','yes'),
('16','mailserver_pass','password','yes'),
('17','mailserver_port','110','yes'),
('18','default_category','1','yes'),
('19','default_comment_status','open','yes'),
('20','default_ping_status','open','yes'),
('21','default_pingback_flag','1','yes'),
('22','posts_per_page','10','yes'),
('23','date_format','F j, Y','yes'),
('24','time_format','g:i a','yes'),
('25','links_updated_date_format','F j, Y g:i a','yes'),
('26','comment_moderation','0','yes'),
('27','moderation_notify','1','yes'),
('28','permalink_structure','/%year%/%monthnum%/%day%/%postname%/','yes'),
('29','rewrite_rules','a:96:{s:11:"^wp-json/?$";s:22:"index.php?rest_route=/";s:14:"^wp-json/(.*)?";s:33:"index.php?rest_route=/$matches[1]";s:21:"^index.php/wp-json/?$";s:22:"index.php?rest_route=/";s:24:"^index.php/wp-json/(.*)?";s:33:"index.php?rest_route=/$matches[1]";s:17:"^wp-sitemap\.xml$";s:23:"index.php?sitemap=index";s:17:"^wp-sitemap\.xsl$";s:36:"index.php?sitemap-stylesheet=sitemap";s:23:"^wp-sitemap-index\.xsl$";s:34:"index.php?sitemap-stylesheet=index";s:48:"^wp-sitemap-([a-z]+?)-([a-z\d_-]+?)-(\d+?)\.xml$";s:75:"index.php?sitemap=$matches[1]&sitemap-subtype=$matches[2]&paged=$matches[3]";s:34:"^wp-sitemap-([a-z]+?)-(\d+?)\.xml$";s:47:"index.php?sitemap=$matches[1]&paged=$matches[2]";s:47:"category/(.+?)/feed/(feed|rdf|rss|rss2|atom)/?$";s:52:"index.php?category_name=$matches[1]&feed=$matches[2]";s:42:"category/(.+?)/(feed|rdf|rss|rss2|atom)/?$";s:52:"index.php?category_name=$matches[1]&feed=$matches[2]";s:23:"category/(.+?)/embed/?$";s:46:"index.php?category_name=$matches[1]&embed=true";s:35:"category/(.+?)/page/?([0-9]{1,})/?$";s:53:"index.php?category_name=$matches[1]&paged=$matches[2]";s:17:"category/(.+?)/?$";s:35:"index.php?category_name=$matches[1]";s:44:"tag/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$";s:42:"index.php?tag=$matches[1]&feed=$matches[2]";s:39:"tag/([^/]+)/(feed|rdf|rss|rss2|atom)/?$";s:42:"index.php?tag=$matches[1]&feed=$matches[2]";s:20:"tag/([^/]+)/embed/?$";s:36:"index.php?tag=$matches[1]&embed=true";s:32:"tag/([^/]+)/page/?([0-9]{1,})/?$";s:43:"index.php?tag=$matches[1]&paged=$matches[2]";s:14:"tag/([^/]+)/?$";s:25:"index.php?tag=$matches[1]";s:45:"type/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$";s:50:"index.php?post_format=$matches[1]&feed=$matches[2]";s:40:"type/([^/]+)/(feed|rdf|rss|rss2|atom)/?$";s:50:"index.php?post_format=$matches[1]&feed=$matches[2]";s:21:"type/([^/]+)/embed/?$";s:44:"index.php?post_format=$matches[1]&embed=true";s:33:"type/([^/]+)/page/?([0-9]{1,})/?$";s:51:"index.php?post_format=$matches[1]&paged=$matches[2]";s:15:"type/([^/]+)/?$";s:33:"index.php?post_format=$matches[1]";s:12:"robots\.txt$";s:18:"index.php?robots=1";s:13:"favicon\.ico$";s:19:"index.php?favicon=1";s:48:".*wp-(atom|rdf|rss|rss2|feed|commentsrss2)\.php$";s:18:"index.php?feed=old";s:20:".*wp-app\.php(/.*)?$";s:19:"index.php?error=403";s:18:".*wp-register.php$";s:23:"index.php?register=true";s:32:"feed/(feed|rdf|rss|rss2|atom)/?$";s:27:"index.php?&feed=$matches[1]";s:27:"(feed|rdf|rss|rss2|atom)/?$";s:27:"index.php?&feed=$matches[1]";s:8:"embed/?$";s:21:"index.php?&embed=true";s:20:"page/?([0-9]{1,})/?$";s:28:"index.php?&paged=$matches[1]";s:41:"comments/feed/(feed|rdf|rss|rss2|atom)/?$";s:42:"index.php?&feed=$matches[1]&withcomments=1";s:36:"comments/(feed|rdf|rss|rss2|atom)/?$";s:42:"index.php?&feed=$matches[1]&withcomments=1";s:17:"comments/embed/?$";s:21:"index.php?&embed=true";s:44:"search/(.+)/feed/(feed|rdf|rss|rss2|atom)/?$";s:40:"index.php?s=$matches[1]&feed=$matches[2]";s:39:"search/(.+)/(feed|rdf|rss|rss2|atom)/?$";s:40:"index.php?s=$matches[1]&feed=$matches[2]";s:20:"search/(.+)/embed/?$";s:34:"index.php?s=$matches[1]&embed=true";s:32:"search/(.+)/page/?([0-9]{1,})/?$";s:41:"index.php?s=$matches[1]&paged=$matches[2]";s:14:"search/(.+)/?$";s:23:"index.php?s=$matches[1]";s:47:"author/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$";s:50:"index.php?author_name=$matches[1]&feed=$matches[2]";s:42:"author/([^/]+)/(feed|rdf|rss|rss2|atom)/?$";s:50:"index.php?author_name=$matches[1]&feed=$matches[2]";s:23:"author/([^/]+)/embed/?$";s:44:"index.php?author_name=$matches[1]&embed=true";s:35:"author/([^/]+)/page/?([0-9]{1,})/?$";s:51:"index.php?author_name=$matches[1]&paged=$matches[2]";s:17:"author/([^/]+)/?$";s:33:"index.php?author_name=$matches[1]";s:69:"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/feed/(feed|rdf|rss|rss2|atom)/?$";s:80:"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]";s:64:"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/(feed|rdf|rss|rss2|atom)/?$";s:80:"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]";s:45:"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/embed/?$";s:74:"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&embed=true";s:57:"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/page/?([0-9]{1,})/?$";s:81:"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&paged=$matches[4]";s:39:"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/?$";s:63:"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]";s:56:"([0-9]{4})/([0-9]{1,2})/feed/(feed|rdf|rss|rss2|atom)/?$";s:64:"index.php?year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]";s:51:"([0-9]{4})/([0-9]{1,2})/(feed|rdf|rss|rss2|atom)/?$";s:64:"index.php?year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]";s:32:"([0-9]{4})/([0-9]{1,2})/embed/?$";s:58:"index.php?year=$matches[1]&monthnum=$matches[2]&embed=true";s:44:"([0-9]{4})/([0-9]{1,2})/page/?([0-9]{1,})/?$";s:65:"index.php?year=$matches[1]&monthnum=$matches[2]&paged=$matches[3]";s:26:"([0-9]{4})/([0-9]{1,2})/?$";s:47:"index.php?year=$matches[1]&monthnum=$matches[2]";s:43:"([0-9]{4})/feed/(feed|rdf|rss|rss2|atom)/?$";s:43:"index.php?year=$matches[1]&feed=$matches[2]";s:38:"([0-9]{4})/(feed|rdf|rss|rss2|atom)/?$";s:43:"index.php?year=$matches[1]&feed=$matches[2]";s:19:"([0-9]{4})/embed/?$";s:37:"index.php?year=$matches[1]&embed=true";s:31:"([0-9]{4})/page/?([0-9]{1,})/?$";s:44:"index.php?year=$matches[1]&paged=$matches[2]";s:13:"([0-9]{4})/?$";s:26:"index.php?year=$matches[1]";s:58:"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/attachment/([^/]+)/?$";s:32:"index.php?attachment=$matches[1]";s:68:"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/attachment/([^/]+)/trackback/?$";s:37:"index.php?attachment=$matches[1]&tb=1";s:88:"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$";s:49:"index.php?attachment=$matches[1]&feed=$matches[2]";s:83:"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$";s:49:"index.php?attachment=$matches[1]&feed=$matches[2]";s:83:"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/attachment/([^/]+)/comment-page-([0-9]{1,})/?$";s:50:"index.php?attachment=$matches[1]&cpage=$matches[2]";s:64:"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/attachment/([^/]+)/embed/?$";s:43:"index.php?attachment=$matches[1]&embed=true";s:53:"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)/embed/?$";s:91:"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&embed=true";s:57:"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)/trackback/?$";s:85:"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&tb=1";s:77:"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$";s:97:"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&feed=$matches[5]";s:72:"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)/(feed|rdf|rss|rss2|atom)/?$";s:97:"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&feed=$matches[5]";s:65:"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)/page/?([0-9]{1,})/?$";s:98:"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&paged=$matches[5]";s:72:"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)/comment-page-([0-9]{1,})/?$";s:98:"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&cpage=$matches[5]";s:61:"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)(?:/([0-9]+))?/?$";s:97:"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&page=$matches[5]";s:47:"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/([^/]+)/?$";s:32:"index.php?attachment=$matches[1]";s:57:"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/([^/]+)/trackback/?$";s:37:"index.php?attachment=$matches[1]&tb=1";s:77:"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$";s:49:"index.php?attachment=$matches[1]&feed=$matches[2]";s:72:"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/([^/]+)/(feed|rdf|rss|rss2|atom)/?$";s:49:"index.php?attachment=$matches[1]&feed=$matches[2]";s:72:"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/([^/]+)/comment-page-([0-9]{1,})/?$";s:50:"index.php?attachment=$matches[1]&cpage=$matches[2]";s:53:"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/([^/]+)/embed/?$";s:43:"index.php?attachment=$matches[1]&embed=true";s:64:"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/comment-page-([0-9]{1,})/?$";s:81:"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&cpage=$matches[4]";s:51:"([0-9]{4})/([0-9]{1,2})/comment-page-([0-9]{1,})/?$";s:65:"index.php?year=$matches[1]&monthnum=$matches[2]&cpage=$matches[3]";s:38:"([0-9]{4})/comment-page-([0-9]{1,})/?$";s:44:"index.php?year=$matches[1]&cpage=$matches[2]";s:27:".?.+?/attachment/([^/]+)/?$";s:32:"index.php?attachment=$matches[1]";s:37:".?.+?/attachment/([^/]+)/trackback/?$";s:37:"index.php?attachment=$matches[1]&tb=1";s:57:".?.+?/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$";s:49:"index.php?attachment=$matches[1]&feed=$matches[2]";s:52:".?.+?/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$";s:49:"index.php?attachment=$matches[1]&feed=$matches[2]";s:52:".?.+?/attachment/([^/]+)/comment-page-([0-9]{1,})/?$";s:50:"index.php?attachment=$matches[1]&cpage=$matches[2]";s:33:".?.+?/attachment/([^/]+)/embed/?$";s:43:"index.php?attachment=$matches[1]&embed=true";s:16:"(.?.+?)/embed/?$";s:41:"index.php?pagename=$matches[1]&embed=true";s:20:"(.?.+?)/trackback/?$";s:35:"index.php?pagename=$matches[1]&tb=1";s:40:"(.?.+?)/feed/(feed|rdf|rss|rss2|atom)/?$";s:47:"index.php?pagename=$matches[1]&feed=$matches[2]";s:35:"(.?.+?)/(feed|rdf|rss|rss2|atom)/?$";s:47:"index.php?pagename=$matches[1]&feed=$matches[2]";s:28:"(.?.+?)/page/?([0-9]{1,})/?$";s:48:"index.php?pagename=$matches[1]&paged=$matches[2]";s:35:"(.?.+?)/comment-page-([0-9]{1,})/?$";s:48:"index.php?pagename=$matches[1]&cpage=$matches[2]";s:24:"(.?.+?)(?:/([0-9]+))?/?$";s:47:"index.php?pagename=$matches[1]&page=$matches[2]";}','yes'),
('30','hack_file','0','yes'),
('31','blog_charset','UTF-8','yes'),
('32','moderation_keys','','no'),
('33','active_plugins','a:0:{}','yes'),
('34','category_base','','yes'),
('35','ping_sites','http://rpc.pingomatic.com/','yes'),
('36','comment_max_links','2','yes'),
('37','gmt_offset','0','yes'),
('38','default_email_category','1','yes'),
('39','recently_edited','','no'),
('40','template','twentytwentythree','yes'),
('41','stylesheet','twentytwentythree','yes'),
('42','comment_registration','0','yes'),
('43','html_type','text/html','yes'),
('44','use_trackback','0','yes'),
('45','default_role','subscriber','yes'),
('46','db_version','53496','yes'),
('47','uploads_use_yearmonth_folders','1','yes'),
('48','upload_path','','yes'),
('49','blog_public','1','yes'),
('50','default_link_category','2','yes'),
('51','show_on_front','posts','yes'),
('52','tag_base','','yes'),
('53','show_avatars','1','yes'),
('54','avatar_rating','G','yes'),
('55','upload_url_path','','yes'),
('56','thumbnail_size_w','150','yes'),
('57','thumbnail_size_h','150','yes'),
('58','thumbnail_crop','1','yes'),
('59','medium_size_w','300','yes'),
('60','medium_size_h','300','yes'),
('61','avatar_default','mystery','yes'),
('62','large_size_w','1024','yes'),
('63','large_size_h','1024','yes'),
('64','image_default_link_type','none','yes'),
('65','image_default_size','','yes'),
('66','image_default_align','','yes'),
('67','close_comments_for_old_posts','0','yes'),
('68','close_comments_days_old','14','yes'),
('69','thread_comments','1','yes'),
('70','thread_comments_depth','5','yes'),
('71','page_comments','0','yes'),
('72','comments_per_page','50','yes'),
('73','default_comments_page','newest','yes'),
('74','comment_order','asc','yes'),
('75','sticky_posts','a:0:{}','yes'),
('76','widget_categories','a:0:{}','yes'),
('77','widget_text','a:0:{}','yes'),
('78','widget_rss','a:0:{}','yes'),
('79','uninstall_plugins','a:0:{}','no'),
('80','timezone_string','','yes'),
('81','page_for_posts','0','yes'),
('82','page_on_front','0','yes'),
('83','default_post_format','0','yes'),
('84','link_manager_enabled','0','yes'),
('85','finished_splitting_shared_terms','1','yes'),
('86','site_icon','0','yes'),
('87','medium_large_size_w','768','yes'),
('88','medium_large_size_h','0','yes'),
('89','wp_page_for_privacy_policy','3','yes'),
('90','show_comments_cookies_opt_in','1','yes'),
('91','admin_email_lifespan','1705505156','yes'),
('92','disallowed_keys','','no'),
('93','comment_previously_approved','1','yes'),
('94','auto_plugin_theme_update_emails','a:0:{}','no'),
('95','auto_update_core_dev','enabled','yes'),
('96','auto_update_core_minor','enabled','yes'),
('97','auto_update_core_major','enabled','yes'),
('98','wp_force_deactivated_plugins','a:0:{}','yes'),
('99','initial_db_version','53496','yes'),
('100','wp_user_roles','a:5:{s:13:"administrator";a:2:{s:4:"name";s:13:"Administrator";s:12:"capabilities";a:61:{s:13:"switch_themes";b:1;s:11:"edit_themes";b:1;s:16:"activate_plugins";b:1;s:12:"edit_plugins";b:1;s:10:"edit_users";b:1;s:10:"edit_files";b:1;s:14:"manage_options";b:1;s:17:"moderate_comments";b:1;s:17:"manage_categories";b:1;s:12:"manage_links";b:1;s:12:"upload_files";b:1;s:6:"import";b:1;s:15:"unfiltered_html";b:1;s:10:"edit_posts";b:1;s:17:"edit_others_posts";b:1;s:20:"edit_published_posts";b:1;s:13:"publish_posts";b:1;s:10:"edit_pages";b:1;s:4:"read";b:1;s:8:"level_10";b:1;s:7:"level_9";b:1;s:7:"level_8";b:1;s:7:"level_7";b:1;s:7:"level_6";b:1;s:7:"level_5";b:1;s:7:"level_4";b:1;s:7:"level_3";b:1;s:7:"level_2";b:1;s:7:"level_1";b:1;s:7:"level_0";b:1;s:17:"edit_others_pages";b:1;s:20:"edit_published_pages";b:1;s:13:"publish_pages";b:1;s:12:"delete_pages";b:1;s:19:"delete_others_pages";b:1;s:22:"delete_published_pages";b:1;s:12:"delete_posts";b:1;s:19:"delete_others_posts";b:1;s:22:"delete_published_posts";b:1;s:20:"delete_private_posts";b:1;s:18:"edit_private_posts";b:1;s:18:"read_private_posts";b:1;s:20:"delete_private_pages";b:1;s:18:"edit_private_pages";b:1;s:18:"read_private_pages";b:1;s:12:"delete_users";b:1;s:12:"create_users";b:1;s:17:"unfiltered_upload";b:1;s:14:"edit_dashboard";b:1;s:14:"update_plugins";b:1;s:14:"delete_plugins";b:1;s:15:"install_plugins";b:1;s:13:"update_themes";b:1;s:14:"install_themes";b:1;s:11:"update_core";b:1;s:10:"list_users";b:1;s:12:"remove_users";b:1;s:13:"promote_users";b:1;s:18:"edit_theme_options";b:1;s:13:"delete_themes";b:1;s:6:"export";b:1;}}s:6:"editor";a:2:{s:4:"name";s:6:"Editor";s:12:"capabilities";a:34:{s:17:"moderate_comments";b:1;s:17:"manage_categories";b:1;s:12:"manage_links";b:1;s:12:"upload_files";b:1;s:15:"unfiltered_html";b:1;s:10:"edit_posts";b:1;s:17:"edit_others_posts";b:1;s:20:"edit_published_posts";b:1;s:13:"publish_posts";b:1;s:10:"edit_pages";b:1;s:4:"read";b:1;s:7:"level_7";b:1;s:7:"level_6";b:1;s:7:"level_5";b:1;s:7:"level_4";b:1;s:7:"level_3";b:1;s:7:"level_2";b:1;s:7:"level_1";b:1;s:7:"level_0";b:1;s:17:"edit_others_pages";b:1;s:20:"edit_published_pages";b:1;s:13:"publish_pages";b:1;s:12:"delete_pages";b:1;s:19:"delete_others_pages";b:1;s:22:"delete_published_pages";b:1;s:12:"delete_posts";b:1;s:19:"delete_others_posts";b:1;s:22:"delete_published_posts";b:1;s:20:"delete_private_posts";b:1;s:18:"edit_private_posts";b:1;s:18:"read_private_posts";b:1;s:20:"delete_private_pages";b:1;s:18:"edit_private_pages";b:1;s:18:"read_private_pages";b:1;}}s:6:"author";a:2:{s:4:"name";s:6:"Author";s:12:"capabilities";a:10:{s:12:"upload_files";b:1;s:10:"edit_posts";b:1;s:20:"edit_published_posts";b:1;s:13:"publish_posts";b:1;s:4:"read";b:1;s:7:"level_2";b:1;s:7:"level_1";b:1;s:7:"level_0";b:1;s:12:"delete_posts";b:1;s:22:"delete_published_posts";b:1;}}s:11:"contributor";a:2:{s:4:"name";s:11:"Contributor";s:12:"capabilities";a:5:{s:10:"edit_posts";b:1;s:4:"read";b:1;s:7:"level_1";b:1;s:7:"level_0";b:1;s:12:"delete_posts";b:1;}}s:10:"subscriber";a:2:{s:4:"name";s:10:"Subscriber";s:12:"capabilities";a:2:{s:4:"read";b:1;s:7:"level_0";b:1;}}}','yes'),
('101','fresh_site','1','yes'),
('102','user_count','1','no'),
('103','widget_block','a:6:{i:2;a:1:{s:7:"content";s:19:"<!-- wp:search /-->";}i:3;a:1:{s:7:"content";s:154:"<!-- wp:group --><div class="wp-block-group"><!-- wp:heading --><h2>Recent Posts</h2><!-- /wp:heading --><!-- wp:latest-posts /--></div><!-- /wp:group -->";}i:4;a:1:{s:7:"content";s:227:"<!-- wp:group --><div class="wp-block-group"><!-- wp:heading --><h2>Recent Comments</h2><!-- /wp:heading --><!-- wp:latest-comments {"displayAvatar":false,"displayDate":false,"displayExcerpt":false} /--></div><!-- /wp:group -->";}i:5;a:1:{s:7:"content";s:146:"<!-- wp:group --><div class="wp-block-group"><!-- wp:heading --><h2>Archives</h2><!-- /wp:heading --><!-- wp:archives /--></div><!-- /wp:group -->";}i:6;a:1:{s:7:"content";s:150:"<!-- wp:group --><div class="wp-block-group"><!-- wp:heading --><h2>Categories</h2><!-- /wp:heading --><!-- wp:categories /--></div><!-- /wp:group -->";}s:12:"_multiwidget";i:1;}','yes'),
('104','sidebars_widgets','a:4:{s:19:"wp_inactive_widgets";a:0:{}s:9:"sidebar-1";a:3:{i:0;s:7:"block-2";i:1;s:7:"block-3";i:2;s:7:"block-4";}s:9:"sidebar-2";a:2:{i:0;s:7:"block-5";i:1;s:7:"block-6";}s:13:"array_version";i:3;}','yes'),
('105','cron','a:4:{i:1689953156;a:8:{s:18:"wp_https_detection";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:10:"twicedaily";s:4:"args";a:0:{}s:8:"interval";i:43200;}}s:34:"wp_privacy_delete_old_export_files";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:6:"hourly";s:4:"args";a:0:{}s:8:"interval";i:3600;}}s:16:"wp_version_check";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:10:"twicedaily";s:4:"args";a:0:{}s:8:"interval";i:43200;}}s:17:"wp_update_plugins";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:10:"twicedaily";s:4:"args";a:0:{}s:8:"interval";i:43200;}}s:16:"wp_update_themes";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:10:"twicedaily";s:4:"args";a:0:{}s:8:"interval";i:43200;}}s:19:"wp_scheduled_delete";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:5:"daily";s:4:"args";a:0:{}s:8:"interval";i:86400;}}s:25:"delete_expired_transients";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:5:"daily";s:4:"args";a:0:{}s:8:"interval";i:86400;}}s:21:"wp_update_user_counts";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:10:"twicedaily";s:4:"args";a:0:{}s:8:"interval";i:43200;}}}i:1689953216;a:1:{s:28:"wp_update_comment_type_batch";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:2:{s:8:"schedule";b:0;s:4:"args";a:0:{}}}}i:1690039556;a:1:{s:30:"wp_site_health_scheduled_check";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:6:"weekly";s:4:"args";a:0:{}s:8:"interval";i:604800;}}}s:7:"version";i:2;}','yes'),
('106','widget_pages','a:1:{s:12:"_multiwidget";i:1;}','yes'),
('107','widget_calendar','a:1:{s:12:"_multiwidget";i:1;}','yes'),
('108','widget_archives','a:1:{s:12:"_multiwidget";i:1;}','yes'),
('109','widget_media_audio','a:1:{s:12:"_multiwidget";i:1;}','yes'),
('110','widget_media_image','a:1:{s:12:"_multiwidget";i:1;}','yes'),
('111','widget_media_gallery','a:1:{s:12:"_multiwidget";i:1;}','yes'),
('112','widget_media_video','a:1:{s:12:"_multiwidget";i:1;}','yes'),
('113','widget_meta','a:1:{s:12:"_multiwidget";i:1;}','yes'),
('114','widget_search','a:1:{s:12:"_multiwidget";i:1;}','yes'),
('115','widget_recent-posts','a:1:{s:12:"_multiwidget";i:1;}','yes'),
('116','widget_recent-comments','a:1:{s:12:"_multiwidget";i:1;}','yes'),
('117','widget_tag_cloud','a:1:{s:12:"_multiwidget";i:1;}','yes'),
('118','widget_nav_menu','a:1:{s:12:"_multiwidget";i:1;}','yes'),
('119','widget_custom_html','a:1:{s:12:"_multiwidget";i:1;}','yes'),
('120','_transient_doing_cron','1689953156.4168980121612548828125','yes'),
('121','_site_transient_update_core','O:8:"stdClass":3:{s:7:"updates";a:0:{}s:15:"version_checked";s:5:"6.2.2";s:12:"last_checked";i:1689953156;}','no'),
('122','_site_transient_update_plugins','O:8:"stdClass":1:{s:12:"last_checked";i:1689953156;}','no'),
('123','_site_transient_timeout_theme_roots','1689954956','no'),
('124','_site_transient_theme_roots','a:3:{s:15:"twentytwentyone";s:7:"/themes";s:17:"twentytwentythree";s:7:"/themes";s:15:"twentytwentytwo";s:7:"/themes";}','no'),
('125','_site_transient_update_themes','O:8:"stdClass":1:{s:12:"last_checked";i:1689953156;}','no');

CREATE TABLE wp_postmeta (
	meta_id  integer   NOT NULL  PRIMARY KEY AUTOINCREMENT ,
	post_id  integer   NOT NULL default '0',
	meta_key   text default NULL,
	meta_value  text 
);

INSERT INTO wp_postmeta (meta_id,post_id,meta_key,meta_value) VALUES
('1','2','_wp_page_template','default'),
('2','3','_wp_page_template','default');

CREATE TABLE wp_posts (
	ID  integer   NOT NULL  PRIMARY KEY AUTOINCREMENT ,
	post_author  integer   NOT NULL default '0',
	post_date   text NOT NULL default '0000-00-00 00:00:00',
	post_date_gmt   text NOT NULL default '0000-00-00 00:00:00',
	post_content  text NOT NULL,
	post_title  text NOT NULL,
	post_excerpt  text NOT NULL,
	post_status   text NOT NULL default 'publish',
	comment_status   text NOT NULL default 'open',
	ping_status   text NOT NULL default 'open',
	post_password   text NOT NULL default '',
	post_name   text NOT NULL default '',
	to_ping  text NOT NULL,
	pinged  text NOT NULL,
	post_modified   text NOT NULL default '0000-00-00 00:00:00',
	post_modified_gmt   text NOT NULL default '0000-00-00 00:00:00',
	post_content_filtered  text NOT NULL,
	post_parent  integer   NOT NULL default '0',
	guid   text NOT NULL default '',
	menu_order   integer NOT NULL default '0',
	post_type   text NOT NULL default 'post',
	post_mime_type   text NOT NULL default '',
	comment_count  integer NOT NULL default '0'
);

INSERT INTO wp_posts (ID,post_author,post_date,post_date_gmt,post_content,post_title,post_excerpt,post_status,comment_status,ping_status,post_password,post_name,to_ping,pinged,post_modified,post_modified_gmt,post_content_filtered,post_parent,guid,menu_order,post_type,post_mime_type,comment_count) VALUES
('1','1','2023-07-21 15:25:56','2023-07-21 15:25:56','<!-- wp:paragraph -->' || char(10) || '<p>Welcome to WordPress. This is your first post. Edit or delete it, then start writing!</p>' || char(10) || '<!-- /wp:paragraph -->','Hello world!','','publish','open','open','','hello-world','','','2023-07-21 15:25:56','2023-07-21 15:25:56','','0','http://example.com/?p=1','0','post','','1'),
('2','1','2023-07-21 15:25:56','2023-07-21 15:25:56','<!-- wp:paragraph -->' || char(10) || '<p>This is an example page. It''s different from a blog post because it will stay in one place and will show up in your site navigation (in most themes). Most people start with an About page that introduces them to potential site visitors. It might say something like this:</p>' || char(10) || '<!-- /wp:paragraph -->' || char(10) || '' || char(10) || '<!-- wp:quote -->' || char(10) || '<blockquote class="wp-block-quote"><p>Hi there! I''m a bike messenger by day, aspiring actor by night, and this is my website. I live in Los Angeles, have a great dog named Jack, and I like pi&#241;a coladas. (And gettin'' caught in the rain.)</p></blockquote>' || char(10) || '<!-- /wp:quote -->' || char(10) || '' || char(10) || '<!-- wp:paragraph -->' || char(10) || '<p>...or something like this:</p>' || char(10) || '<!-- /wp:paragraph -->' || char(10) || '' || char(10) || '<!-- wp:quote -->' || char(10) || '<blockquote class="wp-block-quote"><p>The XYZ Doohickey Company was founded in 1971, and has been providing quality doohickeys to the public ever since. Located in Gotham City, XYZ employs over 2,000 people and does all kinds of awesome things for the Gotham community.</p></blockquote>' || char(10) || '<!-- /wp:quote -->' || char(10) || '' || char(10) || '<!-- wp:paragraph -->' || char(10) || '<p>As a new WordPress user, you should go to <a href="http://example.com/wp-admin/">your dashboard</a> to delete this page and create new pages for your content. Have fun!</p>' || char(10) || '<!-- /wp:paragraph -->','Sample Page','','publish','closed','open','','sample-page','','','2023-07-21 15:25:56','2023-07-21 15:25:56','','0','http://example.com/?page_id=2','0','page','','0'),
('3','1','2023-07-21 15:25:56','2023-07-21 15:25:56','<!-- wp:heading --><h2>Who we are</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class="privacy-policy-tutorial">Suggested text: </strong>Our website address is: http://example.com.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Comments</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class="privacy-policy-tutorial">Suggested text: </strong>When visitors leave comments on the site we collect the data shown in the comments form, and also the visitor&#8217;s IP address and browser user agent string to help spam detection.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>An anonymized string created from your email address (also called a hash) may be provided to the Gravatar service to see if you are using it. The Gravatar service privacy policy is available here: https://automattic.com/privacy/. After approval of your comment, your profile picture is visible to the public in the context of your comment.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Media</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class="privacy-policy-tutorial">Suggested text: </strong>If you upload images to the website, you should avoid uploading images with embedded location data (EXIF GPS) included. Visitors to the website can download and extract any location data from images on the website.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Cookies</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class="privacy-policy-tutorial">Suggested text: </strong>If you leave a comment on our site you may opt-in to saving your name, email address and website in cookies. These are for your convenience so that you do not have to fill in your details again when you leave another comment. These cookies will last for one year.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>If you visit our login page, we will set a temporary cookie to determine if your browser accepts cookies. This cookie contains no personal data and is discarded when you close your browser.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>When you log in, we will also set up several cookies to save your login information and your screen display choices. Login cookies last for two days, and screen options cookies last for a year. If you select &quot;Remember Me&quot;, your login will persist for two weeks. If you log out of your account, the login cookies will be removed.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>If you edit or publish an article, an additional cookie will be saved in your browser. This cookie includes no personal data and simply indicates the post ID of the article you just edited. It expires after 1 day.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Embedded content from other websites</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class="privacy-policy-tutorial">Suggested text: </strong>Articles on this site may include embedded content (e.g. videos, images, articles, etc.). Embedded content from other websites behaves in the exact same way as if the visitor has visited the other website.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>These websites may collect data about you, use cookies, embed additional third-party tracking, and monitor your interaction with that embedded content, including tracking your interaction with the embedded content if you have an account and are logged in to that website.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Who we share your data with</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class="privacy-policy-tutorial">Suggested text: </strong>If you request a password reset, your IP address will be included in the reset email.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>How long we retain your data</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class="privacy-policy-tutorial">Suggested text: </strong>If you leave a comment, the comment and its metadata are retained indefinitely. This is so we can recognize and approve any follow-up comments automatically instead of holding them in a moderation queue.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>For users that register on our website (if any), we also store the personal information they provide in their user profile. All users can see, edit, or delete their personal information at any time (except they cannot change their username). Website administrators can also see and edit that information.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>What rights you have over your data</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class="privacy-policy-tutorial">Suggested text: </strong>If you have an account on this site, or have left comments, you can request to receive an exported file of the personal data we hold about you, including any data you have provided to us. You can also request that we erase any personal data we hold about you. This does not include any data we are obliged to keep for administrative, legal, or security purposes.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Where your data is sent</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class="privacy-policy-tutorial">Suggested text: </strong>Visitor comments may be checked through an automated spam detection service.</p><!-- /wp:paragraph -->','Privacy Policy','','draft','closed','open','','privacy-policy','','','2023-07-21 15:25:56','2023-07-21 15:25:56','','0','http://example.com/?page_id=3','0','page','','0');

CREATE TABLE wp_blogs (
	blog_id  integer NOT NULL  PRIMARY KEY AUTOINCREMENT ,
	site_id  integer NOT NULL default '0',
	domain   text NOT NULL default '',
	path   text NOT NULL default '',
	registered   text NOT NULL default '0000-00-00 00:00:00',
	last_updated   text NOT NULL default '0000-00-00 00:00:00',
	public   integer NOT NULL default '1',
	archived   integer NOT NULL default '0',
	mature   integer NOT NULL default '0',
	spam   integer NOT NULL default '0',
	deleted   integer NOT NULL default '0',
	lang_id   integer NOT NULL default '0'
);

INSERT INTO wp_blogs (blog_id,site_id,domain,path,registered,last_updated,public,archived,mature,spam,deleted,lang_id) VALUES
('1','1','example.com','/','2023-07-21 15:25:56','0000-00-00 00:00:00','1','0','0','0','0','0');

CREATE TABLE wp_blogmeta (
	meta_id  integer   NOT NULL  PRIMARY KEY AUTOINCREMENT ,
	blog_id  integer NOT NULL default '0',
	meta_key   text default NULL,
	meta_value  text 
);

CREATE TABLE wp_registration_log (
	ID  integer NOT NULL  PRIMARY KEY AUTOINCREMENT ,
	email   text NOT NULL default '',
	IP   text NOT NULL default '',
	blog_id  integer NOT NULL default '0',
	date_registered   text NOT NULL default '0000-00-00 00:00:00'
);

CREATE TABLE wp_site (
	id  integer NOT NULL  PRIMARY KEY AUTOINCREMENT ,
	domain   text NOT NULL default '',
	path   text NOT NULL default ''
);

INSERT INTO wp_site (id,domain,path) VALUES
('1','example.com','/');

CREATE TABLE wp_sitemeta (
	meta_id  integer NOT NULL  PRIMARY KEY AUTOINCREMENT ,
	site_id  integer NOT NULL default '0',
	meta_key   text default NULL,
	meta_value  text 
);

INSERT INTO wp_sitemeta (meta_id,site_id,meta_key,meta_value) VALUES
('1','1','site_name','Test'),
('2','1','admin_email','admin@example.com'),
('3','1','admin_user_id','1'),
('4','1','registration','none'),
('5','1','upload_filetypes','jpg jpeg png gif webp mov avi mpg 3gp 3g2 midi mid pdf doc ppt odt pptx docx pps ppsx xls xlsx key mp3 ogg flac m4a wav mp4 m4v webm ogv flv'),
('6','1','blog_upload_space','100'),
('7','1','fileupload_maxk','1500'),
('8','1','site_admins','a:1:{i:0;s:5:"admin";}'),
('9','1','allowedthemes','a:1:{s:17:"twentytwentythree";b:1;}'),
('10','1','illegal_names','a:8:{i:0;s:3:"www";i:1;s:3:"web";i:2;s:4:"root";i:3;s:5:"admin";i:4;s:4:"main";i:5;s:6:"invite";i:6;s:13:"administrator";i:7;s:5:"files";}'),
('11','1','wpmu_upgrade_site','53496'),
('12','1','welcome_email','Howdy USERNAME,' || char(10) || '' || char(10) || 'Your new SITE_NAME site has been successfully set up at:' || char(10) || 'BLOG_URL' || char(10) || '' || char(10) || 'You can log in to the administrator account with the following information:' || char(10) || '' || char(10) || 'Username: USERNAME' || char(10) || 'Password: PASSWORD' || char(10) || 'Log in here: BLOG_URLwp-login.php' || char(10) || '' || char(10) || 'We hope you enjoy your new site. Thanks!' || char(10) || '' || char(10) || '--The Team @ SITE_NAME'),
('13','1','first_post','Welcome to %s. This is your first post. Edit or delete it, then start writing!'),
('14','1','siteurl','http://example.com/'),
('15','1','add_new_users','0'),
('16','1','upload_space_check_disabled','1'),
('17','1','subdomain_install','1'),
('18','1','ms_files_rewriting','0'),
('19','1','user_count','1'),
('20','1','initial_db_version','53496'),
('21','1','active_sitewide_plugins','a:0:{}'),
('22','1','WPLANG','en_US'),
('23','1','main_site','1');

CREATE TABLE wp_signups (
	signup_id  integer NOT NULL  PRIMARY KEY AUTOINCREMENT ,
	domain   text NOT NULL default '',
	path   text NOT NULL default '',
	title  text NOT NULL,
	user_login   text NOT NULL default '',
	user_email   text NOT NULL default '',
	registered   text NOT NULL default '0000-00-00 00:00:00',
	activated   text NOT NULL default '0000-00-00 00:00:00',
	active   integer NOT NULL default '0',
	activation_key   text NOT NULL default '',
	meta  text 
);

CREATE TABLE "wp_users" (
	ID INTEGER  NOT NULL PRIMARY KEY AUTOINCREMENT,
	user_login TEXT NOT NULL DEFAULT '',
	user_pass TEXT NOT NULL DEFAULT '',
	user_nicename TEXT NOT NULL DEFAULT '',
	user_email TEXT NOT NULL DEFAULT '',
	user_url TEXT NOT NULL DEFAULT '',
	user_registered TEXT NOT NULL DEFAULT '0000-00-00 00:00:00',
	user_activation_key TEXT NOT NULL DEFAULT '',
	user_status INTEGER NOT NULL DEFAULT '0',
	display_name   text NOT NULL default ''
, spam INTEGER NOT NULL default '0', deleted INTEGER NOT NULL default '0');

INSERT INTO wp_users (ID,user_login,user_pass,user_nicename,user_email,user_url,user_registered,user_activation_key,user_status,display_name,spam,deleted) VALUES
('1','admin','$P$BHE..1rm3zozA76wayh07BrcdQkcyP1','admin','admin@example.com','','2023-07-21 15:25:56','','0','admin','0','0');

CREATE TABLE "wp_usermeta" (
	umeta_id INTEGER  NOT NULL PRIMARY KEY AUTOINCREMENT,
	user_id INTEGER NOT NULL DEFAULT '0',
	meta_key   text default NULL,
	meta_value TEXT );

INSERT INTO wp_usermeta (umeta_id,user_id,meta_key,meta_value) VALUES
('1','1','nickname','admin'),
('2','1','first_name',''),
('3','1','last_name',''),
('4','1','description',''),
('5','1','rich_editing','true'),
('6','1','syntax_highlighting','true'),
('7','1','comment_shortcuts','false'),
('8','1','admin_color','fresh'),
('9','1','use_ssl','0'),
('10','1','show_admin_bar_front','true'),
('11','1','locale',''),
('12','1','wp_capabilities','a:1:{s:13:"administrator";b:1;}'),
('13','1','wp_user_level','10'),
('14','1','dismissed_wp_pointers',''),
('15','1','show_welcome_panel','1'),
('16','1','source_domain','example.com'),
('17','1','primary_blog','1');

