<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link    https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'test');

/** Database username */
define('DB_USER', 'bob');

/** Database password */
define('DB_PASSWORD', 'secret');

/** Database hostname */
define('DB_HOST', '192.1.2.3:4415');

/** Database charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The database collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY', '3+1G#6A-Yag@M<Hfn[vJjTYJ~Y[y;DpBtds<&rI_$6@H|OyRe}p(jg.plkwhnm1h');
define('SECURE_AUTH_KEY', '/Zy4j#Mwd1-CLb!<k4?FMDS0msV"A;@|omgdjih+yqG.q~(5yP(`Vh;p}SD{kBd/');
define('LOGGED_IN_KEY', 'greZHU_izY"_4HpB?s{d9"epV8V@wmqsD_~,$qkD$9vjqT$iHghDe4sQ<]%xy[S]');
define('NONCE_KEY', 'f?jAD+T0"k<SLyBhRkNi/XcR1A^TOzsW=GJWUDO&;"s2EY@t?p<<VCwLN%42MoG0');
define('AUTH_SALT', 'J#e5@&,hZ4r>Ee{B4l-tu/%*+I/<19$x,-lwG~*Lw]]6U0HEoW1YS8uOW{dW^7^t');
define('SECURE_AUTH_SALT', '5g-R#r|1$E7.Ln7&%I/)D$FI];geGL@IhbcCaV6]}"=m6_:r!+Tac=@qvbupIbLT');
define('LOGGED_IN_SALT', '+O&4M3gP0^DH43"B.*)+9Qgs($]RZUu`iugG,T)535TTm3Q7*-lKFnJ6Rb^;)bQ:');
define('NONCE_SALT', '3:YS~wd5MN%ACFi|rA^["C":=x[qH5/z5yy)yhO,}pjYm@s$9NNY:sct0%{u+_&9');

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'test_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define('WP_DEBUG', false);

/* Add any custom values between this line and the "stop editing" line. */

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
