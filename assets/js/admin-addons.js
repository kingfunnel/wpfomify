;(function($) {

    $('body').delegate('.ibx-wpfomo-ftp-popup .ibx-wpfomo-popup-close', 'click', function(e) {
        e.preventDefault();
        $(this).parents('.ibx-wpfomo-ftp-popup').fadeOut();
    });

    $('.ibx-wpfomo-addon').on('click', '.button', function(e) {
        e.preventDefault();

        var $this   = $(this);
        var $el     = $this.parents('.ibx-wpfomo-addon');
        var $spinner = $el.find('.spinner');

        $el.find('.ibx-wpfomo-addon-error').removeClass('activated').html('');
        $this.addClass('disabled');
        $spinner.addClass('is-active');

        // Install addon.
        if ( $this.hasClass('ibx-wpfomo-addon-install') ) {
            var addon_url = $this.attr('rel');
            $.ajax({
                type: 'post',
                url: ajaxurl,
                async: true,
                cache: false,
                dataType: 'json',
                data: {
                    action: 'ibx_wpfomo_install_addon',
                    addon_url: addon_url,
                    nonce: ibx_wpfomo_addons.install_nonce
                },
                success: function( res ) {
                    $spinner.removeClass('is-active');
                    // if there is an error, ouput the error message.
                    if ( res.error ) {
						$el.find('.ibx-wpfomo-addon-error').html(res.error).addClass('activated');
                        $this.removeClass('disabled');
                        return;
                    }
                    if ( res.form ) {
                        $('.ibx-wpfomo-ftp-popup .ibx-wpfomo-popup-content').html(res.form);
                        $('.ibx-wpfomo-ftp-popup').fadeIn(100);
                        $this.removeClass('disabled');

                        $('.ibx-wpfomo-ftp-popup .ibx-wpfomo-popup-content').on('click', '#upgrade', function(e) {
                            e.preventDefault();

                            // AJAX for form submission.
                            var form      = $(this).parents('form'),
                                hostname  = form.find('#hostname').val(),
                                username  = form.find('#username').val(),
                                password  = form.find('#password').val(),
                                fs_nonce  = form.find('#_fs_nonce').val(),
                                proceed   = $(this);

                            proceed.val(ibx_wpfomo_addons.installing_text);
                            proceed.addClass('disabled');

                            $.ajax({
                                type: 'post',
                                url: ajaxurl,
                                async: true,
                                cache: false,
                                dataType: 'json',
                                data: {
                                    action: 'ibx_wpfomo_install_addon',
                                    addon_url: addon_url,
                                    nonce: ibx_wpfomo_addons.install_nonce,
                                    hostname: hostname,
                                    username: username,
                                    password: password,
                                    _fs_nonce: fs_nonce
                                },
                                success: function(res) {
                                    // if there is an error, ouput the error message.
                                    if ( res.error ) {
                                        $el.find('.ibx-wpfomo-addon-error').html(res.error).addClass('activated');
                                        $this.removeClass('disabled');
                                        proceed.removeClass('disabled');
                                        proceed.after('<div class="notice notice-alt notice-error ibx-wpfomo-notice"><p>' + res.error + '</p></div>');
                                        return;
                                    }
                                    if ( res.form ) {
                                        $el.find('.ibx-wpfomo-addon-error').removeClass('activated');
                                        $('.ibx-wpfomo-notice').remove();
                                        proceed.val(ibx_wpfomo_addons.proceed_text).removeClass('disabled');
                                        proceed.after('<div class="notice notice-alt notice-error ibx-wpfomo-notice"><p>' + ibx_wpfomo_addons.connect_error + '</p></div>');
                                        $el.find('.ibx-wpfomo-addon-error').html(ibx_wpfomo_addons.connect_error).addClass('activated');
                                        $this.removeClass('disabled');
                                        return;
                                    }

                                    // if success..
                                    $('.ibx-wpfomo-ftp-popup').fadeOut();
                                    $this.html(ibx_wpfomo_addons.activate_text).removeClass('ibx-wpfomo-addon-install disabled').addClass('ibx-wpfomo-addon-activate');
                                    $this.attr('rel', res.plugin);
                                    $el.find('.ibx-wpfomo-addon-error').removeClass('activated');
                                }
                            });
                        });

                        return;
                    }
                    // if success, output the activate button.
                    $this.html(ibx_wpfomo_addons.activate_text).removeClass('ibx-wpfomo-addon-install disabled').addClass('ibx-wpfomo-addon-activate');
                    $this.attr('rel', res.plugin);
                    $spinner.removeClass('is-active');
                }
            });
        }

        // Activate addon.
        if ( $this.hasClass('ibx-wpfomo-addon-activate') ) {
            var plugin = $this.attr('rel');
            $.ajax({
                type: 'post',
                url: ajaxurl,
                async: true,
                cache: false,
                dataType: 'json',
                data: {
                    action: 'ibx_wpfomo_activate_addon',
                    plugin: plugin,
                    nonce: ibx_wpfomo_addons.activate_nonce
                },
                success: function( res ) {
                    $spinner.removeClass('is-active');
                    // if there is an error, ouput the error message.
                    if ( res.error ) {
                        $el.find('.ibx-wpfomo-addon-error').html(res.error).addClass('activated');
                        $this.removeClass('disabled');
                        return;
                    }
                    // if success, output the activate button.
                    $this.html(ibx_wpfomo_addons.deactivate_text).removeClass('ibx-wpfomo-addon-activate disabled button-primary').addClass('ibx-wpfomo-addon-deactivate');
                    $this.attr('rel', res.plugin);
                }
            });
        }

        // Dectivate addon.
        if ( $this.hasClass('ibx-wpfomo-addon-deactivate') ) {
            var plugin = $this.attr('rel');
            $.ajax({
                type: 'post',
                url: ajaxurl,
                async: true,
                cache: false,
                dataType: 'json',
                data: {
                    action: 'ibx_wpfomo_deactivate_addon',
                    plugin: plugin,
                    nonce: ibx_wpfomo_addons.deactivate_nonce
                },
                success: function( res ) {
                    $spinner.removeClass('is-active');
                    // if there is an error, ouput the error message.
                    if ( res.error ) {
                        $el.find('.ibx-wpfomo-addon-error').html(res.error).addClass('activated');
                        $this.removeClass('disabled');
                        return;
                    }
                    // if success, output the activate button.
                    $this.html(ibx_wpfomo_addons.activate_text).removeClass('ibx-wpfomo-addon-deactivate disabled').addClass('button-primary ibx-wpfomo-addon-activate');
                    $this.attr('rel', res.plugin);
                }
            });
        }
    });

})(jQuery);
