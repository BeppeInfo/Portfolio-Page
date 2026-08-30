/**
 * Main JS — Mobile nav toggle, language switcher feedback.
 * Minimal vanilla JS, no dependencies.
 */

(function () {
  'use strict';

  // Mobile navigation toggle
  const navToggle = document.getElementById('navToggle');
  const navMenu = document.getElementById('navMenu');

  if (navToggle && navMenu) {
    navToggle.addEventListener('click', function () {
      navMenu.classList.toggle('open');
      const isOpen = navMenu.classList.contains('open');
      navToggle.setAttribute('aria-expanded', isOpen);
    });

    // Close menu when clicking a link
    navMenu.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        navMenu.classList.remove('open');
        navToggle.setAttribute('aria-expanded', 'false');
      });
    });

    // Close menu on Escape
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && navMenu.classList.contains('open')) {
        navMenu.classList.remove('open');
        navToggle.setAttribute('aria-expanded', 'false');
        navToggle.focus();
      }
    });
  }

  // Highlight active language button
  const currentPath = window.location.pathname;
  const urlParams = new URLSearchParams(window.location.search);
  const currentLang = urlParams.get('lang') || 'en';

  document.querySelectorAll('.lang-btn').forEach(function (btn) {
    const btnLang = btn.getAttribute('title');
    if (btnLang === currentLang) {
      btn.classList.add('active');
    }
  });
})();
