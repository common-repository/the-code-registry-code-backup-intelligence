(function($) {
    $(document).ready(function() {
        // Handle form submission
        $('#recreate-archive-form').on('submit', function(e) {
            var $button = $('#recreate-archive-button');
            $button.prop('disabled', true)
                   .val('Recreating code archive, please wait as this can take a while...')
                   .addClass('updating-message');
        });

        // Handle status message
        var urlParams = new URLSearchParams(window.location.search);
        var status = urlParams.get('status');
        
        if (status === 'success') {
            var $messageDiv = $('#message');
            $messageDiv.removeClass('hidden')
                       .addClass('notice-success');
            $messageDiv.find('p').text('Local code archive successfully recreated.');

            // Remove status from URL
            var newUrl = window.location.pathname + '?page=data-and-tools';
            history.replaceState({}, '', newUrl);

            // Re-enable the button
            $('#recreate-archive-button').prop('disabled', false)
                                         .val('Manually Update Code Archive')
                                         .removeClass('updating-message');
        }
    });
})(jQuery);