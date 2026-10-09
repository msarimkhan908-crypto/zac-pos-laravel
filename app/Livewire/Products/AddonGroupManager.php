<?php

namespace App\Livewire\Products;

use App\Models\AddonGroup;
use App\Models\AddonOption;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Validate;
use Livewire\Component;

class AddonGroupManager extends Component
{
    public bool    $showModal   = false;
    public ?string $editingId   = null;

    #[Validate('required|string|max:100')]
    public string $name = '';

    #[Validate('required|integer|min:0|max:10')]
    public int $minSelectable = 0;

    #[Validate('required|integer|min:1|max:10')]
    public int $maxSelectable = 1;

    #[Validate('boolean')]
    public bool $isRequired = false;

    public array $options = [];

    public bool   $showToast    = false;
    public string $toastMessage = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', AddonGroup::class);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showModal = true;
    }

    public function openEdit(string $id): void
    {
        $group = AddonGroup::with('options')->findOrFail($id);
        Gate::authorize('update', $group);

        $this->editingId      = $id;
        $this->name           = $group->name;
        $this->minSelectable  = $group->min_selectable;
        $this->maxSelectable  = $group->max_selectable;
        $this->isRequired     = $group->is_required;
        $this->options        = $group->options->map(fn ($o) => [
            'id'          => $o->id,
            'name'        => $o->name,
            'extra_price' => number_format((float) $o->extra_price, 2, '.', ''),
            'sort_order'  => $o->sort_order,
        ])->all();

        $this->showModal = true;
    }

    public function addOption(): void
    {
        $this->options[] = [
            'id'          => null,
            'name'        => '',
            'extra_price' => '0.00',
            'sort_order'  => count($this->options),
        ];
    }

    public function removeOption(int $index): void
    {
        array_splice($this->options, $index, 1);
        $this->options = array_values($this->options);
    }

    public function save(): void
    {
        $this->validate([
            'name'          => 'required|string|max:100',
            'minSelectable' => 'required|integer|min:0|max:10',
            'maxSelectable' => 'required|integer|min:1|max:10',
            'options.*.name' => 'required|string|max:100',
            'options.*.extra_price' => 'required|numeric|min:0',
        ]);

        $data = [
            'name'           => $this->name,
            'min_selectable' => $this->minSelectable,
            'max_selectable' => $this->maxSelectable,
            'is_required'    => $this->isRequired,
        ];

        if ($this->editingId) {
            $group = AddonGroup::findOrFail($this->editingId);
            Gate::authorize('update', $group);
            $group->update($data);
            $message = 'Group updated.';
        } else {
            Gate::authorize('create', AddonGroup::class);
            $group   = AddonGroup::create($data);
            $message = 'Group created.';
        }

        // Sync options
        $existingIds = $group->options->pluck('id')->all();
        $keptIds     = [];

        foreach ($this->options as $i => $opt) {
            if ($opt['id'] && in_array($opt['id'], $existingIds)) {
                $group->options()->where('id', $opt['id'])->update([
                    'name'        => $opt['name'],
                    'extra_price' => (float) $opt['extra_price'],
                    'sort_order'  => $i,
                ]);
                $keptIds[] = $opt['id'];
            } else {
                $new = $group->options()->create([
                    'name'        => $opt['name'],
                    'extra_price' => (float) $opt['extra_price'],
                    'sort_order'  => $i,
                ]);
                $keptIds[] = $new->id;
            }
        }

        $group->options()->whereNotIn('id', $keptIds)->delete();

        $this->showModal = false;
        $this->resetForm();
        $this->toast($message);
    }

    public function delete(string $id): void
    {
        $group = AddonGroup::findOrFail($id);
        Gate::authorize('delete', $group);
        $group->options()->delete();
        $group->delete();
        $this->toast('Group deleted.');
    }

    public function dismissToast(): void
    {
        $this->showToast = false;
    }

    private function resetForm(): void
    {
        $this->name          = '';
        $this->minSelectable = 0;
        $this->maxSelectable = 1;
        $this->isRequired    = false;
        $this->options       = [];
        $this->resetValidation();
    }

    private function toast(string $message): void
    {
        $this->showToast    = true;
        $this->toastMessage = $message;
    }

    public function render()
    {
        return view('livewire.products.addon-group-manager', [
            'groups' => AddonGroup::withCount('products')
                ->with('options')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
