<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageOptimizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_helper_glide_image_menghasilkan_url_yang_sesuai(): void
    {
        $this->assertNull(glide_image(null));
        $this->assertNull(glide_image(''));

        // External URL should be returned as-is
        $external = 'https://example.com/photo.jpg';
        $this->assertEquals($external, glide_image($external));

        // Data URI should be returned as-is
        $dataUri = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
        $this->assertEquals($dataUri, glide_image($dataUri));

        // Path without params returns standard storage URL
        $storageUrl = glide_image('logos/my-logo.png');
        $this->assertStringContainsString('logos/my-logo.png', $storageUrl);

        // Path with params returns route image.show URL
        $resizedUrl = glide_image('logos/my-logo.png', ['w' => 150, 'h' => 150]);
        $this->assertStringContainsString('/img/logos/my-logo.png', $resizedUrl);
        $this->assertStringContainsString('w=150', $resizedUrl);
        $this->assertStringContainsString('h=150', $resizedUrl);
    }

    public function test_endpoint_img_mengembalikan_404_jika_file_tidak_ditemukan(): void
    {
        $response = $this->get('/img/non-existent-image.jpg');

        $response->assertStatus(404);
    }

    public function test_endpoint_img_melindungi_dari_path_traversal(): void
    {
        $response = $this->get('/img/../../passwords.txt');

        $response->assertStatus(404);
    }

    public function test_endpoint_img_melayani_gambar_dengan_parameter_resize(): void
    {
        // Buat file gambar dummy di disk public
        $file = UploadedFile::fake()->image('prewedding-sample.jpg', 600, 600);
        $path = $file->storeAs('prewedding', 'prewedding-sample.jpg', 'public');

        $response = $this->get("/img/{$path}?w=200&h=200&fit=crop");

        $response->assertStatus(200);
        $this->assertTrue(str_starts_with($response->headers->get('Content-Type') ?? '', 'image/'));
    }
}
