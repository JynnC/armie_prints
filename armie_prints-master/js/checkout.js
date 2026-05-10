const paymentOptions = document.querySelectorAll('.payment-option');

paymentOptions.forEach(option => {
    option.addEventListener('click', () => {
        paymentOptions.forEach(item => item.classList.remove('active'));
        option.classList.add('active');

        const radio = option.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;
    });
});