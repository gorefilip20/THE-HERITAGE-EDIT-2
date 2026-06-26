<footer class="bg-heritage-green text-white mt-auto">
  <div class="max-w-screen-xl mx-auto px-6 lg:px-10 py-16 lg:py-20">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 lg:gap-8">

      <!-- Brand -->
      <div class="lg:col-span-1">
        <a href="/" class="block mb-6">
          <span class="font-serif text-lg tracking-[0.2em] uppercase">THE HERITAGE EDIT</span>
        </a>
        <p class="text-white/50 text-sm leading-relaxed font-light">
          A curated archive of the world's most storied fashion houses. Every piece tells a story.
        </p>
        <div class="flex gap-5 mt-8">
          <?php foreach ([['instagram','#'],['twitter','#'],['pinterest','#']] as [$icon,$href]): ?>
            <a href="<?= $href ?>" aria-label="<?= $icon ?>"
               class="text-white/40 hover:text-white transition-colors">
              <i data-lucide="<?= $icon ?>" class="w-4 h-4"></i>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Shop -->
      <div>
        <h4 class="text-xs tracking-[0.2em] uppercase mb-6 text-white/40">Shop</h4>
        <ul class="space-y-3">
          <?php foreach ([
            ['New Arrivals', '/shop?new_arrivals=1'],
            ['Women',        '/shop?gender=women'],
            ['Men',          '/shop?gender=men'],
            ['Designers',    '/shop?sort=popular'],
            ['Sale',         '/shop?sale=1'],
          ] as [$label, $href]): ?>
            <li>
              <a href="<?= $href ?>"
                 class="text-sm text-white/60 hover:text-white transition-colors">
                <?= $label ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Customer Care -->
      <div>
        <h4 class="text-xs tracking-[0.2em] uppercase mb-6 text-white/40">Customer Care</h4>
        <ul class="space-y-3">
          <?php foreach ([
            ['Shipping & Returns',  '/shipping'],
            ['Size Guide',          '/size-guide'],
            ['Authentication',      '/authentication'],
            ['Contact Us',          '/contact'],
            ['FAQs',                '/faq'],
          ] as [$label, $href]): ?>
            <li>
              <a href="<?= $href ?>"
                 class="text-sm text-white/60 hover:text-white transition-colors">
                <?= $label ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Newsletter -->
      <div>
        <h4 class="text-xs tracking-[0.2em] uppercase mb-6 text-white/40">The Edit Journal</h4>
        <p class="text-sm text-white/50 mb-5 leading-relaxed">
          Receive curated narratives, exclusive access, and the season's finest pieces.
        </p>
        <form class="flex flex-col gap-3" onsubmit="return false;">
          <input type="email" placeholder="Your email address"
                 class="bg-white/10 border border-white/20 px-4 py-3 text-sm text-white
                        placeholder-white/30 focus:border-white/60 outline-none transition-colors">
          <button type="submit"
                  class="bg-white text-heritage-green text-xs tracking-[0.15em] uppercase
                         font-medium px-6 py-3 hover:bg-heritage-ivory transition-colors">
            Subscribe
          </button>
        </form>
      </div>
    </div>

    <!-- Bottom Bar -->
    <div class="border-t border-white/10 mt-14 pt-8 flex flex-col md:flex-row items-center justify-between gap-4">
      <p class="text-xs text-white/30 tracking-wide">
        &copy; <?= date('Y') ?> The Heritage Edit. All rights reserved.
      </p>
      <div class="flex items-center gap-6">
        <?php foreach ([['Privacy Policy','/privacy'],['Terms of Service','/terms'],['Cookie Policy','/cookies']] as [$l,$h]): ?>
          <a href="<?= $h ?>" class="text-xs text-white/30 hover:text-white/60 transition-colors"><?= $l ?></a>
        <?php endforeach; ?>
      </div>
      <!-- Payment Methods -->
      <div class="flex items-center gap-3 text-white/30">
        <span class="text-xs">Secured by</span>
        <span class="text-xs font-medium text-white/50">Paystack</span>
        <span class="text-xs">·</span>
        <span class="text-xs font-medium text-white/50">DHL</span>
        <span class="text-xs">·</span>
        <span class="text-xs font-medium text-white/50">FedEx</span>
      </div>
    </div>
  </div>
</footer>
