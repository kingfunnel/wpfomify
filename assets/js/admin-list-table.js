;(function($) {

    $('.wp-list-table tr .has-row-actions .row-actions .inline a').on('click', function(e) {
        e.preventDefault();

        var row = $(this).parents('tr');

        row.parents('table').find('tr.inline-edit-row').hide();
        row.parents('table').find('tr.ibx-wpfomo-custom-form-entry').show();
        row.next().show();
        row.hide();
    });

    $('tr.inline-edit-row').on('click', '.button', function(e) {
        e.preventDefault();

        var row = $(this).parents('tr.inline-edit-row');

        if ( $(this).hasClass('cancel') ) {
            row.prev().show();
            row.hide();
        }
        if ( $(this).hasClass('save') ) {           
            var data = {                
				'name'      : row.find('input[name="entry_name"]').val(),
				'title'		: row.find('input[name="entry_title"]').val(),
                'email'     : row.find('input[name="entry_email"]').val(),
                'ip_address': row.find('input[name="entry_ip_address"]').val(),
                'time'		: row.find('input[name="entry_time"]').val(),
                'country'   : row.find('input[name="entry_country"]').val(),
                'state'     : row.find('input[name="entry_state"]').val(),
                'city'      : row.find('input[name="entry_city"]').val(),
                'entry_id'  : row.find('input[name="ibx_wpfomo_custom_form_edit_entry_id"]').val(),
                'post_id'   : row.find('input[name="ibx_wpfomo_custom_form_edit_post_id"]').val(),
                'action'    : 'ibx_wpfomo_custom_form_data_edit'
            };

            row.find('.inline-edit-save .spinner').addClass('is-active');

            $.ajax({
                type: 'post',
                url: ajaxurl,
                data: data,
                success: function( res ) {
                    if ( res.success ) {
                        row.find('.inline-edit-save .spinner').removeClass('is-active');
                        row.prev().fadeIn(200, function() {
                            $(this).find('td.column-name strong:first').text(data.name);
							$(this).find('td.column-email').text(data.email);
							$(this).find('td.column-title').text(data.title);
                            $(this).find('td.column-ip_address').text(data.ip_address);
                            $(this).find('td.column-time').text(data.time);
                            $(this).find('td.column-country').text(data.country);
                            $(this).find('td.column-state').text(data.state);
                            $(this).find('td.column-city').text(data.city);
                        });
                        row.hide();
                    }
                }
            });
        }
    });

})(jQuery);
