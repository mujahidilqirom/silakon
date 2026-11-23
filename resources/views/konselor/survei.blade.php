{{--
    SILAKON — Hasil Survei Saya (Konselor)
    Simpan sebagai: resources/views/konselor/survei.blade.php

    Data ($ringkasan, $distribusi, $responden) dari KonselorSurveiController@index (dummy).
    Halaman ini HANYA menampilkan hasil survei dari konseli yang ditangani konselor
    yang sedang login — penyaringannya dilakukan di controller, bukan di sini.
--}}
@extends('layouts.konselor')

@section('menu', 'survei')
@section('judul', 'Hasil Survei Saya')
@section('subjudul', 'Survei dari konseli yang Anda tangani')

@section('content')

<div class="sv-halaman">

    {{-- ============ JUDUL ============ --}}
    <div class="sv-atas">
        <h1>Hasil Survei Saya</h1>
        <p>Hasil survei layanan konseling, hanya dari konseli yang Anda tangani sendiri.</p>
    </div>

    {{-- ============ RINGKASAN ============ --}}
    <div class="sv-stats">
        <div class="kartu sv-stat tone-hijau">
            <b>{{ $ringkasan['total_mengisi'] }}</b>
            <span class="sv-stat-label">Survei Terisi</span>
            <span class="sv-stat-sub">dari {{ $ringkasan['total_selesai'] }} konseli sesi selesai</span>
        </div>
        <div class="kartu sv-stat tone-biru">
            <b>{{ $ringkasan['persen_respon'] }}%</b>
            <span class="sv-stat-label">Tingkat Respon</span>
            <span class="sv-stat-sub">konseli mengisi survei</span>
        </div>
        <div class="kartu sv-stat tone-ungu">
            <b>{{ $ringkasan['persen_puas'] }}%</b>
            <span class="sv-stat-label">Merasa Terbantu</span>
            <span class="sv-stat-sub">menjawab "Ya" pada survei</span>
        </div>
    </div>

    @if($ringkasan['total_mengisi'] > 0)

        {{-- ============ DISTRIBUSI JAWABAN ============ --}}
        <div class="sv-dist">
            @foreach($distribusi as $d)
                <div class="kartu sv-dist-kartu">
                    <h3>{{ $d['judul'] }}</h3>
                    @foreach($d['opsi'] as $o)
                        <div class="sv-opt">
                            <div class="sv-opt-atas">
                                <span>{{ $o['label'] }}</span>
                                <b>{{ $o['jumlah'] }} ({{ $o['persen'] }}%)</b>
                            </div>
                            <div class="sv-bar-track"><div class="sv-bar-fill" style="width:{{ $o['persen'] }}%;"></div></div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        {{-- ============ JAWABAN MASUK ============ --}}
        <div class="kartu sv-daftar-kartu">
            <div class="sv-daftar-atas">
                <h3>Jawaban Masuk</h3>
                <form method="GET" action="{{ route('konselor.survei') }}" class="sv-cari">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="search" name="q" value="{{ $kata }}" placeholder="Cari nama konseli..." autocomplete="off">
                </form>
            </div>

            <div class="sv-tabel-bungkus">
                <table class="sv-tabel" id="svTabel">
                    <thead>
                        <tr>
                            <th class="sv-no">No</th>
                            <th>Konseli</th>
                            <th>Jenis</th>
                            <th class="sv-c">Sesi</th>
                            <th>Diisi</th>
                            <th>Status</th>
                            <th class="sv-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($responden as $r)
                            <tr class="sv-baris" data-detail="{{ json_encode($r['detail']) }}" data-nama="{{ $r['nama'] }}">
                                <td class="sv-no">{{ $loop->iteration }}</td>
                                <td class="sv-nama"><b>{{ $r['nama'] }}</b></td>
                                <td><span class="lencana lencana-jenis">{{ $r['jenis'] }}</span></td>
                                <td class="sv-c">Ke-{{ $r['sesi'] }}</td>
                                <td class="sv-diisi">{{ $r['diisi'] }}</td>
                                <td>
                                    @if($r['puas'])
                                        <span class="lencana lencana-puas">Terbantu</span>
                                    @else
                                        <span class="lencana lencana-tidak-puas">Belum terbantu</span>
                                    @endif
                                </td>
                                <td class="sv-aksi">
                                    <button type="button" class="tombol-garis" data-aksi="lihat" title="Lihat jawaban" aria-label="Lihat jawaban {{ $r['nama'] }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <span class="tx">Lihat Jawaban</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="sv-kosong">Tidak ada jawaban survei yang cocok.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ============ JUMLAH DATA (VIEW BY) & PAGINATION ============ --}}
            @if(count($responden) > 0)
                <div class="sv-kaki">
                    <div class="sv-kaki-kiri">
                        <label class="sv-perhalaman">
                            Tampilkan
                            <select id="svPerHalaman" aria-label="Jumlah data per halaman">
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                            data
                        </label>
                        <span class="sv-info" id="svInfo"></span>
                    </div>
                    <nav class="sv-paginasi" id="svPaginasi" aria-label="Navigasi halaman"></nav>
                </div>
            @endif
        </div>

    @else
        <div class="kartu sv-kosong">Belum ada konseli Anda yang mengisi survei.</div>
    @endif

</div>

{{-- ============ MODAL: DETAIL JAWABAN ============ --}}
<div class="modal-overlay" id="svModalOverlay">
    <div class="modal-box" id="svModalBox">
        <div class="modal-kepala">
            <h3 id="svModalJudul">&nbsp;</h3>
            <button type="button" class="modal-tutup" id="svModalTutup" aria-label="Tutup">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-isi" id="svModalIsi"></div>
    </div>
</div>

<script>
(function () {
    var overlay = document.getElementById('svModalOverlay');
    var judul   = document.getElementById('svModalJudul');
    var isi     = document.getElementById('svModalIsi');

    function bukaModal(nama, detail) {
        judul.textContent = 'Jawaban Survei — ' + nama;

        var esc = function (t) { return String(t == null ? '' : t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); };
        var html = '';
        detail.forEach(function (qa) {
            html += '' +
                '<div class="sv-qa">' +
                    '<div class="sv-qa-tanya">' + esc(qa.tanya) + '</div>' +
                    '<div class="sv-qa-jawab">' + esc(qa.jawab) + '</div>' +
                '</div>';
        });
        isi.innerHTML = html;

        overlay.classList.add('tampil');
    }

    function tutupModal() {
        overlay.classList.remove('tampil');
        isi.innerHTML = '';
    }

    document.getElementById('svModalTutup').addEventListener('click', tutupModal);
    overlay.addEventListener('click', function (e) { if (e.target === overlay) tutupModal(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') tutupModal(); });

    document.querySelectorAll('.sv-baris [data-aksi="lihat"]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var baris = btn.closest('.sv-baris');
            bukaModal(baris.dataset.nama, JSON.parse(baris.dataset.detail));
        });
    });

    // ============ Pagination tabel (view by + nomor halaman) ============
    var barisTabel = Array.prototype.slice.call(document.querySelectorAll('#svTabel tbody tr.sv-baris'));
    var selPer  = document.getElementById('svPerHalaman');
    var nav     = document.getElementById('svPaginasi');
    var info    = document.getElementById('svInfo');
    var halaman = 1, perHalaman = 10;

    function daftarHalaman(aktif, total) {
        var set = {};
        [1, total, aktif - 1, aktif, aktif + 1].forEach(function (n) { if (n >= 1 && n <= total) set[n] = true; });
        var angka = Object.keys(set).map(Number).sort(function (a, b) { return a - b; });
        var hasil = [];
        angka.forEach(function (n, i) {
            if (i > 0 && n - angka[i - 1] > 1) hasil.push('...');
            hasil.push(n);
        });
        return hasil;
    }

    function renderTabel() {
        var total = barisTabel.length;
        if (!total || !nav) return;
        var totalHalaman = Math.max(1, Math.ceil(total / perHalaman));
        if (halaman > totalHalaman) halaman = totalHalaman;
        if (halaman < 1) halaman = 1;
        var awal = (halaman - 1) * perHalaman;
        var akhir = Math.min(awal + perHalaman, total);

        barisTabel.forEach(function (tr, i) {
            var tampil = i >= awal && i < akhir;
            tr.style.display = tampil ? '' : 'none';
            if (tampil) tr.querySelector('.sv-no').textContent = i + 1; // nomor berlanjut antar halaman
        });
        info.textContent = 'Menampilkan ' + (awal + 1) + '–' + akhir + ' dari ' + total + ' data';

        var html = '<button type="button" class="sv-pg" data-hal="' + (halaman - 1) + '" aria-label="Halaman sebelumnya"' + (halaman === 1 ? ' disabled' : '') + '>&lsaquo;</button>';
        daftarHalaman(halaman, totalHalaman).forEach(function (n) {
            if (n === '...') html += '<span class="sv-pg-titik">&hellip;</span>';
            else html += '<button type="button" class="sv-pg' + (n === halaman ? ' aktif' : '') + '" data-hal="' + n + '"' + (n === halaman ? ' aria-current="page"' : '') + '>' + n + '</button>';
        });
        html += '<button type="button" class="sv-pg" data-hal="' + (halaman + 1) + '" aria-label="Halaman berikutnya"' + (halaman === totalHalaman ? ' disabled' : '') + '>&rsaquo;</button>';
        nav.innerHTML = html;
    }

    if (nav) {
        nav.addEventListener('click', function (e) {
            var btn = e.target.closest('.sv-pg');
            if (!btn || btn.disabled) return;
            halaman = parseInt(btn.dataset.hal, 10);
            renderTabel();
        });
        selPer.addEventListener('change', function () {
            perHalaman = parseInt(selPer.value, 10);
            halaman = 1;
            renderTabel();
        });
        renderTabel();
    }
})();
</script>

<style>
    .sv-atas { margin-bottom: 20px; }
    .sv-atas h1 { font-size: 22px; margin: 0 0 4px; color: #16241d; }
    .sv-atas p { margin: 0; color: #6b7a72; font-size: 14px; }

    /* ============ Kartu statistik ============ */
    .sv-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 20px; }
    .sv-stat { padding: 20px 22px; }
    .sv-stat b { display: block; font-size: 28px; font-weight: 800; color: #16241d; line-height: 1.2; }
    .sv-stat-label { display: block; font-size: 13.5px; font-weight: 600; color: #33403a; margin-top: 4px; }
    .sv-stat-sub { display: block; font-size: 12px; color: #6b7a72; margin-top: 2px; }
    .sv-stat.tone-hijau { background: #d9f5e3; }
    .sv-stat.tone-biru  { background: #dfeafc; }
    .sv-stat.tone-ungu  { background: #e8e2fb; }

    /* ============ Distribusi ============ */
    .sv-dist { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 20px; }
    .sv-dist-kartu { padding: 22px; }
    .sv-dist-kartu h3 { margin: 0 0 16px; font-size: 14.5px; font-weight: 700; color: #16241d; line-height: 1.4; }
    .sv-opt + .sv-opt { margin-top: 14px; }
    .sv-opt-atas { display: flex; justify-content: space-between; gap: 10px; font-size: 13px; color: #33403a; margin-bottom: 6px; }
    .sv-opt-atas b { font-weight: 700; color: #16241d; flex-shrink: 0; }
    .sv-bar-track { background: #eef2ef; border-radius: 999px; height: 7px; overflow: hidden; }
    .sv-bar-fill { height: 100%; border-radius: 999px; background: #2f6b52; }

    /* ============ Daftar jawaban (TABEL) ============ */
    .sv-daftar-kartu { padding: 0; overflow: hidden; }
    .sv-daftar-atas { display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; padding: 20px 22px 16px; }
    .sv-daftar-atas h3 { margin: 0; font-size: 15.5px; font-weight: 700; color: #16241d; }

    .sv-cari { position: relative; max-width: 280px; flex: 1; min-width: 200px; }
    .sv-cari svg { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; color: #8a978f; }
    .sv-cari input {
        width: 100%; height: 40px; padding: 0 12px 0 36px; border: 1px solid #dfe6e1; border-radius: 10px;
        font: inherit; font-size: 13.5px; color: #16241d; background: #fff;
    }
    .sv-cari input:focus { outline: none; border-color: #2f6b52; }

    .sv-tabel-bungkus { overflow: visible; } /* desktop: tanpa scrollbar samping */
    .sv-tabel { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 13px; }
    .sv-tabel thead th {
        text-align: left; font-size: 11.5px; text-transform: uppercase; letter-spacing: .03em;
        color: #8a978f; font-weight: 600; padding: 12px 10px; white-space: nowrap;
        border-top: 1px solid #e6ece8; border-bottom: 1px solid #e6ece8; background: #fafbfa;
    }
    .sv-tabel tbody td { padding: 13px 10px; border-bottom: 1px solid #eef2ef; vertical-align: middle; color: #33403a; overflow-wrap: anywhere; }
    .sv-tabel th:first-child, .sv-tabel td:first-child { padding-left: 14px; }
    .sv-tabel th:last-child, .sv-tabel td:last-child { padding-right: 22px; }
    .sv-tabel tbody tr:hover { background: #f7faf8; }
    .sv-no { width: 52px; text-align: center; color: #8a978f; font-weight: 600; }
    .sv-c { text-align: center; }
    .sv-aksi { text-align: right; white-space: nowrap; }
    .sv-nama b { font-size: 13.5px; color: #16241d; }
    .sv-diisi { white-space: nowrap; }
    .sv-tabel td.sv-kosong { text-align: center; padding: 34px 12px; border-bottom: 0; }

    /* ============ Footer tabel: view by & pagination ============ */
    .sv-kaki { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; padding: 14px 22px; font-size: 12.5px; color: #5c6b63; }
    .sv-kaki-kiri { display: flex; align-items: center; flex-wrap: wrap; gap: 8px 16px; }
    .sv-perhalaman { display: inline-flex; align-items: center; gap: 6px; }
    .sv-perhalaman select { height: 32px; padding: 0 8px; border: 1px solid #dfe6e1; border-radius: 8px; background: #fff; color: #33403a; font-size: 12.5px; font-weight: 600; cursor: pointer; }
    .sv-perhalaman select:focus { outline: none; border-color: #2f6b52; }
    .sv-paginasi { display: flex; align-items: center; gap: 4px; flex-wrap: wrap; }
    .sv-pg { min-width: 32px; height: 32px; padding: 0 8px; border: 1px solid #dfe6e1; border-radius: 8px; background: #fff; color: #33403a; font-size: 12.5px; font-weight: 600; cursor: pointer; transition: background .15s, border-color .15s; }
    .sv-pg:hover:not(:disabled):not(.aktif) { border-color: #bcd0c6; background: #f2f8f4; }
    .sv-pg.aktif { background: #2f6b52; border-color: #2f6b52; color: #fff; cursor: default; }
    .sv-pg:disabled { opacity: .45; cursor: not-allowed; }
    .sv-pg-titik { padding: 0 4px; color: #8a978f; }

    .lencana { display: inline-flex; align-items: center; padding: 3px 12px; border-radius: 999px; font-size: 11.5px; font-weight: 700; white-space: nowrap; }
    .lencana-jenis { background: #eceeec; color: #4b5350; }
    .lencana-puas { background: #dff3e6; color: #1f7a4d; }
    .lencana-tidak-puas { background: #fdeaea; color: #b91c1c; }

    .sv-kosong { text-align: center; color: #8a978f; font-size: 13.5px; padding: 34px 12px; }

    .tombol-garis {
        display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px; border-radius: 8px;
        font-size: 12.5px; font-weight: 600; cursor: pointer; white-space: nowrap;
        background: #fff; color: #2f6b52; border: 1px solid #bcd9c9;
    }
    .tombol-garis svg { width: 14px; height: 14px; }
    .tombol-garis:hover { background: #f2f8f4; }

    /* ============ Modal (pola sama dengan halaman Kasus) ============ */
    .modal-overlay {
        position: fixed; inset: 0; background: rgba(16, 24, 20, .45);
        display: flex; align-items: center; justify-content: center;
        opacity: 0; visibility: hidden; transition: opacity .15s; z-index: 1000; padding: 20px;
    }
    .modal-overlay.tampil { opacity: 1; visibility: visible; }
    .modal-box { background: #fff; border-radius: 14px; width: 100%; max-width: 520px; max-height: 85vh; overflow-y: auto; box-shadow: 0 20px 50px rgba(16,24,20,.25); }
    .modal-kepala { display: flex; align-items: center; justify-content: space-between; padding: 18px 20px; border-bottom: 1px solid #eef2ef; position: sticky; top: 0; background: #fff; }
    .modal-kepala h3 { margin: 0; font-size: 16px; color: #16241d; }
    .modal-tutup { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 8px; border: none; background: #f2f5f3; color: #5c6b63; cursor: pointer; }
    .modal-tutup svg { width: 14px; height: 14px; }
    .modal-tutup:hover { background: #e6ece8; }
    .modal-isi { padding: 20px; }

    .sv-qa + .sv-qa { margin-top: 16px; }
    .sv-qa-tanya { font-size: 12.5px; color: #8a978f; margin-bottom: 3px; }
    .sv-qa-jawab { font-size: 14px; color: #16241d; line-height: 1.55; }

    /* =====================================================================
       RESPONSIF — daftar jawaban tetap TABEL
       ===================================================================== */
    @media (max-width: 1100px) {
        .sv-tabel-bungkus {
            overflow-x: auto; -webkit-overflow-scrolling: touch; overscroll-behavior-x: contain;
            background:
                linear-gradient(to right, #fff 30%, rgba(255,255,255,0)) left center,
                linear-gradient(to left,  #fff 30%, rgba(255,255,255,0)) right center,
                radial-gradient(farthest-side at 0 50%,  rgba(0,0,0,.12), rgba(0,0,0,0)) left center,
                radial-gradient(farthest-side at 100% 50%, rgba(0,0,0,.12), rgba(0,0,0,0)) right center;
            background-repeat: no-repeat;
            background-size: 28px 100%, 28px 100%, 10px 100%, 10px 100%;
            background-attachment: local, local, scroll, scroll;
        }
        .sv-tabel { min-width: 800px; }
        .sv-tabel td.sv-nama { min-width: 150px; }
        /* hanya kolom Aksi yang menempel di kanan saat digeser */
        .sv-tabel th.sv-aksi, .sv-tabel td.sv-aksi { position: sticky; right: 0; z-index: 1; background: #fff; box-shadow: -1px 0 0 #eef2ef; }
        .sv-tabel thead th.sv-aksi { background: #fafbfa; }
        .sv-tabel tbody tr:hover td.sv-aksi { background: #f7faf8; }
        .sv-tabel td.sv-kosong { position: static; box-shadow: none; }
    }

    @media (max-width: 900px) {
        .sv-dist { grid-template-columns: 1fr; gap: 12px; }
    }

    @media (max-width: 760px) {
        /* ringkasan: tetap 3 kartu sebaris */
        .sv-stats { gap: 10px; margin-bottom: 14px; }
        .sv-stat { padding: 12px 10px; }
        .sv-stat b { font-size: 22px; }
        .sv-stat-label { font-size: 12px; line-height: 1.3; }
        .sv-stat-sub { font-size: 10.5px; line-height: 1.35; }
    }

    @media (max-width: 720px) {
        .sv-atas { margin-bottom: 14px; }
        .sv-atas h1 { font-size: 20px; }
        .sv-dist { margin-bottom: 14px; }
        .sv-dist-kartu { padding: 16px; }
        .sv-dist-kartu h3 { margin-bottom: 12px; }

        .sv-daftar-atas { padding: 16px 14px 12px; }
        .sv-cari { max-width: none; flex: 1 1 100%; }
        .sv-tabel { min-width: 700px; font-size: 12.5px; }
        .sv-tabel thead th { padding: 10px 8px; font-size: 10.5px; }
        .sv-tabel tbody td { padding: 11px 8px; }
        .sv-tabel th:last-child, .sv-tabel td:last-child { padding-right: 12px; }

        /* tombol Aksi jadi ikon saja agar kolom menempel tidak memakan tempat */
        .tombol-garis { width: 36px; height: 36px; padding: 0; justify-content: center; }
        .tombol-garis .tx { display: none; }
        .tombol-garis svg { width: 16px; height: 16px; }

        .sv-kaki { flex-direction: column; align-items: stretch; gap: 10px; padding: 12px 14px; }
        .sv-kaki-kiri { justify-content: space-between; }
        .sv-paginasi { justify-content: center; }
        .sv-pg { min-width: 34px; height: 34px; }
        .sv-perhalaman select { height: 34px; }

        /* modal */
        .modal-overlay { padding: 12px; }
        .modal-box { max-height: 90vh; max-height: 90dvh; border-radius: 12px; }
        .modal-kepala { padding: 14px 16px; }
        .modal-kepala h3 { font-size: 15px; min-width: 0; overflow-wrap: anywhere; }
        .modal-isi { padding: 16px; }
    }

    @media (max-width: 420px) {
        .sv-stats { gap: 8px; }
        .sv-stat { padding: 10px 8px; }
        .sv-stat b { font-size: 20px; }
        .sv-stat-label { font-size: 11.5px; }
        .sv-stat-sub { font-size: 10px; }
    }
</style>

@endsection