<?php

use App\Http\Controllers\Admin\ModerasiController;
use App\Http\Controllers\Admin\RewardClaimController as AdminRewardClaimController;
use App\Http\Controllers\Admin\RewardSettingController;
use App\Http\Controllers\Admin\XpSettingController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataFeedController;
use App\Http\Controllers\DependantDropdownController;
use App\Http\Controllers\DoaController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\JadwalMajelisController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\KontributorController;
use App\Http\Controllers\MajelisController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RamadhanController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\User\EventController as UserEventController;
use App\Http\Controllers\User\GuruController as UserGuruController;
use App\Http\Controllers\User\JadwalMajelisController as UserJadwalMajelisController;
use App\Http\Controllers\User\KontribusiAcaraController;
use App\Http\Controllers\User\KontribusiAmalanController;
use App\Http\Controllers\User\KontribusiController;
use App\Http\Controllers\User\KontribusiGuruController;
use App\Http\Controllers\User\KontribusiJadwalController;
use App\Http\Controllers\User\KontribusiMajelisController;
use App\Http\Controllers\User\MajelisController as UserMajelisController;
use App\Http\Controllers\User\ManagedMajelisController;
use App\Http\Controllers\User\ManagedRamadhanController;
use App\Http\Controllers\User\ManageEventController;
use App\Http\Controllers\User\SettingController;
use App\Http\Controllers\User\VideoController as UserVideoController;
use App\Http\Controllers\User\WiridController as UserWiridController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\WiridController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::redirect('/', 'beranda');

Route::get('/beranda', [HomeController::class, 'index'])->name('beranda');
Route::get('/majelis', [UserMajelisController::class, 'list'])->name('majelis-list');
Route::get('/jadwal-majelis', [UserJadwalMajelisController::class, 'list'])->name('jadwal-majelis-list');
Route::get('/jadwal-majelis/{id}', [UserJadwalMajelisController::class, 'detail'])->name('jadwal-majelis-detail');
Route::get('/majelis/{id}', [UserMajelisController::class, 'detail'])->name('majelis-detail');
Route::get('/guru', [UserGuruController::class, 'list'])->name('guru-list');
Route::get('/guru/{teacher}', [UserGuruController::class, 'detail'])->name('guru-detail');
Route::get('/video', [UserVideoController::class, 'list'])->name('video-list');
Route::get('/event', [UserEventController::class, 'list'])->name('event-list');
Route::get('/wirid', [UserWiridController::class, 'list'])->name('wirid-list');
Route::get('/manaqib', [\App\Http\Controllers\User\BiographyController::class, 'list'])->name('manaqib-list');
Route::get('/manaqib/{slug}', [\App\Http\Controllers\User\BiographyController::class, 'detail'])->name('manaqib-detail');
Route::get('/pustaka', [\App\Http\Controllers\User\LibraryController::class, 'list'])->name('pustaka-list');
Route::get('/pustaka/{library}', [\App\Http\Controllers\User\LibraryController::class, 'detail'])->name('pustaka-detail');
Route::get('/tulisan', [\App\Http\Controllers\User\PostController::class, 'index'])->name('tulisan.list');
Route::get('/tulisan/{slug}', [\App\Http\Controllers\User\PostController::class, 'detail'])->name('tulisan.detail');
Route::get('/tulisan/{slug}/download', [\App\Http\Controllers\User\PostController::class, 'download'])
    ->name('tulisan.download')
    ->middleware('auth');
Route::get('/artikel/{slug}', [\App\Http\Controllers\User\ArticleController::class, 'detail'])->name('artikel.detail');
Route::get('/artikel/{slug}/download', [\App\Http\Controllers\User\ArticleController::class, 'download'])
    ->name('artikel.download')
    ->middleware('auth');

Route::get('/jadwal-ramadhan', [\App\Http\Controllers\User\RamadhanController::class, 'index'])->name('ramadhan-list');
Route::get('/jadwal-ramadhan/{id}', [\App\Http\Controllers\User\RamadhanController::class, 'detail'])->name('ramadhan-detail');

Route::middleware(['auth'])->group(function () {
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect('/beranda');
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    })->middleware('throttle:6,1')->name('verification.send');

    Route::get('/pengaturan-akun', [SettingController::class, 'index'])->name('pengaturan-akun');
    Route::put('/pengaturan-akun', [SettingController::class, 'update'])->name('pengaturan-akun.update');
    Route::post('/user/onesignal-id', [SettingController::class, 'updateOneSignalId'])->name('user.onesignal.update');
});

Route::get('auth', [\App\Http\Controllers\GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google', [\App\Http\Controllers\GoogleAuthController::class, 'handleGoogleCallback']);

// Kontributor (public)
Route::get('/kontributor', [KontributorController::class, 'index'])->name('kontributor.index');
Route::get('/kontributor/profil/{username}', [KontributorController::class, 'profil'])->name('kontributor.profil');

Route::get('/tentang-kami', function () {
    return view('pages/user/tentang-kami');
})->name('tentang-kami');

Route::get('/provinces', [DependantDropdownController::class, 'provinces'])->name('provinces');
Route::get('/get-cities/{province_code}', [DependantDropdownController::class, 'getCities'])->name('get-cities');
Route::get('/get-districts/{city_code}', [DependantDropdownController::class, 'getDistricts'])->name('get-districts');
Route::get('/get-villages/{district_code}', [DependantDropdownController::class, 'getVillages'])->name('get-villages');

Route::get('/catatan-pengajian', [\App\Http\Controllers\User\CatatanPengajianController::class, 'index'])->name('catatan-pengajian.list');
Route::get('/catatan-pengajian/{id}', [\App\Http\Controllers\User\CatatanPengajianController::class, 'show'])->name('catatan-pengajian.detail');

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/reward-klaim/{claim}/bukti', [\App\Http\Controllers\RewardProofController::class, 'show'])->name('reward-klaim.bukti');

    // Pustaka berbayar
    Route::post('/pustaka/{library}/beli', [\App\Http\Controllers\User\LibraryController::class, 'purchase'])->name('pustaka-purchase');
    Route::get('/pustaka/{library}/baca', [\App\Http\Controllers\User\LibraryController::class, 'read'])->name('pustaka-read');
    Route::get('/pustaka/{library}/dokumen', [\App\Http\Controllers\User\LibraryController::class, 'stream'])->name('pustaka-stream');
    Route::get('/pustaka-saya', [\App\Http\Controllers\User\LibraryController::class, 'myLibraries'])->name('pustaka-saya');

    Route::get('/kelola-majelis/{id}', [ManagedMajelisController::class, 'edit'])->name('kelola-majelis.edit');
    Route::put('/kelola-majelis/{id}', [ManagedMajelisController::class, 'update'])->name('kelola-majelis.update');
    Route::get('/kelola-jadwal-majelis', [ManagedMajelisController::class, 'list'])->name('kelola-jadwal-majelis');
    Route::get('/kelola-jadwal-majelis/create', [ManagedMajelisController::class, 'create'])->name('kelola-jadwal-majelis.create');
    Route::post('/kelola-jadwal-majelis', [ManagedMajelisController::class, 'store'])->name('kelola-jadwal-majelis.store');
    Route::get('/kelola-jadwal-majelis/{id}/edit', [ManagedMajelisController::class, 'editSchedule'])->name('kelola-jadwal-majelis.edit');
    Route::put('/kelola-jadwal-majelis/{id}', [ManagedMajelisController::class, 'updateSchedule'])->name('kelola-jadwal-majelis.update');

    Route::get('/kelola-acara-majelis', [ManageEventController::class, 'index'])->name('kelola-acara-majelis');
    Route::get('/kelola-acara-majelis/create', [ManageEventController::class, 'create'])->name('kelola-acara-majelis.create');
    Route::post('/kelola-acara-majelis', [ManageEventController::class, 'store'])->name('kelola-acara-majelis.store');
    Route::get('/kelola-acara-majelis/{id}/edit', [ManageEventController::class, 'edit'])->name('kelola-acara-majelis.edit');
    Route::put('/kelola-acara-majelis/{id}', [ManageEventController::class, 'update'])->name('kelola-acara-majelis.update');

    Route::resource('kelola-ramadhan', ManagedRamadhanController::class);
    Route::resource('kelola-tulisan', \App\Http\Controllers\PostController::class);

    // Kelola Yayasan
    Route::get('/kelola-mitra/{id}', [\App\Http\Controllers\User\ManagedFoundationController::class, 'edit'])->name('kelola-mitra.edit');
    Route::put('/kelola-mitra/{id}', [\App\Http\Controllers\User\ManagedFoundationController::class, 'update'])->name('kelola-mitra.update');

    // Kelola Artikel Ilmiah
    Route::get('/kelola-artikel-ilmiah', [\App\Http\Controllers\User\ManagedFoundationController::class, 'listArticles'])->name('kelola-artikel.index');
    Route::get('/kelola-artikel-ilmiah/create', [\App\Http\Controllers\User\ManagedFoundationController::class, 'createArticle'])->name('kelola-artikel.create');
    Route::post('/kelola-artikel-ilmiah', [\App\Http\Controllers\User\ManagedFoundationController::class, 'storeArticle'])->name('kelola-artikel.store');
    Route::get('/kelola-artikel-ilmiah/{id}/edit', [\App\Http\Controllers\User\ManagedFoundationController::class, 'editArticle'])->name('kelola-artikel.edit');
    Route::put('/kelola-artikel-ilmiah/{id}', [\App\Http\Controllers\User\ManagedFoundationController::class, 'updateArticle'])->name('kelola-artikel.update');
    Route::delete('/kelola-artikel-ilmiah/{id}', [\App\Http\Controllers\User\ManagedFoundationController::class, 'destroyArticle'])->name('kelola-artikel.destroy');

    // Route khusus onboarding majelis (hanya bisa diakses via link yang di-generate artisan majelis:invite)
    Route::get('/registrasi-majelis/baru', [ManagedMajelisController::class, 'register'])
        ->name('majelis.onboarding')
        ->middleware('signed');

    Route::get('/favorit-saya', [\App\Http\Controllers\User\FavoriteController::class, 'index'])->name('favorit-saya');

    Route::post('/jadwal-majelis/{id}/notes', [\App\Http\Controllers\User\ScheduleNoteController::class, 'store'])->name('jadwal-majelis.notes.store');
    Route::delete('/jadwal-majelis/notes/{id}', [\App\Http\Controllers\User\ScheduleNoteController::class, 'destroy'])->name('jadwal-majelis.notes.destroy');
    Route::post('/jadwal-majelis/notes/{note_id}/comments', [\App\Http\Controllers\User\ScheduleNoteCommentController::class, 'store'])->name('jadwal-majelis.notes.comments.store');
    Route::delete('/jadwal-majelis/notes/comments/{id}', [\App\Http\Controllers\User\ScheduleNoteCommentController::class, 'destroy'])->name('jadwal-majelis.notes.comments.destroy');

    Route::get('/kelola-catatan', [\App\Http\Controllers\User\KelolaCatatanController::class, 'index'])->name('kelola-catatan.index');

    // Daftar Kontributor
    Route::post('/kontributor/daftar', [KontributorController::class, 'daftar'])->name('kontributor.daftar');

    // Dashboard & CRUD Kontribusi (hanya Kontributor)
    Route::middleware(['role:Kontributor'])->group(function () {
        Route::get('/kontributor/saya', [KontribusiController::class, 'index'])->name('kontributor.saya');

        Route::post('/kontributor/saya/reward', [\App\Http\Controllers\User\RewardClaimController::class, 'store'])->name('kontributor.reward.store');

        Route::get('/kontributor/saya/majelis/create', [KontribusiMajelisController::class, 'create'])->name('kontributor.majelis.create');
        Route::post('/kontributor/saya/majelis', [KontribusiMajelisController::class, 'store'])->name('kontributor.majelis.store');
        Route::get('/kontributor/saya/majelis/{id}/edit', [KontribusiMajelisController::class, 'edit'])->name('kontributor.majelis.edit');
        Route::put('/kontributor/saya/majelis/{id}', [KontribusiMajelisController::class, 'update'])->name('kontributor.majelis.update');

        Route::get('/kontributor/saya/guru/create', [KontribusiGuruController::class, 'create'])->name('kontributor.guru.create');
        Route::post('/kontributor/saya/guru', [KontribusiGuruController::class, 'store'])->name('kontributor.guru.store');
        Route::get('/kontributor/saya/guru/{id}/edit', [KontribusiGuruController::class, 'edit'])->name('kontributor.guru.edit');
        Route::put('/kontributor/saya/guru/{id}', [KontribusiGuruController::class, 'update'])->name('kontributor.guru.update');

        Route::get('/kontributor/saya/jadwal/create', [KontribusiJadwalController::class, 'create'])->name('kontributor.jadwal.create');
        Route::post('/kontributor/saya/jadwal', [KontribusiJadwalController::class, 'store'])->name('kontributor.jadwal.store');
        Route::get('/kontributor/saya/jadwal/{id}/edit', [KontribusiJadwalController::class, 'edit'])->name('kontributor.jadwal.edit');
        Route::put('/kontributor/saya/jadwal/{id}', [KontribusiJadwalController::class, 'update'])->name('kontributor.jadwal.update');

        Route::get('/kontributor/saya/acara/create', [KontribusiAcaraController::class, 'create'])->name('kontributor.acara.create');
        Route::post('/kontributor/saya/acara', [KontribusiAcaraController::class, 'store'])->name('kontributor.acara.store');
        Route::get('/kontributor/saya/acara/{id}/edit', [KontribusiAcaraController::class, 'edit'])->name('kontributor.acara.edit');
        Route::put('/kontributor/saya/acara/{id}', [KontribusiAcaraController::class, 'update'])->name('kontributor.acara.update');

        Route::get('/kontributor/saya/amalan/create', [KontribusiAmalanController::class, 'create'])->name('kontributor.amalan.create');
        Route::post('/kontributor/saya/amalan', [KontribusiAmalanController::class, 'store'])->name('kontributor.amalan.store');
        Route::get('/kontributor/saya/amalan/{id}/edit', [KontribusiAmalanController::class, 'edit'])->name('kontributor.amalan.edit');
        Route::put('/kontributor/saya/amalan/{id}', [KontribusiAmalanController::class, 'update'])->name('kontributor.amalan.update');
    });
});

Route::middleware(['auth:sanctum', 'verified', 'is_admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth:sanctum', 'verified'])->name('dashboard');
    Route::resource('/majelis', MajelisController::class);
    Route::resource('/jadwal-majelis', JadwalMajelisController::class);

    // Schedule Notes Admin Moderation
    Route::get('/schedule-notes', [\App\Http\Controllers\ScheduleNoteController::class, 'index'])->name('schedule-notes.index');
    Route::patch('/schedule-notes/{id}/approve', [\App\Http\Controllers\ScheduleNoteController::class, 'approve'])->name('schedule-notes.approve');
    Route::patch('/schedule-notes/{id}/reject', [\App\Http\Controllers\ScheduleNoteController::class, 'reject'])->name('schedule-notes.reject');
    Route::delete('/guru/{id}/foto-bersama', [GuruController::class, 'destroyFotoBersama'])->name('admin.guru.foto-bersama.destroy');
    Route::resource('/guru', GuruController::class);
    Route::resource('/event', \App\Http\Controllers\EventController::class);
    Route::resource('/video', VideoController::class);
    Route::resource('/wirid', WiridController::class);
    Route::resource('/doa', DoaController::class);
    Route::resource('/libraries', \App\Http\Controllers\LibraryController::class);
    Route::resource('/ramadhan-schedules', RamadhanController::class);
    Route::resource('/roles', \App\Http\Controllers\RoleController::class);
    Route::resource('/permissions', \App\Http\Controllers\PermissionController::class);
    Route::resource('/posts', \App\Http\Controllers\PostController::class);
    Route::resource('/foundations', \App\Http\Controllers\FoundationController::class);
    // XP Settings & Moderasi Kontributor
    Route::get('/pengaturan/xp-kontribusi', [XpSettingController::class, 'index'])->name('admin.xp-settings.index');
    Route::put('/pengaturan/xp-kontribusi', [XpSettingController::class, 'update'])->name('admin.xp-settings.update');

    Route::get('/pengaturan/reward', [RewardSettingController::class, 'index'])->name('admin.reward-settings.index');
    Route::put('/pengaturan/reward', [RewardSettingController::class, 'update'])->name('admin.reward-settings.update');

    Route::get('/reward-klaim', [AdminRewardClaimController::class, 'index'])->name('admin.reward-klaim.index');
    Route::put('/reward-klaim/{claim}/paid', [AdminRewardClaimController::class, 'markPaid'])->name('admin.reward-klaim.paid');
    Route::put('/reward-klaim/{claim}/reject', [AdminRewardClaimController::class, 'reject'])->name('admin.reward-klaim.reject');

    // Verifikasi pembelian pustaka berbayar
    Route::get('/library-purchases', [\App\Http\Controllers\Admin\LibraryPurchaseController::class, 'index'])->name('admin.library-purchases.index');
    Route::put('/library-purchases/{purchase}/activate', [\App\Http\Controllers\Admin\LibraryPurchaseController::class, 'activate'])->name('admin.library-purchases.activate');
    Route::put('/library-purchases/{purchase}/reject', [\App\Http\Controllers\Admin\LibraryPurchaseController::class, 'reject'])->name('admin.library-purchases.reject');

    Route::put('/majelis/{id}/moderasi', [ModerasiController::class, 'moderasiAssembly'])->name('admin.moderasi.majelis');
    Route::put('/majelis/{id}/revoke', [ModerasiController::class, 'revokeAssembly'])->name('admin.revoke.majelis');
    Route::put('/guru/{id}/moderasi', [ModerasiController::class, 'moderasiTeacher'])->name('admin.moderasi.guru');
    Route::put('/guru/{id}/revoke', [ModerasiController::class, 'revokeTeacher'])->name('admin.revoke.guru');
    Route::put('/jadwal-majelis/{id}/moderasi', [ModerasiController::class, 'moderasiJadwal'])->name('admin.moderasi.jadwal');
    Route::put('/jadwal-majelis/{id}/revoke', [ModerasiController::class, 'revokeJadwal'])->name('admin.revoke.jadwal');
    Route::put('/event/{id}/moderasi', [ModerasiController::class, 'moderasiEvent'])->name('admin.moderasi.event');
    Route::put('/event/{id}/revoke', [ModerasiController::class, 'revokeEvent'])->name('admin.revoke.event');
    Route::put('/wirid/{id}/moderasi', [ModerasiController::class, 'moderasiWirid'])->name('admin.moderasi.wirid');
    Route::put('/wirid/{id}/revoke', [ModerasiController::class, 'revokeWirid'])->name('admin.revoke.wirid');

    // Route for the getting the data feed
    Route::get('/json-data-feed', [DataFeedController::class, 'getDataFeed'])->name('json_data_feed');
    Route::get('/dashboard/analytics', [DashboardController::class, 'analytics'])->name('analytics');
    Route::get('/dashboard/fintech', [DashboardController::class, 'fintech'])->name('fintech');
    Route::get('/ecommerce/customers', [CustomerController::class, 'index'])->name('customers');
    Route::get('/ecommerce/orders', [OrderController::class, 'index'])->name('orders');
    Route::get('/ecommerce/invoices', [InvoiceController::class, 'index'])->name('invoices');
    Route::get('/ecommerce/shop', function () {
        return view('pages/ecommerce/shop');
    })->name('shop');
    Route::get('/ecommerce/shop-2', function () {
        return view('pages/ecommerce/shop-2');
    })->name('shop-2');
    Route::get('/ecommerce/product', function () {
        return view('pages/ecommerce/product');
    })->name('product');
    Route::get('/ecommerce/cart', function () {
        return view('pages/ecommerce/cart');
    })->name('cart');
    Route::get('/ecommerce/cart-2', function () {
        return view('pages/ecommerce/cart-2');
    })->name('cart-2');
    Route::get('/ecommerce/cart-3', function () {
        return view('pages/ecommerce/cart-3');
    })->name('cart-3');
    Route::get('/ecommerce/pay', function () {
        return view('pages/ecommerce/pay');
    })->name('pay');
    Route::get('/campaigns', [CampaignController::class, 'index'])->name('campaigns');
    Route::get('/community/users-tabs', [MemberController::class, 'indexTabs'])->name('users-tabs');
    Route::get('/community/users-tiles', [MemberController::class, 'indexTiles'])->name('users-tiles');
    Route::get('/community/profile', function () {
        return view('pages/community/profile');
    })->name('profile');
    Route::get('/community/feed', function () {
        return view('pages/community/feed');
    })->name('feed');
    Route::get('/community/forum', function () {
        return view('pages/community/forum');
    })->name('forum');
    Route::get('/community/forum-post', function () {
        return view('pages/community/forum-post');
    })->name('forum-post');
    Route::get('/community/meetups', function () {
        return view('pages/community/meetups');
    })->name('meetups');
    Route::get('/community/meetups-post', function () {
        return view('pages/community/meetups-post');
    })->name('meetups-post');
    Route::get('/finance/cards', function () {
        return view('pages/finance/credit-cards');
    })->name('credit-cards');
    Route::get('/finance/transactions', [TransactionController::class, 'index01'])->name('transactions');
    Route::get('/finance/transaction-details', [TransactionController::class, 'index02'])->name('transaction-details');
    Route::get('/job/job-listing', [JobController::class, 'index'])->name('job-listing');
    Route::get('/job/job-post', function () {
        return view('pages/job/job-post');
    })->name('job-post');
    Route::get('/job/company-profile', function () {
        return view('pages/job/company-profile');
    })->name('company-profile');
    Route::get('/messages', function () {
        return view('pages/messages');
    })->name('messages');
    Route::get('/tasks/kanban', function () {
        return view('pages/tasks/tasks-kanban');
    })->name('tasks-kanban');
    Route::get('/tasks/list', function () {
        return view('pages/tasks/tasks-list');
    })->name('tasks-list');
    Route::get('/inbox', function () {
        return view('pages/inbox');
    })->name('inbox');
    Route::get('/calendar', function () {
        return view('pages/calendar');
    })->name('calendar');
    Route::get('/settings/account', function () {
        return view('pages/settings/account');
    })->name('account');
    Route::get('/settings/notifications', function () {
        return view('pages/settings/notifications');
    })->name('notifications');
    Route::get('/settings/apps', function () {
        return view('pages/settings/apps');
    })->name('apps');
    Route::get('/settings/plans', function () {
        return view('pages/settings/plans');
    })->name('plans');
    Route::get('/settings/billing', function () {
        return view('pages/settings/billing');
    })->name('billing');
    Route::get('/settings/feedback', function () {
        return view('pages/settings/feedback');
    })->name('feedback');
    Route::get('/utility/changelog', function () {
        return view('pages/utility/changelog');
    })->name('changelog');
    Route::get('/utility/roadmap', function () {
        return view('pages/utility/roadmap');
    })->name('roadmap');
    Route::get('/utility/faqs', function () {
        return view('pages/utility/faqs');
    })->name('faqs');
    Route::get('/utility/empty-state', function () {
        return view('pages/utility/empty-state');
    })->name('empty-state');
    Route::get('/utility/404', function () {
        return view('pages/utility/404');
    })->name('404');
    Route::get('/onboarding-01', function () {
        return view('pages/onboarding-01');
    })->name('onboarding-01');
    Route::get('/onboarding-02', function () {
        return view('pages/onboarding-02');
    })->name('onboarding-02');
    Route::get('/onboarding-03', function () {
        return view('pages/onboarding-03');
    })->name('onboarding-03');
    Route::get('/onboarding-04', function () {
        return view('pages/onboarding-04');
    })->name('onboarding-04');
    Route::get('/component/button', function () {
        return view('pages/component/button-page');
    })->name('button-page');
    Route::get('/component/form', function () {
        return view('pages/component/form-page');
    })->name('form-page');
    Route::get('/component/dropdown', function () {
        return view('pages/component/dropdown-page');
    })->name('dropdown-page');
    Route::get('/component/alert', function () {
        return view('pages/component/alert-page');
    })->name('alert-page');
    Route::get('/component/modal', function () {
        return view('pages/component/modal-page');
    })->name('modal-page');
    Route::get('/component/pagination', function () {
        return view('pages/component/pagination-page');
    })->name('pagination-page');
    Route::get('/component/tabs', function () {
        return view('pages/component/tabs-page');
    })->name('tabs-page');
    Route::get('/component/breadcrumb', function () {
        return view('pages/component/breadcrumb-page');
    })->name('breadcrumb-page');
    Route::get('/component/badge', function () {
        return view('pages/component/badge-page');
    })->name('badge-page');
    Route::get('/component/avatar', function () {
        return view('pages/component/avatar-page');
    })->name('avatar-page');
    Route::get('/component/tooltip', function () {
        return view('pages/component/tooltip-page');
    })->name('tooltip-page');
    Route::get('/component/accordion', function () {
        return view('pages/component/accordion-page');
    })->name('accordion-page');
    Route::get('/component/icons', function () {
        return view('pages/component/icons-page');
    })->name('icons-page');
    Route::fallback(function () {
        return view('pages/utility/404');
    });
});
