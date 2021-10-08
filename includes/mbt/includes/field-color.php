<?php
$class = 'mbt-color-picker';

if ( isset( $field['class'] ) && ! empty( $field['class'] ) ) {
	$class .= ' ' . $field['class'];
}
?>
<input type="text" id="<?php echo $id; ?>" name="<?php echo $id; ?>" class="<?php echo $class; ?>" value="<?php echo $value; ?>" />
