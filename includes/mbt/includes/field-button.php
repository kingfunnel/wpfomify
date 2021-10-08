<?php
$button_text = isset( $field['text'] ) ? $field['text'] : $field['label'];

$attr = '';

if ( isset( $field['url'] ) ) {
	$attr .= ' href="' . $field['url'] . '"';

	if ( isset( $field['target'] ) ) {
		$attr .= ' target="' . $field['target'] . '"';
	}
	if ( isset( $field['download'] ) ) {
		$attr .= ' download="' . $field['download'] . '"';
	}
} else {
	$attr .= ' href="javascript:void(0);"';
	$attr .= ' onclick="return false;"';
}

?>
<a class="button mbt-field-button<?php echo ( isset( $field['class'] ) ) ? ' ' . $field['class'] : ''; ?>"<?php echo $attr; ?>><?php echo $button_text; ?></a>
<span class="mbt-loader"><img src="<?php echo admin_url( 'images/spinner.gif' ); ?>" /></span>
