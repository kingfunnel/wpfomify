<?php

if ( is_object( $value ) ) {
	$value = (array) $value;
}

$group_fields   = $field['fields'];
$group_title    = isset( $field['title'] ) ? $field['title'] : '';
$group_field_info = array();
?>
<div class="mbt-fields-group-wrapper">

	<script type="text/html" class="mbt-fields-group-template">
		<div class="mbt-fields-group" data-group-id="1" data-group-title="<?php echo $group_title; ?>" data-field-name="<?php echo $name; ?>">
			<h4 class="mbt-fields-group-title">
				<span class="mbt-group-field-title-text"><?php echo $group_title . ' 1'; ?></span>
				<span class="mbt-group-field-unsaved"> - <?php esc_html_e( 'Unsaved', 'mbt' ); ?></span>
				<div class="mbt-group-field-controls">
					<a href="javascript:void(0)" class="mbt-fields-group-clone" title="<?php esc_html_e( 'Duplicate', 'mbt' ); ?>" data-clone-group="1">
						<svg aria-hidden="true" data-prefix="far" data-icon="clone" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M464 0H144c-26.51 0-48 21.49-48 48v48H48c-26.51 0-48 21.49-48 48v320c0 26.51 21.49 48 48 48h320c26.51 0 48-21.49 48-48v-48h48c26.51 0 48-21.49 48-48V48c0-26.51-21.49-48-48-48zM362 464H54a6 6 0 0 1-6-6V150a6 6 0 0 1 6-6h42v224c0 26.51 21.49 48 48 48h224v42a6 6 0 0 1-6 6zm96-96H150a6 6 0 0 1-6-6V54a6 6 0 0 1 6-6h308a6 6 0 0 1 6 6v308a6 6 0 0 1-6 6z" class=""></path></svg>
					</a>
					<a href="javascript:void(0)" class="mbt-fields-group-remove" title="<?php esc_html_e( 'Remove', 'mbt' ); ?>" data-remove-group="1">
						<svg aria-hidden="true" data-prefix="far" data-icon="times" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="currentColor" d="M231.6 256l130.1-130.1c4.7-4.7 4.7-12.3 0-17l-22.6-22.6c-4.7-4.7-12.3-4.7-17 0L192 216.4 61.9 86.3c-4.7-4.7-12.3-4.7-17 0l-22.6 22.6c-4.7 4.7-4.7 12.3 0 17L152.4 256 22.3 386.1c-4.7 4.7-4.7 12.3 0 17l22.6 22.6c4.7 4.7 12.3 4.7 17 0L192 295.6l130.1 130.1c4.7 4.7 12.3 4.7 17 0l22.6-22.6c4.7-4.7 4.7-12.3 0-17L231.6 256z" class=""></path></svg>
					</a>
				</div>
			</h4>
			<div class="mbt-fields-group-inner">
				<table class="mbt-metabox-form-table form-table">
					<?php
					foreach ( $group_fields as $field_name => $field_data ) : // Fields

						$group_field_name = $name . '[1]' . '[' . $field_name . ']';

						$group_field_info['group_field'] = $name;
						$group_field_info['group_sub_fields'][] = array(
							'field_name'    => $group_field_name,
							'original_name' => $field_name,
						);

						MetaBox_Tabs::render_metabox_field( $group_field_name, $field_data );

					endforeach;
					?>
				</table>
				<div class="mbt-fields-group-info" data-info="<?php echo esc_attr( json_encode( $group_field_info ) ); ?>"></div>
				<div class="mbt-fields-group-footer wp-clearfix">
					<div class="mbt-fields-group-order">
						<a href="javascript:void(0)" class="button mbt-fields-group-up"><span class="dashicons dashicons-arrow-up-alt2"></span></a>
						<a href="javascript:void(0)" class="button mbt-fields-group-down"><span class="dashicons dashicons-arrow-down-alt2"></span></a>
					</div>
					<div class="mbt-fields-group-remove">
						<a href="javascript:void(0)" class="button mbt-fields-group-remove" title="<?php esc_html_e( 'Remove', 'mbt' ); ?>" data-remove-group="1">
							<svg aria-hidden="true" data-prefix="far" data-icon="times" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="currentColor" d="M231.6 256l130.1-130.1c4.7-4.7 4.7-12.3 0-17l-22.6-22.6c-4.7-4.7-12.3-4.7-17 0L192 216.4 61.9 86.3c-4.7-4.7-12.3-4.7-17 0l-22.6 22.6c-4.7 4.7-4.7 12.3 0 17L152.4 256 22.3 386.1c-4.7 4.7-4.7 12.3 0 17l22.6 22.6c4.7 4.7 12.3 4.7 17 0L192 295.6l130.1 130.1c4.7 4.7 12.3 4.7 17 0l22.6-22.6c4.7-4.7 4.7-12.3 0-17L231.6 256z" class=""></path></svg>
						</a>
					</div>
					<div class="mbt-fields-group-clone">
						<a href="javascript:void(0)" class="button mbt-fields-group-clone" title="<?php esc_html_e( 'Duplicate', 'mbt' ); ?>" data-clone-group="1">
							<svg aria-hidden="true" data-prefix="far" data-icon="clone" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M464 0H144c-26.51 0-48 21.49-48 48v48H48c-26.51 0-48 21.49-48 48v320c0 26.51 21.49 48 48 48h320c26.51 0 48-21.49 48-48v-48h48c26.51 0 48-21.49 48-48V48c0-26.51-21.49-48-48-48zM362 464H54a6 6 0 0 1-6-6V150a6 6 0 0 1 6-6h42v224c0 26.51 21.49 48 48 48h224v42a6 6 0 0 1-6 6zm96-96H150a6 6 0 0 1-6-6V54a6 6 0 0 1 6-6h308a6 6 0 0 1 6 6v308a6 6 0 0 1-6 6z" class=""></path></svg>
						</a>
					</div>
				</div>
			</div>
		</div>
	</script>

	<?php if ( ! is_array( $value ) ) :
		$group_field_info = array(); ?>
		<div class="mbt-fields-group" data-group-id="1" data-group-title="<?php echo $group_title; ?>" data-field-name="<?php echo $name; ?>">
			<h4 class="mbt-fields-group-title">
				<span class="mbt-group-field-title-text"><?php echo $group_title . ' 1'; ?></span>
				<span class="mbt-group-field-unsaved"> - <?php esc_html_e( 'Unsaved', 'mbt' ); ?></span>
				<div class="mbt-group-field-controls">
					<a href="javascript:void(0)" class="mbt-fields-group-clone" title="<?php esc_html_e( 'Duplicate', 'mbt' ); ?>" data-clone-group="1">
						<svg aria-hidden="true" data-prefix="far" data-icon="clone" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M464 0H144c-26.51 0-48 21.49-48 48v48H48c-26.51 0-48 21.49-48 48v320c0 26.51 21.49 48 48 48h320c26.51 0 48-21.49 48-48v-48h48c26.51 0 48-21.49 48-48V48c0-26.51-21.49-48-48-48zM362 464H54a6 6 0 0 1-6-6V150a6 6 0 0 1 6-6h42v224c0 26.51 21.49 48 48 48h224v42a6 6 0 0 1-6 6zm96-96H150a6 6 0 0 1-6-6V54a6 6 0 0 1 6-6h308a6 6 0 0 1 6 6v308a6 6 0 0 1-6 6z" class=""></path></svg>
					</a>
					<a href="javascript:void(0)" class="mbt-fields-group-remove" title="<?php esc_html_e( 'Remove', 'mbt' ); ?>" data-remove-group="1">
						<svg aria-hidden="true" data-prefix="far" data-icon="times" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="currentColor" d="M231.6 256l130.1-130.1c4.7-4.7 4.7-12.3 0-17l-22.6-22.6c-4.7-4.7-12.3-4.7-17 0L192 216.4 61.9 86.3c-4.7-4.7-12.3-4.7-17 0l-22.6 22.6c-4.7 4.7-4.7 12.3 0 17L152.4 256 22.3 386.1c-4.7 4.7-4.7 12.3 0 17l22.6 22.6c4.7 4.7 12.3 4.7 17 0L192 295.6l130.1 130.1c4.7 4.7 12.3 4.7 17 0l22.6-22.6c4.7-4.7 4.7-12.3 0-17L231.6 256z" class=""></path></svg>
					</a>
				</div>
			</h4>
			<div class="mbt-fields-group-inner">
				<table class="mbt-metabox-form-table form-table">
					<?php
					foreach ( $group_fields as $field_name => $field_data ) : // Fields

						$group_field_name = $name . '[1]' . '[' . $field_name . ']';

						$group_field_info['group_field'] = $name;
						$group_field_info['group_sub_fields'][] = array(
							'field_name'    => $group_field_name,
							'original_name' => $field_name,
						);

						MetaBox_Tabs::render_metabox_field( $group_field_name, $field_data );

					endforeach;
					?>
				</table>
				<div class="mbt-fields-group-info" data-info="<?php echo esc_attr( json_encode( $group_field_info ) ); ?>"></div>
				<div class="mbt-fields-group-footer wp-clearfix">
					<div class="mbt-fields-group-order">
						<a href="javascript:void(0)" class="button mbt-fields-group-up"><span class="dashicons dashicons-arrow-up-alt2"></span></a>
						<a href="javascript:void(0)" class="button mbt-fields-group-down"><span class="dashicons dashicons-arrow-down-alt2"></span></a>
					</div>
					<div class="mbt-fields-group-remove">
						<a href="javascript:void(0)" class="button mbt-fields-group-remove" title="<?php esc_html_e( 'Remove', 'mbt' ); ?>" data-remove-group="1">
							<svg aria-hidden="true" data-prefix="far" data-icon="times" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="currentColor" d="M231.6 256l130.1-130.1c4.7-4.7 4.7-12.3 0-17l-22.6-22.6c-4.7-4.7-12.3-4.7-17 0L192 216.4 61.9 86.3c-4.7-4.7-12.3-4.7-17 0l-22.6 22.6c-4.7 4.7-4.7 12.3 0 17L152.4 256 22.3 386.1c-4.7 4.7-4.7 12.3 0 17l22.6 22.6c4.7 4.7 12.3 4.7 17 0L192 295.6l130.1 130.1c4.7 4.7 12.3 4.7 17 0l22.6-22.6c4.7-4.7 4.7-12.3 0-17L231.6 256z" class=""></path></svg>
						</a>
					</div>
					<div class="mbt-fields-group-clone">
						<a href="javascript:void(0)" class="button mbt-fields-group-clone" title="<?php esc_html_e( 'Duplicate', 'mbt' ); ?>" data-clone-group="1">
							<svg aria-hidden="true" data-prefix="far" data-icon="clone" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M464 0H144c-26.51 0-48 21.49-48 48v48H48c-26.51 0-48 21.49-48 48v320c0 26.51 21.49 48 48 48h320c26.51 0 48-21.49 48-48v-48h48c26.51 0 48-21.49 48-48V48c0-26.51-21.49-48-48-48zM362 464H54a6 6 0 0 1-6-6V150a6 6 0 0 1 6-6h42v224c0 26.51 21.49 48 48 48h224v42a6 6 0 0 1-6 6zm96-96H150a6 6 0 0 1-6-6V54a6 6 0 0 1 6-6h308a6 6 0 0 1 6 6v308a6 6 0 0 1-6 6z" class=""></path></svg>
						</a>
					</div>
				</div>
			</div>
		</div>
	<?php else : ?>
		<?php foreach ( $value as $group_id => $field_value ) :
			$group_field_info = array(); ?>
			<div class="mbt-fields-group" data-group-id="<?php echo $group_id; ?>" data-group-title="<?php echo $group_title; ?>" data-field-name="<?php echo $name; ?>">
				<h4 class="mbt-fields-group-title">
					<span class="mbt-group-field-title-text"><?php echo $group_title . ' ' . $group_id; ?></span>
					<span class="mbt-group-field-unsaved"> - <?php esc_html_e( 'Unsaved', 'mbt' ); ?></span>
					<div class="mbt-group-field-controls">
						<a href="javascript:void(0)" class="mbt-fields-group-clone" title="<?php esc_html_e( 'Duplicate', 'mbt' ); ?>" data-clone-group="<?php echo $group_id; ?>">
							<svg aria-hidden="true" data-prefix="far" data-icon="clone" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M464 0H144c-26.51 0-48 21.49-48 48v48H48c-26.51 0-48 21.49-48 48v320c0 26.51 21.49 48 48 48h320c26.51 0 48-21.49 48-48v-48h48c26.51 0 48-21.49 48-48V48c0-26.51-21.49-48-48-48zM362 464H54a6 6 0 0 1-6-6V150a6 6 0 0 1 6-6h42v224c0 26.51 21.49 48 48 48h224v42a6 6 0 0 1-6 6zm96-96H150a6 6 0 0 1-6-6V54a6 6 0 0 1 6-6h308a6 6 0 0 1 6 6v308a6 6 0 0 1-6 6z" class=""></path></svg>
						</a>
						<a href="javascript:void(0)" class="mbt-fields-group-remove" title="<?php esc_html_e( 'Remove', 'mbt' ); ?>" data-remove-group="<?php echo $group_id; ?>">
							<svg aria-hidden="true" data-prefix="far" data-icon="times" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="currentColor" d="M231.6 256l130.1-130.1c4.7-4.7 4.7-12.3 0-17l-22.6-22.6c-4.7-4.7-12.3-4.7-17 0L192 216.4 61.9 86.3c-4.7-4.7-12.3-4.7-17 0l-22.6 22.6c-4.7 4.7-4.7 12.3 0 17L152.4 256 22.3 386.1c-4.7 4.7-4.7 12.3 0 17l22.6 22.6c4.7 4.7 12.3 4.7 17 0L192 295.6l130.1 130.1c4.7 4.7 12.3 4.7 17 0l22.6-22.6c4.7-4.7 4.7-12.3 0-17L231.6 256z" class=""></path></svg>
						</a>
					</div>
				</h4>
				<div class="mbt-fields-group-inner">
					<table class="mbt-metabox-form-table form-table">
						<?php
						foreach ( $group_fields as $field_name => $field_data ) : // Fields

							$group_field_name = $name . '[' . $group_id . ']' . '[' . $field_name . ']';

							$group_field_value = isset( $field_value[ $field_name ] ) ? $field_value[ $field_name ] : '';

							$group_field_info['group_field'] = $name;
							$group_field_info['group_sub_fields'][] = array(
								'field_name'    => $group_field_name,
								'original_name' => $field_name,
							);

							MetaBox_Tabs::render_metabox_field( $group_field_name, $field_data, $group_field_value );

						endforeach;
						?>
					</table>
					<div class="mbt-fields-group-info" data-info="<?php echo esc_attr( json_encode( $group_field_info ) ); ?>"></div>
					<div class="mbt-fields-group-footer wp-clearfix">
						<div class="mbt-fields-group-order">
							<a href="javascript:void(0)" class="button mbt-fields-group-up"><span class="dashicons dashicons-arrow-up-alt2"></span></a>
							<a href="javascript:void(0)" class="button mbt-fields-group-down"><span class="dashicons dashicons-arrow-down-alt2"></span></a>
						</div>
						<div class="mbt-fields-group-remove">
							<a href="javascript:void(0)" class="button mbt-fields-group-remove" title="<?php esc_html_e( 'Remove', 'mbt' ); ?>" data-remove-group="<?php echo $group_id; ?>">
								<svg aria-hidden="true" data-prefix="far" data-icon="times" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="currentColor" d="M231.6 256l130.1-130.1c4.7-4.7 4.7-12.3 0-17l-22.6-22.6c-4.7-4.7-12.3-4.7-17 0L192 216.4 61.9 86.3c-4.7-4.7-12.3-4.7-17 0l-22.6 22.6c-4.7 4.7-4.7 12.3 0 17L152.4 256 22.3 386.1c-4.7 4.7-4.7 12.3 0 17l22.6 22.6c4.7 4.7 12.3 4.7 17 0L192 295.6l130.1 130.1c4.7 4.7 12.3 4.7 17 0l22.6-22.6c4.7-4.7 4.7-12.3 0-17L231.6 256z" class=""></path></svg>
							</a>
						</div>
						<div class="mbt-fields-group-clone">
							<a href="javascript:void(0)" class="button mbt-fields-group-clone" title="<?php esc_html_e( 'Duplicate', 'mbt' ); ?>" data-clone-group="<?php echo $group_id; ?>">
								<svg aria-hidden="true" data-prefix="far" data-icon="clone" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M464 0H144c-26.51 0-48 21.49-48 48v48H48c-26.51 0-48 21.49-48 48v320c0 26.51 21.49 48 48 48h320c26.51 0 48-21.49 48-48v-48h48c26.51 0 48-21.49 48-48V48c0-26.51-21.49-48-48-48zM362 464H54a6 6 0 0 1-6-6V150a6 6 0 0 1 6-6h42v224c0 26.51 21.49 48 48 48h224v42a6 6 0 0 1-6 6zm96-96H150a6 6 0 0 1-6-6V54a6 6 0 0 1 6-6h308a6 6 0 0 1 6 6v308a6 6 0 0 1-6 6z" class=""></path></svg>
							</a>
						</div>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
	<div class="mbt-fields-group-action">
		<a href="javascript:void(0)" class="button button-primary mbt-fields-group-add"><?php esc_html_e( 'Add New', 'mbt' ); ?></a>
	</div>
</div>
