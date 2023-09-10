CREATE TABLE IF NOT EXISTS wptests_options
(
    option_id    bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    option_name  varchar(191)        NOT NULL DEFAULT '',
    option_value longtext            NOT NULL,
    autoload     varchar(20)         NOT NULL DEFAULT 'yes',
    PRIMARY KEY (option_id),
    UNIQUE KEY option_name (option_name)
) CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci;
INSERT INTO wptests_options (option_name, option_value)
VALUES ('option_2', 'value_2');
