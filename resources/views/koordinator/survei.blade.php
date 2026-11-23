{{--
    SILAKON — Hasil Survei (Koordinator)
    Simpan sebagai: resources/views/koordinator/survei.blade.php
    Data dikirim dari KoordinatorSurveiController@index (dummy).
    Halaman ini hanya MELIHAT hasil — generate/kirim link ada di sisi Admin.
--}}
@extends('layouts.koordinator')

@section('judul', 'Hasil Survei')
@section('subjudul', 'Hasil survei layanan konseling LPPP UNNES')

@section('content')

<style>
    /* ===== Halaman Hasil Survei ===== */
    .hs-page{ padding-bottom:8px; }

    .hs-head-row{
        display:flex;align-items:flex-start;justify-content:space-between;
        flex-wrap:wrap;gap:14px;margin-bottom:24px;
    }
    .hs-head-row h1{margin:0;font-size:22px;font-weight:700;color:#1f2a24;}
    .hs-head-row p{margin:6px 0 0;color:#6b7a72;font-size:13.5px;}

    /* Toolbar filter + ekspor dalam satu baris */
    .hs-toolbar-row{
        display:flex;align-items:center;justify-content:space-between;gap:12px;
        flex-wrap:wrap;margin-bottom:6px;
    }
    .hs-toolbar-form{display:flex;align-items:center;gap:8px;flex-wrap:wrap;flex:1 1 auto;min-width:0;}
    .hs-toolbar-export{display:flex;align-items:center;gap:8px;flex-wrap:nowrap;flex-shrink:0;margin-left:auto;}

    .hs-select{
        border:1px solid #d8dfda;border-radius:10px;padding:9px 30px 9px 12px;
        font-size:13px;color:#1f2a24;background:#fff;font-family:inherit;
        cursor:pointer;outline:none;appearance:none;max-width:170px;
        background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%235c6a62' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
        background-repeat:no-repeat;background-position:right 10px center;
    }
    .hs-select:focus{border-color:#4d7c5f;}

    .hs-daterange{display:flex;align-items:center;gap:6px;}
    .hs-date{
        border:1px solid #d8dfda;border-radius:10px;padding:8px 10px;
        font-size:13px;color:#1f2a24;background:#fff;font-family:inherit;outline:none;max-width:140px;
    }
    .hs-date:focus{border-color:#4d7c5f;}
    .hs-date-to{color:#8a978f;font-size:12.5px;}

    .hs-btn{
        display:inline-flex;align-items:center;gap:7px;
        padding:9px 16px;border-radius:10px;font-size:13px;font-weight:600;
        cursor:pointer;font-family:inherit;white-space:nowrap;text-decoration:none;
        border:1px solid transparent;flex-shrink:0;
    }
    .hs-btn svg{width:14px;height:14px;}
    .hs-btn-outline{background:#fff;border-color:#d8dfda;color:#3a463f;}
    .hs-btn-outline:hover{background:#f4f7f4;}
    .hs-btn-fill{background:#5f8f6c;color:#fff;}
    .hs-btn-fill:hover{background:#4d7c5f;}

    .hs-periode{font-size:12.5px;color:#8a978f;margin:0 0 20px;}

    @media (max-width:960px){
        .hs-toolbar-row{flex-direction:column;align-items:stretch;}
        .hs-toolbar-export{justify-content:flex-end;}
    }

    /* Kartu statistik atas */
    .hs-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px;}
    @media (max-width:900px){ .hs-stats{grid-template-columns:repeat(2,1fr);} }
    @media (max-width:480px){ .hs-stats{grid-template-columns:1fr;} }
    .hs-stat{border-radius:16px;padding:20px 22px;}
    .hs-stat b{display:block;font-size:28px;font-weight:800;color:#1f2a24;line-height:1.2;}
    .hs-stat .hs-stat-label{display:block;font-size:13.5px;font-weight:600;color:#33403a;margin-top:4px;}
    .hs-stat .hs-stat-sub{display:block;font-size:12px;color:#6b7a72;margin-top:2px;}
    .hs-stat.tone-hijau{background:#d9f5e3;}
    .hs-stat.tone-biru{background:#dfeafc;}
    .hs-stat.tone-ungu{background:#e8e2fb;}
    .hs-stat.tone-kuning{background:#fdf1c2;}

    /* Grid dua kolom */
    .hs-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px;}
    @media (max-width:960px){ .hs-grid-2{grid-template-columns:1fr;} }

    .hs-card{
        background:#fff;border:1px solid #e1e7e3;border-radius:18px;
        box-shadow:0 2px 8px rgba(31,42,36,.04);padding:22px 22px 20px;
    }
    .hs-card-title{margin:0 0 18px;font-size:15.5px;font-weight:700;color:#1f2a24;}
    .hs-card-title small{font-size:12.5px;font-weight:500;color:#8a978f;}

    /* Distribusi 3 kartu */
    .hs-dist{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:18px;}
    @media (max-width:960px){ .hs-dist{grid-template-columns:1fr;} }
    .hs-opt + .hs-opt{margin-top:14px;}
    .hs-opt-top{display:flex;justify-content:space-between;gap:10px;font-size:13px;color:#33403a;margin-bottom:6px;}
    .hs-opt-top b{font-weight:700;color:#1f2a24;flex-shrink:0;}
    .hs-bar-track{background:#eef1ee;border-radius:999px;height:7px;overflow:hidden;}
    .hs-bar-fill{height:100%;border-radius:999px;background:#5f8f6c;}

    /* Tabel performa konselor */
    .hs-table-wrap{overflow-x:auto;}
    .hs-table{width:100%;border-collapse:collapse;font-size:13.5px;color:#4b5a51;}
    .hs-table thead th{
        text-align:left;font-size:11.5px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;
        color:#8a978f;padding:0 10px 12px;white-space:nowrap;
    }
    .hs-table thead th:first-child{padding-left:0;}
    .hs-table tbody td{padding:14px 10px;border-top:1px solid #eef1ee;vertical-align:middle;}
    .hs-table tbody td:first-child{padding-left:0;font-weight:700;color:#1f2a24;}
    .hs-table tbody tr:first-child td{border-top:none;}
    .hs-table tbody tr[hidden]{display:none;}
    .hs-pct-good{color:#1f7a44;font-weight:700;}
    .hs-pct-bad{color:#b91c1c;font-weight:700;}
    .hs-small{font-size:12.5px;color:#8a978f;}

    .hs-pill{display:inline-block;padding:4px 12px;border-radius:999px;font-size:12px;font-weight:600;}
    .hs-pill.jenis{background:#eceeec;color:#4b5350;}
    .hs-pill.tone-hijau{background:#d9f5e3;color:#1f7a4a;}
    .hs-pill.tone-merah{background:#fdeaea;color:#b91c1c;}

    /* Toolbar daftar jawaban */
    .hs-toolbar{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:16px;}
    .hs-search{position:relative;max-width:320px;flex:1;min-width:220px;}
    .hs-search svg{position:absolute;left:14px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#8a978f;}
    .hs-search input{
        width:100%;height:44px;padding:0 14px 0 38px;border:1px solid #d8dfda;border-radius:10px;
        font:inherit;font-size:13.5px;color:#1f2a24;background:#fff;
    }
    .hs-search input:focus{outline:none;border-color:#4d7c5f;}
    .hs-count{font-size:12.5px;color:#8a978f;white-space:nowrap;}

    .hs-btn-lihat{
        display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:999px;
        border:1px solid #b7d0bd;background:#fff;color:#3f6a4f;font:inherit;font-size:12.5px;font-weight:600;cursor:pointer;
    }
    .hs-btn-lihat:hover{background:#f2f7f2;}

    .hs-empty{padding:44px 16px;text-align:center;color:#8a978f;}

    /* Modal detail */
    .hs-overlay{
        position:fixed;inset:0;background:rgba(20,30,25,.45);
        display:none;align-items:center;justify-content:center;padding:20px;z-index:50;
    }
    .hs-overlay.is-open{display:flex;}
    .hs-modal{
        width:100%;max-width:540px;max-height:calc(100vh - 40px);overflow-y:auto;
        background:#fff;border-radius:20px;padding:28px;box-shadow:0 20px 50px rgba(20,30,25,.25);
    }
    .hs-modal-head{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:4px;}
    .hs-modal-head h2{margin:0;font-size:18px;font-weight:700;color:#1f2a24;}
    .hs-close{border:none;background:transparent;cursor:pointer;color:#8a978f;width:28px;height:28px;display:grid;place-items:center;border-radius:8px;}
    .hs-close:hover{background:#f4f7f4;color:#1f2a24;}
    .hs-close svg{width:18px;height:18px;}
    .hs-desc{margin:0 0 18px;font-size:13.5px;color:#4b5a51;line-height:1.5;}
    .hs-desc b{color:#1f2a24;}

    .hs-qa-list{margin:0;}
    .hs-qa + .hs-qa{margin-top:16px;}
    .hs-qa dt{font-size:12.5px;color:#8a978f;margin-bottom:3px;}
    .hs-qa dd{margin:0;font-size:14px;color:#1f2a24;line-height:1.5;}

    .hs-modal-foot{display:flex;justify-content:flex-end;margin-top:22px;}
    .hs-btn-tutup{background:#fff;border:1px solid #d8dfda;color:#1f2a24;height:42px;border-radius:10px;padding:0 22px;font:inherit;font-size:13.5px;font-weight:600;cursor:pointer;}
    .hs-btn-tutup:hover{border-color:#4d7c5f;}

    /* =====================================================================
       RESPONSIF HP — filter dirapikan, statistik 2x2, tabel tetap tabel
       ===================================================================== */

    @media (max-width:640px){
        .hs-head-row{gap:10px;margin-bottom:14px;}
        .hs-head-row h1{font-size:20px;}
        .hs-head-row p{margin-top:3px;font-size:13px;}

        /* filter: satu kotak rapi
           baris 1: tanggal dari - sampai | baris 2: konselor + jenis
           baris 3: Terapkan | baris 4: PDF + Excel sebaris */
        .hs-toolbar-row{
            display:grid;grid-template-columns:minmax(0,1fr);gap:8px;margin-bottom:10px;
            padding:12px;background:#fff;border:1px solid #e1e7e3;border-radius:14px;
        }
        .hs-toolbar-form{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:8px;}
        .hs-daterange{grid-column:1/-1;display:grid;grid-template-columns:minmax(0,1fr) auto minmax(0,1fr);gap:8px;align-items:center;}
        .hs-date,.hs-select{width:100%;max-width:none;min-width:0;height:40px;padding:0 10px;font-size:13px;box-sizing:border-box;}
        .hs-select{padding-right:28px;background-position:right 10px center;text-overflow:ellipsis;}
        .hs-toolbar-form .hs-btn{grid-column:1/-1;justify-content:center;height:40px;}
        .hs-toolbar-export{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:8px;margin-left:0;
            padding-top:8px;border-top:1px solid #eef1ee;}
        .hs-toolbar-export .hs-btn{justify-content:center;height:40px;padding:0 10px;}
        .hs-periode{margin-bottom:14px;font-size:12px;}

        /* kartu statistik 2 x 2 */
        .hs-stats{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-bottom:12px;}
        .hs-stat{padding:12px 14px;border-radius:14px;}
        .hs-stat b{font-size:22px;}
        .hs-stat .hs-stat-label{margin-top:2px;font-size:12.5px;}
        .hs-stat .hs-stat-sub{font-size:11px;line-height:1.35;}

        /* kartu */
        .hs-dist{gap:12px;margin-bottom:12px;}
        .hs-card{padding:14px;border-radius:16px;}
        .hs-card-title{margin-bottom:12px;font-size:15px;}
        .hs-card-title small{display:block;margin-top:2px;font-size:12px;}
        .hs-opt + .hs-opt{margin-top:12px;}

        /* tabel hasil per konselor: tetap tabel, dipadatkan */
        .hs-table{font-size:13px;}
        .hs-table thead th{padding:0 6px 10px;font-size:10.5px;letter-spacing:.03em;white-space:normal;line-height:1.25;vertical-align:bottom;}
        .hs-table tbody td{padding:11px 6px;}
        .hs-table thead th:first-child,.hs-table tbody td:first-child{padding-left:0;}
        .hs-table:not(.hs-table-jawaban) thead th:nth-child(n+2),
        .hs-table:not(.hs-table-jawaban) tbody td:nth-child(n+2){text-align:center;}

        /* pencarian */
        .hs-toolbar{gap:8px;margin-bottom:10px;}
        .hs-search{max-width:none;min-width:0;flex:1 1 100%;}
        .hs-search input{height:42px;}

        /* modal */
        .hs-overlay{padding:12px;}
        .hs-modal{max-height:calc(100vh - 24px);max-height:calc(100dvh - 24px);padding:18px 16px;border-radius:16px;}
        .hs-modal-head h2{font-size:16px;}
        .hs-desc{margin-bottom:14px;}
        .hs-qa + .hs-qa{margin-top:12px;}
        .hs-modal-foot{margin-top:16px;}
        .hs-btn-tutup{width:100%;}
    }

    /* ---------- Jawaban Masuk: jumlah per halaman + pagination ---------- */
    .hs-perpage{display:inline-flex;align-items:center;gap:8px;font-size:12.5px;color:#6b7a72;white-space:nowrap;}
    .hs-perpage .hs-select{padding:7px 28px 7px 10px;height:36px;box-sizing:border-box;}
    .hs-scroll-hint{display:none;margin:0 0 8px;font-size:11.5px;color:#8a978f;}
    .hs-foot{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-top:14px;padding-top:14px;border-top:1px solid #eef1ee;}
    .hs-pager{display:flex;align-items:center;gap:4px;flex-wrap:wrap;}
    .hs-pager[hidden]{display:none;}
    .hs-pg{
        display:inline-flex;align-items:center;justify-content:center;min-width:34px;height:34px;padding:0 8px;
        border:1px solid #d8dfda;border-radius:8px;background:#fff;color:#3a463f;
        font:inherit;font-size:13px;font-weight:600;cursor:pointer;
    }
    .hs-pg:hover:not(:disabled):not(.is-active){background:#f4f7f4;}
    .hs-pg.is-active{background:#5f8f6c;border-color:#5f8f6c;color:#fff;cursor:default;}
    .hs-pg:disabled{opacity:.4;cursor:not-allowed;}
    .hs-pg svg{width:14px;height:14px;}
    .hs-pg-dots{min-width:20px;text-align:center;color:#8a978f;font-size:13px;}
    .hs-pg-of{display:none;padding:0 4px;font-size:12.5px;color:#6b7a72;}

    /* tabel jawaban tetap tabel: di layar sempit digeser ke samping, kolom Konseli menempel */
    @media (max-width:900px){
        .hs-table-wrap{-webkit-overflow-scrolling:touch;}
        .hs-table-jawaban{min-width:720px;}
        .hs-table-jawaban thead th{white-space:nowrap;}
        .hs-table-jawaban tbody td{white-space:nowrap;}
        .hs-table-jawaban thead th:first-child,
        .hs-table-jawaban tbody tr[data-cari] td:first-child{
            position:sticky;left:0;z-index:1;background:#fff;padding-right:12px;
            box-shadow:1px 0 0 #eef1ee;
        }
        .hs-table-jawaban tbody tr[data-cari] td:first-child{white-space:normal;min-width:120px;max-width:170px;}
        .hs-scroll-hint{display:block;}
    }
    @media (max-width:640px){
        .hs-perpage{font-size:12px;}
        .hs-foot{flex-direction:column;align-items:stretch;gap:10px;text-align:center;}
        .hs-pager{justify-content:center;}
        .hs-pg{min-width:36px;height:36px;}
    }
    @media (max-width:480px){
        .hs-pg-num:not(.is-active),.hs-pg-dots{display:none;}
        .hs-pg-of{display:inline;}
    }
</style>

<div class="hs-page">

    {{-- ============ JUDUL ============ --}}
    <div class="hs-head-row">
        <div>
            <h1>Hasil Survei</h1>
            <p>Rekap hasil survei layanan konseling yang diisi konseli</p>
        </div>
    </div>

    {{-- ============ TOOLBAR: FILTER + EKSPOR (1 baris) ============ --}}
    <div class="hs-toolbar-row">
        <form method="GET" action="{{ route('koordinator.survei') }}" class="hs-toolbar-form">
            <div class="hs-daterange">
                <input type="date" name="dari" class="hs-date" value="{{ $dari }}" aria-label="Dari tanggal">
                <span class="hs-date-to">s/d</span>
                <input type="date" name="sampai" class="hs-date" value="{{ $sampai }}" aria-label="Sampai tanggal">
            </div>
            <select name="konselor" class="hs-select" onchange="this.form.submit()">
                <option value="semua" @selected($konselorFilter === 'semua')>Semua Konselor</option>
                @foreach($opsiKonselor as $k)
                    <option value="{{ $k }}" @selected($konselorFilter === $k)>{{ $k }}</option>
                @endforeach
            </select>
            <select name="jenis" class="hs-select" onchange="this.form.submit()">
                <option value="semua" @selected($jenisFilter === 'semua')>Semua Jenis</option>
                <option value="Mahasiswa" @selected($jenisFilter === 'Mahasiswa')>Mahasiswa</option>
                <option value="Alumni" @selected($jenisFilter === 'Alumni')>Alumni</option>
            </select>
            <button type="submit" class="hs-btn hs-btn-outline">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                Terapkan
            </button>
        </form>

        <div class="hs-toolbar-export">
            <a href="{{ route('koordinator.survei.cetak', ['dari' => $dari, 'sampai' => $sampai, 'konselor' => $konselorFilter, 'jenis' => $jenisFilter]) }}" target="_blank" class="hs-btn hs-btn-outline">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                Ekspor PDF
            </a>
            <a href="{{ route('koordinator.survei.ekspor', ['dari' => $dari, 'sampai' => $sampai, 'konselor' => $konselorFilter, 'jenis' => $jenisFilter]) }}" class="hs-btn hs-btn-fill">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg>
                Ekspor Excel
            </a>
        </div>
    </div>

    <p class="hs-periode">Menampilkan periode: <strong>{{ $periodeLabel }}</strong></p>

    {{-- ============ KARTU STATISTIK ============ --}}
    <div class="hs-stats">
        <div class="hs-stat tone-hijau">
            <b>{{ $ringkasan['total_mengisi'] }}</b>
            <span class="hs-stat-label">Survei Terisi</span>
            <span class="hs-stat-sub">dari {{ $ringkasan['total_selesai'] }} sesi selesai</span>
        </div>
        <div class="hs-stat tone-biru">
            <b>{{ $ringkasan['persen_respon'] }}%</b>
            <span class="hs-stat-label">Tingkat Respon</span>
            <span class="hs-stat-sub">konseli mengisi survei</span>
        </div>
        <div class="hs-stat tone-ungu">
            <b>{{ $ringkasan['persen_puas'] }}%</b>
            <span class="hs-stat-label">Terbantu</span>
            <span class="hs-stat-sub">menjawab "Ya" ({{ $ringkasan['membantu_ya'] }} orang)</span>
        </div>
        <div class="hs-stat tone-kuning">
            <b>{{ $ringkasan['sikap_baik'] }}%</b>
            <span class="hs-stat-label">Sikap Memfasilitasi</span>
            <span class="hs-stat-sub">penilaian sikap konselor</span>
        </div>
    </div>

    @if($ringkasan['total_mengisi'] > 0)

        {{-- ============ DISTRIBUSI JAWABAN ============ --}}
        <div class="hs-dist">
            @foreach($distribusi as $d)
                <div class="hs-card">
                    <h3 class="hs-card-title">{{ $d['judul'] }}</h3>
                    @foreach($d['opsi'] as $o)
                        <div class="hs-opt">
                            <div class="hs-opt-top">
                                <span>{{ $o['label'] }}</span>
                                <b>{{ $o['jumlah'] }} ({{ $o['persen'] }}%)</b>
                            </div>
                            <div class="hs-bar-track"><div class="hs-bar-fill" style="width:{{ $o['persen'] }}%;"></div></div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        {{-- ============ PERFORMA PER KONSELOR ============ --}}
        <div class="hs-card" style="margin-bottom:18px;">
            <h3 class="hs-card-title">Hasil Survei per Konselor <small>berdasarkan jawaban konseli</small></h3>
            <div class="hs-table-wrap">
                <table class="hs-table">
                    <thead>
                        <tr>
                            <th>Konselor</th>
                            <th>Survei Masuk</th>
                            <th>Terbantu</th>
                            <th>Sikap Memfasilitasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($perKonselor as $p)
                            <tr>
                                <td>{{ $p['nama'] }}</td>
                                <td>{{ $p['jumlah'] }}</td>
                                <td class="{{ $p['persen_puas'] >= 70 ? 'hs-pct-good' : ($p['persen_puas'] < 50 ? 'hs-pct-bad' : '') }}">{{ $p['persen_puas'] }}%</td>
                                <td class="{{ $p['persen_baik'] >= 70 ? 'hs-pct-good' : ($p['persen_baik'] < 50 ? 'hs-pct-bad' : '') }}">{{ $p['persen_baik'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ============ DAFTAR JAWABAN ============ --}}
        <div class="hs-card">
            <h3 class="hs-card-title">Jawaban Masuk</h3>

            <div class="hs-toolbar">
                <div class="hs-search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="search" id="hs-q" placeholder="Cari nama konseli..." autocomplete="off">
                </div>
                <label class="hs-perpage" for="hs-per">Tampilkan
                    <select id="hs-per" class="hs-select" aria-label="Jumlah data per halaman">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    data
                </label>
            </div>
            <p class="hs-scroll-hint">Geser tabel ke samping untuk melihat semua kolom.</p>

            <div class="hs-table-wrap">
                <table class="hs-table hs-table-jawaban">
                    <thead>
                        <tr>
                            <th>Konseli</th>
                            <th>Jenis</th>
                            <th>Konselor</th>
                            <th>Sesi</th>
                            <th>Diisi</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="hs-body">
                        @foreach($responden as $r)
                            <tr data-cari="{{ strtolower($r['nama']) }}" data-nama="{{ $r['nama'] }}" data-detail="{{ json_encode($r['detail']) }}">
                                <td>{{ $r['nama'] }}</td>
                                <td><span class="hs-pill jenis">{{ $r['jenis'] }}</span></td>
                                <td>{{ $r['konselor'] }}</td>
                                <td>Ke-{{ $r['sesi'] }}</td>
                                <td class="hs-small">{{ $r['diisi'] }}</td>
                                <td>
                                    @if($r['puas'])
                                        <span class="hs-pill tone-hijau">Terbantu</span>
                                    @else
                                        <span class="hs-pill tone-merah">Belum terbantu</span>
                                    @endif
                                </td>
                                <td><button type="button" class="hs-btn-lihat">Lihat jawaban</button></td>
                            </tr>
                        @endforeach
                        <tr id="hs-empty" hidden><td colspan="7" class="hs-empty">Tidak ada jawaban yang cocok dengan pencarian.</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="hs-foot">
                <span class="hs-count" id="hs-count">{{ count($responden) }} jawaban</span>
                <nav class="hs-pager" id="hs-pager" aria-label="Navigasi halaman"></nav>
            </div>
        </div>

    @else
        <div class="hs-card"><div class="hs-empty">Belum ada survei yang terisi untuk filter yang dipilih.</div></div>
    @endif

</div>

{{-- ============ MODAL: DETAIL JAWABAN ============ --}}
<div class="hs-overlay" id="hs-overlay">
    <div class="hs-modal" role="dialog" aria-modal="true" aria-labelledby="hs-modal-title">
        <div class="hs-modal-head">
            <h2 id="hs-modal-title">Jawaban Survei</h2>
            <button type="button" class="hs-close" id="hs-close" aria-label="Tutup">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <p class="hs-desc">Jawaban dari <b id="hs-nama"></b></p>
        <dl class="hs-qa-list" id="hs-isi"></dl>
        <div class="hs-modal-foot">
            <button type="button" class="hs-btn-tutup" id="hs-tutup">Tutup</button>
        </div>
    </div>
</div>

<script>
(function () {
    const body  = document.getElementById('hs-body');
    if (!body) return;

    const rows  = Array.from(body.querySelectorAll('tr[data-cari]'));
    const empty = document.getElementById('hs-empty');
    const count = document.getElementById('hs-count');
    const q     = document.getElementById('hs-q');

    // ---------- Pencarian + jumlah per halaman + pagination ----------
    const per   = document.getElementById('hs-per');
    const pager = document.getElementById('hs-pager');
    const card  = body.closest('.hs-card');
    const ICON_PREV = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>';
    const ICON_NEXT = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>';

    let filtered = rows.slice();
    let page = 1;

    function daftarHalaman(total, cur) {
        const set = new Set([1, total, cur - 1, cur, cur + 1]);
        if (cur <= 3) { set.add(2); set.add(3); }
        if (cur >= total - 2) { set.add(total - 1); set.add(total - 2); }
        const arr = Array.from(set).filter(function (n) { return n >= 1 && n <= total; }).sort(function (a, b) { return a - b; });
        const out = [];
        arr.forEach(function (n, i) {
            if (i && n - arr[i - 1] > 1) out.push('...');
            out.push(n);
        });
        return out;
    }

    function tombol(isi, opsi) {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'hs-pg' + (opsi.kelas ? ' ' + opsi.kelas : '');
        if (opsi.html) b.innerHTML = opsi.html; else b.textContent = isi;
        if (opsi.aria) b.setAttribute('aria-label', opsi.aria);
        if (opsi.aktif) b.setAttribute('aria-current', 'page');
        b.disabled = !!opsi.mati;
        if (opsi.klik) b.addEventListener('click', opsi.klik);
        return b;
    }

    function ke(n) {
        page = n;
        render();
        const r = card.getBoundingClientRect();
        if (r.top < 0) card.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function bangunPager(totalHalaman) {
        pager.textContent = '';
        pager.hidden = totalHalaman <= 1;
        if (totalHalaman <= 1) return;

        pager.appendChild(tombol('', { html: ICON_PREV, aria: 'Halaman sebelumnya', mati: page === 1, klik: function () { ke(page - 1); } }));
        daftarHalaman(totalHalaman, page).forEach(function (n) {
            if (n === '...') {
                const d = document.createElement('span');
                d.className = 'hs-pg-dots';
                d.textContent = '…';
                pager.appendChild(d);
            } else {
                pager.appendChild(tombol(String(n), {
                    kelas: 'hs-pg-num' + (n === page ? ' is-active' : ''),
                    aktif: n === page, aria: 'Halaman ' + n,
                    klik: n === page ? null : function () { ke(n); }
                }));
            }
        });
        const dari = document.createElement('span');
        dari.className = 'hs-pg-of';
        dari.textContent = '/ ' + totalHalaman;
        pager.appendChild(dari);
        pager.appendChild(tombol('', { html: ICON_NEXT, aria: 'Halaman berikutnya', mati: page === totalHalaman, klik: function () { ke(page + 1); } }));
    }

    function render() {
        const ukuran = parseInt(per.value, 10) || 10;
        const total = filtered.length;
        const halaman = Math.max(1, Math.ceil(total / ukuran));
        if (page > halaman) page = halaman;
        const mulai = (page - 1) * ukuran;
        const akhir = Math.min(mulai + ukuran, total);
        const tampil = new Set(filtered.slice(mulai, akhir));

        rows.forEach(function (r) { r.hidden = !tampil.has(r); });
        empty.hidden = total > 0;
        count.textContent = total
            ? 'Menampilkan ' + (mulai + 1) + '\u2013' + akhir + ' dari ' + total + ' jawaban'
            : '0 jawaban';
        bangunPager(halaman);
    }

    q.addEventListener('input', function () {
        const kata = q.value.trim().toLowerCase();
        filtered = rows.filter(function (r) { return !kata || r.dataset.cari.includes(kata); });
        page = 1;
        render();
    });
    per.addEventListener('change', function () { page = 1; render(); });
    render();

    // ---------- Modal detail ----------
    const overlay = document.getElementById('hs-overlay');
    const elNama  = document.getElementById('hs-nama');
    const elIsi   = document.getElementById('hs-isi');

    function tutup() { overlay.classList.remove('is-open'); }

    body.addEventListener('click', function (e) {
        const btn = e.target.closest('.hs-btn-lihat');
        if (!btn) return;

        const row = btn.closest('tr');
        elNama.textContent = row.dataset.nama;
        elIsi.textContent = '';

        JSON.parse(row.dataset.detail).forEach(function (qa) {
            const wrap = document.createElement('div');
            const dt = document.createElement('dt');
            const dd = document.createElement('dd');
            wrap.className = 'hs-qa';
            dt.textContent = qa.tanya;
            dd.textContent = qa.jawab;
            wrap.appendChild(dt);
            wrap.appendChild(dd);
            elIsi.appendChild(wrap);
        });

        overlay.classList.add('is-open');
        document.getElementById('hs-tutup').focus();
    });

    document.getElementById('hs-close').addEventListener('click', tutup);
    document.getElementById('hs-tutup').addEventListener('click', tutup);
    overlay.addEventListener('click', function (e) { if (e.target === overlay) tutup(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && overlay.classList.contains('is-open')) tutup(); });
})();
</script>

@endsection