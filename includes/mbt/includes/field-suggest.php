<?php
$attrs = '';
$class = 'mbt-suggest-field';

// Custom field classes.
if ( isset( $field['class'] ) ) {
	$class .= ' ' . $field['class'];
}

// Required.
if ( isset( $field['required'] ) && $field['required'] ) {
	$class .= ' mbt-required-field';
}

$field_action = isset( $field['action'] ) ? $field['action'] : '';
$field_options = isset( $field['options'] ) ? $field['options'] : array();

$field_data = MetaBox_Tabs_Helper::get_suggest_data( $field_action, $field_options );

if ( ! is_array( $value ) ) {
	$value = (array) $value;
}

if ( ! empty( $value ) ) {
	$class .= ' mbt-suggest-has-data';
}

// TODO:
if ( isset( $field['ajax'] ) && $field['ajax'] ) {
	$attrs = ' data-ajax="true" data-action="' . $field_action . '" data-options="' . esc_attr( json_encode( $field_options ) ) . '"';
}
?>
<div id="<?php echo $id; ?>" class="<?php echo $class; ?>">
	<?php if ( isset( $field['placeholder'] ) && ! empty( $field['placeholder'] ) ) : ?>
		<span class="mbt-suggest-placeholder"><?php echo $field['placeholder']; ?></span>
	<?php endif; ?>
	<div class="mbt-suggest-select"></div>
	<ul class="mbt-suggest-items">
		<?php if ( is_array( $field_data ) && count( $field_data ) ) : ?>
			<li class="mbt-suggest-search">
				<input type="text" class="mbt-suggest-search-input" value="" placeholder="<?php _e( 'Search...', 'mbt' ); ?>" />
			</li>
			<?php foreach ( $field_data as $option_key => $option_value ) : ?>
			<li class="mbt-suggest-item" data-value="<?php echo $option_key; ?>">
				<label for="<?php echo $id; ?>_<?php echo $option_key; ?>">
					<input type="checkbox" id="<?php echo $id; ?>_<?php echo $option_key; ?>" class="mbt-input-field" name="<?php echo $id; ?>[]" value="<?php echo $option_key; ?>" data-label="<?php echo $option_value; ?>"<?php echo in_array( $option_key, $value ) ? ' checked="checked"' : ''; ?> />
					<span><?php echo $option_value; ?></span>
				</label>
			</li>
			<?php endforeach;
		endif; ?>
	</ul>
	<script type="text/html" id="<?php echo $id; ?>_markup" class="mbt-suggest-item-html">
		<li class="mbt-suggest-item" data-value="">
			<label for="">
				<input type="checkbox" id="" class="mbt-input-field" name="<?php echo $id; ?>[]" value="" data-label="" />
				<span></span>
			</label>
		</li>
	</script>
</div>
