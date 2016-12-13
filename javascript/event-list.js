jQuery(function ($) {
    /**
     * Placement
     */
    var eventlist = $('.vc_row > #woo-event-list')
    var column = eventlist.next('.vc_column_container').find('.wpb_wrapper')
    eventlist.detach().appendTo(column)

    /**
     * Filtering
     */
    $('.category').on('click', function (e) {
        var category = $(e.target).data('category')

        $('.category').toggleClass('active', false)
        $(e.target).toggleClass('active', true)

        if (category == 'all') {
            $('#event-list li').toggleClass('hide', false)
        } else {
            $('#event-list li').toggleClass('hide', true)
            $('#event-list li[data-category="' + category + '"]').toggleClass('hide', false)
        }
    })

    /**
     * Link
     */
    $('#event-list li h3, #event-list li .image').on('click', function (e) {
        var permalink = $(e.target).closest('#event-list li').data('permalink')
        window.location.assign(permalink)
    })

    /**
     * Image Proportions
     */
    // var proportion = $('#event-list li').data('proportion')
    // var width = $('#event-list li .image').width()
    // var height = width * proportion
    //
    // $('#event-list li').height(height)
})
