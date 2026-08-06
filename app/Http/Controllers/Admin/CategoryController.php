<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        // withCount, bukan load products penuh - list cuma butuh JUMLAH
        // produk per kategori, load semua produk ke memori percuma & lambat
        // kalau kategori punya ratusan produk (N+1 juga kalau lupa eager load).
        $categories = Category::withCount('products')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.form', ['category' => new Category()]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        Category::create($request->validated());

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil dibuat.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        // FK products.category_id -> cascadeOnDelete (lihat migration) - hapus
        // kategori otomatis HAPUS SEMUA PRODUK di dalamnya. Cek dulu, jangan
        // biarin admin gak sadar produk ikut kehapus tanpa peringatan jelas.
        if ($category->products()->exists()) {
            return back()->with('error', 'Kategori masih punya produk di dalamnya - hapus/pindahkan produknya dulu.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil dihapus.');
    }

    /** AJAX helper - generate slug dari nama, dipanggil dari form pakai fetch(). */
    public function generateSlug(): \Illuminate\Http\JsonResponse
    {
        $name = request()->query('name', '');

        return response()->json(['slug' => Str::slug($name)]);
    }
}
