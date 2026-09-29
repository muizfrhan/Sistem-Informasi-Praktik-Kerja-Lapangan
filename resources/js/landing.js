/**
 * Interaksi landing page SIPKL.
 *
 * Script asli (switchRole / toggleFaq) dipindahkan ke sini agar markup Blade
 * tetap bersih dan tidak ada inline handler. Tidak ada dependensi tambahan:
 * vanilla JS murni, tanpa library animasi.
 */

const ROLE_KEYS = ['mahasiswa', 'admin', 'dosen'];

/** Jeda antar pergantian tab otomatis (ms). */
const ROLE_AUTOPLAY_MS = 5000;

const ROLE_TAB_BASE_CLASSES =
    'role-tab-btn relative z-10 flex-1 rounded-xl px-3 py-2.5 text-center font-title-sm text-title-sm transition-colors duration-200';

let roleKeys = ROLE_KEYS;
let roleTimer = null;
let roleAutoplayAllowed = true;

function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

/** Geser indikator biru ke posisi tab aktif (ukuran ikut, aman saat teks wrap). */
function moveRoleIndicator(button) {
    const indicator = document.getElementById('role-tab-indicator');
    if (!indicator || !button) return;

    indicator.classList.remove('opacity-0');
    indicator.style.width = `${button.offsetWidth}px`;
    indicator.style.height = `${button.offsetHeight}px`;
    indicator.style.transform = `translate(${button.offsetLeft}px, ${button.offsetTop}px)`;
}

function stopRoleAutoplay() {
    if (roleTimer !== null) {
        clearInterval(roleTimer);
        roleTimer = null;
    }
}

function startRoleAutoplay() {
    stopRoleAutoplay();

    if (!roleAutoplayAllowed || prefersReducedMotion() || roleKeys.length < 2) return;

    roleTimer = setInterval(() => {
        // Jangan putar saat tab browser tidak terlihat.
        if (document.hidden) return;

        const current = document.querySelector('[data-role-tab][aria-selected="true"]');
        const nextIndex = (roleKeys.indexOf(current?.dataset.roleTab) + 1) % roleKeys.length;

        setActiveRole(roleKeys[nextIndex]);
    }, ROLE_AUTOPLAY_MS);
}

function setActiveRole(roleKey, { focus = false } = {}) {
    if (!roleKeys.includes(roleKey)) return;

    let activeButton = null;

    roleKeys.forEach((key) => {
        const panel = document.getElementById(`role-panel-${key}`);
        const button = document.getElementById(`tab-btn-${key}`);

        const isActive = key === roleKey;

        if (panel) panel.classList.toggle('hidden', !isActive);

        if (button) {
            button.className =
                ROLE_TAB_BASE_CLASSES +
                (isActive
                    ? ' text-on-primary'
                    : ' text-on-surface-variant hover:text-on-surface');
            button.tabIndex = isActive ? 0 : -1;
            button.setAttribute('aria-selected', isActive ? 'true' : 'false');

            if (isActive) activeButton = button;
        }
    });

    moveRoleIndicator(activeButton);

    if (focus && activeButton) activeButton.focus();
}

function toggleFaq(id, trigger) {
    const body = document.getElementById(`faq-body-${id}`);
    if (!body) return;

    const isOpen = !body.classList.contains('hidden');
    body.classList.toggle('hidden', isOpen);

    if (trigger) {
        trigger.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
    }
}

function closeMobileMenu() {
    const menu = document.getElementById('landing-mobile-menu');
    const trigger = document.getElementById('landing-menu-trigger');

    if (menu) menu.classList.add('hidden');
    if (trigger) trigger.setAttribute('aria-expanded', 'false');
}

function initLandingPage() {
    // Tab pratinjau per peran
    const tablist = document.querySelector('[data-role-tablist]');
    const roleButtons = Array.from(document.querySelectorAll('[data-role-tab]'));

    // Kunci tab ikut markup Blade supaya tidak perlu hardycode dua kali.
    if (roleButtons.length) {
        roleKeys = roleButtons.map((button) => button.dataset.roleTab);
    }

    roleButtons.forEach((button) => {
        button.addEventListener('click', () => {
            setActiveRole(button.dataset.roleTab);
            if (window.innerWidth < 1280) closeMobileMenu();
            // Klik manual: hitung ulang jeda, jangan langsung meloncat.
            startRoleAutoplay();
        });
    });

    // Tab/usr keyboard: panah kiri-kanan, Home, End (pola ARIA tablist)
    if (tablist) {
        tablist.addEventListener('keydown', (event) => {
            const offset = { ArrowRight: 1, ArrowLeft: -1 }[event.key];
            const currentIndex = roleKeys.indexOf(
                document.querySelector('[data-role-tab][aria-selected="true"]')?.dataset.roleTab
            );

            let nextIndex = null;

            if (offset !== undefined && currentIndex >= 0) {
                nextIndex = (currentIndex + offset + roleKeys.length) % roleKeys.length;
            } else if (event.key === 'Home') {
                nextIndex = 0;
            } else if (event.key === 'End') {
                nextIndex = roleKeys.length - 1;
            }

            if (nextIndex === null) return;

            event.preventDefault();
            setActiveRole(roleKeys[nextIndex], { focus: true });
            startRoleAutoplay();
        });

        // Jangan putar otomatis selama kursor/fokus pengguna ada di dalam switcher.
        tablist.addEventListener('mouseenter', () => {
            roleAutoplayAllowed = false;
            stopRoleAutoplay();
        });
        tablist.addEventListener('mouseleave', () => {
            roleAutoplayAllowed = true;
            startRoleAutoplay();
        });
        tablist.addEventListener('focusin', stopRoleAutoplay);
        tablist.addEventListener('focusout', (event) => {
            if (!tablist.contains(event.relatedTarget)) startRoleAutoplay();
        });
    }

    // Section di luar viewport -> tidak perlu memutar tab yang tidak terlihat.
    if (tablist && 'IntersectionObserver' in window) {
        new IntersectionObserver(
            ([entry]) => {
                if (entry.isIntersecting) {
                    startRoleAutoplay();
                } else {
                    stopRoleAutoplay();
                }
            },
            { threshold: 0.25 }
        ).observe(tablist);
    }

    // Tandai tab pertama aktif + tempatkan indikator (hanya sekali).
    setActiveRole(roleKeys[0]);
    startRoleAutoplay();

    // Accordion FAQ
    document.querySelectorAll('[data-faq-trigger]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            toggleFaq(trigger.dataset.faqTrigger, trigger);
        });
    });

    // Menu mobile
    const menuTrigger = document.getElementById('landing-menu-trigger');
    if (menuTrigger) {
        menuTrigger.addEventListener('click', () => {
            const menu = document.getElementById('landing-mobile-menu');
            if (!menu) return;

            const willOpen = menu.classList.contains('hidden');
            menu.classList.toggle('hidden', !willOpen);
            menuTrigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });
    }

    // Tutup menu mobile saat navigasi ke anchor atau saat resize ke desktop
    document.querySelectorAll('#landing-mobile-menu a[href^="#"]').forEach((link) => {
        link.addEventListener('click', closeMobileMenu);
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1280) closeMobileMenu();

        // Lebar tab berubah setelah resize -> indicator ikut digeser.
        moveRoleIndicator(document.querySelector('[data-role-tab][aria-selected="true"]'));
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLandingPage);
} else {
    initLandingPage();
}
