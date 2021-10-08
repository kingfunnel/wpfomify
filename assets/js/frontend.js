;(function($) {

    /**
     * The main fomo interface.
     *
     * @since 1.0.0
     * @class IBXFomo
     */
    IBXFomo = {
		_isActive: false,
        /**
         * A flag to check whether the fomo is showing or not.
         *
         * @since 1.0.0
         * @access private
         */
        _fomoBarActive: 0,

        /**
         * Initialize fomo interface.
         *
         * @since 1.0.0
         * @access private
         * @method _init
         */
        _init: function()
        {
			if ( ! this._isActive ) {
				this._initFomoBar();
				this._initNotifications();
				this._bindEvents();
				this._isActive = true;
			}
        },

        /**
         * Bind events.
         *
         * @since 1.0.0
         * @access private
         * @method _bindEvents
         */
        _bindEvents: function()
        {
            $('body').delegate( '.ibx-fomo .ibx-fomo-bar-close', 'click', function() {
                IBXFomo._fomoBarActive = 0;
                IBXFomo._hideFomoBar();
            } );

            $('body').delegate( '.ibx-notification-popup .ibx-notification-popup-close', 'click', function() {
				var element = $(this).parents('.ibx-notification-popup');
				$.cookie( 'ibx_wpfomo_notification_hidden', true, { path: '/' } );
				IBXFomo._hideNotification(element);
            } );
        },

        /**
         * Initialize the fomo bar and countdown.
         *
         * @since 1.0.0
         * @access private
         * @method _initFomoBar
         */
        _initFomoBar: function()
        {
            var elements = $('.ibx-fomo:last');

            if ( elements.length === 0 ) {
                return;
			}

            elements.each(function() {
                var fomo_bar        = $(this),
                    id              = fomo_bar.data('fomo-id'),
                    duration        = fomo_bar.data('display-duration'),
                    countdown_time  = fomo_bar.find('.ibx-fomo-countdown').data('fomo-time'),
					timer_style  	= fomo_bar.find('.ibx-fomo-countdown').data('style'),
					date 			= new Date(),
					countdown       = [],
					countdown_cookie = '';

				if ( fomo_bar.hasClass( 'ibx-fomo-hide-mobile' ) && window.innerWidth <= 768 ) {
					return;
				}

                if ( 'undefined' !== typeof countdown_time ) {

					if ( 'evergreen' === timer_style ) {
						countdown['days']       = countdown_time.split(',')[0];
						countdown['hours']      = countdown_time.split(',')[1];
						countdown['minutes']    = countdown_time.split(',')[2];
						countdown['seconds']    = countdown_time.split(',')[3];

						var year    = date.getYear() + 1900,
							month   = date.getMonth() + 1,
							days    = ( parseInt( date.getDate() ) + parseInt( countdown['days'] ) ),
							hours   = ( parseInt( date.getHours() ) + parseInt( countdown['hours'] ) ),
							minutes = ( parseInt( date.getMinutes() ) + parseInt( countdown['minutes'] ) ),
							seconds = ( parseInt( date.getSeconds() ) + parseInt( countdown['seconds'] ) );
					} else {
						countdown['days']       = countdown_time.split(',')[0];
						countdown['month']      = countdown_time.split(',')[1];
						countdown['year']    	= countdown_time.split(',')[2];
						countdown['hours']    	= countdown_time.split(',')[3];
						countdown['minutes']    = countdown_time.split(',')[4];
						countdown['seconds']    = countdown_time.split(',')[5];

						var year    = parseInt( countdown['year'] ),
							month   = parseInt( countdown['month'] ),
							days    = parseInt( countdown['days'] ),
							hours   = parseInt( countdown['hours'] ),
							minutes = parseInt( countdown['minutes'] ),
							seconds = parseInt( countdown['seconds'] );
					}

					var new_date = new Date( year, parseInt( month, 10 ) - 1, days, hours, minutes, seconds );

                    // Convert countdown time to miliseconds and add it to current date.
                    date.setTime(date.getTime() +  ( parseInt( countdown['days'] ) * 24 * 60 * 60 * 1000)
                                                +  ( parseInt( countdown['hours'] )  * 60 * 60 * 1000)
                                                +  ( parseInt( countdown['minutes'] ) * 60 * 1000)
                                                +  ( parseInt( countdown['seconds'] ) * 1000) );

                    // Remove countdown value from cookie if countdown value has changed in wp-admin.
                    if( $.cookie('ibx_fomo_countdown_old') !== countdown_time ){
                        $.cookie( 'ibx_fomo_countdown_old', countdown_time, { expires: date } );
                        $.removeCookie('ibx_fomo_countdown');
                    }
                    // Get countdown value from cookie if exist.
                    if ( $.cookie('ibx_fomo_countdown') ){
                        countdown_cookie = $.cookie( 'ibx_fomo_countdown' );
                    }
                    else {
                        // Set countdown value in cookie if doesn't exist.
                        $.cookie( 'ibx_fomo_countdown', new_date.getTime(), { expires: date } );
                        $.cookie( 'ibx_fomo_countdown_old', countdown_time, { expires: date } );
                        countdown_cookie = $.cookie( 'ibx_fomo_countdown' );
                    }

                    // Start countdown.
                    var countdown_interval = setInterval(function() {
                        var now         = new Date().getTime(),
							difference  = 'evergreen' === timer_style ? ( countdown_cookie - now ) : ( new_date.getTime() - now );
							
						// If the count down is over, write some text
						if ( difference <= 0 ) {
							clearInterval( countdown_interval );
							fomo_bar.find('.ibx-fomo-days').html('0');
							fomo_bar.find('.ibx-fomo-hours').html('0');
							fomo_bar.find('.ibx-fomo-minutes').html('0');
							fomo_bar.find('.ibx-fomo-seconds').html('0');
							fomo_bar.find('.ibx-fomo-bar-wrapper').addClass('ibx-fomo-expired');
							return;
						}

                        // Calculate time from difference.
                        var days        = Math.floor( difference / ( 1000 * 60 * 60 * 24 ) ),
                            hours       = Math.floor( ( difference % ( 1000 * 60 * 60 * 24 ) ) / ( 1000 * 60 * 60 ) ),
                            minutes     = Math.floor( ( difference % ( 1000 * 60 * 60 ) ) / ( 1000 * 60 ) ),
                            seconds     = Math.floor( ( difference % ( 1000 * 60 )) / 1000 );

                        // Output the result in an element with id="ibx-fomo-countdown-time"
                        fomo_bar.find('.ibx-fomo-days').html(days);
                        fomo_bar.find('.ibx-fomo-hours').html(hours);
                        fomo_bar.find('.ibx-fomo-minutes').html(minutes);
                        fomo_bar.find('.ibx-fomo-seconds').html(seconds);
                    }, 1000);
                }

                IBXFomo._showFomoBar(fomo_bar);

                if ( 'undefined' !== typeof duration && '' !== duration ) {
                    setTimeout(function() {
                        IBXFomo._hideFomoBar();
                    }, parseInt(duration) * 1000);
                }
            });
        },

		/**
         * Displays the notification fomo bar.
         *
         * @since 1.0.0
         * @access private
         * @method _showFomoBar
         */
        _showFomoBar: function(fomo_bar)
        {
            if ( '' === fomo_bar ) {
                fomo_bar = $('.ibx-fomo:last');
            }
            var initial_delay       = parseInt( fomo_bar.data('initial-delay') ),
                fomo_bar_height     = fomo_bar.find('.ibx-fomo-bar-wrapper').outerHeight(),
                admin_bar_height    = ( $('#wpadminbar').length > 0 ) ? $('#wpadminbar').outerHeight() : 0;

            if ( '' === initial_delay || isNaN( initial_delay ) ) {
                initial_delay = 0;
            }

            setTimeout(function() {
                $('html').addClass('ibx-fomo-bar-active');
                if ( fomo_bar.hasClass('ibx-fomo-position-top') ) {
					$('html').animate({ 'padding-top': fomo_bar_height + 'px' }, 300);
					fomo_bar.find('.ibx-fomo-bar-wrapper').css( 'margin-top', '0px' );
                } else {
					$('body').animate({'margin-bottom': fomo_bar_height + 'px'}, 300);
				}

                IBXFomo._fomoBarActive = 1;
            }, initial_delay * 1000);
        },

		/**
         * Hides the notification fomo bar.
         *
         * @since 1.0.0
         * @access private
         * @method _hideFomoBar
         */
        _hideFomoBar: function()
        {
            var fomo_bar            = $('.ibx-fomo:last'),
                fomo_bar_height     = fomo_bar.find('.ibx-fomo-bar-wrapper').outerHeight(),
                admin_bar_height    = ( $('#wpadminbar').length > 0 ) ? $('#wpadminbar').outerHeight() : 0;

			$('html').removeClass('ibx-fomo-bar-active');
			if ( ! fomo_bar.hasClass( 'ibx-fomo-position-bottom' ) ) {
				$('html, body').css( 'padding-top', '0px' );
			} else {
				$('body').css('margin-bottom', '0px');
			}		
			fomo_bar.find('.ibx-fomo-bar-wrapper').removeAttr( 'style' );

            IBXFomo._fomoBarActive = 0;
		},
		
		/**
         * Initialize the notifications.
         *
         * @since 1.0.0
         * @access private
         * @method _initNotifications
         */
		_initNotifications: function()
		{
			if ( 'undefined' === typeof ibx_fomo ) {
                return;
			}

			$.cookie('ibx_wpfomo_notification_hidden', '', { expires: -1, path: '/' });
			
			if ( ibx_fomo.conversions.length > 0 ) {
                IBXFomo._processNotifications( ibx_fomo.conversions, 'conversions' );
			}
			
			if ( ibx_fomo.reviews.length > 0 ) {
                IBXFomo._processNotifications( ibx_fomo.reviews, 'reviews' );
            }
		},

		/**
         * Fetch the notifications data and process them to render.
         *
         * @since 2.0
         * @access private
         * @method _processNotifications
         */
		_processNotifications: function( ids, type )
		{
			var node = $('<div class="ibx-conversions"></div>');
			var html = '';
			
			if ( 'undefined' !== typeof ibx_fomo.data[type] ) {
				var data = ibx_fomo.data[type];
				var rawHtml = data.content;
						
				if ( rawHtml ) {
					rawHtml = rawHtml.replace( /&#8217;/g, String.fromCharCode(8217) );
					rawHtml = rawHtml.replace( /&amp;#8217;/g, String.fromCharCode(8217) );
					//rawHtml = rawHtml.replace( /&amp;rsquo;/g, String.fromCharCode(8217) );
					rawHtml = rawHtml.replace( /&#8211;/g, String.fromCharCode(8211) );
					rawHtml = rawHtml.replace( /&amp;#8211;/g, String.fromCharCode(8211) );
				}

				html = node.html(rawHtml);

				IBXFomo._removeNotificationLocalData();
				IBXFomo._renderNotification(data.config, html);

				return;
			}

            $.ajax({
                type: 'post',
                url: ibx_fomo.ajaxurl,
				cache: false,
				headers: {
					'X-Requested-With': 'WPfomify'
				},
                data: {
                    action: 'ibx_wpfomo_get_conversions',
                    nonce: ibx_fomo.nonce,
                    ids: ids
                },
                success: function(data) {
                    if ( data ) {

						data = JSON.parse( data );
						var rawHtml = data.content;
						
						if ( rawHtml ) {
							rawHtml = rawHtml.replace( /&#8217;/g, String.fromCharCode(8217) );
							rawHtml = rawHtml.replace( /&amp;#8217;/g, String.fromCharCode(8217) );
							rawHtml = rawHtml.replace( /&#8211;/g, String.fromCharCode(8211) );
							rawHtml = rawHtml.replace( /&amp;#8211;/g, String.fromCharCode(8211) );
						}

                        html = node.html(rawHtml);
                        if ( 'undefined' !== typeof data.config.randomize && data.config.randomize === 1 ) {
							var localData = IBXFomo._getNotificationLocalData(data.config.source);
                            if ( localData ) {
                                html = $(localData);
                            } else {
								if ( html[0].children.length > 0 ) {
									for (var i = html[0].children.length; i >= 0; i--) {
										html[0].appendChild(html[0].children[(Math.random() * i) | 0]);
									}
								}
                                IBXFomo._saveNotificationLocalData(html[0].outerHTML, data.config.source);
							}
                        } else {
							IBXFomo._removeNotificationLocalData();
						}
                        IBXFomo._renderNotification(data.config, html);
                    }
                }
            });
		},

        /**
         * Render the markup of notification.
         *
         * @since 1.0.0
         * @access private
         * @method _renderNotification
         */
        _renderNotification: function(config, html)
        {
            var count       = 0,
                elements    = html.find('.ibx-notification-popup-' + config.id),
                delayCalc   = (config.initial_delay + config.display_duration + config.delay_each) / 1000,
                delayEach   = config.delay_each,
                last        = IBXFomo._getLastNotification(config.id, false);

            if ( last >= 0 ) {
                count = last + 1;
			}

			if ( count >= $(elements).length ) {
				count = 0;
			}

			if ( config.loop === 0 && elements.length === 1 ) {
				count = 0;
			}

            setTimeout(function() {

                // Show the first notification.
				IBXFomo._showNotification( $(elements[count]), config, count );

                setTimeout(function() {

                    // Hide the first notification when display duration is expired.
					IBXFomo._hideNotification( $(elements[count]) );
					
                    // Increase the sequence.
                    count++;

                    // Now lets render next notifications.
                    var next = setInterval(function() {
                        // Show next notification
						IBXFomo._showNotification( $(elements[count]), config, count );

                        setTimeout(function() {
                            // Again hide this notification once display duration is expired.
                            IBXFomo._hideNotification( $(elements[count]) );

                            // reset the count, so that it can either start from begining or stop.
                            if ( count >= elements.length - 1 ) {
                                count = 0;
                                // If notifications are not in loop, clear the interval.
                                if ( config.loop === 0 ) {
                                    clearInterval(next);
                                }
                            } else {
                                count++;
                            }

                        }, config.display_duration);

						// TODO: delayEach can be used as random number.
                    }, delayEach + config.display_duration);

                }, config.display_duration);

            }, config.initial_delay);
        },

        /**
         * Show notification.
         *
         * @since 1.0.0
         * @access private
         * @method _showNotification
         */
        _showNotification: function(element, config, count)
        {
			if ( 'undefined' === typeof element || 0 === element.length ) {
				return;
			}

			if ( 'undefined' !== typeof $.cookie( 'ibx_wpfomo_notification_hidden' ) ) {
				return;
            }
            // if page visitor analytics then randomize.
            var tmp_analytics_elem = element.find("#ibx-notification-popup-analytics-page");
            if ('undefined' !== typeof tmp_analytics_elem && tmp_analytics_elem.length > 0) {
                var prev_cnt = parseInt(tmp_analytics_elem.html().trim(), 10);
                tmp_analytics_elem.html(prev_cnt + Math.abs( parseInt(IBXFomo._getRandomInt(-3, 3), 10)));
            }
			$('body').append(element);
			
			$('body').trigger('wpfomo.notification.beforeShow', [element]);

			//element.animate({ 'bottom': '20px', 'opacity': '1' }, 500);

            element.animate({ 'bottom': '15px', 'opacity': '1' }, 500, function () {
				//this.classList.add("fade");
				$('body').trigger('wpfomo.notification.afterShow', [element]);
            });

			// Save the sequence of the last notification.
			IBXFomo._saveLastConversion( config.id, count );
        },

        /**
         * Hide notification.
         *
         * @since 1.0.0
         * @access private
         * @method _hideNotification
         */
        _hideNotification: function(element)
        {
			$('body').trigger('wpfomo.notification.beforeHide', [element]);

            element.animate({ 'bottom': '-250px', 'opacity': '0' }, 1000, function() {
				IBXFomo._removeNotification(element);
				$('body').trigger('wpfomo.notification.afterHide', [element]);
            });
        },

        /**
         * Remove notification.
         *
         * @since 1.0.0
         * @access private
         * @method _removeNotification
         */
        _removeNotification: function(element)
        {
            if ( element.length > 0 ) {
                element.remove();
            }
        },

        /**
         * Get the sequence of the last notification.
         *
         * @since 1.0.0
         * @access private
         * @method _getLastNotification
         */
        _getLastNotification: function(id, obj)
        {
            var last = -1;
            if ( window.localStorage ) {
                var notificationSequenece = window.localStorage.getItem('ibx_wpfomo_notifications');
                if ( null !== notificationSequenece ) {
                    notificationSequenece = JSON.parse(notificationSequenece);
                    if ( undefined !== notificationSequenece[id] ) {
                        if ( obj ) {
                            return notificationSequenece;
                        }
                        last = notificationSequenece[id];
                    }
                }
            } else {
                console.log('Browser does not support localStorage!');
            }
            return last;
        },

        /**
         * Save the sequence of the last notification.
         *
         * @since 1.0.0
         * @access private
         * @method _saveLastConversion
         */
        _saveLastConversion: function(id, sequence)
        {
            if ( window.localStorage ) {
                var lastConversion = IBXFomo._getLastNotification(id, true);
                if ( 'object' === typeof lastConversion ) {
                    lastConversion[id] = sequence;
                } else {
                    lastConversion = new Object;
                    lastConversion[id] = sequence;
                }
                window.localStorage.setItem('ibx_wpfomo_notifications', JSON.stringify(lastConversion));
            } else {
                console.log('Browser does not support localStorage!');
            }
        },

        /**
         * Get notification data from localStorage.
         *
         * @since 1.0.0
         * @access private
         * @method _getNotificationLocalData
         */
        _getNotificationLocalData: function(source)
        {
            if (window.localStorage) {
				
				if ( window.localStorage.getItem('ibx_wpfomo_notifications_source') !== source ) {
					IBXFomo._removeNotificationLocalData();
					return false;
				}

                var html = window.localStorage.getItem('ibx_wpfomo_notifications_html');
                var expireTime = window.localStorage.getItem("ibx_wpfomo_notifications_expire");
                var difference = Date.now() - expireTime;
                var hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));

                if ( hours >= 3 ) {
					html = '';
					IBXFomo._removeNotificationLocalData();
                }

                if ( typeof html === 'undefined' || html === '' ) {
                    return false;
                }

                return html;
            }

            return false;
        },

        /**
         * Save notification data in localStorage.
         *
         * @since 1.0.0
         * @access private
         * @method _saveNotificationLocalData
         */
        _saveNotificationLocalData: function(html, source)
        {
            if (window.localStorage) {
                window.localStorage.setItem('ibx_wpfomo_notifications_html', html);
                window.localStorage.setItem('ibx_wpfomo_notifications_source', source);
                window.localStorage.setItem('ibx_wpfomo_notifications_expire', Date.now());
            }
		},
		
		/**
         * Remove notification data from localStorage.
         *
         * @since 1.0.0
         * @access private
         * @method _removeNotificationLocalData
         */
		_removeNotificationLocalData: function()
		{
			window.localStorage.removeItem('ibx_wpfomo_notifications_html');
			window.localStorage.removeItem('ibx_wpfomo_notifications_source');
			window.localStorage.removeItem('ibx_wpfomo_notifications_expire');
        },

        /**
		 * Get random integer value between range
		 *
		 * @since 1.0.0
		 * @access private
		 * @method _getRandomInt
		 */
        _getRandomInt: function (min, max) 
        {
            min = Math.ceil(min);
            max = Math.floor(max);
            return Math.floor(Math.random() * (max - min)) + min; // The maximum is exclusive and the minimum is inclusive
        },
    };

	// Load the object.
    $(window).on('load', function() {
		IBXFomo._init();
	});

})(jQuery);
