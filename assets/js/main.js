/**
 * Desire Travel - Main Client-Side Controller
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Theme Switcher Handler
  const themeSelectors = document.querySelectorAll('[data-set-theme]');
  themeSelectors.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const theme = btn.getAttribute('data-set-theme');
      document.body.className = theme;
      document.body.setAttribute('data-theme', theme);
      
      // Save cookie and redirect or set URL
      document.cookie = `desire_theme=${theme};path=/;max-age=${86400 * 30}`;
      localStorage.setItem('desire_theme', theme);
      
      // Also update query param for server-side persistence
      const url = new URL(window.location);
      url.searchParams.set('set_theme', theme);
      window.location.href = url.toString();
    });
  });

  // Apply stored theme if present
  const storedTheme = localStorage.getItem('desire_theme');
  if (storedTheme && !document.body.getAttribute('data-theme')) {
    document.body.className = storedTheme;
    document.body.setAttribute('data-theme', storedTheme);
  }

  // 2. Dynamic Live Clock
  const clockEl = document.getElementById('live-clock');
  if (clockEl) {
    const updateClock = () => {
      const now = new Date();
      clockEl.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    };
    updateClock();
    setInterval(updateClock, 1000);
  }

  // 3. Mobile Sidebar Toggle
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar = document.querySelector('.sidebar');
  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => {
      sidebar.classList.toggle('show');
    });
  }

  // 4. Data Filter / Instant Search in Tables
  const searchInputs = document.querySelectorAll('[data-table-search]');
  searchInputs.forEach(input => {
    const targetTableSelector = input.getAttribute('data-table-search');
    const table = document.querySelector(targetTableSelector);
    if (!table) return;

    input.addEventListener('input', (e) => {
      const term = e.target.value.toLowerCase().trim();
      const rows = table.querySelectorAll('tbody tr');
      rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(term) ? '' : 'none';
      });
    });
  });

  // 5. Delete Confirmation Dialogs
  const deleteButtons = document.querySelectorAll('.btn-confirm-delete');
  deleteButtons.forEach(btn => {
    btn.addEventListener('click', (e) => {
      const msg = btn.getAttribute('data-confirm-msg') || 'Are you sure you want to delete this record?';
      if (!confirm(msg)) {
        e.preventDefault();
      }
    });
  });
});
