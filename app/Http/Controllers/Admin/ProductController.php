<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CloudinaryUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly CloudinaryUploadService $uploader)
    {
    }

    public function index(): View
    {
        // Eager load category (N+1 kalau lupa) + withCount variants. Pagination
        // WAJIB - jangan pernah ->get() semua produk tanpa batas.
        $products = Product::with('category')
            ->withCount('variants')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $product = new Product();
        $product->setRelation('variants', collect());

        return view('admin.products.form', compact('product', 'categories'));
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request) {
            $thumbnail = ['thumbnail_url' => null, 'thumbnail_public_id' => null];
            if ($request->hasFile('thumbnail')) {
                $uploaded = $this->uploader->upload($request->file('thumbnail'), 'cetakpro/products');
                $thumbnail = ['thumbnail_url' => $uploaded['url'], 'thumbnail_public_id' => $uploaded['public_id']];
            }

            $product = Product::create([
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
                'base_price' => $data['base_price'],
                'is_active' => $data['is_active'],
                ...$thumbnail,
            ]);

            foreach ($data['variants'] as $variant) {
                if (! empty($variant['_delete'])) {
                    continue; // varian baru yang langsung dihapus sebelum submit - skip aja
                }
                $product->variants()->create([
                    'ukuran' => $variant['ukuran'] ?? null,
                    'bahan' => $variant['bahan'] ?? null,
                    'price' => $variant['price'],
                    'min_order' => $variant['min_order'],
                    'is_active' => $variant['is_active'],
                ]);
            }
        });

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dibuat.');
    }

    public function edit(Product $product): View
    {
        $product->load('variants');
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('admin.products.form', compact('product', 'categories'));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        try {
            DB::transaction(function () use ($data, $request, $product) {
                $thumbnailUpdate = [];
                if ($request->hasFile('thumbnail')) {
                    // Hapus thumbnail lama di Cloudinary SETELAH upload baru sukses
                    // (bukan sebelum) - kalau upload baru gagal, thumbnail lama
                    // masih ada, bukan malah kehilangan dua-duanya.
                    $uploaded = $this->uploader->upload($request->file('thumbnail'), 'cetakpro/products');
                    $oldPublicId = $product->thumbnail_public_id;
                    $thumbnailUpdate = ['thumbnail_url' => $uploaded['url'], 'thumbnail_public_id' => $uploaded['public_id']];
                    $this->uploader->destroy($oldPublicId);
                }

                $product->update([
                    'category_id' => $data['category_id'],
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'description' => $data['description'] ?? null,
                    'base_price' => $data['base_price'],
                    'is_active' => $data['is_active'],
                    ...$thumbnailUpdate,
                ]);

                foreach ($data['variants'] as $variant) {
                    $variantId = $variant['id'] ?? null;

                    if (! empty($variant['_delete'])) {
                        if ($variantId) {
                            // restrictOnDelete di order_items.product_variant_id (lihat
                            // migration) - kalau varian ini udah pernah dipesan, delete()
                            // throw QueryException. Ditangkep di luar transaction (di bawah),
                            // BUKAN silent-skip, biar admin tau kenapa gagal.
                            ProductVariant::where('id', $variantId)->where('product_id', $product->id)->delete();
                        }
                        continue;
                    }

                    if ($variantId) {
                        ProductVariant::where('id', $variantId)->where('product_id', $product->id)->update([
                            'ukuran' => $variant['ukuran'] ?? null,
                            'bahan' => $variant['bahan'] ?? null,
                            'price' => $variant['price'],
                            'min_order' => $variant['min_order'],
                            'is_active' => $variant['is_active'],
                        ]);
                    } else {
                        $product->variants()->create([
                            'ukuran' => $variant['ukuran'] ?? null,
                            'bahan' => $variant['bahan'] ?? null,
                            'price' => $variant['price'],
                            'min_order' => $variant['min_order'],
                            'is_active' => $variant['is_active'],
                        ]);
                    }
                }
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // FK restrictOnDelete kena (varian udah pernah dipesan) - transaction
            // udah di-rollback otomatis sama DB::transaction, aman gak ada
            // perubahan setengah jalan tersimpan.
            return back()->withInput()->with('error', 'Ada varian yang udah pernah dipesan, gak bisa dihapus - nonaktifkan aja daripada dihapus.');
        }

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        // Product pakai SoftDeletes (lihat model) - ini soft delete, bukan
        // hilang permanen. Order lama yang udah reference variant produk ini
        // TETAP AMAN (restrictOnDelete di order_items, dan soft delete gak
        // beneran hapus baris dari DB).
        $this->uploader->destroy($product->thumbnail_public_id);
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dihapus.');
    }

    public function generateSlug(): \Illuminate\Http\JsonResponse
    {
        $name = request()->query('name', '');

        return response()->json(['slug' => \Illuminate\Support\Str::slug($name)]);
    }
}
