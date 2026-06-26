<?php $pageTitle = 'New Product — Admin'; ?>

<div class="min-h-screen bg-gray-50 p-10">
  <div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-4 mb-8">
      <a href="/admin/products" class="text-gray-400 hover:text-gray-600 transition-colors">
        <i data-lucide="arrow-left" class="w-5 h-5"></i>
      </a>
      <h1 class="font-serif text-2xl font-light text-gray-800">New Product</h1>
      <span class="text-xs bg-amber-50 text-amber-700 px-2 py-1 rounded font-medium">
        AI Enrichment queued on save
      </span>
    </div>

    <form action="/admin/products" method="POST" enctype="multipart/form-data" class="space-y-8">

      <!-- Basic Info -->
      <div class="bg-white border border-gray-200 rounded p-7 space-y-5">
        <h2 class="font-medium text-gray-800 border-b border-gray-100 pb-4">Product Details</h2>

        <div>
          <label class="admin-label">Product Title <span class="text-red-500">*</span></label>
          <input type="text" name="title" required placeholder="e.g. Valentino Couture Crepe Gown"
                 class="admin-input">
          <p class="text-xs text-gray-400 mt-1.5">The AI Heritage Engine will generate narrative content based on this title.</p>
        </div>

        <div class="grid grid-cols-2 gap-5">
          <div>
            <label class="admin-label">Designer / Brand</label>
            <select name="brand_id" class="admin-input">
              <option value="">Select Brand</option>
              <?php foreach ($brands as $brand): ?>
                <option value="<?= $brand['id'] ?>"><?= htmlspecialchars($brand['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="admin-label">Category</label>
            <select name="category_id" class="admin-input">
              <option value="">Select Category</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div>
          <label class="admin-label">Gender</label>
          <div class="flex gap-4">
            <?php foreach (['women'=>'Women','men'=>'Men','unisex'=>'Unisex'] as $val => $label): ?>
              <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" name="gender" value="<?= $val ?>" <?= $val === 'women' ? 'checked' : '' ?>
                       class="accent-heritage-green">
                <span class="text-sm text-gray-700"><?= $label ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Pricing -->
      <div class="bg-white border border-gray-200 rounded p-7 space-y-5">
        <h2 class="font-medium text-gray-800 border-b border-gray-100 pb-4">Pricing</h2>
        <div class="grid grid-cols-3 gap-5">
          <div>
            <label class="admin-label">Base Price <span class="text-red-500">*</span></label>
            <input type="number" name="base_price" step="0.01" min="0" required
                   class="admin-input" placeholder="450000">
          </div>
          <div>
            <label class="admin-label">Sale Price</label>
            <input type="number" name="sale_price" step="0.01" min="0"
                   class="admin-input" placeholder="Optional">
          </div>
          <div>
            <label class="admin-label">Currency</label>
            <select name="currency" class="admin-input">
              <option value="NGN">NGN (₦)</option>
              <option value="USD">USD ($)</option>
              <option value="GBP">GBP (£)</option>
              <option value="EUR">EUR (€)</option>
            </select>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-5">
          <div>
            <label class="admin-label">SKU</label>
            <input type="text" name="sku" class="admin-input" placeholder="Auto-generated if empty">
          </div>
          <div>
            <label class="admin-label">Weight (grams)</label>
            <input type="number" name="weight_grams" class="admin-input" placeholder="500">
          </div>
        </div>
      </div>

      <!-- Variants -->
      <div class="bg-white border border-gray-200 rounded p-7">
        <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-5">
          <h2 class="font-medium text-gray-800">Variants (Size × Colour)</h2>
          <button type="button" onclick="addVariantRow()"
                  class="text-xs text-heritage-green hover:underline">+ Add Variant</button>
        </div>
        <div id="variants-container" class="space-y-3">
          <p class="text-sm text-gray-400" id="no-variants-msg">No variants added. Click "Add Variant" to start.</p>
        </div>
        <input type="hidden" name="variants" id="variants-json" value="[]">
      </div>

      <!-- Images -->
      <div class="bg-white border border-gray-200 rounded p-7">
        <h2 class="font-medium text-gray-800 border-b border-gray-100 pb-4 mb-5">Product Images</h2>
        <div class="border-2 border-dashed border-gray-200 rounded p-10 text-center hover:border-gray-400 transition-colors">
          <i data-lucide="upload-cloud" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
          <p class="text-sm text-gray-500 mb-1">Drop images here or click to browse</p>
          <p class="text-xs text-gray-400">JPG, PNG, WebP — Max 10MB each</p>
          <input type="file" name="images[]" multiple accept="image/*"
                 class="mt-4 text-xs text-gray-500 file:mr-3 file:text-xs file:border file:border-gray-300
                        file:px-4 file:py-2 file:text-gray-600 file:cursor-pointer file:hover:bg-gray-50">
        </div>
      </div>

      <!-- Status -->
      <div class="bg-white border border-gray-200 rounded p-7 space-y-4">
        <h2 class="font-medium text-gray-800 border-b border-gray-100 pb-4">Publishing</h2>
        <div class="grid grid-cols-3 gap-4">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="radio" name="status" value="draft" checked class="accent-heritage-green">
            <span class="text-sm">Draft</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="radio" name="status" value="active" class="accent-heritage-green">
            <span class="text-sm">Active</span>
          </label>
        </div>
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="checkbox" name="is_featured" value="1" class="w-4 h-4 accent-heritage-green">
          <span class="text-sm text-gray-700">Mark as Featured</span>
        </label>
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="checkbox" name="is_new_arrival" value="1" class="w-4 h-4 accent-heritage-green">
          <span class="text-sm text-gray-700">New Arrival</span>
        </label>
      </div>

      <!-- Submit -->
      <div class="flex gap-4">
        <button type="submit"
                class="flex-1 bg-heritage-green text-white py-4 text-sm tracking-[0.1em] uppercase
                       font-medium hover:bg-heritage-green/90 transition-colors">
          Create Product & Queue AI Enrichment
        </button>
        <a href="/admin/products"
           class="px-8 border border-gray-300 text-sm text-gray-600 hover:border-gray-500
                  hover:text-gray-800 transition-all flex items-center">
          Cancel
        </a>
      </div>
    </form>
  </div>
</div>

<style>
  .admin-label { display: block; font-size: 11px; font-weight: 500; letter-spacing: 0.05em; text-transform: uppercase; color: #6b7280; margin-bottom: 6px; }
  .admin-input { width: 100%; border: 1px solid #e5e7eb; padding: 10px 14px; font-size: 14px; border-radius: 4px; outline: none; transition: border-color 0.2s; }
  .admin-input:focus { border-color: #0D2C22; }
</style>

<script>
const variantRows = [];

function addVariantRow() {
  document.getElementById('no-variants-msg')?.remove();
  const container = document.getElementById('variants-container');
  const row = document.createElement('div');
  row.className = 'grid grid-cols-5 gap-3 items-end';
  const id = Date.now();
  row.innerHTML = `
    <div>
      <label class="admin-label">Size</label>
      <input type="text" placeholder="XS, S, M…" class="admin-input variant-size" data-id="${id}">
    </div>
    <div>
      <label class="admin-label">Colour</label>
      <input type="text" placeholder="Ivory" class="admin-input variant-color" data-id="${id}">
    </div>
    <div>
      <label class="admin-label">Hex</label>
      <input type="color" value="#ffffff" class="admin-input h-10 p-1 variant-hex" data-id="${id}">
    </div>
    <div>
      <label class="admin-label">Stock</label>
      <input type="number" min="0" value="0" class="admin-input variant-stock" data-id="${id}">
    </div>
    <button type="button" onclick="this.parentElement.remove(); syncVariants();"
            class="pb-0 text-red-400 hover:text-red-600 text-xl leading-none">×</button>
  `;
  container.appendChild(row);
  row.querySelectorAll('input').forEach(i => i.addEventListener('input', syncVariants));
}

function syncVariants() {
  const rows = document.querySelectorAll('#variants-container > div');
  const variants = Array.from(rows).map(row => ({
    size:      row.querySelector('.variant-size')?.value || null,
    color:     row.querySelector('.variant-color')?.value || null,
    color_hex: row.querySelector('.variant-hex')?.value || null,
    stock:     parseInt(row.querySelector('.variant-stock')?.value || 0),
  }));
  document.getElementById('variants-json').value = JSON.stringify(variants);
}
</script>
