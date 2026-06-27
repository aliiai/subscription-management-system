import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import Chart from 'chart.js/auto';

gsap.registerPlugin(ScrollTrigger);

window.gsap = gsap;
window.ScrollTrigger = ScrollTrigger;

function initAnimations() {
    // Entrance timeline for the hero section.
    const hero = gsap.timeline({ defaults: { ease: 'power3.out', duration: 0.9 } });

    hero.from('[data-animate="hero-badge"]', { y: 20, opacity: 0 })
        .from('[data-animate="hero-title"]', { y: 40, opacity: 0 }, '-=0.5')
        .from('[data-animate="hero-subtitle"]', { y: 30, opacity: 0 }, '-=0.6')
        .from('[data-animate="hero-cta"]', { y: 20, opacity: 0, stagger: 0.15 }, '-=0.5');

    // Continuous floating animation for the orb decoration.
    gsap.to('[data-animate="orb"]', {
        y: -24,
        duration: 2.4,
        ease: 'sine.inOut',
        repeat: -1,
        yoyo: true,
    });

    // Scroll-triggered reveal for each feature card.
    gsap.utils.toArray('[data-animate="card"]').forEach((card) => {
        gsap.from(card, {
            y: 60,
            opacity: 0,
            duration: 0.8,
            ease: 'power2.out',
            scrollTrigger: {
                trigger: card,
                start: 'top 85%',
                toggleActions: 'play none none reverse',
            },
        });
    });

    // Animated counters tied to scroll position.
    gsap.utils.toArray('[data-counter]').forEach((el) => {
        const target = Number(el.dataset.counter) || 0;
        const counter = { value: 0 };

        gsap.to(counter, {
            value: target,
            duration: 1.6,
            ease: 'power1.out',
            scrollTrigger: {
                trigger: el,
                start: 'top 90%',
                toggleActions: 'play none none none',
            },
            onUpdate: () => {
                el.textContent = Math.round(counter.value).toLocaleString();
            },
        });
    });

    // Entrance for auth / dashboard cards.
    gsap.utils.toArray('[data-animate="auth-card"]').forEach((card) => {
        gsap.from(card, { y: 24, opacity: 0, duration: 0.7, ease: 'power3.out' });
    });
}

// Smart toggle between the login and registration panels on the auth portal.
function initAuthPortal() {
    const tabs = document.querySelectorAll('[data-auth-tab]');
    const panels = document.querySelectorAll('[data-auth-panel]');
    const indicator = document.querySelector('[data-auth-indicator]');
    const subtitle = document.querySelector('[data-auth-subtitle]');
    const title = document.querySelector('[data-auth-title]');

    if (!tabs.length || !panels.length) {
        return;
    }

    const copy = {
        login: {
            title: 'حيّاك الله من جديد',
            subtitle: 'سجّل دخولك وكمّل من وين وقفت.',
        },
        register: {
            title: 'سجّل شركتك بثواني',
            subtitle: 'أنشئ حساب شركتك وابدأ تدير اشتراكاتك على طول.',
        },
    };

    const card = document.querySelector('[data-animate="auth-card"]');
    let isAnimating = false;

    const syncTabs = (mode) => {
        tabs.forEach((tab) => {
            const isActive = tab.dataset.authTab === mode;
            tab.classList.toggle('text-[#0a4589]', isActive);
            tab.classList.toggle('text-slate-500', !isActive);
        });

        if (indicator) {
            indicator.classList.toggle('-translate-x-full', mode === 'register');
        }
    };

    const swapCopy = (mode) => {
        if (!copy[mode]) {
            return;
        }
        const heads = [title, subtitle].filter(Boolean);
        gsap.to(heads, {
            opacity: 0,
            y: -6,
            duration: 0.15,
            ease: 'power1.in',
            onComplete: () => {
                if (title) {
                    title.textContent = copy[mode].title;
                }
                if (subtitle) {
                    subtitle.textContent = copy[mode].subtitle;
                }
                gsap.fromTo(heads, { opacity: 0, y: 6 }, { opacity: 1, y: 0, duration: 0.25, ease: 'power2.out' });
            },
        });
    };

    const activate = (mode) => {
        const current = Array.from(panels).find((p) => !p.classList.contains('hidden'));
        const next = Array.from(panels).find((p) => p.dataset.authPanel === mode);

        if (!next || current === next || isAnimating) {
            return;
        }

        isAnimating = true;
        syncTabs(mode);
        swapCopy(mode);

        const tl = gsap.timeline({
            onComplete: () => {
                isAnimating = false;
            },
        });

        // Animate the outgoing panel away.
        if (current) {
            tl.to(current, { opacity: 0, y: -10, duration: 0.18, ease: 'power1.in' });
        }

        // Swap panels and tween the card height to avoid layout jump.
        tl.add(() => {
            const startHeight = card ? card.offsetHeight : 0;

            if (current) {
                current.classList.add('hidden');
            }
            next.classList.remove('hidden');
            gsap.set(next, { opacity: 0, y: 12 });

            if (card) {
                const endHeight = card.offsetHeight;
                gsap.fromTo(
                    card,
                    { height: startHeight },
                    {
                        height: endHeight,
                        duration: 0.35,
                        ease: 'power3.out',
                        onComplete: () => gsap.set(card, { clearProps: 'height' }),
                    },
                );
            }
        });

        // Reveal the incoming panel.
        tl.to(next, { opacity: 1, y: 0, duration: 0.3, ease: 'power2.out' }, '>-0.08');
    };

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => activate(tab.dataset.authTab));
    });

    // Staggered entrance for the brand panel content.
    const brandItems = gsap.utils.toArray('[data-animate="brand"]');

    if (brandItems.length) {
        gsap.from(brandItems, {
            x: 30,
            opacity: 0,
            duration: 0.7,
            ease: 'power3.out',
            stagger: 0.12,
        });
    }
}

// Header dropdown menus (language, notifications, account) with smooth open/close.
function initDropdowns() {
    const dropdowns = Array.from(document.querySelectorAll('[data-dropdown]'));

    if (!dropdowns.length) {
        return;
    }

    const closeMenu = (menu) => {
        if (!menu || menu.classList.contains('hidden')) {
            return;
        }
        gsap.to(menu, {
            opacity: 0,
            y: -6,
            duration: 0.15,
            ease: 'power1.in',
            onComplete: () => menu.classList.add('hidden'),
        });
    };

    const closeAll = (except) => {
        dropdowns.forEach((d) => {
            if (d === except) {
                return;
            }
            closeMenu(d.querySelector('[data-dropdown-menu]'));
        });
    };

    dropdowns.forEach((dropdown) => {
        const trigger = dropdown.querySelector('[data-dropdown-trigger]');
        const menu = dropdown.querySelector('[data-dropdown-menu]');

        if (!trigger || !menu) {
            return;
        }

        trigger.addEventListener('click', (event) => {
            event.stopPropagation();
            const isHidden = menu.classList.contains('hidden');
            closeAll(dropdown);

            if (isHidden) {
                menu.classList.remove('hidden');
                gsap.fromTo(
                    menu,
                    { opacity: 0, y: -8 },
                    { opacity: 1, y: 0, duration: 0.22, ease: 'power2.out' },
                );
            } else {
                closeMenu(menu);
            }
        });

        menu.addEventListener('click', (event) => event.stopPropagation());
    });

    document.addEventListener('click', () => closeAll(null));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAll(null);
        }
    });
}

// Company dashboard shell: sidebar collapse, submenu accordion, dark mode.
function initDashboard() {
    const sidebar = document.getElementById('sidebar');

    if (!sidebar) {
        return;
    }

    const root = document.documentElement;

    // Preserve the sidebar scroll position across full-page navigations.
    const nav = sidebar.querySelector('nav');
    if (nav) {
        const SCROLL_KEY = 'sidebarScroll';
        try {
            const saved = sessionStorage.getItem(SCROLL_KEY);
            if (saved !== null) {
                nav.scrollTop = parseInt(saved, 10) || 0;
            }
        } catch (e) {}

        let scrollFrame = null;
        const persistScroll = () => {
            try {
                sessionStorage.setItem(SCROLL_KEY, String(nav.scrollTop));
            } catch (e) {}
        };

        nav.addEventListener('scroll', () => {
            if (scrollFrame) {
                return;
            }
            scrollFrame = requestAnimationFrame(() => {
                persistScroll();
                scrollFrame = null;
            });
        });

        nav.querySelectorAll('a[href]').forEach((link) => {
            link.addEventListener('click', persistScroll);
        });

        window.addEventListener('beforeunload', persistScroll);
    }

    const resetSubmenus = () => {
        document.querySelectorAll('[data-submenu-toggle]').forEach((btn) => {
            const wrap = btn.nextElementSibling;
            const chevron = btn.querySelector('[data-chevron]');
            btn.setAttribute('aria-expanded', 'false');
            if (wrap) {
                wrap.style.display = 'none';
                wrap.style.height = '';
            }
            if (chevron) {
                gsap.set(chevron, { rotate: 0 });
            }
        });
    };

    // Collapse / expand (desktop) or open / close drawer (mobile).
    const isDesktop = () => window.matchMedia('(min-width: 1024px)').matches;
    const closeMobileSidebar = () => {
        root.dataset.mobileOpen = 'false';
    };

    const collapseBtn = document.querySelector('[data-collapse-toggle]');
    collapseBtn?.addEventListener('click', () => {
        if (!isDesktop()) {
            root.dataset.mobileOpen = root.dataset.mobileOpen === 'true' ? 'false' : 'true';
            return;
        }

        const collapsed = root.dataset.collapsed === 'true';
        root.dataset.collapsed = (!collapsed).toString();
        localStorage.setItem('sidebar-collapsed', (!collapsed).toString());

        if (!collapsed) {
            resetSubmenus();
        }
    });

    // Close the mobile drawer via the backdrop, Escape, or navigating.
    document.querySelector('[data-sidebar-overlay]')?.addEventListener('click', closeMobileSidebar);
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && root.dataset.mobileOpen === 'true') {
            closeMobileSidebar();
        }
    });
    sidebar.querySelectorAll('a[href]').forEach((link) => {
        link.addEventListener('click', () => {
            if (!isDesktop()) {
                closeMobileSidebar();
            }
        });
    });
    window.addEventListener('resize', () => {
        if (isDesktop()) {
            closeMobileSidebar();
        }
    });

    // Submenu accordion.
    document.querySelectorAll('[data-submenu-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (root.dataset.collapsed === 'true') {
                root.dataset.collapsed = 'false';
                localStorage.setItem('sidebar-collapsed', 'false');
            }

            const wrap = btn.nextElementSibling;
            const chevron = btn.querySelector('[data-chevron]');
            const isOpen = btn.getAttribute('aria-expanded') === 'true';
            btn.setAttribute('aria-expanded', (!isOpen).toString());

            if (!wrap) {
                return;
            }

            if (!isOpen) {
                wrap.style.display = 'block';
                gsap.fromTo(
                    wrap,
                    { height: 0, opacity: 0 },
                    { height: 'auto', opacity: 1, duration: 0.3, ease: 'power2.out' },
                );
                gsap.to(chevron, { rotate: 180, duration: 0.3 });
            } else {
                gsap.to(wrap, {
                    height: 0,
                    opacity: 0,
                    duration: 0.25,
                    ease: 'power2.in',
                    onComplete: () => {
                        wrap.style.display = 'none';
                    },
                });
                gsap.to(chevron, { rotate: 0, duration: 0.25 });
            }
        });
    });

    // Dark mode toggle.
    const themeBtn = document.querySelector('[data-theme-toggle]');
    themeBtn?.addEventListener('click', () => {
        const isDark = root.classList.toggle('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
    });

    // Staggered entrance for nav groups — only on the first load of the
    // session, so it does not replay on every page navigation.
    let introPlayed = false;
    try {
        introPlayed = sessionStorage.getItem('dashboardIntro') === '1';
    } catch (e) {
        introPlayed = false;
    }

    if (!introPlayed) {
        gsap.from('#sidebar [data-nav-item]', {
            x: 18,
            opacity: 0,
            duration: 0.4,
            ease: 'power2.out',
            stagger: 0.05,
        });

        try {
            sessionStorage.setItem('dashboardIntro', '1');
        } catch (e) {}
    }
}

// Subscription plans: create / edit / delete modals with GSAP transitions.
function initPlans() {
    const planModal = document.querySelector('[data-modal="plan"]');
    const deleteModal = document.querySelector('[data-modal="delete"]');

    if (!planModal && !deleteModal) {
        return;
    }

    const open = (modal) => {
        if (!modal) {
            return;
        }
        modal.classList.remove('hidden');
        const overlay = modal.querySelector('[data-modal-overlay]');
        const panel = modal.querySelector('[data-modal-panel]');
        gsap.fromTo(overlay, { opacity: 0 }, { opacity: 1, duration: 0.2 });
        gsap.fromTo(panel, { opacity: 0, y: 24, scale: 0.96 }, { opacity: 1, y: 0, scale: 1, duration: 0.3, ease: 'power3.out' });
        document.body.style.overflow = 'hidden';
    };

    const close = (modal) => {
        if (!modal || modal.classList.contains('hidden')) {
            return;
        }
        const overlay = modal.querySelector('[data-modal-overlay]');
        const panel = modal.querySelector('[data-modal-panel]');
        gsap.to(overlay, { opacity: 0, duration: 0.2 });
        gsap.to(panel, {
            opacity: 0,
            y: 16,
            scale: 0.97,
            duration: 0.2,
            ease: 'power2.in',
            onComplete: () => {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            },
        });
    };

    const closeAll = () => [planModal, deleteModal].forEach((m) => close(m));

    const form = planModal?.querySelector('[data-plan-form]');
    const base = form?.dataset.base;
    const title = planModal?.querySelector('[data-plan-modal-title]');
    const submitText = planModal?.querySelector('[data-plan-submit-text]');
    const methodInput = form?.querySelector('[data-method]');
    const idInput = form?.querySelector('[data-plan-id]');
    const activeInput = form?.querySelector('[data-plan-active]');
    const activeLabel = form?.querySelector('[data-active-label]');

    const setField = (name, value) => {
        const el = form?.querySelector(`[name="${name}"]`);
        if (el) {
            el.value = value ?? '';
        }
    };

    const setRadio = (name, value) => {
        form?.querySelectorAll(`[name="${name}"]`).forEach((radio) => {
            radio.checked = radio.value === value;
        });
    };

    // Custom currency dropdown (flag images + symbol).
    const currencyWrap = form?.querySelector('[data-currency]');
    const currencyInput = currencyWrap?.querySelector('[data-currency-input]');
    const currencyButton = currencyWrap?.querySelector('[data-currency-button]');
    const currencyMenu = currencyWrap?.querySelector('[data-currency-menu]');
    const currencyFlag = currencyWrap?.querySelector('[data-currency-flag]');
    const currencySymbol = currencyWrap?.querySelector('[data-currency-symbol]');

    const setCurrency = (code) => {
        const option = currencyMenu?.querySelector(`[data-code="${code}"]`);
        if (!option || !currencyInput) {
            return;
        }
        currencyInput.value = code;
        if (currencyFlag) {
            currencyFlag.src = option.dataset.flag;
        }
        if (currencySymbol) {
            currencySymbol.textContent = option.dataset.symbol;
        }
    };

    const closeCurrencyMenu = () => currencyMenu?.classList.add('hidden');

    currencyButton?.addEventListener('click', (event) => {
        event.stopPropagation();
        const isHidden = currencyMenu.classList.contains('hidden');
        if (isHidden) {
            currencyMenu.classList.remove('hidden');
            gsap.fromTo(currencyMenu, { opacity: 0, y: -6 }, { opacity: 1, y: 0, duration: 0.18, ease: 'power2.out' });
        } else {
            closeCurrencyMenu();
        }
    });

    currencyMenu?.querySelectorAll('[data-currency-option]').forEach((option) => {
        option.addEventListener('click', () => {
            setCurrency(option.dataset.code);
            closeCurrencyMenu();
        });
    });

    document.addEventListener('click', closeCurrencyMenu);

    const syncActiveLabel = () => {
        if (activeLabel && activeInput) {
            activeLabel.textContent = activeInput.checked ? 'نشطة' : 'متوقفة';
        }
    };

    activeInput?.addEventListener('change', syncActiveLabel);

    const toCreate = () => {
        form.action = base;
        methodInput.value = 'POST';
        idInput.value = '';
        title.textContent = 'إنشاء خطة جديدة';
        if (submitText) {
            submitText.textContent = 'حفظ الخطة';
        }
        ['name', 'description', 'price', 'features'].forEach((n) => setField(n, ''));
        setCurrency('SAR');
        setRadio('billing_cycle', 'monthly');
        if (activeInput) {
            activeInput.checked = true;
        }
        syncActiveLabel();
    };

    const toEdit = (data) => {
        form.action = `${base}/${data.id}`;
        methodInput.value = 'PUT';
        idInput.value = data.id;
        title.textContent = 'تعديل الخطة';
        if (submitText) {
            submitText.textContent = 'تحديث الخطة';
        }
        setField('name', data.name);
        setField('description', data.description);
        setField('price', data.price);
        setCurrency(data.currency);
        setRadio('billing_cycle', data.billing);

        let features = [];
        try {
            features = JSON.parse(data.features || '[]');
        } catch (e) {
            features = [];
        }
        setField('features', Array.isArray(features) ? features.join('\n') : '');

        if (activeInput) {
            activeInput.checked = data.active === '1';
        }
        syncActiveLabel();
    };

    const deleteForm = deleteModal?.querySelector('[data-delete-form]');
    const deleteName = deleteModal?.querySelector('[data-delete-name]');

    // Delegated triggers so they keep working after the table is re-rendered via AJAX.
    document.addEventListener('click', (event) => {
        const createBtn = event.target.closest('[data-plan-create]');
        if (createBtn) {
            toCreate();
            open(planModal);
            return;
        }

        const editBtn = event.target.closest('[data-plan-edit]');
        if (editBtn) {
            toEdit(editBtn.dataset);
            open(planModal);
            return;
        }

        const deleteBtn = event.target.closest('[data-plan-delete]');
        if (deleteBtn) {
            if (deleteForm) {
                deleteForm.action = `${base}/${deleteBtn.dataset.id}`;
            }
            if (deleteName) {
                deleteName.textContent = deleteBtn.dataset.name;
            }
            open(deleteModal);
        }
    });

    document.querySelectorAll('[data-modal-close]').forEach((btn) => btn.addEventListener('click', closeAll));
    document.querySelectorAll('[data-modal-overlay]').forEach((overlay) => overlay.addEventListener('click', closeAll));
    document.querySelectorAll('[data-modal-dismiss]').forEach((area) => {
        area.addEventListener('click', (event) => {
            if (event.target === area) {
                closeAll();
            }
        });
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAll();
        }
    });

    // Re-open the modal after a server-side validation error.
    if (window.__openPlanModal && form) {
        if (window.__openPlanModal.mode === 'edit') {
            form.action = `${base}/${window.__openPlanModal.id}`;
            methodInput.value = 'PUT';
            title.textContent = 'تعديل الخطة';
            if (submitText) {
                submitText.textContent = 'تحديث الخطة';
            }
        }
        syncActiveLabel();
        open(planModal);
    }

    // Auto-dismiss the status toast.
    const toast = document.querySelector('[data-toast]');
    if (toast) {
        gsap.fromTo(toast, { opacity: 0, y: -8 }, { opacity: 1, y: 0, duration: 0.3 });
        setTimeout(() => gsap.to(toast, { opacity: 0, y: -8, duration: 0.3, onComplete: () => toast.remove() }), 4000);
    }
}

// Company plans: AJAX search, status / cycle filters and pagination without full page reloads.
function initPlansTable() {
    const root = document.querySelector('[data-plans]');
    if (!root) {
        return;
    }

    const baseUrl = root.dataset.url;
    const results = root.querySelector('[data-plans-results]');
    const searchInput = root.querySelector('[data-plans-search]');
    const statusSelect = root.querySelector('[data-plans-status]');
    const cycleSelect = root.querySelector('[data-plans-cycle]');
    const sortSelect = root.querySelector('[data-plans-sort]');
    const loading = root.querySelector('[data-plans-loading]');

    let page = 1;
    let debounceId = null;
    let controller = null;

    const buildUrl = () => {
        const params = new URLSearchParams();
        const q = searchInput?.value.trim();
        if (q) {
            params.set('q', q);
        }
        if (statusSelect?.value) {
            params.set('status', statusSelect.value);
        }
        if (cycleSelect?.value) {
            params.set('cycle', cycleSelect.value);
        }
        if (sortSelect?.value) {
            params.set('sort', sortSelect.value);
        }
        if (page > 1) {
            params.set('page', page);
        }
        const query = params.toString();
        return query ? `${baseUrl}?${query}` : baseUrl;
    };

    const fetchResults = async () => {
        if (controller) {
            controller.abort();
        }
        controller = new AbortController();
        loading?.classList.remove('hidden');

        const url = buildUrl();

        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: controller.signal,
            });
            if (!response.ok) {
                throw new Error('request failed');
            }
            const html = await response.text();
            results.innerHTML = html;
            gsap.fromTo(results, { opacity: 0.4 }, { opacity: 1, duration: 0.25 });
            window.history.replaceState({}, '', url);
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error(error);
            }
        } finally {
            loading?.classList.add('hidden');
        }
    };

    searchInput?.addEventListener('input', () => {
        clearTimeout(debounceId);
        debounceId = setTimeout(() => {
            page = 1;
            fetchResults();
        }, 350);
    });

    [statusSelect, cycleSelect, sortSelect].forEach((select) => {
        select?.addEventListener('change', () => {
            page = 1;
            fetchResults();
        });
    });

    // Delegated pagination clicks (the buttons live inside the swapped results).
    results.addEventListener('click', (event) => {
        const pageBtn = event.target.closest('[data-plans-page]');
        if (!pageBtn || pageBtn.disabled) {
            return;
        }
        const target = parseInt(pageBtn.dataset.plansPage, 10);
        if (!Number.isNaN(target) && target >= 1) {
            page = target;
            fetchResults();
            const scroller = results.querySelector('.brand-scroll');
            if (scroller) {
                scroller.scrollTop = 0;
            }
        }
    });
}

// Admin company-users: edit / delete modals with GSAP transitions.
function initCompanyUsers() {
    const editModal = document.querySelector('[data-modal="user-edit"]');
    const deleteModal = document.querySelector('[data-modal="user-delete"]');

    if (!editModal && !deleteModal) {
        return;
    }

    const open = (modal) => {
        if (!modal) {
            return;
        }
        modal.classList.remove('hidden');
        const overlay = modal.querySelector('[data-modal-overlay]');
        const panel = modal.querySelector('[data-modal-panel]');
        gsap.fromTo(overlay, { opacity: 0 }, { opacity: 1, duration: 0.2 });
        gsap.fromTo(panel, { opacity: 0, y: 24, scale: 0.96 }, { opacity: 1, y: 0, scale: 1, duration: 0.3, ease: 'power3.out' });
        document.body.style.overflow = 'hidden';
    };

    const close = (modal) => {
        if (!modal || modal.classList.contains('hidden')) {
            return;
        }
        const overlay = modal.querySelector('[data-modal-overlay]');
        const panel = modal.querySelector('[data-modal-panel]');
        gsap.to(overlay, { opacity: 0, duration: 0.2 });
        gsap.to(panel, {
            opacity: 0,
            y: 16,
            scale: 0.97,
            duration: 0.2,
            ease: 'power2.in',
            onComplete: () => {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            },
        });
    };

    const closeAll = () => [editModal, deleteModal].forEach((m) => close(m));

    const form = editModal?.querySelector('[data-user-form]');
    const base = form?.dataset.base;
    const idInput = form?.querySelector('[data-user-id]');
    const ownerInput = form?.querySelector('[data-user-owner]');
    const ownerLabel = form?.querySelector('[data-owner-label]');
    const companyLabel = editModal?.querySelector('[data-user-company]');

    const setField = (name, value) => {
        const el = form?.querySelector(`[name="${name}"]`);
        if (el) {
            el.value = value ?? '';
        }
    };

    const syncOwnerLabel = () => {
        if (ownerLabel && ownerInput) {
            ownerLabel.textContent = ownerInput.checked ? 'مدير الشركة' : 'موظف';
        }
    };

    ownerInput?.addEventListener('change', syncOwnerLabel);

    document.querySelectorAll('[data-user-edit]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const data = btn.dataset;
            form.action = `${base}/${data.id}`;
            if (idInput) {
                idInput.value = data.id;
            }
            setField('name', data.name);
            setField('email', data.email);
            setField('password', '');
            if (ownerInput) {
                ownerInput.checked = data.owner === '1';
            }
            if (companyLabel) {
                companyLabel.textContent = data.company || '—';
            }
            syncOwnerLabel();
            open(editModal);
        });
    });

    const deleteForm = deleteModal?.querySelector('[data-delete-form]');
    const deleteName = deleteModal?.querySelector('[data-delete-name]');

    document.querySelectorAll('[data-user-delete]').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (deleteForm) {
                deleteForm.action = `${base}/${btn.dataset.id}`;
            }
            if (deleteName) {
                deleteName.textContent = btn.dataset.name;
            }
            open(deleteModal);
        });
    });

    editModal?.querySelectorAll('[data-modal-close]').forEach((btn) => btn.addEventListener('click', closeAll));
    deleteModal?.querySelectorAll('[data-modal-close]').forEach((btn) => btn.addEventListener('click', closeAll));
    document.querySelectorAll('[data-modal-overlay]').forEach((overlay) => overlay.addEventListener('click', closeAll));
    document.querySelectorAll('[data-modal-dismiss]').forEach((area) => {
        area.addEventListener('click', (event) => {
            if (event.target === area) {
                closeAll();
            }
        });
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAll();
        }
    });

    // Re-open the edit modal after a server-side validation error.
    if (window.__openUserModal && form) {
        form.action = `${base}/${window.__openUserModal.id}`;
        if (idInput) {
            idInput.value = window.__openUserModal.id;
        }
        syncOwnerLabel();
        open(editModal);
    }

    // Auto-dismiss the status toast.
    const toast = document.querySelector('[data-toast]');
    if (toast) {
        gsap.fromTo(toast, { opacity: 0, y: -8 }, { opacity: 1, y: 0, duration: 0.3 });
        setTimeout(() => gsap.to(toast, { opacity: 0, y: -8, duration: 0.3, onComplete: () => toast.remove() }), 4000);
    }
}

// Admin companies: edit / delete modals with GSAP transitions.
function initCompanies() {
    const editModal = document.querySelector('[data-modal="company-edit"]');
    const deleteModal = document.querySelector('[data-modal="company-delete"]');

    if (!editModal && !deleteModal) {
        return;
    }

    const open = (modal) => {
        if (!modal) {
            return;
        }
        modal.classList.remove('hidden');
        const overlay = modal.querySelector('[data-modal-overlay]');
        const panel = modal.querySelector('[data-modal-panel]');
        gsap.fromTo(overlay, { opacity: 0 }, { opacity: 1, duration: 0.2 });
        gsap.fromTo(panel, { opacity: 0, y: 24, scale: 0.96 }, { opacity: 1, y: 0, scale: 1, duration: 0.3, ease: 'power3.out' });
        document.body.style.overflow = 'hidden';
    };

    const close = (modal) => {
        if (!modal || modal.classList.contains('hidden')) {
            return;
        }
        const overlay = modal.querySelector('[data-modal-overlay]');
        const panel = modal.querySelector('[data-modal-panel]');
        gsap.to(overlay, { opacity: 0, duration: 0.2 });
        gsap.to(panel, {
            opacity: 0,
            y: 16,
            scale: 0.97,
            duration: 0.2,
            ease: 'power2.in',
            onComplete: () => {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            },
        });
    };

    const closeAll = () => [editModal, deleteModal].forEach((m) => close(m));

    const form = editModal?.querySelector('[data-company-form]');
    const base = form?.dataset.base;
    const idInput = form?.querySelector('[data-company-id]');
    const activeInput = form?.querySelector('[data-company-active]');
    const statusLabel = form?.querySelector('[data-status-label]');

    const setField = (name, value) => {
        const el = form?.querySelector(`[name="${name}"]`);
        if (el) {
            el.value = value ?? '';
        }
    };

    const syncStatusLabel = () => {
        if (statusLabel && activeInput) {
            statusLabel.textContent = activeInput.checked ? 'نشطة' : 'موقوفة';
        }
    };

    activeInput?.addEventListener('change', syncStatusLabel);

    document.querySelectorAll('[data-company-edit]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const data = btn.dataset;
            form.action = `${base}/${data.id}`;
            if (idInput) {
                idInput.value = data.id;
            }
            setField('name', data.name);
            setField('email', data.email);
            setField('phone', data.phone);
            if (activeInput) {
                activeInput.checked = data.active === '1';
            }
            syncStatusLabel();
            open(editModal);
        });
    });

    const deleteForm = deleteModal?.querySelector('[data-delete-form]');
    const deleteName = deleteModal?.querySelector('[data-delete-name]');

    document.querySelectorAll('[data-company-delete]').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (deleteForm) {
                deleteForm.action = `${base}/${btn.dataset.id}`;
            }
            if (deleteName) {
                deleteName.textContent = btn.dataset.name;
            }
            open(deleteModal);
        });
    });

    editModal?.querySelectorAll('[data-modal-close]').forEach((btn) => btn.addEventListener('click', closeAll));
    deleteModal?.querySelectorAll('[data-modal-close]').forEach((btn) => btn.addEventListener('click', closeAll));
    document.querySelectorAll('[data-modal-overlay]').forEach((overlay) => overlay.addEventListener('click', closeAll));
    document.querySelectorAll('[data-modal-dismiss]').forEach((area) => {
        area.addEventListener('click', (event) => {
            if (event.target === area) {
                closeAll();
            }
        });
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAll();
        }
    });

    // Re-open the edit modal after a server-side validation error.
    if (window.__openCompanyModal && form) {
        form.action = `${base}/${window.__openCompanyModal.id}`;
        if (idInput) {
            idInput.value = window.__openCompanyModal.id;
        }
        syncStatusLabel();
        open(editModal);
    }

    // Auto-dismiss the status toast.
    const toast = document.querySelector('[data-toast]');
    if (toast) {
        gsap.fromTo(toast, { opacity: 0, y: -8 }, { opacity: 1, y: 0, duration: 0.3 });
        setTimeout(() => gsap.to(toast, { opacity: 0, y: -8, duration: 0.3, onComplete: () => toast.remove() }), 4000);
    }
}

// Company customers: create / edit / delete modals with GSAP transitions.
function initCustomers() {
    const editModal = document.querySelector('[data-modal="customer"]');
    const deleteModal = document.querySelector('[data-modal="customer-delete"]');

    if (!editModal && !deleteModal) {
        return;
    }

    const open = (modal) => {
        if (!modal) {
            return;
        }
        modal.classList.remove('hidden');
        const overlay = modal.querySelector('[data-modal-overlay]');
        const panel = modal.querySelector('[data-modal-panel]');
        gsap.fromTo(overlay, { opacity: 0 }, { opacity: 1, duration: 0.2 });
        gsap.fromTo(panel, { opacity: 0, y: 24, scale: 0.96 }, { opacity: 1, y: 0, scale: 1, duration: 0.3, ease: 'power3.out' });
        document.body.style.overflow = 'hidden';
    };

    const close = (modal) => {
        if (!modal || modal.classList.contains('hidden')) {
            return;
        }
        const overlay = modal.querySelector('[data-modal-overlay]');
        const panel = modal.querySelector('[data-modal-panel]');
        gsap.to(overlay, { opacity: 0, duration: 0.2 });
        gsap.to(panel, {
            opacity: 0,
            y: 16,
            scale: 0.97,
            duration: 0.2,
            ease: 'power2.in',
            onComplete: () => {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            },
        });
    };

    const closeAll = () => [editModal, deleteModal].forEach((m) => close(m));

    const form = editModal?.querySelector('[data-customer-form]');
    const base = form?.dataset.base;
    const title = editModal?.querySelector('[data-customer-modal-title]');
    const submitText = editModal?.querySelector('[data-customer-submit-text]');
    const methodInput = form?.querySelector('[data-method]');
    const idInput = form?.querySelector('[data-customer-id]');

    const setField = (name, value) => {
        const el = form?.querySelector(`[name="${name}"]`);
        if (el) {
            el.value = value ?? '';
        }
    };

    const planSelect = form?.querySelector('[name="plan_id"]');
    const startWrapper = editModal?.querySelector('[data-customer-start-wrapper]');
    const today = () => new Date().toISOString().slice(0, 10);

    // Show the start-date field only when a plan is attached.
    const toggleStart = () => {
        if (startWrapper) {
            startWrapper.classList.toggle('hidden', !planSelect?.value);
        }
    };
    planSelect?.addEventListener('change', toggleStart);

    const toCreate = () => {
        form.action = base;
        methodInput.value = 'POST';
        if (idInput) {
            idInput.value = '';
        }
        title.textContent = 'إضافة عميل جديد';
        if (submitText) {
            submitText.textContent = 'حفظ العميل';
        }
        ['name', 'email', 'phone'].forEach((n) => setField(n, ''));
        setField('plan_id', '');
        setField('start_date', today());
        toggleStart();
    };

    const toEdit = (data) => {
        form.action = `${base}/${data.id}`;
        methodInput.value = 'PUT';
        if (idInput) {
            idInput.value = data.id;
        }
        title.textContent = 'تعديل العميل';
        if (submitText) {
            submitText.textContent = 'حفظ التعديلات';
        }
        setField('name', data.name);
        setField('email', data.email);
        setField('phone', data.phone);
        setField('plan_id', data.plan || '');
        setField('start_date', (data.start || today()).slice(0, 10));
        toggleStart();
    };

    const deleteForm = deleteModal?.querySelector('[data-delete-form]');
    const deleteName = deleteModal?.querySelector('[data-delete-name]');

    // Delegated triggers so they keep working after the table is re-rendered via AJAX.
    document.addEventListener('click', (event) => {
        const createBtn = event.target.closest('[data-customer-create]');
        if (createBtn) {
            toCreate();
            open(editModal);
            return;
        }

        const editBtn = event.target.closest('[data-customer-edit]');
        if (editBtn) {
            toEdit(editBtn.dataset);
            open(editModal);
            return;
        }

        const deleteBtn = event.target.closest('[data-customer-delete]');
        if (deleteBtn) {
            if (deleteForm) {
                deleteForm.action = `${base}/${deleteBtn.dataset.id}`;
            }
            if (deleteName) {
                deleteName.textContent = deleteBtn.dataset.name;
            }
            open(deleteModal);
        }
    });

    editModal?.querySelectorAll('[data-modal-close]').forEach((btn) => btn.addEventListener('click', closeAll));
    deleteModal?.querySelectorAll('[data-modal-close]').forEach((btn) => btn.addEventListener('click', closeAll));
    document.querySelectorAll('[data-modal-overlay]').forEach((overlay) => overlay.addEventListener('click', closeAll));
    document.querySelectorAll('[data-modal-dismiss]').forEach((area) => {
        area.addEventListener('click', (event) => {
            if (event.target === area) {
                closeAll();
            }
        });
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAll();
        }
    });

    // Re-open the modal after a server-side validation error.
    if (window.__openCustomerModal && form) {
        if (window.__openCustomerModal.mode === 'edit') {
            form.action = `${base}/${window.__openCustomerModal.id}`;
            methodInput.value = 'PUT';
            title.textContent = 'تعديل العميل';
            if (submitText) {
                submitText.textContent = 'حفظ التعديلات';
            }
        }
        toggleStart();
        open(editModal);
    }

    // Auto-dismiss the status toast.
    const toast = document.querySelector('[data-toast]');
    if (toast) {
        gsap.fromTo(toast, { opacity: 0, y: -8 }, { opacity: 1, y: 0, duration: 0.3 });
        setTimeout(() => gsap.to(toast, { opacity: 0, y: -8, duration: 0.3, onComplete: () => toast.remove() }), 4000);
    }
}

// Company customers: AJAX search, plan filter and pagination without full page reloads.
function initCustomersTable() {
    const root = document.querySelector('[data-customers]');
    if (!root) {
        return;
    }

    const baseUrl = root.dataset.url;
    const results = root.querySelector('[data-customers-results]');
    const searchInput = root.querySelector('[data-customers-search]');
    const planSelect = root.querySelector('[data-customers-plan]');
    const loading = root.querySelector('[data-customers-loading]');

    let page = 1;
    let debounceId = null;
    let controller = null;

    const buildUrl = () => {
        const params = new URLSearchParams();
        const q = searchInput?.value.trim();
        const plan = planSelect?.value;
        if (q) {
            params.set('q', q);
        }
        if (plan) {
            params.set('plan', plan);
        }
        if (page > 1) {
            params.set('page', page);
        }
        const query = params.toString();
        return query ? `${baseUrl}?${query}` : baseUrl;
    };

    const fetchResults = async () => {
        if (controller) {
            controller.abort();
        }
        controller = new AbortController();
        loading?.classList.remove('hidden');

        const url = buildUrl();

        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: controller.signal,
            });
            if (!response.ok) {
                throw new Error('request failed');
            }
            const html = await response.text();
            results.innerHTML = html;
            gsap.fromTo(results, { opacity: 0.4 }, { opacity: 1, duration: 0.25 });
            window.history.replaceState({}, '', url);
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error(error);
            }
        } finally {
            loading?.classList.add('hidden');
        }
    };

    searchInput?.addEventListener('input', () => {
        clearTimeout(debounceId);
        debounceId = setTimeout(() => {
            page = 1;
            fetchResults();
        }, 350);
    });

    planSelect?.addEventListener('change', () => {
        page = 1;
        fetchResults();
    });

    // Delegated pagination clicks (the buttons live inside the swapped results).
    results.addEventListener('click', (event) => {
        const pageBtn = event.target.closest('[data-customers-page]');
        if (!pageBtn || pageBtn.disabled) {
            return;
        }
        const target = parseInt(pageBtn.dataset.customersPage, 10);
        if (!Number.isNaN(target) && target >= 1) {
            page = target;
            fetchResults();
            const scroller = results.querySelector('.brand-scroll');
            if (scroller) {
                scroller.scrollTop = 0;
            }
        }
    });
}

// Company activity log: AJAX search, type/range filters, and pagination.
function initActivityLog() {
    const root = document.querySelector('[data-activity]');
    if (!root) {
        return;
    }

    const baseUrl = root.dataset.url;
    const results = root.querySelector('[data-activity-results]');
    const searchInput = root.querySelector('[data-activity-search]');
    const typeSelect = root.querySelector('[data-activity-type]');
    const rangeSelect = root.querySelector('[data-activity-range]');
    const loading = root.querySelector('[data-activity-loading]');

    let page = 1;
    let debounceId = null;
    let controller = null;

    const buildUrl = () => {
        const params = new URLSearchParams();
        const q = searchInput?.value.trim();
        if (q) {
            params.set('q', q);
        }
        if (typeSelect?.value) {
            params.set('type', typeSelect.value);
        }
        if (rangeSelect?.value) {
            params.set('range', rangeSelect.value);
        }
        if (page > 1) {
            params.set('page', page);
        }
        const query = params.toString();
        return query ? `${baseUrl}?${query}` : baseUrl;
    };

    const fetchResults = async () => {
        if (controller) {
            controller.abort();
        }
        controller = new AbortController();
        loading?.classList.remove('hidden');

        const url = buildUrl();

        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: controller.signal,
            });
            if (!response.ok) {
                throw new Error('request failed');
            }
            const html = await response.text();
            results.innerHTML = html;
            gsap.fromTo(results, { opacity: 0.4 }, { opacity: 1, duration: 0.25 });
            window.history.replaceState({}, '', url);
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error(error);
            }
        } finally {
            loading?.classList.add('hidden');
        }
    };

    searchInput?.addEventListener('input', () => {
        clearTimeout(debounceId);
        debounceId = setTimeout(() => {
            page = 1;
            fetchResults();
        }, 350);
    });

    [typeSelect, rangeSelect].forEach((select) => {
        select?.addEventListener('change', () => {
            page = 1;
            fetchResults();
        });
    });

    // Delegated pagination clicks (the buttons live inside the swapped results).
    results.addEventListener('click', (event) => {
        const pageBtn = event.target.closest('[data-activity-page]');
        if (!pageBtn || pageBtn.disabled) {
            return;
        }
        const target = parseInt(pageBtn.dataset.activityPage, 10);
        if (!Number.isNaN(target) && target >= 1) {
            page = target;
            fetchResults();
            const scroller = results.querySelector('.brand-scroll');
            if (scroller) {
                scroller.scrollTop = 0;
            }
        }
    });
}

// Company subscriptions: create / edit / delete modals with GSAP transitions.
function initSubscriptions() {
    const editModal = document.querySelector('[data-modal="subscription"]');
    const deleteModal = document.querySelector('[data-modal="subscription-delete"]');

    if (!editModal && !deleteModal) {
        return;
    }

    const open = (modal) => {
        if (!modal) {
            return;
        }
        modal.classList.remove('hidden');
        const overlay = modal.querySelector('[data-modal-overlay]');
        const panel = modal.querySelector('[data-modal-panel]');
        gsap.fromTo(overlay, { opacity: 0 }, { opacity: 1, duration: 0.2 });
        gsap.fromTo(panel, { opacity: 0, y: 24, scale: 0.96 }, { opacity: 1, y: 0, scale: 1, duration: 0.3, ease: 'power3.out' });
        document.body.style.overflow = 'hidden';
    };

    const close = (modal) => {
        if (!modal || modal.classList.contains('hidden')) {
            return;
        }
        const overlay = modal.querySelector('[data-modal-overlay]');
        const panel = modal.querySelector('[data-modal-panel]');
        gsap.to(overlay, { opacity: 0, duration: 0.2 });
        gsap.to(panel, {
            opacity: 0,
            y: 16,
            scale: 0.97,
            duration: 0.2,
            ease: 'power2.in',
            onComplete: () => {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            },
        });
    };

    const closeAll = () => [editModal, deleteModal].forEach((m) => close(m));

    const form = editModal?.querySelector('[data-subscription-form]');
    const base = form?.dataset.base;
    const title = editModal?.querySelector('[data-subscription-modal-title]');
    const submitText = editModal?.querySelector('[data-subscription-submit-text]');
    const methodInput = form?.querySelector('[data-method]');
    const idInput = form?.querySelector('[data-subscription-id]');

    const setField = (name, value) => {
        const el = form?.querySelector(`[name="${name}"]`);
        if (el) {
            el.value = value ?? '';
        }
    };

    const toCreate = () => {
        form.action = base;
        methodInput.value = 'POST';
        if (idInput) {
            idInput.value = '';
        }
        title.textContent = 'اشتراك جديد';
        if (submitText) {
            submitText.textContent = 'حفظ الاشتراك';
        }
        setField('customer_id', '');
        setField('plan_id', '');
        setField('start_date', new Date().toISOString().slice(0, 10));
        setField('status', 'active');
    };

    const toEdit = (data) => {
        form.action = `${base}/${data.id}`;
        methodInput.value = 'PUT';
        if (idInput) {
            idInput.value = data.id;
        }
        title.textContent = 'تعديل الاشتراك';
        if (submitText) {
            submitText.textContent = 'حفظ التعديلات';
        }
        setField('customer_id', data.customer);
        setField('plan_id', data.plan);
        setField('start_date', data.start);
        setField('status', data.status);
    };

    const deleteForm = deleteModal?.querySelector('[data-delete-form]');
    const deleteName = deleteModal?.querySelector('[data-delete-name]');

    // Delegated triggers so they keep working after the table is re-rendered via AJAX.
    document.addEventListener('click', (event) => {
        const createBtn = event.target.closest('[data-subscription-create]');
        if (createBtn) {
            toCreate();
            open(editModal);
            return;
        }

        const editBtn = event.target.closest('[data-subscription-edit]');
        if (editBtn) {
            toEdit(editBtn.dataset);
            open(editModal);
            return;
        }

        const deleteBtn = event.target.closest('[data-subscription-delete]');
        if (deleteBtn) {
            if (deleteForm) {
                deleteForm.action = `${base}/${deleteBtn.dataset.id}`;
            }
            if (deleteName) {
                deleteName.textContent = deleteBtn.dataset.name;
            }
            open(deleteModal);
        }
    });

    editModal?.querySelectorAll('[data-modal-close]').forEach((btn) => btn.addEventListener('click', closeAll));
    deleteModal?.querySelectorAll('[data-modal-close]').forEach((btn) => btn.addEventListener('click', closeAll));
    document.querySelectorAll('[data-modal-overlay]').forEach((overlay) => overlay.addEventListener('click', closeAll));
    document.querySelectorAll('[data-modal-dismiss]').forEach((area) => {
        area.addEventListener('click', (event) => {
            if (event.target === area) {
                closeAll();
            }
        });
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAll();
        }
    });

    // Re-open the modal after a server-side validation error.
    if (window.__openSubscriptionModal && form) {
        if (window.__openSubscriptionModal.mode === 'edit') {
            form.action = `${base}/${window.__openSubscriptionModal.id}`;
            methodInput.value = 'PUT';
            title.textContent = 'تعديل الاشتراك';
            if (submitText) {
                submitText.textContent = 'حفظ التعديلات';
            }
        }
        open(editModal);
    }

    // Auto-dismiss the status toast.
    const toast = document.querySelector('[data-toast]');
    if (toast) {
        gsap.fromTo(toast, { opacity: 0, y: -8 }, { opacity: 1, y: 0, duration: 0.3 });
        setTimeout(() => gsap.to(toast, { opacity: 0, y: -8, duration: 0.3, onComplete: () => toast.remove() }), 4000);
    }
}

// Company subscriptions: AJAX search, status / plan filters and pagination without full page reloads.
function initSubscriptionsTable() {
    const root = document.querySelector('[data-subscriptions]');
    if (!root) {
        return;
    }

    const baseUrl = root.dataset.url;
    const results = root.querySelector('[data-subscriptions-results]');
    const searchInput = root.querySelector('[data-subscriptions-search]');
    const statusSelect = root.querySelector('[data-subscriptions-status]');
    const planSelect = root.querySelector('[data-subscriptions-plan]');
    const loading = root.querySelector('[data-subscriptions-loading]');

    let page = 1;
    let debounceId = null;
    let controller = null;

    const buildUrl = () => {
        const params = new URLSearchParams();
        const q = searchInput?.value.trim();
        if (q) {
            params.set('q', q);
        }
        if (statusSelect?.value) {
            params.set('status', statusSelect.value);
        }
        if (planSelect?.value) {
            params.set('plan', planSelect.value);
        }
        if (page > 1) {
            params.set('page', page);
        }
        const query = params.toString();
        return query ? `${baseUrl}?${query}` : baseUrl;
    };

    const fetchResults = async () => {
        if (controller) {
            controller.abort();
        }
        controller = new AbortController();
        loading?.classList.remove('hidden');

        const url = buildUrl();

        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: controller.signal,
            });
            if (!response.ok) {
                throw new Error('request failed');
            }
            const html = await response.text();
            results.innerHTML = html;
            gsap.fromTo(results, { opacity: 0.4 }, { opacity: 1, duration: 0.25 });
            window.history.replaceState({}, '', url);
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error(error);
            }
        } finally {
            loading?.classList.add('hidden');
        }
    };

    searchInput?.addEventListener('input', () => {
        clearTimeout(debounceId);
        debounceId = setTimeout(() => {
            page = 1;
            fetchResults();
        }, 350);
    });

    [statusSelect, planSelect].forEach((select) => {
        select?.addEventListener('change', () => {
            page = 1;
            fetchResults();
        });
    });

    // Delegated pagination clicks (the buttons live inside the swapped results).
    results.addEventListener('click', (event) => {
        const pageBtn = event.target.closest('[data-subscriptions-page]');
        if (!pageBtn || pageBtn.disabled) {
            return;
        }
        const target = parseInt(pageBtn.dataset.subscriptionsPage, 10);
        if (!Number.isNaN(target) && target >= 1) {
            page = target;
            fetchResults();
            const scroller = results.querySelector('.brand-scroll');
            if (scroller) {
                scroller.scrollTop = 0;
            }
        }
    });
}

// Toggle password field visibility.
function initPasswordToggles() {
    document.querySelectorAll('[data-toggle-password]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.togglePassword);

            if (!input) {
                return;
            }

            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            button.querySelector('[data-eye="show"]').classList.toggle('hidden', !showing);
            button.querySelector('[data-eye="hide"]').classList.toggle('hidden', showing);
        });
    });
}

// Shared modal open/close with GSAP transitions over a set of modal elements.
function createModalController(modals) {
    const open = (modal) => {
        if (!modal) {
            return;
        }
        modal.classList.remove('hidden');
        const overlay = modal.querySelector('[data-modal-overlay]');
        const panel = modal.querySelector('[data-modal-panel]');
        gsap.fromTo(overlay, { opacity: 0 }, { opacity: 1, duration: 0.2 });
        gsap.fromTo(panel, { opacity: 0, y: 24, scale: 0.96 }, { opacity: 1, y: 0, scale: 1, duration: 0.3, ease: 'power3.out' });
        document.body.style.overflow = 'hidden';
    };

    const close = (modal) => {
        if (!modal || modal.classList.contains('hidden')) {
            return;
        }
        const overlay = modal.querySelector('[data-modal-overlay]');
        const panel = modal.querySelector('[data-modal-panel]');
        gsap.to(overlay, { opacity: 0, duration: 0.2 });
        gsap.to(panel, {
            opacity: 0,
            y: 16,
            scale: 0.97,
            duration: 0.2,
            ease: 'power2.in',
            onComplete: () => {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            },
        });
    };

    const closeAll = () => modals.forEach((m) => close(m));

    modals.forEach((modal) => {
        modal.querySelectorAll('[data-modal-close]').forEach((btn) => btn.addEventListener('click', closeAll));
        modal.querySelector('[data-modal-overlay]')?.addEventListener('click', closeAll);
        modal.querySelector('[data-modal-dismiss]')?.addEventListener('click', (event) => {
            if (event.target === event.currentTarget) {
                closeAll();
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAll();
        }
    });

    return { open, close, closeAll };
}

// Auto-dismiss flash toasts after a few seconds.
function initToasts() {
    document.querySelectorAll('[data-toast]').forEach((toast) => {
        if (toast.dataset.toastBound) {
            return;
        }
        toast.dataset.toastBound = 'true';
        gsap.fromTo(toast, { opacity: 0, y: -8 }, { opacity: 1, y: 0, duration: 0.3 });
        setTimeout(() => gsap.to(toast, { opacity: 0, y: -8, duration: 0.3, onComplete: () => toast.remove() }), 4500);
    });
}

// Company invoices: create / generate / void modals + dependent fields.
function initInvoices() {
    const createModal = document.querySelector('[data-modal="invoice"]');
    const generateModal = document.querySelector('[data-modal="invoice-generate"]');
    const voidModal = document.querySelector('[data-modal="invoice-void"]');

    if (!createModal && !generateModal && !voidModal) {
        return;
    }

    const modals = [createModal, generateModal, voidModal].filter(Boolean);
    const { open } = createModalController(modals);

    // Create modal: filter subscriptions by chosen customer and autofill amount.
    if (createModal) {
        const customerSelect = createModal.querySelector('[name="customer_id"]');
        const subSelect = createModal.querySelector('[name="subscription_id"]');
        const amount = createModal.querySelector('[name="amount"]');

        const filterSubs = () => {
            const cid = customerSelect?.value ?? '';
            Array.from(subSelect?.options ?? []).forEach((opt) => {
                if (!opt.value) {
                    return;
                }
                const match = opt.dataset.customer === cid;
                opt.hidden = !match;
                if (!match && opt.selected) {
                    subSelect.value = '';
                }
            });
        };

        customerSelect?.addEventListener('change', filterSubs);
        subSelect?.addEventListener('change', () => {
            const opt = subSelect.selectedOptions[0];
            if (opt && opt.dataset.amount && amount) {
                amount.value = opt.dataset.amount;
            }
        });
        filterSubs();

        document.querySelectorAll('[data-invoice-create]').forEach((btn) => {
            btn.addEventListener('click', () => {
                filterSubs();
                open(createModal);
            });
        });
    }

    document.querySelectorAll('[data-invoice-generate]').forEach((btn) => {
        btn.addEventListener('click', () => open(generateModal));
    });

    if (voidModal) {
        const voidForm = voidModal.querySelector('[data-void-form]');
        const voidName = voidModal.querySelector('[data-void-name]');

        // Delegated so it keeps working after the table is re-rendered via AJAX.
        document.addEventListener('click', (event) => {
            const btn = event.target.closest('[data-invoice-void]');
            if (!btn) {
                return;
            }
            if (voidForm) {
                voidForm.action = `${voidForm.dataset.base}/${btn.dataset.id}`;
            }
            if (voidName) {
                voidName.textContent = btn.dataset.name;
            }
            open(voidModal);
        });
    }

    if (window.__openInvoiceModal && createModal) {
        open(createModal);
    }

    initToasts();
}

// Company invoices: AJAX search, status tabs, date filters and pagination without full page reloads.
function initInvoicesTable() {
    const root = document.querySelector('[data-invoices]');
    if (!root) {
        return;
    }

    const baseUrl = root.dataset.url;
    const results = root.querySelector('[data-invoices-results]');
    const searchInput = root.querySelector('[data-invoices-search]');
    const fromInput = root.querySelector('[data-invoices-from]');
    const toInput = root.querySelector('[data-invoices-to]');
    const statusSelect = root.querySelector('[data-invoices-status]');
    const loading = root.querySelector('[data-invoices-loading]');

    let page = 1;
    let debounceId = null;
    let controller = null;

    const buildUrl = () => {
        const params = new URLSearchParams();
        const q = searchInput?.value.trim();
        if (q) {
            params.set('q', q);
        }
        if (statusSelect?.value) {
            params.set('status', statusSelect.value);
        }
        if (fromInput?.value) {
            params.set('from', fromInput.value);
        }
        if (toInput?.value) {
            params.set('to', toInput.value);
        }
        if (page > 1) {
            params.set('page', page);
        }
        const query = params.toString();
        return query ? `${baseUrl}?${query}` : baseUrl;
    };

    const fetchResults = async () => {
        if (controller) {
            controller.abort();
        }
        controller = new AbortController();
        loading?.classList.remove('hidden');

        const url = buildUrl();

        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: controller.signal,
            });
            if (!response.ok) {
                throw new Error('request failed');
            }
            const html = await response.text();
            results.innerHTML = html;
            gsap.fromTo(results, { opacity: 0.4 }, { opacity: 1, duration: 0.25 });
            window.history.replaceState({}, '', url);
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error(error);
            }
        } finally {
            loading?.classList.add('hidden');
        }
    };

    searchInput?.addEventListener('input', () => {
        clearTimeout(debounceId);
        debounceId = setTimeout(() => {
            page = 1;
            fetchResults();
        }, 350);
    });

    [fromInput, toInput, statusSelect].forEach((input) => {
        input?.addEventListener('change', () => {
            page = 1;
            fetchResults();
        });
    });

    // Delegated pagination clicks (the buttons live inside the swapped results).
    results.addEventListener('click', (event) => {
        const pageBtn = event.target.closest('[data-invoices-page]');
        if (!pageBtn || pageBtn.disabled) {
            return;
        }
        const target = parseInt(pageBtn.dataset.invoicesPage, 10);
        if (!Number.isNaN(target) && target >= 1) {
            page = target;
            fetchResults();
            const scroller = results.querySelector('.brand-scroll');
            if (scroller) {
                scroller.scrollTop = 0;
            }
        }
    });
}

// Company payments: record payment modal (shared across invoice & payment pages).
function initPayments() {
    const modal = document.querySelector('[data-modal="payment"]');

    initToasts();

    if (!modal) {
        return;
    }

    const { open } = createModalController([modal]);
    const invoiceSelect = modal.querySelector('[data-payment-invoice]');
    const amount = modal.querySelector('[data-payment-amount]');

    const setAmountFromInvoice = () => {
        const opt = invoiceSelect?.selectedOptions[0];
        if (opt && opt.dataset.balance && amount) {
            amount.value = opt.dataset.balance;
        }
    };

    invoiceSelect?.addEventListener('change', setAmountFromInvoice);

    // Delegated so it keeps working after the invoices table is re-rendered via AJAX.
    document.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-payment-create]');
        if (!btn) {
            return;
        }
        if (btn.dataset.invoice && invoiceSelect) {
            invoiceSelect.value = btn.dataset.invoice;
        }
        if (btn.dataset.balance && amount) {
            amount.value = btn.dataset.balance;
        } else {
            setAmountFromInvoice();
        }
        open(modal);
    });

    if (window.__openPaymentModal) {
        open(modal);
    }
}

// Company payments: AJAX search, method / date filters and pagination without full page reloads.
function initPaymentsTable() {
    const root = document.querySelector('[data-payments]');
    if (!root) {
        return;
    }

    const baseUrl = root.dataset.url;
    const results = root.querySelector('[data-payments-results]');
    const searchInput = root.querySelector('[data-payments-search]');
    const methodSelect = root.querySelector('[data-payments-method]');
    const fromInput = root.querySelector('[data-payments-from]');
    const toInput = root.querySelector('[data-payments-to]');
    const loading = root.querySelector('[data-payments-loading]');

    let page = 1;
    let debounceId = null;
    let controller = null;

    const buildUrl = () => {
        const params = new URLSearchParams();
        const q = searchInput?.value.trim();
        if (q) {
            params.set('q', q);
        }
        if (methodSelect?.value) {
            params.set('method', methodSelect.value);
        }
        if (fromInput?.value) {
            params.set('from', fromInput.value);
        }
        if (toInput?.value) {
            params.set('to', toInput.value);
        }
        if (page > 1) {
            params.set('page', page);
        }
        const query = params.toString();
        return query ? `${baseUrl}?${query}` : baseUrl;
    };

    const fetchResults = async () => {
        if (controller) {
            controller.abort();
        }
        controller = new AbortController();
        loading?.classList.remove('hidden');

        const url = buildUrl();

        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: controller.signal,
            });
            if (!response.ok) {
                throw new Error('request failed');
            }
            const html = await response.text();
            results.innerHTML = html;
            gsap.fromTo(results, { opacity: 0.4 }, { opacity: 1, duration: 0.25 });
            window.history.replaceState({}, '', url);
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error(error);
            }
        } finally {
            loading?.classList.add('hidden');
        }
    };

    searchInput?.addEventListener('input', () => {
        clearTimeout(debounceId);
        debounceId = setTimeout(() => {
            page = 1;
            fetchResults();
        }, 350);
    });

    [methodSelect, fromInput, toInput].forEach((input) => {
        input?.addEventListener('change', () => {
            page = 1;
            fetchResults();
        });
    });

    // Delegated pagination clicks (the buttons live inside the swapped results).
    results.addEventListener('click', (event) => {
        const pageBtn = event.target.closest('[data-payments-page]');
        if (!pageBtn || pageBtn.disabled) {
            return;
        }
        const target = parseInt(pageBtn.dataset.paymentsPage, 10);
        if (!Number.isNaN(target) && target >= 1) {
            page = target;
            fetchResults();
            const scroller = results.querySelector('.brand-scroll');
            if (scroller) {
                scroller.scrollTop = 0;
            }
        }
    });
}

// Company revenue recognition: tab switching + run confirmation modal.
function initRevenueRecognition() {
    const modal = document.querySelector('[data-modal="revenue-recognize"]');
    const root = document.querySelector('[data-revenue]');

    if (!root && !modal) {
        return;
    }

    initToasts();

    if (modal) {
        const { open } = createModalController([modal]);
        document.querySelectorAll('[data-revenue-recognize]').forEach((btn) => {
            btn.addEventListener('click', () => {
                if (!btn.disabled) {
                    open(modal);
                }
            });
        });
    }

    if (!root) {
        return;
    }

    const baseUrl = root.dataset.url;
    const results = root.querySelector('[data-revenue-results]');
    const searchInput = root.querySelector('[data-revenue-search]');
    const monthInput = root.querySelector('[data-revenue-month]');
    const viewTabs = Array.from(root.querySelectorAll('[data-revenue-view]'));
    const loading = root.querySelector('[data-revenue-loading]');

    let page = 1;
    let view = viewTabs.find((tab) => tab.classList.contains('bg-brand'))?.dataset.revenueView ?? 'pending';
    let debounceId = null;
    let controller = null;

    const idleClasses = ['text-slate-500', 'hover:bg-slate-100', 'dark:text-slate-400', 'dark:hover:bg-white/5'];

    const setActiveTab = (value) => {
        viewTabs.forEach((tab) => {
            const isActive = tab.dataset.revenueView === value;
            tab.classList.toggle('bg-brand', isActive);
            tab.classList.toggle('text-white', isActive);
            tab.classList.toggle('shadow', isActive);
            tab.classList.toggle('shadow-brand/25', isActive);
            idleClasses.forEach((cls) => tab.classList.toggle(cls, !isActive));
        });
    };

    const buildUrl = () => {
        const params = new URLSearchParams();
        const q = searchInput?.value.trim();
        if (q) {
            params.set('q', q);
        }
        if (view) {
            params.set('view', view);
        }
        if (monthInput?.value) {
            params.set('month', monthInput.value);
        }
        if (page > 1) {
            params.set('page', page);
        }
        const query = params.toString();
        return query ? `${baseUrl}?${query}` : baseUrl;
    };

    const fetchResults = async () => {
        if (controller) {
            controller.abort();
        }
        controller = new AbortController();
        loading?.classList.remove('hidden');

        try {
            const response = await fetch(buildUrl(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: controller.signal,
            });
            if (!response.ok) {
                throw new Error('request failed');
            }
            const html = await response.text();
            results.innerHTML = html;
            gsap.fromTo(results, { opacity: 0.4 }, { opacity: 1, duration: 0.25 });
            window.history.replaceState({}, '', buildUrl());
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error(error);
            }
        } finally {
            loading?.classList.add('hidden');
        }
    };

    searchInput?.addEventListener('input', () => {
        clearTimeout(debounceId);
        debounceId = setTimeout(() => {
            page = 1;
            fetchResults();
        }, 350);
    });

    // The month also reloads the whole page so KPIs + the recognize action stay in sync.
    monthInput?.addEventListener('change', () => {
        const params = new URLSearchParams(window.location.search);
        params.set('month', monthInput.value);
        params.set('view', view);
        window.location.search = params.toString();
    });

    viewTabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            view = tab.dataset.revenueView;
            page = 1;
            setActiveTab(view);
            fetchResults();
        });
    });

    results.addEventListener('click', (event) => {
        const pageBtn = event.target.closest('[data-rev-page]');
        if (!pageBtn || pageBtn.disabled) {
            return;
        }
        const target = parseInt(pageBtn.dataset.revPage, 10);
        if (!Number.isNaN(target) && target >= 1) {
            page = target;
            fetchResults();
            const scroller = results.querySelector('.brand-scroll');
            if (scroller) {
                scroller.scrollTop = 0;
            }
        }
    });
}

// Income statement: AJAX date filtering with branded loading state.
function initIncomeStatement() {
    const root = document.querySelector('[data-income]');

    if (!root) {
        return;
    }

    const baseUrl = root.dataset.url;
    const fromInput = root.querySelector('[data-income-from]');
    const toInput = root.querySelector('[data-income-to]');
    const loading = root.querySelector('[data-income-loading]');
    const results = document.querySelector('[data-income-results]');
    let controller = null;

    const buildUrl = () => {
        const params = new URLSearchParams();
        if (fromInput?.value) {
            params.set('from', fromInput.value);
        }
        if (toInput?.value) {
            params.set('to', toInput.value);
        }
        const query = params.toString();
        return query ? `${baseUrl}?${query}` : baseUrl;
    };

    const fetchResults = async () => {
        if (controller) {
            controller.abort();
        }
        controller = new AbortController();
        loading?.classList.remove('hidden');

        try {
            const response = await fetch(buildUrl(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: controller.signal,
            });
            if (!response.ok) {
                throw new Error('request failed');
            }
            results.innerHTML = await response.text();
            gsap.fromTo(results, { opacity: 0.4 }, { opacity: 1, duration: 0.25 });
            window.history.replaceState({}, '', buildUrl());
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error(error);
            }
        } finally {
            loading?.classList.add('hidden');
        }
    };

    [fromInput, toInput].forEach((input) => {
        input?.addEventListener('change', fetchResults);
    });
}

// Balance sheet: AJAX date filtering with branded loading state.
function initBalanceSheet() {
    const root = document.querySelector('[data-balance]');

    if (!root) {
        return;
    }

    const baseUrl = root.dataset.url;
    const asOfInput = root.querySelector('[data-balance-asof]');
    const loading = root.querySelector('[data-balance-loading]');
    const results = document.querySelector('[data-balance-results]');
    let controller = null;

    const buildUrl = () => {
        const params = new URLSearchParams();
        if (asOfInput?.value) {
            params.set('as_of', asOfInput.value);
        }
        const query = params.toString();
        return query ? `${baseUrl}?${query}` : baseUrl;
    };

    const fetchResults = async () => {
        if (controller) {
            controller.abort();
        }
        controller = new AbortController();
        loading?.classList.remove('hidden');

        try {
            const response = await fetch(buildUrl(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: controller.signal,
            });
            if (!response.ok) {
                throw new Error('request failed');
            }
            results.innerHTML = await response.text();
            gsap.fromTo(results, { opacity: 0.4 }, { opacity: 1, duration: 0.25 });
            window.history.replaceState({}, '', buildUrl());
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error(error);
            }
        } finally {
            loading?.classList.add('hidden');
        }
    };

    asOfInput?.addEventListener('change', fetchResults);
}

// Company settings: tabbed navigation + logo preview + account deactivation modal.
function initSettings() {
    const root = document.querySelector('[data-settings]');
    const logoInput = document.querySelector('[data-logo-input]');
    const modal = document.querySelector('[data-modal="account-deactivate"]');

    if (!root && !logoInput && !modal) {
        return;
    }

    initToasts();

    // Tab switching between settings sections.
    if (root) {
        const tabs = Array.from(root.querySelectorAll('[data-settings-tab]'));
        const panels = Array.from(root.querySelectorAll('[data-settings-panel]'));

        const activate = (name) => {
            tabs.forEach((tab) => tab.setAttribute('data-active', (tab.dataset.settingsTab === name).toString()));
            panels.forEach((panel) => {
                const isActive = panel.dataset.settingsPanel === name;
                panel.classList.toggle('hidden', !isActive);
                if (isActive) {
                    gsap.fromTo(panel, { opacity: 0, y: 10 }, { opacity: 1, y: 0, duration: 0.3, ease: 'power2.out' });
                }
            });
        };

        tabs.forEach((tab) => tab.addEventListener('click', () => activate(tab.dataset.settingsTab)));
    }

    if (logoInput) {
        logoInput.addEventListener('change', () => {
            const file = logoInput.files?.[0];
            if (!file) {
                return;
            }
            const url = URL.createObjectURL(file);
            // Update every logo preview (hero + form card).
            document.querySelectorAll('[data-logo-preview]').forEach((img) => {
                img.src = url;
                img.classList.remove('hidden');
            });
            document.querySelectorAll('[data-logo-fallback]').forEach((el) => el.classList.add('hidden'));
            const removeCheckbox = document.querySelector('[name="remove_logo"]');
            if (removeCheckbox) {
                removeCheckbox.checked = false;
            }
        });
    }

    if (modal) {
        const { open } = createModalController([modal]);
        document.querySelectorAll('[data-account-deactivate]').forEach((btn) => {
            btn.addEventListener('click', () => open(modal));
        });
    }
}

// Company financial reports: auto-submit filters on date change + toasts.
function initReports() {
    const forms = document.querySelectorAll('[data-report-form]');

    if (!forms.length) {
        return;
    }

    initToasts();

    forms.forEach((form) => {
        form.querySelectorAll('input[type="date"]').forEach((input) => {
            input.addEventListener('change', () => form.submit());
        });
    });
}

// Company dashboard: render Chart.js visualizations from JSON embedded in the page.
function initDashboardCharts() {
    const dataEl = document.getElementById('dashboard-charts-data');
    if (!dataEl || typeof Chart === 'undefined') {
        return;
    }

    let data;
    try {
        data = JSON.parse(dataEl.textContent);
    } catch (e) {
        return;
    }

    const isDark = () => document.documentElement.classList.contains('dark');
    const grid = () => (isDark() ? 'rgba(255,255,255,0.06)' : 'rgba(15,23,42,0.05)');
    const tick = () => (isDark() ? 'rgba(148,163,184,0.9)' : 'rgba(100,116,139,0.95)');
    const tooltipBg = () => (isDark() ? 'rgba(15,23,42,0.95)' : 'rgba(255,255,255,0.98)');
    const tooltipFg = () => (isDark() ? '#e2e8f0' : '#0f172a');
    const tooltipBorder = () => (isDark() ? 'rgba(255,255,255,0.1)' : 'rgba(15,23,42,0.08)');

    const symbol = data.symbol || '';
    const brand = '#2563eb';
    const emerald = '#10b981';
    const amber = '#f59e0b';
    const slate = '#94a3b8';
    const palette = ['#2563eb', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6', '#f43f5e', '#0ea5e9'];

    const nf = new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 });
    const nf2 = new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const money = (v) => `${nf2.format(v)} ${symbol}`.trim();

    Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
    Chart.defaults.font.size = 12;
    Chart.defaults.animation = { duration: 800, easing: 'easeOutQuart' };

    // Build a soft vertical gradient for area fills.
    const gradient = (ctx, area, hex) => {
        if (!area) return hex + '20';
        const g = ctx.createLinearGradient(0, area.top, 0, area.bottom);
        g.addColorStop(0, hex + '40');
        g.addColorStop(1, hex + '00');
        return g;
    };

    const baseTooltip = (formatter) => ({
        backgroundColor: tooltipBg(),
        titleColor: tooltipFg(),
        bodyColor: tooltipFg(),
        borderColor: tooltipBorder(),
        borderWidth: 1,
        padding: 12,
        cornerRadius: 12,
        boxPadding: 6,
        usePointStyle: true,
        titleFont: { weight: '700' },
        bodyFont: { weight: '500' },
        rtl: true,
        callbacks: formatter,
    });

    const legendBottom = () => ({
        position: 'bottom',
        rtl: true,
        labels: { color: tick(), usePointStyle: true, pointStyle: 'circle', padding: 16, boxWidth: 8, font: { size: 12 } },
    });

    // Plugin: render the total in the middle of a doughnut.
    const centerText = {
        id: 'centerText',
        afterDraw(chart) {
            const opts = chart.config.options.plugins.centerText;
            if (!opts || !opts.display) return;
            const { ctx, chartArea: { left, right, top, bottom } } = chart;
            const x = (left + right) / 2;
            const y = (top + bottom) / 2;
            const total = chart.data.datasets[0].data.reduce((a, b) => a + Number(b || 0), 0);
            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = tick();
            ctx.font = '600 11px ' + Chart.defaults.font.family;
            ctx.fillText(opts.label || '', x, y - 12);
            ctx.fillStyle = tooltipFg();
            ctx.font = '700 18px ' + Chart.defaults.font.family;
            ctx.fillText(nf.format(total), x, y + 8);
            ctx.restore();
        },
    };

    const charts = [];

    // Revenue trend (recognized vs collected) — gradient area line chart.
    const trendEl = document.getElementById('chart-revenue-trend');
    if (trendEl && data.revenueTrend) {
        const ctx = trendEl.getContext('2d');
        charts.push(new Chart(trendEl, {
            type: 'line',
            data: {
                labels: data.revenueTrend.labels,
                datasets: [
                    {
                        label: 'الإيراد المعترف به',
                        data: data.revenueTrend.recognized,
                        borderColor: brand,
                        backgroundColor: (c) => gradient(ctx, c.chart.chartArea, brand),
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2.5,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointBackgroundColor: brand,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                    },
                    {
                        label: 'النقدية المحصّلة',
                        data: data.revenueTrend.collected,
                        borderColor: emerald,
                        backgroundColor: (c) => gradient(ctx, c.chart.chartArea, emerald),
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2.5,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointBackgroundColor: emerald,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: legendBottom(),
                    tooltip: baseTooltip({ label: (c) => `  ${c.dataset.label}: ${money(c.parsed.y)}` }),
                },
                scales: {
                    x: { grid: { display: false }, border: { display: false }, ticks: { color: tick() } },
                    y: { grid: { color: grid() }, border: { display: false }, ticks: { color: tick(), callback: (v) => nf.format(v), maxTicksLimit: 5 }, beginAtZero: true },
                },
            },
        }));
    }

    // Deferred vs recognized revenue — doughnut with center total.
    const deferredEl = document.getElementById('chart-deferred');
    if (deferredEl && data.deferredVsRecognized) {
        charts.push(new Chart(deferredEl, {
            type: 'doughnut',
            plugins: [centerText],
            data: {
                labels: ['إيراد مؤجل', 'إيراد معترف به'],
                datasets: [{
                    data: [data.deferredVsRecognized.deferred, data.deferredVsRecognized.recognized],
                    backgroundColor: [amber, emerald],
                    borderColor: isDark() ? '#0f172a' : '#fff',
                    borderWidth: 3,
                    hoverOffset: 8,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: legendBottom(),
                    centerText: { display: true, label: 'الإجمالي' },
                    tooltip: baseTooltip({ label: (c) => `  ${c.label}: ${money(c.parsed)}` }),
                },
            },
        }));
    }

    // Active subscriptions MRR by plan — doughnut with center total.
    const plansEl = document.getElementById('chart-plans');
    if (plansEl && data.plans && data.plans.labels.length) {
        charts.push(new Chart(plansEl, {
            type: 'doughnut',
            plugins: [centerText],
            data: {
                labels: data.plans.labels,
                datasets: [{
                    data: data.plans.values,
                    backgroundColor: palette,
                    borderColor: isDark() ? '#0f172a' : '#fff',
                    borderWidth: 3,
                    hoverOffset: 8,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '66%',
                plugins: {
                    legend: legendBottom(),
                    centerText: { display: true, label: 'MRR' },
                    tooltip: baseTooltip({ label: (c) => `  ${c.label}: ${money(c.parsed)}` }),
                },
            },
        }));
    }

    // Invoice status breakdown — rounded bar chart.
    const statusEl = document.getElementById('chart-invoice-status');
    if (statusEl && data.invoiceStatus) {
        charts.push(new Chart(statusEl, {
            type: 'bar',
            data: {
                labels: data.invoiceStatus.labels,
                datasets: [{
                    label: 'عدد الفواتير',
                    data: data.invoiceStatus.values,
                    backgroundColor: [emerald, brand, amber, slate],
                    borderRadius: 10,
                    borderSkipped: false,
                    maxBarThickness: 44,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: baseTooltip({ label: (c) => `  ${c.parsed.y} فاتورة` }),
                },
                scales: {
                    x: { grid: { display: false }, border: { display: false }, ticks: { color: tick() } },
                    y: { grid: { color: grid() }, border: { display: false }, ticks: { color: tick(), precision: 0, maxTicksLimit: 5 }, beginAtZero: true },
                },
            },
        }));
    }

    // Re-theme charts when dark mode is toggled.
    const observer = new MutationObserver(() => {
        charts.forEach((chart) => {
            if (chart.options.scales) {
                Object.values(chart.options.scales).forEach((scale) => {
                    if (scale.grid && scale.grid.color !== undefined) scale.grid.color = grid();
                    if (scale.ticks) scale.ticks.color = tick();
                });
            }
            if (chart.options.plugins?.legend?.labels) {
                chart.options.plugins.legend.labels.color = tick();
            }
            if (chart.options.plugins?.tooltip) {
                Object.assign(chart.options.plugins.tooltip, {
                    backgroundColor: tooltipBg(),
                    titleColor: tooltipFg(),
                    bodyColor: tooltipFg(),
                    borderColor: tooltipBorder(),
                });
            }
            if (chart.config.type === 'doughnut') {
                chart.data.datasets[0].borderColor = isDark() ? '#0f172a' : '#fff';
            }
            chart.update('none');
        });
    });
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
}

// Landing page: nav scroll state, mobile menu, scroll reveals, timeline + ledger motion.
function initLanding() {
    const page = document.querySelector('[data-page="landing"]');

    if (!page) {
        return;
    }

    // Navbar turns solid once the visitor scrolls past the hero top.
    const nav = document.querySelector('[data-landing-nav]');

    if (nav) {
        const syncNav = () => nav.classList.toggle('nav-scrolled', window.scrollY > 24);
        syncNav();
        window.addEventListener('scroll', syncNav, { passive: true });
    }

    // Mobile menu open/close with a smooth height animation.
    const menuToggle = document.querySelector('[data-menu-toggle]');
    const mobileMenu = document.querySelector('[data-mobile-menu]');

    if (menuToggle && mobileMenu) {
        const closeMenu = () => {
            gsap.to(mobileMenu, {
                height: 0,
                opacity: 0,
                duration: 0.25,
                ease: 'power2.in',
                onComplete: () => mobileMenu.classList.add('hidden'),
            });
            menuToggle.setAttribute('aria-expanded', 'false');
        };

        const openMenu = () => {
            mobileMenu.classList.remove('hidden');
            gsap.fromTo(
                mobileMenu,
                { height: 0, opacity: 0 },
                { height: 'auto', opacity: 1, duration: 0.3, ease: 'power2.out' },
            );
            menuToggle.setAttribute('aria-expanded', 'true');
        };

        menuToggle.addEventListener('click', () => {
            mobileMenu.classList.contains('hidden') ? openMenu() : closeMenu();
        });

        mobileMenu.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeMenu));
    }

    // Single-element scroll reveals.
    // immediateRender:false keeps content visible by default, so a missed/late
    // ScrollTrigger can never leave a section permanently hidden. once:true avoids
    // the reverse-hide on scroll-up.
    gsap.utils.toArray('[data-animate="reveal"]').forEach((el) => {
        gsap.from(el, {
            y: 40,
            opacity: 0,
            duration: 0.8,
            ease: 'power2.out',
            immediateRender: false,
            scrollTrigger: { trigger: el, start: 'top 90%', once: true },
        });
    });

    // Staggered reveals for grouped children (feature grid, steps, value cards).
    gsap.utils.toArray('[data-animate="stagger"]').forEach((group) => {
        gsap.from(group.children, {
            y: 30,
            opacity: 0,
            duration: 0.6,
            ease: 'power2.out',
            stagger: 0.12,
            immediateRender: false,
            scrollTrigger: { trigger: group, start: 'top 88%', once: true },
        });
    });

    // "How it works" vertical progress line fills as the visitor scrolls through it.
    const stepsLine = document.querySelector('[data-steps-line]');

    if (stepsLine) {
        gsap.fromTo(
            stepsLine,
            { scaleY: 0 },
            {
                scaleY: 1,
                transformOrigin: 'center top',
                ease: 'none',
                scrollTrigger: { trigger: stepsLine, start: 'top 75%', end: 'bottom 70%', scrub: true },
            },
        );
    }

    // Looping highlight on the double-entry ledger rows to feel "live".
    const ledgerRows = gsap.utils.toArray('[data-ledger-row]');

    if (ledgerRows.length) {
        gsap.timeline({ repeat: -1, repeatDelay: 1.4 })
            .fromTo(
                ledgerRows,
                { backgroundColor: 'rgba(26,163,153,0)' },
                { backgroundColor: 'rgba(26,163,153,0.14)', duration: 0.5, stagger: 0.3 },
            )
            .to(ledgerRows, { backgroundColor: 'rgba(26,163,153,0)', duration: 0.5, delay: 0.9 });
    }

    // Recompute trigger positions once layout/fonts/images have settled.
    requestAnimationFrame(() => ScrollTrigger.refresh());
    window.addEventListener('load', () => ScrollTrigger.refresh());
}

function boot() {
    initAnimations();
    initAuthPortal();
    initLanding();
    initPasswordToggles();
    initDropdowns();
    initDashboard();
    initPlans();
    initPlansTable();
    initCompanyUsers();
    initCompanies();
    initCustomers();
    initCustomersTable();
    initSubscriptions();
    initSubscriptionsTable();
    initInvoices();
    initInvoicesTable();
    initPayments();
    initPaymentsTable();
    initRevenueRecognition();
    initIncomeStatement();
    initBalanceSheet();
    initSettings();
    initActivityLog();
    initReports();
    initDashboardCharts();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}
