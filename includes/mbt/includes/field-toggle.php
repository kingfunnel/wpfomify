<?php
$attrs = '';
$class = 'mbt-input-field';

if ( $value && '1' == $value ) {
	$attrs .= ' checked="checked"';
}

// Toggle data.
if ( isset( $field['toggle'] ) ) {
	$attrs .= ' data-toggle="' . esc_attr( json_encode( $field['toggle'] ) ) . '"';
}

// Custom field classes.
if ( isset( $field['class'] ) ) {
	$class .= ' ' . $field['class'];
}
?>
<label class="mbt-toggle-field">
	<input type="checkbox" id="<?php echo $id; ?>" name="<?php echo $id; ?>" class="<?php echo $class; ?>" value="1"<?php echo $attrs; ?> />
	<span class="mbt-toggle-slider"></span>
</label>
