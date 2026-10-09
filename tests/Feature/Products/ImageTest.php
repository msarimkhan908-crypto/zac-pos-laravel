<?php

namespace Tests\Feature\Products;

use App\Livewire\Products\ProductForm;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ImageTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_valid_jpeg_upload_stores_main_and_thumb(): void
    {
        $file = UploadedFile::fake()->image('product.jpg', 900, 900);

        Livewire::actingAs($this->admin())
            ->test(ProductForm::class)
            ->set('name', 'Burger')
            ->set('price', '8.99')
            ->set('newImage', $file)
            ->call('save')
            ->assertHasNoErrors();

        $product = Product::where('name', 'Burger')->first();
        $this->assertNotNull($product->image, 'Product image path should be set after upload');

        Storage::disk('public')->assertExists($product->image);

        $thumb = str_replace('products/', 'products/thumbs/', $product->image);
        Storage::disk('public')->assertExists($thumb);
    }

    public function test_valid_png_upload_is_accepted(): void
    {
        $file = UploadedFile::fake()->image('product.png', 400, 400);

        Livewire::actingAs($this->admin())
            ->test(ProductForm::class)
            ->set('name', 'Fries')
            ->set('price', '3.49')
            ->set('newImage', $file)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('products', ['name' => 'Fries']);
    }

    public function test_gif_is_rejected_by_mime_rule(): void
    {
        // GIF is not in the allowed mimes (jpg,jpeg,png,webp) but is previewable by Livewire
        $file = UploadedFile::fake()->image('product.gif', 100, 100);

        Livewire::actingAs($this->admin())
            ->test(ProductForm::class)
            ->set('name', 'Burger')
            ->set('price', '8.99')
            ->set('newImage', $file)
            ->call('save')
            ->assertHasErrors(['newImage']);
    }

    public function test_image_over_2mb_is_rejected(): void
    {
        $file = UploadedFile::fake()->image('big.jpg')->size(2049);

        Livewire::actingAs($this->admin())
            ->test(ProductForm::class)
            ->set('name', 'Burger')
            ->set('price', '8.99')
            ->set('newImage', $file)
            ->call('save')
            ->assertHasErrors(['newImage']);
    }

    public function test_remove_image_deletes_files(): void
    {
        Storage::disk('public')->put('products/test-image.jpg', 'fake');
        Storage::disk('public')->put('products/thumbs/test-image.jpg', 'fake');

        $product = Product::factory()->create(['image' => 'products/test-image.jpg']);

        Livewire::actingAs($this->admin())
            ->test(ProductForm::class, ['productId' => $product->id])
            ->call('clearImage')
            ->call('save')
            ->assertHasNoErrors();

        Storage::disk('public')->assertMissing('products/test-image.jpg');
        Storage::disk('public')->assertMissing('products/thumbs/test-image.jpg');
        $this->assertDatabaseHas('products', ['id' => $product->id, 'image' => null]);
    }

    public function test_uploading_new_image_replaces_old(): void
    {
        Storage::disk('public')->put('products/old-image.jpg', 'fake');
        Storage::disk('public')->put('products/thumbs/old-image.jpg', 'fake');

        $product = Product::factory()->create(['image' => 'products/old-image.jpg']);
        $newFile = UploadedFile::fake()->image('new.jpg', 400, 400);

        Livewire::actingAs($this->admin())
            ->test(ProductForm::class, ['productId' => $product->id])
            ->set('newImage', $newFile)
            ->call('save')
            ->assertHasNoErrors();

        $updated = $product->fresh();
        $this->assertNotNull($updated->image);
        $this->assertNotEquals('products/old-image.jpg', $updated->image);
    }
}
