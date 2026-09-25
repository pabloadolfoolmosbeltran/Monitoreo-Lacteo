function initializeControl() {
    document.querySelectorAll('[data-auto-submit]').forEach((input) => {
        input.addEventListener('change', () => {
            input.form?.requestSubmit();
        });
    });
}

document.addEventListener('DOMContentLoaded', initializeControl);
document.addEventListener('app:content-loaded', initializeControl);
