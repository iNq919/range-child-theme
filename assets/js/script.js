document.addEventListener('DOMContentLoaded', () => {
  // HOVER IMAGE – PRODUCTS + SOCIALS
  const ITEMS_SELECTOR = '.products-list .product, .socials-list .social';

  document.addEventListener('mouseover', (e) => {
    const item = e.target.closest(ITEMS_SELECTOR);
    if (!item) return;

    item.querySelector('.image')?.classList.add('hovered');
  });

  document.addEventListener('mouseout', (e) => {
    const item = e.target.closest(ITEMS_SELECTOR);
    if (!item) return;

    item.querySelector('.image')?.classList.remove('hovered');
  });

  // MENU STATE
  const WRAPPER_SELECTOR = '.e-n-menu-wrapper';
  const ITEM_SELECTOR = '.e-n-menu-item';
  const SUBMENU_SELECTOR = '.e-n-menu-content';

  const menuWrapper = document.querySelector(WRAPPER_SELECTOR);

  if (menuWrapper) {
    const updateMenuState = () => {
      const items = menuWrapper.querySelectorAll(ITEM_SELECTOR);
      let hasActive = false;

      items.forEach(item => {
        const isActive =
          item.querySelector(`${SUBMENU_SELECTOR}.e-active`) ||
          item.querySelector('[aria-expanded="true"]');

        item.classList.toggle('active', !!isActive);
        hasActive ||= !!isActive;
      });

      menuWrapper.classList.toggle('has-active', hasActive);
    };

    new MutationObserver(updateMenuState).observe(menuWrapper, {
      subtree: true,
      attributes: true,
      attributeFilter: ['class', 'aria-expanded'],
    });

    updateMenuState();
  }

  // HEADER SCROLL + ACTIVE MENU CONTENT
  const HEADER_SELECTOR = '.nav-menu';
  const SCROLL_OFFSET = 80;
  const SCROLLED_CLASS = 'nav-menu--scrolled';
  const ACTIVE_CONTENT_CLASS = 'nav-menu--active-content';

  const header = document.querySelector(HEADER_SELECTOR);

  if (header) {
    let ticking = false;

    const updateScrollState = () => {
      header.classList.toggle(
        SCROLLED_CLASS,
        window.scrollY > SCROLL_OFFSET
      );
      ticking = false;
    };

    window.addEventListener(
      'scroll',
      () => {
        if (!ticking) {
          requestAnimationFrame(updateScrollState);
          ticking = true;
        }
      },
      { passive: true }
    );

    const updateActiveContent = () => {
      const hasActiveContent = document.querySelector(
        '.e-n-menu-content.e-active'
      );
      header.classList.toggle(ACTIVE_CONTENT_CLASS, !!hasActiveContent);
    };

    const menuWrapper = document.querySelector(WRAPPER_SELECTOR);
    if (menuWrapper) {
      new MutationObserver(updateActiveContent).observe(menuWrapper, {
        subtree: true,
        attributes: true,
        attributeFilter: ['class'],
      });
    }

    updateScrollState();
    updateActiveContent();
  }

  // THEME TOGGLE
  (() => {
    const STORAGE_KEY = 'theme';
    const CLASS_DARK = 'is-dark';
    const THEME_SELECTOR = '.theme-toggle';

    const toggle = document.querySelector(THEME_SELECTOR);
    if (!toggle || !window.matchMedia) return;

    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

    const getStoredTheme = () => localStorage.getItem(STORAGE_KEY);

    const getPreferredTheme = () => {
      const stored = getStoredTheme();
      if (stored === 'dark' || stored === 'light') return stored;
      return mediaQuery.matches ? 'dark' : 'light';
    };

    const applyTheme = (theme, persist = true) => {
      const isDark = theme === 'dark';

      document.body.classList.toggle(CLASS_DARK, isDark);
      toggle.setAttribute('aria-pressed', String(isDark));

      if (persist) {
        localStorage.setItem(STORAGE_KEY, theme);
      }
    };

    applyTheme(getPreferredTheme(), false);

    toggle.addEventListener('click', () => {
      applyTheme(
        document.body.classList.contains(CLASS_DARK) ? 'light' : 'dark'
      );
    });

    mediaQuery.addEventListener('change', e => {
      if (getStoredTheme()) return;
      applyTheme(e.matches ? 'dark' : 'light', false);
    });

    window.addEventListener('storage', e => {
      if (e.key !== STORAGE_KEY || !e.newValue) return;
      applyTheme(e.newValue, false);
    });
  })();

const HERO_SELECTOR = '.hero-section';
const VIDEO_SELECTOR = '.elementor-background-video-hosted';

    function forceHeroVideoCover() {
        const hero = document.querySelector(HERO_SELECTOR);
        const video = hero ? hero.querySelector(VIDEO_SELECTOR) : null;

        if (!hero || !video) {
            return;
        }

        const heroHeight = hero.offsetHeight;
        const heroWidth = hero.offsetWidth;

        video.removeAttribute('style');

        video.style.width = heroWidth + 'px';
        video.style.height = heroHeight + 'px';

        video.style.position = 'absolute';
        video.style.top = '50%';
        video.style.left = '50%';
        video.style.transform = 'translate(-50%, -50%) scale(1.15)';
        video.style.pointerEvents = 'none';
    }

    setTimeout(forceHeroVideoCover, 100);
    setTimeout(forceHeroVideoCover, 500);
    setTimeout(forceHeroVideoCover, 1000);

    window.addEventListener('resize', forceHeroVideoCover);
});