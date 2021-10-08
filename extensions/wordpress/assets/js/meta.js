;(function($) {
	$(window).on('load', function() {
		var repoTypeOpts = $('#ibx_wpfomo_wp_repo_type').html();

		var fieldsToggle = function( type ) {
			if ( 'reviews' === type ) {
					$('#ibx_wpfomo_wp_repo_type option[value="theme"]').remove();
					$('#mbt-field-wp_repo_slug_theme').hide();
					if ( 'wordpress' === $('#ibx_wpfomo_reviews_source').val() ) {
						$('#mbt-field-wp_notification_reviews_tmpl').show();
						$('#mbt-field-wp_notification_reviews_condition').show();
						$('#ibx_wpfomo_wp_notification_link').trigger('change');
					}
			} else {
				$('#ibx_wpfomo_wp_repo_type').html( repoTypeOpts );
				$('#mbt-field-wp_notification_reviews_tmpl').hide();
				$('#mbt-field-wp_notification_reviews_condition').hide();
				if ( 'wordpress' === $('#ibx_wpfomo_conversions_source').val() ) {
					$('#ibx_wpfomo_wp_repo_type').trigger('change');
					$('#ibx_wpfomo_wp_notification_link').trigger('change');
				}
			}
		};

		setTimeout(function() {
			fieldsToggle( $('#ibx_wpfomo_type').val() );

			$(document).on('ibx_wpfomo_type_change', function(e, type) {
				fieldsToggle( type );
			});

			$(document).on('ibx_wpfomo_source_change', function(e, field, type) {
				if ( 'wordpress' === field.val() ) {
					fieldsToggle( type );
				}
			});
		}, 600);
	});
})(jQuery);