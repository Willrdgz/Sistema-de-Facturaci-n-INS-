document.querySelector('[data-reset-theme]')?.addEventListener('click', () => {
    document.querySelectorAll('[data-theme-settings] input[type="color"]').forEach(input => {
        input.value = input.dataset.default;
    });
});
