<?php
/**
 * @package Lockr
 */
/*
Plugin Name: Lockr
Plugin URI: https://lockr.io/
Description: Integrate with the Lockr hosted key management platform. Secure all your API and encryption keys according to industry best practices. With Lockr, key management is easy. 
Version: 1.0.0
Author: Lockr
Author URI: htts://lockr.io/
License: GPLv2 or later
Text Domain: lockr
*/

// Don't call the file directly and give up info!
if ( !function_exists( 'add_action' ) ) {
	echo 'Lock it up!';
	exit;
}

define( 'LOCKR__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * @file
 * Hook implementations and callbacks for lockr_pantheon.
 */

use Lockr\Exception\ClientException;
use Lockr\Exception\ServerException;

/**
 * Include our autoloader.
 */
require_once( LOCKR__PLUGIN_DIR . '/class.lockr-autoload.php' );

/**
 * Include our admin forms.
 */
if ( is_admin() ) {
	require_once( LOCKR__PLUGIN_DIR . 'class.lockr-pantheon-admin.php' );
}



/**
 * Returns if this site is currently registered with Lockr.
 *
 * @return bool
 * TRUE if this site is registered, FALSE if not.
 */
function lockr_check_registration() {
  try {
    return \Lockr\Lockr::site()->exists();
  }
  catch (ServerException $e) {
    return FALSE;
  }
  catch (ClientException $e) {
    return FALSE;
  }
}

/**
 * Gets a key from Lockr.
 *
 * @param string $key_name
 * The key name.
 *
 * @return string | FALSE
 * Returns the key value, or FALSE on failure.
 */
function lockr_get_key($key_name, $encoded) {
  try {
    return \Lockr\Lockr::key()->encrypted($encoded)->get($key_name);
  }
  catch (\Exception $e) {
    return FALSE;
  }
}

/**
 * Sets a key value in lockr.
 *
 * @param string $key_name
 * The key name.
 * @param string $key_value
 * The key value.
 * @param string $key_label
 * The key label.
 * @param string|bool $old_name
 * The old key name if it changed.
 *
 * @return bool
 * TRUE if they key set successfully, FALSE if not.
 */
function lockr_set_key($key_name, $key_value, $key_label) {
  try {
    return \Lockr\Lockr::key()->encrypted()
      ->set($key_name, $key_value, $key_label);
  }
  catch (\Exception $e) {
    return FALSE;
  }
}

/**
 * Deletes a key from Lockr.
 *
 * @param string $key_name
 * The key name
 */
function lockr_delete_key($key_name) {
  \Lockr\Lockr::key()->delete($key_name);
}

