<?php

use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ScrapedItemController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/articles/{article:slug}', [ArticleController::class, 'show'])->name('articles.show');
Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');

Route::middleware('guest')->group(function (): void {
    Route::get('/admin/login', [AuthController::class, 'create'])->name('login');
    Route::post('/admin/login', [AuthController::class, 'store'])->name('login.store');
});

Route::post('/admin/logout', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function (): void {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/categories', [DashboardController::class, 'categories'])->name('categories.index');
    Route::get('/providers', [DashboardController::class, 'providers'])->name('providers.index');
    Route::get('/sources', [DashboardController::class, 'sources'])->name('sources.index');
    Route::get('/scraped-items', [ScrapedItemController::class, 'index'])->name('scraped-items.index');
    Route::get('/articles', [AdminArticleController::class, 'index'])->name('articles.index');
    Route::post('/sync', [DashboardController::class, 'syncAll'])->name('sync');
    Route::post('/sources/{source}/sync', [DashboardController::class, 'syncSource'])->name('sources.sync');

    Route::post('/categories', [DashboardController::class, 'storeCategory'])->name('categories.store');
    Route::patch('/categories/{category}', [DashboardController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{category}', [DashboardController::class, 'destroyCategory'])->name('categories.destroy');

    Route::post('/providers', [DashboardController::class, 'storeProvider'])->name('providers.store');
    Route::patch('/providers/{provider}', [DashboardController::class, 'updateProvider'])->name('providers.update');
    Route::delete('/providers/{provider}', [DashboardController::class, 'destroyProvider'])->name('providers.destroy');

    Route::post('/sources', [DashboardController::class, 'storeSource'])->name('sources.store');
    Route::patch('/sources/{source}', [DashboardController::class, 'updateSource'])->name('sources.update');
    Route::delete('/sources/{source}', [DashboardController::class, 'destroySource'])->name('sources.destroy');
    Route::get('/articles/{article}/edit', [AdminArticleController::class, 'edit'])->name('articles.edit');
    Route::patch('/articles/{article}', [AdminArticleController::class, 'update'])->name('articles.update');
    Route::delete('/articles/{article}', [AdminArticleController::class, 'destroy'])->name('articles.destroy');
    Route::post('/articles/{article}/publish', [AdminArticleController::class, 'publish'])->name('articles.publish');
    Route::post('/articles/{article}/unpublish', [AdminArticleController::class, 'unpublish'])->name('articles.unpublish');
});
