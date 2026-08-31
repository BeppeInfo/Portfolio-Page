/**
 * Main JS — Mobile nav toggle, language switcher, media tabs, lightboxes.
 * Minimal vanilla JS, no dependencies.
 */

(function () {
  'use strict';

  // ===== Mobile navigation toggle =====
  const navToggle = document.getElementById('navToggle');
  const navMenu = document.getElementById('navMenu');

  if (navToggle && navMenu) {
    navToggle.addEventListener('click', function () {
      navMenu.classList.toggle('open');
      const isOpen = navMenu.classList.contains('open');
      navToggle.setAttribute('aria-expanded', isOpen);
    });

    navMenu.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        navMenu.classList.remove('open');
        navToggle.setAttribute('aria-expanded', 'false');
      });
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && navMenu.classList.contains('open')) {
        navMenu.classList.remove('open');
        navToggle.setAttribute('aria-expanded', 'false');
        navToggle.focus();
      }
    });
  }

  // ===== Active language button =====
  const currentPath = window.location.pathname;
  const urlParams = new URLSearchParams(window.location.search);
  const currentLang = urlParams.get('lang') || 'en';

  document.querySelectorAll('.lang-btn').forEach(function (btn) {
    const btnLang = btn.getAttribute('title');
    if (btnLang === currentLang) {
      btn.classList.add('active');
    }
  });

  // ===== Media category tabs =====
  const mediaTabs = document.querySelectorAll('.media-tab');
  const mediaCategories = document.querySelectorAll('.media-category');

  mediaTabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      const category = this.getAttribute('data-category');

      // Update active tab
      mediaTabs.forEach(function (t) { t.classList.remove('active'); });
      this.classList.add('active');

      // Show/hide categories
      mediaCategories.forEach(function (cat) {
        if (cat.getAttribute('data-category') === category) {
          cat.style.display = 'grid';
        } else {
          cat.style.display = 'none';
        }
      });
    });
  });

  // ===== Video lightbox (lazy-load Peertube embeds) =====
  const videoLightbox = document.getElementById('videoLightbox');
  const videoLightboxClose = document.getElementById('videoLightboxClose');
  const videoLightboxIframe = document.querySelector('.video-lightbox-iframe');

  document.querySelectorAll('.video-placeholder').forEach(function (placeholder) {
    placeholder.addEventListener('click', function () {
      const embedUrl = this.getAttribute('data-embed-url');
      if (!embedUrl) return;

      if (videoLightboxIframe) {
        videoLightboxIframe.innerHTML =
          '<iframe src="' + embedUrl + '" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
      }
      if (videoLightbox) {
        videoLightbox.classList.add('active');
        document.body.style.overflow = 'hidden';
      }
    });
  });

  function closeVideoLightbox() {
    if (videoLightbox) {
      videoLightbox.classList.remove('active');
    }
    if (videoLightboxIframe) {
      videoLightboxIframe.innerHTML = '';
    }
    document.body.style.overflow = '';
  }

  if (videoLightboxClose) {
    videoLightboxClose.addEventListener('click', closeVideoLightbox);
  }
  if (videoLightbox) {
    videoLightbox.addEventListener('click', function (e) {
      if (e.target === videoLightbox) closeVideoLightbox();
    });
  }

  // ===== Image lightbox =====
  const imageLightbox = document.getElementById('imageLightbox');
  const imageLightboxClose = document.getElementById('imageLightboxClose');
  const imageLightboxImg = document.getElementById('imageLightboxImg');

  document.querySelectorAll('.media-image-link').forEach(function (link) {
    link.addEventListener('click', function (e) {
      e.preventDefault();
      const imgSrc = this.getAttribute('href');
      const imgAlt = this.querySelector('img').getAttribute('alt');
      if (imageLightboxImg) {
        imageLightboxImg.src = imgSrc;
        imageLightboxImg.alt = imgAlt;
      }
      if (imageLightbox) {
        imageLightbox.classList.add('active');
        document.body.style.overflow = 'hidden';
      }
    });
  });

  function closeImageLightbox() {
    if (imageLightbox) {
      imageLightbox.classList.remove('active');
    }
    if (imageLightboxImg) {
      imageLightboxImg.src = '';
      imageLightboxImg.alt = '';
    }
    document.body.style.overflow = '';
  }

  if (imageLightboxClose) {
    imageLightboxClose.addEventListener('click', closeImageLightbox);
  }
  if (imageLightbox) {
    imageLightbox.addEventListener('click', function (e) {
      if (e.target === imageLightbox) closeImageLightbox();
    });
  }

  // ===== Close lightboxes on Escape =====
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      closeVideoLightbox();
      closeImageLightbox();
    }
  });
})();
