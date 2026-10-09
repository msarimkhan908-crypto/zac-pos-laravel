<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', Product::class);

        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $path = ImageService::store($request->file('image'));
        $thumb = str_replace('products/', 'products/thumbs/', $path);

        return response()->json([
            'path'     => $path,
            'url'      => Storage::disk('public')->url($path),
            'thumbUrl' => Storage::disk('public')->url($thumb),
        ]);
    }
}
