<?php

namespace App\Livewire\Products;

use App\Models\Category;
use App\Models\Product;
use App\Services\ImageService;
use App\Services\ProductService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ProductList extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $categoryFilter = '';

    #[Url]
    public string $statusFilter = 'all';

    #[Url]
    public string $availabilityFilter = 'all';

    #[Url]
    public string $view = 'table';

    public array  $selectedIds = [];
    public bool   $selectAll   = false;
    public int    $perPage     = 20;

    public bool   $showToast    = false;
    public string $toastMessage = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Product::class);
    }

    public function updatedSearch(): void      { $this->resetPage(); }
    public function updatedCategoryFilter(): void { $this->resetPage(); }
    public function updatedStatusFilter(): void { $this->resetPage(); }
    public function updatedAvailabilityFilter(): void { $this->resetPage(); }

    public function updatedSelectAll(bool $value): void
    {
        $this->selectedIds = $value
            ? $this->getBaseQuery()->pluck('id')->all()
            : [];
    }

    public function toggleActive(string $id): void
    {
        $product = Product::findOrFail($id);
        Gate::authorize('update', $product);
        $product->update(['is_active' => ! $product->is_active]);
        $this->toast($product->is_active ? 'Product activated.' : 'Product deactivated.');
    }

    public function toggleAvailable(string $id): void
    {
        $product = Product::findOrFail($id);
        Gate::authorize('update', $product);
        $product->update(['is_available' => ! $product->is_available]);
        $this->toast($product->is_available ? 'Product available.' : 'Marked as sold out.');
    }

    public function duplicate(string $id): void
    {
        $product = Product::findOrFail($id);
        Gate::authorize('create', Product::class);
        ProductService::duplicate($product);
        $this->toast('Product duplicated.');
    }

    public function delete(string $id): void
    {
        $product = Product::findOrFail($id);
        Gate::authorize('delete', $product);

        if ($product->image) {
            ImageService::delete($product->image);
        }

        $product->delete();
        $this->selectedIds = array_diff($this->selectedIds, [$id]);
        $this->toast('Product deleted.');
    }

    // ── Bulk actions ─────────────────────────────────────────────────────────

    public function bulkActivate(): void
    {
        Gate::authorize('update', new Product);
        Product::whereIn('id', $this->selectedIds)->update(['is_active' => true]);
        $this->afterBulk('Products activated.');
    }

    public function bulkDeactivate(): void
    {
        Gate::authorize('update', new Product);
        Product::whereIn('id', $this->selectedIds)->update(['is_active' => false]);
        $this->afterBulk('Products deactivated.');
    }

    public function bulkMarkSoldOut(): void
    {
        Gate::authorize('update', new Product);
        Product::whereIn('id', $this->selectedIds)->update(['is_available' => false]);
        $this->afterBulk('Products marked as sold out.');
    }

    public function bulkDelete(): void
    {
        Gate::authorize('delete', new Product);
        $products = Product::whereIn('id', $this->selectedIds)->get();
        foreach ($products as $p) {
            if ($p->image) {
                ImageService::delete($p->image);
            }
        }
        Product::whereIn('id', $this->selectedIds)->delete();
        $this->afterBulk('Products deleted.');
    }

    private function afterBulk(string $message): void
    {
        $this->selectedIds = [];
        $this->selectAll   = false;
        $this->toast($message);
    }

    public function dismissToast(): void
    {
        $this->showToast = false;
    }

    private function toast(string $message): void
    {
        $this->showToast    = true;
        $this->toastMessage = $message;
    }

    private function getBaseQuery()
    {
        return Product::with(['category'])
            ->when($this->search, function ($q) {
                $q->where(function ($inner) {
                    $inner->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('sku', 'like', '%' . $this->search . '%')
                          ->orWhere('barcode', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->categoryFilter, fn ($q) => $q->where('category_id', $this->categoryFilter))
            ->when($this->statusFilter === 'active', fn ($q) => $q->where('is_active', true))
            ->when($this->statusFilter === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($this->availabilityFilter === 'available', fn ($q) => $q->where('is_available', true))
            ->when($this->availabilityFilter === 'soldout', fn ($q) => $q->where('is_available', false))
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function render()
    {
        return view('livewire.products.product-list', [
            'products'   => $this->getBaseQuery()->paginate($this->perPage),
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }
}
