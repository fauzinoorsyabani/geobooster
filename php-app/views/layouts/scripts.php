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
    var isCatalogPage = document.body.classList.contains('page-catalog');
    var pageSize = isCatalogPage ? 12 : 6;
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

    // ─── Single-Line Hero Sub-Headline Typewriter Rotator (Bottom Line Only, Looping) ───
    (function initBottomLineTypewriter() {
      var el = document.getElementById('hero-rotator');
      if (!el) return;

      var phrases = [
        "Akses Lengkap Dunia AI.",
        "Tools Digital Terverifikasi.",
        "Aktivasi Cepat & Otomatis.",
        "Solusi Digital Terpercaya.",
        "Eksplorasi AI Tanpa Batas."
      ];

      var pIdx = 0;
      var cIdx = 0;
      var isDeleting = false;
      var speed = 60;

      el.textContent = '';

      function step() {
        var curr = phrases[pIdx];
        if (isDeleting) {
          cIdx--;
          el.textContent = curr.substring(0, cIdx);
          speed = 26;
        } else {
          cIdx++;
          el.textContent = curr.substring(0, cIdx);
          speed = 60;
        }

        if (!isDeleting && cIdx === curr.length) {
          isDeleting = true;
          speed = 2200; // Pause 2.2s so visitors can comfortably read
        } else if (isDeleting && cIdx === 0) {
          isDeleting = false;
          pIdx = (pIdx + 1) % phrases.length;
          speed = 320; // Brief pause before typing next phrase
        }
        setTimeout(step, speed);
      }

      // Start typing immediately from beginning (char 0)
      setTimeout(step, 350);
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

    // ─── Robot Mascot Voice & Speech Interaction ("Saya akan lawan!") ───
    (function initMascotVoice() {
      var mascot = document.getElementById('mascot-hero-laptop');
      if (!mascot) return;

      var isHovered = false;
      var customAudio = null;

      // Preload custom audio from /assets/audio/mascot-voice.mp3
      try {
        customAudio = new Audio('/assets/audio/mascot-voice.mp3');
        customAudio.preload = 'auto';
      } catch (e) {
        customAudio = null;
      }

      function speakMascot() {
        mascot.classList.add('is-speaking');

        // Priority 1: Play custom audio file if available
        if (customAudio) {
          try {
            customAudio.currentTime = 0;
            var playPromise = customAudio.play();
            if (playPromise !== undefined) {
              playPromise.then(function() {
                customAudio.onended = function() {
                  if (!isHovered) mascot.classList.remove('is-speaking');
                };
              }).catch(function() {
                fallbackSpeechSynthesis();
              });
              return;
            }
          } catch (e) {
            fallbackSpeechSynthesis();
            return;
          }
        }

        // Priority 2: Fallback to Browser Speech Synthesis
        fallbackSpeechSynthesis();
      }

      function fallbackSpeechSynthesis() {
        if (!('speechSynthesis' in window)) return;
        try {
          window.speechSynthesis.cancel(); // Stop any pending audio immediately

          var utter = new SpeechSynthesisUtterance('Saya akan lawan!');
          utter.lang = 'id-ID';
          utter.rate = 1.15;
          utter.pitch = 1.35; // cute robot-like pitch

          var voices = window.speechSynthesis.getVoices();
          if (voices && voices.length > 0) {
            var idVoice = voices.find(function(v) {
              return v.lang && (v.lang.toLowerCase().indexOf('id') !== -1);
            });
            if (idVoice) utter.voice = idVoice;
          }

          utter.onend = function() {
            if (!isHovered) {
              mascot.classList.remove('is-speaking');
            }
          };
          utter.onerror = function() {
            mascot.classList.remove('is-speaking');
          };

          window.speechSynthesis.speak(utter);
        } catch (err) {}
      }

      function stopMascot() {
        if (customAudio) {
          try {
            customAudio.pause();
            customAudio.currentTime = 0;
          } catch (e) {}
        }
        if ('speechSynthesis' in window) {
          try {
            window.speechSynthesis.cancel(); // Mute/stop immediately
          } catch (err) {}
        }
        mascot.classList.remove('is-speaking');
        mascot.classList.remove('is-hovered');
        mascot.classList.add('is-settling');
      }

      mascot.addEventListener('mouseenter', function() {
        isHovered = true;
        this.classList.remove('is-settling');
        this.classList.add('is-hovered');
        speakMascot();
      });

      mascot.addEventListener('mouseleave', function() {
        isHovered = false;
        stopMascot();
      });

      mascot.addEventListener('click', function(e) {
        e.preventDefault();
        isHovered = true;
        this.classList.remove('is-settling');
        this.classList.add('is-hovered');
        speakMascot();
        setTimeout(function() {
          isHovered = false;
          mascot.classList.remove('is-speaking');
        }, 1800);
      });

      mascot.addEventListener('animationend', function(e) {
        if (e.animationName === 'mascotWiggleDecelerate') {
          this.classList.remove('is-settling');
        }
      });
    })();

    // ─── Shopping Cart System & LocalStorage State ───
    var cartModal = document.getElementById('cart-drawer-modal');
    var cartNavBtn = document.getElementById('cart-nav-btn');
    var btnCloseCart = document.getElementById('btn-close-cart');
    var btnCheckoutCart = document.getElementById('btn-checkout-cart');
    var btnClearCart = document.getElementById('btn-clear-cart');
    var btnBrowseCatalog = document.getElementById('btn-browse-catalog');
    var cartBadgeCount = document.getElementById('cart-badge-count');
    var cartDrawerBadge = document.getElementById('cart-drawer-badge');
    var cartEmptyView = document.getElementById('cart-empty-view');
    var cartItemsView = document.getElementById('cart-items-view');
    var cartItemsList = document.getElementById('cart-items-list');
    var cartFooter = document.getElementById('cart-footer');
    var cartSubtotalVal = document.getElementById('cart-subtotal-val');
    var cartDiscountRow = document.getElementById('cart-discount-row');
    var cartDiscountVal = document.getElementById('cart-discount-val');
    var cartTotalVal = document.getElementById('cart-total-val');

    var ZiuCart = {
      items: [],
      load: function() {
        try {
          var saved = localStorage.getItem('ziulabs_cart');
          this.items = saved ? JSON.parse(saved) : [];
          if (!Array.isArray(this.items)) this.items = [];
        } catch(e) {
          this.items = [];
        }
        this.render();
      },
      save: function() {
        try {
          localStorage.setItem('ziulabs_cart', JSON.stringify(this.items));
        } catch(e) {}
        this.render();
      },
      add: function(product) {
        var existing = this.items.find(function(it) { return it.sku_id == product.skuId; });
        if (existing) {
          existing.quantity += 1;
        } else {
          var priceMinor = parseInt(product.priceMinor, 10) || 5000000;
          this.items.push({
            sku_id: parseInt(product.skuId, 10),
            product_id: parseInt(product.productId, 10) || 1,
            sku: product.sku || 'SKU',
            name: product.name || 'Produk Digital',
            price: product.price || 'Rp50.000',
            price_minor: priceMinor,
            logo: product.logo || '/assets/logos/gemini.webp',
            quantity: 1
          });
        }
        this.save();
        this.open();
      },
      remove: function(skuId) {
        this.items = this.items.filter(function(it) { return it.sku_id != skuId; });
        this.save();
      },
      updateQty: function(skuId, delta) {
        var item = this.items.find(function(it) { return it.sku_id == skuId; });
        if (item) {
          item.quantity = Math.max(1, item.quantity + delta);
          this.save();
        }
      },
      clear: function() {
        this.items = [];
        this.save();
      },
      getTotalMinor: function() {
        return this.items.reduce(function(acc, it) {
          return acc + (it.price_minor * it.quantity);
        }, 0);
      },
      getItemCount: function() {
        return this.items.reduce(function(acc, it) { return acc + it.quantity; }, 0);
      },
      formatRupiah: function(amountMinor) {
        var val = Math.floor(amountMinor / 100);
        return 'Rp' + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
      },
      render: function() {
        var count = this.getItemCount();
        if (cartBadgeCount) {
          cartBadgeCount.textContent = count;
          cartBadgeCount.style.display = count > 0 ? 'inline-block' : 'none';
        }
        if (cartDrawerBadge) {
          cartDrawerBadge.textContent = count + ' Item';
        }

        if (this.items.length === 0) {
          if (cartEmptyView) cartEmptyView.style.display = 'flex';
          if (cartItemsView) cartItemsView.style.display = 'none';
          if (cartFooter) cartFooter.style.display = 'none';
          return;
        }

        if (cartEmptyView) cartEmptyView.style.display = 'none';
        if (cartItemsView) cartItemsView.style.display = 'block';
        if (cartFooter) cartFooter.style.display = 'block';

        if (cartItemsList) {
          var html = '';
          var self = this;
          this.items.forEach(function(item) {
            var itemTotal = item.price_minor * item.quantity;
            html += '<div class="cart-item-card" data-sku-id="' + item.sku_id + '">';
            html += '  <img src="' + item.logo + '" alt="' + item.name + '" class="cart-item-img">';
            html += '  <div class="cart-item-info">';
            html += '    <h4 class="cart-item-title">' + item.name + '</h4>';
            html += '    <div class="cart-item-sku">' + item.sku + ' &bull; ' + item.price + '</div>';
            html += '    <div class="cart-item-bottom">';
            html += '      <div class="cart-item-price">' + self.formatRupiah(itemTotal) + '</div>';
            html += '      <div style="display: flex; align-items: center;">';
            html += '        <div class="cart-qty-ctrl">';
            html += '          <button type="button" class="cart-qty-btn js-qty-minus" data-sku-id="' + item.sku_id + '">&minus;</button>';
            html += '          <span class="cart-qty-val">' + item.quantity + '</span>';
            html += '          <button type="button" class="cart-qty-btn js-qty-plus" data-sku-id="' + item.sku_id + '">&plus;</button>';
            html += '        </div>';
            html += '        <button type="button" class="cart-item-del js-item-del" data-sku-id="' + item.sku_id + '" title="Hapus Item">&times;</button>';
            html += '      </div>';
            html += '    </div>';
            html += '  </div>';
            html += '</div>';
          });
          cartItemsList.innerHTML = html;
        }

        var totalMinor = this.getTotalMinor();
        if (cartSubtotalVal) cartSubtotalVal.textContent = this.formatRupiah(totalMinor);
        if (cartTotalVal) cartTotalVal.textContent = this.formatRupiah(totalMinor);

        // Calculate hemat demo (Gemini promo)
        var totalSavings = this.items.reduce(function(acc, it) {
          if (it.name.toLowerCase().indexOf('gemini') !== -1) {
            return acc + (5000000 * it.quantity);
          }
          return acc;
        }, 0);

        if (cartDiscountRow) {
          if (totalSavings > 0) {
            cartDiscountRow.style.display = 'flex';
            if (cartDiscountVal) cartDiscountVal.textContent = '- ' + this.formatRupiah(totalSavings);
          } else {
            cartDiscountRow.style.display = 'none';
          }
        }
      },
      open: function() {
        if (!cartModal) return;
        this.render();
        cartModal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
      },
      close: function() {
        if (!cartModal) return;
        cartModal.classList.remove('is-open');
        document.body.style.overflow = '';
      }
    };

    // Initialize cart on page load
    ZiuCart.load();

    if (cartNavBtn) {
      cartNavBtn.addEventListener('click', function() { ZiuCart.open(); });
    }
    if (btnCloseCart) {
      btnCloseCart.addEventListener('click', function() { ZiuCart.close(); });
    }
    if (cartModal) {
      cartModal.addEventListener('click', function(e) {
        if (e.target === cartModal) ZiuCart.close();
      });
    }
    if (btnBrowseCatalog) {
      btnBrowseCatalog.addEventListener('click', function() {
        ZiuCart.close();
        var catEl = document.getElementById('catalog');
        if (catEl) catEl.scrollIntoView({ behavior: 'smooth' });
      });
    }
    if (btnClearCart) {
      btnClearCart.addEventListener('click', function() {
        if (confirm('Kosongkan semua item di keranjang?')) {
          ZiuCart.clear();
        }
      });
    }

    // Cart item delegation: plus, minus, delete, add
    document.addEventListener('click', function(e) {
      var plusBtn = e.target.closest('.js-qty-plus');
      if (plusBtn) {
        e.preventDefault();
        ZiuCart.updateQty(plusBtn.dataset.skuId, 1);
        return;
      }
      var minusBtn = e.target.closest('.js-qty-minus');
      if (minusBtn) {
        e.preventDefault();
        ZiuCart.updateQty(minusBtn.dataset.skuId, -1);
        return;
      }
      var delBtn = e.target.closest('.js-item-del');
      if (delBtn) {
        e.preventDefault();
        ZiuCart.remove(delBtn.dataset.skuId);
        return;
      }
      var addCartBtn = e.target.closest('.js-add-to-cart');
      if (addCartBtn) {
        e.preventDefault();
        ZiuCart.add(addCartBtn.dataset);
        return;
      }
    });

    // ─── Instant Checkout Drawer Interaction ───
    var checkoutModal = document.getElementById('checkout-modal');
    var btnCloseCheckout = document.getElementById('btn-close-checkout');
    var checkoutForm = document.getElementById('checkout-form');
    var chkAlert = document.getElementById('checkout-alert');
    var chkSingleSummary = document.getElementById('chk-single-summary');
    var chkCartSummary = document.getElementById('chk-cart-summary');
    var chkCartItemCount = document.getElementById('chk-cart-item-count');
    var chkCartItemsPreview = document.getElementById('chk-cart-items-preview');
    var chkInputCartItems = document.getElementById('chk-input-cart-items');

    function openCheckout(data) {
      if (!checkoutModal) return;
      if (chkInputCartItems) chkInputCartItems.value = '';
      if (chkSingleSummary) chkSingleSummary.style.display = 'flex';
      if (chkCartSummary) chkCartSummary.style.display = 'none';

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
      // Ensure CSRF token is fresh
      var metaCsrf = document.querySelector('meta[name="csrf-token"]');
      var cookieCsrf = (function() {
        var m = document.cookie.match(/(?:^|;\s*)csrf_token=([^;]+)/);
        return m ? decodeURIComponent(m[1]) : '';
      })();
      var freshCsrf = (metaCsrf && metaCsrf.content) || cookieCsrf;
      var csrfInp = document.getElementById('chk-input-csrf');
      if (freshCsrf && csrfInp && (!csrfInp.value || csrfInp.value.trim() === '')) {
        csrfInp.value = freshCsrf;
      }

      checkoutModal.classList.add('is-open');
      document.body.style.overflow = 'hidden';
    }

    function openCartCheckout() {
      if (!checkoutModal || ZiuCart.items.length === 0) return;
      ZiuCart.close();

      if (chkSingleSummary) chkSingleSummary.style.display = 'none';
      if (chkCartSummary) chkCartSummary.style.display = 'block';

      if (chkInputCartItems) {
        var cartPayload = ZiuCart.items.map(function(it) {
          return { sku_id: it.sku_id, quantity: it.quantity };
        });
        chkInputCartItems.value = JSON.stringify(cartPayload);
      }
      document.getElementById('chk-input-sku-id').value = '';
      document.getElementById('chk-input-product-id').value = '';

      var count = ZiuCart.getItemCount();
      if (chkCartItemCount) chkCartItemCount.textContent = count + ' Item';

      if (chkCartItemsPreview) {
        var previewHtml = '';
        ZiuCart.items.forEach(function(it) {
          previewHtml += '<div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.85rem; padding: 4px 0; border-bottom: 1px dashed #e2e8f0;">';
          previewHtml += '  <span style="font-weight: 600; color: #1e293b;">' + it.name + ' <span style="color: #64748b; font-size: 0.78rem;">(' + it.quantity + 'x)</span></span>';
          previewHtml += '  <strong style="color: #0066ff;">' + ZiuCart.formatRupiah(it.price_minor * it.quantity) + '</strong>';
          previewHtml += '</div>';
        });
        chkCartItemsPreview.innerHTML = previewHtml;
      }

      var totalFormatted = ZiuCart.formatRupiah(ZiuCart.getTotalMinor());
      document.getElementById('chk-subtotal').textContent = totalFormatted;
      document.getElementById('chk-grand-total').textContent = totalFormatted;

      if (chkAlert) {
        chkAlert.style.display = 'none';
        chkAlert.textContent = '';
      }
      // Ensure CSRF token is fresh
      var metaCsrf = document.querySelector('meta[name="csrf-token"]');
      var cookieCsrf = (function() {
        var m = document.cookie.match(/(?:^|;\s*)csrf_token=([^;]+)/);
        return m ? decodeURIComponent(m[1]) : '';
      })();
      var freshCsrf = (metaCsrf && metaCsrf.content) || cookieCsrf;
      var csrfInp = document.getElementById('chk-input-csrf');
      if (freshCsrf && csrfInp && (!csrfInp.value || csrfInp.value.trim() === '')) {
        csrfInp.value = freshCsrf;
      }

      checkoutModal.classList.add('is-open');
      document.body.style.overflow = 'hidden';
    }

    if (btnCheckoutCart) {
      btnCheckoutCart.addEventListener('click', openCartCheckout);
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
        
        // Ensure CSRF token is synchronized from cookie or input and sent in headers
        var cookieCsrf = (function() {
          var m = document.cookie.match(/(?:^|;\s*)csrf_token=([^;]+)/);
          return m ? decodeURIComponent(m[1]) : '';
        })();
        var formCsrf = formData.get('_csrf') || '';
        var finalCsrf = formCsrf || cookieCsrf;
        if (finalCsrf) {
          formData.set('_csrf', finalCsrf);
        }

        fetch('/api/checkout.php', {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'X-CSRF-TOKEN': finalCsrf
          },
          body: formData
        })
        .then(function(res) {
          return res.json().then(function(data) {
            return { ok: res.ok, data: data };
          });
        })
        .then(function(result) {
          if (result.ok && result.data.success && result.data.redirect_url) {
            // Clear cart if this was a cart order
            if (chkInputCartItems && chkInputCartItems.value) {
              ZiuCart.clear();
            }
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
      })
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (data && data.csrf_token) {
          document.querySelectorAll('input[name="_csrf"]').forEach(function(inp) {
            inp.value = data.csrf_token;
          });
        }
      })
      .catch(function(e) {});
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
