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

    // ── Upload endpoint ───────────────────────────────────────────────────────

    public function test_valid_jpeg_upload_stores_main_and_thumb(): void
    {
        $file = UploadedFile::fake()->image('product.jpg', 900, 900);

        $response = $this->actingAs($this->admin())
            ->postJson(route('products.upload-image'), ['image' => $file]);

        $response->assertOk()->assertJsonStructure(['path', 'url', 'thumbUrl']);

        $path = $response->json('path');
        Storage::disk('public')->assertExists($path);
        $thumb = str_replace('products/', 'products/thumbs/', $path);
        Storage::disk('public')->assertExists($thumb);
    }

    public function test_valid_png_upload_is_accepted(): void
    {
        $file = UploadedFile::fake()->image('product.png', 400, 400);

        $this->actingAs($this->admin())
            ->postJson(route('products.upload-image'), ['image' => $file])
            ->assertOk()
            ->assertJsonStructure(['path']);
    }

    public function test_non_image_file_is_rejected(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $this->actingAs($this->admin())
            ->postJson(route('products.upload-image'), ['image' => $file])
            ->assertUnprocessable();
    }

    public function test_image_over_2mb_is_rejected(): void
    {
        $file = UploadedFile::fake()->image('big.jpg')->size(2049);

        $this->actingAs($this->admin())
            ->postJson(route('products.upload-image'), ['image' => $file])
            ->assertUnprocessable();
    }

    public function test_guest_cannot_upload_image(): void
    {
        $file = UploadedFile::fake()->image('product.jpg');

        $this->postJson(route('products.upload-image'), ['image' => $file])
            ->assertUnauthorized();
    }

    public function test_cashier_cannot_upload_image(): void
    {
        $file = UploadedFile::fake()->image('product.jpg');

        $this->actingAs(User::factory()->create(['role' => 'cashier']))
            ->postJson(route('products.upload-image'), ['image' => $file])
            ->assertForbidden();
    }

    // ── ProductForm with image path ───────────────────────────────────────────

    public function test_pending_image_path_is_saved_with_product(): void
    {
        Storage::disk('public')->put('products/test-image.jpg', 'fake');

        Livewire::actingAs($this->admin())
            ->test(ProductForm::class)
            ->set('name', 'Burger')
            ->set('price', '8.99')
            ->set('pendingImagePath', 'products/test-image.jpg')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('products', [
            'name'  => 'Burger',
            'image' => 'products/test-image.jpg',
        ]);
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

    public function test_replacing_image_deletes_old_files(): void
    {
        Storage::disk('public')->put('products/old-image.jpg', 'fake');
        Storage::disk('public')->put('products/thumbs/old-image.jpg', 'fake');
        Storage::disk('public')->put('products/new-image.jpg', 'fake');

        $product = Product::factory()->create(['image' => 'products/old-image.jpg']);

        Livewire::actingAs($this->admin())
            ->test(ProductForm::class, ['productId' => $product->id])
            ->set('pendingImagePath', 'products/new-image.jpg')
            ->call('save')
            ->assertHasNoErrors();

        Storage::disk('public')->assertMissing('products/old-image.jpg');
        $this->assertDatabaseHas('products', ['id' => $product->id, 'image' => 'products/new-image.jpg']);
    }
}
