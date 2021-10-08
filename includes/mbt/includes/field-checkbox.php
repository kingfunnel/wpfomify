<?php
$attrs = '';
$class = 'mbt-input-field';

if ( isset( $field['disabled'] ) && $field['disabled'] ) {
	$attrs .= ' disabled="disabled"';
}

if ( isset( $field['multiple'] ) && $field['multiple'] ) {
	$attrs .= ' multiple="multiple"';
}

if ( absint( $value ) == 1 ) {
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
<input type="checkbox" id="<?php echo $id; ?>" name="<?php echo $id; ?>" class="<?php echo $class; ?>" value="1"<?php echo $attrs; ?> />
