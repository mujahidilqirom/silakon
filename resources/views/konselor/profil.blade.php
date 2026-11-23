{{--
    SILAKON — Profil Saya (Konselor)
    Simpan sebagai: resources/views/konselor/profil.blade.php

    Data ($profil, $telegram, $notifikasi) dikirim dari ProfilController (session dummy).
--}}
@extends('layouts.konselor')

@section('menu', 'profil')
@section('judul', 'Profil Saya')
@section('subjudul', $tanggalHariIni)

@section('content')

<div class="pf-halaman">

    @if(session('sukses'))
        <div class="pf-flash">{{ session('sukses') }}</div>
    @endif

    {{-- ============ DATA DIRI ============ --}}
    <div class="kartu pf-kartu">
        <div class="pf-profil-baris">
            <div class="pf-avatar">{{ $profil['inisial'] }}</div>
            <div class="pf-profil-info">
                <b>{{ $profil['nama'] }}</b>
                <span class="pf-peran">Konselor {{ $profil['spesialisasi'] }}</span>
                <div class="pf-badge-baris">
                    <span class="pf-badge pf-badge-hijau">{{ $profil['status'] }}</span>
                    <span class="pf-badge pf-badge-biru">{{ $profil['kasus_aktif'] }} Kasus Aktif</span>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('konselor.profil.update') }}">
            @csrf
            @method('PUT')

            <div class="pf-grid-2">
                <div class="pf-field">
                    <label for="pfNama">Nama Lengkap</label>
                    <input type="text" id="pfNama" name="nama" value="{{ old('nama', $profil['nama']) }}" required>
                </div>
                <div class="pf-field">
                    <label for="pfEmail">Email</label>
                    <input type="email" id="pfEmail" name="email" value="{{ old('email', $profil['email']) }}" required>
                </div>
                <div class="pf-field">
                    <label for="pfSpesialisasi">Spesialisasi</label>
                    <input type="text" id="pfSpesialisasi" name="spesialisasi" value="{{ old('spesialisasi', $profil['spesialisasi']) }}" required>
                </div>
                <div class="pf-field">
                    <label for="pfNoHp">Nomor HP</label>
                    <input type="text" id="pfNoHp" name="no_hp" value="{{ old('no_hp', $profil['no_hp']) }}" required>
                </div>
            </div>

            <div class="pf-field pf-field-lebar">
                <label for="pfBio">Deskripsi / Bio</label>
                <textarea id="pfBio" name="bio" rows="3">{{ old('bio', $profil['bio']) }}</textarea>
            </div>

            @error('nama') <p class="pf-error">{{ $message }}</p> @enderror
            @error('email') <p class="pf-error">{{ $message }}</p> @enderror
            @error('no_hp') <p class="pf-error">{{ $message }}</p> @enderror

            <button type="submit" class="pf-tombol pf-tombol-hijau">Simpan Perubahan</button>
        </form>
    </div>

    {{-- ============ UBAH PASSWORD ============ --}}
    <div class="kartu pf-kartu">
        <div class="pf-kartu-judul">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <h2>Ubah Password</h2>
        </div>

        @if(session('gagalPassword'))
            <p class="pf-error pf-error-atas">{{ $errors->first('password_saat_ini') }}</p>
        @endif

        <form method="POST" action="{{ route('konselor.profil.password') }}">
            @csrf
            @method('PUT')

            <div class="pf-field pf-field-lebar">
                <label for="pfPasswordLama">Password Saat Ini</label>
                <input type="password" id="pfPasswordLama" name="password_saat_ini" placeholder="••••••••" required>
            </div>
            <div class="pf-field pf-field-lebar">
                <label for="pfPasswordBaru">Password Baru</label>
                <input type="password" id="pfPasswordBaru" name="password_baru" placeholder="••••••••" required>
                @error('password_baru') <span class="pf-error">{{ $message }}</span> @enderror
            </div>
            <div class="pf-field pf-field-lebar">
                <label for="pfPasswordKonfirmasi">Konfirmasi Password Baru</label>
                <input type="password" id="pfPasswordKonfirmasi" name="password_baru_confirmation" placeholder="••••••••" required>
            </div>

            <button type="submit" class="pf-tombol pf-tombol-outline">Ubah Password</button>
        </form>
    </div>

    {{-- ============ INTEGRASI TELEGRAM ============ --}}
    <div class="kartu pf-kartu">
        <div class="pf-kartu-judul">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            <h2>Integrasi Telegram</h2>
            @if($telegram['terhubung'])
                <span class="pf-badge pf-badge-hijau pf-badge-status">Terhubung</span>
            @else
                <span class="pf-badge pf-badge-abu pf-badge-status">Belum Terhubung</span>
            @endif
        </div>

        <p class="pf-deskripsi">Hubungkan akun Telegram untuk menerima notifikasi penugasan baru, pengingat jadwal, dan pesan dari admin secara real-time.</p>

        <div class="pf-bot-baris">
            <div class="pf-bot-ikon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            </div>
            <div class="pf-bot-info">
                <b>{{ $telegram['bot_nama'] }}</b>
                @if($telegram['terhubung'])
                    <span>Terhubung sebagai {{ $telegram['username_telegram'] }}</span>
                @else
                    <span>Belum terhubung ke akun Telegram</span>
                @endif
            </div>
        </div>

        @if($telegram['terhubung'])
            <form method="POST" action="{{ route('konselor.profil.telegram.putus') }}">
                @csrf
                <button type="submit" class="pf-tombol pf-tombol-outline pf-tombol-tengah">Putuskan Telegram</button>
            </form>
        @else
            <form method="POST" action="{{ route('konselor.profil.telegram.hubungkan') }}">
                @csrf
                <button type="submit" class="pf-tombol pf-tombol-hijau pf-tombol-tengah">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    Hubungkan Telegram
                </button>
            </form>
            <p class="pf-hint">Klik tombol di atas, lalu ketik <b>/start</b> di bot Telegram untuk menghubungkan akun Anda.</p>
        @endif
    </div>

    {{-- ============ PENGATURAN NOTIFIKASI ============ --}}
    <div class="kartu pf-kartu">
        <div class="pf-kartu-judul">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
            <h2>Pengaturan Notifikasi</h2>
        </div>

        <div class="pf-tabel-bungkus">
            <table class="pf-tabel-notif" id="pfTabelNotifikasi">
                <thead>
                    <tr>
                        <th class="pf-kolom-jenis">Jenis Notifikasi</th>
                        <th>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                            Dashboard
                        </th>
                        <th>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                            Telegram
                        </th>
                        <th>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16v16H4z"/><path d="m4 4 8 8 8-8"/></svg>
                            Email
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($notifikasi as $n)
                        <tr>
                            <td class="pf-kolom-jenis">{{ $n['jenis'] }}</td>
                            @foreach(['dashboard', 'telegram', 'email'] as $kanal)
                                <td>
                                    <label class="pf-switch {{ $kanal === 'telegram' && !$telegram['terhubung'] ? 'pf-switch-nonaktif' : '' }}">
                                        <input
                                            type="checkbox"
                                            data-jenis="{{ $n['jenis'] }}"
                                            data-kanal="{{ $kanal }}"
                                            {{ $n[$kanal] ? 'checked' : '' }}
                                            {{ $kanal === 'telegram' && !$telegram['terhubung'] ? 'disabled' : '' }}
                                        >
                                        <span class="pf-slider"></span>
                                    </label>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @unless($telegram['terhubung'])
            <div class="pf-peringatan">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                <p>Kolom Telegram dinonaktifkan. Hubungkan akun Telegram terlebih dahulu untuk mengaktifkan notifikasi Telegram.</p>
            </div>
        @endunless
    </div>

</div>

<script>
    (function () {
        var tabel = document.getElementById('pfTabelNotifikasi');
        if (!tabel) return;

        var metaCsrf = document.querySelector('meta[name="csrf-token"]');
        var csrfToken = metaCsrf ? metaCsrf.content : '{{ csrf_token() }}';
        var urlNotifikasi = "{{ route('konselor.profil.notifikasi') }}";

        tabel.addEventListener('change', function (e) {
            var input = e.target;
            if (input.tagName !== 'INPUT' || !input.dataset.jenis) return;

            var jenis = input.dataset.jenis;
            var kanal = input.dataset.kanal;
            var aktif = input.checked;

            fetch(urlNotifikasi, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ jenis: jenis, kanal: kanal, aktif: aktif }),
            })
                .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
                .then(function (hasil) {
                    if (!hasil.ok) {
                        input.checked = !aktif; // batalkan kalau ditolak server
                        alert(hasil.data.pesan || 'Gagal menyimpan pengaturan notifikasi.');
                    }
                })
                .catch(function () {
                    input.checked = !aktif;
                    alert('Terjadi kesalahan jaringan. Coba lagi.');
                });
        });
    })();
</script>

<style>
    .pf-halaman { display: flex; flex-direction: column; gap: 20px; }

    .pf-flash {
        background: #dff3e6; color: #1f7a4d; border: 1px solid #bfe4cd;
        border-radius: 10px; padding: 12px 16px; font-size: 13.5px; font-weight: 600;
    }

    .pf-kartu { padding: 24px; }

    .pf-profil-baris {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 24px;
    }
    .pf-avatar {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: #2f6b52;
        color: #fff;
        font-weight: 700;
        font-size: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .pf-profil-info { display: flex; flex-direction: column; gap: 4px; padding-top: 2px; }
    .pf-profil-info b { font-size: 18px; color: #16241d; }
    .pf-peran { font-size: 13.5px; color: #2f6b52; font-weight: 600; }
    .pf-badge-baris { display: flex; gap: 8px; margin-top: 4px; }

    .pf-badge {
        display: inline-flex;
        align-items: center;
        padding: 3px 11px;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 700;
    }
    .pf-badge-hijau { background: #dff3e6; color: #1f7a4d; }
    .pf-badge-biru { background: #dfeaf9; color: #1d5fa8; }
    .pf-badge-abu { background: #eef2ef; color: #6b7a72; }
    .pf-badge-status { margin-left: 4px; }

    .pf-kartu-judul {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
    }
    .pf-kartu-judul svg { width: 17px; height: 17px; color: #2f6b52; }
    .pf-kartu-judul h2 { margin: 0; font-size: 16px; color: #16241d; }

    .pf-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 16px;
    }
    .pf-field { display: flex; flex-direction: column; gap: 7px; margin-bottom: 16px; }
    .pf-field:last-of-type { margin-bottom: 0; }
    .pf-field-lebar { grid-column: 1 / -1; }
    .pf-field label { font-size: 13px; font-weight: 600; color: #16241d; }
    .pf-field input,
    .pf-field textarea {
        border: 1px solid #dfe6e1;
        border-radius: 10px;
        padding: 11px 14px;
        font-size: 13.5px;
        font-family: inherit;
        color: #33403a;
        outline: none;
        box-sizing: border-box;
        width: 100%;
    }
    .pf-field textarea { resize: vertical; }
    .pf-field input:focus,
    .pf-field textarea:focus { border-color: #6faf8a; }

    .pf-error { margin: -8px 0 12px; font-size: 12.5px; color: #b3261e; }
    .pf-error-atas { margin-top: -4px; }

    .pf-tombol {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 11px 20px;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: background .15s, border-color .15s;
    }
    .pf-tombol svg { width: 15px; height: 15px; }
    .pf-tombol-hijau { background: #2f6b52; color: #fff; }
    .pf-tombol-hijau:hover { background: #24543f; }
    .pf-tombol-outline { background: #fff; border: 1px solid #dfe6e1; color: #33403a; }
    .pf-tombol-outline:hover { border-color: #bcd0c6; background: #f2f8f4; }
    .pf-tombol-tengah { margin: 0 auto; display: flex; }

    .pf-deskripsi { margin: 0 0 16px; font-size: 13.5px; color: #6b7a72; line-height: 1.6; }

    .pf-bot-baris {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #f6f9f7;
        border-radius: 10px;
        padding: 14px 16px;
        margin-bottom: 16px;
    }
    .pf-bot-ikon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: #e6ece8;
        color: #6b7a72;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .pf-bot-ikon svg { width: 18px; height: 18px; }
    .pf-bot-info { display: flex; flex-direction: column; gap: 2px; }
    .pf-bot-info b { font-size: 13.5px; color: #16241d; }
    .pf-bot-info span { font-size: 12.5px; color: #8a978f; }

    .pf-hint { margin: 14px 0 0; font-size: 12.5px; color: #8a978f; text-align: center; }

    /* Tabel notifikasi */
    .pf-tabel-bungkus { overflow-x: auto; }
    .pf-tabel-notif { width: 100%; border-collapse: collapse; min-width: 520px; }
    .pf-tabel-notif thead th {
        text-align: left;
        font-size: 12.5px;
        font-weight: 600;
        color: #16241d;
        padding: 10px 12px;
        border-bottom: 1px solid #e6ece8;
    }
    .pf-tabel-notif thead th:not(.pf-kolom-jenis) {
        display: table-cell;
    }
    .pf-tabel-notif thead th svg {
        width: 13px; height: 13px; margin-right: 5px; vertical-align: -2px; color: #6b7a72;
    }
    .pf-tabel-notif tbody td {
        padding: 12px;
        border-bottom: 1px solid #eef2ef;
        vertical-align: middle;
    }
    .pf-tabel-notif tbody tr:last-child td { border-bottom: none; }
    .pf-kolom-jenis { font-size: 13.5px; color: #33403a; font-weight: 500; white-space: nowrap; }

    .pf-switch {
        position: relative;
        display: inline-block;
        width: 38px;
        height: 21px;
    }
    .pf-switch input { opacity: 0; width: 0; height: 0; }
    .pf-slider {
        position: absolute; inset: 0; cursor: pointer;
        background: #dfe6e1; border-radius: 999px; transition: background .15s;
    }
    .pf-slider::before {
        content: ""; position: absolute; width: 15px; height: 15px; left: 3px; top: 3px;
        background: #fff; border-radius: 50%; transition: transform .15s;
        box-shadow: 0 1px 2px rgba(16,24,20,.2);
    }
    .pf-switch input:checked + .pf-slider { background: #2f6b52; }
    .pf-switch input:checked + .pf-slider::before { transform: translateX(17px); }
    .pf-switch-nonaktif { cursor: not-allowed; }
    .pf-switch-nonaktif .pf-slider { cursor: not-allowed; opacity: .6; }

    .pf-peringatan {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-top: 16px;
        padding: 14px 16px;
        background: #fdf6e8;
        border: 1px solid #f3e3b8;
        border-radius: 12px;
    }
    .pf-peringatan svg { width: 16px; height: 16px; color: #92720c; flex-shrink: 0; margin-top: 1px; }
    .pf-peringatan p { margin: 0; font-size: 12.5px; color: #8a7a4f; line-height: 1.5; }

    @media (max-width: 720px) {
        .pf-grid-2 { grid-template-columns: 1fr; }
        .pf-profil-baris { flex-direction: column; align-items: flex-start; }
    }
</style>

@endsection