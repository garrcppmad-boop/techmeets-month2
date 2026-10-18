<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * アップロード画像を WebP に変換して保存する。
 *
 *   - 横幅が MAX_WIDTH を超える画像は縮小する（縦横比は維持）
 *   - JPEG / PNG / GIF / WebP を受け付け、WebP（品質 QUALITY）で保存する
 *   - ファイル名は毎回ユニークなので、CDN・ブラウザに長期間キャッシュさせる（Cache-Control）
 */
class ImageOptimizer
{
    public const MAX_WIDTH = 1200;
    public const QUALITY = 80;

    /** 1年間キャッシュ。ファイル名が変わらない限り中身も変わらない（immutable） */
    public const CACHE_CONTROL = 'public, max-age=31536000, immutable';

    /**
     * @return string 保存したファイルのパス（例: products/xxxxxxxx.webp）
     */
    public function storeAsWebp(UploadedFile $file, string $directory, string $disk = 's3'): string
    {
        $webp = $this->toWebp((string) file_get_contents($file->getRealPath()));
        $path = trim($directory, '/') . '/' . Str::uuid() . '.webp';

        Storage::disk($disk)->put($path, $webp, [
            'ContentType' => 'image/webp',
            'CacheControl' => self::CACHE_CONTROL,
        ]);

        return $path;
    }

    /**
     * 画像データ（バイナリ）を WebP に変換する
     */
    public function toWebp(string $binary): string
    {
        $image = @imagecreatefromstring($binary);
        if ($image === false) {
            throw new RuntimeException('画像を読み込めませんでした');
        }

        // 大きすぎる画像は縮小（スマホ・PC の表示に十分なサイズに抑える）
        if (imagesx($image) > self::MAX_WIDTH) {
            $resized = imagescale($image, self::MAX_WIDTH);
            imagedestroy($image);
            if ($resized === false) {
                throw new RuntimeException('画像を縮小できませんでした');
            }
            $image = $resized;
        }

        // PNG / GIF の透過を保ったまま WebP にする
        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        ob_start();
        $ok = imagewebp($image, null, self::QUALITY);
        $webp = (string) ob_get_clean();
        imagedestroy($image);

        if (! $ok || $webp === '') {
            throw new RuntimeException('WebP に変換できませんでした');
        }

        return $webp;
    }
}
