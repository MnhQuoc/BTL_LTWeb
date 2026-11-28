const menuToggle = document.querySelector('.menu-toggle');
const navigation = document.querySelector('.tailadmin-nav')?.closest('.tailadmin-sidebar') || document.querySelector('.main-nav');
if (menuToggle && navigation) {
    menuToggle.addEventListener('click', () => {
        const open = navigation.classList.toggle('open');
        menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
}

document.querySelectorAll('[data-confirm]').forEach((link) => {
    link.addEventListener('click', (event) => {
        if (!window.confirm(link.dataset.confirm)) {
            event.preventDefault();
        }
    });
});

const orderStatusClasses = [
    'order-status--pending',
    'order-status--confirmed',
    'order-status--shipping',
    'order-status--completed',
    'order-status--cancelled',
];
document.querySelectorAll('.order-status-select').forEach((select) => {
    select.addEventListener('change', () => {
        select.classList.remove(...orderStatusClasses);
        select.classList.add(`order-status--${select.value}`);
    });
});

document.querySelectorAll('form[data-validate]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!form.checkValidity()) {
            event.preventDefault();
            form.reportValidity();
        }
    });
});
