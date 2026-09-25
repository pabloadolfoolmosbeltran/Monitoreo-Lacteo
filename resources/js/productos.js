function initializeProductForms() {
    const productNameInputs = document.querySelectorAll('.js-product-name');

    productNameInputs.forEach((input) => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/[^a-zA-ZÀ-ÿñÑ0-9\s]/g, '');
        });
    });
}

document.addEventListener('DOMContentLoaded', initializeProductForms);
document.addEventListener('app:content-loaded', initializeProductForms);
