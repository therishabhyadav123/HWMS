(function(){
  const root = document.documentElement;
  const key = 'medieco_theme';
  const stored = localStorage.getItem(key);
  if (stored) {
    root.setAttribute('data-theme', stored);
  }

  const updateLabel = () => {
    const isDark = root.getAttribute('data-theme') === 'dark';
    document.querySelectorAll('.js-theme-toggle').forEach((btn) => {
      const label = btn.getAttribute('data-dark-label') || 'Dark Mode';
      const alt = btn.getAttribute('data-light-label') || 'Light Mode';
      btn.textContent = isDark ? alt : label;
    });
  };

  const toggleTheme = () => {
    const isDark = root.getAttribute('data-theme') === 'dark';
    const next = isDark ? 'light' : 'dark';
    root.setAttribute('data-theme', next);
    localStorage.setItem(key, next);
    updateLabel();
  };

  document.addEventListener('click', (event) => {
    const target = event.target.closest('.js-theme-toggle');
    if (target) {
      event.preventDefault();
      toggleTheme();
    }
  });

  updateLabel();
})();
