<?php
define( 'WP_CACHE', true );

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
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'u773725531_jlPKH' );

/** Database username */
define( 'DB_USER', 'u773725531_Oihx4' );

/** Database password */
define( 'DB_PASSWORD', '4YKQsVqy11' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

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
define( 'AUTH_KEY',          'unifItv:dr+-gB~Lw?-eR:JRaWBUi&PY;K9+$T53/+(})]<KXE]Ht6xdmr;ha.o8' );
define( 'SECURE_AUTH_KEY',   '<&>*8$(EV._`1L2QuF;ByAnrzl<_?T{VHNW;p!WQ{J0RYPkYbdAP.p=x-VZGvmUf' );
define( 'LOGGED_IN_KEY',     'D=F5|[IWTdCfFZ,<n<LuABx3.FPZ5SNoC8Z9^.)egyqpfgrGY~R%8uBZ3m)Quy3O' );
define( 'NONCE_KEY',         'FUdjKwIZ^%<H&]#JJ>M~B|:i%Pv1BVNx_~Vg&]AGe_m|sQTB;rCO>=1T416y[!rL' );
define( 'AUTH_SALT',         '(R47Eb{Eu&X1-5M<iD1RsdFcJi,}L-(b[=X|l`qY)&42+F~.YlrEXT7ImvSI?SBj' );
define( 'SECURE_AUTH_SALT',  'w^*>OZ% /)BRg6|{(YjvZ:s2|kVA=+PNe0;ycAONYshM}}powe{2R(B79kH35w-(' );
define( 'LOGGED_IN_SALT',    'j0-pg`:XrvX8[[jqN@xGL[Wi$,hf$`zJ1J*8B>hy12Laz+gVx>E?$ah+uD+&Duka' );
define( 'NONCE_SALT',        '5.*,c~ %xT{bPW./f;IV>72iW1{;G?ihk]h;O)Uw@z[AlAny+>u2|P`+OB2vfD6j' );
define( 'WP_CACHE_KEY_SALT', 't zGmZNdV,9@6Y-F.R 6U/Sy*AobRg:|8aw eJ:5:@q$hlYZ oLQypm.{4 ,Or[>' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'FS_METHOD', 'direct' );
define( 'COOKIEHASH', '97666c6cef86dedf089a976a432571d7' );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
