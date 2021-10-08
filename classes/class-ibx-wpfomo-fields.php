<?php
/**
 * Class for registering new custom fields for metabox.
 */
class IBX_WPFomo_Fields {
	/**
	 * Initialize the hooks and filters.
	 *
	 * @since 2.0
	 */
	static public function init() {
		add_action( 'mbt_field_template', __CLASS__ . '::register_template_field', 10, 4 );
		add_action( 'mbt_field_layout', __CLASS__ . '::register_layout_field', 10, 4 );
	}

	/**
	 * Register a new field and call it template.
	 *
	 * @since 2.0
	 *
	 * @param string $name
	 * @param mixed $value
	 * @param array $field
	 * @param int $post_id
	 *
	 * @hook mbt_field_template
	 */
	static public function register_template_field( $name, $value, $field, $post_id ) {
		$vars = array();
		$class = 'mbt-input-field ibx-wpfomo-notification-template';
		$source = '';
		if ( isset( $field['source'] ) ) {
			$source = ' data-source="' . $field['source'] . '"';
		}

		if ( isset( $field['class'] ) ) {
			$class .= ' ' . $field['class'];
		}

		if ( isset( $field['variables'] ) ) {
			$vars = (array) $field['variables'];
		}

		if ( empty( $value ) ) {
			$value = array();
		}

		if ( ! is_array( $value ) ) {
			$value_array = explode( '{{title}}', $value );
			$value = array(
				isset( $value_array[0] ) ? trim( $value_array[0] ) : '',
				'{{title}}',
				isset( $value_array[1] ) ? trim( $value_array[1] ) : '',
			);
		}

		$layout = get_post_meta( $post_id, 'ibx_wpfomo_notification_layout', true );
		$first_row_font_size = get_post_meta( $post_id, 'ibx_wpfomo_first_row_font_size', true );
		$second_row_font_size = get_post_meta( $post_id, 'ibx_wpfomo_second_row_font_size', true );

		$css = array();
		$parsed_css = array();

		if ( isset( $value['_css'] ) ) {
			$css = $value['_css'];
			unset( $value['_css'] );
		}

		if ( is_array( $css ) && ! empty( $css ) ) {
			$parsed_css = $css;
		}
		?>

		<div class="ibx-wpfomo-template-field">
			<?php for ( $i = 0; $i < 3; $i++ ) {
				$font_size = '12';

				if ( $i < 1 ) {
					$font_size = empty( $first_row_font_size ) ? 13 : $first_row_font_size;
				}
				if ( 1 == $i ) {
					$font_size = empty( $second_row_font_size ) ? 14 : $second_row_font_size;
				}

				$selector_style = isset( $parsed_css[ $i ] ) ? $parsed_css[ $i ] : '';
				$style = IBX_WPFomo_Helper::parse_notification_css( $selector_style );

				// old notification layout field backward compatibility.
				if ( 'first' == $layout && $i < 1 ) {
					$style['font-weight'] = 'bold';
				}
				if ( 'second' == $layout && 1 == $i ) {
					$style['font-weight'] = 'bold';
				}

				$selector_style = IBX_WPFomo_Helper::stringify_notification_css( $style );
				?>
				<div class="ibx-wpfomo-template--row">
					<input type="text" id="ibx_wpfomo_<?php echo $name; ?>_<?php echo $i; ?>" name="ibx_wpfomo_<?php echo $name; ?>[]" class="<?php echo $class; ?>" value="<?php echo isset( $value[ $i ] ) ? htmlspecialchars( $value[ $i ] ) : ''; ?>" autocomplete="false" data-index="<?php echo $i; ?>"<?php echo $source; ?> style="<?php echo $selector_style; ?>" />
					<input type="hidden" name="ibx_wpfomo_<?php echo $name; ?>[_css][]" class="ibx_wpfomo_notification_css" value="<?php echo $selector_style; ?>" />
					<div class="ibx-wpfomo-template-toolbar">
						<span class="ibx-wpfomo-template--bold<?php echo isset( $style['font-weight'] ) ? ' active': ''; ?>" title="<?php esc_html_e( 'Bold', 'ibx-wpfomo' ); ?>">B</span>
						<span class="ibx-wpfomo-template--italic<?php echo isset( $style['font-style'] ) ? ' active': ''; ?>" title="<?php esc_html_e( 'Italic', 'ibx-wpfomo' ); ?>"><em>I</em></span>
						<span class="ibx-wpfomo-template--font-size" title="<?php esc_html_e( 'Font Size', 'ibx-wpfomo' ); ?>">
							<input type="number" value="<?php echo isset( $style['font-size'] ) ? str_replace( 'px', '', $style['font-size'] ) : $font_size; ?>" />
						</span>
					</div>
				</div>
			<?php } ?>
		</div>
		<?php if ( ! empty( $vars ) ) { ?>
			<div class="ibx-wpfomo-template-vars">
				<?php _e( 'Variables: ', 'ibx-wpfomo' ); ?>
				<?php foreach ( $vars as $var ) { ?>
					<span class="ibx-wpfomo-merge-tag"><?php echo $var; ?></span>&nbsp;
				<?php } ?>
			</div>
		<?php } ?>
		<?php
	}

	static public function register_layout_field( $name, $value, $field, $post_id ) {
		$value = empty( $value ) ? 'custom' : $value;
		$image_radius = get_post_meta( $post_id, 'ibx_wpfomo_img_round_corners', true );
		?>
		<input type="hidden" name="ibx_wpfomo_<?php echo $name; ?>" value="<?php echo $value; ?>" />
		<style>
			.ibx-wpfomo-layouts .ibx-wpfomo-layout .ibx-popup-style-custom .ibx-notification-popup-img {
				<?php if ( $image_radius !== '' ) { ?>
					border-radius: <?php echo $image_radius; ?>px;
				<?php } ?>
			}
		</style>
		<div class="ibx-wpfomo-layouts">
			<div class="ibx-wpfomo-layout<?php echo '1' == $value ? ' current' : ''; ?>" data-skin="1">
				<div class="ibx-notification-popup ibx-popup-style-1">
					<div class="ibx-notification-popup-img">
						<img src="<?php echo IBX_WPFOMO_URL . 'assets/img/wpfomify-80x80.png'; ?>" alt="">
					</div>
					<div class="ibx-notification-popup-text type-conversion">
						<span class="ibx-notification-row-first"><?php _e( 'John D. recently purchased', 'ibx-wpfomo' ); ?></span>
						<span class="ibx-notification-popup-title ibx-notification-row-second"><?php _e( 'Example Product', 'ibx-wpfomo' ); ?></span>
						<span class="ibx-notification-row-third"><small>1 hour ago</small></span>
					</div>
				</div>
			</div>
			<div class="ibx-wpfomo-layout<?php echo '2' == $value ? ' current' : ''; ?>" data-skin="2">
				<div class="ibx-notification-popup ibx-popup-style-2">
					<div class="ibx-notification-popup-img">
						<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 20 20" xml:space="preserve"><path class="st0" d="M0,7.4c0,4.2,0,8.4,0,12.6c0,0,0,0,0,0C0.1,19.9,13.4,6.5,20,0C14.7,4.6,7.9,7.4,0.4,7.4C0.3,7.4,0.1,7.4,0,7.4z"></path></svg>
						<img src="<?php echo IBX_WPFOMO_URL . 'assets/img/wpfomify-80x80.png'; ?>" alt="">
					</div>
					<div class="ibx-notification-content">
						<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 4.4 20" xml:space="preserve"><path class="st0" d="M0.7,0C3,2.6,4.4,5.9,4.4,9.6c0,4-1.7,7.7-4.3,10.4c1.5,0,4,0,5.4,0V0C3.8,0,1.5,0,0.7,0z"></path></svg>
						<div class="ibx-notification-popup-text type-conversion">
							<span class="ibx-notification-row-first"><?php _e( 'John D. recently purchased', 'ibx-wpfomo' ); ?></span>
							<span class="ibx-notification-popup-title ibx-notification-row-second"><?php _e( 'Example Product', 'ibx-wpfomo' ); ?></span>
							<span class="ibx-notification-row-third"><small>1 hour ago</small></span>
						</div>
					</div>
				</div>
			</div>
			<div class="ibx-wpfomo-layout ibx-wpfomo-layout-custom<?php echo 'custom' == $value ? ' current' : ''; ?>" data-skin="custom">
				<div class="ibx-notification-popup ibx-popup-style-custom ibx-notification-popup-<?php echo $post_id; ?>">
					<div class="ibx-notification-popup-img">
						<img src="<?php echo IBX_WPFOMO_URL . 'assets/img/wpfomify-80x80.png'; ?>" alt="">
					</div>
					<div class="ibx-notification-popup-text type-conversion">
						<span class="ibx-notification-row-first"><?php _e( 'John D. recently purchased', 'ibx-wpfomo' ); ?></span>
						<span class="ibx-notification-popup-title ibx-notification-row-second"><?php _e( 'Example Product', 'ibx-wpfomo' ); ?></span>
						<span class="ibx-notification-row-third"><small>1 hour ago</small></span>
					</div>
				</div>
			</div>
		</div>
		<script>
			jQuery('#mbt-field-<?php echo $name; ?> .ibx-wpfomo-layout').on('click', function() {
				jQuery(this).siblings().removeClass('current');
				jQuery(this).addClass('current');
				jQuery('input[name="ibx_wpfomo_<?php echo $name; ?>').val( jQuery(this).data('skin') );
			});
		</script>
		<?php
	}
}

// Initialize the class.
IBX_WPFomo_Fields::init();
