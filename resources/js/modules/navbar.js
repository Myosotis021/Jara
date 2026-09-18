/**
 * Apple Resizable Navbar (Scroll Detection & Mobile Sheet)
 */

export function initAppleResizableNavbar() {
    const navbar = document.getElementById('apple-resizable-navbar');
    const toggleBtn = document.getElementById('apple-mobile-menu-toggle');
    const sheet = document.getElementById('apple-mobile-nav-sheet');

    if (!navbar) return;

    // Scroll listener for expanding / contracting into floating pill
    let ticking = false;
    const onScroll = () => {
        if (!ticking) {
            window.requestAnimationFrame(() => {
                if (window.scrollY > 40) {
                    navbar.classList.add('is-scrolled');
                } else {
                    navbar.classList.remove('is-scrolled');
                }
                ticking = false;
            });
            ticking = true;
        }
    };

    window.removeEventListener('scroll', onScroll);
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    // Mobile Hamburger Toggle
    if (toggleBtn && sheet) {
        const iconMenu = toggleBtn.querySelector('.icon-menu-hamburger');
        const iconClose = toggleBtn.querySelector('.icon-menu-close');

        const openMenu = () => {
            sheet.classList.add('is-open');
            toggleBtn.setAttribute('aria-expanded', 'true');
            if (iconMenu) iconMenu.style.display = 'none';
            if (iconClose) iconClose.style.display = 'block';
        };

        const closeMenu = () => {
            sheet.classList.remove('is-open');
            toggleBtn.setAttribute('aria-expanded', 'false');
            if (iconMenu) iconMenu.style.display = 'block';
            if (iconClose) iconClose.style.display = 'none';
        };

        // Remove old listeners to avoid stacking
        const newToggleBtn = toggleBtn.cloneNode(true);
        toggleBtn.parentNode.replaceChild(newToggleBtn, toggleBtn);

        newToggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            sheet.classList.contains('is-open') ? closeMenu() : openMenu();
        });

        document.addEventListener('click', (e) => {
            if (!navbar.contains(e.target)) {
                closeMenu();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeMenu();
            }
        });
    }
}
