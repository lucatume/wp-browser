CREATE TABLE wp_users (
    ID  integer   NOT NULL  PRIMARY KEY AUTOINCREMENT ,
	user_login   text NOT NULL default '',
	user_pass   text NOT NULL default '',
	user_nicename   text NOT NULL default '',
	user_email   text NOT NULL default '',
	user_url   text NOT NULL default '',
	user_registered   text NOT NULL default '0000-00-00 00:00:00',
	user_activation_key   text NOT NULL default '',
	user_status   integer NOT NULL default '0',
	display_name   text NOT NULL default ''
);

INSERT INTO wp_users (ID,user_login,user_pass,user_nicename,user_email,user_url,user_registered,user_activation_key,user_status,display_name) VALUES
('1','admin','$P$BqAqlDVWYHmzIpGooj.vkq.w7yiFm.1','admin','admin@example.com','','2023-07-19 22:09:43','','0','admin');

CREATE TABLE wp_usermeta (
	umeta_id  integer   NOT NULL  PRIMARY KEY AUTOINCREMENT ,
	user_id  integer   NOT NULL default '0',
	meta_key   text default NULL,
	meta_value  text
);

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
('15','1','show_welcome_panel','1');

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
('1','1','A WordPress Commenter','wapuu@wordpress.example','https://wordpress.org/','','2023-07-19 22:09:43','2023-07-19 22:09:43','Hi, this is a comment.
To get started with moderating, editing, and deleting comments, please visit the Comments screen in the dashboard.
Commenter avatars come from <a href="https://en.gravatar.com/">Gravatar</a>.','0','1','','comment','0','0');

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
('28','permalink_structure','','yes'),
('29','rewrite_rules','','yes'),
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
('91','admin_email_lifespan','1705356583','yes'),
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
('105','cron','a:3:{i:1689804584;a:5:{s:18:"wp_https_detection";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:10:"twicedaily";s:4:"args";a:0:{}s:8:"interval";i:43200;}}s:34:"wp_privacy_delete_old_export_files";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:6:"hourly";s:4:"args";a:0:{}s:8:"interval";i:3600;}}s:16:"wp_version_check";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:10:"twicedaily";s:4:"args";a:0:{}s:8:"interval";i:43200;}}s:17:"wp_update_plugins";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:10:"twicedaily";s:4:"args";a:0:{}s:8:"interval";i:43200;}}s:16:"wp_update_themes";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:10:"twicedaily";s:4:"args";a:0:{}s:8:"interval";i:43200;}}}i:1689890984;a:1:{s:30:"wp_site_health_scheduled_check";a:1:{s:32:"40cd750bba9870f18aada2478b24840a";a:3:{s:8:"schedule";s:6:"weekly";s:4:"args";a:0:{}s:8:"interval";i:604800;}}}s:7:"version";i:2;}','yes'),
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
('120','_transient_doing_cron','1689804584.0911190509796142578125','yes');

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
('1','1','2023-07-19 22:09:43','2023-07-19 22:09:43','<!-- wp:paragraph -->
<p>Welcome to WordPress. This is your first post. Edit or delete it, then start writing!</p>
<!-- /wp:paragraph -->','Hello world!','','publish','open','open','','hello-world','','','2023-07-19 22:09:43','2023-07-19 22:09:43','','0','http://example.com/?p=1','0','post','','1'),
('2','1','2023-07-19 22:09:43','2023-07-19 22:09:43','<!-- wp:paragraph -->
<p>This is an example page. It''s different from a blog post because it will stay in one place and will show up in your site navigation (in most themes). Most people start with an About page that introduces them to potential site visitors. It might say something like this:</p>
<!-- /wp:paragraph -->

<!-- wp:quote -->
<blockquote class="wp-block-quote"><p>Hi there! I''m a bike messenger by day, aspiring actor by night, and this is my website. I live in Los Angeles, have a great dog named Jack, and I like pi&#241;a coladas. (And gettin'' caught in the rain.)</p></blockquote>
<!-- /wp:quote -->

<!-- wp:paragraph -->
<p>...or something like this:</p>
<!-- /wp:paragraph -->

<!-- wp:quote -->
<blockquote class="wp-block-quote"><p>The XYZ Doohickey Company was founded in 1971, and has been providing quality doohickeys to the public ever since. Located in Gotham City, XYZ employs over 2,000 people and does all kinds of awesome things for the Gotham community.</p></blockquote>
<!-- /wp:quote -->

<!-- wp:paragraph -->
<p>As a new WordPress user, you should go to <a href="http://example.com/wp-admin/">your dashboard</a> to delete this page and create new pages for your content. Have fun!</p>
<!-- /wp:paragraph -->','Sample Page','','publish','closed','open','','sample-page','','','2023-07-19 22:09:43','2023-07-19 22:09:43','','0','http://example.com/?page_id=2','0','page','','0'),
('3','1','2023-07-19 22:09:43','2023-07-19 22:09:43','<!-- wp:heading --><h2>Who we are</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class="privacy-policy-tutorial">Suggested text: </strong>Our website address is: http://example.com.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Comments</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class="privacy-policy-tutorial">Suggested text: </strong>When visitors leave comments on the site we collect the data shown in the comments form, and also the visitor&#8217;s IP address and browser user agent string to help spam detection.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>An anonymized string created from your email address (also called a hash) may be provided to the Gravatar service to see if you are using it. The Gravatar service privacy policy is available here: https://automattic.com/privacy/. After approval of your comment, your profile picture is visible to the public in the context of your comment.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Media</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class="privacy-policy-tutorial">Suggested text: </strong>If you upload images to the website, you should avoid uploading images with embedded location data (EXIF GPS) included. Visitors to the website can download and extract any location data from images on the website.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Cookies</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class="privacy-policy-tutorial">Suggested text: </strong>If you leave a comment on our site you may opt-in to saving your name, email address and website in cookies. These are for your convenience so that you do not have to fill in your details again when you leave another comment. These cookies will last for one year.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>If you visit our login page, we will set a temporary cookie to determine if your browser accepts cookies. This cookie contains no personal data and is discarded when you close your browser.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>When you log in, we will also set up several cookies to save your login information and your screen display choices. Login cookies last for two days, and screen options cookies last for a year. If you select &quot;Remember Me&quot;, your login will persist for two weeks. If you log out of your account, the login cookies will be removed.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>If you edit or publish an article, an additional cookie will be saved in your browser. This cookie includes no personal data and simply indicates the post ID of the article you just edited. It expires after 1 day.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Embedded content from other websites</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class="privacy-policy-tutorial">Suggested text: </strong>Articles on this site may include embedded content (e.g. videos, images, articles, etc.). Embedded content from other websites behaves in the exact same way as if the visitor has visited the other website.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>These websites may collect data about you, use cookies, embed additional third-party tracking, and monitor your interaction with that embedded content, including tracking your interaction with the embedded content if you have an account and are logged in to that website.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Who we share your data with</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class="privacy-policy-tutorial">Suggested text: </strong>If you request a password reset, your IP address will be included in the reset email.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>How long we retain your data</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class="privacy-policy-tutorial">Suggested text: </strong>If you leave a comment, the comment and its metadata are retained indefinitely. This is so we can recognize and approve any follow-up comments automatically instead of holding them in a moderation queue.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>For users that register on our website (if any), we also store the personal information they provide in their user profile. All users can see, edit, or delete their personal information at any time (except they cannot change their username). Website administrators can also see and edit that information.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>What rights you have over your data</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class="privacy-policy-tutorial">Suggested text: </strong>If you have an account on this site, or have left comments, you can request to receive an exported file of the personal data we hold about you, including any data you have provided to us. You can also request that we erase any personal data we hold about you. This does not include any data we are obliged to keep for administrative, legal, or security purposes.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Where your data is sent</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class="privacy-policy-tutorial">Suggested text: </strong>Visitor comments may be checked through an automated spam detection service.</p><!-- /wp:paragraph -->','Privacy Policy','','draft','closed','open','','privacy-policy','','','2023-07-19 22:09:43','2023-07-19 22:09:43','','0','http://example.com/?page_id=3','0','page','','0');

