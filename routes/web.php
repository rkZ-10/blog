<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// Placeholder pages until the posts and projects controllers land in Phase 3.
Route::view('/posts', 'posts.index')->name('posts.index');
Route::view('/projects', 'projects.index')->name('projects.index');
