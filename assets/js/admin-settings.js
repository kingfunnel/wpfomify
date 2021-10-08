;(function($) {
    // Tabs.
    var navEl       = $('.ibx-wpfomo-settings-tabs'),
        container   = $('.ibx-wpfomo-settings-tabs-content'),
        tabHash     = window.location.hash,
        currentTab  = window.location.hash.replace( '!', '' );

    // If the URL contains a hash beginning with wpfomo-tab, mark that tab as open
    // and display that tab's panel.
    if ( tabHash && tabHash.indexOf('ibx-wpfomo-tab-') >= 0 ) {

        // Remove the active class from everything in this tab navigation and section.
        container.find('.ibx-wpfomo-tab-content').removeClass('active');
        navEl.find('.ibx-wpfomo-tab').removeClass('active');

        // Add the active class to the chosen tab and section.
        $(currentTab).addClass('active');
        navEl.find('a[href="'+currentTab+'"]').addClass('active');
    }

    navEl.find('a').on('click', function(e) {
        // Prevent the default action.
        e.preventDefault();

        // Remove the active class from everything in this tab navigation and section.
        container.find('.ibx-wpfomo-tab-content').removeClass('active');
        navEl.find('.ibx-wpfomo-tab').removeClass('active');

        // Get the nav tab ID.
        var tabId = $(this).attr('href');

        // Add the active class to the chosen tab and section.
        $(tabId).addClass('active');
        $(this).addClass('active');

        // Update the window URL to contain the selected tab as a hash in the URL.
        window.location.hash = tabId.split('#').join('#!');

    });
})(jQuery);
