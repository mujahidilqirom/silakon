<?php

namespace App\Http\Controllers;

use App\Models\KodeVerifikasiEmail;
use App\Models\Konseli;
use App\Models\Pengajuan;
use App\Models\VerifikasiWaliPengajuan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use App\Support\TelegramNotifier;
use App\Services\PrioritasKonselingService;
use App\Support\InteractiveMail;
use Illuminate\Support\Str;

class PengajuanController extends Controller
{
    public function kirimKode(Request $request): JsonResponse
    {
        // Form mahasiswa menampilkan kolom sebagai alamat lokal (tanpa domain).
        // Normalisasi di server agar pengiriman OTP selalu menggunakan alamat
        // email yang valid, termasuk ketika JavaScript tidak berjalan.
        $email = $this->normalizeEmail((string) $request->input('email'), 'mahasiswa');
        validator(['email' => $email], ['email' => ['required', 'email']])->validate();

        $existing = KodeVerifikasiEmail::where('email', $email)->latest()->first();
        if ($existing && $existing->dikirim_terakhir_at?->gt(now()->subSeconds(60))) {
            return response()->json(['message' => 'Tunggu satu menit sebelum meminta kode baru.'], 429);
        }

        $code = (string) random_int(100000, 999999);
        $otp = KodeVerifikasiEmail::create([
            'email' => $email,
            'kode_hash' => Hash::make($code),
            'percobaan' => 0,
            'jumlah_kirim' => ($existing?->jumlah_kirim ?? 0) + 1,
            'berlaku_sampai' => now()->addMinutes(10),
            'dikirim_terakhir_at' => now(),
        ]);

        InteractiveMail::send($email, 'Kode Verifikasi Pengajuan Konseling', [
            'greeting' => 'Kode verifikasi Anda',
            'message' => "Masukkan kode berikut pada halaman pengajuan. Kode ini berlaku selama 10 menit dan hanya dapat digunakan untuk satu proses verifikasi.",
            'status' => 'Verifikasi email',
            'details' => ['Kode verifikasi' => $code, 'Berlaku sampai' => now()->addMinutes(10)->translatedFormat('d F Y, H:i').' WIB'],
            'quote' => 'Terima kasih sudah memberi ruang bagi diri sendiri untuk mencari bantuan.',
        ]);

        return response()->json(['message' => 'Kode verifikasi telah dikirim.', 'expires_in' => 600]);
    }

    public function store(Request $request): RedirectResponse
    {
        $type = (string) $request->input('jenis_konseli');
        $emailField = match ($type) {
            'mahasiswa' => 'mahasiswa_email',
            'alumni' => 'alumni_email',
            'tenaga_kependidikan' => 'tk_email',
            'wali_mahasiswa' => 'wali_email',
            default => null,
        };

        if (! $emailField) {
            throw ValidationException::withMessages(['jenis_konseli' => 'Jenis konseli tidak valid.']);
        }

        // Simpan alamat lengkap ke OTP, konseli, dan pengajuan. Nilai yang
        // terlihat di form mahasiswa boleh hanya berupa bagian sebelum @.
        $email = $this->normalizeEmail((string) $request->input($emailField), $type);
        $request->merge([$emailField => $email]);
        $this->validateCommon($request, $type, $emailField);

        $waliMahasiswa = $type === 'wali_mahasiswa' && $request->input('wali_subjek_konseling') === 'mahasiswa';
        $targetEmail = $waliMahasiswa
            ? $this->normalizeEmail((string) $request->input('wali_mahasiswa_email'), 'mahasiswa')
            : $email;
        if ($waliMahasiswa) {
            $email = $targetEmail;
            $request->merge(['wali_mahasiswa_email' => $targetEmail]);
        }

        $nim = $type === 'mahasiswa'
            ? trim((string) $request->input('mahasiswa_nim'))
            : ($type === 'alumni' ? trim((string) $request->input('alumni_nim')) : null);

        $active = Pengajuan::where(function ($query) use ($email, $nim) {
            $query->where('email', $email);
            if ($nim) $query->orWhere('nim', $nim);
        })->aktif()->latest()->first();

        if ($active) {
            return redirect()->route('cek-status.hasil', [
                'kode' => $active->kode_pengajuan,
                'email' => $active->email,
            ])->with('pengajuan_aktif', 'Anda masih memiliki pengajuan konseling yang aktif. Gunakan tiket ini untuk melihat statusnya.');
        }

        // Bila mahasiswa yang akan menjadi konseli, wali tidak mengisi WHO-5
        // dan tidak memegang kode verifikasi. Sistem mengirim tautan persetujuan
        // langsung ke email mahasiswa untuk mengisi WHO-5 sendiri.
        if ($waliMahasiswa) {
            $data = $request->except(['_token', 'verification_code', 'consent', 'who5']);
            $data['who5_jawaban'] = [];
            $data['wali_verifikasi_email'] = $targetEmail;
            $token = Str::random(64);
            VerifikasiWaliPengajuan::create([
                'token' => $token,
                'email_mahasiswa' => $targetEmail,
                'email_wali' => $request->input('wali_email'),
                'data' => $data,
                'berlaku_sampai' => now()->addHours(24),
            ]);
            $link = route('konseling.wali.verifikasi', $token);
            try {
                InteractiveMail::send($targetEmail, 'Persetujuan Pengajuan Konseling', [
                    'greeting' => 'Ada pengajuan konseling yang ditujukan kepada Anda',
                    'message' => 'Seseorang telah mengajukan layanan konseling terkait Anda. Silakan tinjau pengajuan ini, berikan persetujuan, lalu isi WHO-5 secara mandiri. Tautan berlaku selama 24 jam.',
                    'status' => 'Menunggu persetujuan', 'actionUrl' => $link, 'actionLabel' => 'Tinjau Pengajuan',
                    'quote' => 'Cerita Anda layak didengar dengan aman dan penuh hormat.',
                ]);
            } catch (\Throwable $e) { report($e); }
            return response()->view('konseling.wali-menunggu', ['email' => $targetEmail]);
        }

        $otp = KodeVerifikasiEmail::where('email', $email)->latest()->first();
        $code = (string) $request->input('verification_code');
        if (! $otp || $otp->berlaku_sampai->isPast()) {
            throw ValidationException::withMessages(['verification_code' => 'Kode verifikasi sudah kedaluwarsa.']);
        }
        if ($otp->percobaan >= 5) {
            throw ValidationException::withMessages(['verification_code' => 'Batas percobaan kode verifikasi telah tercapai.']);
        }
        $otp->increment('percobaan');
        if (! Hash::check($code, $otp->kode_hash)) {
            throw ValidationException::withMessages(['verification_code' => 'Kode verifikasi tidak sesuai.']);
        }
        $otp->update(['terverifikasi_at' => now()]);

        $who5Jawaban = array_values((array) $request->input('who5', []));
        $prioritasService = app(PrioritasKonselingService::class);
        $darurat = $request->input('kondisi_darurat') === 'ya';
        $prioritas = $prioritasService->hitung($who5Jawaban, now(), $darurat);
        $data = $request->except(['_token', 'verification_code', 'consent', 'who5']);
        $data['who5_jawaban'] = $who5Jawaban;
        // Field lama tetap disimpan sebagai kompatibilitas data/view, tetapi
        // nilainya sekarang berasal dari WHO-5, bukan pilihan manual konseli.

        [$name, $phone] = $this->identity($request, $type);
        // Utamakan email sebagai identitas utama. NIM dipakai sebagai fallback
        // ketika email berubah, sehingga pengajuan baru tetap masuk ke profil
        // konseli yang sama dan tidak membuat profil ganda.
        $konseli = Konseli::where('email', $email)->first();
        if (! $konseli && $nim) {
            $konseli = Konseli::where('nim', $nim)->first();
        }
        if (! $konseli) {
            $konseli = new Konseli();
        }

        $dataLama = (array) ($konseli->data ?? []);
        $riwayatLama = (array) ($dataLama['_riwayat_pengajuan'] ?? []);
        unset($dataLama['_riwayat_pengajuan']);
        if ($konseli->exists && $dataLama !== []) {
            $riwayatLama[] = [
                'tersimpan_pada' => now()->toIso8601String(),
                'nama' => $konseli->nama,
                'email' => $konseli->email,
                'nim' => $konseli->nim,
                'telepon' => $konseli->telepon,
                'data' => $dataLama,
            ];
        }

        // Profil ringkas mempertahankan nama pertama. Isian pendaftaran terbaru
        // tetap disimpan sebagai snapshot di pengajuan dan riwayat profil.
        $dataProfil = array_merge($dataLama, $data);
        $dataProfil['_riwayat_pengajuan'] = $riwayatLama;
        $konseli->fill([
            'jenis_konseli' => $type,
            'nama' => $konseli->nama ?: $name,
            'email' => $email,
            'nim' => $nim,
            'telepon' => $konseli->telepon ?: $phone,
            'data' => $dataProfil,
            'terakhir_mendaftar_at' => now(),
        ])->save();

        $pengajuan = DB::transaction(function () use ($konseli, $email, $nim, $data, $prioritas) {
            $pengajuan = Pengajuan::create([
                'konseli_id' => $konseli->id,
                'kode_pengajuan' => $this->generateCode(),
                'jenis_layanan' => 'individual',
                'status' => Pengajuan::STATUS_MENUNGGU_PEMERIKSAAN,
                'email' => $email,
                'nim' => $nim,
                'data' => $data,
                'who5_skor' => $prioritas['who5_skor'],
                'saw_nilai' => $prioritas['saw_nilai'],
                'prioritas' => $prioritas['prioritas'],
                'terverifikasi_at' => now(),
            ]);

            $pengajuan->riwayatStatus()->create([
                'status_baru' => Pengajuan::STATUS_MENUNGGU_PEMERIKSAAN,
                'keterangan' => 'Pengajuan berhasil dikirim dan menunggu pemeriksaan Admin.',
            ]);

            return $pengajuan;
        });

        if (in_array(strtolower((string) ($data['kondisi_darurat'] ?? '')), ['ya', 'mendesak', 'darurat', 'keadaan darurat'], true)) {
            app(TelegramNotifier::class)->roles(['admin', 'koordinator'],
                "🚨 Pengajuan mendesak baru di SILAKON\n\n".
                "Kode: {$pengajuan->kode_pengajuan}\n".
                "Konseli: ".($name ?: 'Konseli')."\n".
                "Segera periksa pengajuan pada dashboard Admin/Koordinator."
            );
        }

        try {
            InteractiveMail::send($email, 'Pengajuan Konseling Berhasil Dikirim', [
                'greeting' => 'Halo, '.($name ?: 'Konseli'),
                'message' => 'Terima kasih telah berani mengambil langkah untuk bercerita. Pengajuan Anda telah berhasil diterima dan sedang menunggu pemeriksaan.',
                'status' => 'Menunggu pemeriksaan',
                'details' => ['Kode pengajuan' => $pengajuan->kode_pengajuan, 'Waktu pengajuan' => now()->translatedFormat('d F Y, H:i').' WIB'],
                'actionUrl' => route('cek-status.hasil', ['kode' => $pengajuan->kode_pengajuan, 'email' => $email]),
                'actionLabel' => 'Lihat Perjalanan Pengajuan',
                'quote' => 'Keberanian untuk mencari bantuan adalah langkah kecil yang berarti.',
            ]);
        } catch (\Throwable $e) { report($e); }

        return redirect()->route('cek-status.hasil', ['kode' => $pengajuan->kode_pengajuan, 'email' => $email])
            ->with('sukses', 'Pengajuan berhasil dikirim. Simpan kode pengajuan untuk mengecek status.');
    }

    public function waliVerifikasi(string $token)
    {
        $pending = VerifikasiWaliPengajuan::where('token', $token)->whereNull('diselesaikan_at')->firstOrFail();
        abort_if($pending->berlaku_sampai->isPast(), 410, 'Tautan verifikasi sudah kedaluwarsa.');

        return view('konseling.wali-verifikasi', ['pending' => $pending]);
    }

    public function waliSelesaikan(Request $request, string $token): RedirectResponse
    {
        $pending = VerifikasiWaliPengajuan::where('token', $token)->whereNull('diselesaikan_at')->firstOrFail();
        abort_if($pending->berlaku_sampai->isPast(), 410, 'Tautan verifikasi sudah kedaluwarsa.');
        $validated = $request->validate([
            'persetujuan_wali' => ['accepted'],
            'who5' => ['required', 'array', 'size:5'],
            'who5.*' => ['required', 'integer', 'between:0,5'],
        ]);

        $data = (array) $pending->data;
        $data['who5_jawaban'] = array_values($validated['who5']);
        $prioritas = app(PrioritasKonselingService::class)->hitung($data['who5_jawaban'], now(), ($data['kondisi_darurat'] ?? null) === 'ya');
        $active = Pengajuan::where(function ($query) use ($pending, $data) {
            $query->where('email', $pending->email_mahasiswa)->orWhere('nim', $data['wali_mahasiswa_nim'] ?? '');
        })->aktif()->latest()->first();
        if ($active) {
            return redirect()->route('cek-status.hasil', ['kode' => $active->kode_pengajuan, 'email' => $active->email])
                ->with('pengajuan_aktif', 'Mahasiswa masih memiliki pengajuan konseling yang aktif.');
        }
        $pengajuan = $this->persistPengajuan($data, 'wali_mahasiswa', $pending->email_mahasiswa, (string) ($data['wali_mahasiswa_nim'] ?? ''), $prioritas);
        $pending->update(['diselesaikan_at' => now()]);

        if (($data['kondisi_darurat'] ?? null) === 'ya') {
            app(TelegramNotifier::class)->roles(['admin', 'koordinator'], "🚨 Pengajuan mendesak baru di SILAKON\n\nKode: {$pengajuan->kode_pengajuan}\nKonseli: ".($data['wali_mahasiswa_nama'] ?? 'Mahasiswa')."\nSegera periksa Pengajuan Mendesak dan antrean pemeriksaan.");
        }

        try {
            InteractiveMail::send($pending->email_wali, 'Pengajuan Konseling Berhasil Dikirim', [
                'greeting' => 'Pengajuan Anda telah diterima',
                'message' => 'Persetujuan dan pengisian WHO-5 berhasil diselesaikan. Pengajuan kini menunggu pemeriksaan Admin.',
                'status' => 'Menunggu pemeriksaan',
                'details' => ['Kode pengajuan' => $pengajuan->kode_pengajuan],
                'actionUrl' => route('cek-status.hasil', ['kode' => $pengajuan->kode_pengajuan, 'email' => $pending->email_mahasiswa]),
                'actionLabel' => 'Lihat Status Pengajuan',
                'quote' => 'Satu langkah telah selesai. Terima kasih telah menjalaninya dengan berani.',
            ]);
        } catch (\Throwable $e) { report($e); }

        return redirect()->route('cek-status.hasil', ['kode' => $pengajuan->kode_pengajuan, 'email' => $pending->email_mahasiswa])
            ->with('sukses', 'Persetujuan dan WHO-5 berhasil dikirim. Pengajuan menunggu pemeriksaan Admin.');
    }

    private function persistPengajuan(array $data, string $type, string $email, ?string $nim, array $prioritas): Pengajuan
    {
        $name = $type === 'wali_mahasiswa' ? (string) ($data['wali_mahasiswa_nama'] ?? '') : (string) ($data['nama'] ?? '');
        $phone = $type === 'wali_mahasiswa' ? (string) ($data['wali_mahasiswa_telepon'] ?? $data['wali_telepon'] ?? '') : (string) ($data['telepon'] ?? '');
        $konseli = Konseli::where('email', $email)->when($nim, fn ($q) => $q->orWhere('nim', $nim))->first() ?: new Konseli();
        $old = (array) ($konseli->data ?? []);
        $history = (array) ($old['_riwayat_pengajuan'] ?? []);
        unset($old['_riwayat_pengajuan']);
        if ($konseli->exists && $old !== []) $history[] = ['tersimpan_pada' => now()->toIso8601String(), 'nama' => $konseli->nama, 'email' => $konseli->email, 'nim' => $konseli->nim, 'telepon' => $konseli->telepon, 'data' => $old];
        $profile = array_merge($old, $data); $profile['_riwayat_pengajuan'] = $history;
        $konseli->fill(['jenis_konseli' => $type, 'nama' => $konseli->nama ?: $name, 'email' => $email, 'nim' => $nim ?: null, 'telepon' => $konseli->telepon ?: $phone, 'data' => $profile, 'terakhir_mendaftar_at' => now()])->save();
        return DB::transaction(function () use ($konseli, $email, $nim, $data, $prioritas) {
            $p = Pengajuan::create(['konseli_id' => $konseli->id, 'kode_pengajuan' => $this->generateCode(), 'jenis_layanan' => 'individual', 'status' => Pengajuan::STATUS_MENUNGGU_PEMERIKSAAN, 'email' => $email, 'nim' => $nim ?: null, 'data' => $data, 'who5_skor' => $prioritas['who5_skor'], 'saw_nilai' => $prioritas['saw_nilai'], 'prioritas' => $prioritas['prioritas'], 'terverifikasi_at' => now()]);
            $p->riwayatStatus()->create(['status_baru' => Pengajuan::STATUS_MENUNGGU_PEMERIKSAAN, 'keterangan' => 'Mahasiswa menyetujui pengajuan wali dan mengisi WHO-5.']);
            return $p;
        });
    }

    private function validateCommon(Request $request, string $type, string $emailField): void
    {
        $rules = [
            'jenis_konseli' => ['required', 'in:mahasiswa,alumni,tenaga_kependidikan,wali_mahasiswa'],
            'metode_layanan' => ['required', 'string', 'max:40'],
            'preferensi_hari' => ['required', 'string', 'max:30'],
            'preferensi_waktu' => ['required', 'string', 'max:30'],
            // Pilihan manual lama dipertahankan untuk kompatibilitas data lama,
            // tetapi tidak lagi wajib/ditampilkan pada formulir baru.
            'tingkat_kerentanan' => ['nullable', 'string', 'max:30'],
            'kondisi_darurat' => ['required', 'in:ya,tidak'],
            'konfirmasi_darurat' => ['accepted_if:kondisi_darurat,ya'],
            'deskripsi_masalah' => ['required', 'string', 'max:5000'],
            'lingkup_hambatan' => ['required', 'array', 'min:1'],
            'lingkup_hambatan.*' => ['string', 'max:100'],
            'lingkup_hambatan_lainnya' => ['required_if:lingkup_hambatan.*,Yang lain', 'nullable', 'string', 'max:150'],
            'verification_code' => [$type === 'wali_mahasiswa' && $request->input('wali_subjek_konseling') === 'mahasiswa' ? 'nullable' : 'required', 'digits:6'],
            $emailField => ['required', 'email'],
            'who5' => [$type === 'wali_mahasiswa' && $request->input('wali_subjek_konseling') === 'mahasiswa' ? 'nullable' : 'required', 'array', 'size:5'],
            'who5.*' => ['required', 'integer', 'between:0,5'],
        ];

        if ($type === 'wali_mahasiswa') {
            $rules += [
                'wali_mahasiswa_fakultas' => ['required', 'string', 'max:150'],
                'wali_mahasiswa_prodi' => ['required', 'string', 'max:150'],
                'wali_jenis_kelamin' => ['required', 'in:laki_laki,perempuan'],
                'wali_mahasiswa_jenis_kelamin' => ['required', 'in:laki_laki,perempuan'],
                'wali_subjek_konseling' => ['required', 'in:wali,mahasiswa'],
                'wali_mahasiswa_telepon' => ['required_if:wali_subjek_konseling,mahasiswa', 'nullable', 'string', 'max:40'],
                'wali_mahasiswa_email' => ['required_if:wali_subjek_konseling,mahasiswa', 'email'],
            ];
        }

        if ($type === 'mahasiswa' || $type === 'alumni') {
            $rules[$type.'_tanggal_lahir'] = ['required', 'date', 'before_or_equal:today'];
        } elseif ($type === 'tenaga_kependidikan') {
            $rules['tk_tanggal_lahir'] = ['required', 'date', 'before_or_equal:today'];
        }

        validator($request->all(), $rules)->validate();
    }

    private function identity(Request $request, string $type): array
    {
        return match ($type) {
            'mahasiswa' => [(string) $request->input('mahasiswa_nama'), (string) $request->input('mahasiswa_telepon')],
            'alumni' => [(string) $request->input('alumni_nama'), (string) $request->input('alumni_telepon')],
            'tenaga_kependidikan' => [(string) $request->input('tk_nama'), (string) $request->input('tk_telepon')],
            'wali_mahasiswa' => $request->input('wali_subjek_konseling') === 'mahasiswa'
                ? [(string) $request->input('wali_mahasiswa_nama'), (string) $request->input('wali_mahasiswa_telepon')]
                : [(string) $request->input('wali_nama'), (string) $request->input('wali_telepon')],
            default => ['', ''],
        };
    }

    private function normalizeEmail(string $email, string $type): string
    {
        $email = strtolower(trim($email));

        if ($type === 'mahasiswa' && $email !== '' && ! str_contains($email, '@')) {
            $email .= '@students.unnes.ac.id';
        }

        return $email;
    }

    private function generateCode(): string
    {
        do {
            $code = 'SLK-' . now()->format('Y-m') . '-' . random_int(1000, 9999);
        } while (Pengajuan::where('kode_pengajuan', $code)->exists());

        return $code;
    }
}
