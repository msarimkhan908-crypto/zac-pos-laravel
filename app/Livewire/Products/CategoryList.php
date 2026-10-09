<?php

namespace App\Livewire\Products;

use App\Models\Category;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Component;

class CategoryList extends Component
{
    // ── Modal state ──────────────────────────────────────────────────────────
    public bool    $showModal      = false;
    public ?string $editingId      = null;
    public ?string $confirmDeleteId = null;
    public string  $deleteError    = '';

    // ── Form fields ──────────────────────────────────────────────────────────
    #[Validate('required|string|max:100')]
    public string $name = '';

    #[Validate('nullable|string|max:500')]
    public string $description = '';

    #[Validate('nullable|regex:/^#[0-9a-fA-F]{6}$/')]
    public ?string $color = null;

    #[Validate('nullable|string|max:50')]
    public ?string $icon = null;

    // ── Toast ────────────────────────────────────────────────────────────────
    public bool   $showToast    = false;
    public string $toastMessage = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Category::class);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showModal = true;
    }

    public function openEdit(string $id): void
    {
        $category = Category::findOrFail($id);
        Gate::authorize('update', $category);

        $this->editingId   = $id;
        $this->name        = $category->name;
        $this->description = $category->description ?? '';
        $this->color       = $category->color;
        $this->icon        = $category->icon;
        $this->showModal   = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            $category = Category::findOrFail($this->editingId);
            Gate::authorize('update', $category);
            $category->update([
                'name'        => $this->name,
                'slug'        => Category::uniqueSlug($this->name, $this->editingId),
                'description' => $this->description ?: null,
                'color'       => $this->color ?: null,
                'icon'        => $this->icon ?: null,
            ]);
            $message = 'Category updated.';
        } else {
            Gate::authorize('create', Category::class);
            $maxOrder = Category::max('sort_order') ?? 0;
            Category::create([
                'name'        => $this->name,
                'slug'        => Category::uniqueSlug($this->name),
                'description' => $this->description ?: null,
                'color'       => $this->color ?: null,
                'icon'        => $this->icon ?: null,
                'sort_order'  => $maxOrder + 1,
            ]);
            $message = 'Category created.';
        }

        $this->showModal = false;
        $this->resetForm();
        $this->toast($message);
    }

    public function confirmDelete(string $id): void
    {
        $this->deleteError     = '';
        $this->confirmDeleteId = $id;
    }

    public function delete(): void
    {
        if (! $this->confirmDeleteId) {
            return;
        }

        $category = Category::withCount('products')->findOrFail($this->confirmDeleteId);
        Gate::authorize('delete', $category);

        if ($category->hasActiveProducts()) {
            $this->deleteError = 'This category has products. Move or delete them first.';
            return;
        }

        $category->delete();
        $this->confirmDeleteId = null;
        $this->deleteError     = '';
        $this->toast('Category deleted.');
    }

    public function cancelDelete(): void
    {
        $this->confirmDeleteId = null;
        $this->deleteError     = '';
    }

    public function toggleActive(string $id): void
    {
        $category = Category::findOrFail($id);
        Gate::authorize('update', $category);
        $category->update(['is_active' => ! $category->is_active]);
        $this->toast($category->is_active ? 'Category activated.' : 'Category deactivated.');
    }

    public function moveUp(string $id): void
    {
        $this->reorder($id, 'up');
    }

    public function moveDown(string $id): void
    {
        $this->reorder($id, 'down');
    }

    private function reorder(string $id, string $direction): void
    {
        $categories = Category::orderBy('sort_order')->get();
        $index      = $categories->search(fn ($c) => $c->id === $id);

        if ($index === false) {
            return;
        }

        $swapIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if ($swapIndex < 0 || $swapIndex >= $categories->count()) {
            return;
        }

        $a = $categories[$index];
        $b = $categories[$swapIndex];

        [$a->sort_order, $b->sort_order] = [$b->sort_order, $a->sort_order];

        if ($a->sort_order === $b->sort_order) {
            $a->sort_order = $direction === 'up' ? $b->sort_order - 1 : $b->sort_order + 1;
        }

        $a->save();
        $b->save();
    }

    public function dismissToast(): void
    {
        $this->showToast = false;
    }

    private function resetForm(): void
    {
        $this->name        = '';
        $this->description = '';
        $this->color       = null;
        $this->icon        = null;
        $this->resetValidation();
    }

    private function toast(string $message): void
    {
        $this->showToast    = true;
        $this->toastMessage = $message;
    }

    public function render()
    {
        return view('livewire.products.category-list', [
            'categories' => Category::withCount('products')
                ->orderBy('sort_order')
                ->get(),
        ]);
    }
}
