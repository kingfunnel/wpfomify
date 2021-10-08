<?php

class IBX_WPFomo_GForms_Helper {

	static public function get_forms_list()
	{
		if ( class_exists( 'GFForms' ) ) {
			$options = array();
			$forms = RGFormsModel::get_forms( null, 'title' );
			if ( count( $forms ) ) {
				foreach ( $forms as $form )
				$options[$form->id] = $form->title;
			}
		}
		
		return $options;
	}

	static public function get_form_entries( $form_id, $total = null, $search_criteria = array() )
	{
		if ( empty( $form_id ) ) {
			return;
		}

		$entries = GFAPI::get_entries( $form_id, $search_criteria, null, null, $total );

		return $entries;
	}

	static public function get_form_fields( $form_id )
	{
		if ( empty( $form_id ) ) {
			return;
		}

		$options 	= array();
		$form 		= GFAPI::get_form( $form_id );
		$fields 	= $form['fields'];

		if ( empty( $fields ) ) {
			return $options;
		}

		foreach ( $fields as $field ) {
			$field_label = $field->label;

			if ( empty( $field_label ) ) {
				$field_label = ( isset( $field->adminLabel ) && ! empty( $field->adminLabel ) ) ? $field->adminLabel : $field->type;
			}
			
			if ( isset( $field['inputs'] ) ) {
				$sub_fields = $field['inputs'];
				foreach ( $sub_fields as $sub_field ) {
					if ( ! isset( $sub_field['isHidden'] ) || ! $sub_field['isHidden'] ) {
						$options[ $sub_field['id'] ] = $field_label . ' - ' . $sub_field['label'];
					}
				}
			} else {
				$options[$field->id] = $field_label;
			}
		}

		return $options;
	}
}