<?php $pageTitle = 'Checkout — THE HERITAGE EDIT'; ?>

<div class="min-h-screen bg-heritage-ivory">
  <!-- Checkout Header -->
  <header class="bg-white border-b border-heritage-slate py-5 px-6 lg:px-10">
    <div class="max-w-screen-xl mx-auto flex items-center justify-between">
      <a href="/" class="font-serif text-xl tracking-[0.2em] uppercase">THE HERITAGE EDIT</a>
      <div class="flex items-center gap-8 text-xs tracking-[0.15em] uppercase text-heritage-obsidian/40">
        <span id="step-info" class="step-active text-heritage-obsidian/80">1 — Information</span>
        <span class="hidden md:block">→</span>
        <span id="step-shipping" class="">2 — Shipping</span>
        <span class="hidden md:block">→</span>
        <span id="step-payment" class="">3 — Payment</span>
      </div>
    </div>
  </header>

  <div class="max-w-screen-xl mx-auto px-6 lg:px-10 py-12">
    <div class="grid lg:grid-cols-[1fr_400px] xl:grid-cols-[1fr_440px] gap-14">

      <!-- LEFT: Checkout Steps -->
      <div>

        <!-- Step 1: Information -->
        <div id="checkout-step-1">
          <h2 class="font-serif text-2xl font-light mb-8">Contact Information</h2>

          <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="checkout-label">First Name</label>
                <input id="first-name" type="text" class="checkout-input" placeholder="Adaeze" required>
              </div>
              <div>
                <label class="checkout-label">Last Name</label>
                <input id="last-name" type="text" class="checkout-input" placeholder="Okafor" required>
              </div>
            </div>

            <div>
              <label class="checkout-label">Email Address</label>
              <input id="email" type="email" class="checkout-input" placeholder="hello@email.com" required>
            </div>

            <div>
              <label class="checkout-label">Phone Number</label>
              <input id="phone" type="tel" class="checkout-input" placeholder="+234 800 000 0000">
            </div>

            <!-- Shipping Address -->
            <div class="pt-6 border-t border-heritage-slate">
              <h3 class="font-serif text-lg font-light mb-5">Shipping Address</h3>
            </div>

            <div>
              <label class="checkout-label">Country</label>
              <select id="country" class="checkout-input" onchange="Heritage.checkout.onCountryChange()">
                <option value="">Select Country</option>
                <?php
                $countries = [
                  'NG'=>'Nigeria','US'=>'United States','GB'=>'United Kingdom',
                  'FR'=>'France','DE'=>'Germany','IT'=>'Italy','AE'=>'UAE',
                  'GH'=>'Ghana','ZA'=>'South Africa','KE'=>'Kenya',
                ];
                foreach ($countries as $code => $name):
                ?>
                  <option value="<?= $code ?>"><?= $name ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div>
              <label class="checkout-label">Street Address</label>
              <input id="line1" type="text" class="checkout-input" placeholder="1, Marina Drive" required>
            </div>

            <div>
              <label class="checkout-label">Apartment, Suite (optional)</label>
              <input id="line2" type="text" class="checkout-input" placeholder="Flat 4B">
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="checkout-label">City</label>
                <input id="city" type="text" class="checkout-input" placeholder="Lagos" required>
              </div>
              <div>
                <label class="checkout-label">State / Province</label>
                <input id="state" type="text" class="checkout-input" placeholder="Lagos" required>
              </div>
            </div>

            <div>
              <label class="checkout-label">Postal Code</label>
              <input id="postal-code" type="text" class="checkout-input" placeholder="100001">
            </div>

            <button onclick="Heritage.checkout.goToStep2()"
                    class="w-full bg-heritage-green text-white py-4 text-xs tracking-[0.2em]
                           uppercase font-medium hover:bg-heritage-green/90 transition-colors mt-4">
              Continue to Shipping
            </button>
          </div>
        </div>

        <!-- Step 2: Shipping -->
        <div id="checkout-step-2" class="hidden">
          <h2 class="font-serif text-2xl font-light mb-8">Shipping Method</h2>

          <!-- Landed Cost Info -->
          <div id="duties-info" class="hidden mb-6 p-5 bg-amber-50 border border-amber-200">
            <div class="flex items-start gap-3">
              <i data-lucide="info" class="w-4 h-4 text-amber-600 mt-0.5 flex-shrink-0"></i>
              <div>
                <p class="text-sm font-medium text-amber-800 mb-1">Estimated Duties & Taxes</p>
                <p class="text-xs text-amber-700" id="duties-text"></p>
              </div>
            </div>
          </div>

          <!-- Shipping Options -->
          <div id="shipping-options" class="space-y-3 mb-8">
            <div class="skeleton h-20 rounded"></div>
            <div class="skeleton h-20 rounded"></div>
          </div>

          <div class="flex gap-3">
            <button onclick="Heritage.checkout.goToStep1()"
                    class="flex-1 border border-heritage-slate py-4 text-xs tracking-[0.15em]
                           uppercase text-heritage-obsidian/60 hover:border-heritage-obsidian
                           hover:text-heritage-obsidian transition-all">
              Back
            </button>
            <button onclick="Heritage.checkout.goToStep3()"
                    class="flex-[2] bg-heritage-green text-white py-4 text-xs tracking-[0.2em]
                           uppercase font-medium hover:bg-heritage-green/90 transition-colors">
              Continue to Payment
            </button>
          </div>
        </div>

        <!-- Step 3: Payment -->
        <div id="checkout-step-3" class="hidden">
          <h2 class="font-serif text-2xl font-light mb-2">Payment</h2>
          <p class="text-sm text-heritage-obsidian/50 mb-8">
            All transactions are secured and encrypted via Paystack.
          </p>

          <!-- Order Summary Recap -->
          <div class="bg-heritage-ivory border border-heritage-slate p-5 mb-8 space-y-2.5">
            <div class="flex justify-between text-sm">
              <span class="text-heritage-obsidian/60">Subtotal</span>
              <span id="summary-subtotal" class="font-medium"></span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-heritage-obsidian/60">Shipping</span>
              <span id="summary-shipping" class="font-medium"></span>
            </div>
            <div id="duties-line" class="flex justify-between text-sm hidden">
              <span class="text-heritage-obsidian/60">Duties & Taxes</span>
              <span id="summary-duties" class="font-medium"></span>
            </div>
            <div class="border-t border-heritage-slate pt-2.5 flex justify-between">
              <span class="font-medium text-sm">Total</span>
              <span id="summary-total" class="font-medium text-base"></span>
            </div>
          </div>

          <!-- Discount Code -->
          <div class="mb-6">
            <div class="flex gap-2">
              <input id="discount-code" type="text" placeholder="Gift card or discount code"
                     class="flex-1 checkout-input">
              <button onclick="Heritage.checkout.applyDiscount()"
                      class="px-5 border border-heritage-slate text-xs tracking-[0.1em] uppercase
                             text-heritage-obsidian/60 hover:border-heritage-obsidian hover:text-heritage-obsidian
                             transition-all whitespace-nowrap">
                Apply
              </button>
            </div>
          </div>

          <div class="flex gap-3">
            <button onclick="Heritage.checkout.goToStep2()"
                    class="flex-1 border border-heritage-slate py-4 text-xs tracking-[0.15em]
                           uppercase text-heritage-obsidian/60 hover:border-heritage-obsidian
                           transition-all">
              Back
            </button>
            <button id="pay-btn" onclick="Heritage.checkout.pay()"
                    class="flex-[2] bg-heritage-green text-white py-4 text-xs tracking-[0.2em]
                           uppercase font-medium hover:bg-heritage-green/90 transition-colors
                           flex items-center justify-center gap-2">
              <i data-lucide="lock" class="w-3.5 h-3.5"></i>
              Pay Securely
            </button>
          </div>

          <div class="flex items-center justify-center gap-3 mt-6">
            <i data-lucide="shield" class="w-3.5 h-3.5 text-heritage-obsidian/30"></i>
            <p class="text-xs text-heritage-obsidian/40 text-center">
              Secured by Paystack · 256-bit SSL encryption
            </p>
          </div>
        </div>

      </div>

      <!-- RIGHT: Order Summary -->
      <div>
        <div class="bg-white border border-heritage-slate p-7 sticky top-10">
          <h3 class="font-serif text-lg font-light mb-6">Your Edit</h3>

          <!-- Cart Items -->
          <div class="space-y-5 mb-6">
            <?php foreach ($cart['items'] as $item): ?>
              <div class="flex gap-4">
                <div class="w-16 h-20 bg-heritage-slate/20 flex-shrink-0 relative overflow-hidden">
                  <?php if ($item['image']): ?>
                    <img src="<?= htmlspecialchars($item['image']) ?>"
                         alt="<?= htmlspecialchars($item['title']) ?>"
                         class="w-full h-full object-cover">
                  <?php endif; ?>
                  <span class="absolute -top-1 -right-1 bg-heritage-obsidian text-white text-[9px]
                               w-4.5 h-4.5 rounded-full flex items-center justify-center font-medium">
                    <?= $item['quantity'] ?>
                  </span>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-[10px] tracking-[0.15em] uppercase text-heritage-obsidian/40 mb-0.5">
                    <?= htmlspecialchars($item['brand_name'] ?? '') ?>
                  </p>
                  <p class="text-sm text-heritage-obsidian leading-snug line-clamp-2 mb-1">
                    <?= htmlspecialchars($item['title']) ?>
                  </p>
                  <?php if ($item['size'] || $item['color']): ?>
                    <p class="text-xs text-heritage-obsidian/40">
                      <?= implode(' · ', array_filter([$item['size'], $item['color']])) ?>
                    </p>
                  <?php endif; ?>
                </div>
                <div class="text-sm font-medium text-heritage-obsidian flex-shrink-0">
                  ₦<?= number_format($item['unit_price'] * $item['quantity'], 0, '.', ',') ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Totals -->
          <div class="border-t border-heritage-slate pt-5 space-y-3">
            <div class="flex justify-between text-sm">
              <span class="text-heritage-obsidian/60">Subtotal (<?= $cart['item_count'] ?> items)</span>
              <span class="font-medium">₦<?= number_format($cart['subtotal'], 0, '.', ',') ?></span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-heritage-obsidian/60">Shipping</span>
              <span class="text-heritage-obsidian/40 text-xs">Calculated at next step</span>
            </div>
            <div class="border-t border-heritage-slate pt-3 flex justify-between">
              <span class="font-medium">Estimated Total</span>
              <span class="font-medium text-base">₦<?= number_format($cart['subtotal'], 0, '.', ',') ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  .checkout-label { display: block; font-size: 10px; letter-spacing: 0.15em; text-transform: uppercase; color: rgba(17,17,17,0.5); margin-bottom: 6px; }
  .checkout-input { width: 100%; border: 1px solid var(--slate); padding: 12px 14px; font-size: 14px; background: white; outline: none; transition: border-color 0.2s; }
  .checkout-input:focus { border-color: var(--green); }
  .shipping-option { border: 1px solid var(--slate); padding: 16px; cursor: pointer; transition: all 0.2s; }
  .shipping-option.selected { border-color: var(--green); background: rgba(13,44,34,0.03); }
  .shipping-option:hover { border-color: var(--obsidian); }
</style>

<script>
window._paystackPublicKey = '<?= htmlspecialchars($paystack_pub_key) ?>';
window._cartSubtotal      = <?= (float)$cart['subtotal'] ?>;
window._cartItems         = <?= json_encode($cart['items']) ?>;
</script>
<script src="https://js.paystack.co/v2/inline.js"></script>
