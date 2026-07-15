<?php
/**
 * k-config.sample.php — configuration template.
 *
 * Copy this file to `k-config.php` in the same directory and fill in the real
 * values for your environment. `k-config.php` is git-ignored and must never be
 * committed: it holds live database credentials and the cron key.
 *
 *   cp k-config.sample.php k-config.php   (or copy on Windows)
 *
 * Loaded first by index.php before the Yii framework boots.
 */

// --- Database connection -----------------------------------------------------
define( 'DB_NAME',     'your_database_name' );
define( 'DB_USER',     'your_database_user' );
define( 'DB_PASSWORD', 'your_database_password' );
// define( 'DB_HOST', 'localhost' );
define( 'DB_HOST',     '127.0.0.1:3306' );
define( 'DB_CHARSET',  'utf8' );
define( 'DB_PREFIX',   'st_' );

// --- Application ------------------------------------------------------------
define( 'KMRS_DEFAULT_LANGUAGE', 'en' );
define( 'BACKOFFICE_FOLDER',     'backoffice' );

// Shared secret required to trigger cron/task endpoints. Use a long random hex
// string, e.g. output of: bin2hex(random_bytes(16))
define( 'CRON_KEY', 'CHANGE_ME_TO_A_RANDOM_32_CHAR_HEX' );

// Demo mode: when true, mutating operations are restricted for the merchant ids
// listed in DEMO_MERCHANT. Keep false in production.
define( 'DEMO_MODE',     false );
define( 'DEMO_MERCHANT', array() );
