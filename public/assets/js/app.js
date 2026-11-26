const menuToggle = document.querySelector('.menu-toggle');
const navigation = document.querySelector('.main-nav');
if (menuToggle && navigation) {
    menuToggle.addEventListener('click', () => {
        const open = navigation.classList.toggle('open');
        menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
}

document.querySelectorAll('form[data-validate]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!form.checkValidity()) {
            event.preventDefault();
            form.reportValidity();
        }
    });
});
