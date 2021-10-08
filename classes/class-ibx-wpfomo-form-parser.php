<?php
/**
 * Form Parser class.
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'ibx_simple_html_dom' ) && ! class_exists( 'ibx_simple_html_dom_node' ) ) {
	require_once IBX_WPFOMO_DIR . 'includes/dom-parser.php';
}

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'IBX_WPFomo_Form_Parser' ) ) {

	final class IBX_WPFomo_Form_Parser {
		/**
		 * Holds error messages.
		 *
		 * @since 1.0.0
		 * @var array $errors
		 */
		public static $errors = array();

		/**
		 * Holds allowed input types.
		 *
		 * @since 1.0.0
		 * @var array $allowed_input_types
		 */
		public static $allowed_input_types = array( 'text', 'number', 'email', 'tel', 'date', 'submit', 'hidden', 'button' );

		/**
		 * Holds parsed forms details.
		 *
		 * @since 1.0.0
		 * @var array $forms
		 */
		public static $forms = array();

		/**
		 * Holds Url to be parsed.
		 *
		 * @since 1.0.0
		 * @var array $forms
		 */
		public static $url = '';

		/**
		 * Holds form classes to be parsed.
		 *
		 * @since 1.0.0
		 * @var array $form_classes_array
		 */
		public static $form_classes_array = array( 'pp-subscribe-form', 'fl-subscribe-form' );

		/**
		 * Parse url DOM.
		 *
		 * @param string $url for html url to be parsed.
		 * @param string $src for form source inline or embedded.
		 * @since 1.0.0
		 * @return array
		 */
		public static function parse_dom( $url, $src = 'inline' ) {
			self::$url = $url;
			if ( ! empty( $url ) ) {
				$html   = file_get_html( $url );
				$postid = url_to_postid( $url );
				foreach ( $html->find( 'form' ) as $f ) {

					if ( ! empty( $f->name ) ) {
						// json key is not taking hyphen.
						$formkey              = 'ibx_frm_' . str_ireplace( '-', '_', $f->name );
						$form_unique_key      = $f->name;
						$form_unique_key_attr = 'name';
					} elseif ( ! empty( $f->id ) ) {
						$formkey              = 'ibx_frm_' . str_ireplace( '-', '_', $f->id );
						$form_unique_key      = $f->id;
						$form_unique_key_attr = 'id';
					} else {
						continue;
					}
					self::$forms[ $formkey ]['name']                 = $f->name;
					self::$forms[ $formkey ]['action']               = $f->action;
					self::$forms[ $formkey ]['id']                   = $f->id;
					self::$forms[ $formkey ]['form_unique_key']      = $form_unique_key;
					self::$forms[ $formkey ]['form_unique_key_attr'] = $form_unique_key_attr;
					self::$forms[ $formkey ]['form_src']             = $src;
					self::$forms[ $formkey ]['form_src_post']        = $postid;

					// to get all fields.
					foreach ( $f->find( 'input' ) as $input ) {
						if ( in_array( $input->type, self::$allowed_input_types, true ) ) {

							if ( ! empty( $input->name ) ) {
								$input_unique_key      = $input->name;
								$input_unique_key_attr = 'name';
							} elseif ( ! empty( $input->id ) ) {
								$input_unique_key      = $input->id;
								$input_unique_key_attr = 'id';
							} else {
								continue;
							}

							self::$forms[ $formkey ]['inputs'][ $input_unique_key ]['name']                  = $input->name;
							self::$forms[ $formkey ]['inputs'][ $input_unique_key ]['id']                    = $input->id;
							self::$forms[ $formkey ]['inputs'][ $input_unique_key ]['type']                  = $input->type;
							self::$forms[ $formkey ]['inputs'][ $input_unique_key ]['input_unique_key']      = $input_unique_key;
							self::$forms[ $formkey ]['inputs'][ $input_unique_key ]['input_unique_key_attr'] = $input_unique_key_attr;
						}
					}
				} // End foreach().

				$html->clear();
				unset( $html );
			} // End if().

			return self::$forms;
		}

		/**
		 * Get css selector.
		 *
		 * @param string $node
		 * @param string $p_node
		 * @since 1.0.0
		 * @return string
		 */
		public static function get_full_css_selector( $node, $p_node ) {
			$node_id      = self::get_node_id( $node );
			$node_classes = self::get_node_classes( $node );

			// @codingStandardsIgnoreStart.
			if ( ! empty( $node_id ) ) {
				return $node->nodeName . '#' . $node_id;
			} elseif ( 'body' === $node->nodeName || $node == $p_node ) {
				if ( $node_classes !== '' && ( 'form' == strtolower( $node->nodeName ) || ( 0 < count( array_intersect( array_map( 'strtolower', explode( '.', $node_classes ) ), self::$form_classes_array ) ) ) ) ) {
					return $node->nodeName . '.' . $node_classes;
				}
				return $node->nodeName;
			} else {
				if ( $node_classes !== '' && ( 'form' == strtolower( $node->nodeName ) || ( 0 < count( array_intersect( array_map( 'strtolower', explode( '.', $node_classes ) ), self::$form_classes_array ) ) ) ) ) {
					return self::get_full_css_selector( $node->parentNode, $p_node ) . '>' . $node->nodeName . '.' . $node_classes;
				}

				return self::get_full_css_selector( $node->parentNode, $p_node ) . '>' . $node->nodeName;
			}
			// @codingStandardsIgnoreEnd.
		}

		/**
		 * Get Node Id.
		 *
		 * @param string $node
		 * @since 1.0.0
		 * @return string
		 */
		public static function get_node_id( $node ) {
			$node_id    = '';
			$node_attrs = $node->attributes;
			foreach ( $node_attrs as $node_attr ) {
				// @codingStandardsIgnoreStart.
				if ( 'id' === $node_attr->nodeName ) {
					$node_id = $node_attr->nodeValue;
				}
				// @codingStandardsIgnoreEnd.
			}
			return $node_id;
		}

		/**
		 * Get Node Class.
		 *
		 * @param string $node
		 * @since 1.0.0
		 * @return string
		 */
		public static function get_node_classes( $node ) {
			$node_classes = '';
			$node_attrs   = $node->attributes;

			foreach ( $node_attrs as $node_attr ) {
				// @codingStandardsIgnoreStart.
				if ( 'class' === $node_attr->nodeName ) {
					$node_classes = str_replace( ' ', '.', trim( $node_attr->nodeValue ) );
				}
				// @codingStandardsIgnoreEnd.
			}
			return $node_classes;
		}

		/**
		 * Parse Forms.
		 *
		 * @param string $capture_url
		 * @since 1.0.0
		 * @return array
		 */

		public static function parse_forms( $capture_url ) {
			$postid            = url_to_postid( $capture_url );
			$forms_data        = array();
			$div_classes_query = '';
			$page_html         = @file_get_contents( $capture_url );

			if ( false === $page_html ) {
				$curl = curl_init( $capture_url );
				curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, 2 );
				curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
				curl_setopt( $curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36' );

				$page_html = curl_exec( $curl );
				if ( ! $page_html ) {
					return false;
				}
			}

			$dom = new DomDocument();
			@$dom->loadHTML( $page_html );

			$xpath = new DomXPath( $dom );

			foreach ( self::$form_classes_array as $form_class ) {
				if ( empty( $div_classes_query ) ) {
					$div_classes_query = 'contains(concat(" ", normalize-space(@class), " "), " ' . $form_class . ' ")';
				} else {
					$div_classes_query .= ' or contains(concat(" ", normalize-space(@class), " "), " ' . $form_class . ' ")';
				}
			}

			if ( ! empty( $div_classes_query ) ) {
				$forms = $xpath->query( './/form|.//*[' . $div_classes_query . ']' );
			} else {
				$forms = $xpath->query( './/form' );
			}

			foreach ( $forms as $form ) {
				$form_fields_data = array();
				$form_fields      = $xpath->query( './/input[@type="text" or @type="email"]', $form );
				foreach ( $form_fields as $form_field ) {
					$form_fields_data[] = array(
						'id'       => $form_field->getAttribute( 'id' ),
						'name'     => $form_field->getAttribute( 'name' ),
						'value'    => $form_field->getAttribute( 'name' ),
						'type'     => $form_field->getAttribute( 'type' ),
						'class'    => $form_field->getAttribute( 'class' ),
						'selector' => self::get_full_css_selector( $form_field, $form ),
					);
				}

				if ( count( $form_fields_data ) > 0 ) {
					$form_unique_key = self::get_full_css_selector( $form, $xpath->query( './/body' )->item( 1 ) );
					$forms_data[]    = array(
						'id'              => $form->getAttribute( 'id' ),
						'name'            => $form->getAttribute( 'name' ),
						'class'           => $form->getAttribute( 'class' ),
						'action'          => $form->getAttribute( 'action' ),
						'method'          => $form->getAttribute( 'method' ),
						'selector'        => $form_unique_key,
						'form_unique_key' => $form_unique_key,
						'form_src_post'   => $postid,
						'inputs'          => $form_fields_data,
					);

				}
			}

			// search Ninja forms
			if ( function_exists( 'Ninja_Forms' ) ) {
				$exclude_field_types = array(
					'starrating',
					'recaptcha',
					'spam',
					'hidden',
					'confirm',
					'html',
					'total',
					'liststate',
					'submit',
					'listselect',
					'listradio',
					'listmultiselect',
					'listcheckbox',
					'date',
					'checkbox',
					'listcountry',
					'number',
					'hr',
					'total',
					'product',
					'shipping',
					'quantity',
				);

				$forms = $xpath->query( './/div[@role="form"]' );
				foreach ( $forms as $form ) {
					$form_fields_data = array();

					$form_id = preg_replace( '/[^0-9]/', '', $form->getAttribute( 'id' ) );
					if ( ! $form_id ) {
						continue;
					}

					$form_fields = Ninja_Forms()->form( $form_id )->get_fields();
					foreach ( $form_fields as $form_field ) {
						$field_type = $form_field->get_setting( 'type' );
						if ( in_array( $field_type, $exclude_field_types ) ) {
							continue;
						}
						$field_id           = $form_field->get_id();
						$form_fields_data[] = array(
							'id'    => $field_id,
							'name'  => $form_field->get_setting( 'label' ),
							'value' => $field_id,
							'type'  => '',
							'class' => '',
						);
					}
					$form_unique_key = self::get_full_css_selector( $form, $xpath->query( './/body' )->item( 1 ) );
					if ( count( $form_fields_data ) > 0 ) {
						$forms_data[] = array(
							'id'              => $form->getAttribute( 'id' ),
							'name'            => $form->getAttribute( 'name' ),
							'class'           => $form->getAttribute( 'class' ),
							'action'          => $form->getAttribute( 'action' ),
							'method'          => $form->getAttribute( 'method' ),
							'selector'        => $form_unique_key,
							'form_unique_key' => $form_unique_key,
							'form_src_post'   => $postid,
							'inputs'          => $form_fields_data,
						);
					}
				} // End foreach().
			} // End if().

			return $forms_data;
		}

	}
} // End if().
