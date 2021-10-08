;(function($) {
	$(window).on('load', function() {
		setTimeout(function() {
			$(document).on('ibx_wpfomo_type_change', function(e, type) {
				if ( 'conversion' !== type ) {
					$('#mbt-metabox-section-import_csv').hide();
				} else {
					if ( 'import_csv' === $('#ibx_wpfomo_conversions_source').val() ) {
						//$('#mbt-metabox-section-import_csv').show();
					}
				}
			});
		}, 600);
	});
	$('#ibx_wpfomo_csv_import_file').on('mbt:complete', function(e, response, $msg) {
		$('.csv_column_fields').each(function () {
			//remove all options except select option
			$(this).children('option:not(:first)').remove();
		});

		if ( response.trim() !== '' && typeof response !== 'undefined' ){
			var data = JSON.parse( response );
							
			if ( typeof data.success_msg !== 'undefined'){
				$msg.html( data.success_msg );
				$msg.css('color', 'green');
				$(e.target).val('');
				//csv column fields
				if (typeof data.file_columns !== 'undefined') {                               
					for( var key in data.file_columns ) {
						$('.csv_column_fields').append("<option value=" + key + ">" + data.file_columns[key] +"</option>");
					}                           
				}
				$('#mbt-metabox-section-csv_fields_section').removeClass('mbt-is-hidden');
			}
			else{
				$msg.html(data.error_msg);
				$msg.css('color', 'red');
			}
		}
		else {
			$msg.html(data.success_msg);
			$msg.css('color', 'red');
		}
	});
})(jQuery);