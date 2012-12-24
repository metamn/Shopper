<?php
/** Enable W3 Total Cache */
define('WP_CACHE', true); // Added by W3 Total Cache

/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */
 
 
 
 if (isset($_SERVER["DATABASE_URL"])) {
   $db = parse_url($_SERVER["DATABASE_URL"]);
   define("DB_NAME", trim($db["path"],"/"));
   define("DB_USER", $db["user"]);
   define("DB_PASSWORD", $db["pass"]);
   define("DB_HOST", $db["host"]);
   
   // define('WP_SITEURL', 'http://' . $_SERVER['SERVER_NAME'] );
  } else {

    // ** MySQL settings - You can get this info from your web host ** //
    /** The name of the database for WordPress */
    define('DB_NAME', 'shopper');

    /** MySQL database username */
    define('DB_USER', 'cs');

    /** MySQL database password */
    define('DB_PASSWORD', 'cs-33');

    /** MySQL hostname */
    define('DB_HOST', 'localhost');

    /** Database Charset to use in creating database tables. */
    define('DB_CHARSET', 'utf8');

    /** The Database Collate type. Don't change this if in doubt. */
    define('DB_COLLATE', '');
    
}





/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '/Qi]q(rJ,Zp)=b;F,26!ho_[+b}m&Wb$xz+*VI6Q`~[B/iH)#D}y/+L_FjDsNG |');
define('SECURE_AUTH_KEY',  '2tY0Ee}|3Wc#bUm~^iC}.y&rh?>hu!Pfk7SO3ckwk=AV|70Oc#u=4G(:pSSvATg^');
define('LOGGED_IN_KEY',    '/i!lIZ+AU2P!wcQg]/%?r}2ne24k{[6HE(o|1|e.rlgtb|F?R3XZvq2msz3G;*kf');
define('NONCE_KEY',        'u-P_+633h4nsB1z5[Hy:|W,*E >-Dj]kyW Na$(z=va;up4V_~X5`+?2gHzccp>J');
define('AUTH_SALT',        '4k#L*4HaUNG[>&X~92ySJoUXiUg-a{n7#iG;V0B#rfZQ2xU?&khfz#oGbxlF7HWl');
define('SECURE_AUTH_SALT', 'XJ9;i1q&j|HsvV;nfd,:p$$n!-q9#h[~x%QptY&@@(>n%EI ;Zr>9s_D#~A}2~5P');
define('LOGGED_IN_SALT',   '.k[x[a,b6s%?n[l^Rzeq]wCi{ Ia]*|iR{uRU*dJgSV(q0i}v //}gl9h>.`p (+');
define('NONCE_SALT',       'v+|j~pwvWJ/ ^7gl_QDvHh+0$&<ovX&4LsLWW)-eb%~TdF3+3;{&7)E_J^0Xoq6;');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

