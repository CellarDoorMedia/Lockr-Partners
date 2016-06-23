<?php

// Don't call the file directly and give up info!
if ( ! function_exists( 'add_action' ) ) {
	echo 'Lock it up!';
	exit;
}

function lockr_admin_submit_add_key() {
	if ( !current_user_can( 'manage_options' ) ) {
		wp_die( 'You are not allowed to add a key.' );
	}
	
	check_admin_referer( 'lockr_admin_verify' );
	
	$key_label = $_POST['key_label'];
	//Just incase our javascript didn't clean it up
	$key_name = strtolower( $_POST['key_name'] );
	$key_name = preg_replace( '@[^a-z0-9_]+@','_', $key_name );
	$key_value = $_POST['key_value'];
	
	$key_store = lockr_set_key( $key_name, $key_value, $key_label );
	
	if ( $key_store != false ) {
		// Successfully Added
		wp_redirect( admin_url( 'admin.php?page=lockr&message=success' ) );
		exit;
	} else {
		// Failed Addition
		wp_redirect( admin_url( 'admin.php?page=lockr-add-key&message=failed' ) );
		exit;
	}
}

function lockr_add_form() {
	list( $exists, $available ) = lockr_check_registration();
	$js_url = LOCKR__PLUGIN_URL . '/js/lockr.js';
	?>
<script type="text/javascript" src="<?php print $js_url; ?>"></script>
<div class="wrap">
	<?php if ( !$exists ): ?>
		<h1>Register Lockr First</h1>
		<p>Before you can add keys, you must first <a href="<?php echo admin_url( 'admin.php?page=lockr-site-config' ); ?>">register your site</a> with Lockr.</p>
	<?php else: ?>
		<h1>Add a Key to Lockr</h1>
			<?php if ( isset( $_GET['message'] ) && $_GET['message'] == 'failed' ): ?>
				<div id='message' class='updated fade'><p><strong>There was an issue in saving your key, please try again.</strong></p></div>
			<?php endif; ?>
			<p> Simply fill in the form below and we'll keep the key safe for you in Lockr.</p>
			<form method="post" action="admin-post.php">
				<input type="hidden" name="action" value="lockr_admin_submit_add_key" />
				<?php wp_nonce_field( 'lockr_admin_verify' ); ?>
				<div class="form-item key-label">
					<label for="key_label">Key Name:</label>
					<input type="text" name="key_label" placeholder="Your Key Name"/>
					<span class="machine-name-label">Machine Name:<a href="" class="show-key-name"></a></span>
				</div>
				<div class="form-item machine-name hidden">
					<label for="key_name">Key Machine Name:</label>
					<input type="text" name="key_name" placeholder=""/>
				</div>
				<div class="form-item">
					<label for="key_value">Key Value:</label>
					<input type="text" name="key_value" placeholder="Your Key Value"/>
				</div>
				<br />
				<input type="submit" value="Add Key" class="button-primary"/>
			</form>
	<?php endif; ?>
	
</div>
<?php }
