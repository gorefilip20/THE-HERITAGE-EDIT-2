/**
 * THE HERITAGE EDIT — Frontend Application
 * Handles: Cart state, Filters, Checkout flow, UI interactions
 */

'use strict';

const Heritage = (() => {
  // ─── Utilities ──────────────────────────────────────────
  const $ = (sel, ctx = document) => ctx.querySelector(sel);
  const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

  function formatCurrency(amount, currency = 'NGN') {
    const symbols = { NGN: '₦', USD: '$', GBP: '£', EUR: '€' };
    return (symbols[currency] || '₦') + Number(amount).toLocaleString('en-NG', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
  }

  async function apiPost(url, data) {
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body: JSON.stringify(data),
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
  }

  async function apiGet(url) {
    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
  }

  // ─── Cart ───────────────────────────────────────────────
  const cart = (() => {
    let _state = { items: [], subtotal: 0, item_count: 0 };

    function _renderItems() {
      const container = $('#cart-items');
      const footer    = $('#cart-footer');
      if (!container) return;

      if (_state.items.length === 0) {
        container.innerHTML = `
          <div class="text-center py-16 text-heritage-obsidian/40">
            <i data-lucide="shopping-bag" class="w-10 h-10 mx-auto mb-3 opacity-30"></i>
            <p class="font-serif text-lg">Your edit is empty</p>
            <p class="text-sm mt-1">Discover our curated collections</p>
          </div>`;
        footer?.classList.add('hidden');
        lucide.createIcons();
        return;
      }

      container.innerHTML = _state.items.map(item => `
        <div class="flex gap-4 pb-5 border-b border-heritage-slate last:border-0 last:pb-0"
             data-item-id="${item.id}">
          <a href="/product/${item.slug}" class="w-20 h-24 bg-heritage-slate/20 flex-shrink-0 overflow-hidden">
            ${item.image
              ? `<img src="${item.image}" alt="${escHtml(item.title)}" class="w-full h-full object-cover">`
              : ''}
          </a>
          <div class="flex-1 min-w-0">
            <p class="text-[10px] tracking-[0.15em] uppercase text-heritage-obsidian/40 mb-0.5">
              ${escHtml(item.brand_name || '')}
            </p>
            <a href="/product/${item.slug}"
               class="text-sm text-heritage-obsidian leading-snug hover:text-heritage-green
                      transition-colors line-clamp-2 mb-1 block">
              ${escHtml(item.title)}
            </a>
            ${[item.size, item.color].filter(Boolean).length
              ? `<p class="text-xs text-heritage-obsidian/40 mb-2">${[item.size, item.color].filter(Boolean).map(escHtml).join(' · ')}</p>`
              : ''}
            <div class="flex items-center justify-between">
              <div class="flex items-center border border-heritage-slate">
                <button onclick="Heritage.cart.updateQty('${item.id}', ${item.quantity - 1})"
                        class="w-7 h-7 flex items-center justify-center text-heritage-obsidian/50
                               hover:text-heritage-obsidian hover:bg-heritage-slate transition-all text-xs">
                  −
                </button>
                <span class="w-7 text-center text-xs font-medium">${item.quantity}</span>
                <button onclick="Heritage.cart.updateQty('${item.id}', ${item.quantity + 1})"
                        class="w-7 h-7 flex items-center justify-center text-heritage-obsidian/50
                               hover:text-heritage-obsidian hover:bg-heritage-slate transition-all text-xs">
                  +
                </button>
              </div>
              <div class="text-right">
                <p class="text-sm font-medium">${formatCurrency(item.unit_price * item.quantity)}</p>
                <button onclick="Heritage.cart.removeItem('${item.id}')"
                        class="text-[10px] text-heritage-obsidian/30 hover:text-red-500 transition-colors tracking-wide">
                  Remove
                </button>
              </div>
            </div>
          </div>
        </div>
      `).join('');

      if (footer) {
        footer.classList.remove('hidden');
        const subtotalEl = $('#cart-subtotal');
        if (subtotalEl) subtotalEl.textContent = formatCurrency(_state.subtotal);
      }

      _updateBadge();
    }

    function _updateBadge() {
      const badge = $('#cart-badge');
      if (!badge) return;
      if (_state.item_count > 0) {
        badge.textContent = _state.item_count > 99 ? '99+' : _state.item_count;
        badge.classList.remove('hidden');
      } else {
        badge.classList.add('hidden');
      }
    }

    async function _refresh() {
      try {
        _state = await apiGet('/api/cart');
        _renderItems();
      } catch (e) {
        console.warn('[Heritage Cart] refresh failed:', e);
      }
    }

    async function addItem(productId, variantId = null, quantity = 1) {
      const data = await apiPost('/api/cart/add', { product_id: productId, variant_id: variantId, quantity });
      _state = data.cart;
      _renderItems();
      open();
    }

    async function quickAdd(event, productId) {
      event.preventDefault();
      event.stopPropagation();
      await addItem(productId);
    }

    async function updateQty(itemId, qty) {
      const data = await apiPost('/api/cart/update', { item_id: itemId, quantity: qty });
      _state = data.cart;
      _renderItems();
    }

    async function removeItem(itemId) {
      const data = await apiPost('/api/cart/remove', { item_id: itemId });
      _state = data.cart;
      _renderItems();
    }

    function open() {
      const drawer  = $('#cart-drawer');
      const overlay = $('#cart-overlay');
      if (!drawer) return;
      drawer.classList.remove('translate-x-full');
      if (overlay) {
        overlay.classList.remove('opacity-0', 'pointer-events-none');
        overlay.classList.add('opacity-100');
      }
      document.body.style.overflow = 'hidden';
    }

    function close() {
      const drawer  = $('#cart-drawer');
      const overlay = $('#cart-overlay');
      if (!drawer) return;
      drawer.classList.add('translate-x-full');
      if (overlay) {
        overlay.classList.add('opacity-0', 'pointer-events-none');
        overlay.classList.remove('opacity-100');
      }
      document.body.style.overflow = '';
    }

    // Init
    document.addEventListener('DOMContentLoaded', _refresh);

    return { open, close, addItem, quickAdd, updateQty, removeItem };
  })();

  // ─── Filters (Catalog) ──────────────────────────────────
  const filters = (() => {
    function _getParams() {
      return new URLSearchParams(window.location.search);
    }

    function _navigate(params) {
      window.location.href = '/shop?' + params.toString();
    }

    function update(input) {
      const params = _getParams();
      if (input.checked) {
        params.set(input.name, input.value);
      } else {
        params.delete(input.name);
      }
      params.delete('page');
      _navigate(params);
    }

    function toggleSize(btn, size) {
      const params = _getParams();
      if (params.get('size') === size) {
        params.delete('size');
      } else {
        params.set('size', size);
      }
      params.delete('page');
      _navigate(params);
    }

    function toggleColor(btn, color) {
      const params = _getParams();
      if (params.get('color') === color) {
        params.delete('color');
      } else {
        params.set('color', color);
      }
      params.delete('page');
      _navigate(params);
    }

    function updateSort(value) {
      const params = _getParams();
      params.set('sort', value);
      params.delete('page');
      _navigate(params);
    }

    function updatePrice(input) {
      const params = _getParams();
      params.set('max_price', input.value);
      const label = $('#price-max-label');
      if (label) label.textContent = '₦' + Number(input.value).toLocaleString();
      clearTimeout(updatePrice._debounce);
      updatePrice._debounce = setTimeout(() => _navigate(params), 500);
    }

    return { update, toggleSize, toggleColor, updateSort, updatePrice };
  })();

  // ─── Catalog Grid ───────────────────────────────────────
  const catalog = (() => {
    function setGrid(cols) {
      const grid = $('#product-grid');
      if (!grid) return;
      grid.className = grid.className.replace(/grid-cols-\d+/, `grid-cols-${cols}`);
    }
    return { setGrid };
  })();

  // ─── Checkout Flow ──────────────────────────────────────
  const checkout = (() => {
    let _shippingRates     = [];
    let _selectedShipping  = null;
    let _landedCost        = { total_taxes: 0 };

    function _updateStep(step) {
      ['info','shipping','payment'].forEach((s, i) => {
        const el = document.getElementById(`step-${s}`);
        if (!el) return;
        el.className = i + 1 <= step
          ? 'text-heritage-obsidian/80'
          : 'text-heritage-obsidian/40';
      });
      [1,2,3].forEach(n => {
        const el = document.getElementById(`checkout-step-${n}`);
        if (el) el.classList.toggle('hidden', n !== step);
      });
    }

    function _getValue(id) {
      const el = document.getElementById(id);
      return el?.value?.trim() || '';
    }

    function goToStep1() { _updateStep(1); }

    async function goToStep2() {
      const required = ['first-name','last-name','email','country','line1','city'];
      for (const id of required) {
        if (!_getValue(id)) {
          document.getElementById(id)?.focus();
          document.getElementById(id)?.classList.add('border-red-400');
          return;
        }
        document.getElementById(id)?.classList.remove('border-red-400');
      }

      _updateStep(2);

      const country    = _getValue('country');
      const subtotal   = window._cartSubtotal || 0;
      const weightGrams = 500;

      const container = document.getElementById('shipping-options');
      if (container) {
        container.innerHTML = '<div class="skeleton h-20 rounded"></div><div class="skeleton h-20 rounded mt-3"></div>';
      }

      try {
        const data = await apiPost('/api/shipping/rates', {
          country, city: _getValue('city'), postal_code: _getValue('postal-code'),
          subtotal, weight_grams: weightGrams,
        });

        _shippingRates = data.rates || [];
        _landedCost    = data.landed_cost || { total_taxes: 0 };

        if (_landedCost.total_taxes > 0) {
          const info = document.getElementById('duties-info');
          const text = document.getElementById('duties-text');
          if (info && text) {
            info.classList.remove('hidden');
            text.textContent = `Estimated duties: ${formatCurrency(_landedCost.duty_amount)} + VAT: ${formatCurrency(_landedCost.vat_amount)} = ${formatCurrency(_landedCost.total_taxes)}. ${_landedCost.disclaimer}`;
          }
        }

        if (container && _shippingRates.length > 0) {
          container.innerHTML = _shippingRates.map((r, i) => `
            <label class="shipping-option flex items-center gap-5 cursor-pointer ${i === 0 ? 'selected' : ''}"
                   onclick="Heritage.checkout.selectShipping(${i}, this)">
              <input type="radio" name="shipping" ${i === 0 ? 'checked' : ''} class="accent-heritage-green">
              <div class="flex-1">
                <div class="flex items-center justify-between">
                  <div>
                    <span class="font-medium text-sm">${escHtml(r.carrier)} ${escHtml(r.service)}</span>
                    <span class="text-xs text-heritage-obsidian/40 ml-3">${escHtml(r.delivery_label)}</span>
                  </div>
                  <span class="font-medium text-sm">${formatCurrency(r.rate, r.currency)}</span>
                </div>
              </div>
            </label>
          `).join('');
          _selectedShipping = _shippingRates[0];
        } else if (container) {
          container.innerHTML = '<p class="text-sm text-heritage-obsidian/50">No shipping options available for this destination.</p>';
        }
      } catch (e) {
        console.error('[Heritage Checkout] Shipping rates error:', e);
        if (container) container.innerHTML = '<p class="text-sm text-red-500">Unable to load shipping rates. Please try again.</p>';
      }
    }

    function selectShipping(index, el) {
      $$('.shipping-option').forEach(o => o.classList.remove('selected'));
      el.classList.add('selected');
      _selectedShipping = _shippingRates[index];
    }

    function goToStep3() {
      if (!_selectedShipping) return;
      _updateStep(3);

      const subtotal      = window._cartSubtotal || 0;
      const shippingCost  = _selectedShipping.rate || 0;
      const dutiesTaxes   = _landedCost.total_taxes || 0;
      const total         = subtotal + shippingCost + dutiesTaxes;

      const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
      set('summary-subtotal', formatCurrency(subtotal));
      set('summary-shipping', formatCurrency(shippingCost, _selectedShipping.currency));
      set('summary-total',    formatCurrency(total));

      if (dutiesTaxes > 0) {
        set('summary-duties', formatCurrency(dutiesTaxes));
        document.getElementById('duties-line')?.classList.remove('hidden');
      }
    }

    async function pay() {
      const btn = document.getElementById('pay-btn');
      if (btn) { btn.disabled = true; btn.textContent = 'Processing…'; }

      const subtotal      = window._cartSubtotal || 0;
      const shippingCost  = _selectedShipping?.rate || 0;
      const dutiesTaxes   = _landedCost?.total_taxes || 0;
      const total         = subtotal + shippingCost + dutiesTaxes;

      try {
        const result = await apiPost('/api/checkout/initialize', {
          email:          _getValue('email'),
          first_name:     _getValue('first-name'),
          last_name:      _getValue('last-name'),
          phone:          _getValue('phone'),
          currency:       'NGN',
          subtotal,
          shipping_cost:  shippingCost,
          duties_taxes:   dutiesTaxes,
          carrier:        _selectedShipping?.carrier,
          shipping_address: {
            first_name:  _getValue('first-name'),
            last_name:   _getValue('last-name'),
            line1:       _getValue('line1'),
            line2:       _getValue('line2'),
            city:        _getValue('city'),
            state:       _getValue('state'),
            postal_code: _getValue('postal-code'),
            country:     _getValue('country'),
            phone:       _getValue('phone'),
          },
        });

        if (result.authorization_url) {
          // Use Paystack inline
          if (typeof PaystackPop !== 'undefined') {
            const handler = PaystackPop.setup({
              key:       window._paystackPublicKey,
              email:     _getValue('email'),
              amount:    Math.round(total * 100),
              currency:  'NGN',
              ref:       result.reference,
              metadata:  { order_id: result.order_id },
              onClose:   () => {
                if (btn) { btn.disabled = false; btn.innerHTML = '<i data-lucide="lock" class="w-3.5 h-3.5 inline mr-2"></i>Pay Securely'; lucide.createIcons(); }
              },
              callback:  (response) => {
                window.location.href = `/checkout/verify?reference=${response.reference}`;
              },
            });
            handler.openIframe();
          } else {
            // Fallback: redirect to Paystack hosted page
            window.location.href = result.authorization_url;
          }
        }
      } catch (e) {
        console.error('[Heritage Checkout] Pay error:', e);
        alert('Payment initialization failed. Please try again.');
        if (btn) { btn.disabled = false; btn.innerHTML = '<i data-lucide="lock" class="w-3.5 h-3.5 inline mr-2"></i>Pay Securely'; lucide.createIcons(); }
      }
    }

    function onCountryChange() {}

    function applyDiscount() {
      const code = _getValue('discount-code');
      if (!code) return;
      // TODO: implement discount code validation
      console.log('[Heritage] Discount code:', code);
    }

    return { goToStep1, goToStep2, goToStep3, selectShipping, pay, onCountryChange, applyDiscount };
  })();

  // ─── Wishlist ───────────────────────────────────────────
  const wishlist = (() => {
    function toggle(event, productId) {
      if (event) { event.preventDefault(); event.stopPropagation(); }
      // TODO: persist to backend
      const btn = event?.currentTarget;
      if (btn) {
        btn.classList.toggle('text-red-500');
        btn.classList.toggle('text-heritage-obsidian/50');
      }
    }
    return { toggle };
  })();

  // ─── Escape HTML ────────────────────────────────────────
  function escHtml(str) {
    return String(str ?? '').replace(/[&<>"']/g, c => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[c]));
  }

  // ─── Init ───────────────────────────────────────────────
  document.addEventListener('DOMContentLoaded', () => {
    lucide.createIcons();

    // Close search modal on Escape
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') {
        document.getElementById('search-modal')?.classList.add('hidden');
        cart.close();
        document.getElementById('gallery-lightbox')?.classList.add('hidden');
      }
    });

    // Intersection observer for fade-in animations
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

    $$('.product-card').forEach((el, i) => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(20px)';
      el.style.transition = `opacity 0.5s ease ${i * 0.06}s, transform 0.5s ease ${i * 0.06}s`;
      observer.observe(el);
    });
  });

  return { cart, filters, catalog, checkout, wishlist, tabs: {} };
})();
