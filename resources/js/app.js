const menuToggle = document.querySelector('.mobile-menu-toggle');
const sidebar = document.querySelector('#admin-sidebar');

if (menuToggle && sidebar) {
    const closeMenu = () => {
        sidebar.classList.remove('mobile-open');
        menuToggle.setAttribute('aria-expanded', 'false');
    };

    menuToggle.addEventListener('click', () => {
        const open = sidebar.classList.toggle('mobile-open');
        menuToggle.setAttribute('aria-expanded', String(open));
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMenu();
            menuToggle.focus();
        }
    });
    document.addEventListener('click', (event) => {
        if (!sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
            closeMenu();
        }
    });
}
