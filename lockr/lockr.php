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
if ( ! function_exists( 'add_action' ) ) {
	echo 'Lock it up!';
	exit;
}

define( 'LOCKR__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LOCKR__PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * @file
 * Create database table for keys in the system.
 */

register_activation_hook( __FILE__, 'lockr_install' );

/**
 * @file
 * Hook implementations and callbacks for lockr.
 */

use Lockr\KeyClient;
use Lockr\Lockr;
use Lockr\Partner;
use Lockr\SiteClient;
use Lockr\Exception\ClientException;
use Lockr\Exception\ServerException;

/**
 * Include our autoloader.
 */
require_once( LOCKR__PLUGIN_DIR . '/lockr-autoload.php' );

/**
 * Include our admin forms.
 */
if ( is_admin() ) {
	require_once( LOCKR__PLUGIN_DIR . '/lockr-admin.php' );
}

/**
 * Include our WP CLI Commands if available.
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once dirname( __FILE__ ) . '/lockr-command.php';
}

/**
 * Set our db version which will be updated should the schema change.
 */
global $lockr_db_version;
$lockr_db_version = '1.0';

function lockr_install() {
	global $wpdb;
	global $lockr_db_version;
	
	$table_name = $wpdb->prefix . 'lockr_keys';
	$charset_collate = $wpdb->get_charset_collate();
	
	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT null AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT null,
		key_name tinytext NOT null,
		key_value text NOT null,
		key_label text NOT null,
		key_abstract text,
		UNIQUE KEY id (id)
	) $charset_collate;";
	
	require_once ( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
	
	add_option( 'lockr_db_version', $lockr_db_version );
	
}

/**
 * Returns the detected partner, if available.
 */
function lockr_get_partner() {
	if ( defined('PANTHEON_BINDING') ) {
		return array(
			'name' => 'pantheon',
			'title' => __( 'Pantheon' ),
			'description' => __( 'Our system has detected that your website is hosted on one of our supported providers, no additional configuration is necessary.' ),
			'cert' => '/srv/bindings/' . PANTHEON_BINDING . '/certs/binding.pem',
		);
  	}

	return null;
}

/**
 * Returns the Lockr site client.
 */
function lockr_site_client() {
	$base_client = lockr_client();

	if ( $base_client === false ) {
		return false;
	}

	$client = new SiteClient( $base_client );

	return $client;
}

/**
 * Returns the Lockr key client.
 */
function lockr_key_client() {
	$base_client = lockr_client();

	if ( $base_client === false ) {
		return false;
	}

	$client = new KeyClient( $base_client );

	return $client;
}

/**
 * Returns the Lockr client for this site.
 */
function lockr_client() {
	$partner = get_option( 'lockr_partner' );
	$cert = get_option( 'lockr_cert' );

	if ( ! $partner || ! $cert ) {
		return false;
	}

	if ($partner !== 'custom') {
		$partner_info = lockr_get_partner();

		if ( ! $partner_info ) {
			return false;
		}

		$partner = $partner_info['name'];
		$cert = $partner_info['cert'];
	}

	$client = Lockr::create( new Partner( $cert, $partner ) );

	return $client;
}

/**
 * Returns if this site is currently registered with Lockr.
 *
 * @return bool
 * true if this site is registered, false if not.
 */
function lockr_check_registration() {
	$client = lockr_site_client();
	try {
		if ( $client ) {
			return $client->exists();
		} else {
			return false;
		}
	} catch ( ServerException $e ) {
		return false;
	} catch ( ClientException $e ) {
		return false;
	}
}

/**
 * Encrypt plaintext using a key from Lockr.
 *
 * @param string $key_name The key name in Lockr.
 * @param string $plaintext The plaintext to be encrypted.
 *
 * @return string|null
 *   The encrypted and encoded ciphertext or null if encryption fails.
 */
function lockr_encrypt( $key_name, $plaintext ) {
	$cipher = MCRYPT_RIJNDAEL_256;
	$mode = MCRYPT_MODE_CBC;

	$key = lockr_get_key( $key_name );
	if ( ! $key ) {
		return null;
	}

	$iv_len = mcrypt_get_iv_size( $cipher, $mode );
	$iv = mcrypt_create_iv( $iv_len );

	$ciphertext = mcrypt_encrypt( $cipher, $key, $plaintext, $mode, $iv );
	if ( $ciphertext === false ) {
		return null;
	}

	$iv = base64_encode( $iv );
	if ( $iv === false ) {
		return null;
	}

	$ciphertext = base64_encode( $ciphertext );
	if ( $ciphertext === false ) {
		return null;
	}

	$parts = array(
		'cipher'     => $cipher,
		'mode'       => $mode,
		'key_name'   => $key_name,
		'iv'         => $iv,
		'ciphertext' => $ciphertext,
	);
	$encoded = json_encode( $parts );
	if ( json_last_error() !== JSON_ERROR_NONE ) {
		return null;
	}

	return $encoded;
}

/**
 * Decrypt ciphertext using a key from Lockr.
 *
 * @param string $encoded The encrypted and encoded ciphertext.
 *
 * @return string|null The plaintext or null if decryption fails.
 */
function lockr_decrypt( $encoded ) {
	$parts = json_decode( $encoded, true );
	if ( json_last_error() !== JSON_ERROR_NONE ) {
		return null;
	}

	if ( ! isset( $parts['cipher'] ) ) {
		return null;
	}
	$cipher = $parts['cipher'];

	if ( ! isset( $parts['mode'] ) ) {
		return null;
	}
	$mode = $parts['mode'];

	if ( ! isset( $parts['key_name'] ) ) {
		return null;
	}
	$key = lockr_get_key( $parts['key_name'] );
	if ( ! $key ) {
		return null;
	}

	if ( ! isset( $parts['iv'] ) ) {
		return null;
	}
	$iv = base64_decode( $parts['iv'] );
	if ( $iv === false ) {
		return null;
	}

	if ( ! isset( $parts['ciphertext'] ) ) {
		return null;
	}
	$ciphertext = base64_decode( $parts['ciphertext'] );
	if ( $ciphertext === false ) {
		return null;
	}

	$plaintext = mcrypt_decrypt( $cipher, $key, $ciphertext, $mode, $iv );
	if ( $plaintext === false ) {
		return null;
	}

	return trim( $plaintext );
}

/**
 * Gets a key from Lockr.
 *
 * @param string $key_name
 * The key name.
 *
 * @return string | false
 * Returns the key value, or false on failure.
 */
function lockr_get_key( $key_name ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'lockr_keys';
	$query = $wpdb->prepare( "SELECT * FROM $table_name WHERE key_name = '%s'", array( $key_name ) );
	$key_store = $wpdb->get_results( $query );
	
	if ( $key_store == null ) {
		return false;
	}
	
	$encoded = $key_store[0]->key_value;
	
	$client = lockr_key_client();
	
	try {
		if( $client ) {
		return $client->encrypted( $encoded )->get( $key_name );
		} else {
			return false;
		}
	} catch (\Exception $e) {
		return false;
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
 * @param string|bool $encoded
 * The exisiting key metadata if it exists.
 *
 * @return bool
 * true if they key set successfully, false if not.
 */
function lockr_set_key( $key_name, $key_value, $key_label ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'lockr_keys';
	$key_abstract = '**************' . substr($key_value, -4);
	$query = $wpdb->prepare( "SELECT * FROM $table_name WHERE key_name = '%s'", array( $key_name ) );
	$key_exists = $wpdb->get_results( $query );
	if ( empty( $key_exists ) ) {
		$key_exists = null;
		$encoded = null;
	} else {
		$encoded = $key_exists[0]->key_value;
	}
	
	$client = lockr_key_client();

	if ( $client === false ) {
		return false;
	}
	$client = $client->encrypted();

	try {
		$key_remote = $client->set( $key_name, $key_value, $key_label, $encoded );
	} catch ( ClientException $e ) {
		$body = $e->getMessage();
		$data = json_decode($body, true);
		if ( isset( $data['title']) && $data['title'] === 'Not paid' ) {
			return 'NOTE: Key was not set. Please go to <a href="https://lockr.io/">Lockr</a> and add a payment method to your account.';
		}
	}
	catch (\Exception $e) {
		return false;
	}

	if ( $key_remote != false ) {
		// Setup our storage array
		$key_data = array(
			'time' => date( "Y-m-d H:i:s" ),
			'key_name' => $key_name,
			'key_label' => $key_label,
			'key_value' => $key_remote,
			'key_abstract' => $key_abstract,
		);

		if ( ! empty( $key_exists ) ) {
			$key_id = array('id' => $key_exists[0]->id);
			$key_store = $wpdb->update( $table_name, $key_data, $key_id );
		} else {
			$key_store = $wpdb->insert( $table_name, $key_data );
		}

		return $key_store;
	} else {
		return false;
	}
}

/**
 * Deletes a key from Lockr.
 *
 * @param string $key_name
 * The key name
 */
function lockr_delete_key( $key_name ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'lockr_keys';

	$key_store = array( 'key_name' => $key_name );
	$key_delete = $wpdb->delete( $table_name, $key_store );
	if ( ! empty( $key_delete ) ) {
		$client = lockr_key_client();
		if ( $client ) {
			$client->delete($key_name);
			return true;
		}
	} else {
		return false;
	}
}

/**
 * Performs a generic option-override.
 */
function lockr_override_option( $option_name, $key_name, $key_desc ) {
	$option_value = get_option( $option_name );

	if ( $option_value == '' || substr( $option_value, 0, 5 ) == 'lockr' ) {
		return;
	}

	if ( lockr_set_key( $key_name, $option_value, $key_desc ) ) {
		update_option( $option_name, $key_name );
	}
}

/**
 * Gets a possibly overridden option value.
 */
function lockr_get_override_value( $option_name ) {
	$option_value = get_option( $option_name );

	if ( substr ( $option_value, 0, 5 ) != 'lockr' ) {
		return $option_value;
	}

	$lockr_key = lockr_get_key( $option_value );
	if ( $lockr_key ) {
		return $lockr_key;
	}

	return $option_value;
}
