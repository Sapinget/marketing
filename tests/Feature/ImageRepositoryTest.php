<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ImageRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private string $repositoryPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repositoryPath = 'test-img-repo-'.uniqid();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(base_path('resources/img/'.$this->repositoryPath));

        parent::tearDown();
    }

    public function test_serve_requires_settings_access(): void
    {
        $this->app['auth']->logout();
        $path = $this->createImage('private.png');

        $this->get('/api/img-repo/serve?path='.$path)->assertUnauthorized();
    }

    public function test_mkdir_allows_nonexistent_parents_inside_repository(): void
    {
        $this->postJson('/api/img-repo/mkdir', [
            'path' => $this->repositoryPath.'/new-parent',
            'name' => 'child',
        ])->assertOk()
            ->assertJsonPath('path', $this->repositoryPath.'/new-parent/child');

        $this->assertDirectoryExists(base_path('resources/img/'.$this->repositoryPath.'/new-parent/child'));
    }

    public function test_repository_rejects_traversal_paths(): void
    {
        $this->getJson('/api/img-repo/browse?path=../')->assertForbidden();

        $this->postJson('/api/img-repo/mkdir', [
            'path' => $this->repositoryPath.'/../../outside',
            'name' => 'child',
        ])->assertForbidden();
    }

    public function test_upload_rejects_non_image_content_and_generates_filename(): void
    {
        File::makeDirectory(base_path('resources/img/'.$this->repositoryPath), 0755, true);

        $this->post('/api/img-repo/upload', [
            'path' => $this->repositoryPath,
            'files' => [UploadedFile::fake()->create('photo.png', 10, 'image/png')],
        ])->assertUnprocessable();

        $response = $this->post('/api/img-repo/upload', [
            'path' => $this->repositoryPath,
            'files' => [UploadedFile::fake()->image('client-name.png', 10, 10)],
        ])->assertOk();

        $saved = $response->json('saved.0');
        $this->assertMatchesRegularExpression('#^'.$this->repositoryPath.'/[0-9a-f-]{36}\\.png$#', $saved);
        $this->assertFileExists(base_path('resources/img/'.$saved));
    }

    private function createImage(string $name): string
    {
        $directory = base_path('resources/img/'.$this->repositoryPath);
        File::makeDirectory($directory, 0755, true);
        UploadedFile::fake()->image($name, 10, 10)->move($directory, $name);

        return $this->repositoryPath.'/'.$name;
    }
}
