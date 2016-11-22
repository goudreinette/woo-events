jQuery(function ($) {

    /**
     * Select Days, Show/Hide Event Tickets
     */
    $('.events .event').addClass('hidden')
    $('.events .event').each((i, el) => {
        const startDate = $(el).data('start-date')
        const day = $('.day[data-start-date="' + startDate + '"]')
        day.addClass('has-event')
    })

    $(document).on('click', '.day', function () {
        const startDate = $(this).data('start-date')
        $('.day').removeClass('active')
        $(this).addClass('active')
        $('.events .event').addClass('hidden')
        $('.events .event[data-start-date="' + startDate + '"]').removeClass('hidden')
    })

    /**
     * Next/Previous Month
     */
    $('.month:first-child').toggleClass('active', true)

    $(document).on('click', '#calendar-wrap #next', function () {
        const nextMonthText = $('.month.active:not(:last-child)')
            .toggleClass('active', false)
            .next()
            .toggleClass('active', true)
    })

    $(document).on('click', '#calendar-wrap #previous', function () {
        const prevMonthText = $('.month.active:not(:first-child)')
            .toggleClass('active', false)
            .prev()
            .toggleClass('active', true)
    })
});
