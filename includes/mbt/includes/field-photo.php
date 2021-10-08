<?php
$class = 'mbt-input-field';

// Custom field classes.
if ( isset( $field['class'] ) ) {
	$class .= ' ' . $field['class'];
}

// Required.
if ( isset( $field['required'] ) && $field['required'] ) {
	$class .= ' mbt-required-field';
}

if ( is_object( $value ) ) {
	$value = (array) $value;
}

// Get WordPress' media upload URL
$upload_link = esc_url( get_upload_iframe_src( 'image', $post_id ) );

// See if there's a media id already saved as post meta
$img_id = isset( $value['id'] ) ? $value['id'] : '';

// See if there's a media url already saved as post meta
$img_url = isset( $value['url'] ) ? $value['url'] : '';

// Get the image src
$img_src = wp_get_attachment_image_src( $img_id, 'full' );

// For convenience, see if the array is valid
$has_img = is_array( $img_src );
?>

<div class="mbt-photo-field-wrapper">
	<!-- Image container, which can be manipulated with js -->
	<div class="mbt-img-container<?php echo ( $has_img  ) ? ' mbt-has-img' : ''; ?>">
		<?php if ( $has_img ) : ?>
			<img src="<?php echo $img_src[0] ?>" alt="" style="max-width:100%;" />
		<?php endif; ?>
	</div>

	<div class="mbt-img-input">
		<input type="text" id="<?php echo $id; ?>[url]" name="<?php echo $id; ?>[url]" class="mbt-img-url <?php echo $class; ?>" value="<?php echo $img_url; ?>" placeholder="<?php echo isset( $field['placeholder'] ) ? sanitize_text_field( $field['placeholder'] ) : ''; ?>" />
		<div class="mbt-img-action">
			<!-- Add & remove image buttons -->
			<p class="hide-if-no-js">
				<a class="button mbt-upload-img<?php echo ( $has_img ) ? ' hidden' : ''; ?>"
					href="<?php echo $upload_link ?>">
					<?php echo ( isset( $field['text'] ) && isset( $field['text']['upload'] ) ) ? $field['text']['upload'] : esc_html__( 'Upload Photo', 'mbt' ); ?>
				</a>
				<a class="mbt-delete-img<?php echo ( ! $has_img ) ? ' hidden' : ''; ?>"
					href="#" style="color: red;">
					<?php echo ( isset( $field['text'] ) && isset( $field['text']['remove'] ) ) ? $field['text']['remove'] : esc_html__( 'Remove', 'mbt' ); ?>
				</a>
			</p>
		</div>
	</div>

	<!-- A hidden input to set and post the chosen image id -->
	<input class="mbt-img-id" id="<?php echo $id; ?>[id]" name="<?php echo $id; ?>[id]" type="hidden" value="<?php echo esc_attr( $img_id ); ?>" />
</div>
