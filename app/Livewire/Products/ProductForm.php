<?php

namespace App\Livewire\Products;

use App\Models\AddonGroup;
use App\Models\Category;
use App\Models\Product;
use App\Services\ImageService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ProductForm extends Component
{

    public ?string $productId = null;

    // ── Basic info ────────────────────────────────────────────────────────────
    public string  $name        = '';
    public ?string $categoryId  = null;
    public ?string $sku         = null;
    public ?string $barcode     = null;
    public string  $description = '';
    public string  $price       = '0.00';
    public ?string $costPrice   = null;
    public bool    $isActive    = true;
    public bool    $isAvailable = true;
    public bool    $printsOnKot = true;
    public string  $prepNote    = '';

    // ── Variants ──────────────────────────────────────────────────────────────
    public array $variants = [];

    // ── Add-ons ───────────────────────────────────────────────────────────────
    public array $selectedAddonGroupIds = [];

    // ── Image ─────────────────────────────────────────────────────────────────
    public ?string $pendingImagePath = null;
    public ?string $existingImage    = null;
    public bool    $removeImage      = false;

    // ── Toast ─────────────────────────────────────────────────────────────────
    public bool   $showToast    = false;
    public string $toastMessage = '';

    public function mount(?string $productId = null): void
    {
        $this->productId = $productId;

        if ($productId) {
            $product = Product::with(['variants', 'addonGroups'])->findOrFail($productId);
            Gate::authorize('update', $product);

            $this->name       = $product->name;
            $this->categoryId = $product->category_id;
            $this->sku        = $product->sku;
            $this->barcode    = $product->barcode;
            $this->description = $product->description ?? '';
            $this->price      = number_format((float) $product->price, 2, '.', '');
            $this->costPrice  = $product->cost_price ? number_format((float) $product->cost_price, 2, '.', '') : null;
            $this->isActive   = $product->is_active;
            $this->isAvailable = $product->is_available;
            $this->printsOnKot = $product->prints_on_kot;
            $this->prepNote   = $product->prep_note ?? '';
            $this->existingImage = $product->image;

            $this->variants = $product->variants->map(fn ($v) => [
                'id'         => $v->id,
                'name'       => $v->name,
                'price'      => number_format((float) $v->price, 2, '.', ''),
                'is_default' => $v->is_default,
                'sort_order' => $v->sort_order,
            ])->all();

            $this->selectedAddonGroupIds = $product->addonGroups->pluck('id')->all();
        } else {
            Gate::authorize('create', Product::class);
        }
    }

    public function generateSku(): void
    {
        $this->sku = Product::generateSku();
    }

    public function addVariant(): void
    {
        $this->variants[] = [
            'id'         => null,
            'name'       => '',
            'price'      => '0.00',
            'is_default' => empty($this->variants),
            'sort_order' => count($this->variants),
        ];
    }

    public function removeVariant(int $index): void
    {
        array_splice($this->variants, $index, 1);
        // Re-index
        $this->variants = array_values($this->variants);
    }

    public function setDefaultVariant(int $index): void
    {
        foreach ($this->variants as $i => &$v) {
            $v['is_default'] = ($i === $index);
        }
    }

    public function clearImage(): void
    {
        $this->removeImage       = true;
        $this->pendingImagePath  = null;
    }

    public function save(): void
    {
        $this->validate([
            'name'        => 'required|string|max:255',
            'categoryId'  => 'nullable|uuid|exists:categories,id',
            'sku'         => ['nullable', 'string', 'max:100',
                              Rule::unique('products', 'sku')
                                  ->ignore($this->productId)
                                  ->whereNull('deleted_at')],
            'barcode'     => 'nullable|string|max:100',
            'description' => 'nullable|string|max:2000',
            'price'       => 'required|numeric|min:0',
            'costPrice'   => 'nullable|numeric|min:0',
            'prepNote'    => 'nullable|string|max:500',
            'variants.*.name'  => 'required|string|max:100',
            'variants.*.price' => 'required|numeric|min:0',
        ]);

        $imagePath = $this->existingImage;

        if ($this->removeImage && $this->existingImage) {
            ImageService::delete($this->existingImage);
            $imagePath = null;
        }

        if ($this->pendingImagePath) {
            if ($this->existingImage && $this->existingImage !== $this->pendingImagePath) {
                ImageService::delete($this->existingImage);
            }
            $imagePath = $this->pendingImagePath;
        }

        $data = [
            'name'          => $this->name,
            'category_id'   => $this->categoryId ?: null,
            'sku'           => $this->sku ?: null,
            'barcode'       => $this->barcode ?: null,
            'description'   => $this->description ?: null,
            'price'         => (float) $this->price,
            'cost_price'    => $this->costPrice ? (float) $this->costPrice : null,
            'image'         => $imagePath,
            'is_active'     => $this->isActive,
            'is_available'  => $this->isAvailable,
            'prints_on_kot' => $this->printsOnKot,
            'prep_note'     => $this->prepNote ?: null,
        ];

        if ($this->productId) {
            $product = Product::findOrFail($this->productId);
            $product->update($data);
        } else {
            $product = Product::create($data);
            $this->productId = $product->id;
        }

        // Sync variants
        $existingIds = $product->variants->pluck('id')->all();
        $keptIds     = [];

        foreach ($this->variants as $i => $v) {
            if ($v['id'] && in_array($v['id'], $existingIds)) {
                $product->variants()->where('id', $v['id'])->update([
                    'name'       => $v['name'],
                    'price'      => (float) $v['price'],
                    'is_default' => (bool) $v['is_default'],
                    'sort_order' => $i,
                ]);
                $keptIds[] = $v['id'];
            } else {
                $new = $product->variants()->create([
                    'name'       => $v['name'],
                    'price'      => (float) $v['price'],
                    'is_default' => (bool) $v['is_default'],
                    'sort_order' => $i,
                ]);
                $keptIds[] = $new->id;
            }
        }

        // Delete removed variants
        $product->variants()->whereNotIn('id', $keptIds)->delete();

        // Sync add-on groups
        $product->addonGroups()->sync($this->selectedAddonGroupIds);

        // Update component state
        $product->refresh()->load(['variants', 'addonGroups']);
        $this->existingImage    = $product->image;
        $this->removeImage      = false;
        $this->pendingImagePath = null;
        $this->variants         = $product->variants->map(fn ($v) => [
            'id'         => $v->id,
            'name'       => $v->name,
            'price'      => number_format((float) $v->price, 2, '.', ''),
            'is_default' => $v->is_default,
            'sort_order' => $v->sort_order,
        ])->all();
        $this->selectedAddonGroupIds = $product->addonGroups->pluck('id')->all();

        $this->showToast    = true;
        $this->toastMessage = 'Product saved.';
    }

    public function cancel(): void
    {
        $this->redirectRoute('products.index');
    }

    public function dismissToast(): void
    {
        $this->showToast = false;
    }

    public function render()
    {
        return view('livewire.products.product-form', [
            'categories'   => Category::where('is_active', true)->orderBy('sort_order')->get(),
            'addonGroups'  => AddonGroup::with('options')->orderBy('name')->get(),
        ]);
    }
}
