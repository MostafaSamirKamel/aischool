<?php

namespace Tests\Unit\Services;

use App\Constants\FileConstants;
use App\Services\FileUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileUploadServiceTest extends TestCase
{
    protected FileUploadService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->service = new FileUploadService();
    }

    /** @test */
    public function it_can_upload_image(): void
    {
        $file = UploadedFile::fake()->image('test.jpg', 600, 400);

        $path = $this->service->uploadImage($file, 'test-images');

        $this->assertNotEmpty($path);
        Storage::disk('public')->assertExists($path);
    }

    /** @test */
    public function it_can_upload_document(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $path = $this->service->uploadDocument($file, 'test-documents');

        $this->assertNotEmpty($path);
        Storage::disk('public')->assertExists($path);
    }

    /** @test */
    public function it_rejects_oversized_files(): void
    {
        $this->expectException(\Exception::class);

        // Create file larger than max allowed
        $file = UploadedFile::fake()->create('large.pdf', FileConstants::MAX_DOCUMENT_SIZE + 1);

        $this->service->uploadDocument($file, 'test-documents');
    }

    /** @test */
    public function it_rejects_invalid_mime_types(): void
    {
        $this->expectException(\Exception::class);

        $file = UploadedFile::fake()->create('malicious.exe', 100);

        $this->service->uploadDocument($file, 'test-documents');
    }

    /** @test */
    public function it_can_delete_files(): void
    {
        $file = UploadedFile::fake()->image('test.jpg');
        $path = $this->service->uploadImage($file, 'test-images');

        $result = $this->service->delete($path);

        $this->assertTrue($result);
        Storage::disk('public')->assertMissing($path);
    }

    /** @test */
    public function it_generates_secure_filenames(): void
    {
        $file = UploadedFile::fake()->image('test.jpg');

        $path = $this->service->uploadImage($file, 'test-images');

        // Check that filename doesn't contain original name
        $this->assertStringNotContainsString('test', basename($path));
        // Check that filename has timestamp and random string
        $this->assertMatchesRegularExpression('/\d+_[a-zA-Z0-9]+\.jpg$/', $path);
    }

    /** @test */
    public function it_can_check_file_existence(): void
    {
        $file = UploadedFile::fake()->image('test.jpg');
        $path = $this->service->uploadImage($file, 'test-images');

        $this->assertTrue($this->service->exists($path));
        $this->assertFalse($this->service->exists('non-existent-file.jpg'));
    }

    /** @test */
    public function it_can_get_file_url(): void
    {
        $file = UploadedFile::fake()->image('test.jpg');
        $path = $this->service->uploadImage($file, 'test-images');

        $url = $this->service->getUrl($path);

        $this->assertStringContainsString('/storage/', $url);
    }
}
