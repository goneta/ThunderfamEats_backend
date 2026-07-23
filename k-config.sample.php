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

// --- Chat: Firebase custom-token endpoint (POST /interface/firebaseCustomToken) ---
// Absolute path to the Firebase service-account JSON. Keep this file OUTSIDE the
// web root and out of git (see docs/ai-gateway-and-firebase-token.md). The
// endpoint signs a Firebase custom token (uid = customer client_uuid) so
// participant-only Firestore rules can secure chat. Leave undefined to disable.
// define( 'FIREBASE_SA_JSON_PATH', '/secure/path/thunderfameats-firebase-adminsdk.json' );

// --- AI assistant gateway (POST /interface/aichat) --------------------------
// Server-side conversational assistant for the customer app. The model API key
// stays here and is NEVER shipped to the app. Leave TFE_AI_API_KEY empty/undefined
// to disable the gateway (the app then falls back to its read-only deterministic
// intents). See docs/ai-gateway-and-firebase-token.md.
// define( 'TFE_AI_PROVIDER', 'anthropic' );          // v1 supports 'anthropic'
// define( 'TFE_AI_MODEL',    'claude-sonnet-5' );
// define( 'TFE_AI_API_KEY',  '' );                   // server-side secret only
