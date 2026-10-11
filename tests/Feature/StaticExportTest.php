<?php

use App\Models\Post;
use App\Models\Project;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    config([
        'filesystems.disks.export_test' => ['driver' => 'local', 'root' => storage_path('framework/testing/export')],
        'export.disk' => 'export_test',
    ]);
});

afterEach(function () {
    Storage::disk('export_test')->deleteDirectory('/');
});

it('exports public pages, the 404 page and .nojekyll, and nothing private', function () {
    Project::factory()->create(['slug' => 'exported-project']);
    Post::factory()->create(['slug' => 'exported-post', 'title' => 'Exported post']);
    Post::factory()->draft()->create(['slug' => 'draft-post']);

    $this->artisan('export')->assertSuccessful();

    $disk = Storage::disk('export_test');
    expect($disk->get('posts/exported-post/index.html'))->toContain('Exported post');
    expect($disk->get('404.html'))->toContain("This page doesn't exist");
    expect($disk->exists('index.html'))->toBeTrue();
    expect($disk->exists('projects/exported-project/index.html'))->toBeTrue();
    expect($disk->exists('feed.xml'))->toBeTrue();
    expect($disk->exists('sitemap.xml'))->toBeTrue();
    expect($disk->exists('.nojekyll'))->toBeTrue();
    expect($disk->exists('posts/draft-post/index.html'))->toBeFalse();
    expect($disk->exists('admin'))->toBeFalse();
    expect($disk->exists('up'))->toBeFalse();
    expect($disk->exists('index.php'))->toBeFalse();
});
