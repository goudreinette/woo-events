const $ = window.jQuery

$(function () {
    $('form.cart button').click(function (e) {
        e.preventDefault()
        e.stopPropagation()
        location.assign(window['external_link'][0])
    })
})