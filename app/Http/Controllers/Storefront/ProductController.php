<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        $selectedCategory = $request->query('category');

        // Eager load category + variants (buat nampilin range harga di card) -
        // withMin dipake daripada load semua variant, cukup ambil harga
        // terendah tanpa narik semua baris variant ke memori percuma.
        $products = Product::with('category')
            ->where('is_active', true)
            ->withMin(['variants as min_price' => fn ($q) => $q->where('is_active', true)], 'price')
            ->when($selectedCategory, fn ($q) => $q->whereHas('category', fn ($qq) => $qq->where('slug', $selectedCategory)))
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('storefront.products.index', compact('products', 'categories', 'selectedCategory'));
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load(['category', 'variants' => fn ($q) => $q->where('is_active', true)->orderBy('price')]);

        abort_if($product->variants->isEmpty(), 404, 'Produk ini belum ada varian yang tersedia.');

        return view('storefront.products.show', compact('product'));
    }
}
