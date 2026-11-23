<?php

use App\Http\Controllers\CekStatusController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminPendaftaranController;
use App\Http\Controllers\KonselingKelompokController;
use App\Http\Controllers\Konselor\DashboardController;
use App\Http\Controllers\KoordinatorController;
use App\Http\Controllers\AdminDataMasterController;
use App\Http\Controllers\Konselor\JadwalController;
use App\Http\Controllers\Konselor\DetailSesiController;
use App\Http\Controllers\Konselor\KasusController;
use App\Http\Controllers\Konselor\CatatanKonselingController;
use App\Http\Controllers\Konselor\ProfilController;
use App\Http\Controllers\SurveiController;
use App\Http\Controllers\AdminSurveiController;
use App\Http\Controllers\KoordinatorSurveiController;
use App\Http\Controllers\Konselor\SurveiController as KonselorSurveiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\TelegramWebhookController;

Route::get('/', function () {
    return view('landing');
})->name('landing');

Route::middleware('auth')->group(function () {
    Route::get('/telegram/hubungkan', [TelegramController::class, 'link'])->name('telegram.link');
    Route::post('/telegram/putus', [TelegramController::class, 'unlink'])->name('telegram.unlink');
});

Route::post('/telegram/webhook', TelegramWebhookController::class)
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->name('telegram.webhook');

Route::get('/tentang', function () {
    return view('tentang');
})->name('tentang');

Route::get('/layanan', function () {
    return view('layanan');
})->name('layanan');

Route::get('/panduan', function () {
    return view('panduan');
})->name('panduan');

Route::prefix('konseling-kelompok')
    ->name('konseling-kelompok.')
    ->controller(KonselingKelompokController::class)
    ->where(['id' => '[0-9]+'])
    ->group(function () {
        // GET  /konseling-kelompok                 -> daftar program (blade lama)
        Route::get('/', 'index')->name('index');
 
        // GET  /konseling-kelompok/{id}/daftar     -> formulir data pendaftar
        Route::get('/{id}/daftar', 'create')->name('daftar');
 
        // POST /konseling-kelompok/{id}/daftar     -> halaman konfirmasi
        Route::post('/{id}/daftar', 'konfirmasi')->name('konfirmasi');
 
        // POST /konseling-kelompok/{id}/kirim      -> proses kirim (dummy)
        Route::post('/{id}/kirim', 'store')->name('kirim');
 
        // GET  /konseling-kelompok/{id}/berhasil   -> halaman sukses
        Route::get('/{id}/berhasil', 'berhasil')->name('berhasil');
    });

Route::get('/self-help', [App\Http\Controllers\SelfHelpController::class, 'index'])->name('self-help');
Route::get('/self-help/{id}', [App\Http\Controllers\SelfHelpController::class, 'show'])->name('self-help.detail');
Route::post('/self-help/{id}/pengerjaan', [App\Http\Controllers\SelfHelpController::class, 'submit'])->whereNumber('id')->name('self-help.submit');

Route::get('/survei/{token}',  [SurveiController::class, 'tampil'])->name('survei.tampil');
Route::post('/survei/{token}', [SurveiController::class, 'kirim'])->middleware('throttle:public-submit')->name('survei.kirim');
 
// --- Admin: masukkan ke group admin yang sudah ada (middleware auth dst) ---
Route::get('/admin/survei', [AdminSurveiController::class, 'index'])->middleware(['auth', 'role:admin'])->name('admin.survei');
Route::post('/admin/survei/{id}/generate', [AdminSurveiController::class, 'generate'])->middleware(['auth', 'role:admin'])->name('admin.survei.generate');

Route::get('/ajukan-konseling', function () {
    $katalog = collect();
    if (\Illuminate\Support\Facades\Schema::hasTable('master_fakultas')) {
        $katalog = \App\Models\MasterFakultas::with('prodi')->where('aktif', true)->orderBy('nama')->get()->mapWithKeys(fn ($f) => [$f->nama => $f->prodi->where('aktif', true)->groupBy('jenjang')->map->pluck('nama')->all()])->all();
    }
    return view('konseling.create', compact('katalog'));
})->name('konseling.create');

Route::view('/login', 'auth.login')->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.store');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::post('/konseling/kirim-kode', [PengajuanController::class, 'kirimKode'])->middleware('throttle:otp')->name('konseling.kirim-kode');
Route::post('/konseling', [PengajuanController::class, 'store'])->middleware('throttle:public-submit')->name('konseling.store');
Route::get('/konseling/wali/verifikasi/{token}', [PengajuanController::class, 'waliVerifikasi'])->name('konseling.wali.verifikasi');
Route::post('/konseling/wali/verifikasi/{token}', [PengajuanController::class, 'waliSelesaikan'])->middleware('throttle:public-submit')->name('konseling.wali.selesaikan');

Route::get('/cek-status', [CekStatusController::class, 'index'])->name('cek-status.index');
Route::post('/cek-status', [CekStatusController::class, 'cari'])->middleware('throttle:status-check')->name('cek-status.cari');
Route::get('/cek-status/{kode}', [CekStatusController::class, 'hasil'])->name('cek-status.hasil');

Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {

    Route::get('/dashboard', [AdminDashboardController::class, 'index'])
        ->name('admin.dashboard');

    Route::get('/pendaftaran', [AdminPendaftaranController::class, 'index'])
        ->name('admin.pendaftaran');
    Route::get('/pendaftaran/ekspor', [AdminPendaftaranController::class, 'eksporPendaftaran'])->name('admin.pendaftaran.ekspor');

    Route::get('/penjadwalan', [AdminPendaftaranController::class, 'penjadwalan'])
        ->name('admin.penjadwalan');
    Route::post('/penjadwalan/ketersediaan', [AdminPendaftaranController::class, 'simpanKetersediaan'])
        ->name('admin.penjadwalan.ketersediaan.store');
    Route::put('/penjadwalan/ketersediaan/{id}', [AdminPendaftaranController::class, 'perbaruiKetersediaan'])
        ->name('admin.penjadwalan.ketersediaan.update');
    Route::delete('/penjadwalan/ketersediaan/{id}', [AdminPendaftaranController::class, 'hapusKetersediaan'])
        ->name('admin.penjadwalan.ketersediaan.delete');
    Route::post('/penjadwalan/jadwal', [AdminPendaftaranController::class, 'simpanJadwal'])
        ->name('admin.penjadwalan.jadwal.store');
    Route::post('/penjadwalan/jadwal/{id}/batal', [AdminPendaftaranController::class, 'batalkanJadwal'])
        ->name('admin.penjadwalan.jadwal.batal');
    Route::post('/penjadwalan/jadwal/{id}/tidak-hadir', [AdminPendaftaranController::class, 'tandaiTidakHadir'])
        ->name('admin.penjadwalan.jadwal.tidak-hadir');

    Route::get('/kalender', [AdminPendaftaranController::class, 'kalender'])    
        ->name('admin.kalender');

    Route::get('/kasus-mendesak', [AdminPendaftaranController::class, 'kasusMendesak'])    
        ->name('admin.kasus-mendesak');
    Route::post('/kasus-mendesak/{kode}/tangani', [AdminPendaftaranController::class, 'tandaiKasusMendesak'])
        ->name('admin.kasus-mendesak.tangani');

    Route::get('/semua-tiket', [AdminPendaftaranController::class, 'semuaTiket'])    
        ->name('admin.semua-tiket');
    Route::get('/semua-tiket/ekspor', [AdminPendaftaranController::class, 'eksporSemuaTiket'])
        ->name('admin.semua-tiket.ekspor');
    
    Route::get('/data-konseli', [AdminPendaftaranController::class, 'dataKonseli'])    
        ->name('admin.data-konseli');
    Route::get('/data-konseli/ekspor', [AdminPendaftaranController::class, 'eksporDataKonseli'])
        ->name('admin.data-konseli.ekspor');
    Route::put('/data-konseli/{id}', [AdminPendaftaranController::class, 'perbaruiKonseli'])
        ->whereNumber('id')->name('admin.data-konseli.update');
    
    Route::get('/konseling-kelompok', [AdminPendaftaranController::class, 'konselingKelompok'])    
        ->name('admin.konseling-kelompok');

    Route::post('/konseling-kelompok', [AdminPendaftaranController::class, 'simpanKonselingKelompok'])
    ->name('admin.konseling-kelompok.store');
    Route::put('/konseling-kelompok/{id}', [AdminPendaftaranController::class, 'ubahKonselingKelompok'])->whereNumber('id')->name('admin.konseling-kelompok.update');
    Route::delete('/konseling-kelompok/{id}', [AdminPendaftaranController::class, 'hapusKonselingKelompok'])->whereNumber('id')->name('admin.konseling-kelompok.destroy');

    Route::get('/konseling-kelompok/{id}/peserta', [AdminPendaftaranController::class, 'pesertaKonselingKelompok'])
    ->name('admin.konseling-kelompok.peserta');

    Route::get('/self-help',  [AdminPendaftaranController::class, 'selfHelp'])->name('admin.self-help');
    
    Route::post('/self-help', [AdminPendaftaranController::class, 'simpanSelfHelp'])->name('admin.self-help.store');
    Route::put('/self-help/{id}', [AdminPendaftaranController::class, 'updateSelfHelp'])->whereNumber('id')->name('admin.self-help.update');
    Route::delete('/self-help/{id}', [AdminPendaftaranController::class, 'hapusSelfHelp'])->whereNumber('id')->name('admin.self-help.destroy');

    Route::get('/data-master', [AdminDataMasterController::class, 'index'])->name('admin.data-master');
    Route::post('/data-master/fakultas', [AdminDataMasterController::class, 'fakultasStore'])->name('admin.data-master.fakultas.store');
    Route::delete('/data-master/fakultas/{id}', [AdminDataMasterController::class, 'fakultasDestroy'])->whereNumber('id')->name('admin.data-master.fakultas.destroy');
    Route::post('/data-master/prodi', [AdminDataMasterController::class, 'prodiStore'])->name('admin.data-master.prodi.store');
    Route::put('/data-master/prodi/{id}', [AdminDataMasterController::class, 'prodiUpdate'])->whereNumber('id')->name('admin.data-master.prodi.update');
    Route::delete('/data-master/prodi/{id}', [AdminDataMasterController::class, 'prodiDestroy'])->whereNumber('id')->name('admin.data-master.prodi.destroy');
    Route::post('/data-master/kategori', [AdminDataMasterController::class, 'kategoriStore'])->name('admin.data-master.kategori.store');
    Route::post('/data-master/kategori/{id}/toggle', [AdminDataMasterController::class, 'kategoriToggle'])->whereNumber('id')->name('admin.data-master.kategori.toggle');
    Route::delete('/data-master/kategori/{id}', [AdminDataMasterController::class, 'kategoriDestroy'])->whereNumber('id')->name('admin.data-master.kategori.destroy');
    Route::post('/data-master/lokasi', [AdminDataMasterController::class, 'lokasiStore'])->name('admin.data-master.lokasi.store');
    Route::put('/data-master/lokasi/{id}', [AdminDataMasterController::class, 'lokasiUpdate'])->whereNumber('id')->name('admin.data-master.lokasi.update');
    Route::post('/data-master/lokasi/{id}/toggle', [AdminDataMasterController::class, 'lokasiToggle'])->whereNumber('id')->name('admin.data-master.lokasi.toggle');
    Route::delete('/data-master/lokasi/{id}', [AdminDataMasterController::class, 'lokasiDestroy'])->whereNumber('id')->name('admin.data-master.lokasi.destroy');
    Route::put('/data-master/template/{kode}', [AdminDataMasterController::class, 'templateUpdate'])->name('admin.data-master.template.update');

    Route::get('/profil', [AdminDashboardController::class, 'profil'])->name('admin.profil');
    Route::put('/profil', [AdminDashboardController::class, 'profilUpdate'])->name('admin.profil.update');
    Route::put('/profil/password', [AdminDashboardController::class, 'profilPassword'])->name('admin.profil.password');
    Route::post('/profil/preferensi', [AdminDashboardController::class, 'profilPreferensi'])->name('admin.profil.preferensi');
    Route::post('/profil/jam-operasional', [AdminDashboardController::class, 'profilJam'])->name('admin.profil.jam');
});

Route::get('/admin/pendaftaran/{kode}', [AdminPendaftaranController::class, 'show'])->middleware(['auth', 'role:admin'])->name('admin.pendaftaran.show');
Route::post('/admin/pendaftaran/{kode}/verifikasi', [AdminPendaftaranController::class, 'verifikasi'])->middleware(['auth', 'role:admin'])->name('admin.pendaftaran.verifikasi');

Route::prefix('konselor')->name('konselor.')->middleware(['auth', 'role:konselor'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
Route::get('/konselor/survei', [KonselorSurveiController::class, 'index'])->middleware(['auth', 'role:konselor'])->name('konselor.survei');
Route::get('/konselor/jadwal', [JadwalController::class, 'index'])->middleware(['auth', 'role:konselor'])->name('konselor.jadwal');

Route::get('/konselor/detail-sesi/{kode}', [DetailSesiController::class, 'show'])->middleware(['auth', 'role:konselor'])->name('konselor.detail-sesi.show');
Route::get('/konselor/kasus', [KasusController::class, 'index'])->middleware(['auth', 'role:konselor'])->name('konselor.kasus.index');
Route::post('/konselor/kasus/{kode}/catatan', [KasusController::class, 'simpanCatatan'])->middleware(['auth', 'role:konselor'])->name('konselor.kasus.catatan.simpan');
Route::post('/konselor/detail-sesi/{kode}/catatan', [KasusController::class, 'simpanCatatan'])->middleware(['auth', 'role:konselor'])->name('konselor.detail-sesi.catatan');
Route::post('/konselor/detail-sesi/{kode}/darurat', [KasusController::class, 'tandaiDarurat'])->middleware(['auth', 'role:konselor'])->name('konselor.detail-sesi.darurat');
Route::post('/konselor/kasus/{kode}/survey', [KasusController::class, 'kirimSurvey'])->middleware(['auth', 'role:konselor'])->name('konselor.kasus.survey.kirim');
Route::get('/konselor/catatan-konseling', [CatatanKonselingController::class, 'index'])
            ->middleware(['auth', 'role:konselor'])
            ->name('konselor.catatan-konseling');

Route::get('/konselor/profil', [ProfilController::class, 'index'])->middleware(['auth', 'role:konselor'])->name('konselor.profil');
Route::put('/konselor/profil', [ProfilController::class, 'update'])->middleware(['auth', 'role:konselor'])->name('konselor.profil.update');
Route::put('/konselor/profil/password', [ProfilController::class, 'updatePassword'])->middleware(['auth', 'role:konselor'])->name('konselor.profil.password');
Route::post('/konselor/profil/telegram/hubungkan', [ProfilController::class, 'telegramHubungkan'])->middleware(['auth', 'role:konselor'])->name('konselor.profil.telegram.hubungkan');
Route::post('/konselor/profil/telegram/putus', [ProfilController::class, 'telegramPutus'])->middleware(['auth', 'role:konselor'])->name('konselor.profil.telegram.putus');
Route::post('/konselor/profil/notifikasi', [ProfilController::class, 'notifikasiUpdate'])->middleware(['auth', 'role:konselor'])->name('konselor.profil.notifikasi');

Route::prefix('koordinator')
    ->name('koordinator.')
    ->controller(KoordinatorController::class)
    ->middleware(['auth', 'role:koordinator'])
    ->group(function () {
        Route::get('/',                    'dashboard')->name('dashboard');
         Route::get('/dashboard/ekspor',    'eksporDashboard')->name('dashboard.ekspor');
    });
Route::get('/koordinator/survei',        [KoordinatorSurveiController::class, 'index'])->middleware(['auth', 'role:koordinator'])->name('koordinator.survei');
Route::get('/koordinator/survei/ekspor', [KoordinatorSurveiController::class, 'ekspor'])->middleware(['auth', 'role:koordinator'])->name('koordinator.survei.ekspor');
Route::get('/koordinator/survei/cetak',  [KoordinatorSurveiController::class, 'cetak'])->middleware(['auth', 'role:koordinator'])->name('koordinator.survei.cetak');Route::prefix('koordinator')->middleware(['auth', 'role:koordinator'])->group(function () {
    Route::get('/kasus', [KoordinatorController::class, 'kasus'])    
    ->name('koordinator.kasus');
    Route::post('/kasus/{kode}/tangani', [KoordinatorController::class, 'tandaiKasusMendesak'])
    ->name('koordinator.kasus.tangani');

    Route::get('/catatan-konseling', [KoordinatorController::class, 'catatan'])    
    ->name('koordinator.catatan-konseling');
    
    Route::get('/konseling-kelompok', [KoordinatorController::class, 'kelompok'])->name('kelompok');
        Route::post('/konseling-kelompok', [KoordinatorController::class, 'simpanKelompok'])->name('kelompok.store');
        Route::put('/konseling-kelompok/{id}', [KoordinatorController::class, 'ubahKelompok'])->whereNumber('id')->name('kelompok.update');
        Route::delete('/konseling-kelompok/{id}', [KoordinatorController::class, 'hapusKelompok'])->whereNumber('id')->name('kelompok.destroy');
        Route::get('/konseling-kelompok/{id}/peserta', [KoordinatorController::class, 'pesertaKelompok'])
            ->whereNumber('id')
            ->name('kelompok.peserta');

    Route::get('/self-help', [KoordinatorController::class, 'selfHelp'])->name('koordinator.self-help');

    Route::post  ('/self-help',         [KoordinatorController::class, 'simpanMateriSelfHelp'])->name('koordinator.self-help.store');
    Route::put   ('/self-help/{id}',    [KoordinatorController::class, 'updateMateriSelfHelp'])->name('koordinator.self-help.update');
    Route::delete('/self-help/{id}',    [KoordinatorController::class, 'hapusMateriSelfHelp'])->name('koordinator.self-help.destroy');

    Route::get('/laporan',        [KoordinatorController::class, 'laporan'])
    ->name('koordinator.laporan');
    Route::get('/laporan/ekspor', [KoordinatorController::class, 'eksporLaporan'])
    ->name('koordinator.laporan.ekspor');
    Route::get('/laporan/ekspor-konselor', [KoordinatorController::class, 'eksporKonselor'])
    ->name('koordinator.laporan.ekspor-konselor');

    Route::get('/manajemen-pengguna', [KoordinatorController::class, 'pengguna'])->name('pengguna');
    Route::post('/manajemen-pengguna', [KoordinatorController::class, 'penggunaStore'])->name('pengguna.store');
    Route::put('/manajemen-pengguna/{id}', [KoordinatorController::class, 'penggunaUpdate'])->name('pengguna.update');
    Route::post('/manajemen-pengguna/{id}/reset', [KoordinatorController::class, 'penggunaReset'])->name('pengguna.reset');
    Route::post('/manajemen-pengguna/{id}/toggle', [KoordinatorController::class, 'penggunaToggle'])->name('pengguna.toggle');
    Route::delete('/manajemen-pengguna/{id}', [KoordinatorController::class, 'penggunaDestroy'])->whereNumber('id')->name('pengguna.destroy');
});

Route::get('/koordinator/pengaturan', [KoordinatorController::class, 'pengaturan'])->middleware(['auth', 'role:koordinator'])->name('koordinator.pengaturan');
Route::put('/koordinator/pengaturan', [KoordinatorController::class, 'pengaturanUpdate'])->middleware(['auth', 'role:koordinator'])->name('koordinator.pengaturan.update');
Route::post('/koordinator/pengaturan', [KoordinatorController::class, 'pengaturanUpdate'])->middleware(['auth', 'role:koordinator'])->name('koordinator.pengaturan.save');

Route::get('/koordinator/log-aktivitas', [KoordinatorController::class, 'log'])->middleware(['auth', 'role:koordinator'])->name('koordinator.log');
Route::get('/koordinator/log-aktivitas/ekspor', [KoordinatorController::class, 'logEkspor'])->middleware(['auth', 'role:koordinator'])->name('koordinator.log.ekspor');

Route::get('/koordinator/profil', [KoordinatorController::class, 'profil'])->middleware(['auth', 'role:koordinator'])->name('koordinator.profil');
Route::put('/koordinator/profil', [KoordinatorController::class, 'profilUpdate'])->middleware(['auth', 'role:koordinator'])->name('koordinator.profil.update');
Route::put('/koordinator/profil/password', [KoordinatorController::class, 'profilUbahPassword'])->middleware(['auth', 'role:koordinator'])->name('koordinator.profil.password');
