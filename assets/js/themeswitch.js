  // Theme switching functions
  function switchThemeClasses(fromSuffix, toSuffix) {
    const elements = document.querySelectorAll(`[class*="${fromSuffix}"]`);
    elements.forEach(el => {
      el.classList.forEach(className => {
        if (className.includes(fromSuffix)) {
          const newClass = className.replace(fromSuffix, toSuffix);
          el.classList.replace(className, newClass);
        }
      });
    });
  }

  function updateThemeClasses() {
    const htmlTag = document.documentElement;
    const theme = htmlTag.getAttribute("data-bs-theme");

    if (theme === "dark") {
      switchThemeClasses("-light", "-dark");
    } else if (theme === "light") {
      switchThemeClasses("-dark", "-light");
    }
    updateThemeLogo(theme);
  }

  function updateThemeLogo(theme) {
    const logo = document.getElementById('themeLogo');
    if (!logo) return;

    if (theme === 'dark') {
      logo.src = '../assets/images/theme-logo-dark.png';
    } else {
      logo.src = '../assets/images/theme-logo-light.png';
    }
  }

  // Apply theme on load
  window.addEventListener('DOMContentLoaded', () => {
    const root = document.documentElement;
    const toggleSwitch = document.getElementById('modeSwitch');
    const savedTheme = localStorage.getItem('preferred-theme');
    const theme = savedTheme || 'dark';

    root.setAttribute('data-bs-theme', theme);
    toggleSwitch.checked = (theme === 'dark');
    
    updateThemeClasses();

    toggleSwitch.addEventListener('change', function () {
      const isDark = toggleSwitch.checked;
      const newTheme = isDark ? 'dark' : 'light';

      root.setAttribute('data-bs-theme', newTheme);
      localStorage.setItem('preferred-theme', newTheme);
      updateThemeClasses();
    });
  });