const navToggle = document.querySelector('[data-nav-toggle]');
const navMenu = document.querySelector('[data-nav-menu]');
const navActions = document.querySelector('[data-nav-actions]');

if (navToggle && navMenu && navActions) {
    navToggle.addEventListener('click', () => {
        navToggle.classList.toggle('is-open');
        navMenu.classList.toggle('is-open');
        navActions.classList.toggle('is-open');
    });
}

document.querySelectorAll('[data-confirm]').forEach((button) => {
    button.addEventListener('click', (event) => {
        const message = button.getAttribute('data-confirm');

        if (message && !window.confirm(message)) {
            event.preventDefault();
        }
    });
});
