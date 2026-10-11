<?php

use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TagController;
use App\Http\Middleware\EnsureLocalEnvironment;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::resource('posts', PostController::class)->only(['index', 'show']);
Route::resource('projects', ProjectController::class)->only(['index', 'show']);
Route::get('/tags/{tag}', TagController::class)->name('tags.show');

Route::get('/feed.xml', FeedController::class)->name('feed');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

// Exported as 404.html, which GitHub Pages serves (with a 404 status) for any unknown path.
Route::view('/404.html', 'errors.404')->name('not-found');

// Local-only admin (404 in every other environment). Never linked from public pages and excluded
// from the static export.
Route::prefix('admin')
    ->name('admin.')
    ->middleware(EnsureLocalEnvironment::class)
    ->group(function () {
        Route::get('/', fn () => to_route('admin.posts.index'))->name('index');
        Route::resource('posts', AdminPostController::class);
    });
