<?php

declare(strict_types=1);

namespace HeritageEdit\Controllers;

use HeritageEdit\Core\Request;
use HeritageEdit\Core\Response;
use HeritageEdit\Models\Product;

final class ProductController
{
    private Product $product;

    public function __construct()
    {
        $this->product = new Product();
    }

    /** GET / */
    public function home(Request $request): void
    {
        $featured    = $this->product->featured(8);
        $newArrivals = $this->product->newArrivals(8);
        Response::view('pages/home', compact('featured', 'newArrivals'));
    }

    /** GET /shop */
    public function catalog(Request $request): void
    {
        $filters = [
            'category'    => $request->get('category'),
            'brand'       => $request->get('brand'),
            'gender'      => $request->get('gender'),
            'min_price'   => $request->get('min_price'),
            'max_price'   => $request->get('max_price'),
            'color'       => $request->get('color'),
            'size'        => $request->get('size'),
            'sort'        => $request->get('sort', 'newest'),
            'new_arrivals'=> $request->get('new_arrivals'),
        ];
        $page    = max(1, (int) $request->get('page', 1));

        $data    = $this->product->catalog(array_filter($filters), $page);
        $filters_options = $this->product->getFilterOptions();

        Response::view('pages/catalog', array_merge($data, [
            'filters'         => $filters,
            'filter_options'  => $filters_options,
        ]));
    }

    /** GET /product/{slug} */
    public function show(Request $request): void
    {
        $slug    = $request->param('slug');
        $product = $this->product->findBySlug($slug);

        if (!$product) {
            Response::abort(404, 'Product not found');
        }

        $this->product->incrementViews($product['id']);

        // Decode JSON enrichment fields
        if ($product['enrichment']) {
            $e = $product['enrichment'];
            $product['enrichment']['right_occasion']        = json_decode($e['right_occasion'] ?? '[]', true);
            $product['enrichment']['style_recommendations'] = json_decode($e['style_recommendations'] ?? '[]', true);
        }

        Response::view('pages/product', compact('product'));
    }

    /** GET /api/products (JSON) */
    public function apiCatalog(Request $request): void
    {
        $filters = array_filter([
            'category'  => $request->get('category'),
            'brand'     => $request->get('brand'),
            'gender'    => $request->get('gender'),
            'min_price' => $request->get('min_price'),
            'max_price' => $request->get('max_price'),
            'color'     => $request->get('color'),
            'size'      => $request->get('size'),
            'sort'      => $request->get('sort', 'newest'),
        ]);

        $page = max(1, (int) $request->get('page', 1));
        Response::json($this->product->catalog($filters, $page));
    }
}
