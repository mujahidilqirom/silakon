<?php

namespace App\Http\Controllers\Konselor;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\JadwalKonseling;
use App\Models\KonselorPreference;
use Illuminate\Support\Str;
use App\Support\TelegramNotifier;
use App\Models\ActivityLog;

/**
 * SILAKON — Profil Saya (Konselor)
 * Simpan sebagai: app/Http/Controllers/Konselor/ProfilController.php
 *
 * Pola sama dengan KoordinatorController@profil dkk: data dummy
 * disimpan di session supaya perubahan kelihatan nyata selama demo,
 * padahal belum ada database. Nanti tinggal ganti tiap method privat
 * di bawah dengan query/Auth::user() sungguhan.
 */
class ProfilController extends Controller
{
    /** GET /konselor/profil */
    public function index(): View
    {
        $u = Auth::user();
        $profil = ['nama'=>$u->name, 'email'=>$u->email, 'no_hp'=>$u->phone ?? '', 'spesialisasi'=>'Konselor', 'bio'=>'', 'status'=>$u->is_active ? 'Aktif' : 'Nonaktif', 'kasus_aktif'=>JadwalKonseling::where('konselor_id',$u->id)->whereIn('status',['terjadwal','berlangsung'])->count(), 'inisial'=>collect(explode(' ', $u->name))->map(fn($p)=>mb_substr($p,0,1))->take(2)->implode('')];
        return view('konselor.profil', ['profil'=>$profil,'telegram'=>$this->telegramSemua(),'notifikasi'=>$this->notifikasiSemua(),'tanggalHariIni'=>now()->translatedFormat('l, d F Y')]);
        return view('konselor.profil', [
            'profil'         => $this->profilSemua(),
            'telegram'       => $this->telegramSemua(),
            'notifikasi'     => $this->notifikasiSemua(),
            'tanggalHariIni' => 'Rabu, 23 September 2026',
        ]);
    }

    /** PUT /konselor/profil — simpan perubahan Data Diri */
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama'         => ['required', 'string', 'max:150'],
            'email'        => ['required', 'email', 'max:150'],
            'spesialisasi' => ['required', 'string', 'max:100'],
            'no_hp'        => ['required', 'regex:/^\+?[0-9\-\s]{9,16}$/'],
            'bio'          => ['nullable', 'string', 'max:1000'],
        ], [
            'no_hp.regex' => 'Format nomor HP tidak valid (contoh: +62 812-3456-7890).',
        ]);

        $profil = $this->profilSemua();
        $u = Auth::user(); $u->update(['name'=>$data['nama'], 'email'=>$data['email'], 'phone'=>$data['no_hp']]);
        $data['inisial'] = collect(explode(' ', preg_replace('/[.,]/', '', $data['nama'])))
            ->filter(fn ($p) => !in_array(strtolower($p), ['dr', 'drs', 'dra', 'm', 'psi', 'pd', 'si', 'kons']))
            ->map(fn ($p) => mb_substr($p, 0, 1))
            ->take(2)->implode('');
        $data['status']      = $profil['status'];
        $data['kasus_aktif'] = $profil['kasus_aktif'];

        $this->profilSimpan($data);

        // TODO ganti dengan: Auth::user()->update([...]);

        return back()->with('sukses', 'Profil berhasil diperbarui.');
    }

    /** PUT /konselor/profil/password — ubah password */
    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'password_saat_ini'          => ['required', 'string'],
            'password_baru'              => ['required', 'string', 'min:8', 'regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[\W_]).+$/', 'confirmed'],
            'password_baru_confirmation' => ['required'],
        ], [
            'password_baru.regex'     => 'Password baru harus mengandung huruf, angka, dan simbol.',
            'password_baru.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        if (! Hash::check($data['password_saat_ini'], Auth::user()->password)) {
            return back()->withErrors(['password_saat_ini' => 'Password saat ini salah.'])->with('gagalPassword', true);
        }
        Auth::user()->update(['password' => Hash::make($data['password_baru'])]);
        return back()->with('sukses', 'Password berhasil diubah.');

        // TODO ganti dengan pengecekan sungguhan, mis. Hash::check($data['password_saat_ini'], Auth::user()->password)
        $passwordSaatIniDummy = session('konselor.profil_password_dummy', 'Password123!');
        if ($data['password_saat_ini'] !== $passwordSaatIniDummy) {
            return back()->withErrors(['password_saat_ini' => 'Password saat ini salah.'])->with('gagalPassword', true);
        }

        session(['konselor.profil_password_dummy' => $data['password_baru']]);

        // TODO ganti dengan: Auth::user()->update(['password' => Hash::make($data['password_baru'])]);

        return back()->with('sukses', 'Password berhasil diubah.');
    }

    /**
     * POST /konselor/profil/telegram/hubungkan
     *
     * Di dunia nyata: buat token unik, arahkan ke https://t.me/{bot}?start={token},
     * lalu tandai "terhubung" lewat webhook Telegram saat user mengetik /start.
     * Di sini disimulasikan langsung terhubung supaya bisa didemokan tanpa bot asli.
     */
    public function telegramHubungkan(): RedirectResponse
    {
        $token = Str::random(40);
        Auth::user()->update(['telegram_link_token' => $token]);
        ActivityLog::create(['user_id'=>Auth::id(),'action'=>'Hubungkan Telegram','detail'=>'Memulai proses pengaitan akun Telegram.','ip_address'=>request()->ip()]);
        $username = app(TelegramNotifier::class)->botUsername();
        if (! $username) return back()->withErrors(['telegram' => 'Bot Telegram belum dapat dihubungi.']);
        return redirect()->away('https://t.me/'.$username.'?start='.$token);
    }

    /** POST /konselor/profil/telegram/putus */
    public function telegramPutus(): RedirectResponse
    {
        Auth::user()->update(['telegram_chat_id' => null, 'telegram_link_token' => null, 'telegram_connected_at' => null]);
        ActivityLog::create(['user_id'=>Auth::id(),'action'=>'Putuskan Telegram','detail'=>'Memutuskan akun Telegram dari SILAKON.','ip_address'=>request()->ip()]);
        $telegram = $this->telegramSemua();
        $telegram['terhubung'] = false;
        $telegram['username_telegram'] = null;
        $this->telegramSimpan($telegram);

        // Kanal Telegram otomatis nonaktif lagi kalau akunnya diputus
        $notifikasi = collect($this->notifikasiSemua())
            ->map(fn ($n) => [...$n, 'telegram' => false])
            ->all();
        $this->notifikasiSimpan($notifikasi);

        return back()->with('sukses', 'Akun Telegram berhasil diputuskan.');
    }

    /**
     * POST /konselor/profil/notifikasi — toggle satu switch (AJAX).
     * Body: { jenis: 'Penugasan Baru', kanal: 'dashboard'|'telegram'|'email', aktif: bool }
     */
    public function notifikasiUpdate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'jenis' => ['required', 'string'],
            'kanal' => ['required', 'in:dashboard,telegram,email'],
            'aktif' => ['required', 'boolean'],
        ]);

        // Kanal Telegram tidak boleh diaktifkan kalau akun belum terhubung
        if ($data['kanal'] === 'telegram' && $data['aktif'] && !$this->telegramSemua()['terhubung']) {
            return response()->json([
                'sukses' => false,
                'pesan'  => 'Hubungkan akun Telegram terlebih dahulu.',
            ], 422);
        }

        $notifikasi = collect($this->notifikasiSemua())
            ->map(function ($n) use ($data) {
                if ($n['jenis'] === $data['jenis']) {
                    $n[$data['kanal']] = $data['aktif'];
                }
                return $n;
            })
            ->all();

        $this->notifikasiSimpan($notifikasi);

        // TODO ganti dengan: PreferensiNotifikasi::updateOrCreate([...]);

        return response()->json(['sukses' => true]);
    }

    /**
     * Sumber data dummy Profil.
     * TODO ganti: ambil dari Auth::user() / relasi profil konselor.
     */
    private function profilSemua(): array
    {
        if (!session()->has('konselor.profil_dummy')) {
            session(['konselor.profil_dummy' => [
                'nama'         => 'Dr. Sari Dewi, M.Psi',
                'email'        => 'sari.dewi@lppp.unnes.ac.id',
                'spesialisasi' => 'Kesehatan Mental',
                'no_hp'        => '+62 812-3456-7890',
                'bio'          => 'Psikolog klinis bersertifikat dengan pengalaman 12 tahun dalam bidang kesehatan mental remaja dan dewasa muda. Spesialisasi dalam kecemasan, depresi, dan stres akademik.',
                'inisial'      => 'S',
                'status'       => 'Aktif',
                'kasus_aktif'  => 20,
            ]]);
        }

        return session('konselor.profil_dummy');
    }

    private function profilSimpan(array $data): void
    {
        session(['konselor.profil_dummy' => $data]);
    }

    private function telegramSemua(): array
    {
        $u = Auth::user();
        return ['terhubung'=>(bool)$u->telegram_chat_id,'bot_nama'=>'SILAKON Notification Bot','bot_username'=>app(TelegramNotifier::class)->botUsername(),'username_telegram'=>$u->telegram_chat_id ? 'Terhubung' : null];
    }

    private function telegramSimpan(array $data): void
    {
        $p=KonselorPreference::firstOrCreate(['user_id'=>Auth::id()],['notifikasi'=>$this->notifikasiDefault()]);
        $p->update(['telegram_terhubung'=>(bool)$data['terhubung'],'telegram_username'=>$data['username_telegram']]);
    }

    /** TODO ganti: ambil dari tabel preferensi notifikasi milik konselor. */
    private function notifikasiSemua(): array
    {
        $p=KonselorPreference::firstOrCreate(['user_id'=>Auth::id()],['notifikasi'=>$this->notifikasiDefault()]);
        return $p->notifikasi ?: $this->notifikasiDefault();
    }

    private function notifikasiSimpan(array $data): void
    {
        $p=KonselorPreference::firstOrCreate(['user_id'=>Auth::id()]); $p->update(['notifikasi'=>$data]);
    }

    private function notifikasiDefault(): array
    {
        return [['jenis'=>'Penugasan Baru','dashboard'=>true,'telegram'=>false,'email'=>true],['jenis'=>'Pengingat Besok','dashboard'=>true,'telegram'=>false,'email'=>true],['jenis'=>'Jadwal Berubah','dashboard'=>true,'telegram'=>false,'email'=>true],['jenis'=>'Pesan Admin','dashboard'=>true,'telegram'=>false,'email'=>true]];
    }
}
