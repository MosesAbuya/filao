// Filao Adventures   Navigation JS
// Handles: hamburger overlay, mega menu tab switching, transparent→sticky header

(function() {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {

    var header      = document.getElementById('faNavbar');
    var heroSection = document.getElementById('heroSection');

    // ── Hero mode: transparent header ──────────────────────
    if (header && heroSection) {
      var TRIGGER = heroSection.offsetHeight * 0.18;

      window.addEventListener('scroll', function () {
        if (window.scrollY > TRIGGER) {
          header.classList.add('scrolled');
        } else {
          header.classList.remove('scrolled');
        }
      }, { passive: true });

    } else if (header) {
      // Non-hero page: always solid, no logo row
      var logoRow = header.querySelector('.fa-logo-row');
      if (logoRow) logoRow.style.display = 'none';
      header.classList.add('scrolled');

      window.addEventListener('scroll', function () {
        if (window.scrollY > 10) {
          header.style.boxShadow = '0 4px 28px rgba(0,0,0,0.12)';
        } else {
          header.style.boxShadow = '';
        }
      }, { passive: true });
    }

    // ── Hamburger overlay & Mobile Mainnav ─────────────────
    var overlay = document.getElementById('fa-hamburger-overlay');
    var openBtn = document.getElementById('fa-menu-open');
    var closeBtn = document.getElementById('fa-menu-close');
    var mainNav = document.querySelector('.fa-mainnav');
    var mainNavClose = document.getElementById('fa-mainnav-close');
    var mobileMoreBtn = document.getElementById('fa-mobile-more');

    if (openBtn) {
      openBtn.addEventListener('click', function () {
        if (window.innerWidth < 992 && mainNav) {
          // Open the mobile mainnav instead of the side overlay
          mainNav.classList.add('mobile-open');
          document.body.style.overflow = 'hidden';
        } else if (overlay) {
          // PC: Open the standard side overlay
          overlay.classList.add('open');
          document.body.style.overflow = 'hidden';
        }
      });
    }
    
    if (mainNavClose && mainNav) {
      mainNavClose.addEventListener('click', function () {
        mainNav.classList.remove('mobile-open');
        document.body.style.overflow = '';
      });
    }

    if (mobileMoreBtn && mainNav && overlay) {
      mobileMoreBtn.addEventListener('click', function() {
        mainNav.classList.remove('mobile-open');
        overlay.classList.add('open');
      });
    }

    if (closeBtn && overlay) {
      closeBtn.addEventListener('click', function () {
        overlay.classList.remove('open');
        document.body.style.overflow = '';
      });
    }
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        if (overlay && overlay.classList.contains('open')) {
          overlay.classList.remove('open');
          document.body.style.overflow = '';
        }
        if (mainNav && mainNav.classList.contains('mobile-open')) {
          mainNav.classList.remove('mobile-open');
          document.body.style.overflow = '';
        }
        // Also close any open mega menu
        document.querySelectorAll('.fa-subnav-inner > li.open').forEach(function(li) {
          li.classList.remove('open');
        });
        document.body.classList.remove('no-scroll');
      }
    });

    // ── Mega menu toggle (click to open) ──────────────────
    document.querySelectorAll('.nav-top-link').forEach(function (link) {
      link.addEventListener('click', function (e) {
        e.preventDefault();
        var li = link.parentElement;
        var wasOpen = li.classList.contains('open');

        // Close all
        document.querySelectorAll('.fa-subnav-inner > li').forEach(function(item) {
          item.classList.remove('open');
        });

        if (!wasOpen) {
          li.classList.add('open');
          document.body.classList.add('no-scroll');
        } else {
          document.body.classList.remove('no-scroll');
        }
      });
    });

    // Close buttons inside mega menus
    document.querySelectorAll('.mm-close-btn').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        var li = btn.closest('li');
        if (li) li.classList.remove('open');
        document.body.classList.remove('no-scroll');
      });
    });

    // Close mega menu when clicking backdrop
    document.addEventListener('click', function(e) {
      if (!e.target.closest('.fa-subnav-inner') && !e.target.closest('.fa-mainnav')) {
        var anyOpen = false;
        document.querySelectorAll('.fa-subnav-inner > li').forEach(function(item) {
          if (item.classList.contains('open')) anyOpen = true;
          item.classList.remove('open');
        });
        if (anyOpen) document.body.classList.remove('no-scroll');
      }
    });

    // ── Mega menu tabs & Image switch ─────────────────────
    document.querySelectorAll('.fa-megamenu').forEach(function (menu) {
      var tabs   = menu.querySelectorAll('.mm-tab-trigger');
      var panels = menu.querySelectorAll('.mm-panel');
      var imgEl  = menu.querySelector('.fa-mm-image img');
      var capEl  = menu.querySelector('.fa-mm-image .mm-caption');

      tabs.forEach(function (tab) {
        tab.addEventListener('click', function (e) {
          e.preventDefault();
          var target = tab.getAttribute('data-panel');
          var newImg = tab.getAttribute('data-img');
          var newCap = tab.getAttribute('data-caption');

          tabs.forEach(function (t) { t.parentElement.classList.remove('mm-active'); });
          tab.parentElement.classList.add('mm-active');

          panels.forEach(function (p) {
            p.style.display = p.getAttribute('data-id') === target ? 'block' : 'none';
          });

          if (imgEl && newImg) imgEl.src = newImg;
          if (capEl && newCap) capEl.textContent = newCap;
        });
      });
    });

    // ── Search toggle ──────────────────────────────────────
    var searchBtn   = document.getElementById('fa-search-toggle');
    var searchBar   = document.getElementById('fa-search-bar');
    var searchClose = document.getElementById('fa-search-close');

    if (searchBtn && searchBar) {
      searchBtn.addEventListener('click', function () {
        var isVisible = searchBar.style.display === 'flex';
        searchBar.style.display = isVisible ? 'none' : 'flex';
        if (!isVisible) {
          var inp = searchBar.querySelector('input');
          if (inp) inp.focus();
        }
      });
    }
    if (searchClose && searchBar) {
      searchClose.addEventListener('click', function () {
        searchBar.style.display = 'none';
      });
    }

    // ── Active nav link based on current page ─────────────
    var currentPage = window.location.pathname.split('/').pop() || 'index.php';
    document.querySelectorAll('.fa-subnav-inner > li').forEach(function (li) {
      var link = li.querySelector('a');
      if (link) {
        var href = (link.getAttribute('href') || '').replace(/^\//, '');
        if (href && currentPage && currentPage.indexOf(href) !== -1) {
          li.classList.add('active');
        }
      }
    });

  });
})();
