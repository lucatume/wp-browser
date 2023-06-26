CREATE TABLE wp_options (
    option_id INTEGER PRIMARY KEY AUTOINCREMENT,
    option_name VARCHAR(191) NOT NULL DEFAULT '',
    option_value LONGTEXT NOT NULL,
    autoload VARCHAR(20) NOT NULL DEFAULT 'yes'
);

INSERT INTO wp_options (option_id,option_name,option_value,autoload) VALUES
('1','siteurl','http://example.com','yes'),
('2','home','http://example.com','yes'),
('3','blogname','Example','yes'),
('4','users_can_register','0','yes'),
('5','admin_email','hello@wordpress.test','yes');

