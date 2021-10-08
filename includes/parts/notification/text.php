<?php
$tags = array();
$title = '';//'bienvenue dans\' éàkè';

if ( ! empty( $value['title'] ) && ! is_array( $template ) ) {
	$title = '<span class="ibx-notification-popup-title">' . htmlspecialchars( $value['title'] ) . '</span>';
}

foreach ( $value as $tag => $val ) {
	$tags[ '{{' . $tag . '}}' ] = ! is_array( $val ) ? htmlspecialchars( $val ) : $val;
	if ( 'title' == $tag && ! empty( $title ) ) {
		$tags[ '{{' . $tag . '}}' ] = $title;
	}
	if ( is_array( $template ) ) {
		if ( 'time' == $tag ) {
			if ( is_array( $val ) ) {
				$time = $val['date'] . ' ' . $val['time'];
				$tags['{{time}}'] = IBX_WPFomo_Helper::get_timeago_html( $time );
			} else {
				$tags['{{time}}'] = strip_tags( htmlspecialchars_decode( $val ) );
			}
		}
	}
}

if ( 'conversion' == $type ) {

	if ( ! empty( $value['email'] ) && in_array( trim( $value['email'] ), $exclude_emails ) ) {
		$tags['{{name}}'] = IBX_WPFomo_Helper::get_someone_translation();
	}
	if ( ! isset( $value['name'] ) || empty( trim( $value['name'] ) ) ) {
		$tags['{{name}}'] = IBX_WPFomo_Helper::get_someone_translation();
	}
}

if ( 'reviews' == $type ) {

	$rating = '';
	$review_name = '<span class="ibx-notification-popup-review-name">' . $value['name'] . '</span>';

	if ( isset( $value['rating'] ) && '' != $value['rating'] ) {
		$stars = IBX_WPFomo_Helper::get_rating_stars( $value['rating'] );
		$rating = '<div class="ibx-notification-popup-rating">' . $stars . '</div>';
	}

	$tags['{{name}}'] = $review_name;
	$tags['{{rating}}'] = $rating;
}

do_action( 'ibx_wpfomo_notification_content', $settings );

echo IBX_WPFomo_Helper::get_notification_template( $template, $tags );
