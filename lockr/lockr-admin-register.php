<?php
	
// Don't call the file directly and give up info!
if ( ! function_exists( 'add_action' ) ) {
	echo 'Lock it up!';
	exit;
}

function register_lockr_settings() {
	register_setting( 'lockr_options', 'lockr_options', 'lockr_options_validate' );
	add_settings_section( 'lockr_main', 'Register a Lockr account to this site', 'lockr_main_text', 'lockr' );
	add_settings_field( 'lockr_account_email', 'Account Email', 'lockr_account_email_input', 'lockr', 'lockr_main' );
	add_settings_field( 'lockr_account_password', 'Account Password', 'lockr_account_password_input', 'lockr', 'lockr_main' );
}

function lockr_main_text() {
	echo "<p style='width: 80%;'>You're just one step away from secure key management! To register your site with Lockr, simply input an email address you'd like to associate your account with. If you're already a Lockr user, you can enter the email and password to login to your account and register this site. Dont' worry, we won't store your password locally.</p>";
}

function lockr_account_email_input() {
	$options = get_option( 'lockr_options' );
	echo  "<input id='lockr_account_email' name='lockr_options[account_email]' size='60' type='email' value='{$options['account_email']}' />";
}

function lockr_account_password_input() {
	$options = get_option( 'lockr_options' );
	echo  "<input id='lockr_account_email' name='lockr_options[account_password]' size='60' type='password' value='{$options['account_password']}' />";
}

function lockr_options_validate($input) {
	$options = get_option( 'lockr_options' );
	$options['account_email'] = trim( $input['account_email'] );
	$options['account_password'] = trim( $input['account_password'] );
	$name = get_bloginfo( 'name', 'display' );
	
	if ( ! filter_var( $options['account_email'], FILTER_VALIDATE_EMAIL ) ) {
		add_settings_error( 'lockr_options', 'lockr-email', $options['account_email'] . ' is not a proper email address. Please try again.', 'error' );
		$options['account_email'] = '';
	} else {
		try {
			lockr_site_client()->register( $options['account_email'], null, $name );
		} catch ( ClientException $e ) {
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
	list( $exists, $available ) = lockr_check_registration();
	?>
<div class="wrap">
	<?php if ( ! $exists ): ?>
		<h1>Lockr Registration</h1>
		<form method="post" action="options.php">
			<?php settings_fields('lockr_options'); ?>
			<?php do_settings_sections('lockr'); ?>
			<?php submit_button('Register Site with Lockr'); ?>
		</form>
	<?php else: ?>
		<h1>Lockr Registration Complete</h1>
		<p> You're all set! Your site is registered and ready to begin using Lockr. Wasn't that easy?</p>
		<p>There's nothing left for you do to here, your keys sent to Lockr are now protected. If you registered with the wrong account, you can click <a href="https://lockr.io/user/login" target="_blank">here</a> to go to Lockr and manage your sites.</p>
	<?php endif; ?>
	
</div>
<?php }
