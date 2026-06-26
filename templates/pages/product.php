<?php
$pageTitle = htmlspecialchars($product['title']) . ' — THE HERITAGE EDIT';
$pageDesc  = $product['enrichment']['when_to_wear'] ?? '';
$e = $product['enrichment'] ?? [];
$occasions = $e['right_occasion'] ?? [];
$styleRecs  = $e['style_recommendations'] ?? [];
$symbol = match($product['currency'] ?? 'NGN') {
    'USD' => '$', 'GBP' => '£', 'EUR' => '€', default => '₦'
};
$price = $product['sale_price'] ?? $product['base_price'];
$isOnSale = !empty($product['sale_price']) && $product['sale_price'] < $product['base_price'];

// Group variants
$sizes  = array_unique(array_filter(array_column($product['variants'], 'size')));
$colors = [];
foreach ($product['variants'] as $v) {
    if ($v['color']) $colors[$v['color']] = $v['color_hex'] ?? '#ccc';
}
?>

<div class="max-w-screen-xl mx-auto px-6 lg:px-10 py-8">
  <!-- Breadcrumb -->
  <nav class="flex items-center gap-2 text-xs text-heritage-obsidian/40 mb-10 tracking-wide">
    <a href="/" class="hover:text-heritage-obsidian transition-colors">Home</a>
    <span>/</span>
    <a href="/shop" class="hover:text-heritage-obsidian transition-colors">Shop</a>
    <span>/</span>
    <?php if ($product['brand_name']): ?>
      <a href="/shop?brand=<?= urlencode($product['brand_slug']) ?>"
         class="hover:text-heritage-obsidian transition-colors">
        <?= htmlspecialchars($product['brand_name']) ?>
      </a>
      <span>/</span>
    <?php endif; ?>
    <span class="text-heritage-obsidian line-clamp-1 max-w-xs"><?= htmlspecialchars($product['title']) ?></span>
  </nav>

  <!-- PDP Split Layout -->
  <div class="grid lg:grid-cols-[1fr_460px] xl:grid-cols-[1fr_500px] gap-10 xl:gap-16">

    <!-- LEFT: Vertical Scroll Gallery -->
    <div class="order-2 lg:order-1">
      <?php if (!empty($product['images'])): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <?php foreach ($product['images'] as $i => $img): ?>
            <div class="<?= $i === 0 ? 'md:col-span-2' : '' ?> relative overflow-hidden bg-heritage-slate/20 cursor-zoom-in group"
                 onclick="Heritage.gallery.open(<?= $i ?>)"
                 style="aspect-ratio: <?= $i === 0 ? '4/3' : '3/4' ?>">
              <img src="<?= htmlspecialchars($img['url']) ?>"
                   alt="<?= htmlspecialchars($img['alt_text'] ?? $product['title']) ?>"
                   loading="<?= $i === 0 ? 'eager' : 'lazy' ?>"
                   class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-luxury">
              <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300
                          bg-black/5 flex items-end justify-end p-3">
                <i data-lucide="zoom-in" class="w-4 h-4 text-white"></i>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="aspect-[4/5] bg-heritage-slate/30 flex items-center justify-center">
          <i data-lucide="image" class="w-16 h-16 text-heritage-obsidian/20"></i>
        </div>
      <?php endif; ?>
    </div>

    <!-- RIGHT: Sticky Details Panel -->
    <div class="order-1 lg:order-2">
      <div class="lg:sticky lg:top-28">

        <!-- Brand & Title -->
        <div class="mb-7">
          <?php if ($product['brand_name']): ?>
            <a href="/shop?brand=<?= urlencode($product['brand_slug']) ?>"
               class="text-xs tracking-[0.25em] uppercase text-heritage-obsidian/40
                      hover:text-heritage-obsidian transition-colors font-medium mb-3 block">
              <?= htmlspecialchars($product['brand_name']) ?>
            </a>
          <?php endif; ?>
          <h1 class="font-serif text-2xl lg:text-3xl font-light leading-tight text-heritage-obsidian mb-4">
            <?= htmlspecialchars($product['title']) ?>
          </h1>

          <!-- Price -->
          <div class="flex items-baseline gap-3">
            <span class="text-xl font-medium <?= $isOnSale ? 'text-red-600' : '' ?>">
              <?= $symbol . number_format((float)$price, 0, '.', ',') ?>
            </span>
            <?php if ($isOnSale): ?>
              <span class="text-sm text-heritage-obsidian/40 line-through">
                <?= $symbol . number_format((float)$product['base_price'], 0, '.', ',') ?>
              </span>
              <span class="text-xs bg-red-50 text-red-600 px-2 py-0.5 font-medium">
                Save <?= round((1 - $product['sale_price']/$product['base_price']) * 100) ?>%
              </span>
            <?php endif; ?>
          </div>
        </div>

        <!-- Divider -->
        <div class="w-12 h-px bg-heritage-obsidian/20 mb-7"></div>

        <!-- Color Selection -->
        <?php if (!empty($colors)): ?>
        <div class="mb-6">
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs tracking-[0.15em] uppercase text-heritage-obsidian/50">Colour</span>
            <span id="selected-color" class="text-xs text-heritage-obsidian/70 font-medium"></span>
          </div>
          <div class="flex flex-wrap gap-2.5">
            <?php foreach ($colors as $colorName => $colorHex): ?>
              <button onclick="Heritage.pdp.selectColor(this, '<?= htmlspecialchars($colorName) ?>')"
                      title="<?= htmlspecialchars($colorName) ?>"
                      class="color-btn w-8 h-8 rounded-full border-2 border-transparent
                             hover:border-heritage-obsidian transition-all duration-200"
                      style="background-color: <?= htmlspecialchars($colorHex) ?>">
              </button>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Size Selection -->
        <?php if (!empty($sizes)): ?>
        <div class="mb-6">
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs tracking-[0.15em] uppercase text-heritage-obsidian/50">Size</span>
            <button class="text-xs text-heritage-obsidian/40 hover:text-heritage-obsidian transition-colors underline underline-offset-2">
              Size Guide
            </button>
          </div>
          <div class="flex flex-wrap gap-2">
            <?php foreach ($sizes as $size): ?>
              <button onclick="Heritage.pdp.selectSize(this, '<?= htmlspecialchars($size) ?>')"
                      class="size-btn px-4 py-2.5 border border-heritage-slate text-sm
                             hover:border-heritage-obsidian transition-all duration-200
                             text-heritage-obsidian/60 hover:text-heritage-obsidian
                             data-[selected=true]:border-heritage-obsidian data-[selected=true]:bg-heritage-obsidian data-[selected=true]:text-white">
                <?= htmlspecialchars($size) ?>
              </button>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Size Predictor -->
        <div class="mb-7 p-4 bg-heritage-ivory border border-heritage-slate">
          <div class="flex items-center gap-2 mb-3">
            <i data-lucide="ruler" class="w-3.5 h-3.5 text-heritage-obsidian/40"></i>
            <span class="text-xs tracking-[0.15em] uppercase text-heritage-obsidian/50">Size Predictor</span>
          </div>
          <div class="flex gap-3">
            <select class="flex-1 text-xs border border-heritage-slate bg-white px-3 py-2 outline-none text-heritage-obsidian/70">
              <option value="">Height</option>
              <option>Under 5'4" (163cm)</option>
              <option>5'4"–5'7" (163–170cm)</option>
              <option>5'7"–5'10" (170–178cm)</option>
              <option>Over 5'10" (178cm+)</option>
            </select>
            <select class="flex-1 text-xs border border-heritage-slate bg-white px-3 py-2 outline-none text-heritage-obsidian/70">
              <option value="">Usual Size</option>
              <?php foreach (['XS','S','M','L','XL','XXL','6','8','10','12','14','16'] as $s): ?>
                <option><?= $s ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <!-- Quantity -->
        <div class="flex items-center gap-4 mb-6">
          <div class="flex items-center border border-heritage-slate">
            <button onclick="Heritage.pdp.changeQty(-1)"
                    class="w-10 h-10 flex items-center justify-center text-heritage-obsidian/50
                           hover:text-heritage-obsidian hover:bg-heritage-slate transition-all">
              <i data-lucide="minus" class="w-3.5 h-3.5"></i>
            </button>
            <span id="pdp-qty" class="w-10 text-center text-sm font-medium">1</span>
            <button onclick="Heritage.pdp.changeQty(1)"
                    class="w-10 h-10 flex items-center justify-center text-heritage-obsidian/50
                           hover:text-heritage-obsidian hover:bg-heritage-slate transition-all">
              <i data-lucide="plus" class="w-3.5 h-3.5"></i>
            </button>
          </div>
          <span class="text-xs text-heritage-obsidian/40">
            <?php
            $totalStock = array_sum(array_column($product['variants'], 'stock'));
            if ($totalStock > 0 && $totalStock <= 5) echo "Only $totalStock left";
            elseif ($totalStock === 0) echo "Sold out";
            else echo "In stock";
            ?>
          </span>
        </div>

        <!-- CTA -->
        <div class="flex flex-col gap-3 mb-8">
          <button id="add-to-cart-btn"
                  onclick="Heritage.pdp.addToCart('<?= htmlspecialchars($product['id']) ?>')"
                  class="w-full bg-heritage-green text-white py-4 text-xs tracking-[0.2em] uppercase
                         font-medium hover:bg-heritage-green/90 transition-colors duration-300
                         flex items-center justify-center gap-3">
            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
            Add to My Edit
          </button>
          <button onclick="Heritage.wishlist.toggle(event, '<?= htmlspecialchars($product['id']) ?>')"
                  class="w-full border border-heritage-slate py-4 text-xs tracking-[0.2em] uppercase
                         font-medium text-heritage-obsidian/60 hover:border-heritage-obsidian
                         hover:text-heritage-obsidian transition-all duration-300 flex items-center justify-center gap-3">
            <i data-lucide="heart" class="w-4 h-4"></i>
            Save to Wishlist
          </button>
        </div>

        <!-- Trust Signals -->
        <div class="space-y-3 pt-6 border-t border-heritage-slate">
          <?php foreach ([
            ['truck', 'Complimentary DHL Express on orders above ₦500,000'],
            ['shield-check', 'Authenticity Guaranteed — Every piece verified'],
            ['rotate-ccw', 'Free returns within 14 days of delivery'],
            ['lock', 'Secure payment via Paystack'],
          ] as [$icon, $text]): ?>
            <div class="flex items-center gap-3">
              <i data-lucide="<?= $icon ?>" class="w-3.5 h-3.5 text-heritage-green flex-shrink-0"></i>
              <span class="text-xs text-heritage-obsidian/60"><?= $text ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Heritage Narrative — AI Enrichment Tabs -->
  <?php if ($e && ($e['history_and_heritage'] || $e['when_to_wear'])): ?>
  <div class="mt-20 lg:mt-24 border-t border-heritage-slate pt-16">
    <div class="max-w-3xl">
      <p class="text-xs tracking-[0.3em] uppercase text-heritage-obsidian/30 mb-3">AI-Powered Curation</p>
      <h2 class="font-serif text-3xl lg:text-4xl font-light mb-10">The Heritage Narrative</h2>
    </div>

    <!-- Tab Navigation -->
    <div class="flex gap-0 border-b border-heritage-slate mb-10 overflow-x-auto" id="heritage-tabs">
      <?php
      $tabs = [
        'provenance' => 'Provenance',
        'wear'       => 'When to Wear',
        'occasions'  => 'Occasions',
        'style'      => 'Complete the Look',
      ];
      $first = true;
      foreach ($tabs as $key => $label):
      ?>
        <button onclick="Heritage.tabs.show('<?= $key ?>')"
                id="tab-btn-<?= $key ?>"
                class="tab-btn flex-shrink-0 px-6 py-4 text-xs tracking-[0.15em] uppercase border-b-2 transition-all duration-200
                       <?= $first ? 'border-heritage-obsidian text-heritage-obsidian' : 'border-transparent text-heritage-obsidian/40 hover:text-heritage-obsidian' ?>">
          <?= $label ?>
        </button>
      <?php $first = false; endforeach; ?>
    </div>

    <!-- Tab Panels -->
    <div class="max-w-3xl">

      <!-- Provenance -->
      <div id="tab-provenance" class="tab-panel">
        <?php if ($e['history_and_heritage']): ?>
          <p class="font-serif text-lg leading-relaxed text-heritage-obsidian/80 italic mb-6">
            <?= nl2br(htmlspecialchars($e['history_and_heritage'])) ?>
          </p>
        <?php endif; ?>
        <?php if ($e['material_story']): ?>
          <div class="mt-8 p-6 border-l-4 border-heritage-green bg-heritage-green/5">
            <p class="text-xs tracking-[0.2em] uppercase text-heritage-green/60 mb-2">Material Story</p>
            <p class="text-sm leading-relaxed text-heritage-obsidian/70"><?= htmlspecialchars($e['material_story']) ?></p>
          </div>
        <?php endif; ?>
        <?php if ($e['craftsmanship_notes']): ?>
          <div class="mt-5 p-6 bg-heritage-ivory border border-heritage-slate">
            <p class="text-xs tracking-[0.2em] uppercase text-heritage-obsidian/40 mb-2">Craftsmanship</p>
            <p class="text-sm leading-relaxed text-heritage-obsidian/70"><?= htmlspecialchars($e['craftsmanship_notes']) ?></p>
          </div>
        <?php endif; ?>
      </div>

      <!-- When to Wear -->
      <div id="tab-wear" class="tab-panel hidden">
        <?php if ($e['when_to_wear']): ?>
          <p class="font-serif text-lg leading-relaxed text-heritage-obsidian/80 italic">
            <?= nl2br(htmlspecialchars($e['when_to_wear'])) ?>
          </p>
        <?php endif; ?>
      </div>

      <!-- Occasions -->
      <div id="tab-occasions" class="tab-panel hidden">
        <?php if (!empty($occasions)): ?>
          <div class="grid sm:grid-cols-2 gap-3">
            <?php foreach ($occasions as $occ): ?>
              <div class="flex items-center gap-4 p-5 border border-heritage-slate hover:border-heritage-green transition-colors">
                <div class="w-2 h-2 rounded-full bg-heritage-green flex-shrink-0"></div>
                <span class="text-sm text-heritage-obsidian/80 font-light"><?= htmlspecialchars($occ) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Style Recommendations -->
      <div id="tab-style" class="tab-panel hidden">
        <?php if (!empty($styleRecs)): ?>
          <div class="space-y-4">
            <?php foreach ($styleRecs as $rec): ?>
              <div class="flex items-start gap-5 p-5 bg-heritage-ivory border border-heritage-slate">
                <div class="w-10 h-10 bg-heritage-green/10 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                  <i data-lucide="sparkles" class="w-4 h-4 text-heritage-green"></i>
                </div>
                <div>
                  <p class="font-medium text-sm text-heritage-obsidian mb-1"><?= htmlspecialchars($rec['item'] ?? '') ?></p>
                  <p class="text-xs tracking-wide text-heritage-obsidian/40 uppercase mb-2"><?= htmlspecialchars($rec['category'] ?? '') ?></p>
                  <p class="text-sm text-heritage-obsidian/60 leading-relaxed"><?= htmlspecialchars($rec['reason'] ?? '') ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- Lightbox -->
<div id="gallery-lightbox" class="hidden fixed inset-0 bg-black/95 z-50 flex items-center justify-center">
  <button onclick="Heritage.gallery.close()"
          class="absolute top-6 right-6 text-white/60 hover:text-white">
    <i data-lucide="x" class="w-7 h-7"></i>
  </button>
  <img id="lightbox-img" src="" alt="" class="max-h-[90vh] max-w-[90vw] object-contain">
</div>

<script>
Heritage.pdp = Heritage.pdp || {};
Heritage.pdp._productId = '<?= htmlspecialchars($product['id']) ?>';
Heritage.pdp._qty = 1;
Heritage.pdp._selectedSize = null;
Heritage.pdp._selectedColor = null;
Heritage.pdp._images = <?= json_encode(array_column($product['images'], 'url')) ?>;

Heritage.pdp.changeQty = function(delta) {
  const el = document.getElementById('pdp-qty');
  const v  = Math.max(1, Math.min(10, parseInt(el.textContent) + delta));
  el.textContent = v;
  Heritage.pdp._qty = v;
};

Heritage.pdp.selectSize = function(btn, size) {
  document.querySelectorAll('.size-btn').forEach(b => {
    b.style.background = '';
    b.style.color = '';
    b.style.borderColor = '';
  });
  btn.style.background = '#111';
  btn.style.color = '#fff';
  btn.style.borderColor = '#111';
  Heritage.pdp._selectedSize = size;
};

Heritage.pdp.selectColor = function(btn, color) {
  document.querySelectorAll('.color-btn').forEach(b => b.style.borderColor = 'transparent');
  btn.style.borderColor = '#111';
  Heritage.pdp._selectedColor = color;
  document.getElementById('selected-color').textContent = color;
};

Heritage.pdp.addToCart = async function(productId) {
  const btn = document.getElementById('add-to-cart-btn');
  btn.disabled = true;
  btn.innerHTML = '<span class="animate-pulse">Adding…</span>';

  await Heritage.cart.addItem(productId, null, Heritage.pdp._qty);

  btn.disabled = false;
  btn.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i> Added to Your Edit';
  lucide.createIcons();
  setTimeout(() => {
    btn.innerHTML = '<i data-lucide="shopping-bag" class="w-4 h-4"></i> Add to My Edit';
    lucide.createIcons();
  }, 2000);
};

Heritage.gallery = {
  open(i) {
    document.getElementById('lightbox-img').src = Heritage.pdp._images[i] || '';
    document.getElementById('gallery-lightbox').classList.remove('hidden');
  },
  close() {
    document.getElementById('gallery-lightbox').classList.add('hidden');
  }
};

Heritage.tabs = {
  show(key) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(b => {
      b.classList.remove('border-heritage-obsidian', 'text-heritage-obsidian');
      b.classList.add('border-transparent', 'text-heritage-obsidian/40');
    });
    document.getElementById('tab-' + key).classList.remove('hidden');
    const btn = document.getElementById('tab-btn-' + key);
    btn.classList.add('border-heritage-obsidian', 'text-heritage-obsidian');
    btn.classList.remove('border-transparent', 'text-heritage-obsidian/40');
  }
};
</script>
