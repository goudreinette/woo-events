jQuery(function ($) {

    if (assigns['hide-button']) {
        $('form.cart button').remove()
    } else {
        $('form.cart button').text(assigns['external-link-text'])
    }

    $('.quantity, .add_to_wishlist, .price-container').remove()

    $('form.cart button').click(function (e) {
        e.preventDefault()
        e.stopPropagation()
        location.assign(assigns['external-link'])
    })

    /**
     * Uncode fixes
     */
    $('.uncont > .date').insertBefore($('.uncont .product_title'))
})