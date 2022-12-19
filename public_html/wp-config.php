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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'miengweb_wp205' );

/** Database username */
define( 'DB_USER', 'miengweb_wp205' );

/** Database password */
define( 'DB_PASSWORD', '-@B7S6hg5p' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         '98d3iz62evw9pbyemr0d3cyertr2ap1xzhjrmlenssba7t9c4dsgilzcpvss4vbm' );
define( 'SECURE_AUTH_KEY',  'l3nugf0mq4t9hx0wa8gv8dqqcwh94oxkhx6iybkcfesrvb5fffuadvrov12cvcls' );
define( 'LOGGED_IN_KEY',    'praifwvspaauq6nbrykuidvi0outl305h1srou3s5t2zddbox1vndawxqsiovtli' );
define( 'NONCE_KEY',        'ee7ibtpdghdagicgxlkqklqg1anzksnfokswdezbh6leu68udwy9p4nvilyofuim' );
define( 'AUTH_SALT',        '8zagsiijyy8aynytvj8md2nftm54d6ud434jmoadrjv2suxm3hi0ogsbynoljxgc' );
define( 'SECURE_AUTH_SALT', 'kdp57ffxzypjlrbruttthkwlybe4hjryeepkob8l80qq9rwcko1m1qemmen6rxiv' );
define( 'LOGGED_IN_SALT',   'x60ojm0cy3l8ua758zxrprzspqtamcwocoemsrbbfqevzyjkyhajapinprzc6mcx' );
define( 'NONCE_SALT',       '3ekntayyw0tv9dkt0bgc8jezn1lujup33kx9ggo7hvo3dowdqom47z0h2ge76hs2' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wpk7_';

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
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
