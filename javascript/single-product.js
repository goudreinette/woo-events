jQuery(function ($) {

    if (assigns['hideButton']) {
        $('form.cart button').remove()
    } else {
        $('form.cart button').text(assigns['cartButtonText'])
    }

    $('.quantity, .add_to_wishlist, .price-container').remove()

    $('form.cart button').click(function (e) {
        e.preventDefault()
        e.stopPropagation()
        location.assign(assigns['externalLink'])
    })
})