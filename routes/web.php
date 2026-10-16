<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — IKI IT Helpdesk
|--------------------------------------------------------------------------
*/

// ─── System Health Diagnostics ───
Route::get('/health', [HealthCheckController::class, 'check'])->name('health');

// ─── Authentication ───
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ─── Authenticated Routes ───
Route::middleware('auth')->group(function () {

    // Redirect root to dashboard
    Route::get('/', fn () => redirect()->route('dashboard'));

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ─── Notifications ───
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('markAsRead');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllRead'])->name('markAllRead');
    });

    // ─── Approvals ───
    Route::prefix('approvals')->name('approvals.')->group(function () {
        Route::get('/', [ApprovalController::class, 'index'])->name('index');
        Route::post('/{approval}/decide', [ApprovalController::class, 'decide'])->name('decide');
    });

    // ─── Reports & Analytics ───
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/export', [ReportController::class, 'export'])->name('export');
    });

    // ─── IT Asset Registry ───
    Route::prefix('assets')->name('assets.')->group(function () {
        Route::get('/', [AssetController::class, 'index'])->name('index');
        Route::get('/create', [AssetController::class, 'create'])->name('create');
        Route::post('/', [AssetController::class, 'store'])->name('store');
        Route::get('/{asset}', [AssetController::class, 'show'])->name('show');
        Route::get('/{asset}/edit', [AssetController::class, 'edit'])->name('edit');
        Route::put('/{asset}', [AssetController::class, 'update'])->name('update');
        Route::delete('/{asset}', [AssetController::class, 'destroy'])->name('destroy');
    });

    // ─── Knowledge Base ───
    Route::prefix('knowledge-base')->name('knowledge-base.')->group(function () {
        Route::get('/', [KnowledgeBaseController::class, 'index'])->name('index');
        Route::get('/manage', [KnowledgeBaseController::class, 'manage'])->name('manage');
        Route::get('/create', [KnowledgeBaseController::class, 'create'])->name('create');
        Route::post('/', [KnowledgeBaseController::class, 'store'])->name('store');
        Route::get('/article/{slug}', [KnowledgeBaseController::class, 'show'])->name('show');
        Route::post('/article/{article}/helpful', [KnowledgeBaseController::class, 'helpful'])->name('helpful');
        Route::post('/article/{article}/publish', [KnowledgeBaseController::class, 'publish'])->name('publish');
        Route::get('/article/{article}/edit', [KnowledgeBaseController::class, 'edit'])->name('edit');
        Route::put('/article/{article}', [KnowledgeBaseController::class, 'update'])->name('update');
        Route::delete('/article/{article}', [KnowledgeBaseController::class, 'destroy'])->name('destroy');
    });

    // ─── Tickets ───
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [TicketController::class, 'index'])->name('index');
        Route::get('/create', [TicketController::class, 'create'])->name('create');
        Route::post('/', [TicketController::class, 'store'])->name('store');
        Route::get('/{ticket}', [TicketController::class, 'show'])->name('show');

        // Ticket actions
        Route::post('/{ticket}/assign', [TicketController::class, 'assign'])->name('assign');
        Route::post('/{ticket}/transition', [TicketController::class, 'transition'])->name('transition');
        Route::post('/{ticket}/comments', [TicketController::class, 'comment'])->name('comment');
        Route::post('/{ticket}/attachments', [TicketController::class, 'uploadAttachment'])->name('uploadAttachment');
        Route::get('/{ticket}/attachments/{attachment}', [TicketController::class, 'downloadAttachment'])->name('downloadAttachment');
        Route::post('/{ticket}/rate', [TicketController::class, 'rate'])->name('rate');
    });
});
