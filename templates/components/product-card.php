<?php
// Expects: $p = product array with keys: slug, title, brand_name, base_price, sale_price, currency, primary_image, hover_image, is_new_arrival
$price      = $p['sale_price'] ? $p['sale_price'] : $p['base_price'];
$isOnSale   = !empty($p['sale_price']) && $p['sale_price'] < $p['base_price'];
$symbol     = match($p['currency'] ?? 'NGN') {
    'USD' => '$', 'GBP' => '£', 'EUR' => '€', default => '₦'
};
$formattedPrice = $symbol . number_format((float)$price, 0, '.', ',');
$formattedOrig  = $symbol . number_format((float)$p['base_price'], 0, '.', ',');
?>

<article class="product-card group cursor-pointer">
  <!-- Image Container -->
  <a href="/product/<?= htmlspecialchars($p['slug']) ?>" class="block relative overflow-hidden bg-heritage-slate/20 aspect-[3/4] mb-4">
    <!-- Skeleton -->
    <div class="skeleton absolute inset-0"></div>

    <!-- Primary Image -->
    <?php if (!empty($p['primary_image'])): ?>
      <img src="<?= htmlspecialchars($p['primary_image']) ?>"
           alt="<?= htmlspecialchars($p['title']) ?>"
           loading="lazy"
           class="product-card__primary absolute inset-0 w-full h-full object-cover"
           onload="this.previousElementSibling.style.display='none'">
    <?php endif; ?>

    <!-- Hover Image -->
    <?php if (!empty($p['hover_image'])): ?>
      <img src="<?= htmlspecialchars($p['hover_image']) ?>"
           alt="<?= htmlspecialchars($p['title']) ?> — alternate view"
           loading="lazy"
           class="product-card__hover absolute inset-0 w-full h-full object-cover opacity-0">
    <?php endif; ?>

    <!-- Badges -->
    <div class="absolute top-3 left-3 flex flex-col gap-1.5">
      <?php if (!empty($p['is_new_arrival'])): ?>
        <span class="bg-heritage-green text-white text-[9px] tracking-[0.2em] uppercase px-2.5 py-1 font-medium">
          New In
        </span>
      <?php endif; ?>
      <?php if ($isOnSale): ?>
        <span class="bg-red-600 text-white text-[9px] tracking-[0.15em] uppercase px-2.5 py-1 font-medium">
          Sale
        </span>
      <?php endif; ?>
    </div>

    <!-- Quick Add Button -->
    <div class="absolute bottom-0 left-0 right-0 translate-y-full group-hover:translate-y-0 transition-transform duration-400 ease-luxury">
      <button onclick="Heritage.cart.quickAdd(event, '<?= htmlspecialchars($p['id']) ?>')"
              class="w-full bg-heritage-obsidian text-white text-[10px] tracking-[0.2em] uppercase py-3.5
                     hover:bg-heritage-green transition-colors duration-300">
        Quick Add
      </button>
    </div>

    <!-- Wishlist -->
    <button onclick="Heritage.wishlist.toggle(event, '<?= htmlspecialchars($p['id']) ?>')"
            aria-label="Add to wishlist"
            class="absolute top-3 right-3 text-heritage-obsidian/0 group-hover:text-heritage-obsidian/50
                   hover:!text-heritage-obsidian transition-all duration-300 bg-white/90 rounded-full p-1.5">
      <i data-lucide="heart" class="w-3.5 h-3.5"></i>
    </button>
  </a>

  <!-- Product Info -->
  <a href="/product/<?= htmlspecialchars($p['slug']) ?>">
    <p class="text-[10px] tracking-[0.18em] uppercase text-heritage-obsidian/40 mb-1 font-medium">
      <?= htmlspecialchars($p['brand_name'] ?? '') ?>
    </p>
    <h3 class="text-sm text-heritage-obsidian leading-snug mb-2 group-hover:text-heritage-green
               transition-colors duration-200 line-clamp-2">
      <?= htmlspecialchars($p['title']) ?>
    </h3>
    <div class="flex items-center gap-2">
      <span class="text-sm font-medium <?= $isOnSale ? 'text-red-600' : 'text-heritage-obsidian' ?>">
        <?= $formattedPrice ?>
      </span>
      <?php if ($isOnSale): ?>
        <span class="text-xs text-heritage-obsidian/40 line-through"><?= $formattedOrig ?></span>
      <?php endif; ?>
    </div>
  </a>
</article>
