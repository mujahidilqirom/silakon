{{--
    SILAKON — Penugasan Baru (Konselor)
    Simpan sebagai: resources/views/konselor/penugasan.blade.php

    Data masih statis, dikirim dari PenugasanController.
    Daftar kasus ditampilkan sebagai tabel (bukan kartu) dan bisa
    dicari secara langsung (client-side, tanpa reload).
--}}
@extends('layouts.konselor')

@section('menu', 'penugasan')
@section('judul', 'Penugasan Baru')
@section('subjudul', $tanggalHariIni)

@section('content')

<div class="penugasan-halaman">

    {{-- ============ JUDUL & DESKRIPSI ============ --}}
    <div class="penugasan-atas">
        <h1>Penugasan Baru</h1>
        <p>Konseli yang baru ditugaskan kepada Anda. Konfirmasi ketersediaan melalui WhatsApp ke admin.</p>
    </div>

    {{-- ============ KARTU TABEL PENUGASAN ============ --}}
    <div class="kartu penugasan-kartu">
        <div class="kartu-kepala penugasan-kepala">
            <div>
                <h2>Daftar Penugasan</h2>
                <p>{{ count($penugasan) }} penugasan menunggu konfirmasi</p>
            </div>

            <div class="tabel-cari">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                <input type="text" id="cariPenugasan" placeholder="Cari nama, kode, atau kategori...">
            </div>
        </div>

        <div class="tabel-bungkus">
            <table class="tabel-slim" id="tabelPenugasan">
                <thead>
                    <tr>
                        <th>Konseli</th>
                        <th>Kategori</th>
                        <th>Prioritas SAW</th>
                        <th>Mode</th>
                        <th>Tanggal Konseling</th>
                        <th class="kolom-aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($penugasan as $item)
                        <tr>
                            <td>
                                <div class="sel-konseli">
                                    <b>{{ $item['nama'] }}</b>
                                </div>
                                <div class="sel-sub">{{ $item['kode'] }}</div>
                                <div class="sel-sub">{{ $item['peran'] }} &middot; {{ $item['nim'] }}</div>
                            </td>
                            <td>{{ $item['kategori'] }}</td>
                            <td><span class="lencana">{{ $item['prioritas'] }}</span></td>
                            <td>{{ $item['mode'] }}</td>
                            <td>
                                <div class="sel-jadwal">
                                    <b>{{ $item['tanggal_konseling'] }}</b>
                                    <span>{{ $item['jam_konseling'] }} WIB &middot; {{ $item['tempat_konseling'] }}</span>
                                </div>
                            </td>
                            <td class="kolom-aksi">
                                <div class="tabel-aksi">
                                    <a href="#" class="ikon-tombol ikon-detail" title="Lihat Detail" aria-label="Lihat Detail">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M1.5 12S5 5 12 5s10.5 7 10.5 7-3.5 7-10.5 7S1.5 12 1.5 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                    <a href="#" class="ikon-tombol ikon-wa" title="Konfirmasi WA" aria-label="Konfirmasi WA">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M20.5 3.5a11 11 0 0 0-17.4 13.3L2 21l4.4-1.1A11 11 0 0 0 20.5 3.5z"/><path d="M8.5 8.3c.2-.5.6-1.4 1-1.5.4-.1.9 0 1.1.4l.8 1.5c.2.4.1.8-.1 1.1l-.6.7c.4 1 1.6 2.2 2.6 2.6l.7-.6c.3-.2.7-.3 1.1-.1l1.5.8c.4.2.5.7.4 1.1-.1.4-1 .8-1.5 1-2.7 1-6.9-3.2-7.9-6z"/></svg>
                                    </a>
                                    <a href="#" class="ikon-tombol ikon-bersedia" title="Bersedia" aria-label="Bersedia">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6.5L9 17.5l-5-5"/></svg>
                                    </a>
                                    <a href="#" class="ikon-tombol ikon-tolak" title="Tidak Bersedia" aria-label="Tidak Bersedia">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="tabel-kosong">Belum ada penugasan baru.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <p class="tabel-tidak-ada" id="pesanKosongCari" style="display:none;">
                Tidak ada penugasan yang cocok dengan pencarian.
            </p>
        </div>
    </div>
</div>

<script>
    (function () {
        var input = document.getElementById('cariPenugasan');
        var baris = document.querySelectorAll('#tabelPenugasan tbody tr');
        var pesanKosong = document.getElementById('pesanKosongCari');

        input.addEventListener('input', function (e) {
            var q = e.target.value.trim().toLowerCase();
            var tampil = 0;

            baris.forEach(function (tr) {
                var cocok = tr.textContent.toLowerCase().indexOf(q) !== -1;
                tr.style.display = cocok ? '' : 'none';
                if (cocok) tampil++;
            });

            pesanKosong.style.display = (tampil === 0 && q !== '') ? '' : 'none';
        });
    })();
</script>

<style>
    .penugasan-atas h1 {
        font-size: 22px;
        margin: 0 0 4px;
        color: #16241d;
    }
    .penugasan-atas p {
        margin: 0 0 16px;
        color: #6b7a72;
        font-size: 14px;
    }

    .penugasan-kepala {
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }
    .penugasan-kepala h2 { margin: 0 0 2px; font-size: 16px; }
    .penugasan-kepala p { margin: 0; font-size: 12.5px; color: #8a978f; }

    .tabel-cari {
        position: relative;
        min-width: 260px;
    }
    .tabel-cari svg {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        width: 15px;
        height: 15px;
        color: #9aa6a0;
    }
    .tabel-cari input {
        width: 100%;
        padding: 8px 12px 8px 32px;
        border: 1px solid #dfe6e1;
        border-radius: 8px;
        font-size: 13px;
        outline: none;
        background: #fbfdfc;
    }
    .tabel-cari input:focus {
        border-color: #6faf8a;
        background: #fff;
    }

    .tabel-bungkus { overflow-x: auto; margin-top: 4px; }

    .tabel-slim {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .tabel-slim thead th {
        text-align: left;
        font-size: 11.5px;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #8a978f;
        font-weight: 600;
        padding: 8px 12px;
        border-bottom: 1px solid #e6ece8;
        white-space: nowrap;
    }
    .tabel-slim tbody td {
        padding: 9px 12px;
        border-bottom: 1px solid #eef2ef;
        vertical-align: middle;
        color: #33403a;
    }
    .tabel-slim tbody tr:hover { background: #f7faf8; }
    .tabel-slim .kolom-aksi { text-align: right; white-space: nowrap; }

    .sel-konseli { display: flex; align-items: center; gap: 8px; }
    .sel-konseli b { font-size: 13.5px; color: #16241d; }
    .sel-sub { font-size: 11.5px; color: #8a978f; margin-top: 1px; }

    .sel-jadwal { display: flex; flex-direction: column; gap: 1px; }
    .sel-jadwal b { font-size: 13px; color: #16241d; }
    .sel-jadwal span { font-size: 11.5px; color: #8a978f; }

    .lencana {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .02em;
        white-space: nowrap;
    }
    .lencana-tingkat-tinggi { background: #fde2df; color: #b3261e; }
    .lencana-tingkat-sedang { background: #fdf3d0; color: #92720c; }
    .lencana-tingkat-rendah { background: #dff3e6; color: #1f7a4d; }

    .tabel-aksi { display: inline-flex; gap: 6px; justify-content: flex-end; }

    .ikon-tombol {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 8px;
        border: 1px solid #dfe6e1;
        background: #fff;
        color: #5c6b63;
        transition: background .15s, color .15s, border-color .15s;
    }
    .ikon-tombol svg { width: 15px; height: 15px; }

    .ikon-detail:hover { border-color: #bcd0c6; color: #1f4a35; background: #f2f8f4; }
    .ikon-wa { color: #2f6b52; border-color: #cfe8da; }
    .ikon-wa:hover { background: #2f6b52; color: #fff; }
    .ikon-bersedia { color: #1f7a4d; border-color: #cdeadb; }
    .ikon-bersedia:hover { background: #1f7a4d; color: #fff; }
    .ikon-tolak { color: #b3261e; border-color: #f4d3d0; }
    .ikon-tolak:hover { background: #b3261e; color: #fff; }

    .tabel-kosong, .tabel-tidak-ada {
        text-align: center;
        color: #8a978f;
        font-size: 13px;
        padding: 24px 12px;
    }
</style>

@endsection
