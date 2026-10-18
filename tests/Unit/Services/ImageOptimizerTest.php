<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\ImageOptimizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class ImageOptimizerTest extends TestCase
{
    private ImageOptimizer $optimizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->optimizer = new ImageOptimizer();
    }

    /**
     * WebP のファイルは "RIFF....WEBP" で始まる
     */
    private function assertIsWebp(string $binary): void
    {
        $this->assertSame('RIFF', substr($binary, 0, 4));
        $this->assertSame('WEBP', substr($binary, 8, 4));
    }

    public function test_converts_jpeg_to_webp(): void
    {
        $jpeg = UploadedFile::fake()->image('photo.jpg', 800, 600);

        $webp = $this->optimizer->toWebp((string) file_get_contents($jpeg->getRealPath()));

        $this->assertIsWebp($webp);
        $this->assertSame([800, 600], array_slice(getimagesizefromstring($webp), 0, 2));
    }

    public function test_converts_png_to_webp(): void
    {
        $png = UploadedFile::fake()->image('icon.png', 300, 300);

        $this->assertIsWebp($this->optimizer->toWebp((string) file_get_contents($png->getRealPath())));
    }

    public function test_resizes_image_wider_than_max_width_keeping_aspect_ratio(): void
    {
        $large = UploadedFile::fake()->image('large.jpg', 3000, 2000);

        $webp = $this->optimizer->toWebp((string) file_get_contents($large->getRealPath()));

        [$width, $height] = getimagesizefromstring($webp);
        $this->assertSame(ImageOptimizer::MAX_WIDTH, $width);
        $this->assertSame(800, $height); // 3000x2000 → 1200x800
    }

    public function test_does_not_resize_image_exactly_at_max_width(): void
    {
        $image = UploadedFile::fake()->image('edge.jpg', ImageOptimizer::MAX_WIDTH, 100);

        $webp = $this->optimizer->toWebp((string) file_get_contents($image->getRealPath()));

        $this->assertSame(ImageOptimizer::MAX_WIDTH, getimagesizefromstring($webp)[0]);
    }

    public function test_converted_file_is_smaller_than_original_png(): void
    {
        $png = UploadedFile::fake()->image('big.png', 1000, 1000);
        $original = (string) file_get_contents($png->getRealPath());

        $this->assertLessThan(strlen($original), strlen($this->optimizer->toWebp($original)));
    }

    public function test_throws_for_non_image_data(): void
    {
        $this->expectException(RuntimeException::class);

        $this->optimizer->toWebp('これは画像ではない');
    }

    public function test_store_as_webp_saves_unique_webp_file(): void
    {
        Storage::fake('s3');
        $file = UploadedFile::fake()->image('photo.jpg', 400, 300);

        $path1 = $this->optimizer->storeAsWebp($file, 'products');
        $path2 = $this->optimizer->storeAsWebp($file, 'products');

        $this->assertMatchesRegularExpression('#^products/[0-9a-f-]{36}\.webp$#', $path1);
        $this->assertNotSame($path1, $path2);
        Storage::disk('s3')->assertExists($path1);
        $this->assertIsWebp(Storage::disk('s3')->get($path1));
    }
}
