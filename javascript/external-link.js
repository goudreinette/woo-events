const $ = window.jQuery

$(function () {
    $('form.cart button').text('Bekijken')
    $('.quantity, .add_to_wishlist, .price-container').remove()

    $('form.cart button').click(function (e) {
        e.preventDefault()
        e.stopPropagation()
        location.assign(assigns['external-link'])
    })
})