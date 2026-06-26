<?php $pageTitle = 'THE HERITAGE EDIT — Ultra-Luxury Fashion'; ?>

<!-- Hero -->
<section class="relative h-[92vh] min-h-[600px] overflow-hidden">
  <!-- Background -->
  <div class="absolute inset-0 bg-gradient-to-br from-heritage-green via-heritage-green/90 to-heritage-purple"></div>

  <!-- Parallax Grid Image -->
  <div class="absolute inset-0 opacity-20" style="background-image: url('/assets/images/hero-texture.jpg'); background-size: cover; background-position: center;"></div>

  <!-- Geometric accent -->
  <div class="absolute top-0 right-0 w-1/2 h-full">
    <div class="absolute inset-0 bg-white/5 skew-x-12 origin-top-right"></div>
  </div>

  <div class="relative z-10 max-w-screen-xl mx-auto px-6 lg:px-10 h-full flex items-center">
    <div class="max-w-2xl" id="hero-content">
      <p class="text-white/50 text-xs tracking-[0.3em] uppercase mb-6 font-medium"
         style="animation: fadeInUp 0.8s ease forwards 0.2s; opacity: 0;">
        Summer/Autumn 2025 Collection
      </p>
      <h1 class="font-serif text-5xl md:text-6xl lg:text-7xl text-white leading-[1.08] mb-8 font-light"
          style="animation: fadeInUp 0.9s ease forwards 0.4s; opacity: 0;">
        Dressed in<br>
        <em class="italic font-normal">Heritage.</em><br>
        <span class="font-medium">Worn in Story.</span>
      </h1>
      <p class="text-white/60 text-base lg:text-lg leading-relaxed mb-10 font-light max-w-lg"
         style="animation: fadeInUp 0.9s ease forwards 0.6s; opacity: 0;">
        Every silhouette carries centuries of craftsmanship. Discover pieces curated
        from the world's most storied fashion houses.
      </p>
      <div class="flex flex-wrap gap-4"
           style="animation: fadeInUp 0.9s ease forwards 0.8s; opacity: 0;">
        <a href="/shop"
           class="bg-white text-heritage-green px-9 py-4 text-xs tracking-[0.2em] uppercase font-medium
                  hover:bg-heritage-ivory transition-all duration-300 hover:-translate-y-0.5">
          Explore The Edit
        </a>
        <a href="/shop?new_arrivals=1"
           class="border border-white/40 text-white px-9 py-4 text-xs tracking-[0.2em] uppercase font-medium
                  hover:border-white hover:bg-white/10 transition-all duration-300 hover:-translate-y-0.5">
          New Arrivals
        </a>
      </div>
    </div>
  </div>

  <!-- Scroll indicator -->
  <div class="absolute bottom-10 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2"
       style="animation: fadeIn 1s ease forwards 1.5s; opacity: 0;">
    <span class="text-white/40 text-[10px] tracking-[0.3em] uppercase">Scroll</span>
    <div class="w-px h-12 bg-white/20 relative overflow-hidden">
      <div class="absolute inset-0 bg-white/60" style="animation: scrollLine 1.5s ease infinite;"></div>
    </div>
  </div>
</section>

<style>
  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(24px); }
    to   { opacity: 1; transform: translateY(0); }
  }
  @keyframes fadeIn {
    from { opacity: 0; } to { opacity: 1; }
  }
  @keyframes scrollLine {
    0%   { transform: translateY(-100%); }
    100% { transform: translateY(200%); }
  }
</style>

<!-- Category Grid -->
<section class="py-20 lg:py-28 max-w-screen-xl mx-auto px-6 lg:px-10">
  <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
    <?php $cats = [
      ['Women',  '/shop?gender=women', 'bg-heritage-green',  'text-white'],
      ['Men',    '/shop?gender=men',   'bg-heritage-purple', 'text-white'],
      ['New In', '/shop?new_arrivals=1','bg-heritage-obsidian','text-white'],
    ]; foreach ($cats as [$label, $href, $bg, $tc]): ?>
      <a href="<?= $href ?>"
         class="<?= $bg ?> <?= $tc ?> group relative h-80 lg:h-96 overflow-hidden flex items-end p-8">
        <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500
                    bg-gradient-to-t from-black/30 to-transparent"></div>
        <div class="relative z-10 flex items-end justify-between w-full">
          <span class="font-serif text-3xl font-light"><?= $label ?></span>
          <span class="text-xs tracking-[0.2em] uppercase opacity-60 group-hover:opacity-100 transition-opacity">
            Shop Now →
          </span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- Featured Products -->
<?php if (!empty($featured)): ?>
<section class="py-4 pb-24 max-w-screen-xl mx-auto px-6 lg:px-10">
  <div class="flex items-end justify-between mb-12">
    <div>
      <p class="text-xs tracking-[0.25em] uppercase text-heritage-obsidian/40 mb-2">Curated Selections</p>
      <h2 class="font-serif text-3xl lg:text-4xl font-light">The Heritage Favourites</h2>
    </div>
    <a href="/shop?sort=popular"
       class="hidden md:inline-flex text-xs tracking-[0.15em] uppercase text-heritage-obsidian/60
              hover:text-heritage-obsidian transition-colors border-b border-heritage-obsidian/20
              hover:border-heritage-obsidian pb-0.5">
      View All
    </a>
  </div>

  <div class="grid grid-cols-2 lg:grid-cols-4 gap-x-5 gap-y-10">
    <?php foreach ($featured as $p): ?>
      <?php include __DIR__ . '/../components/product-card.php'; ?>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- Editorial Banner -->
<section class="bg-heritage-green py-24">
  <div class="max-w-screen-xl mx-auto px-6 lg:px-10">
    <div class="grid lg:grid-cols-2 gap-16 items-center">
      <div>
        <p class="text-white/40 text-xs tracking-[0.3em] uppercase mb-5">The Heritage Narrative</p>
        <h2 class="font-serif text-4xl lg:text-5xl text-white font-light leading-tight mb-8">
          Every thread has a story<br>worth telling.
        </h2>
        <p class="text-white/50 leading-relaxed mb-10 font-light">
          Our AI-powered Heritage Engine researches the provenance, craftsmanship,
          and cultural context behind each piece — so you don't just wear a garment,
          you wear a narrative.
        </p>
        <a href="/shop"
           class="inline-block border border-white/30 text-white px-8 py-4 text-xs
                  tracking-[0.2em] uppercase hover:bg-white hover:text-heritage-green transition-all duration-300">
          Discover The Edit
        </a>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <?php foreach (['Provenance Research', 'Styling Intelligence', 'Occasion Curation', 'Craft Documentation'] as $i => $feature): ?>
          <div class="bg-white/5 border border-white/10 p-6">
            <div class="w-8 h-8 bg-white/10 rounded-full flex items-center justify-center mb-4">
              <span class="text-white/60 text-xs font-serif">0<?= $i+1 ?></span>
            </div>
            <p class="text-white font-serif text-sm leading-relaxed"><?= $feature ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- New Arrivals -->
<?php if (!empty($newArrivals)): ?>
<section class="py-24 max-w-screen-xl mx-auto px-6 lg:px-10">
  <div class="flex items-end justify-between mb-12">
    <div>
      <p class="text-xs tracking-[0.25em] uppercase text-heritage-obsidian/40 mb-2">Just Landed</p>
      <h2 class="font-serif text-3xl lg:text-4xl font-light">New Arrivals</h2>
    </div>
    <a href="/shop?new_arrivals=1"
       class="hidden md:inline-flex text-xs tracking-[0.15em] uppercase text-heritage-obsidian/60
              hover:text-heritage-obsidian transition-colors border-b border-heritage-obsidian/20 pb-0.5">
      View All
    </a>
  </div>
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-x-5 gap-y-10">
    <?php foreach ($newArrivals as $p): ?>
      <?php include __DIR__ . '/../components/product-card.php'; ?>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
