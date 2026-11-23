{{--
    SILAKON — Daftar Peserta Konseling Kelompok (Admin)
    Simpan sebagai: resources/views/admin/konseling-kelompok-peserta.blade.php

    Route yang dibutuhkan:
    Route::get('/admin/konseling-kelompok/{id}/peserta', [AdminPendaftaranController::class, 'pesertaKonselingKelompok'])
        ->name('admin.konseling-kelompok.peserta');

    Data dummy dari controller (pesertaKonselingKelompok). Pencarian nama / NIM / kode
    berjalan di browser. Tombol "Detail" masih href="#", tinggal disambungkan.
--}}
@extends('layouts.admin')

@section('judul', 'Daftar Peserta')

@section('content')

<style>
    /* ===== Halaman Daftar Peserta Konseling Kelompok ===== */
    .kkp-page{ padding-bottom:8px; }

    .kkp-crumb{
        display:flex;align-items:center;gap:8px;
        font-size:13.5px;color:#6b7a72;margin-bottom:26px;
    }
    .kkp-crumb a{color:#6b7a72;text-decoration:none;display:inline-flex;align-items:center;}
    .kkp-crumb a:hover{color:#3f6a4f;}
    .kkp-crumb svg{width:14px;height:14px;flex-shrink:0;}
    .kkp-crumb .kkp-sep{color:#a7b1ab;}
    .kkp-crumb .kkp-now{color:#1f2a24;font-weight:500;}

    .kkp-head{
        display:flex;align-items:flex-start;justify-content:space-between;
        flex-wrap:wrap;gap:16px;margin-bottom:26px;
    }
    .kkp-back{
        display:inline-flex;align-items:center;gap:6px;
        color:#5f9873;font-size:13.5px;text-decoration:none;margin-bottom:10px;
    }
    .kkp-back:hover{color:#3f6a4f;}
    .kkp-back svg{width:14px;height:14px;}
    .kkp-head h1{margin:0;font-size:22px;font-weight:700;color:#1f2a24;}
    .kkp-head p{margin:6px 0 0;font-size:13.5px;color:#6b7a72;}

    .kkp-stats{display:flex;gap:12px;}
    .kkp-stat{
        min-width:64px;border-radius:14px;padding:14px 16px;text-align:center;
        box-shadow:0 2px 8px rgba(31,42,36,.06);
    }
    .kkp-stat b{display:block;font-size:20px;font-weight:700;color:#1f2a24;line-height:1.3;}
    .kkp-stat span{font-size:12px;color:#5c6a62;white-space:nowrap;}
    .kkp-stat.tone-biru{background:#e2ecfb;}
    .kkp-stat.tone-hijau{background:#dff5e7;}
    .kkp-stat.tone-kuning{background:#fdf3c4;}

    /* Pencarian */
    .kkp-search{
        position:relative;margin-bottom:20px;
    }
    .kkp-search svg{
        position:absolute;left:18px;top:50%;transform:translateY(-50%);
        width:16px;height:16px;color:#8a978f;pointer-events:none;
    }
    .kkp-search input{
        width:100%;box-sizing:border-box;background:#fff;
        border:1px solid #e1e7e3;border-radius:14px;
        padding:15px 18px 15px 44px;font-size:14px;color:#1f2a24;
        font-family:inherit;outline:none;
    }
    .kkp-search input::placeholder{color:#8a978f;}
    .kkp-search input:focus{border-color:#4d7c5f;box-shadow:0 0 0 3px rgba(77,124,95,.12);}

    /* Tabel */
    .kkp-card{
        background:#fff;border:1px solid #e1e7e3;border-radius:18px;
        overflow:hidden;
    }
    .kkp-scroll{overflow-x:auto;}
    .kkp-table{width:100%;border-collapse:collapse;font-size:13.5px;color:#4b5a51;}
    .kkp-table thead th{
        background:#f4f7f4;text-align:left;
        font-size:12px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;
        color:#5c6a62;padding:18px 12px;white-space:nowrap;
    }
    .kkp-table thead th:first-child,
    .kkp-table tbody td:first-child{padding-left:16px;}
    .kkp-table thead th:last-child,
    .kkp-table tbody td:last-child{padding-right:16px;}
    .kkp-table tbody td{
        padding:22px 12px;border-top:1px solid #eef1ee;vertical-align:middle;
    }
    .kkp-table tbody tr:hover{background:#fafbfa;}
    .kkp-kode{color:#3f7a54;font-weight:500;white-space:nowrap;}
    .kkp-nama{color:#1f2a24;font-weight:700;white-space:nowrap;}
    .kkp-nowrap{white-space:nowrap;}

    .kkp-badge{
        display:inline-block;font-size:12px;font-weight:600;white-space:nowrap;
        padding:5px 12px;border-radius:999px;
    }
    .kkp-badge.tone-hijau{background:#d9f5e3;color:#1f7a44;}
    .kkp-badge.tone-biru{background:#dfeafc;color:#2456a8;}
    .kkp-badge.tone-merah{background:#fde0e0;color:#a72b2b;}
    .kkp-badge.tone-kuning{background:#fdf1c2;color:#8a6100;}

    .kkp-btn-detail{
        display:inline-block;background:#fff;border:1px solid #4d7c5f;color:#3f6a4f;
        padding:7px 16px;border-radius:9px;font-size:13px;font-weight:500;
        text-decoration:none;white-space:nowrap;
    }
    .kkp-btn-detail:hover{background:#eef5f0;}

    .kkp-empty{
        text-align:center;color:#8a978f;padding:36px 12px !important;
    }
</style>

<div class="kkp-page">

    {{-- Breadcrumb --}}
    <nav class="kkp-crumb" aria-label="Breadcrumb">
        <a href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : url('/admin') }}" aria-label="Beranda">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>
            </svg>
        </a>
        <span class="kkp-sep">&rsaquo;</span>
        <a href="{{ route('admin.konseling-kelompok') }}">Konseling Kelompok</a>
        <span class="kkp-sep">&rsaquo;</span>
        <span class="kkp-now">Daftar Peserta</span>
    </nav>

    {{-- Judul + ringkasan kuota --}}
    <div class="kkp-head">
        <div>
            <a href="{{ route('admin.konseling-kelompok') }}" class="kkp-back">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Kembali
            </a>
            <h1>Daftar Peserta</h1>
            <p>{{ $program['judul'] }} &middot; {{ $program['tanggal'] }}</p>
        </div>

        <div class="kkp-stats">
            <div class="kkp-stat tone-biru"><b>{{ $kuota }}</b><span>Kuota</span></div>
            <div class="kkp-stat tone-hijau"><b>{{ $terdaftar }}</b><span>Terdaftar</span></div>
            <div class="kkp-stat tone-kuning"><b>{{ $sisa }}</b><span>Sisa Kuota</span></div>
        </div>
    </div>

    {{-- Pencarian --}}
    <div class="kkp-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
        </svg>
        <input type="text" id="kkpCari" placeholder="Cari nama atau NIM..." autocomplete="off">
    </div>

    {{-- Tabel peserta --}}
    <div class="kkp-card">
        <div class="kkp-scroll">
            <table class="kkp-table" id="kkpTabel">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama Lengkap</th>
                        <th>NIM</th>
                        <th>Fakultas</th>
                        <th>Prodi</th>
                        <th>Smt</th>
                        <th>Tgl Daftar</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($peserta as $i => $p)
                        <tr data-cari="{{ mb_strtolower($p['nama'] . ' ' . $p['nim']) }}">
                            <td>{{ $i + 1 }}</td>
                            <td class="kkp-nama">{{ $p['nama'] }}</td>
                            <td class="kkp-nowrap">{{ $p['nim'] }}</td>
                            <td>{{ $p['fakultas'] }}</td>
                            <td>{{ $p['prodi'] }}</td>
                            <td>{{ $p['semester'] }}</td>
                            <td class="kkp-nowrap">{{ $p['tgl_daftar'] }}</td>
                            <td><span class="kkp-badge tone-{{ $p['status_tone'] }}">{{ $p['status'] }}</span></td>
                            <td><a href="mailto:{{ $p['email'] ?? '' }}" class="kkp-btn-detail">Email</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="kkp-empty">Belum ada peserta yang mendaftar di program ini.</td>
                        </tr>
                    @endforelse

                    @if($peserta->isNotEmpty())
                        <tr id="kkpKosong" style="display:none;">
                            <td colspan="9" class="kkp-empty">Tidak ada peserta yang cocok dengan pencarian.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    (function () {
        var input = document.getElementById('kkpCari');
        var kosong = document.getElementById('kkpKosong');
        var rows = Array.prototype.slice.call(document.querySelectorAll('#kkpTabel tbody tr[data-cari]'));

        input.addEventListener('input', function () {
            var q = this.value.trim().toLowerCase();
            var tampil = 0;

            rows.forEach(function (row) {
                var cocok = row.getAttribute('data-cari').indexOf(q) !== -1;
                row.style.display = cocok ? '' : 'none';
                if (cocok) tampil++;
            });

            if (kosong) kosong.style.display = tampil ? 'none' : '';
        });
    })();
</script>

@endsection
