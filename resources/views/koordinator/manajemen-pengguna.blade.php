{{--
    SILAKON — Manajemen Pengguna
    Simpan sebagai: resources/views/koordinator/manajemen-pengguna.blade.php
    Data dikirim dari KoordinatorController@pengguna
--}}
@extends('layouts.koordinator')

@section('judul', 'Manajemen Pengguna')
@section('subjudul', now()->translatedFormat('l, d F Y'))

@section('content')

<div class="k-head-row">
    <div class="k-page-head">
        <h2>Manajemen Pengguna</h2>
        <p>Kelola semua akun dalam sistem SILAKON</p>
    </div>
    <button type="button" class="k-btn" onclick="bukaModalTambah()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
        Tambah Akun
    </button>
</div>

@if(session('sukses'))
    <div class="k-alert k-alert-sukses">{{ session('sukses') }}</div>
@endif
@if($errors->any())
    <div class="k-alert k-alert-error">
        <ul>
            @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
    </div>
@endif

{{-- ============ TABS ============ --}}
<div class="k-card k-panel" style="padding-top:0">
    <div class="k-user-tabs">
        <button type="button" class="k-user-tab active" data-tab="semua">Semua <span>{{ count($pengguna) }}</span></button>
        <button type="button" class="k-user-tab" data-tab="admin">Admin <span>{{ collect($pengguna)->filter(fn($p)=>in_array('Admin',$p['role']))->count() }}</span></button>
        <button type="button" class="k-user-tab" data-tab="konselor">Konselor <span>{{ collect($pengguna)->filter(fn($p)=>in_array('Konselor',$p['role']))->count() }}</span></button>
        <button type="button" class="k-user-tab" data-tab="koordinator">Koordinator <span>{{ collect($pengguna)->filter(fn($p)=>in_array('Koordinator',$p['role']))->count() }}</span></button>
    </div>

    <div class="k-table-wrap">
        <table class="k-table-user">
            <thead>
                <tr>
                    <th class="k-col-nama">Nama</th>
                    <th class="k-col-kontak">Kontak</th>
                    <th class="k-col-role">Role</th>
                    <th class="k-col-ket">Keterangan</th>
                    <th class="c k-col-status">Status</th>
                    <th class="k-col-tgl">Tgl Dibuat</th>
                    <th class="c k-col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengguna as $p)
                    <tr class="k-user-row" data-roles="{{ strtolower(implode(',', $p['role'])) }}">
                        <td class="k-user-nama" data-label="Nama">
                            <div class="k-profil-wrap">
                                <span class="k-avatar">{{ strtoupper(substr($p['nama'],0,1)) }}</span>
                                <span class="k-nama-teks">{{ $p['nama'] }}</span>
                            </div>
                        </td>
                        <td class="k-user-kontak" data-label="Kontak">
                            <div class="k-kontak-wrap">
                                <a href="mailto:{{ $p['email'] }}" class="k-kontak-link" title="Kirim email">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                    {{ $p['email'] }}
                                </a>
                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/\D/', '', $p['wa'])) }}" target="_blank" rel="noopener" class="k-kontak-link k-kontak-wa" title="Chat WhatsApp">
                                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.46 1.32 4.96L2 22l5.28-1.39a9.9 9.9 0 0 0 4.76 1.21h.01c5.46 0 9.9-4.45 9.9-9.91 0-2.65-1.03-5.14-2.9-7.01A9.87 9.87 0 0 0 12.04 2zm0 1.67c2.2 0 4.26.86 5.82 2.42a8.2 8.2 0 0 1 2.41 5.82c0 4.54-3.7 8.24-8.25 8.24a8.2 8.2 0 0 1-4.19-1.15l-.3-.18-3.13.82.84-3.05-.2-.31a8.18 8.18 0 0 1-1.26-4.38c0-4.55 3.71-8.23 8.26-8.23zm-4.53 4.7c-.16 0-.42.06-.64.3-.22.24-.85.83-.85 2.02 0 1.19.87 2.34.99 2.5.12.16 1.7 2.71 4.2 3.7 2.08.82 2.5.66 2.95.62.45-.04 1.46-.6 1.66-1.18.2-.58.2-1.08.14-1.18-.06-.1-.22-.16-.46-.28-.24-.12-1.46-.72-1.68-.8-.23-.08-.4-.12-.56.12-.16.24-.64.8-.79.97-.14.16-.29.18-.53.06-.24-.12-1.02-.38-1.95-1.2-.72-.64-1.2-1.44-1.35-1.68-.14-.24-.02-.37.1-.49.11-.11.24-.29.37-.43.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.43-.06-.12-.56-1.35-.77-1.85-.2-.48-.4-.42-.56-.42z"/></svg>
                                    {{ $p['wa'] }}
                                </a>
                            </div>
                        </td>
                        <td data-label="Role">
                            <div class="k-badge-wrap">
                                @foreach($p['role'] as $r)
                                    <span class="k-badge k-badge-{{ strtolower($r) }}">{{ $r }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="k-user-ket" data-label="Keterangan">{{ $p['keterangan'] ?? '—' }}</td>
                        <td class="c" data-label="Status">
                            <span class="k-badge {{ $p['status'] === 'Aktif' ? 'k-badge-aktif' : 'k-badge-nonaktif' }}">{{ $p['status'] }}</span>
                        </td>
                        <td data-label="Tgl Dibuat" style="white-space:nowrap">{{ \Carbon\Carbon::parse($p['tgl'])->translatedFormat('d M Y') }}</td>
                        <td class="c k-user-aksi" data-label="Aksi">
                            <div class="k-aksi-group">
                                <button type="button" class="k-btn-mini" onclick='bukaModalEdit(@json($p))' title="Edit Akun">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                </button>

                                <form action="{{ route('pengguna.reset', $p['id']) }}" method="POST" onsubmit="return confirm('Reset password untuk {{ $p['nama'] }}?')">
                                    @csrf
                                    <button type="submit" class="k-btn-mini" title="Reset Password">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-2.64-6.36"/><path d="M21 3v6h-6"/></svg>
                                    </button>
                                </form>

                                <form action="{{ route('pengguna.toggle', $p['id']) }}" method="POST" onsubmit="return confirm('{{ $p['status'] === 'Aktif' ? 'Nonaktifkan' : 'Aktifkan' }} akun {{ $p['nama'] }}?')">
                                    @csrf
                                    <button type="submit" class="k-btn-mini {{ $p['status'] === 'Aktif' ? 'k-btn-mini-bahaya' : 'k-btn-mini-sukses' }}" title="{{ $p['status'] === 'Aktif' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        @if($p['status'] === 'Aktif')
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m4.9 4.9 14.2 14.2"/></svg>
                                        @else
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                        @endif
                                    </button>
                                </form>
                                @if((int) $p['id'] !== (int) auth()->id())
                                <form action="{{ route('pengguna.destroy', $p['id']) }}" method="POST" onsubmit="return confirm('Hapus akun {{ $p['nama'] }} secara permanen?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="k-btn-mini k-btn-mini-bahaya" title="Hapus akun"><span aria-hidden="true">×</span></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="c" style="padding:32px;color:var(--k-muted,#6b7280)">Belum ada pengguna.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ============ MODAL TAMBAH / EDIT ============ --}}
<div id="modalPengguna" class="k-modal-overlay" style="display:none">
    <div class="k-modal">
        <div class="k-modal-head">
            <h3 id="modalJudul">Tambah Akun</h3>
            <button type="button" class="k-modal-close" onclick="tutupModal()">&times;</button>
        </div>
        <form id="formPengguna" method="POST" action="{{ route('pengguna.store') }}">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">

            <div class="k-form-grid">
                <label class="k-field">
                    <span>Nama Lengkap</span>
                    <input type="text" name="nama" id="fNama" required placeholder="Dr. Nama Lengkap, M.Pd.">
                </label>
                <label class="k-field">
                    <span>Email</span>
                    <input type="email" name="email" id="fEmail" required placeholder="nama@lppp.unnes.ac.id">
                </label>
                <label class="k-field">
                    <span>No. WhatsApp</span>
                    <input type="text" name="wa" id="fWa" required placeholder="081234567890">
                </label>
                <label class="k-field">
                    <span>Status</span>
                    <select name="status" id="fStatus">
                        <option value="Aktif">Aktif</option>
                        <option value="Nonaktif">Nonaktif</option>
                    </select>
                </label>

                <div class="k-field k-field-full" id="roleField">
                    <span>Role</span>
                    <div class="k-checkbox-row">
                        <label><input type="radio" name="role[]" value="Admin" class="fRole"> Admin</label>
                        <label><input type="radio" name="role[]" value="Konselor" class="fRole" onchange="toggleKeterangan()"> Konselor</label>
                        <label><input type="radio" name="role[]" value="Koordinator" class="fRole"> Koordinator</label>
                    </div>
                </div>

                <label class="k-field k-field-full" id="wrapKeterangan" style="display:none">
                    <span>Keterangan Konselor</span>
                    <select name="keterangan" id="fKeterangan">
                        <option value="">— Pilih keterangan —</option>
                        <option value="Dosen Konselor">Dosen Konselor</option>
                        <option value="Magang PPK">Magang PPK</option>
                        <option value="Konselor Tetap">Konselor Tetap</option>
                        <option value="Konselor Mitra">Konselor Mitra</option>
                    </select>
                </label>

                <p class="k-field-hint k-field-full" id="hintPassword">Password awal akan dibuat otomatis dan dikirim ke email pengguna.</p>
            </div>

            <div class="k-modal-foot">
                <button type="button" class="k-btn-outline" onclick="tutupModal()">Batal</button>
                <button type="submit" class="k-btn">Simpan</button>
            </div>
        </form>
    </div>
</div>

<style>
/* Header & Layout */
.k-head-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.k-page-head h2 { margin: 0 0 4px; color: #1b2432; }
.k-page-head p { margin: 0; color: #6b7280; font-size: 14px; }

/* Menyelaraskan Tombol Utama (Batal & Simpan) */
.k-btn, .k-btn-outline {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 0 18px;
    margin: 0;
    border-radius: 8px;
    font-family: inherit; /* jangan pakai font bawaan browser utk <button> */
    font-size: 14px;
    line-height: 1; /* cegah teks turun/naik akibat line-height default beda antar elemen */
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
    box-sizing: border-box; /* Mencegah perbedaan tinggi */
    height: 42px; /* Mengunci tinggi yang sama untuk kedua tombol */
    vertical-align: middle;
}
.k-btn { background: #3f6e33; color: #fff; border: 1px solid transparent; /* Border transparan agar sama ukurannya dengan outline */ }
.k-btn svg { width: 18px; height: 18px; }
.k-btn:hover { background: #325928; }

.k-btn-outline { background: #fff; color: #3a4552; border: 1px solid #dfe3df; }
.k-btn-outline:hover { background: #f5f7f4; }

/* Alert */
.k-alert { padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-size: 14px; }
.k-alert ul { margin: 0; padding-left: 18px; }
.k-alert-sukses { background: #eaf4e6; color: #3f6e33; border: 1px solid #cfe6c4; }
.k-alert-error { background: #fdecec; color: #a83232; border: 1px solid #f6c6c6; }

/* Tabs */
.k-user-tabs { display: flex; gap: 6px; scrollbar-width: none; border-bottom: 1px solid #eef0ee; padding: 14px 0 0; }
.k-user-tab { 
    background: none; border: none; padding: 10px 14px; font-size: 14px; color: #6b7280; 
    cursor: pointer; border-bottom: 2px solid transparent; display: flex; align-items: center; gap: 6px; 
}
.k-user-tab span { background: #eef1ee; color: #556; border-radius: 20px; font-size: 12px; padding: 1px 8px; }
.k-user-tab.active { color: #3f6e33; border-bottom-color: #6E8F60; font-weight: 600; }
.k-user-tab.active span { background: #dcecd4; color: #3f6e33; }

/* Tabel Diperbaiki: lebar kolom proporsional, total 100% agar pas tanpa scrollbar */
.k-table-wrap {
    overflow-x: auto; width: 100%; border-radius: 8px;
    -webkit-overflow-scrolling: touch;
    /* bayangan tipis di tepi kiri/kanan sebagai petunjuk bisa digeser */
    background:
        linear-gradient(to right, #fff 30%, rgba(255,255,255,0)) left center,
        linear-gradient(to left,  #fff 30%, rgba(255,255,255,0)) right center,
        radial-gradient(farthest-side at 0 50%,  rgba(0,0,0,.12), rgba(0,0,0,0)) left center,
        radial-gradient(farthest-side at 100% 50%, rgba(0,0,0,.12), rgba(0,0,0,0)) right center;
    background-repeat: no-repeat;
    background-size: 28px 100%, 28px 100%, 10px 100%, 10px 100%;
    background-attachment: local, local, scroll, scroll;
}
.k-table-user {
    width: 100%;
    min-width: 960px;   /* di bawah lebar ini tabel digeser, bukan dipadatkan */
    table-layout: fixed;
    border-collapse: separate;
    border-spacing: 0;
}
.k-table-user thead th {
    font-size: 12px; text-transform: uppercase; letter-spacing: .03em; color: #8b93a1;
    text-align: left; padding: 14px 12px; border-bottom: 1px solid #eef0ee;
}
.k-table-user td {
    padding: 16px 12px;
    border-bottom: 1px solid #f2f3f1;
    vertical-align: top; /* Penyelarasan vertikal sempurna untuk seluruh kolom */
    font-size: 14px;
    overflow-wrap: break-word;
    word-break: break-word;
}
.k-table-user th.c, .k-table-user td.c { text-align: center; }

/* Lebar tiap kolom (jumlah = 100%) */
.k-col-nama   { width: 21%; }
.k-col-kontak { width: 20%; }
.k-col-role   { width: 13%; }
.k-col-ket    { width: 12%; }
.k-col-status { width: 9%;  }
.k-col-tgl    { width: 11%; }
.k-col-aksi   { width: 14%; }

/* User Info & Kontak (Pemisahan Jelas) */
.k-profil-wrap { display: flex; align-items: flex-start; gap: 12px; }
.k-avatar { 
    width: 38px; height: 38px; border-radius: 50%; background: #6E8F60; color: #fff; 
    display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; flex-shrink: 0; 
}
.k-nama-teks { font-weight: 600; color: #1b2432; line-height: 1.4; padding-top: 8px; }

.k-kontak-wrap { display: flex; flex-direction: column; gap: 8px; padding-top: 6px; }
.k-kontak-link { display: inline-flex; align-items: flex-start; gap: 8px; color: #3a4552; text-decoration: none; font-size: 13px; overflow-wrap: anywhere; line-height: 1.4; }
.k-kontak-link svg { width: 15px; height: 15px; flex-shrink: 0; margin-top: 2px; }
.k-kontak-link:hover { color: #6E8F60; text-decoration: underline; }
.k-kontak-wa { color: #2e8b57; }
.k-kontak-wa:hover { color: #1f6b40; }

.k-badge-wrap { display: flex; flex-direction: column; gap: 6px; align-items: flex-start; padding-top: 4px; }
.k-user-ket { color: #556; font-size: 13px; padding-top: 6px; }

/* Badge */
.k-badge { display: inline-flex; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.k-badge-admin { background: #e4ecfc; color: #2f5bd7; }
.k-badge-konselor { background: #e2f3e6; color: #2f8a4e; }
.k-badge-koordinator { background: #fdf3d8; color: #a1780e; }
.k-badge-aktif { background: #e2f3e6; color: #2f8a4e; margin-top: 4px; }
.k-badge-nonaktif { background: #f1f1ef; color: #7a8177; margin-top: 4px; }

/* Tombol Aksi (Icon Saja) */
.k-aksi-group { display: flex; gap: 6px; justify-content: center; align-items: center; margin-top: 2px; }
.k-aksi-group form { display: inline-flex; margin: 0; }
.k-btn-mini { 
    display: inline-flex; align-items: center; justify-content: center;
    width: 32px; height: 32px; 
    padding: 0; border-radius: 8px; border: 1px solid #dfe3df; background: #fff; color: #3a4552; 
    cursor: pointer; transition: 0.2s;
}
.k-btn-mini svg { width: 16px; height: 16px; }
.k-btn-mini:hover { background: #f5f7f4; color: #1b2432; }
.k-btn-mini-bahaya { color: #c33; border-color: #f2cccc; }
.k-btn-mini-bahaya:hover { background: #fdecec; color: #a83232; }
.k-btn-mini-sukses { color: #2f8a4e; border-color: #cfe6c4; }
.k-btn-mini-sukses:hover { background: #eaf4e6; color: #256d3e; }

/* Modal */
.k-modal-overlay { 
    position: fixed; inset: 0; background: rgba(20,25,20,.45); display: flex; 
    align-items: center; justify-content: center; z-index: 1000; padding: 16px; 
}
.k-modal { 
    background: #fff; border-radius: 14px; width: 100%; max-width: 560px; 
    max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 50px rgba(0,0,0,.25); 
}
.k-modal-head { 
    display: flex; align-items: center; justify-content: space-between; 
    padding: 18px 22px; border-bottom: 1px solid #eef0ee; 
}
.k-modal-head h3 { margin: 0; font-size: 17px; color: #1b2432; }
.k-modal-close { background: none; border: none; font-size: 24px; line-height: 1; cursor: pointer; color: #8b93a1; transition: 0.2s; }
.k-modal-close:hover { color: #3a4552; }

/* Form Grid dalam Modal */
.k-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; padding: 22px; }
.k-field { display: flex; flex-direction: column; gap: 8px; font-size: 13px; color: #3a4552; font-weight: 600; }
.k-field input, .k-field select { 
    font-weight: 400; padding: 10px 12px; border: 1px solid #dfe3df; 
    border-radius: 8px; font-size: 14px; color: #1b2432; width: 100%; box-sizing: border-box;
}
.k-field input:focus, .k-field select:focus { outline: none; border-color: #6E8F60; }
.k-field-full { grid-column: 1 / -1; }
.k-checkbox-row { display: flex; gap: 18px; font-weight: 400; flex-wrap: wrap; }
.k-checkbox-row label { display: flex; align-items: center; gap: 8px; font-size: 14px; cursor: pointer; }
.k-field-hint { font-size: 12.5px; color: #8b93a1; font-weight: 400; margin: 0; }

.k-modal-foot { 
    display: flex; justify-content: flex-end; align-items: center; gap: 12px; 
    padding: 18px 22px; border-top: 1px solid #eef0ee; 
}

@media(max-width:560px) {
    .k-form-grid { grid-template-columns: 1fr; }
}

/* ============ RESPONSIVE (tetap tabel) ============ */

/* Tablet & di bawahnya: kolom Nama dan Aksi dikunci (sticky) supaya
   identitas & tombol selalu terlihat saat tabel digeser */
@media (max-width: 1100px) {
    .k-table-user th.k-col-nama,
    .k-table-user td.k-user-nama {
        position: sticky; left: 0; z-index: 2; background: #fff;
    }
    .k-table-user th.k-col-aksi,
    .k-table-user td.k-user-aksi {
        position: sticky; right: 0; z-index: 2; background: #fff;
    }
    .k-table-user thead th.k-col-nama,
    .k-table-user thead th.k-col-aksi { z-index: 3; }
    .k-table-user td.k-user-nama { box-shadow: 1px 0 0 #eef0ee; }
    .k-table-user td.k-user-aksi { box-shadow: -1px 0 0 #eef0ee; }
    .k-table-user thead th.k-col-nama { box-shadow: 1px 0 0 #eef0ee; }
    .k-table-user thead th.k-col-aksi { box-shadow: -1px 0 0 #eef0ee; }
}

@media (max-width: 900px) {
    .k-head-row { flex-wrap: wrap; gap: 12px; }
    .k-user-tabs { overflow-x: auto; white-space: nowrap; }
    .k-user-tab { flex-shrink: 0; }
}

@media (max-width: 768px) {
    .k-head-row .k-btn { width: 100%; }
    .k-page-head { width: 100%; }

    /* lebarkan area tabel sedikit keluar dari padding kartu */
    .k-table-wrap { margin: 0 -10px; width: auto; }
    .k-table-user { min-width: 840px; }

    /* HP: hanya kolom Nama yang menempel; Aksi ikut tergeser
       (kalau dua-duanya menempel, kolom lain tidak kebagian ruang) */
    .k-table-user th.k-col-aksi,
    .k-table-user td.k-user-aksi {
        position: static; box-shadow: none;
    }
    .k-table-user thead th { padding: 12px 10px; font-size: 11px; }
    .k-table-user td { padding: 12px 10px; font-size: 13px; }

    /* lebar kolom dalam px agar stabil saat digeser */
    .k-col-nama   { width: 132px; }
    .k-col-kontak { width: 210px; }
    .k-col-role   { width: 110px; }
    .k-col-ket    { width: 120px; }
    .k-col-status { width: 90px;  }
    .k-col-tgl    { width: 110px; }
    .k-col-aksi   { width: 128px; }

    .k-avatar { width: 32px; height: 32px; font-size: 13px; }
    .k-profil-wrap { gap: 10px; align-items: center; }
    .k-nama-teks { padding-top: 0; font-size: 12.5px; line-height: 1.3; }
    .k-table-user td.k-user-nama { padding-left: 8px; padding-right: 6px; }
    .k-avatar { display: none; }
    .k-btn-mini { width: 30px; height: 30px; }
}

@media (max-width: 420px) {
    .k-modal-foot { flex-direction: column-reverse; }
    .k-modal-foot .k-btn, .k-modal-foot .k-btn-outline { width: 100%; }
}
</style>

<script>
// ---- Tab filter ----
document.querySelectorAll('.k-user-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.k-user-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        const target = tab.dataset.tab;
        document.querySelectorAll('.k-user-row').forEach(row => {
            const roles = row.dataset.roles.split(',');
            row.style.display = (target === 'semua' || roles.includes(target)) ? '' : 'none';
        });
    });
});

// ---- Keterangan toggle ----
function toggleKeterangan(){
    const konselorChecked = document.querySelector('.fRole[value="Konselor"]').checked;
    document.getElementById('wrapKeterangan').style.display = konselorChecked ? 'flex' : 'none';
}

// ---- Modal tambah ----
function bukaModalTambah(){
    document.getElementById('modalJudul').textContent = 'Tambah Akun';
    document.getElementById('formPengguna').action = "{{ route('pengguna.store') }}";
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('formPengguna').reset();
    document.getElementById('roleField').style.display = '';
    document.querySelectorAll('.fRole').forEach(r => r.required = true);
    toggleKeterangan();
    document.getElementById('hintPassword').style.display = 'block';
    document.getElementById('modalPengguna').style.display = 'flex';
}

// ---- Modal edit ----
function bukaModalEdit(p){
    document.getElementById('modalJudul').textContent = 'Edit Akun — ' + p.nama;
    document.getElementById('formPengguna').action = "{{ url('/koordinator/manajemen-pengguna') }}/" + p.id;
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('roleField').style.display = 'none';
    document.querySelectorAll('.fRole').forEach(r => { r.checked = false; r.required = false; });
    document.getElementById('fNama').value = p.nama;
    document.getElementById('fEmail').value = p.email;
    document.getElementById('fWa').value = p.wa;
    document.getElementById('fStatus').value = p.status;
    document.getElementById('fKeterangan').value = p.keterangan ?? '';
    toggleKeterangan();
    document.getElementById('hintPassword').style.display = 'none';
    document.getElementById('modalPengguna').style.display = 'flex';
}

function tutupModal(){
    document.getElementById('modalPengguna').style.display = 'none';
}
document.getElementById('modalPengguna').addEventListener('click', e => {
    if(e.target.id === 'modalPengguna') tutupModal();
});
</script>

@endsection