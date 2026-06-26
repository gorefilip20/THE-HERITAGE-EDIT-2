<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? config('app.name')) ?></title>
  <meta name="description" content="<?= htmlspecialchars($pageDesc ?? 'THE HERITAGE EDIT — Ultra-luxury curated fashion.') ?>">

  <!-- Preconnect -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,500&family=Playfair+Display:ital,wght@0,400;0,500;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

  <!-- Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            'heritage': {
              'green':   '#0D2C22',
              'purple':  '#2E1A47',
              'ivory':   '#FBFBFA',
              'slate':   '#EAEAEA',
              'obsidian':'#111111',
            }
          },
          fontFamily: {
            'serif':  ['"Cormorant Garamond"', '"Playfair Display"', 'Georgia', 'serif'],
            'sans':   ['"Inter"', 'system-ui', 'sans-serif'],
          },
          transitionTimingFunction: {
            'luxury': 'cubic-bezier(0.25, 0.46, 0.45, 0.94)',
          }
        }
      }
    }
  </script>

  <!-- Lucide Icons -->
  <script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.min.js"></script>

  <!-- Framer Motion (via CDN) -->
  <script src="https://cdn.jsdelivr.net/npm/framer-motion@11/dist/framer-motion.js"></script>

  <style>
    * { -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }

    :root {
      --green:    #0D2C22;
      --purple:   #2E1A47;
      --ivory:    #FBFBFA;
      --slate:    #EAEAEA;
      --obsidian: #111111;
    }

    body { background: var(--ivory); color: var(--obsidian); }

    /* Marquee */
    @keyframes marquee { from { transform: translateX(0) } to { transform: translateX(-50%) } }
    .marquee-inner { animation: marquee 28s linear infinite; width: max-content; }
    .marquee-inner:hover { animation-play-state: paused; }

    /* Product card hover */
    .product-card:hover .product-card__hover { opacity: 1; }
    .product-card:hover .product-card__primary { opacity: 0; }
    .product-card__hover, .product-card__primary {
      transition: opacity 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    /* Side cart drawer */
    #cart-drawer {
      transition: transform 0.45s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    #cart-overlay {
      transition: opacity 0.45s ease;
    }

    /* Skeleton */
    @keyframes skeleton-shimmer {
      0%   { background-position: -200% 0; }
      100% { background-position:  200% 0; }
    }
    .skeleton {
      background: linear-gradient(90deg, #ececec 25%, #f5f5f5 50%, #ececec 75%);
      background-size: 200% 100%;
      animation: skeleton-shimmer 1.5s infinite;
    }

    /* Luxury focus ring */
    *:focus-visible { outline: 2px solid var(--green); outline-offset: 2px; }

    /* Smooth scroll native */
    html { scroll-behavior: smooth; }

    /* Custom scrollbar */
    ::-webkit-scrollbar { width: 4px; }
    ::-webkit-scrollbar-track { background: var(--ivory); }
    ::-webkit-scrollbar-thumb { background: var(--slate); border-radius: 2px; }

    /* Range input */
    input[type="range"] { accent-color: var(--green); }
  </style>
</head>
<body class="font-sans bg-heritage-ivory min-h-screen flex flex-col">

<?php include __DIR__ . '/header.php'; ?>

<!-- Cart Drawer Overlay -->
<div id="cart-overlay" class="fixed inset-0 bg-obsidian/40 z-40 opacity-0 pointer-events-none" onclick="Heritage.cart.close()"></div>

<!-- Cart Drawer -->
<aside id="cart-drawer" class="fixed top-0 right-0 h-full w-full max-w-md bg-white z-50 flex flex-col shadow-2xl translate-x-full">
  <div class="flex items-center justify-between px-6 py-5 border-b border-heritage-slate">
    <h2 class="font-serif text-xl tracking-wide">Your Edit</h2>
    <button onclick="Heritage.cart.close()" aria-label="Close cart"
            class="text-heritage-obsidian/50 hover:text-heritage-obsidian transition-colors">
      <i data-lucide="x" class="w-5 h-5"></i>
    </button>
  </div>

  <div id="cart-items" class="flex-1 overflow-y-auto px-6 py-4 space-y-5">
    <div class="text-center py-16 text-heritage-obsidian/40">
      <i data-lucide="shopping-bag" class="w-10 h-10 mx-auto mb-3 opacity-30"></i>
      <p class="font-serif text-lg">Your edit is empty</p>
      <p class="text-sm mt-1">Discover our curated collections</p>
    </div>
  </div>

  <div id="cart-footer" class="border-t border-heritage-slate px-6 py-6 space-y-4 hidden">
    <div class="flex justify-between text-sm">
      <span class="text-heritage-obsidian/60 tracking-wide uppercase text-xs">Subtotal</span>
      <span id="cart-subtotal" class="font-medium text-heritage-obsidian"></span>
    </div>
    <p class="text-xs text-heritage-obsidian/40">Taxes and shipping calculated at checkout</p>
    <a href="/checkout"
       class="block w-full bg-heritage-green text-white text-center py-4 text-sm tracking-[0.15em] uppercase font-medium hover:bg-heritage-green/90 transition-colors duration-300">
      Proceed to Checkout
    </a>
    <a href="/shop"
       class="block w-full text-center text-sm text-heritage-obsidian/60 hover:text-heritage-obsidian transition-colors tracking-wide">
      Continue Shopping
    </a>
  </div>
</aside>

<!-- Main Content -->
<main class="flex-1">
  <?= $content ?>
</main>

<?php include __DIR__ . '/footer.php'; ?>

<!-- App JS -->
<script src="/assets/js/app.js"></script>

<script>
  // Initialize Lucide icons
  lucide.createIcons();
</script>

</body>
</html>
