<?php

declare(strict_types=1);

/**
 * Layout: Scripts & Closing Body/HTML tags
 * @var array $app
 * @var array $products
 */
?>
  <!-- ─── Interactive Scripts (Hardware Accelerated, Zero Lag) ─── -->
  <script>
  (function() {
    var products = <?= json_encode($products, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    var waNumber = '<?= preg_replace('/[^0-9]/', '', $app['whatsapp_number']) ?>';
    var pageSize = 6;
    var currentPage = 1;
    var currentFilter = 'all';
    var searchQuery = '';

    var cells = Array.from(document.querySelectorAll('.product-card'));
    var pageCounter = document.querySelector('.page-counter');
    var btnPrev = document.querySelector('.page-btn-prev');
    var btnNext = document.querySelector('.page-btn-next');
    var searchInput = document.getElementById('catalog-search');

    function updateCatalog() {
      var visible = cells.filter(function(card) {
        var matchesCat = (currentFilter === 'all' || card.dataset.category === currentFilter);
        var matchesQuery = (!searchQuery || card.dataset.name.indexOf(searchQuery) !== -1);
        return matchesCat && matchesQuery;
      });

      var totalPages = Math.max(1, Math.ceil(visible.length / pageSize));
      currentPage = Math.min(currentPage, totalPages);

      cells.forEach(function(c) { c.hidden = true; });
      visible.slice((currentPage - 1) * pageSize, currentPage * pageSize).forEach(function(c) {
        c.hidden = false;
      });

      if (pageCounter) {
        pageCounter.textContent = 'Halaman ' + currentPage + ' dari ' + totalPages + ' (' + visible.length + ' SKU)';
      }
      if (btnPrev) btnPrev.disabled = (currentPage === 1);
      if (btnNext) btnNext.disabled = (currentPage === totalPages);
    }

    // Tab buttons
    document.querySelectorAll('.filter-tab').forEach(function(btn) {
      btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-tab').forEach(function(t) { t.classList.remove('is-active'); });
        btn.classList.add('is-active');
        currentFilter = btn.dataset.filter;
        currentPage = 1;
        updateCatalog();
      });
    });

    // Realtime search input
    if (searchInput) {
      searchInput.addEventListener('input', function() {
        searchQuery = this.value.trim().toLowerCase();
        currentPage = 1;
        updateCatalog();
      });
    }

    // Pagination
    if (btnPrev) {
      btnPrev.addEventListener('click', function() {
        if (currentPage > 1) { currentPage--; updateCatalog(); }
      });
    }
    if (btnNext) {
      btnNext.addEventListener('click', function() {
        var visible = cells.filter(function(card) {
          var matchesCat = (currentFilter === 'all' || card.dataset.category === currentFilter);
          var matchesQuery = (!searchQuery || card.dataset.name.indexOf(searchQuery) !== -1);
          return matchesCat && matchesQuery;
        });
        if (currentPage < Math.ceil(visible.length / pageSize)) { currentPage++; updateCatalog(); }
      });
    }

    // ─── 3D Showcase Card Tilt ───
    var board = document.getElementById('interactive-board');
    if (board && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      var isTicking = false;
      board.addEventListener('mousemove', function(e) {
        if (!isTicking) {
          window.requestAnimationFrame(function() {
            var rect = board.getBoundingClientRect();
            var x = (e.clientX - rect.left) / rect.width - 0.5;
            var y = (e.clientY - rect.top) / rect.height - 0.5;
            board.style.transform = 'perspective(1000px) rotateX(' + (-y * 8).toFixed(2) + 'deg) rotateY(' + (x * 8).toFixed(2) + 'deg) translateY(-4px)';
            isTicking = false;
          });
          isTicking = true;
        }
      });
      board.addEventListener('mouseleave', function() {
        board.style.transform = '';
      });
    }

    // ─── ⌘K Modal Logic ───
    var modal = document.getElementById('palette-modal');
    var cmdkBtn = document.getElementById('cmdk-btn');
    var pSearch = document.getElementById('palette-search');
    var pResults = document.getElementById('palette-results');

    function openCmdk() {
      if (!modal) return;
      modal.classList.add('is-visible');
      renderCmdk('');
      if (pSearch) { pSearch.value = ''; pSearch.focus(); }
    }
    function closeCmdk() {
      if (!modal) return;
      modal.classList.remove('is-visible');
    }

    function renderCmdk(query) {
      if (!pResults) return;
      var q = query.toLowerCase();
      var matches = products.filter(function(p) {
        return !q || p.name.toLowerCase().indexOf(q) !== -1 || p.sku.toLowerCase().indexOf(q) !== -1;
      });

      pResults.innerHTML = '';
      if (matches.length === 0) {
        pResults.innerHTML = '<div style="padding: 20px; text-align: center; color: var(--ink-muted); font-size: 13px;">Tidak ada produk yang cocok</div>';
        return;
      }

      matches.slice(0, 6).forEach(function(p, i) {
        var a = document.createElement('a');
        a.className = 'palette-item' + (i === 0 ? ' is-selected' : '');
        var msg = p.available 
          ? 'Halo ZiuLabs, saya ingin menanyakan harga dan pemesanan paket [' + p.sku + '] ' + p.name + '.'
          : 'Halo ZiuLabs, saya ingin info stok [' + p.sku + '] ' + p.name + '.';
        a.href = 'https://wa.me/' + waNumber + '?text=' + encodeURIComponent(msg);
        a.target = '_blank';
        a.rel = 'noopener';
        a.innerHTML = '<div><strong>[' + p.sku + ']</strong> ' + p.name + '</div><div style="font-family: \'JetBrains Mono\', monospace; font-size: 11px; font-weight: 700; color: var(--blue-ziulabs); white-space: nowrap;">Tanya WA ↗</div>';
        pResults.appendChild(a);
      });
    }

    if (cmdkBtn) cmdkBtn.addEventListener('click', openCmdk);
    if (modal) {
      modal.addEventListener('click', function(e) {
        if (e.target === modal) closeCmdk();
      });
    }
    if (pSearch) {
      pSearch.addEventListener('input', function() {
        renderCmdk(this.value.trim());
      });
    }
    window.addEventListener('keydown', function(e) {
      if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        if (modal && modal.classList.contains('is-visible')) closeCmdk();
        else openCmdk();
      }
      if (e.key === 'Escape' && modal && modal.classList.contains('is-visible')) {
        closeCmdk();
      }
    });

    // ─── Web3 Headline Text Rotator (Smooth Typewriter) ───
    (function initRotator() {
      var el = document.getElementById('hero-rotator');
      if (!el) return;
      var phrases = [
        "Akses Lengkap Dunia AI.",
        "Tools Digital Terverifikasi.",
        "Solusi Kerja Produktif.",
        "Aktivasi Instan & Bergaransi."
      ];
      var pIdx = 0;
      var cIdx = phrases[0].length;
      var isDeleting = false;
      var speed = 65;

      function step() {
        var curr = phrases[pIdx];
        if (isDeleting) {
          cIdx--;
          el.textContent = curr.substring(0, cIdx);
          speed = 30;
        } else {
          cIdx++;
          el.textContent = curr.substring(0, cIdx);
          speed = 65;
        }

        if (!isDeleting && cIdx === curr.length) {
          isDeleting = true;
          speed = 2200;
        } else if (isDeleting && cIdx === 0) {
          isDeleting = false;
          pIdx = (pIdx + 1) % phrases.length;
          speed = 400;
        }
        setTimeout(step, speed);
      }
      setTimeout(step, 2000);
    })();

    // ─── Web3 Spotlight ───
    var spotlight = document.getElementById('grid-spotlight');
    if (spotlight && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
      var ticking = false;
      window.addEventListener('mousemove', function(e) {
        if (!ticking) {
          window.requestAnimationFrame(function() {
            spotlight.style.background = 'radial-gradient(550px circle at ' + e.clientX + 'px ' + e.clientY + 'px, rgba(0, 102, 255, 0.16) 0%, transparent 80%)';
            ticking = false;
          });
          ticking = true;
        }
      }, { passive: true });
    }

    // ─── Cute Mascot Hover & Deceleration Damping ───
    var mascotLaptop = document.getElementById('mascot-hero-laptop');
    if (mascotLaptop) {
      mascotLaptop.addEventListener('mouseenter', function() {
        this.classList.remove('is-settling');
        this.classList.add('is-hovered');
      });
      mascotLaptop.addEventListener('mouseleave', function() {
        this.classList.remove('is-hovered');
        this.classList.add('is-settling');
      });
      mascotLaptop.addEventListener('animationend', function(e) {
        if (e.animationName === 'mascotWiggleDecelerate') {
          this.classList.remove('is-settling');
        }
      });
    }

    // ─── Instant Checkout Drawer Interaction ───
    var checkoutModal = document.getElementById('checkout-modal');
    var btnCloseCheckout = document.getElementById('btn-close-checkout');
    var checkoutForm = document.getElementById('checkout-form');
    var chkAlert = document.getElementById('checkout-alert');

    function openCheckout(data) {
      if (!checkoutModal) return;
      document.getElementById('chk-input-product-id').value = data.productId || '';
      document.getElementById('chk-input-sku-id').value = data.skuId || '';
      document.getElementById('chk-name').textContent = data.name || 'Produk Digital';
      document.getElementById('chk-sku').textContent = data.sku || 'SKU';
      document.getElementById('chk-price').textContent = data.price || 'Rp0';
      document.getElementById('chk-subtotal').textContent = data.price || 'Rp0';
      document.getElementById('chk-grand-total').textContent = data.price || 'Rp0';
      if (data.logo) {
        document.getElementById('chk-logo').src = data.logo;
      }
      if (chkAlert) {
        chkAlert.style.display = 'none';
        chkAlert.textContent = '';
      }
      checkoutModal.classList.add('is-open');
      document.body.style.overflow = 'hidden';
    }

    function closeCheckout() {
      if (!checkoutModal) return;
      checkoutModal.classList.remove('is-open');
      document.body.style.overflow = '';
    }

    document.addEventListener('click', function(e) {
      var buyBtn = e.target.closest('.js-open-checkout');
      if (buyBtn) {
        e.preventDefault();
        openCheckout(buyBtn.dataset);
      }
    });

    if (btnCloseCheckout) {
      btnCloseCheckout.addEventListener('click', closeCheckout);
    }
    if (checkoutModal) {
      checkoutModal.addEventListener('click', function(e) {
        if (e.target === checkoutModal) closeCheckout();
      });
    }

    // Payment method selector visual toggle
    var pmRadios = document.querySelectorAll('input[name="payment_method"]');
    pmRadios.forEach(function(radio) {
      radio.addEventListener('change', function() {
        document.querySelectorAll('.payment-method-card').forEach(function(card) {
          card.classList.remove('is-selected');
        });
        radio.closest('.payment-method-card').classList.add('is-selected');
      });
    });

    // Handle AJAX Checkout Form Submit
    if (checkoutForm) {
      checkoutForm.addEventListener('submit', function(e) {
        e.preventDefault();
        var submitBtn = document.getElementById('btn-submit-order');
        var btnText = submitBtn.querySelector('.btn-text');
        var btnSpinner = submitBtn.querySelector('.btn-spinner');

        submitBtn.disabled = true;
        if (btnText) btnText.style.display = 'none';
        if (btnSpinner) btnSpinner.style.display = 'inline';

        var formData = new FormData(checkoutForm);

        fetch('/api/checkout.php', {
          method: 'POST',
          body: formData
        })
        .then(function(res) {
          return res.json().then(function(data) {
            return { ok: res.ok, data: data };
          });
        })
        .then(function(result) {
          if (result.ok && result.data.success && result.data.redirect_url) {
            window.location.href = result.data.redirect_url;
          } else {
            throw new Error(result.data.error || 'Terjadi kesalahan saat memproses pesanan.');
          }
        })
        .catch(function(err) {
          submitBtn.disabled = false;
          if (btnText) btnText.style.display = 'inline';
          if (btnSpinner) btnSpinner.style.display = 'none';
          if (chkAlert) {
            chkAlert.style.display = 'block';
            chkAlert.textContent = err.message;
          }
        });
      });
    }

    // ─── Clerk Authentication Integration ───
    var btnClerkSignIn = document.getElementById('btn-clerk-sign-in');
    var clerkUserButtonMount = document.getElementById('clerk-user-button-mount');
    var btnHeaderDash = document.getElementById('btn-header-dash');

    function syncClerkUserToBackend(user) {
      if (!user) return;
      var email = user.primaryEmailAddress ? user.primaryEmailAddress.emailAddress : '';
      var name = user.fullName || (user.firstName ? (user.firstName + ' ' + (user.lastName || '')).trim() : 'Pelanggan');
      var avatar = user.imageUrl || '';
      var clerkId = user.id;

      fetch('/api/clerk-sync.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          clerk_id: clerkId,
          email: email,
          name: name,
          avatar: avatar
        })
      }).catch(function(e) {});
    }

    function renderClerkState(user) {
      if (user) {
        if (btnClerkSignIn) btnClerkSignIn.style.display = 'none';
        if (btnHeaderDash) btnHeaderDash.style.display = 'inline-flex';
        if (clerkUserButtonMount) {
          clerkUserButtonMount.style.display = 'inline-flex';
          clerkUserButtonMount.innerHTML = '';
          Clerk.mountUserButton(clerkUserButtonMount, {
            afterSignOutUrl: '/'
          });
        }
        syncClerkUserToBackend(user);

        var chkCustName = document.getElementById('chk-customer-name');
        var chkCustEmail = document.getElementById('chk-customer-email');
        if (chkCustName && !chkCustName.value) {
          chkCustName.value = user.fullName || user.firstName || '';
        }
        if (chkCustEmail && !chkCustEmail.value && user.primaryEmailAddress) {
          chkCustEmail.value = user.primaryEmailAddress.emailAddress;
        }
      } else {
        if (btnClerkSignIn) {
          btnClerkSignIn.style.display = 'inline-flex';
          btnClerkSignIn.onclick = function(e) {
            e.preventDefault();
            if (typeof Clerk !== 'undefined') {
              Clerk.openSignIn();
            }
          };
        }
        if (clerkUserButtonMount) clerkUserButtonMount.style.display = 'none';
        if (btnHeaderDash) btnHeaderDash.style.display = 'none';
      }
    }

    async function initClerk() {
      if (typeof Clerk === 'undefined') {
        setTimeout(initClerk, 80);
        return;
      }
      try {
        await Clerk.load();
        renderClerkState(Clerk.user);
        Clerk.addListener(function(emission) {
          renderClerkState(emission.user);
        });
      } catch (err) {
        console.error('Clerk load error:', err);
      }
    }

    if (document.readyState === 'loading') {
      window.addEventListener('load', initClerk);
    } else {
      initClerk();
    }

    updateCatalog();
  })();
  </script>
</body>
</html>
