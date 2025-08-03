msg_m = document.getElementById("msg-modal");
msg_b = document.getElementById("msg-backdrop");
function close_modal(){
  msg_m.classList.remove('d-block');
  msg_b.classList.remove('show');
  msg_b.classList.add('d-none');
}

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

function calculateRowTotal(input) {
  const row = input.closest('tr');
  const quantity = parseFloat(row.children[1].textContent) || 0;
  const unitPrice = parseFloat(input.value) || 0;
  const total = (quantity * unitPrice).toFixed(2);
  row.querySelector('.total-cell').textContent = total;
  updateGrandTotal();
}

function updateGrandTotal() {
  let grandTotal = 0;
  document.querySelectorAll('.total-cell').forEach(cell => {
      grandTotal += parseFloat(cell.textContent) || 0;
  });
  document.getElementById('grand-total').textContent = grandTotal.toFixed(2);
}