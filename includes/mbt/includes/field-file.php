<?php
$attrs = '';
$class = 'mbt-input-field';
$action = isset( $field['action'] ) && ! empty( $field['action'] ) ? sanitize_text_field( $field['action'] ) : '';
$accept = isset( $field['accept'] ) && ! empty( $field['accept'] ) ? sanitize_text_field( $field['accept'] ) : '';

if ( empty( $action ) ) {
	echo '<p style="color: red;">' . esc_html__( 'File input requires ajax "action" attribute.', 'mbt' ) . '</p>';
	return;
}
if ( empty( $accept ) ) {
	echo '<p style="color: red;">' . esc_html__( 'File input requires "accept" attribute.', 'mbt' ) . '</p>';
	return;
}

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
if ( isset( $field['accept'] ) && $field['accept'] ) {
	$attrs .= ' accept="' . $field['accept'] . '"';
}
?>
<div class="mbt-file-input">
	<input type="file" id="<?php echo $id; ?>" name="<?php echo $id; ?>" class="<?php echo $class; ?>" value="<?php echo $value; ?>"<?php echo $attrs; ?> />
	<div class="mbt-file-action" style="display:inline-block;">
		<p class="hide-if-no-js">
			<a class="button mbt-upload-file" href="javascript:void(0);" data-field="<?php echo $id; ?>" data-action="<?php echo $action; ?>">
			<?php echo ( isset( $field['text'] ) && isset( $field['text']['upload'] ) ) ? $field['text']['upload'] : esc_html__( 'Upload', 'mbt' ); ?>
			</a>
			<span class="mbt-loader" style="display: none;"><img src="<?php echo admin_url( 'images/spinner.gif' ); ?>" /></span>
		</p>
	</div>		
</div>
<div class="mbt-file-message" style='padding:10px 0px;'></div>
