<?php
$editor_settings = array(
	'textarea_rows' => isset( $field['rows'] ) ? $field['rows'] : 10,
	'wpautop'       => true,
);

if ( isset( $field['teeny'] ) ) {
	$editor_settings['teeny'] = $field['teeny'];
}
if ( isset( $field['media_buttons'] ) ) {
	$editor_settings['media_buttons'] = $field['media_buttons'];
}
if ( isset( $field['drag_drop_upload'] ) ) {
	$editor_settings['drag_drop_upload'] = $field['drag_drop_upload'];
}

wp_editor( $value, $id, $editor_settings );
