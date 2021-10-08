<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Logic to parse reviews HTML content.
 *
 * @since 2.1
 */
class IBX_WPFomo_WordPress_Reviews {
	/**
	 * Plugin reviews.
	 *
	 * @since  2.1
	 * @var    array
	 */
	protected $reviews = array();

	/**
	 * Primary class constructor.
	 *
	 * @param string $reviews HTML string.
	 */
	public function __construct( $reviews ) {
		$this->reviews = $reviews;
		$this->split_reviews();
	}

	/**
	 * Returns reviews array.
	 *
	 * @since 2.1
	 */
	public function get_reviews() {
		return $this->reviews;
	}

	/**
	 * Split the reviews.
	 *
	 * The reveiws we get from the plugin information are all contained
	 * in a text string with HTML markup. We need to split the reviews
	 * into individual elements in order to process them.
	 *
	 * @return array Splitted reviews
	 */
	protected function split_reviews() {
		$reviews       = $this->reviews;
		$this->reviews = array();
		$dom           = new DOMDocument();
		$dom->loadHTML( '<?xml encoding="UTF-8">' . $reviews );
		$finder        = new DomXPath( $dom );
		$nodes         = $finder->query( "//*[contains(concat(' ', normalize-space(@class), ' '), ' review ')]" );
		foreach ( $nodes as $node ) {
			$raw_review = $node->ownerDocument->saveXML( $node ); //@codingStandardsIgnoreLine.
			$review     = $this->extract_review_data( $raw_review );
			array_push( $this->reviews, $review );
		}
		return $this->reviews;
	}

	/**
	 * Get the content of an HTML node.
	 *
	 * Get the content of an HTML node using DOMDocument.
	 * We are searching the node by its class.
	 *
	 * @param  string $html  HTML string to get the content from
	 * @param  string $class Class of the node to get the content from
	 * @return string        Node content
	 */
	protected function get_node_content( $html, $class ) {
		$dom     = new DOMDocument();
		$dom->loadHTML( $html );
		$finder  = new DomXPath( $dom );
		$nodes   = $finder->query( "//*[contains(concat(' ', normalize-space(@class), ' '), ' $class ')]" );
		$content = '';

		foreach ( $nodes as $element ) {
			$content = $element->ownerDocument->saveXML( $element ); //@codingStandardsIgnoreLine.
		}

		return trim( strip_tags( $content ) );
	}

	/**
	 * Get the content of an HTML node.
	 *
	 * Get the content of an HTML node using DOMDocument.
	 * We are searching the node by its class.
	 *
	 * @param  string $html  HTML string to get the content from
	 * @param  string $class Class of the node to get the content from
	 * @return string        Node content
	 */
	protected function get_rating_content( $html, $class ) {
		$dom     = new DOMDocument();
		$dom->loadHTML( $html );
		$finder  = new DomXPath( $dom );
		$nodes   = $finder->query( "//*[contains(concat(' ', normalize-space(@class), ' '), ' $class ')]" );
		$content = '';
		foreach ( $nodes as $element ) {
			$content = $element->getAttribute('data-rating'); //@codingStandardsIgnoreLine.
		}
		return trim( strip_tags( $content ) );
	}

	/**
	 * Construct review array.
	 *
	 * Prepare the review in its final form.
	 * We extract ll required information and store them
	 * in a re-usable array.
	 *
	 * @param  string $review An individual formatted review
	 * @return array          Review data
	 */
	protected function extract_review_data( $review ) {
		$data  = array();
		$links = self::get_links( $review, true );
		$data['username']['text'] = isset( $links[1] ) ? $links[1] : '';
		$data['username']['href'] = self::get_link_href( $review );
		$data['avatar']['src']    = self::get_image_src( $review );
		$data['content']          = iconv( 'UTF-8', 'ISO-8859-1', self::get_node_content( $review, 'review-body' ) );
		$data['title']            = iconv( 'UTF-8', 'ISO-8859-1', self::get_tag_content( $review, 'h4' ) );
		$data['date']             = iconv( 'UTF-8', 'ISO-8859-1', self::get_node_content( $review, 'review-date' ) );
		$data['rating']           = self::get_rating_content( $review, 'wporg-ratings' );

		return $data;
	}

	/**
	 * Get the source of an image using DOMDocument.
	 *
	 * @param  string $html String containing an image tag
	 * @return string       Image source
	 */
	protected function get_image_src( $html ) {
		$doc        = new DOMDocument();
		$doc->loadHTML( $html );
		$imagepaths = array();
		$imagetags  = $doc->getElementsByTagName( 'img' );
		foreach ( $imagetags as $tag ) {
			$imagepaths[] = $tag->getAttribute( 'src' );
		}
		if ( ! empty( $imagepaths ) ) {
			return $imagepaths[0];
		} else {
			return '';
		}
	}

	/**
	 * Get the target of a link.
	 *
	 * @param  string $html String containing a link tag
	 * @return string       Link target
	 */
	protected function get_link_href( $html ) {
		$doc       = new DOMDocument();
		$doc->loadHTML( $html );
		$linkhrefs = array();
		$linktags  = $doc->getElementsByTagName( 'a' );
		foreach ( $linktags as $tag ) {
			$linkhrefs[] = $tag->getAttribute( 'href' );
		}
		if ( ! empty( $linkhrefs ) ) {
			return $linkhrefs[0];
		} else {
			return '';
		}
	}

	/**
	 * Get all links from a string.
	 *
	 * @param  string  $html       HTML string
	 * @param  boolean $strip_tags Whether of not to strip the tags and only retrieve the link anchor
	 * @return array               All links contained in the string
	 */
	protected function get_links( $html, $strip_tags = false ) {
		$links = array();
		$doc       = new DOMDocument();
		$doc->loadHTML( $html );
		$linktags  = $doc->getElementsByTagName( 'a' );
		foreach ( $linktags as $tag ) {
			if ( $strip_tags ) {
				$links[] = trim( strip_tags( $tag->ownerDocument->saveXML( $tag ) ) ); //@codingStandardsIgnoreLine.
			} else {
				$links[] = $tag->ownerDocument->saveXML( $tag ); //@codingStandardsIgnoreLine.
			}
		}
		return $links;
	}

	/**
	 * Get the content of any HTML tag.
	 *
	 * @param  string $html   HTML string to parse
	 * @param  string $search Tag to search
	 * @return string         Tag content
	 */
	protected function get_tag_content( $html, $search ) {
		$doc        = new DOMDocument();
		$doc->loadHTML( $html );
		$titlepaths = array();
		$titletags  = $doc->getElementsByTagName( $search );
		foreach ( $titletags as $tag ) {
			$titlepaths[] = $tag->ownerDocument->saveXML( $tag ); //@codingStandardsIgnoreLine.
		}
		if ( ! empty( $titlepaths ) ) {
			return trim( strip_tags( $titlepaths[0] ) );
		} else {
			return '';
		}
	}
}
