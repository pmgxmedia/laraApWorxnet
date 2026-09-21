<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CoverageController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProviderApplicationController;
use App\Http\Controllers\ProviderInboxController;
use App\Http\Controllers\ProviderSearchController;
use App\Http\Controllers\ServiceRequestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Public provider search — no login required to find nearby specialists.
Route::post('/providers/search', [ProviderSearchController::class, 'search'])
    ->middleware('throttle:60,1')
    ->name('providers.search');

// Public, read-only feed of live posted projects for the homepage listing.
Route::get('/projects/feed', [ProjectController::class, 'index'])->name('projects.feed');

// Public snapshot of nearby providers for the homepage coverage radar
// (live updates for this feed are pushed over the "coverage" websocket channel).
Route::get('/coverage/feed', [CoverageController::class, 'index'])->name('coverage.feed');

// Auth-required actions surfaced on the public homepage: post a project and
// send a service request to a provider. Rate-limited to prevent abuse.
Route::middleware(['auth', 'throttle:20,1'])->group(function () {
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::post('/service-requests', [ServiceRequestController::class, 'store'])->name('service-requests.store');
});

// Guest-only authentication routes.
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Provider application flow (requires an authenticated account).
Route::middleware('auth')->prefix('provider')->name('provider.')->group(function () {
    Route::get('/apply', [ProviderApplicationController::class, 'create'])->name('apply');
    Route::post('/apply', [ProviderApplicationController::class, 'store'])->name('apply.store');
    Route::get('/pending', [ProviderApplicationController::class, 'pending'])->name('pending');

    // Inbox: unread messages and bid status notifications for logged-in providers.
    Route::get('/inbox', [ProviderInboxController::class, 'index'])->name('inbox.index');
    Route::get('/inbox/conversations/{conversation}', [ProviderInboxController::class, 'showConversation'])->name('inbox.conversation');
    Route::post('/inbox/bids/{bid}/seen', [ProviderInboxController::class, 'markBidSeen'])->name('inbox.bid.seen');

    // Profile: account details, ratings and earnings analytics for logged-in providers.
    Route::get('/profile', [ProviderInboxController::class, 'profile'])->name('profile');
});
