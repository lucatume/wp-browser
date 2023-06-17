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
-- Start a transaction
START TRANSACTION;
-- Insert a row into the wptests_options table.
INSERT INTO wptests_options (option_name, option_value, autoload)
VALUES ('test_option_1', 'test_value_1', 'yes');
-- Commit the changes.
COMMIT;
