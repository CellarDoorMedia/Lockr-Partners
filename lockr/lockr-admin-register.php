<?php

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
	echo  "<input id='lockr_account_email' name='lockr_options[account_email]' size='60' type='email' value='{$options['account_email']}' />";
}

function lockr_partner_name_input() {
?>
<input id="lockr_partner_name"
       name="lockr_partner_name"
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

	$cert_path = trim( $input['lockr_cert_path'] );

	if ( ! $cert_path ) {
		update_option( 'lockr_partner', '' );
		update_option( 'lockr_cert', '' );
	} else {
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
	}

	$options['account_email'] = trim( $input['account_email'] );
	$options['account_password'] = trim( $input['account_password'] );

	if ( ! get_option( 'lockr_cert' ) && ! get_option( 'lockr_requested' ) ) {
		$ch = curl_init('https://lockr.io/api/v2/request-certificate');
		curl_setopt_array($ch, array(
			CURLOPT_POST => TRUE,
			CURLOPT_POSTFIELDS => array(
				'email' => $options['account_email'],
				'partner' => trim( $input['lockr_partner'] ),
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
		try {
			lockr_site_client()->register( $options['account_email'], null, $name );
		} catch ( ClientException $e ) {
			if ( ! $options['account_password'] ) {
				wp_redirect( admin_url( 'admin.php?page=lockr-site-registration&p' ) );
				exit;
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
	$options['account_password'] = '';
	return $options;
}

function lockr_registration_form() {
	list( $exists ) = lockr_check_registration();
	$partner_info = lockr_get_partner();
	?>
<div class="wrap">
	<h1>Lockr Registration</h1>
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
			<p>Our system has detected that your website is hosted on one of our supported providers, no additional configuration is necessary.</p>
			<table class="form-table">
			<?php do_settings_fields( 'lockr', 'lockr_email' ); ?>
			</table>
		<?php endif; ?>

		<?php if ( isset( $_GET['p'] ) ): ?>
			<table class="form-table">
				<?php do_settings_fields( 'lockr', 'lockr_password' ); ?>
			</table>
		<?php endif; ?>
	<?php else: ?>
		<p> You're all set! Your site is registered and ready to begin using Lockr. Wasn't that easy?</p>
		<p>There's nothing left for you do to here, your keys sent to Lockr are now protected. If you registered with the wrong account, you can click <a href="https://lockr.io/user/login" target="_blank">here</a> to go to Lockr and manage your sites.</p>
	<?php endif; ?>

	<h2>Advanced</h2>
	<table class="form-table">
		<?php do_settings_fields( 'lockr', 'lockr_advanced' ); ?>
	</table>
	<?php submit_button( 'Submit' ); ?>
	</form>
</div>
<?php }
