<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IBX_WPFomo_List extends WP_List_Table {
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Entry', 'ibx-wpfomo' ), // singular name of the listed records
				'plural'   => __( 'Entries', 'ibx-wpfomo' ), // plural name of the listed records
				'ajax'     => true, // should this table support ajax?
			)
		);
	}

	/**
	 * Text displayed when no data is available.
	 */
	public function no_items() {
		_e( 'No entries available.', 'ibx-wpfomo' );
	}

	/**
	 * Method for column title.
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	public function column_name( $item ) {
		// create a nonce
		$delete_nonce = wp_create_nonce( 'ibx_wpfomo_custom_form_delete_entry' );

		$title = '<strong>' . $item['name'] . '</strong>';

		$post_id = IBX_WPFomo_List_Helper::get_post_id();
		$item_id = isset( $item['id'] ) ? $item['id'] : 0;

		if ( isset( $_GET['src'] ) && ! empty( $_GET['src'] ) ) {
			$actions = array(
				'inline' => sprintf(
					'<a href="?post_type=%s&page=%s&post_id=%s&action=%s&entry_id=%s&src=%s" class="editinline" aria-label="%s">%s</a>',
					esc_attr( wp_unslash( $_REQUEST['post_type'] ) ),
					esc_attr( wp_unslash( $_REQUEST['page'] ) ),
					$post_id,
					'edit',
					$item_id,
					esc_attr( $_GET['src'] ),
					__( 'Quick Edit', 'ibx-wpfomo' ),
					__( 'Quick Edit', 'ibx-wpfomo' )
				),
				'delete' => sprintf(
					'<a href="?post_type=%s&page=%s&post_id=%s&action=%s&entry_id=%s&_wpnonce=%s&src=%s" aria-label="%s">%s</a>',
					esc_attr( wp_unslash( $_REQUEST['post_type'] ) ),
					esc_attr( wp_unslash( $_REQUEST['page'] ) ),
					$post_id,
					'delete',
					$item_id,
					$delete_nonce,
					esc_attr( $_GET['src'] ),
					__( 'Delete', 'ibx-wpfomo' ),
					__( 'Delete', 'ibx-wpfomo' )
				),
			);

		} else {
			$actions = array(
				'inline' => sprintf(
					'<a href="?post_type=%s&page=%s&post_id=%s&action=%s&entry_id=%s" class="editinline" aria-label="%s">%s</a>',
					esc_attr( wp_unslash( $_REQUEST['post_type'] ) ),
					esc_attr( wp_unslash( $_REQUEST['page'] ) ),
					$post_id,
					'edit',
					$item_id,
					__( 'Quick Edit', 'ibx-wpfomo' ),
					__( 'Quick Edit', 'ibx-wpfomo' )
				),
				'delete' => sprintf(
					'<a href="?post_type=%s&page=%s&post_id=%s&action=%s&entry_id=%s&_wpnonce=%s" aria-label="%s">%s</a>',
					esc_attr( wp_unslash( $_REQUEST['post_type'] ) ),
					esc_attr( wp_unslash( $_REQUEST['page'] ) ),
					$post_id,
					'delete',
					$item_id,
					$delete_nonce,
					__( 'Delete', 'ibx-wpfomo' ),
					__( 'Delete', 'ibx-wpfomo' )
				),
			);
		} // End if().

		return $title . $this->row_actions( $actions );
	}

	/**
	 * Render a column when no column specific method exists.
	 *
	 * @param array  $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'name':
			case 'email':
			case 'title':
			case 'ip_address':
			case 'time':
				return isset( $item[ $column_name ] ) ? $item[ $column_name ] : '-';
			default:
				// return print_r( $item, true ); // show the whole array for troubleshooting purposes
				return isset( $item[ $column_name ] ) ? $item[ $column_name ] : '-';// for country, state and city
		}
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		$item_id = isset( $item['id'] ) ? $item['id'] : 0;

		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />',
			$item_id
		);
	}

	/**
	 * Associative array of columns
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'         => '<input type="checkbox" />',
			'name'       => __( 'Name', 'ibx-wpfomo' ),
			'email'      => __( 'Email', 'ibx-wpfomo' ),
			'title'		 => __( 'Form', 'ibx-wpfomo' ),
			'ip_address' => __( 'IP Address', 'ibx-wpfomo' ),
			'time'       => __( 'Time', 'ibx-wpfomo' ),
		);

		if ( isset( $_REQUEST['src'] ) && 'csv' == sanitize_text_field( $_REQUEST['src'] ) ) {
			$columns['title'] 	= __( 'Title', 'ibx-wpfomo' );
			$columns['country'] = __( 'Country', 'ibx-wpfomo' );
			$columns['state']   = __( 'State', 'ibx-wpfomo' );
			$columns['city']    = __( 'City', 'ibx-wpfomo' );
			//unset( $columns['form'] );
		}

		return $columns;
	}

	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {

		$sortable_columns = array(
			'name'  => array( 'name', true ),
			'email' => array( 'email', true ),
			'time'  => array( 'time', true ),
		);

		return $sortable_columns;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'bulk-delete' => __( 'Delete', 'ibx-wpfomo' ),
		);

		return $actions;
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {
		$this->_column_headers = $this->get_column_info();

		// Process bulk action.
		$this->process_bulk_action();

		$per_page     = $this->get_items_per_page( 'ibx_wpfomo_custom_form_entries_per_page', 10 );
		$current_page = $this->get_pagenum();
		$total_items  = IBX_WPFomo_List_Helper::entries_count();

		$this->set_pagination_args(
			array(
				'total_items' => $total_items, // We have to calculate the total number of items
				'per_page'    => $per_page, // We have to determine how many items to show on a page
			)
		);

		$this->items = IBX_WPFomo_List_Helper::get_entries( $per_page, $current_page );
	}

	/**
	 * Handles bulk action for entries.
	 */
	public function process_bulk_action() {
		$post_type = esc_attr( wp_unslash( $_REQUEST['post_type'] ) );
		$page = esc_attr( wp_unslash( $_REQUEST['post_type'] ) );

		// Detect when a bulk action is being triggered..
		if ( 'delete' === $this->current_action() ) {
			// Verify the nonce first.
			$nonce = sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) );

			if ( ! wp_verify_nonce( $nonce, 'ibx_wpfomo_custom_form_delete_entry' ) ) {
				die( __( 'Invalid delete request.', 'ibx-wpfomo' ) );
			} else {
				IBX_WPFomo_List_Helper::delete_entry( absint( $_GET['entry_id'] ) );

				$post_id   = IBX_WPFomo_List_Helper::get_post_id();
				$admin_url = admin_url( '/edit.php' );

				if ( isset( $_GET['src'] ) && 'csv' === sanitize_text_field( $_GET['src'] ) ) {
					wp_redirect( sprintf( '%s?post_type=%s&page=%s&post=%s&src=%s', $admin_url, $post_type, $page, $post_id, 'csv' ) );
				} else {
					wp_redirect( sprintf( '%s?post_type=%s&page=%s&post=%s', $admin_url, $post_type, $page, $post_id ) );
				}
				exit;
			}
		}

		// If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && 'bulk-delete' === sanitize_text_field( $_POST['action'] ) )
			|| ( isset( $_POST['action2'] ) && 'bulk-delete' === sanitize_text_field( $_POST['action2'] ) )
		) {

			$delete_ids = esc_sql( $_POST['bulk-delete'] );

			// loop over the array of record IDs and delete them
			foreach ( $delete_ids as $id ) {
				IBX_WPFomo_List_Helper::delete_entry( $id );
			}

			$post_id   = IBX_WPFomo_List_Helper::get_post_id();
			$admin_url = admin_url( '/edit.php' );

			if ( isset( $_GET['src'] ) && 'csv' === sanitize_text_field( $_GET['src'] ) ) {
				wp_redirect( sprintf( '%s?post_type=%s&page=%s&post=%s&src=%s', $admin_url, $post_type, $page, $post_id, 'csv' ) );
			} else {
				wp_redirect( sprintf( '%s?post_type=%s&page=%s&post=%s', $admin_url, $post_type, $page, $post_id ) );
			}

			exit;
		}
	}

	/**
	 * Generates content for a single row of the table.
	 *
	 * @param object $item The current item
	 */
	public function single_row( $item ) {
		$entry_id = isset( $item['id'] ) ? $item['id'] : '0';

		echo '<tr id="entry-' . $entry_id . '" class="ibx-wpfomo-custom-form-entry">';
		$this->single_row_columns( $item );
		echo '</tr>';

		// Inline edit.
		$columns = $this->get_columns();
		if ( isset( $columns['cb'] ) ) {
			unset( $columns['cb'] );
		}
		?>
		<tr id="edit-<?php echo $entry_id; ?>" class="inline-edit-row inline-edit-row-page quick-edit-row quick-edit-row-page" style="display: none;">
			<td colspan="<?php echo $this->get_column_count(); ?>" class="colspanchange">
				<fieldset class="inline-edit-col-left">
					<legend class="inline-edit-legend"><?php _e( 'Quick Edit', 'ibx-wpfomo' ); ?></legend>
					<div class="inline-edit-col">
						<?php
						foreach ( $columns as $key => $col_name ) :
							if ( 'time' === $key ) {
								//continue;
							}
							?>
						<label>
							<span class="title"><?php echo $col_name; ?></span>
							<span class="input-text-wrap">
								<input type="text" class="entry-input" name="entry_<?php echo $key; ?>" value="<?php echo isset( $item[ $key ] ) ? $item[ $key ] : ''; ?>" />
							</span>
						</label>
						<?php endforeach; ?>
					</div>
				</fieldset>
				
				<p class="submit inline-edit-save">
					<button type="button" class="button cancel alignleft"><?php _e( 'Cancel', 'ibx-wpfomo' ); ?></button>
					<?php wp_nonce_field( 'inlineeditnonce', '_inline_edit', false ); ?>
					<button type="button" class="button button-primary save" style="float: inherit;margin-left: 10px;"><?php _e( 'Update', 'ibx-wpfomo' ); ?></button>
					<span class="spinner"></span>
					<input type="hidden" name="ibx_wpfomo_custom_form_edit_entry_id" value="<?php echo $entry_id; ?>" />
					<input type="hidden" name="ibx_wpfomo_custom_form_edit_post_id" value="<?php echo IBX_WPFomo_List_Helper::get_post_id(); ?>" />
					<span class="error" style="display:none"></span>
					<br class="clear" />
				</p>
			</td>
		</tr>
		<?php
	}
}
