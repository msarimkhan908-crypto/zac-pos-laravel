<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageService
{
    const DISK      = 'public';
    const PATH      = 'products';
    const MAX_BYTES = 2 * 1024 * 1024;
    const MAX_DIM   = 800;
    const THUMB_DIM = 200;

    public static function store(UploadedFile $file, ?string $oldPath = null): string
    {
        if ($oldPath) {
            static::delete($oldPath);
        }

        $filename = Str::uuid() . '.jpg';
        $path     = static::PATH . '/' . $filename;
        $thumb    = static::PATH . '/thumbs/' . $filename;

        $src = $file->getRealPath();

        Storage::disk(static::DISK)->put($path, static::resizeGd($src, static::MAX_DIM, static::MAX_DIM));
        Storage::disk(static::DISK)->put($thumb, static::resizeGd($src, static::THUMB_DIM, static::THUMB_DIM));

        return $path;
    }

    public static function delete(string $path): void
    {
        Storage::disk(static::DISK)->delete($path);
        $thumb = str_replace(static::PATH . '/', static::PATH . '/thumbs/', $path);
        Storage::disk(static::DISK)->delete($thumb);
    }

    private static function resizeGd(string $sourcePath, int $maxW, int $maxH): string
    {
        [$origW, $origH, $type] = getimagesize($sourcePath);

        $scale = ($origW > $maxW || $origH > $maxH)
            ? min($maxW / $origW, $maxH / $origH)
            : 1.0;

        $newW = (int) round($origW * $scale);
        $newH = (int) round($origH * $scale);

        $src = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG  => imagecreatefrompng($sourcePath),
            IMAGETYPE_WEBP => imagecreatefromwebp($sourcePath),
            default        => throw new \InvalidArgumentException('Unsupported image type'),
        };

        $dst   = imagecreatetruecolor($newW, $newH);
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefill($dst, 0, 0, $white);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        ob_start();
        imagejpeg($dst, null, 85);
        $content = ob_get_clean();

        imagedestroy($src);
        imagedestroy($dst);

        return $content;
    }
}
