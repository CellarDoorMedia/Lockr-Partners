<?php

/**
 * @file
 * Form callbacks for Lockr register form.
 */
 
// Don't call the file directly and give up info!
if ( ! function_exists( 'add_action' ) ) {
	echo 'Lock it up!';
	exit;
}

use Lockr\Exception\ClientException;
use Lockr\Exception\ServerException;

//Include our admin forms
require_once( LOCKR__PLUGIN_DIR . '/lockr-admin-config.php' );
require_once( LOCKR__PLUGIN_DIR . '/lockr-admin-add.php' );
require_once( LOCKR__PLUGIN_DIR . '/lockr-admin-edit.php' );

add_action( 'admin_menu', 'lockr_admin_menu');
add_action( 'admin_init', 'register_lockr_settings' );
add_action( 'admin_post_lockr_admin_submit_add_key', 'lockr_admin_submit_add_key' );
add_action( 'admin_post_lockr_admin_submit_edit_key', 'lockr_admin_submit_edit_key' );

function lockr_admin_menu() {
	$icon_svg = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxOS4xLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiDQoJIHZpZXdCb3g9IjAgMCAyMCAyMCIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMjAgMjA7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxnPg0KCTxwYXRoIGlkPSJYTUxJRF8yODlfIiBkPSJNMTUuNSw5LjZoLTIuNFY0LjdjMC0xLjQtMC43LTIuMS0yLTIuMWgtMC44VjAuMmgwLjNjMy4yLDAsNC44LDEuNSw0LjgsNC42VjkuNnoiLz4NCgk8cGF0aCBpZD0iWE1MSURfMjg2XyIgZD0iTTQuNCwxMC4zdjQuNWMwLDMsMS43LDUsNC44LDVoMC4yaDEuMmgwLjFjMy4xLDAsNC43LTEuOSw1LTV2LTQuNUg0LjR6IE0xMC42LDE1LjJ2MS4yDQoJCWMwLDAuNC0wLjMsMC44LTAuNywwLjhjLTAuNCwwLTAuNy0wLjMtMC43LTAuOHYtMS4yYy0wLjMtMC4zLTAuOC0wLjgtMC44LTEuNGMwLTAuOSwwLjctMS42LDEuNi0xLjZjMC45LDAsMS42LDAuNywxLjYsMS42DQoJCUMxMS41LDE0LjMsMTAuOSwxNC45LDEwLjYsMTUuMnoiLz4NCgk8cGF0aCBpZD0iWE1MSURfMjc3XyIgZD0iTTQuNCw0LjdjMC4xLTMsMS43LTQuNiw0LjgtNC42aDAuM3YyLjVIOC44Yy0xLjMsMC0yLDAuNy0yLDIuMXY0LjlINC40VjQuN3oiLz4NCjwvZz4NCjwvc3ZnPg0K';
	add_menu_page( __( 'Lockr Key Storage', 'lockr' ), __( 'Lockr', 'lockr' ), 'manage_options', 'lockr', 'lockr_keys_table', $icon_svg  );
	add_submenu_page( 'lockr', __( 'Lockr Key Storage', 'lockr' ), __( 'All Keys', 'lockr' ), 'manage_options', 'lockr' );
	add_submenu_page( 'lockr', __( 'Create Lockr Key', 'lockr' ), __( 'Add Key', 'lockr' ), 'manage_options', 'lockr-add-key', 'lockr_add_form' );
	add_submenu_page( null, __( 'Edit Lockr Key', 'lockr' ), __( 'Edit Key', 'lockr' ), 'manage_options', 'lockr-edit-key', 'lockr_edit_form' );
	add_submenu_page( 'lockr', __( 'Lockr Configuration', 'lockr' ), __( 'Lockr Configuration', 'lockr' ), 'manage_options', 'lockr-site-config', 'lockr_configuration_form' );
}

//Admin Table for Lockr Key Management
if ( ! class_exists('WP_List_Table') ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if ( ! get_option( 'lockr_partner' ) ) {
  $partner = lockr_get_partner();

  if ($partner) {
    add_option( 'lockr_partner', $partner['name'] );
    add_option( 'lockr_cert', $partner['cert'] );
  }
}

class Key_List extends WP_List_Table {
	
	public function __construct() {
		parent::__construct(array(
			'singular' => __( 'Key', 'lockr' ),
			'plural' => __( 'Keys', 'lockr' ),
			'ajax' => false
		));
	}
	
	// Text displayed when no key data is available
	public function no_items() {
		_e( 'No keys stored yet.', 'sp' );
	}
	
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s" value="%2$s" />',
			$this->_args['plural'] . '[]',
			$item->key_name
		);
	}
	
	function get_columns() {
		return $columns = array(
			'cb' => '<input type="checkbox" />',
			'key_label' => __( 'Key Name' ),
			'key_abstract' => __( 'Key Value' ),
			'time' => __( 'Created' ),
			'edit' => '',
		);
	}
	
	public function get_sortable_columns() {
		$sortable_columns = array(
			'key_label' => array( 'key_label', true ),
			'time' => array( 'time', false )
		);

		return $sortable_columns;
	}

	function column_default($item, $column_name) {
		switch ( $column_name ) {
			case 'key_label':
				return $item->key_label;
			case 'key_abstract':
				return $item->key_abstract;
			case 'time':
				return $item->time;
			case 'edit':
				$url = admin_url( 'admin.php?page=lockr-edit-key' );
				$url .= '&key=' . $item->key_name;
				return "<a href='$url' >edit</a>";
		}
	}
  
	function prepare_items() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'lockr_keys';

		// Process any bulk actions first
		$this->process_bulk_action();

		$query = "SELECT * FROM $table_name";

		// setup result ordering
		$orderby = ! empty( $_GET['orderby'] ) ? $_GET['orderby'] : 'ASC';
		$order = ! empty( $_GET['order'] ) ? $_GET['order'] : '';
		if ( ! empty( $orderby ) & ! empty( $order ) ) {
			$query .= $wpdb->prepare( ' ORDER BY %s %s', array( $orderby , $order ) );
		}

		$totalitems = $wpdb->query( $query );

		// First, lets decide how many records per page to show
		$perpage = 5;

		// Which page is this?
		$paged = ! empty( $_GET['paged'] ) ? esc_sql( $_GET['paged'] ) : '';
		// Page Number
		if ( empty( $paged ) || ! is_numeric( $paged ) || $paged <= 0 ) {
			$paged = 1;
		}

		// How many pages do we have in total?
		$totalpages = ceil( $totalitems / $perpage );
		// Adjust the query to take pagination into account
		if ( ! empty( $paged ) && ! empty( $perpage ) ) {
			$offset = ($paged - 1) * $perpage;
			$query .= $wpdb->prepare( ' LIMIT %d,%d', array( (int) $offset, (int) $perpage ) );
		}

		// Register the pagination
		$this->set_pagination_args( array(
			'total_items' => $totalitems,
			'total_pages' => $totalpages,
			'per_page' => $perpage,
		) );

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items = $wpdb->get_results( $query );
	}
  
	/**
	 * Delete a Lockr key.
	 *
	 * @param string $key_name machine name of the key
	 */
	public static function delete_key( $key_name ) {
		lockr_delete_key( $key_name );
	}
	
	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	function get_bulk_actions() {
		$actions = array(
			'bulk-delete' => 'Delete'
		);

		return $actions;
	}
	
	public function process_bulk_action() {
		if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {
			$nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
			$nonce_action = 'bulk-' . $this->_args['plural'];
			if ( ! wp_verify_nonce( $nonce, $nonce_action ) )
			wp_die( 'Lock it up!' );
		}

		// If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
			|| ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
		) {
			$names = esc_sql( $_POST['keys'] );
			foreach ( $names as $name ) {
				self::delete_key( $name );
				echo "<div id='message' class='updated fade'><p><strong>You successfully deleted the $name key from Lockr.</strong></p></div>";
			}
		}
	}  
}

function lockr_keys_table() {
	list( $exists, $available ) = lockr_check_registration();
	$keyTable = new Key_List();
	$keyTable->prepare_items();
	?>
	<div class="wrap">
		<?php if ( !$exists ): ?>
			<h1>Register Lockr First</h1>
			<p>Before you can add keys, you must first <a href="<?php echo admin_url( 'admin.php?page=lockr-site-config' ); ?>">register your site</a> with Lockr.</p>
		<?php else: ?>
			<h1>Lockr Key Storage:</h1>
				<?php if ( isset( $_GET['message'] ) && $_GET['message'] == 'success' ): ?>
					<div id='message' class='updated fade'><p><strong>You successfully added the key to Lockr.</strong></p></div>
				<?php endif; ?>
				<?php if ( isset( $_GET['message'] ) && $_GET['message'] == 'editsuccess' ): ?>
					<div id='message' class='updated fade'><p><strong>You successfully edited your key in Lockr.</strong></p></div>
				<?php endif; ?>
				<p> Below is a list of the keys currently stored within Lockr. You may edit/delete from here or <a href="<?php echo admin_url( 'admin.php?page=lockr-add-key' ); ?>">add one manually</a> for any plugins not yet supporting Lockr. </p>
				<form id="lockr-key-table" method="post">
          <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
          <?php $keyTable->display(); ?>
				</form>
		<?php endif; ?>
	</div>
<?php }
