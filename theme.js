(function(){
  const root = document.documentElement;
  const toggle = document.getElementById('themeToggle');
  const icon = document.getElementById('themeIcon');
  const key = 'medieco_theme';

  const applyTheme = (theme) => {
    root.setAttribute('data-theme', theme);
    if (icon) {
      icon.setAttribute('data-lucide', theme === 'dark' ? 'sun' : 'moon');
    }
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
      window.lucide.createIcons();
    }
  };

  const stored = localStorage.getItem(key) || 'light';
  applyTheme(stored);

  if (toggle) {
    toggle.addEventListener('click', () => {
      const current = root.getAttribute('data-theme') || 'light';
      const next = current === 'dark' ? 'light' : 'dark';
      localStorage.setItem(key, next);
      applyTheme(next);
    });
  }
})();
