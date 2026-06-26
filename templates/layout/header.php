<?php
$currentUri = $_SERVER['REQUEST_URI'] ?? '/';
$navLinks = [
    ['label' => 'Women',       'href' => '/shop?gender=women'],
    ['label' => 'Men',         'href' => '/shop?gender=men'],
    ['label' => 'New Arrivals','href' => '/shop?new_arrivals=1'],
    ['label' => 'Designers',   'href' => '/shop?sort=popular'],
    ['label' => 'The Edit',    'href' => '/shop?sort=featured'],
];
?>

<!-- Marquee Announcement Bar -->
<div class="bg-heritage-green text-white text-xs tracking-[0.2em] uppercase overflow-hidden py-2.5">
  <div class="marquee-inner flex">
    <?php for ($i = 0; $i < 2; $i++): ?>
      <span class="flex items-center gap-12 px-8 whitespace-nowrap">
        <span>Complimentary DHL Express on orders above ₦500,000</span>
        <span class="opacity-40">✦</span>
        <span>New Arrivals: The Riviera Resort Collection</span>
        <span class="opacity-40">✦</span>
        <span>Free Returns Within 14 Days</span>
        <span class="opacity-40">✦</span>
        <span>Secure Payment via Paystack</span>
        <span class="opacity-40">✦</span>
      </span>
    <?php endfor; ?>
  </div>
</div>

<!-- Main Header -->
<header class="bg-white border-b border-heritage-slate sticky top-0 z-30 backdrop-blur-sm bg-white/95">
  <div class="max-w-screen-xl mx-auto px-6 lg:px-10">
    <div class="flex items-center justify-between h-16 lg:h-20">

      <!-- Nav Left -->
      <nav class="hidden lg:flex items-center gap-8">
        <?php foreach (array_slice($navLinks, 0, 2) as $link): ?>
          <a href="<?= $link['href'] ?>"
             class="text-xs tracking-[0.15em] uppercase text-heritage-obsidian/70 hover:text-heritage-obsidian transition-colors duration-200 font-medium">
            <?= $link['label'] ?>
          </a>
        <?php endforeach; ?>
      </nav>

      <!-- Logo (centered) -->
      <a href="/" class="flex-1 lg:flex-none text-center">
        <span class="font-serif text-xl lg:text-2xl tracking-[0.25em] uppercase text-heritage-obsidian font-medium">
          THE HERITAGE EDIT
        </span>
      </a>

      <!-- Nav Right -->
      <div class="flex items-center gap-5">
        <nav class="hidden lg:flex items-center gap-8">
          <?php foreach (array_slice($navLinks, 2) as $link): ?>
            <a href="<?= $link['href'] ?>"
               class="text-xs tracking-[0.15em] uppercase text-heritage-obsidian/70 hover:text-heritage-obsidian transition-colors duration-200 font-medium">
              <?= $link['label'] ?>
            </a>
          <?php endforeach; ?>
        </nav>

        <!-- Search -->
        <button aria-label="Search" onclick="document.getElementById('search-modal').classList.remove('hidden')"
                class="text-heritage-obsidian/60 hover:text-heritage-obsidian transition-colors">
          <i data-lucide="search" class="w-4.5 h-4.5"></i>
        </button>

        <!-- Account -->
        <a href="/account" aria-label="Account"
           class="text-heritage-obsidian/60 hover:text-heritage-obsidian transition-colors">
          <i data-lucide="user" class="w-4.5 h-4.5"></i>
        </a>

        <!-- Wishlist -->
        <a href="/wishlist" aria-label="Wishlist"
           class="text-heritage-obsidian/60 hover:text-heritage-obsidian transition-colors">
          <i data-lucide="heart" class="w-4.5 h-4.5"></i>
        </a>

        <!-- Cart -->
        <button onclick="Heritage.cart.open()" aria-label="Shopping bag"
                class="relative text-heritage-obsidian/60 hover:text-heritage-obsidian transition-colors">
          <i data-lucide="shopping-bag" class="w-4.5 h-4.5"></i>
          <span id="cart-badge"
                class="absolute -top-2 -right-2 bg-heritage-green text-white text-[9px] w-4 h-4 rounded-full flex items-center justify-center font-medium leading-none hidden">
            0
          </span>
        </button>

        <!-- Mobile Menu -->
        <button class="lg:hidden text-heritage-obsidian/60 hover:text-heritage-obsidian transition-colors"
                onclick="document.getElementById('mobile-menu').classList.toggle('hidden')">
          <i data-lucide="menu" class="w-5 h-5"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- Mobile Menu -->
  <div id="mobile-menu" class="hidden lg:hidden border-t border-heritage-slate bg-white">
    <nav class="px-6 py-4 flex flex-col gap-4">
      <?php foreach ($navLinks as $link): ?>
        <a href="<?= $link['href'] ?>"
           class="text-sm tracking-[0.1em] uppercase text-heritage-obsidian/70 hover:text-heritage-obsidian transition-colors py-1">
          <?= $link['label'] ?>
        </a>
      <?php endforeach; ?>
    </nav>
  </div>
</header>

<!-- Search Modal -->
<div id="search-modal" class="hidden fixed inset-0 z-50 bg-white/98 backdrop-blur-sm">
  <div class="max-w-2xl mx-auto px-6 pt-24">
    <div class="flex items-center justify-between mb-8">
      <h2 class="font-serif text-2xl">Search</h2>
      <button onclick="document.getElementById('search-modal').classList.add('hidden')"
              class="text-heritage-obsidian/50 hover:text-heritage-obsidian">
        <i data-lucide="x" class="w-6 h-6"></i>
      </button>
    </div>
    <form action="/shop" method="GET">
      <div class="relative">
        <input type="text" name="q" placeholder="Search designers, pieces, collections…"
               class="w-full border-0 border-b-2 border-heritage-slate focus:border-heritage-green
                      text-xl py-4 bg-transparent outline-none placeholder-heritage-obsidian/30
                      transition-colors duration-300"
               autofocus>
        <button type="submit" class="absolute right-0 top-4 text-heritage-obsidian/50 hover:text-heritage-obsidian">
          <i data-lucide="search" class="w-6 h-6"></i>
        </button>
      </div>
    </form>
    <div class="mt-10">
      <p class="text-xs tracking-[0.15em] uppercase text-heritage-obsidian/40 mb-4">Trending Searches</p>
      <div class="flex flex-wrap gap-2">
        <?php foreach (['Valentino', 'Bottega Veneta', 'The Row', 'Silk Gown', 'Trench Coat'] as $term): ?>
          <a href="/shop?q=<?= urlencode($term) ?>"
             class="px-4 py-2 border border-heritage-slate text-sm text-heritage-obsidian/60
                    hover:border-heritage-obsidian hover:text-heritage-obsidian transition-colors">
            <?= $term ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
