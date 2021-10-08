<style>
.ibx-wpfomo-clear:before,
.ibx-wpfomo-clear:after {
    content: " ";
    display: table;
}
.ibx-wpfomo-clear:after {
    clear: both;
}
.ibx-wpfomo-floating-button-wrap,
.ibx-wpfomo-floating-button-wrap * {
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
}
.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-button-inner {
    position: fixed;
    bottom: 25px;
    right: 25px;
    width: 60px;
    height: 60px;
	<?php if ( ! empty( $settings->fb_button_bg_color ) ) { ?>
		background: <?php echo $settings->fb_button_bg_color; ?>;
	<?php } ?>
	<?php if ( $settings->border >= 0 ) { ?>
		border: <?php echo $settings->border; ?>px solid <?php echo $settings->border_color; ?>;
	<?php } ?>
	border-radius: 100%;
	cursor: pointer;
	z-index: <?php echo is_admin() ? 99999 : 999999; ?>;
	<?php
		$shadow_opacity = ! empty( $settings->shadow_opacity ) ? ($settings->shadow_opacity / 100) : 0;
		$shadow_color = IBX_WPFomo_Helper::hex2rgba( $settings->shadow_color, $shadow_opacity );
	?>
	<?php echo IBX_WPFomo_Helper::render_box_shadow_css( '1px', '4px', $settings->shadow_blur . 'px', $settings->shadow_spread . 'px', $shadow_color ); ?>
}
.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-button {
	height: 100%;
	width: 100%;
	border-radius: 60px;
	display: inline-block;
	overflow: hidden;
	text-align: center;
	position: relative;
	user-select: none;
}
.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-button,
.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-button span {
	color: <?php echo ( ! empty( $settings->fb_button_text_color ) ) ? $settings->fb_button_text_color : '#fff'; ?>;
}
.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-button span {
	font-size: 27px;
	line-height: 60px;
	height: 100%;
	width: 100%;
	margin: auto;
	z-index: 1111;
}
.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-button img {
	max-width: 100%;
}
.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-button-icon,
.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-button-close {
	position: absolute;
	width: 100%;
	transform-style: flat;
	transform-origin: center;
	-webkit-transition: all 0.3s ease;
	-moz-transition: all 0.3s ease;
	transition: all 0.3s ease;
}
.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-button-icon {
	opacity: 1;
	transform: rotate(0deg);
}
.ibx-wpfomo-floating-button-wrap.ibx-wpfomo-floating-popup-active .ibx-wpfomo-floating-button-icon {
	opacity: 0;
	transform: rotate(25deg);
}
.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-button-close {
	opacity: 0;
	font-family: Helvetica, sans-serif;
	font-weight: 300;
	transform: rotate(45deg);
}
.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-button-close span {
	line-height: 55px;
}
.ibx-wpfomo-floating-button-wrap.ibx-wpfomo-floating-popup-active .ibx-wpfomo-floating-button-close {
	opacity: 1;
	transform: rotate(0deg);
}
.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-button-msg {
	position: absolute;
	left: -105px;
	overflow: visible;
	top: 0;
	bottom: 0;
	margin: auto;
	width: 90px;
	box-shadow: 1px 1px 10px -1px #aaa;
	border-radius: 5px;
	padding: 10px 15px;
	height: 40px;
	color: #333;
	line-height: 20px;
	background-color: #fff;
	transform-origin: 100% 100%;
	-webkit-transform-origin: 100% 100%;
	-moz-transform-origin: 100% 100%;
	-o-transform-origin: 100% 100%;
	-ms-transform-origin: 100% 100%;
	transform: scaleX(1);
	-webkit-transform: scaleX(1);
	-moz-transform: scaleX(1);
	-o-transform: scaleX(1);
	-ms-transform: scaleX(1);
}
.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-button-msg:after {
	display: inline-block;
	content: '';
	position: absolute;
	background-color: #fff;
	height: 10px;
	width: 10px;
	transform: rotate(45deg);
	-ms-transform: rotate(45deg);
	-webkit-transform: rotate(45deg);
	-o-transform: rotate(45deg);
	-moz-transform: rotate(45deg);
	top: 0;
	bottom: 0;
	margin: auto;
	right: -5px;
	border-radius: 0 3px 0 0;
	box-shadow: 1px -1px 4px 0 #eee;
}
.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-popup {
	position: fixed;
	display: none;
	bottom: 90px;
	right: 25px;
	opacity: 0;
	z-index: 999999999 !important;
	height: auto;
	width: 370px;
	background: #fff;
	border-radius: 5px;
	box-shadow: 1px 1px 10px -1px rgba(0,0,0,0.3);
	-webkit-font-smoothing: antialiased;
}
.ibx-wpfomo-floating-button-wrap.ibx-wpfomo-floating-popup-show .ibx-wpfomo-floating-popup {
	display: block;
	bottom: 100px;
	opacity: 1;
}
.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-popup-header {
	background: <?php echo $settings->fb_header_bg_color; ?>;
	color: <?php echo $settings->fb_header_text_color; ?>;
	border-top-left-radius: 5px;
	border-top-right-radius: 5px;
}
.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-popup-header p {
	color: <?php echo $settings->fb_header_text_color; ?>;
}
.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-popup-header p:last-of-type {
	margin-bottom: 0;
}
.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-popup-header-content {
	padding: 20px;
}
.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-popup-header-content p:last-of-type {
	margin-bottom: 0;
}
.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-popup-close {
	float: right;
	padding: 6px;
	width: 35px;
	height: 35px;
	text-align: center;
	vertical-align: middle;
	font-size: 20px;
	font-family: Helvetica, sans-serif;
	font-weight: 400;
	line-height: 20px;
	color: #666;
	cursor: pointer;
}
.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-popup-close:hover {
	color: #000;
}
.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-popup-content {
	padding: 20px;
	width: 100%;
	max-height: 520px;
	overflow-y: auto;
}
.ibx-wpfomo-floating-button-wrap p {
	margin: 0 0 10px;
}
.ibx-wpfomo-floating-popup-footer .ibx-wpfomo-branding {
	float: right;
	margin-bottom: 10px;
	margin-right: 10px;
	font-size: 12px;
	color: #999;
}
<?php if ( ! is_admin() ) : ?>
.ibx-wpfomo-floating-button-wrap.ibx-notification-bottom-right .ibx-wpfomo-floating-button-inner,
.ibx-wpfomo-floating-button-wrap.ibx-notification-bottom-right .ibx-wpfomo-floating-popup {
	right: 25px;
}
.ibx-wpfomo-floating-button-wrap.ibx-notification-bottom-left .ibx-wpfomo-floating-button-inner,
.ibx-wpfomo-floating-button-wrap.ibx-notification-bottom-left .ibx-wpfomo-floating-popup {
	left: 25px;
}
<?php endif; ?>
@media only screen and (max-width: 767px) {
	.ibx-wpfomo-floating-button-wrap {
		<?php if ( '1' == $settings->hide_mobile && ! is_admin() ) { ?>
			display: none !important;
		<?php } ?>
	}
}
@media only screen and (max-width: 385px) {
	.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-popup {
		height: 580px;
		width: 350px;
	}
	.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-popup-content {
		max-height: 475px;
	}
}
</style>

<?php
$class = 'ibx-wpfomo-floating-button-wrap';
if ( $settings->fb_show_popup_always ) {
	$class .= ' ibx-wpfomo-floating-popup-show ibx-wpfomo-floating-popup-active';
}
if ( isset( $settings->fb_position ) ) {
	$class .= ' ibx-notification-' . $settings->fb_position;
}

$header_content = isset( $settings->fb_header ) ? $settings->fb_header : '';
?>

<div id="ibx-wpfomo-floating-button-<?php echo $settings->post_id; ?>" class="<?php echo $class; ?>">
	<div class="ibx-wpfomo-floating-button-inner">
		<div class="ibx-wpfomo-floating-button" data-id="<?php echo $settings->post_id; ?>">
			<div class="ibx-wpfomo-floating-button-icon">
				<?php if ( 'default' === $settings->fb_icon_source ) : ?>
					<span class="dashicons dashicons-format-chat"></span>
				<?php endif; ?>
				<?php if ( 'dashicons' === $settings->fb_icon_source ) : ?>
					<span class="dashicons <?php echo ( ! empty( $settings->fb_dashicons ) ) ? $settings->fb_dashicons : 'dashicons-format-chat'; ?>"></span>
				<?php endif; ?>
				<?php if ( 'custom' === $settings->fb_icon_source ) : ?>
					<span><img src="<?php echo $settings->fb_icon_custom['url']; ?>" /></span>
				<?php endif; ?>
			</div>
			<div class="ibx-wpfomo-floating-button-close"><span>×</span></div>
		</div>
		<?php if ( is_admin() ) : ?>
		<div class="ibx-wpfomo-floating-button-msg">
			<strong>PREVIEW</strong>
		</div>
		<?php endif; ?>
	</div>
	<div class="ibx-wpfomo-floating-popup">
		<div class="ibx-wpfomo-floating-popup-header ibx-wpfomo-clear">
			<?php if ( '' != trim( $header_content ) ) : ?>
				<div class="ibx-wpfomo-floating-popup-header-content">
					<?php echo wpautop( $header_content ); ?>
				</div>
			<?php endif; ?>
		</div>
		<div class="ibx-wpfomo-floating-popup-content">
			<?php 
			global $wp_embed;
			echo do_shortcode( wpautop( $wp_embed->autoembed( $settings->fb_content ) ) );
			?>
		</div>
		<div class="ibx-wpfomo-floating-popup-footer">
			<?php if ( '1' != get_option( 'ibx_wpfomo_credit_link_disable' ) ) : ?>
			<div class="ibx-wpfomo-branding">
				<svg width="7" height="13" viewBox="0 0 7 13" xmlns="http://www.w3.org/2000/svg" title="<?php _e( 'Powered by', 'ibx-wpfomo' ); ?> WPfomify"><g fill="none" fill-rule="evenodd"><path d="M4.127.496C4.51-.12 5.37.356 5.16 1.07L3.89 5.14H6.22c.483 0 .757.616.464 1.044l-4.338 6.34c-.407.595-1.244.082-1.01-.618L2.72 7.656H.778c-.47 0-.748-.59-.48-1.02L4.13.495z" fill="#F6A623"></path><path fill="#FEF79E" d="M4.606.867L.778 7.007h2.807l-1.7 5.126 4.337-6.34H3.16"></path></g></svg>
					<?php _e( 'by', 'ibx-wpfomo' ); ?> <a href="https://wpfomify.com/?utm_source=<?php echo urlencode( home_url() ); ?>&utm_medium=widget_referrer" target="_blank">WPfomify</a>
			</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<script>
(function($) {
	$('.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-button').on('click', function() {
		var id = $(this).data('id');
		var wrapper = $('#ibx-wpfomo-floating-button-' + id);

		if ( wrapper.hasClass('ibx-wpfomo-floating-popup-active') || wrapper.find('.ibx-wpfomo-floating-popup').is(':visible') ) {
			wrapper.find('.ibx-wpfomo-floating-popup').animate({ 'bottom': '90px', 'opacity': 0 }, 300, function() {
				$(this).hide();
				wrapper.removeClass('ibx-wpfomo-floating-popup-active');
			});
		}

		if ( ! wrapper.hasClass('ibx-wpfomo-floating-popup-active') || ! wrapper.find('.ibx-wpfomo-floating-popup').is(':visible') ) {
			wrapper.find('.ibx-wpfomo-floating-popup').show().animate({ 'bottom': '100px', 'opacity': 1 }, 200);
			wrapper.addClass('ibx-wpfomo-floating-popup-active');
		}
	});
	$('.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-popup-close').on('click', function() {
		var id = $(this).data('id');
		var wrapper = $('#ibx-wpfomo-floating-button-' + id);

		wrapper.find('.ibx-wpfomo-floating-popup').animate({ 'bottom': '90px', 'opacity': 0 }, 300, function() {
			$(this).hide();
			wrapper.removeClass('ibx-wpfomo-floating-popup-active');
		});
	});

	<?php if ( is_admin() ) : ?>

		IBXFomoPreview.init([
			// Button background color
			{
				type: 'color',
				field: '#ibx_wpfomo_fb_button_bg_color',
				selector: '.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-button-inner',
				property: 'background'
			},
			// Button text color
			{
				type: 'color',
				field: '#ibx_wpfomo_fb_button_text_color',
				selector: '.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-button, .ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-button span',
				property: 'color'
			},
			// Header background color
			{
				type: 'color',
				field: '#ibx_wpfomo_fb_header_bg_color',
				selector: '.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-popup-header',
				property: 'background'
			},
			// Header text color
			{
				type: 'color',
				field: '#ibx_wpfomo_fb_header_text_color',
				selector: '.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-popup-header, .ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-popup-header p',
				property: 'color'
			},
			// Border width
			{
				type: 'color',
				field: '#ibx_wpfomo_border',
				selector: '.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-button-inner',
				property: 'border-width',
				unit: 'px'
			},
			// Border color
			{
				type: 'number',
				field: '#ibx_wpfomo_border_color',
				selector: '.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-button-inner',
				property: 'border-color'
			},
			// Box Shadow
			{
				type: 'box-shadow',
				field: {
					blur: '#ibx_wpfomo_shadow_blur',
					spread: '#ibx_wpfomo_shadow_spread',
					color: '#ibx_wpfomo_shadow_color',
					opacity: '#ibx_wpfomo_shadow_opacity'
				},
				selector: '.ibx-wpfomo-floating-button-wrap .ibx-wpfomo-floating-button-inner',
				property: 'box-shadow'
			},
		]);

		$(window).on('load', function() {
			setTimeout(function() {

				$('#ibx_wpfomo_type').on('change', function() {
					if ( $(this).val() !== 'floating_button' ) {
						$('.ibx-wpfomo-floating-button-wrap').hide();
					} else {
						$('.ibx-wpfomo-floating-button-wrap').show();
					}
				});

			}, 100);
		});
	<?php endif; ?>
})(jQuery);
</script>
