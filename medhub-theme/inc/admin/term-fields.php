<?php
/**
 * Editable archive content on category and brand edit screens (wp-admin):
 *   - Display heading   (medhub_heading)        optional H1 override, e.g. "Medical Disposables and Supplies"
 *   - Below-products    (medhub_below_content)  "how to choose", buying notes, FAQs… shown under the grid
 *
 * The intro above the grid stays the existing WooCommerce term description.
 * These fields store term meta only when an editor saves the term – the theme
 * never writes them on its own.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

const MEDHUB_TERM_FIELD_TAXONOMIES = array( 'product_cat', 'product_brand' );

add_action(
	'admin_init',
	static function () {
		foreach ( MEDHUB_TERM_FIELD_TAXONOMIES as $taxonomy ) {
			add_action( "{$taxonomy}_edit_form_fields", 'medhub_term_fields_render', 20 );
			add_action( "edited_{$taxonomy}", 'medhub_term_fields_save' );
		}
	}
);

/**
 * Fields on the term edit screen.
 *
 * @param WP_Term $term Term being edited.
 */
function medhub_term_fields_render( WP_Term $term ): void {
	$heading = (string) get_term_meta( $term->term_id, 'medhub_heading', true );
	$below   = (string) get_term_meta( $term->term_id, 'medhub_below_content', true );
	wp_nonce_field( 'medhub_term_fields', 'medhub_term_fields_nonce' );
	?>
	<tr class="form-field">
		<th scope="row"><label for="medhub_heading"><?php esc_html_e( 'Display heading (H1)', 'medhub' ); ?></label></th>
		<td>
			<input name="medhub_heading" id="medhub_heading" type="text" value="<?php echo esc_attr( $heading ); ?>">
			<p class="description"><?php esc_html_e( 'Optional. Replaces the name as the page heading. Leave empty to use the name.', 'medhub' ); ?></p>
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row"><label for="medhub_below_content"><?php esc_html_e( 'Content below products', 'medhub' ); ?></label></th>
		<td>
			<?php
			wp_editor(
				$below,
				'medhub_below_content',
				array(
					'textarea_name' => 'medhub_below_content',
					'textarea_rows' => 10,
					'media_buttons' => false,
				)
			);
			?>
			<p class="description"><?php esc_html_e( 'Optional. Buying guidance or questions shown under the product grid. The short description above stays the intro.', 'medhub' ); ?></p>
		</td>
	</tr>
	<?php
}

/**
 * Save the fields (capability + nonce checked).
 *
 * @param int $term_id Term ID.
 */
function medhub_term_fields_save( int $term_id ): void {
	if (
		! isset( $_POST['medhub_term_fields_nonce'] )
		|| ! wp_verify_nonce( sanitize_key( $_POST['medhub_term_fields_nonce'] ), 'medhub_term_fields' )
		|| ! current_user_can( 'edit_term', $term_id )
	) {
		return;
	}

	$heading = isset( $_POST['medhub_heading'] ) ? sanitize_text_field( wp_unslash( $_POST['medhub_heading'] ) ) : '';
	$below   = isset( $_POST['medhub_below_content'] ) ? wp_kses_post( wp_unslash( $_POST['medhub_below_content'] ) ) : '';

	foreach ( array( 'medhub_heading' => $heading, 'medhub_below_content' => $below ) as $key => $value ) {
		if ( '' === $value ) {
			delete_term_meta( $term_id, $key );
		} else {
			update_term_meta( $term_id, $key, $value );
		}
	}
}
