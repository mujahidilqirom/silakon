{{--
    SILAKON — Catatan Konseling (Koordinator)
    Simpan sebagai: resources/views/koordinator/catatan-konseling.blade.php
    Route contoh:  Route::get('/koordinator/catatan-konseling', [KoordinatorController::class, 'catatan'])->name('koordinator.catatan');

    Data ($catatan) dikirim dari KoordinatorController@catatan (masih dummy).
    Satu baris tabel = satu konseli (dikenali lewat NIM). Kode tiket, konselor, dan kategori
    ada di detail per sesi, karena tiap sesi bisa ditangani konselor yang berbeda.
    Kolom Konselor/Kategori/Tgl Sesi di tabel menampilkan sesi terakhir.
    Tombol "Baca" membuka modal berisi seluruh sesi + catatannya.
    Subjudul tidak di-override, jadi otomatis memakai tanggal hari ini dari navbar.blade.php.
--}}
@extends('layouts.koordinator')

@section('judul', 'Catatan Konseling')

@section('content')

<style>
    .ctn {
        --ctn-primary: #7a9c7c;
        --ctn-primary-dark: #4d7a55;
        --ctn-ink: #1a2321;
        --ctn-muted: #7d8582;
        --ctn-line: #eceeea;
    }

    /* Header */
    .ctn-head { margin-bottom: 24px; }
    .ctn-head h1 { margin: 0; font-size: 22px; font-weight: 700; color: var(--ctn-ink); }
    .ctn-head p { margin: 6px 0 0; font-size: 14px; color: var(--ctn-muted); }

    /* Catatan */
    .ctn-note-bar {
        margin-bottom: 24px; padding: 14px 20px;
        background: #fdf1c3; border: 1px solid #f3e1a0; border-radius: 12px;
        font-size: 13px; line-height: 1.6; color: #8a3f0b;
    }

    /* Pencarian */
    .ctn-search { position: relative; width: min(400px, 100%); margin-bottom: 20px; }
    .ctn-search svg {
        position: absolute; left: 16px; top: 50%; width: 16px; height: 16px;
        transform: translateY(-50%); color: #a3a9a6; pointer-events: none;
    }
    .ctn-search input {
        display: block; width: 100%; height: 46px; padding: 0 16px 0 42px;
        font: inherit; font-size: 14px; color: var(--ctn-ink);
        background: #fff; border: 1px solid #dfe3de; border-radius: 12px;
    }
    .ctn-search input::placeholder { color: #a3a9a6; }
    .ctn-search input:focus-visible { outline: 2px solid var(--ctn-primary); outline-offset: 1px; }

    /* Tabel */
    .ctn-tablewrap {
        background: #fff; border: 1px solid #e3e7e1; border-radius: 16px;
        overflow-x: auto;
    }
    .ctn-table { width: 100%; border-collapse: collapse; min-width: 860px; }
    .ctn-table th {
        padding: 14px 16px; text-align: left; white-space: nowrap;
        background: #f6f8f5; font-size: 12px; font-weight: 700;
        letter-spacing: .06em; text-transform: uppercase; color: #6b7370;
    }
    .ctn-table td {
        padding: 0 16px; height: 60px; border-top: 1px solid var(--ctn-line);
        font-size: 13px; color: #4a524f; vertical-align: middle; white-space: nowrap;
    }
    .ctn-table tbody tr:first-child td { border-top: 0; }
    .ctn-table tbody tr:hover td { background: #fafbf9; }

    .ctn-nim { display: inline-flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: var(--ctn-primary-dark); }
    .ctn-alert {
        display: inline-flex; align-items: center; justify-content: center; flex: none;
        width: 20px; height: 20px; border-radius: 50%;
        background: #fde2e2; color: #dc2626; font-size: 11px; font-weight: 800;
    }
    .ctn-name { font-size: 14px; font-weight: 700; color: var(--ctn-ink); }
    .ctn-date { display: inline-flex; align-items: center; gap: 6px; }
    .ctn-date svg { width: 13px; height: 13px; color: #9aa19e; }

    .ctn-badge {
        display: inline-block; padding: 4px 11px; border-radius: 999px;
        font-size: 11px; font-weight: 700;
    }
    .ctn-badge-final { background: #d9f5e3; color: #1f6b3f; }
    .ctn-badge-draft { background: #fdefb9; color: #8a3f0b; }

    .ctn-none { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; color: #a3a9a6; }
    .ctn-none svg { width: 13px; height: 13px; }

    /* Tombol */
    .ctn-btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 14px; border-radius: 8px; border: 1px solid transparent;
        font: inherit; font-size: 12px; font-weight: 600; cursor: pointer;
        transition: background .15s, border-color .15s;
    }
    .ctn-btn svg { width: 14px; height: 14px; }
    .ctn-btn:focus-visible { outline: 2px solid var(--ctn-primary-dark); outline-offset: 2px; }
    .ctn-btn-soft { background: #eef5ee; border-color: #b8ceb9; color: var(--ctn-primary-dark); }
    .ctn-btn-soft:hover { background: #e2eee3; border-color: var(--ctn-primary); }

    .ctn-empty {
        padding: 36px 20px; text-align: center; font-size: 14px; color: var(--ctn-muted);
        background: #fff; border: 1px dashed #d8ddd6; border-radius: 16px;
    }
    .ctn-empty[hidden] { display: none; }

    /* Modal detail catatan */
    .ctn-modal {
        width: min(680px, calc(100vw - 32px)); max-height: calc(100vh - 32px);
        padding: 0; border: 0; border-radius: 20px; background: #fff; color: var(--ctn-ink);
        box-shadow: 0 24px 64px rgba(0, 0, 0, .28); overflow: hidden;
    }
    .ctn-modal[open] { display: flex; flex-direction: column; }
    .ctn-modal::backdrop { background: rgba(38, 42, 40, .5); }

    .ctn-modal-head {
        display: flex; align-items: flex-start; justify-content: space-between; gap: 16px;
        padding: 24px 28px 18px; border-bottom: 1px solid var(--ctn-line);
    }
    .ctn-modal-head h2 { margin: 0; font-size: 18px; font-weight: 700; }
    .ctn-modal-close {
        display: inline-flex; align-items: center; justify-content: center; flex: none;
        width: 34px; height: 34px; margin: -4px -6px 0 0; padding: 0;
        background: transparent; border: 0; border-radius: 8px; color: #6b7370; cursor: pointer;
    }
    .ctn-modal-close:hover { background: #f0f3f0; }
    .ctn-modal-close:focus-visible { outline: 2px solid var(--ctn-primary-dark); outline-offset: 1px; }
    .ctn-modal-close svg { width: 18px; height: 18px; }

    .ctn-modal-body { padding: 20px 28px 8px; overflow-y: auto; }
    .ctn-modal-foot { padding: 16px 28px 24px; border-top: 1px solid var(--ctn-line); }
    .ctn-modal-foot .ctn-btn { width: 100%; justify-content: center; padding: 12px 16px; font-size: 15px; }
    .ctn-btn-outline { background: #fff; border-color: var(--ctn-primary); color: var(--ctn-primary-dark); }
    .ctn-btn-outline:hover { background: #eef5ee; }

    .ctn-who { display: flex; align-items: baseline; flex-wrap: wrap; gap: 4px 10px; margin: 0 0 18px; font-size: 14px; color: var(--ctn-muted); }
    .ctn-who strong { font-size: 16px; color: var(--ctn-ink); }
    .ctn-who span + span::before { content: "·"; margin-right: 10px; }

    .ctn-sesi-list { display: flex; flex-direction: column; gap: 14px; margin: 0; padding: 0; list-style: none; }
    .ctn-sesi { padding: 18px 20px; background: #f7f8f7; border: 1px solid #e9ece8; border-radius: 14px; }
    .ctn-sesi-head { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .ctn-sesi-head h3 { margin: 0; font-size: 15px; font-weight: 700; }
    .ctn-sesi-head .ctn-date { margin-left: auto; font-size: 13px; color: var(--ctn-muted); }
    .ctn-tag-darurat {
        padding: 2px 8px; border-radius: 999px; background: #fdefb9; border: 1px solid #f3d98a;
        font-size: 11px; font-weight: 700; color: #8a3f0b;
    }

    .ctn-facts { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px 16px; margin: 14px 0 0; }
    .ctn-facts dt { margin: 0 0 2px; font-size: 12px; color: var(--ctn-muted); }
    .ctn-facts dd { margin: 0; font-size: 13px; font-weight: 600; color: var(--ctn-ink); overflow-wrap: anywhere; }
    .ctn-facts dd.ctn-code { color: var(--ctn-primary-dark); }

    .ctn-note { margin-top: 14px; padding-top: 14px; border-top: 1px solid #e3e7e1; }
    .ctn-note-label { margin: 0 0 6px; font-size: 12px; font-weight: 600; color: var(--ctn-muted); }
    .ctn-note-text { margin: 0; font-size: 14px; line-height: 1.65; color: #2c3532; white-space: pre-line; }
    .ctn-note-empty { margin: 0; font-size: 13px; font-style: italic; color: #a3a9a6; }

    @media (max-width: 720px) {
        .ctn-facts { grid-template-columns: 1fr; }
        .ctn-modal-head, .ctn-modal-body, .ctn-modal-foot { padding-left: 20px; padding-right: 20px; }
        .ctn-sesi-head .ctn-date { margin-left: 0; width: 100%; }
    }
</style>

<div class="ctn">

    {{-- ============ JUDUL ============ --}}
    <div class="ctn-head">
        <h1>Catatan Konseling</h1>
        <p>Halaman ini sudah tidak digunakan untuk membuka isi catatan konseling. Koordinator hanya melihat ringkasan pengajuan dan statistik.</p>
    </div>

    {{-- ============ CATATAN ============ --}}
    <div class="ctn-note-bar">
        Isi catatan konseling bersifat rahasia dan hanya dapat diakses konselor yang menangani.
    </div>

    {{-- ============ PENCARIAN ============ --}}
    <div class="ctn-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="search" id="ctn-cari" placeholder="Cari konseli, NIM, atau konselor..." aria-label="Cari catatan konseling" autocomplete="off">
    </div>

    {{-- ============ TABEL ============ --}}
    @if(count($catatan) > 0)
        <div class="ctn-tablewrap">
            <table class="ctn-table" id="ctn-table">
                <thead>
                    <tr>
                        <th scope="col">NIM</th>
                        <th scope="col">Konseli</th>
                        <th scope="col">Konselor</th>
                        <th scope="col">Kategori</th>
                        <th scope="col">Tgl Sesi</th>
                        <th scope="col">Catatan</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($catatan as $i => $c)
                        @php
                            $sesi      = collect($c['sesi']);
                            $terakhir  = $sesi->last();
                            $adaCatatan = $sesi->contains(fn ($s) => !empty($s['catatan']));
                            $adaDarurat = $sesi->contains(fn ($s) => !empty($s['darurat']));
                            $cari = strtolower($c['nim'].' '.$c['nama'].' '.$sesi->pluck('konselor')->unique()->implode(' '));
                        @endphp
                        <tr data-cari="{{ $cari }}">
                            <td>
                                <span class="ctn-nim">
                                    {{ $c['nim'] }}
                                    @if($adaDarurat)
                                        <span class="ctn-alert" title="Ada sesi kasus mendesak" aria-label="Ada sesi kasus mendesak">!</span>
                                    @endif
                                </span>
                            </td>
                            <td><span class="ctn-name">{{ $c['nama'] }}</span></td>
                            <td>{{ $terakhir['konselor'] }}</td>
                            <td>{{ $terakhir['kategori'] }}</td>
                            <td>
                                <span class="ctn-date">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                    {{ $terakhir['tgl'] }}
                                </span>
                            </td>
                            <td>
                                @if($adaCatatan)
                                    <span class="ctn-badge ctn-badge-final">Final</span>
                                @else
                                    <span class="ctn-badge ctn-badge-draft">Belum Diisi</span>
                                @endif
                            </td>
                            <td>
                                @if($adaCatatan)
                                    <button type="button" class="ctn-btn ctn-btn-soft" data-baca="{{ $i }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        Baca
                                    </button>
                                @else
                                    <span class="ctn-none">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2Z"/><path d="M14 2v6h6"/></svg>
                                        Belum ada
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="ctn-empty" id="ctn-kosong" hidden style="margin-top:16px">Tidak ada catatan yang cocok dengan pencarian.</div>
    @else
        <div class="ctn-empty">Belum ada catatan konseling.</div>
    @endif

    {{-- ============ DETAIL PER KONSELI (disalin ke modal saat "Baca" diklik) ============ --}}
    @foreach($catatan as $i => $c)
        <template id="ctn-tpl-{{ $i }}">
            <p class="ctn-who">
                <strong>{{ $c['nama'] }}</strong>
                <span>NIM {{ $c['nim'] }}</span>
                <span>{{ count($c['sesi']) }} sesi</span>
            </p>

            <ol class="ctn-sesi-list">
                @foreach($c['sesi'] as $n => $s)
                    <li class="ctn-sesi">
                        <div class="ctn-sesi-head">
                            <h3>Sesi {{ $n + 1 }}</h3>
                            @if(!empty($s['darurat']))
                                <span class="ctn-tag-darurat">Mendesak</span>
                            @endif
                            <span class="ctn-date">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                {{ $s['tgl'] }}
                            </span>
                        </div>

                        <dl class="ctn-facts">
                            <div><dt>Konselor</dt><dd>{{ $s['konselor'] }}</dd></div>
                            <div><dt>Kategori masalah</dt><dd>{{ $s['kategori'] }}</dd></div>
                            <div><dt>Kode tiket</dt><dd class="ctn-code">{{ $s['kode'] }}</dd></div>
                        </dl>

                        <div class="ctn-note">
                            <p class="ctn-note-label">Catatan</p>
                            @if(!empty($s['catatan']))
                                <p class="ctn-note-text">{{ $s['catatan'] }}</p>
                            @else
                                <p class="ctn-note-empty">Konselor belum mengisi catatan untuk sesi ini.</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        </template>
    @endforeach

    {{-- ============ MODAL BACA CATATAN ============ --}}
    <dialog class="ctn-modal" id="ctn-modal" aria-labelledby="ctn-modal-title">
        <div class="ctn-modal-head">
            <h2 id="ctn-modal-title">Catatan Konseling</h2>
            <button type="button" class="ctn-modal-close" id="ctn-tutup-x" aria-label="Tutup">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <div class="ctn-modal-body" id="ctn-modal-body"></div>
        <div class="ctn-modal-foot">
            <button type="button" class="ctn-btn ctn-btn-outline" id="ctn-tutup">Tutup</button>
        </div>
    </dialog>
</div>

<script>
(function () {
    const table = document.getElementById('ctn-table');
    const modal = document.getElementById('ctn-modal');
    if (!table || !modal) return;

    const body   = document.getElementById('ctn-modal-body');
    const cari   = document.getElementById('ctn-cari');
    const kosong = document.getElementById('ctn-kosong');
    let pemicu = null;

    // Pencarian: konseli, NIM, atau konselor (semua konselor di seluruh sesi)
    cari.addEventListener('input', function () {
        const q = cari.value.trim().toLowerCase();
        let tampil = 0;
        table.querySelectorAll('tbody tr').forEach(function (tr) {
            const cocok = tr.dataset.cari.includes(q);
            tr.hidden = !cocok;
            if (cocok) tampil++;
        });
        kosong.hidden = tampil > 0;
    });

    // Klik "Baca" -> isi modal dengan detail semua sesi konseli tersebut
    table.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-baca]');
        if (!btn) return;

        const tpl = document.getElementById('ctn-tpl-' + btn.dataset.baca);
        if (!tpl) return;

        pemicu = btn;
        body.replaceChildren(tpl.content.cloneNode(true));
        body.scrollTop = 0;
        modal.showModal();

        // Frontend saja. Nanti catat akses ke server sebelum/sesudah modal dibuka
        // (mis. POST /koordinator/catatan-konseling/{nim}/akses) agar masuk ke Log Aktivitas.
    });

    // Tutup: tombol, Esc, atau klik di luar modal
    document.getElementById('ctn-tutup').addEventListener('click', () => modal.close());
    document.getElementById('ctn-tutup-x').addEventListener('click', () => modal.close());
    modal.addEventListener('click', e => { if (e.target === modal) modal.close(); });
    modal.addEventListener('close', function () {
        body.replaceChildren();
        if (pemicu && document.contains(pemicu)) pemicu.focus();
    });
})();
</script>
@endsection
