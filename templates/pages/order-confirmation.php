<?php $pageTitle = 'Order Confirmed — THE HERITAGE EDIT'; ?>

<div class="min-h-screen bg-heritage-ivory flex items-center justify-center py-20 px-6">
  <div class="max-w-lg w-full text-center">
    <!-- Checkmark Animation -->
    <div class="w-20 h-20 bg-heritage-green rounded-full flex items-center justify-center mx-auto mb-10"
         style="animation: scaleIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;">
      <svg class="w-9 h-9 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
      </svg>
    </div>

    <p class="text-xs tracking-[0.3em] uppercase text-heritage-obsidian/40 mb-3">Order Confirmed</p>
    <h1 class="font-serif text-3xl lg:text-4xl font-light text-heritage-obsidian mb-3">
      Thank You<?= !empty($order['first_name']) ? ', ' . htmlspecialchars($order['first_name']) : '' ?>.
    </h1>
    <p class="text-heritage-obsidian/50 leading-relaxed mb-8">
      Your edit has been received and is being carefully prepared.
      A confirmation has been sent to <strong><?= htmlspecialchars($order['guest_email'] ?? '') ?></strong>.
    </p>

    <!-- Order Number -->
    <div class="bg-white border border-heritage-slate p-6 mb-8 text-left">
      <div class="flex items-center justify-between mb-5">
        <span class="text-xs tracking-[0.2em] uppercase text-heritage-obsidian/40">Order</span>
        <span class="font-mono text-sm font-medium"><?= htmlspecialchars($order['order_number']) ?></span>
      </div>

      <!-- Order Items -->
      <div class="space-y-4 border-t border-heritage-slate pt-5">
        <?php foreach ($order['items'] as $item): ?>
          <div class="flex justify-between items-start gap-4">
            <div class="flex-1 min-w-0">
              <p class="text-sm text-heritage-obsidian leading-snug"><?= htmlspecialchars($item['product_title']) ?></p>
              <?php if ($item['variant_label']): ?>
                <p class="text-xs text-heritage-obsidian/40 mt-0.5"><?= htmlspecialchars($item['variant_label']) ?></p>
              <?php endif; ?>
              <p class="text-xs text-heritage-obsidian/40 mt-0.5">Qty: <?= $item['quantity'] ?></p>
            </div>
            <span class="text-sm font-medium text-heritage-obsidian flex-shrink-0">
              ₦<?= number_format($item['total_price'], 0, '.', ',') ?>
            </span>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Totals -->
      <div class="border-t border-heritage-slate mt-5 pt-4 space-y-2">
        <div class="flex justify-between text-sm">
          <span class="text-heritage-obsidian/60">Subtotal</span>
          <span>₦<?= number_format($order['subtotal'], 0, '.', ',') ?></span>
        </div>
        <?php if ($order['shipping_cost'] > 0): ?>
        <div class="flex justify-between text-sm">
          <span class="text-heritage-obsidian/60">Shipping (<?= htmlspecialchars($order['shipping_carrier'] ?? '') ?>)</span>
          <span>₦<?= number_format($order['shipping_cost'], 0, '.', ',') ?></span>
        </div>
        <?php endif; ?>
        <?php if ($order['duties_taxes'] > 0): ?>
        <div class="flex justify-between text-sm">
          <span class="text-heritage-obsidian/60">Duties & Taxes</span>
          <span>₦<?= number_format($order['duties_taxes'], 0, '.', ',') ?></span>
        </div>
        <?php endif; ?>
        <div class="flex justify-between font-medium pt-2 border-t border-heritage-slate">
          <span>Total</span>
          <span>₦<?= number_format($order['total'], 0, '.', ',') ?></span>
        </div>
      </div>
    </div>

    <!-- Shipping Address -->
    <?php if (!empty($order['line1'])): ?>
    <div class="text-sm text-heritage-obsidian/60 text-left bg-heritage-ivory border border-heritage-slate p-5 mb-8">
      <p class="text-xs tracking-[0.2em] uppercase text-heritage-obsidian/40 mb-2">Shipping To</p>
      <p><?= htmlspecialchars($order['first_name'] ?? '') ?></p>
      <p><?= htmlspecialchars($order['line1']) ?></p>
      <p><?= htmlspecialchars($order['city']) ?>, <?= htmlspecialchars($order['country']) ?></p>
    </div>
    <?php endif; ?>

    <!-- CTAs -->
    <div class="flex flex-col sm:flex-row gap-3">
      <a href="/shop"
         class="flex-1 bg-heritage-green text-white py-4 text-xs tracking-[0.2em] uppercase
                font-medium hover:bg-heritage-green/90 transition-colors">
        Continue Shopping
      </a>
      <a href="/account/orders"
         class="flex-1 border border-heritage-slate py-4 text-xs tracking-[0.15em] uppercase
                text-heritage-obsidian/60 hover:border-heritage-obsidian hover:text-heritage-obsidian
                transition-all">
        Track Your Order
      </a>
    </div>

    <!-- Promise -->
    <div class="mt-12 pt-8 border-t border-heritage-slate grid grid-cols-3 gap-4">
      <?php foreach ([
        ['truck', 'DHL Tracked Delivery'],
        ['shield-check', 'Authenticity Guaranteed'],
        ['rotate-ccw', '14-Day Free Returns'],
      ] as [$icon, $label]): ?>
        <div class="flex flex-col items-center gap-2 text-center">
          <i data-lucide="<?= $icon ?>" class="w-5 h-5 text-heritage-green"></i>
          <span class="text-xs text-heritage-obsidian/50"><?= $label ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<style>
  @keyframes scaleIn {
    from { transform: scale(0); opacity: 0; }
    to   { transform: scale(1); opacity: 1; }
  }
</style>
