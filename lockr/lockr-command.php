<?php
	
use Lockr\Exception\ClientException;
use Lockr\Exception\ServerException;

/**
 * Allow for key retrieval from WP-CLI.
 */
function lockr_command_get_key( $args, $assoc_args ) {
	//Get our key name from one of 2 ways
	$key_name = $args[0];
	if( ! $key_name ) {
		$key_name = $assoc_args['key'];
	}
	if( ! $key_name ){
		WP_CLI::error( 'No key name provided' );
	}
	
	$key = lockr_get_key( $key_name );
	if ( $key ) {
	  WP_CLI::success( $key );
	} else {
	  WP_CLI::error( 'No Key Found' );
	}
}

/**
 * Register a site from WP-CLI.
 */
WP_CLI::add_command( 'lockr get key', 'lockr_command_get_key' );

function lockr_command_register_site( $args, $assoc_args ) {
	list( $exists, $available ) = lockr_check_registration();
	
	if ( $exists ) {
		WP_CLI::error( 'This site is already registered with Lockr.' );
	}
	
	$name = get_bloginfo( 'name', 'display' );

	if ( ! $assoc_args['email'] ) {
		WP_CLI::error( 'No Email Provided' );
	}
	
	if ( ! filter_var( $assoc_args['email'], FILTER_VALIDATE_EMAIL ) ) {
		WP_CLI::error( $assoc_args['email'] . ' is not a valid email address' );
	}
	try {
		lockr_site_client()->register( $assoc_args['email'], NULL, $name );
	} catch ( ClientException $e ) {
		if ( !$assoc_args['password'] ) {
			WP_CLI::error( 'Lockr account already exists for this email, please provide a password to authenticate and register site.' );
		} else {
			try {
				lockr_site_client()->register( $assoc_args['email'], $assoc_args['password'], $name );
			} catch ( ClientException $e ) {
				WP_CLI::error( 'Login credentials incorrect, please try again.' );
			} catch ( ServerException $e ) {
				WP_CLI::error( 'An unknown error has occurred, please try again later.' );
			}
		}
	} catch ( ServerException $e ) {
		WP_CLI::error( 'An unknown error has occurred, please try again later.' );
	}

	list( $exists, $available ) = lockr_check_registration();

	if ( $exists ) {
		WP_CLI::success( "Site is now registered with Lockr. You're good to start setting keys" );
	} else {
		WP_CLI::error( 'An unknown error has occurred, please try again later.' );
	}
}

WP_CLI::add_command( 'lockr register site', 'lockr_command_register_site' );

/**
 * Set a key from WP CLI.
 */
function lockr_command_set_key( $args, $assoc_args ) {
	if ( ! $assoc_args['name'] ) {
		WP_CLI::error( 'Please provide a key machine name with --name=[key name]. This must be all lowercase with no spaces or dashes, underscores are ok.' );
	}
	if ( ! $assoc_args['value'] ) {
		WP_CLI::error( 'No key value provided, please provide one with --key=[key value] . ' );
	}
	if ( ! $assoc_args['label'] ) {
		WP_CLI::error( 'No key label provided, please provide one with --label=[key label]. This is the display name for the key.' );
	}
	
	$key_name = $assoc_args['name'];
	$key_value = $assoc_args['value'];
	$key_label = $assoc_args['label'];
	
	// Double check our key name is properly formatted.
	$key_name = strtolower( $key_name );
	$key_name = preg_replace( '@[^a-z0-9_]+@','_', $key_name );
	
	$key = lockr_set_key( $key_name, $key_value, $key_label );

	if ( $key ) {
		WP_CLI::success( $key_label . ' added to Lockr.' );
	} else {
		WP_CLI::error( $key_label . ' was not added to Lockr. Please try again.');
	}
}

WP_CLI::add_command( 'lockr set key', 'lockr_command_set_key' );
