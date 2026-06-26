<?php $pageTitle = 'Shop — THE HERITAGE EDIT'; ?>

<div class="max-w-screen-xl mx-auto px-6 lg:px-10 py-10">
  <!-- Breadcrumb -->
  <nav class="flex items-center gap-2 text-xs text-heritage-obsidian/40 mb-8 tracking-wide">
    <a href="/" class="hover:text-heritage-obsidian transition-colors">Home</a>
    <span>/</span>
    <span class="text-heritage-obsidian">Shop</span>
  </nav>

  <div class="flex gap-10 lg:gap-14">
    <!-- Sidebar Filters -->
    <aside id="filter-sidebar"
           class="w-56 xl:w-64 flex-shrink-0 hidden lg:block">
      <div class="sticky top-28 space-y-8">

        <!-- Clear Filters -->
        <?php if (array_filter($filters)): ?>
          <a href="/shop"
             class="text-xs tracking-[0.15em] uppercase text-heritage-obsidian/40
                    hover:text-heritage-obsidian transition-colors flex items-center gap-2">
            <i data-lucide="x" class="w-3 h-3"></i> Clear All Filters
          </a>
        <?php endif; ?>

        <!-- Gender Filter -->
        <div>
          <h3 class="text-xs tracking-[0.2em] uppercase text-heritage-obsidian/40 mb-4 font-medium">Department</h3>
          <ul class="space-y-2.5">
            <?php foreach ([['women','Women'],['men','Men'],['unisex','Unisex']] as [$val,$label]): ?>
              <li>
                <label class="flex items-center gap-3 cursor-pointer group">
                  <input type="checkbox" name="gender" value="<?= $val ?>"
                         <?= ($filters['gender'] ?? '') === $val ? 'checked' : '' ?>
                         onchange="Heritage.filters.update(this)"
                         class="w-3.5 h-3.5 accent-heritage-green">
                  <span class="text-sm text-heritage-obsidian/70 group-hover:text-heritage-obsidian transition-colors">
                    <?= $label ?>
                  </span>
                </label>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <!-- Brand Filter -->
        <?php if (!empty($filter_options['brands'])): ?>
        <div>
          <h3 class="text-xs tracking-[0.2em] uppercase text-heritage-obsidian/40 mb-4 font-medium">Designer</h3>
          <ul class="space-y-2.5 max-h-52 overflow-y-auto pr-1">
            <?php foreach ($filter_options['brands'] as $brand): ?>
              <li>
                <label class="flex items-center gap-3 cursor-pointer group">
                  <input type="checkbox" name="brand" value="<?= htmlspecialchars($brand['slug']) ?>"
                         <?= ($filters['brand'] ?? '') === $brand['slug'] ? 'checked' : '' ?>
                         onchange="Heritage.filters.update(this)"
                         class="w-3.5 h-3.5 accent-heritage-green">
                  <span class="text-sm text-heritage-obsidian/70 group-hover:text-heritage-obsidian transition-colors flex-1">
                    <?= htmlspecialchars($brand['name']) ?>
                  </span>
                  <span class="text-xs text-heritage-obsidian/30"><?= $brand['product_count'] ?></span>
                </label>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>

        <!-- Size Filter -->
        <?php if (!empty($filter_options['sizes'])): ?>
        <div>
          <h3 class="text-xs tracking-[0.2em] uppercase text-heritage-obsidian/40 mb-4 font-medium">Size</h3>
          <div class="flex flex-wrap gap-2">
            <?php foreach ($filter_options['sizes'] as $size): ?>
              <button onclick="Heritage.filters.toggleSize(this, '<?= htmlspecialchars($size['size']) ?>')"
                      data-active="<?= ($filters['size'] ?? '') === $size['size'] ? 'true' : 'false' ?>"
                      class="size-btn px-3 py-2 border text-xs transition-all duration-200
                             <?= ($filters['size'] ?? '') === $size['size']
                                ? 'border-heritage-obsidian bg-heritage-obsidian text-white'
                                : 'border-heritage-slate text-heritage-obsidian/60 hover:border-heritage-obsidian' ?>">
                <?= htmlspecialchars($size['size']) ?>
              </button>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Color Filter -->
        <?php if (!empty($filter_options['colors'])): ?>
        <div>
          <h3 class="text-xs tracking-[0.2em] uppercase text-heritage-obsidian/40 mb-4 font-medium">Colour</h3>
          <div class="flex flex-wrap gap-2.5">
            <?php foreach ($filter_options['colors'] as $color): ?>
              <button onclick="Heritage.filters.toggleColor(this, '<?= htmlspecialchars($color['color']) ?>')"
                      title="<?= htmlspecialchars($color['color']) ?>"
                      class="w-7 h-7 rounded-full border-2 transition-all duration-200 relative
                             <?= ($filters['color'] ?? '') === $color['color']
                                ? 'border-heritage-obsidian scale-110'
                                : 'border-transparent hover:border-heritage-slate' ?>"
                      style="background-color: <?= htmlspecialchars($color['color_hex'] ?? '#ccc') ?>">
              </button>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Price Range -->
        <?php if (!empty($filter_options['price_range'])): ?>
        <div>
          <h3 class="text-xs tracking-[0.2em] uppercase text-heritage-obsidian/40 mb-4 font-medium">Price</h3>
          <div class="space-y-3">
            <div class="flex justify-between text-xs text-heritage-obsidian/60">
              <span id="price-min-label">₦<?= number_format($filter_options['price_range']['min_price'], 0, '.', ',') ?></span>
              <span id="price-max-label">₦<?= number_format($filter_options['price_range']['max_price'], 0, '.', ',') ?></span>
            </div>
            <input type="range"
                   min="<?= $filter_options['price_range']['min_price'] ?>"
                   max="<?= $filter_options['price_range']['max_price'] ?>"
                   value="<?= $filters['max_price'] ?? $filter_options['price_range']['max_price'] ?>"
                   class="w-full"
                   oninput="Heritage.filters.updatePrice(this)">
          </div>
        </div>
        <?php endif; ?>
      </div>
    </aside>

    <!-- Main Grid -->
    <div class="flex-1 min-w-0">
      <!-- Toolbar -->
      <div class="flex items-center justify-between mb-8">
        <p class="text-sm text-heritage-obsidian/50">
          <span class="text-heritage-obsidian font-medium"><?= number_format($total) ?></span> pieces
        </p>

        <div class="flex items-center gap-4">
          <!-- Sort -->
          <select onchange="Heritage.filters.updateSort(this.value)"
                  class="text-xs tracking-wide border-0 border-b border-heritage-slate py-1 pr-6
                         bg-transparent outline-none cursor-pointer text-heritage-obsidian/70
                         hover:text-heritage-obsidian transition-colors">
            <option value="newest"    <?= ($filters['sort']??'newest')==='newest'    ? 'selected' : '' ?>>Newest</option>
            <option value="popular"   <?= ($filters['sort']??'')==='popular'         ? 'selected' : '' ?>>Most Popular</option>
            <option value="price_asc" <?= ($filters['sort']??'')==='price_asc'       ? 'selected' : '' ?>>Price: Low to High</option>
            <option value="price_desc"<?= ($filters['sort']??'')==='price_desc'      ? 'selected' : '' ?>>Price: High to Low</option>
          </select>

          <!-- Grid Toggle -->
          <div class="hidden md:flex items-center border border-heritage-slate">
            <button onclick="Heritage.catalog.setGrid(2)"
                    class="p-2 hover:bg-heritage-slate transition-colors" title="2 columns">
              <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
            </button>
            <button onclick="Heritage.catalog.setGrid(3)"
                    class="p-2 hover:bg-heritage-slate transition-colors" title="3 columns">
              <i data-lucide="grid-3x3" class="w-3.5 h-3.5"></i>
            </button>
          </div>

          <!-- Mobile Filter Toggle -->
          <button class="lg:hidden flex items-center gap-2 text-xs tracking-[0.1em] uppercase border border-heritage-slate px-4 py-2"
                  onclick="document.getElementById('filter-drawer').classList.toggle('hidden')">
            <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5"></i>
            Filters
          </button>
        </div>
      </div>

      <!-- Product Grid -->
      <div id="product-grid" class="grid grid-cols-2 md:grid-cols-3 gap-x-5 gap-y-10">
        <?php if (empty($products)): ?>
          <div class="col-span-full text-center py-24">
            <i data-lucide="search-x" class="w-12 h-12 mx-auto text-heritage-obsidian/20 mb-4"></i>
            <p class="font-serif text-xl text-heritage-obsidian/50">No pieces match your selection</p>
            <a href="/shop" class="inline-block mt-6 text-xs tracking-[0.15em] uppercase
                                   border-b border-heritage-obsidian/30 hover:border-heritage-obsidian
                                   pb-0.5 transition-colors">
              Clear Filters
            </a>
          </div>
        <?php else: ?>
          <?php foreach ($products as $p): ?>
            <?php include __DIR__ . '/../components/product-card.php'; ?>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Pagination -->
      <?php if ($total_pages > 1): ?>
        <div class="flex justify-center items-center gap-2 mt-16">
          <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <?php
            $params = array_merge($_GET, ['page' => $i]);
            $href   = '/shop?' . http_build_query($params);
            $active = $i === $page;
            ?>
            <a href="<?= htmlspecialchars($href) ?>"
               class="w-10 h-10 flex items-center justify-center text-sm transition-all duration-200
                      <?= $active
                        ? 'bg-heritage-obsidian text-white'
                        : 'border border-heritage-slate text-heritage-obsidian/60 hover:border-heritage-obsidian hover:text-heritage-obsidian' ?>">
              <?= $i ?>
            </a>
          <?php endfor; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
