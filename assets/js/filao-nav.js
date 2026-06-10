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

    // 🍔 Hamburger overlay & Mobile Mainnav 🍔🍔🍔🍔🍔🍔🍔🍔🍔🍔🍔🍔🍔🍔🍔🍔🍔🍔🍔🍔🍔🍔🍔🍔🍔🍔🍔🍔
    var overlay = document.getElementById('fa-hamburger-overlay');
    var openBtn = document.getElementById('fa-menu-open');
    var closeBtn = document.getElementById('fa-menu-close');
    var mainNav = document.querySelector('.fa-mainnav');
    var mainNavClose = document.getElementById('fa-mainnav-close');
    var mobileMoreBtn = document.getElementById('fa-mobile-more');
    
    var rmmMenu = document.getElementById('rmm-menu');
    var rmmOverlay = document.getElementById('rmm-overlay');
    var rmmCloseBtn = document.querySelector('.rmm-close-btn');

    if (openBtn) {
      openBtn.addEventListener('click', function() {
        if (window.innerWidth < 992 && rmmMenu) {
            // Open Rhino mobile menu
            rmmMenu.classList.add('open');
            rmmOverlay.classList.add('open');
            document.body.style.overflow = 'hidden';
        } else if (overlay) {
            // Open PC hamburger overlay
            overlay.classList.add('open');
            document.body.style.overflow = 'hidden';
        }
      });
    }

    if (closeBtn && overlay) {
      closeBtn.addEventListener('click', function() {
        overlay.classList.remove('open');
        document.body.style.overflow = '';
      });
    }
    
    // Close Rhino mobile menu
    if (rmmCloseBtn && rmmMenu) {
        rmmCloseBtn.addEventListener('click', function() {
            rmmMenu.classList.remove('open');
            rmmOverlay.classList.remove('open');
            document.body.style.overflow = '';
            
            // Reset to main panel after 300ms
            setTimeout(function() {
                var panels = document.querySelectorAll('.rmm-panel');
                panels.forEach(function(p) { p.classList.remove('rmm-active', 'rmm-left'); });
                document.getElementById('rmm-panel-main').classList.add('rmm-active');
            }, 300);
        });
        
        rmmOverlay.addEventListener('click', function() {
            rmmCloseBtn.click();
        });
    }

    // Rhino mobile menu drill-down logic
    var rmmTriggers = document.querySelectorAll('.rmm-trigger');
    rmmTriggers.forEach(function(trigger) {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            var targetId = this.getAttribute('data-target');
            var targetPanel = document.getElementById(targetId);
            if (targetPanel) {
                // Find current active panel and slide it left
                var currentPanel = this.closest('.rmm-panel');
                if (currentPanel) {
                    currentPanel.classList.remove('rmm-active');
                    currentPanel.classList.add('rmm-left');
                }
                targetPanel.classList.add('rmm-active');
            }
        });
    });

    var rmmBackBtns = document.querySelectorAll('.rmm-back-btn');
    rmmBackBtns.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var targetId = this.getAttribute('data-target');
            var targetPanel = document.getElementById(targetId);
            if (targetPanel) {
                // Find current panel and remove active
                var currentPanel = this.closest('.rmm-panel');
                if (currentPanel) {
                    currentPanel.classList.remove('rmm-active');
                }
                // Bring back target panel
                targetPanel.classList.remove('rmm-left');
                targetPanel.classList.add('rmm-active');
            }
        });
    });

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
      var defaultImg = imgEl ? imgEl.src : '';
      var defaultCap = capEl ? capEl.textContent : '';

      // Column 1: Click to switch active panel
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

          // Set default image for the new active panel
          if (imgEl && newImg) { imgEl.src = newImg; defaultImg = newImg; }
          if (capEl && newCap) { capEl.textContent = newCap; defaultCap = newCap; }
        });
      });

      // Column 2: Hover to swap image
      var childLinks = menu.querySelectorAll('.mm-panel a');
      childLinks.forEach(function(link) {
        link.addEventListener('mouseenter', function() {
          var hoverImg = link.getAttribute('data-img');
          var hoverCap = link.getAttribute('data-caption');
          if (imgEl && hoverImg) imgEl.src = hoverImg;
          if (capEl && hoverCap) capEl.textContent = hoverCap;
        });
        link.addEventListener('mouseleave', function() {
          if (imgEl) imgEl.src = defaultImg;
          if (capEl) capEl.textContent = defaultCap;
        });
      });
    });

    // ── Search toggle ──────────────────────────────────────
    var searchBtn   = document.getElementById('fa-search-toggle');
    var searchBar   = document.getElementById('fa-search-bar');
    var searchClose = document.getElementById('fa-search-close');
    var searchInput = document.getElementById('fa-search-input');
    var searchResults = document.getElementById('fa-search-results');

    if (searchBtn && searchBar) {
      searchBtn.addEventListener('click', function () {
        var isVisible = searchBar.style.display === 'flex';
        searchBar.style.display = isVisible ? 'none' : 'flex';
        if (!isVisible && searchInput) {
          searchInput.focus();
        }
      });
    }
    if (searchClose && searchBar) {
      searchClose.addEventListener('click', function () {
        searchBar.style.display = 'none';
        if (searchResults) searchResults.style.display = 'none';
      });
    }

    // ── AJAX Search ──────────────────────────────────────
    if (searchInput && searchResults) {
      var searchTimeout;
      searchInput.addEventListener('input', function() {
        var q = this.value.trim();
        clearTimeout(searchTimeout);

        if (q.length < 2) {
          searchResults.style.display = 'none';
          return;
        }

        searchTimeout = setTimeout(function() {
          fetch('ajax-search.php?q=' + encodeURIComponent(q))
            .then(function(res) { return res.json(); })
            .then(function(data) {
              if (data.length === 0) {
                searchResults.innerHTML = '<div style="padding:16px;color:#6B6358;font-size:14px;text-align:center;">No results found</div>';
              } else {
                var html = '<ul style="list-style:none;margin:0;padding:0;">';
                data.forEach(function(item) {
                  html += `
                    <li style="border-bottom:1px solid #E5DDD0;">
                      <a href="${item.url}" style="display:flex;align-items:center;padding:12px 16px;text-decoration:none;transition:background 0.2s;" onmouseover="this.style.background='#FAF8F4'" onmouseout="this.style.background='transparent'">
                        <img src="${item.image}" alt="" style="width:50px;height:50px;object-fit:cover;border-radius:4px;margin-right:16px;">
                        <div>
                          <div style="font-family:'Cormorant Garant',serif;font-size:18px;color:#1C1712;line-height:1.2;margin-bottom:4px;">${item.title}</div>
                          <div style="font-family:'Inter',sans-serif;font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#C49018;">${item.type}</div>
                        </div>
                      </a>
                    </li>
                  `;
                });
                html += '</ul>';
                searchResults.innerHTML = html;
              }
              searchResults.style.display = 'block';
            })
            .catch(function(err) {
              console.error('Search error', err);
            });
        }, 300); // debounce 300ms
      });
      
      // Close search results when clicking outside
      document.addEventListener('click', function(e) {
        if (!e.target.closest('#fa-search-bar')) {
          searchResults.style.display = 'none';
        }
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

    // Rhino mobile menu search trigger
    var rmmSearchBtn = document.querySelector('.rmm-search-btn');
    var globalSearchToggle = document.getElementById('fa-search-toggle');
    if (rmmSearchBtn && globalSearchToggle) {
        rmmSearchBtn.addEventListener('click', function() {
            if (rmmCloseBtn) rmmCloseBtn.click(); // Close mobile menu
            globalSearchToggle.click(); // Open search bar
        });
    }
