<?php $pageTitle = 'Admin Dashboard — THE HERITAGE EDIT'; ?>

<div class="min-h-screen bg-gray-50">
  <!-- Admin Header -->
  <header class="bg-heritage-green text-white px-8 py-4 flex items-center justify-between">
    <div class="flex items-center gap-6">
      <span class="font-serif text-lg tracking-[0.2em] uppercase">THE HERITAGE EDIT</span>
      <span class="text-white/30 text-xs">Admin</span>
    </div>
    <div class="flex items-center gap-6 text-sm">
      <a href="/admin/products/new" class="bg-white/10 hover:bg-white/20 px-4 py-2 text-xs tracking-[0.1em] uppercase transition-colors">
        + New Product
      </a>
      <a href="/" class="text-white/60 hover:text-white transition-colors text-xs">View Store →</a>
    </div>
  </header>

  <div class="flex min-h-[calc(100vh-57px)]">
    <!-- Sidebar -->
    <nav class="w-56 bg-white border-r border-gray-200 pt-8 flex-shrink-0">
      <?php foreach ([
        ['layout-dashboard', 'Dashboard',  '/admin'],
        ['package',          'Products',   '/admin/products'],
        ['shopping-bag',     'Orders',     '/admin/orders'],
        ['users',            'Customers',  '/admin/customers'],
        ['sparkles',         'AI Queue',   '/admin/ai-queue'],
        ['settings',         'Settings',   '/admin/settings'],
      ] as [$icon, $label, $href]): ?>
        <a href="<?= $href ?>"
           class="flex items-center gap-3 px-6 py-3.5 text-sm text-gray-600 hover:bg-gray-50
                  hover:text-gray-900 transition-colors group <?= str_starts_with($_SERVER['REQUEST_URI'], $href) ? 'bg-heritage-green/5 text-heritage-green border-r-2 border-heritage-green' : '' ?>">
          <i data-lucide="<?= $icon ?>" class="w-4 h-4 opacity-60 group-hover:opacity-100"></i>
          <?= $label ?>
        </a>
      <?php endforeach; ?>
    </nav>

    <!-- Content -->
    <main class="flex-1 p-10">
      <h1 class="font-serif text-2xl font-light text-gray-800 mb-8">Dashboard</h1>

      <!-- Stats Grid -->
      <div class="grid grid-cols-2 lg:grid-cols-5 gap-5 mb-10">
        <?php foreach ([
          ['Total Orders',    number_format($stats['total_orders']),   'shopping-bag', 'heritage-green'],
          ['Revenue',         '₦' . number_format($stats['total_revenue'], 0, '.', ','), 'trending-up', 'blue-600'],
          ['Pending Orders',  $stats['pending_orders'],                'clock',        'amber-600'],
          ['Active Products', $stats['total_products'],                'package',      'purple-600'],
          ['AI Queue',        $stats['ai_pending'],                    'sparkles',     'green-600'],
        ] as [$label, $value, $icon, $color]): ?>
          <div class="bg-white border border-gray-200 rounded p-5">
            <div class="flex items-center justify-between mb-3">
              <span class="text-xs text-gray-500 tracking-wide"><?= $label ?></span>
              <i data-lucide="<?= $icon ?>" class="w-4 h-4 text-<?= $color ?> opacity-60"></i>
            </div>
            <p class="text-2xl font-semibold text-gray-900"><?= $value ?></p>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Recent Orders -->
      <div class="bg-white border border-gray-200 rounded">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
          <h2 class="font-medium text-gray-800">Recent Orders</h2>
          <a href="/admin/orders" class="text-xs text-heritage-green hover:underline">View All</a>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-gray-50 text-xs text-gray-500 tracking-wide uppercase">
                <th class="px-6 py-3 text-left">Order</th>
                <th class="px-6 py-3 text-left">Customer</th>
                <th class="px-6 py-3 text-left">Total</th>
                <th class="px-6 py-3 text-left">Status</th>
                <th class="px-6 py-3 text-left">Payment</th>
                <th class="px-6 py-3 text-left">Date</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <?php foreach ($recent_orders as $order): ?>
                <tr class="hover:bg-gray-50 transition-colors">
                  <td class="px-6 py-4 font-mono text-xs font-medium text-heritage-green">
                    <a href="/admin/orders/<?= htmlspecialchars($order['order_number']) ?>"><?= htmlspecialchars($order['order_number']) ?></a>
                  </td>
                  <td class="px-6 py-4 text-gray-700"><?= htmlspecialchars($order['email']) ?></td>
                  <td class="px-6 py-4 font-medium">₦<?= number_format($order['total'], 0, '.', ',') ?></td>
                  <td class="px-6 py-4">
                    <?php
                    $statusColors = [
                      'pending'    => 'bg-amber-50 text-amber-700',
                      'confirmed'  => 'bg-blue-50 text-blue-700',
                      'shipped'    => 'bg-purple-50 text-purple-700',
                      'delivered'  => 'bg-green-50 text-green-700',
                      'cancelled'  => 'bg-red-50 text-red-700',
                    ];
                    $color = $statusColors[$order['status']] ?? 'bg-gray-100 text-gray-600';
                    ?>
                    <span class="<?= $color ?> text-xs px-2 py-1 rounded font-medium">
                      <?= ucfirst($order['status']) ?>
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <span class="<?= $order['payment_status'] === 'paid' ? 'text-green-600' : 'text-amber-600' ?> text-xs font-medium">
                      <?= ucfirst($order['payment_status']) ?>
                    </span>
                  </td>
                  <td class="px-6 py-4 text-gray-400 text-xs"><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</div>
