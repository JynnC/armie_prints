function updateQty(cartId, action) {
    fetch('update-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'cart_id=' + cartId + '&action=' + action
    })
    .then(() => location.reload());
}

function removeItem(cartId) {
    fetch('remove-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'cart_id=' + cartId
    })
    .then(() => location.reload());
}