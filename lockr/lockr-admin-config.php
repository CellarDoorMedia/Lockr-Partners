<?php

use Lockr\Exception\ClientException;
use Lockr\Exception\ServerException;

// Don't call the file directly and give up info!
if ( ! function_exists( 'add_action' ) ) {
	echo 'Lock it up!';
	exit;
}

function register_lockr_settings() {
	register_setting( 'lockr_options', 'lockr_options', 'lockr_options_validate' );
	add_settings_section(
		'lockr_email',
		'Email Address',
		'lockr_email_text',
		'lockr'
	);
	add_settings_field(
		'lockr_account_email',
		'Email Address',
		'lockr_account_email_input',
		'lockr',
		'lockr_email'
	);

	add_settings_section(
		'lockr_partner',
		'Lockr Partner',
		'lockr_partner_text',
		'lockr'
	);
	add_settings_field(
		'lockr_partner_name',
		'Lockr Partner',
		'lockr_partner_name_input',
		'lockr',
		'lockr_partner'
	);

	add_settings_section(
		'lockr_password',
		'Account Password',
		'lockr_password_text',
		'lockr'
	);
	add_settings_field(
		'lockr_account_password',
		'Account Password',
		'lockr_account_password_input',
		'lockr',
		'lockr_password'
	);

	add_settings_section(
		'lockr_advanced',
		'Lockr Advanced Settings',
		'lockr_advanced_text',
		'lockr'
	);
	add_settings_field(
		'lockr_cert_path',
		'Custom Certificate Location',
		'lockr_cert_path_input',
		'lockr',
		'lockr_advanced'
	);
}

function lockr_email_text() {
}

function lockr_advanced_text() {
}

function lockr_request_text() {
}

function lockr_register_text() {
	echo "<p style='width: 80%;'>You're just one step away from secure key management! To register your site with Lockr, simply input an email address you'd like to associate your account with. If you're already a Lockr user, you can enter the email and password to login to your account and register this site. Dont' worry, we won't store your password locally.</p>";
}

function lockr_account_email_input() {
	$options = get_option( 'lockr_options' );
	if( isset( $options['account_email'] ) ){
		echo  "<input id='lockr_account_email' name='lockr_options[account_email]' size='60' type='email' value='{$options['account_email']}' />";
	} else{
		echo  "<input id='lockr_account_email' name='lockr_options[account_email]' size='60' type='email' value='' />";
	}
}

function lockr_partner_name_input() {
?>
<input id="lockr_partner_name"
       name="lockr_options[partner_name]"
       size="60"
	   type="text" />
<?php
}

function lockr_cert_path_input() {
	if ( get_option( 'lockr_partner' ) === 'custom' ) {
		$cert_path = get_option( 'lockr_cert' );
	} else {
		$cert_path = '';
	}
?>
<input id="lockr_cert_path"
       name="lockr_options[lockr_cert_path]"
       size="60"
       type="text"
       value="<?php echo $cert_path; ?>" />
<?php
}

function lockr_account_password_input() {
	$options = get_option( 'lockr_options' );
	echo  "<input id='lockr_account_email' name='lockr_options[account_password]' size='60' type='password' value='{$options['account_password']}' />";
}

function lockr_options_validate($input) {
	$options = get_option( 'lockr_options' );
	if ( array_key_exists( 'lockr_cert_path', $input ) ){
		$cert_path = trim( $input['lockr_cert_path'] );

		if ( $cert_path ) {
			if ( $cert_path[0] !== '/' ) {
				$cert_path = ABSPATH . $cert_path;
			}
				
			if ( ! is_readable($cert_path) ) {
				add_settings_error(
					'lockr_options',
					'lockr-cert-path',
					"{$cert_path} must be a readable file."
				);
	
				return $options;
			}
		
			update_option( 'lockr_partner', 'custom' );
			update_option( 'lockr_cert', $cert_path );
		} else {
			$partner = lockr_get_partner();
			if ( $partner ) {
				update_option( 'lockr_partner', $partner['name'] );
				update_option( 'lockr_cert', $partner['cert'] );
			} else {
				update_option( 'lockr_partner', '' );
				update_option( 'lockr_cert', '' );
			}
		}
	} else {
		$options['account_email'] = trim( $input['account_email'] );
		if( isset( $input['account_password'] ) ){
			$options['account_password'] = trim( $input['account_password'] );
		} else{
			$options['account_password'] = '';
		}
	
		if ( ! get_option( 'lockr_cert' ) && ! get_option( 'lockr_requested' ) ) {
			$ch = curl_init('https://lockr.io/api/v2/request-certificate');
			curl_setopt_array($ch, array(
				CURLOPT_POST => TRUE,
				CURLOPT_POSTFIELDS => array(
					'email' => $options['account_email'],
					'partner' => trim( $input['partner_name'] ),
				),
			));
			curl_exec($ch);
			update_option( 'lockr_requested', true );
			return $options;
		}
	
		$name = get_bloginfo( 'name', 'display' );
	
		if ( ! filter_var( $options['account_email'], FILTER_VALIDATE_EMAIL ) ) {
			add_settings_error( 'lockr_options', 'lockr-email', $options['account_email'] . ' is not a proper email address. Please try again.', 'error' );
			$options['account_email'] = '';
		} else {
			// I guess this form double-posts? Seems like Wordpress weirdness.
			list( $exists ) = lockr_check_registration();
			if ( ! $exists ) {
				try {
					lockr_site_client()->register( $options['account_email'], null, $name );
				} catch ( ClientException $e ) {
					if ( ! $options['account_password'] ) {
						add_settings_error( 'lockr_options', 'lockr-password', 'Please enter your password to add this site to your Lockr account.', 'error' );
						return $options;
					}
					try {
						lockr_site_client()->register( $options['account_email'], $options['account_password'], $name );
					} catch ( ClientException $e ) {
						add_settings_error( 'lockr_options', 'lockr-email', 'Login credentials incorrect, please try again.', 'error' );
					} catch ( ServerException $e ) {
						add_settings_error( 'lockr_options', 'lockr-email', 'An unknown error has occurred, please try again later.', 'error' );
					}
				} catch ( ServerException $e ) {
					add_settings_error( 'lockr_options', 'lockr-email', 'An unknown error has occurred, please try again later.', 'error' );
				}
			}
		}
		$options['account_password'] = '';
		return $options;	
	}
}

function lockr_configuration_form() {
	list( $exists ) = lockr_check_registration();
	$partner_info = lockr_get_partner();
	$errors = (get_settings_errors());
	$error_codes = array();
	foreach( $errors as $error ){
		$error_codes[] = $error['code'];
	}
	?>
<div class="wrap">
	<h1>Lockr Registration</h1>
	<?php settings_errors(); ?>
	<form method="post" action="options.php">
	<?php settings_fields( 'lockr_options' ); ?>

	<?php if ( ! $exists ): ?>
		<?php if ( ! $partner_info ): ?>
			<?php if ( ! get_option( 'lockr_requested' ) ): ?>
				<p>Thank you for your interest in Lockr! Our system is detecting that your website is not currently hosted by a supported provider. To help us better prioritize our integrations, please provide your current hosting provider and email in the fields below. We will be sure to notify you when Lockr is integrated with your hosting provider. In the meantime, a custom certificate to use Lockr will be generated and sent to the email you provided.</p>
				<table class="form-table">
				<?php do_settings_fields( 'lockr', 'lockr_email' ); ?>
				<?php do_settings_fields( 'lockr', 'lockr_partner' ); ?>
				</table>
			<?php else: ?>
				<p>Thank you for your request for a custom cert! A member from our development team will be generating that cert and getting back to you shortly.</p>
				<table class="form-table">
				<?php do_settings_fields( 'lockr', 'lockr_email' ); ?>
				</table>
			<?php endif; ?>
		<?php else: ?>
			<p>Our system has detected that your website is hosted on one of our supported providers, enter your email address to register.</p>
			<table class="form-table">
			<?php do_settings_fields( 'lockr', 'lockr_email' ); ?>
			</table>
		<?php endif; ?>

		<?php if ( in_array( 'lockr-password', $error_codes ) ): ?>
			<table class="form-table">
				<?php do_settings_fields( 'lockr', 'lockr_password' ); ?>
			</table>
		<?php endif; ?>
		<?php submit_button( 'Register Site' ); ?>
	<?php else: ?>
		<p> You're all set! Your site is registered and ready to begin using Lockr. Wasn't that easy?</p>
		<p>There's nothing left for you do to here, your keys sent to Lockr are now protected. If you registered with the wrong account, you can click <a href="https://lockr.io/user/login" target="_blank">here</a> to go to Lockr and manage your sites.</p>
	<?php endif; ?>
	</form>
	<hr>
	<h1>Advanced Configuration</h1>
	<?php if ( ! $partner_info ): ?>
		<p>Use the following field to set the location of your custom certificate. If you are on a supported hosting provider you do not need to enter any value here.</p>
	<?php else: ?>
		<p><strong>Our system has detected that your website is hosted on one of our supported providers, this setting is not necessary under regular usage.</strong> </p>
		<p>Use the following field to set the location of your custom certificate.</p>
	<?php endif;?>
	<form method="post" action="options.php">
	<?php settings_fields( 'lockr_options' ); ?>
	<table class="form-table">
		<?php do_settings_fields( 'lockr', 'lockr_advanced' ); ?>
	</table>
	<?php submit_button( 'Confirm Certificate Location', 'secondary' ); ?>
	</form>
</div>
<?php }
