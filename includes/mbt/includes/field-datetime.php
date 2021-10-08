<?php
$attrs = '';
$class = 'mbt-input-field';

if ( isset( $field['size'] ) && ! empty( $field['size'] ) ) {
	$attrs .= ' size="' . $field['size'] . '"';
}
if ( isset( $field['maxlength'] ) && ! empty( $field['maxlength'] ) ) {
	$attrs .= ' maxlength="' . $field['maxlength'] . '"';
}
if ( isset( $field['placeholder'] ) ) {
	$attrs .= ' placeholder="' . $field['placeholder'] . '"';
}
if ( isset( $field['readonly'] ) && $field['readonly'] ) {
	$attrs .= ' readonly="readonly"';
}
if ( isset( $field['disabled'] ) && $field['disabled'] ) {
	$attrs .= ' disabled="disabled"';
}
if ( isset( $field['clickselect'] ) && $field['clickselect'] ) {
	$attrs .= ' onclick="this.select()"';
}
if ( isset( $field['class'] ) && ! empty( $field['class'] ) ) {
	$class .= ' ' . $field['class'];
}
// Required.
if ( isset( $field['required'] ) && $field['required'] ) {
	$class .= ' mbt-required-field';
}
?>
<div class="mbt-field-datetime">
	<span>
		<input type="date" id="<?php echo $id; ?>[date]" name="<?php echo $id; ?>[date]" class="<?php echo $class; ?>" value="<?php echo isset( $value['date'] ) ? $value['date'] : ''; ?>"<?php echo $attrs; ?> />
	</span>
	<span>
		<input type="time" id="<?php echo $id; ?>[time]" name="<?php echo $id; ?>[time]" class="<?php echo $class; ?>" value="<?php echo isset( $value['time'] ) ? $value['time'] : ''; ?>"<?php echo $attrs; ?> />
	</span>
</div>