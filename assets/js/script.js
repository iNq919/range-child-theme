document.addEventListener('DOMContentLoaded', () => {
  // HOVER IMAGE – PRODUCTS + SOCIALS
  const ITEMS_SELECTOR = '.products-list .product, .socials-list .social';
  document.querySelectorAll(ITEMS_SELECTOR).forEach(item => {
    const imageDiv = item.querySelector('.image');
    if (!imageDiv) return;

    item.addEventListener('mouseenter', () => {
      imageDiv.classList.add('hovered');
    });

    item.addEventListener('mouseleave', () => {
      imageDiv.classList.remove('hovered');
    });
  });

  // MENU STATE
  const WRAPPER_SELECTOR = '.e-n-menu-wrapper';
  const ITEM_SELECTOR = '.e-n-menu-item';
  const SUBMENU_SELECTOR = '.e-n-menu-content';
  const TOGGLE_SELECTOR = '.e-n-menu-dropdown-icon';

  const wrapper = document.querySelector(WRAPPER_SELECTOR);
  if (wrapper) {
    const items = wrapper.querySelectorAll(ITEM_SELECTOR);

    const updateItemState = (item) => {
      const submenu = item.querySelector(SUBMENU_SELECTOR);
      const toggle = item.querySelector(TOGGLE_SELECTOR);

      const isActive = submenu?.classList.contains('e-active') || (toggle?.getAttribute('aria-expanded') === 'true');

      item.classList.toggle('active', isActive);
    };

    const updateWrapperJustify = () => {
      const hasActive = Array.from(items).some(item => item.classList.contains('active'));
      wrapper.style.justifyContent = hasActive ? 'flex-start' : 'space-around';
    };

    items.forEach(item => {
      const observer = new MutationObserver(() => {
        updateItemState(item);
        updateWrapperJustify();
      });

      observer.observe(item, {
        subtree: true,
        attributes: true,
        attributeFilter: ['class', 'aria-expanded'],
      });

      updateItemState(item);
    });

    updateWrapperJustify();
  }

  // HEADER SCROLL + ACTIVE MENU CONTENT
  const HEADER_SELECTOR = '.nav-menu';
  const SCROLL_OFFSET = 80;
  const SCROLLED_CLASS = 'nav-menu--scrolled';
  const ACTIVE_CONTENT_CLASS = 'nav-menu--active-content';

  const header = document.querySelector(HEADER_SELECTOR);
  if (header) {
    const onScroll = () => {
      header.classList.toggle(
        SCROLLED_CLASS,
        window.scrollY > SCROLL_OFFSET
      );
    };

    const checkActiveContent = () => {
      const hasActiveContent = document.querySelector('.e-n-menu-content.e-active');
      header.classList.toggle(ACTIVE_CONTENT_CLASS, !!hasActiveContent);
    };

    const menuWrapper = document.querySelector('.e-n-menu-wrapper');

    if (menuWrapper) {
      const observer = new MutationObserver(checkActiveContent);
      observer.observe(menuWrapper, {
        subtree: true,
        attributes: true,
        attributeFilter: ['class'],
      });
    }

    window.addEventListener('scroll', onScroll);

    onScroll();
    checkActiveContent();
  }

  // THEME TOGGLE
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
});