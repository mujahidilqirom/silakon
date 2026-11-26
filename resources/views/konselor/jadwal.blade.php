{{--
    SILAKON — Jadwal Saya (Konselor)
    Simpan sebagai: resources/views/konselor/jadwal.blade.php

    Data masih statis, dikirim dari JadwalController.
    Tampilan List aktif secara default, tombol Kalender menampilkan
    placeholder (belum ada logic kalender sungguhan).
--}}
@extends('layouts.konselor')

@section('menu', 'jadwal')
@section('judul', 'Jadwal Saya')
@section('subjudul', $tanggalHariIni)

@section('content')

<div class="jadwal-halaman">

    {{-- ============ JUDUL & TOGGLE VIEW ============ --}}
    <div class="jadwal-atas">
        <div>
            <h1>Jadwal Saya</h1>
            <p>{{ $bulanTahun }}</p>
        </div>

        <div class="jadwal-toggle" id="jadwalToggle">
            <button type="button" class="jadwal-toggle-btn aktif" data-tampilan="list">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                List
            </button>
            <button type="button" class="jadwal-toggle-btn" data-tampilan="kalender">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Kalender
            </button>
        </div>
    </div>

    {{-- ============ KARTU RINGKASAN ============ --}}
    <div class="jadwal-ringkasan">
        <div class="kartu ringkasan-kartu">
            <div class="ringkasan-ikon ringkasan-ikon-hijau">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div>
                <h3>{{ $statistik['hari_ini'] }}</h3>
                <p>Sesi Hari Ini</p>
            </div>
        </div>

        <div class="kartu ringkasan-kartu">
            <div class="ringkasan-ikon ringkasan-ikon-biru">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            </div>
            <div>
                <h3>{{ $statistik['minggu_ini'] }}</h3>
                <p>Sesi Minggu Ini</p>
            </div>
        </div>

        <div class="kartu ringkasan-kartu">
            <div class="ringkasan-ikon ringkasan-ikon-ungu">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div>
                <h3>{{ $statistik['bulan_ini'] }}</h3>
                <p>Sesi Bulan Ini</p>
            </div>
        </div>
    </div>

    {{-- ============ TAMPILAN LIST (TABEL) ============ --}}
    <div class="kartu jadwal-kartu" id="tampilanList">
        <div class="tabel-bungkus">
            <table class="tabel-slim" id="tabelJadwal">
                <thead>
                    <tr>
                        <th class="kolom-no">No</th>
                        <th>Konseli</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Lokasi</th>
                        <th>Status</th>
                        <th class="kolom-aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jadwal as $item)
                        <tr class="baris-jadwal">
                            <td class="kolom-no">{{ $loop->iteration }}</td>
                            <td>
                                <div class="sel-konseli">
                                    <b>{{ $item['nama'] }}</b>
                                </div>
                                <div class="sel-sub">{{ $item['nim'] }}</div>
                                <div class="sel-sub">{{ $item['prodi'] }}</div>
                            </td>
                            <td>{{ $item['hari'] }}, {{ $item['tanggal'] }}</td>
                            <td class="jadwal-waktu">{{ $item['jam'] }} WIB</td>
                            <td>{{ $item['lokasi'] }}</td>
                            <td>
                                <span class="lencana lencana-status-{{ \Illuminate\Support\Str::slug($item['status']) }}">
                                    {{ $item['status'] }}
                                </span>
                            </td>
                            <td class="kolom-aksi">
                                <a href="{{ route('konselor.detail-sesi.show', $item['kode']) }}" class="tombol-mulai">
                                    Mulai
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="tabel-kosong">Belum ada jadwal konseling.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ============ PAGINATION & JUMLAH DATA ============ --}}
        @if(count($jadwal) > 0)
            <div class="tabel-kaki" id="tabelKaki">
                <div class="tabel-kaki-kiri">
                    <label class="tabel-perhalaman">
                        Tampilkan
                        <select id="selPerHalaman" aria-label="Jumlah data per halaman">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        data
                    </label>
                    <span class="tabel-info" id="infoTabel"></span>
                </div>
                <nav class="paginasi" id="paginasi" aria-label="Navigasi halaman"></nav>
            </div>
        @endif
    </div>

    {{-- ============ TAMPILAN KALENDER ============ --}}
    <div class="kartu jadwal-kartu kalender-kartu" id="tampilanKalender" style="display:none;">
        <div class="kalender-nav">
            <div class="kalender-nav-kiri">
                <button type="button" class="kalender-nav-btn" id="klBulanSebelum" aria-label="Bulan sebelumnya">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <h3 id="klJudulBulan">&nbsp;</h3>
                <button type="button" class="kalender-nav-btn" id="klBulanBerikut" aria-label="Bulan berikutnya">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </div>
            <button type="button" class="kalender-hari-ini-btn" id="klHariIni">Hari Ini</button>
        </div>

        <div class="kalender-grid-header">
            <div>Sen</div><div>Sel</div><div>Rab</div><div>Kam</div><div>Jum</div><div>Sab</div><div>Min</div>
        </div>

        <div class="kalender-grid" id="klGrid"></div>

        <div class="kalender-legenda">
            <span><i class="klg-dot klg-menunggu"></i> Menunggu</span>
            <span><i class="klg-dot klg-terjadwal"></i> Terjadwal</span>
        </div>
    </div>

    {{-- ============ DETAIL HARI TERPILIH (untuk tampilan kalender) ============ --}}
    <div class="kartu jadwal-kartu kalender-detail" id="klDetail" style="display:none;">
        <div class="kalender-detail-kepala">
            <h3 id="klDetailJudul">&nbsp;</h3>
            <button type="button" class="kalender-detail-tutup" id="klDetailTutup" aria-label="Tutup">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div id="klDetailIsi"></div>
    </div>

</div>

<script>
    (function () {
        // ============ Data dari controller ============
        var dataJadwal = @json($jadwal);
        var tahunAktif  = {{ (int) ($kalenderTahunAwal ?? 2025) }};
        var bulanAktif  = {{ (int) ($kalenderBulanAwal ?? 1) }} - 1; // 0-indexed untuk Date JS

        // Base URL detail Catatan Konseling; ":kode" diganti kode asli tiap acara
        var urlDetailBase = "{{ route('konselor.detail-sesi.show', ':kode') }}";

        var namaBulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

        // ============ Toggle List <-> Kalender ============
        var tombolToggle = document.querySelectorAll('#jadwalToggle .jadwal-toggle-btn');
        var viewList = document.getElementById('tampilanList');
        var viewKalender = document.getElementById('tampilanKalender');
        var viewDetail = document.getElementById('klDetail');

        tombolToggle.forEach(function (btn) {
            btn.addEventListener('click', function () {
                tombolToggle.forEach(function (b) { b.classList.remove('aktif'); });
                btn.classList.add('aktif');

                if (btn.dataset.tampilan === 'kalender') {
                    viewList.style.display = 'none';
                    viewKalender.style.display = 'block';
                    renderKalender();
                } else {
                    viewList.style.display = '';
                    viewKalender.style.display = 'none';
                    viewDetail.style.display = 'none';
                }
            });
        });

        // ============ Pagination tabel (List) ============
        var barisTabel = Array.prototype.slice.call(document.querySelectorAll('#tabelJadwal tbody tr.baris-jadwal'));
        var selPerHalaman = document.getElementById('selPerHalaman');
        var navPaginasi = document.getElementById('paginasi');
        var infoTabel = document.getElementById('infoTabel');
        var halamanTabel = 1;
        var perHalaman = 10;

        function daftarHalaman(aktif, total) {
            // contoh: 1 … 4 5 6 … 12
            var set = {};
            [1, total, aktif - 1, aktif, aktif + 1].forEach(function (n) {
                if (n >= 1 && n <= total) set[n] = true;
            });
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
            if (!total || !navPaginasi) return;

            var totalHalaman = Math.max(1, Math.ceil(total / perHalaman));
            if (halamanTabel > totalHalaman) halamanTabel = totalHalaman;
            if (halamanTabel < 1) halamanTabel = 1;

            var awal = (halamanTabel - 1) * perHalaman;
            var akhir = Math.min(awal + perHalaman, total);

            barisTabel.forEach(function (tr, i) {
                var tampil = i >= awal && i < akhir;
                tr.style.display = tampil ? '' : 'none';
                if (tampil) tr.querySelector('.kolom-no').textContent = i + 1;
            });

            infoTabel.textContent = 'Menampilkan ' + (awal + 1) + '–' + akhir + ' dari ' + total + ' data';

            var html = '<button type="button" class="pg-btn" data-hal="' + (halamanTabel - 1) + '" aria-label="Halaman sebelumnya"' +
                (halamanTabel === 1 ? ' disabled' : '') + '>&lsaquo;</button>';
            daftarHalaman(halamanTabel, totalHalaman).forEach(function (n) {
                if (n === '...') {
                    html += '<span class="pg-titik">&hellip;</span>';
                } else {
                    html += '<button type="button" class="pg-btn' + (n === halamanTabel ? ' aktif' : '') + '" data-hal="' + n + '"' +
                        (n === halamanTabel ? ' aria-current="page"' : '') + '>' + n + '</button>';
                }
            });
            html += '<button type="button" class="pg-btn" data-hal="' + (halamanTabel + 1) + '" aria-label="Halaman berikutnya"' +
                (halamanTabel === totalHalaman ? ' disabled' : '') + '>&rsaquo;</button>';
            navPaginasi.innerHTML = html;
        }

        if (navPaginasi) {
            navPaginasi.addEventListener('click', function (e) {
                var btn = e.target.closest('.pg-btn');
                if (!btn || btn.disabled) return;
                halamanTabel = parseInt(btn.dataset.hal, 10);
                renderTabel();
            });
            selPerHalaman.addEventListener('change', function () {
                perHalaman = parseInt(selPerHalaman.value, 10);
                halamanTabel = 1;
                renderTabel();
            });
            renderTabel();
        }

        // ============ Helper tanggal ============
        function isoDari(y, m, d) {
            var mm = String(m + 1).padStart(2, '0');
            var dd = String(d).padStart(2, '0');
            return y + '-' + mm + '-' + dd;
        }

        function jadwalPadaTanggal(iso) {
            return dataJadwal
                .filter(function (j) { return j.tanggal_iso === iso; })
                .sort(function (a, b) { return a.jam.localeCompare(b.jam); });
        }

        function kelasStatus(status) {
            var s = status.toLowerCase();
            if (s === 'menunggu') return 'klg-menunggu';
            if (s === 'terjadwal') return 'klg-terjadwal';
            return 'klg-lain';
        }

        // ============ Render grid kalender ============
        function renderKalender() {
            var grid = document.getElementById('klGrid');
            var judul = document.getElementById('klJudulBulan');
            judul.textContent = namaBulan[bulanAktif] + ' ' + tahunAktif;

            var hariPertama = new Date(tahunAktif, bulanAktif, 1).getDay(); // 0=Min...6=Sab
            var offsetSenin = (hariPertama + 6) % 7; // 0=Sen...6=Min
            var jumlahHari = new Date(tahunAktif, bulanAktif + 1, 0).getDate();
            var jumlahHariBulanLalu = new Date(tahunAktif, bulanAktif, 0).getDate();

            var hariIni = new Date();
            var isoHariIni = isoDari(hariIni.getFullYear(), hariIni.getMonth(), hariIni.getDate());

            var totalSel = Math.ceil((offsetSenin + jumlahHari) / 7) * 7;
            var html = '';

            for (var i = 0; i < totalSel; i++) {
                var nomorHari, isoSel, mutedClass = '';

                if (i < offsetSenin) {
                    // sisa bulan sebelumnya
                    nomorHari = jumlahHariBulanLalu - offsetSenin + i + 1;
                    var blnLalu = bulanAktif === 0 ? 11 : bulanAktif - 1;
                    var thnLalu = bulanAktif === 0 ? tahunAktif - 1 : tahunAktif;
                    isoSel = isoDari(thnLalu, blnLalu, nomorHari);
                    mutedClass = ' kalender-sel-muram';
                } else if (i >= offsetSenin + jumlahHari) {
                    // awal bulan berikutnya
                    nomorHari = i - (offsetSenin + jumlahHari) + 1;
                    var blnBerikut = bulanAktif === 11 ? 0 : bulanAktif + 1;
                    var thnBerikut = bulanAktif === 11 ? tahunAktif + 1 : tahunAktif;
                    isoSel = isoDari(thnBerikut, blnBerikut, nomorHari);
                    mutedClass = ' kalender-sel-muram';
                } else {
                    nomorHari = i - offsetSenin + 1;
                    isoSel = isoDari(tahunAktif, bulanAktif, nomorHari);
                }

                var acara = jadwalPadaTanggal(isoSel);
                var isHariIni = isoSel === isoHariIni ? ' kalender-sel-hariini' : '';

                var pilHtml = '';
                acara.slice(0, 2).forEach(function (a) {
                    pilHtml += '<div class="kalender-pil ' + kelasStatus(a.status) + '">' +
                        '<b>' + a.jam + '</b> ' + a.nama.split(' ')[0] +
                        '</div>';
                });
                if (acara.length > 2) {
                    pilHtml += '<div class="kalender-pil-lainnya">+' + (acara.length - 2) + ' lainnya</div>';
                }

                html += '<div class="kalender-sel' + mutedClass + isHariIni + '" data-iso="' + isoSel + '">' +
                    '<span class="kalender-sel-angka">' + nomorHari + '</span>' +
                    '<div class="kalender-sel-acara">' + pilHtml + '</div>' +
                    '</div>';
            }

            grid.innerHTML = html;

            grid.querySelectorAll('.kalender-sel').forEach(function (sel) {
                sel.addEventListener('click', function () {
                    tampilkanDetail(sel.dataset.iso);
                });
            });
        }

        // ============ Detail hari terpilih ============
        function tampilkanDetail(iso) {
            var acara = jadwalPadaTanggal(iso);
            var tgl = new Date(iso + 'T00:00:00');
            var namaHariList = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
            var labelTanggal = namaHariList[tgl.getDay()] + ', ' + tgl.getDate() + ' ' + namaBulan[tgl.getMonth()] + ' ' + tgl.getFullYear();

            document.getElementById('klDetailJudul').textContent = labelTanggal;

            var isi = document.getElementById('klDetailIsi');
            if (acara.length === 0) {
                isi.innerHTML = '<p class="kalender-detail-kosong">Tidak ada jadwal konseling pada tanggal ini.</p>';
            } else {
                var html = '';
                acara.forEach(function (a) {
                    html += '' +
                        '<div class="kalender-detail-baris">' +
                            '<div class="kalender-detail-info">' +
                                '<b>' + a.nama + '</b>' +
                                '<span class="sel-sub">' + a.nim + ' &middot; ' + a.prodi + '</span>' +
                            '</div>' +
                            '<span class="kalender-detail-jam">' + a.jam + ' WIB</span>' +
                            '<span class="sel-sub">' + a.lokasi + '</span>' +
                            '<span class="lencana lencana-status-' + a.status.toLowerCase() + '">' + a.status + '</span>' +
                            '<a href="' + urlDetailBase.replace(':kode', encodeURIComponent(a.kode)) + '" class="tombol-mulai">Mulai' +
                                '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>' +
                            '</a>' +
                        '</div>';
                });
                isi.innerHTML = html;
            }

            viewDetail.style.display = 'block';
            viewDetail.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        document.getElementById('klDetailTutup').addEventListener('click', function () {
            viewDetail.style.display = 'none';
        });

        // ============ Navigasi bulan ============
        document.getElementById('klBulanSebelum').addEventListener('click', function () {
            bulanAktif--;
            if (bulanAktif < 0) { bulanAktif = 11; tahunAktif--; }
            viewDetail.style.display = 'none';
            renderKalender();
        });

        document.getElementById('klBulanBerikut').addEventListener('click', function () {
            bulanAktif++;
            if (bulanAktif > 11) { bulanAktif = 0; tahunAktif++; }
            viewDetail.style.display = 'none';
            renderKalender();
        });

        document.getElementById('klHariIni').addEventListener('click', function () {
            var skrg = new Date();
            tahunAktif = skrg.getFullYear();
            bulanAktif = skrg.getMonth();
            viewDetail.style.display = 'none';
            renderKalender();
        });
    })();
</script>

<style>
    .jadwal-atas {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 16px;
    }
    .jadwal-atas h1 {
        font-size: 22px;
        margin: 0 0 4px;
        color: #16241d;
    }
    .jadwal-atas p {
        margin: 0;
        color: #6b7a72;
        font-size: 14px;
    }

    .jadwal-toggle {
        display: inline-flex;
        gap: 4px;
        padding: 4px;
        background: #eef2ef;
        border-radius: 10px;
    }
    .jadwal-toggle-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        border: none;
        border-radius: 7px;
        background: transparent;
        color: #5c6b63;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: background .15s, color .15s, box-shadow .15s;
    }
    .jadwal-toggle-btn svg { width: 14px; height: 14px; }
    .jadwal-toggle-btn.aktif {
        background: #fff;
        color: #16241d;
        box-shadow: 0 1px 2px rgba(16,24,20,.08);
    }

    .jadwal-ringkasan {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 16px;
    }
    .ringkasan-kartu {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 18px;
    }
    .ringkasan-kartu h3 {
        margin: 0;
        font-size: 22px;
        color: #16241d;
    }
    .ringkasan-kartu p {
        margin: 2px 0 0;
        font-size: 13px;
        color: #8a978f;
    }
    .ringkasan-ikon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .ringkasan-ikon svg { width: 20px; height: 20px; }
    .ringkasan-ikon-hijau { background: #dff3e6; color: #1f7a4d; }
    .ringkasan-ikon-biru { background: #dfeaf9; color: #1d5fa8; }
    .ringkasan-ikon-ungu { background: #e9e2fb; color: #6c3fc9; }

    .jadwal-kartu { padding: 0; }

    .tabel-bungkus { overflow: visible; } /* tanpa scrollbar di desktop; fallback ada di media query bawah */

    .tabel-slim {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
    }
    .tabel-slim thead th {
        text-align: left;
        font-size: 11.5px;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #8a978f;
        font-weight: 600;
        padding: 12px 10px;
        border-bottom: 1px solid #e6ece8;
    }
    .tabel-slim tbody td {
        padding: 12px 10px;
        border-bottom: 1px solid #eef2ef;
        vertical-align: middle;
        color: #33403a;
        overflow-wrap: anywhere; /* teks panjang turun baris, bukan melebar */
    }
    .tabel-slim th:first-child, .tabel-slim td:first-child { padding-left: 16px; }
    .tabel-slim th:last-child, .tabel-slim td:last-child { padding-right: 16px; }
    .tabel-slim .kolom-no { width: 44px; text-align: center; color: #8a978f; font-weight: 600; }
    .tabel-slim thead th.kolom-no { padding-left: 10px; }
    .tabel-slim .jadwal-waktu, .tabel-slim .kolom-aksi { white-space: nowrap; }
    .tabel-slim tbody tr:hover { background: #f7faf8; }
    .tabel-slim .kolom-aksi { text-align: right; }

    .sel-konseli b { font-size: 13.5px; color: #16241d; }
    .sel-sub { font-size: 11.5px; color: #8a978f; margin-top: 1px; }

    .jadwal-waktu { color: #1f7a4d; font-weight: 600; }

    .lencana {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: .01em;
        white-space: nowrap;
    }
    .lencana-kategori { background: #dfeaf9; color: #1d5fa8; }
    .lencana-status-menunggu { background: #fdf3d0; color: #92720c; }
    .lencana-status-terjadwal { background: #dff3e6; color: #1f7a4d; }
    .lencana-status-selesai { background: #e5e9f0; color: #445; }
    .lencana-status-dibatalkan { background: #fde2df; color: #b3261e; }

    .tombol-mulai {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        border-radius: 8px;
        background: #2f6b52;
        color: #fff;
        font-size: 12.5px;
        font-weight: 600;
        text-decoration: none;
        transition: background .15s;
    }
    .tombol-mulai svg { width: 13px; height: 13px; }
    .tombol-mulai:hover { background: #24543f; }

    .tabel-kosong {
        text-align: center;
        color: #8a978f;
        font-size: 13px;
        padding: 24px 12px;
    }

    /* ============ Kalender ============ */
    .kalender-kartu { padding: 18px; }

    .kalender-nav {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
    }
    .kalender-nav-kiri {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .kalender-nav-kiri h3 {
        margin: 0;
        font-size: 16px;
        color: #16241d;
        min-width: 150px;
        text-align: center;
    }
    .kalender-nav-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 8px;
        border: 1px solid #dfe6e1;
        background: #fff;
        color: #5c6b63;
        cursor: pointer;
        transition: background .15s, color .15s, border-color .15s;
    }
    .kalender-nav-btn svg { width: 15px; height: 15px; }
    .kalender-nav-btn:hover { border-color: #bcd0c6; color: #1f4a35; background: #f2f8f4; }

    .kalender-hari-ini-btn {
        padding: 7px 14px;
        border-radius: 8px;
        border: 1px solid #dfe6e1;
        background: #fff;
        color: #33403a;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        transition: background .15s, border-color .15s;
    }
    .kalender-hari-ini-btn:hover { border-color: #bcd0c6; background: #f2f8f4; }

    .kalender-grid-header {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        margin-bottom: 6px;
    }
    .kalender-grid-header div {
        text-align: center;
        font-size: 11.5px;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #8a978f;
        font-weight: 700;
        padding: 6px 0;
    }

    .kalender-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 6px;
    }

    .kalender-sel {
        min-height: 92px;
        border: 1px solid #e6ece8;
        border-radius: 10px;
        padding: 6px;
        background: #fff;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        gap: 4px;
        transition: border-color .15s, background .15s;
    }
    .kalender-sel:hover { border-color: #9fc9b0; background: #f7faf8; }
    .kalender-sel-angka {
        font-size: 12.5px;
        font-weight: 700;
        color: #33403a;
    }
    .kalender-sel-muram { background: #fafbfa; }
    .kalender-sel-muram .kalender-sel-angka { color: #c1cbc5; }
    .kalender-sel-hariini { border-color: #2f6b52; box-shadow: inset 0 0 0 1px #2f6b52; }
    .kalender-sel-hariini .kalender-sel-angka {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #2f6b52;
        color: #fff;
    }

    .kalender-sel-acara {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }
    .kalender-pil {
        font-size: 10.5px;
        line-height: 1.3;
        padding: 2px 6px;
        border-radius: 6px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .kalender-pil b { font-weight: 700; }
    .kalender-pil.klg-menunggu { background: #fdf3d0; color: #92720c; }
    .kalender-pil.klg-terjadwal { background: #dff3e6; color: #1f7a4d; }
    .kalender-pil.klg-lain { background: #e5e9f0; color: #445; }
    .kalender-pil-lainnya {
        font-size: 10.5px;
        color: #8a978f;
        padding: 0 6px;
        font-weight: 600;
    }

    .kalender-legenda {
        display: flex;
        gap: 18px;
        margin-top: 14px;
        font-size: 12px;
        color: #5c6b63;
    }
    .kalender-legenda span { display: inline-flex; align-items: center; gap: 6px; }
    .klg-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        display: inline-block;
    }
    .klg-dot.klg-menunggu { background: #d9a824; }
    .klg-dot.klg-terjadwal { background: #1f7a4d; }

    /* ============ Detail hari terpilih ============ */
    .kalender-detail { padding: 16px 18px; margin-top: 16px; }
    .kalender-detail-kepala {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .kalender-detail-kepala h3 {
        margin: 0;
        font-size: 15px;
        color: #16241d;
    }
    .kalender-detail-tutup {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border-radius: 7px;
        border: none;
        background: #f2f5f3;
        color: #5c6b63;
        cursor: pointer;
    }
    .kalender-detail-tutup svg { width: 13px; height: 13px; }
    .kalender-detail-tutup:hover { background: #e6ece8; }

    .kalender-detail-kosong {
        margin: 0;
        font-size: 13px;
        color: #8a978f;
        padding: 12px 0;
    }

    .kalender-detail-baris {
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
        padding: 12px 0;
        border-bottom: 1px solid #eef2ef;
    }
    .kalender-detail-baris:last-child { border-bottom: none; }
    .kalender-detail-info { display: flex; flex-direction: column; min-width: 160px; }
    .kalender-detail-info b { font-size: 13.5px; color: #16241d; }
    .kalender-detail-jam { font-size: 12.5px; font-weight: 600; color: #1f7a4d; }

    /* ============ Footer tabel: jumlah data & pagination ============ */
    .tabel-kaki {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        padding: 12px 16px;
        font-size: 12.5px;
        color: #5c6b63;
    }
    .tabel-kaki-kiri { display: flex; align-items: center; flex-wrap: wrap; gap: 8px 16px; }
    .tabel-perhalaman { display: inline-flex; align-items: center; gap: 6px; }
    .tabel-perhalaman select {
        height: 30px;
        padding: 0 8px;
        border: 1px solid #dfe6e1;
        border-radius: 8px;
        background: #fff;
        color: #33403a;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
    }
    .tabel-perhalaman select:focus { outline: none; border-color: #2f6b52; }
    .paginasi { display: flex; align-items: center; gap: 4px; flex-wrap: wrap; }
    .pg-btn {
        min-width: 30px;
        height: 30px;
        padding: 0 8px;
        border: 1px solid #dfe6e1;
        border-radius: 8px;
        background: #fff;
        color: #33403a;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        transition: background .15s, border-color .15s, color .15s;
    }
    .pg-btn:hover:not(:disabled):not(.aktif) { border-color: #bcd0c6; background: #f2f8f4; }
    .pg-btn.aktif { background: #2f6b52; border-color: #2f6b52; color: #fff; cursor: default; }
    .pg-btn:disabled { opacity: .45; cursor: not-allowed; }
    .pg-titik { padding: 0 4px; color: #8a978f; }

    /* =====================================================================
       RESPONSIF — tabel tetap tabel
       Desktop: muat tanpa scrollbar samping. Layar sempit: tabel digeser,
       hanya kolom Aksi yang menempel di kanan.
       ===================================================================== */
    .jadwal-atas > div:first-child { min-width: 0; }

    @media (max-width: 1100px) {
        .jadwal-kartu .tabel-bungkus {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior-x: contain;
            border-radius: inherit;
            background:
                linear-gradient(to right, #fff 30%, rgba(255,255,255,0)) left center,
                linear-gradient(to left,  #fff 30%, rgba(255,255,255,0)) right center,
                radial-gradient(farthest-side at 0 50%,  rgba(0,0,0,.12), rgba(0,0,0,0)) left center,
                radial-gradient(farthest-side at 100% 50%, rgba(0,0,0,.12), rgba(0,0,0,0)) right center;
            background-repeat: no-repeat;
            background-size: 28px 100%, 28px 100%, 10px 100%, 10px 100%;
            background-attachment: local, local, scroll, scroll;
        }
        .jadwal-kartu .tabel-slim { min-width: 900px; }
        .jadwal-kartu .tabel-slim thead th { white-space: nowrap; }
        .jadwal-kartu .tabel-slim td:nth-child(2) { min-width: 180px; }
        .jadwal-kartu .tabel-slim td:nth-child(4) { white-space: nowrap; }
        .jadwal-kartu .tabel-slim td:nth-child(6) { min-width: 120px; }

        .jadwal-kartu .tabel-slim th.kolom-aksi,
        .jadwal-kartu .tabel-slim td.kolom-aksi {
            position: sticky; right: 0; z-index: 1;
            background: #fff;
            box-shadow: -1px 0 0 #eef2ef;
        }
        .jadwal-kartu .tabel-slim tbody tr:hover td.kolom-aksi { background: #f7faf8; }
    }

    @media (max-width: 720px) {
        .jadwal-atas h1 { font-size: 20px; }
        .jadwal-atas { margin-bottom: 14px; }

        /* ringkasan: tetap 3 kartu sebaris, isi disusun vertikal */
        .jadwal-ringkasan { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-bottom: 14px; }
        .ringkasan-kartu { flex-direction: column; align-items: flex-start; gap: 10px; padding: 12px 10px; }
        .ringkasan-ikon { width: 34px; height: 34px; border-radius: 9px; }
        .ringkasan-ikon svg { width: 17px; height: 17px; }
        .ringkasan-kartu h3 { font-size: 20px; line-height: 1.1; }
        .ringkasan-kartu p { font-size: 11.5px; line-height: 1.3; margin-top: 3px; }

        /* tabel */
        .jadwal-kartu .tabel-slim { min-width: 840px; font-size: 12.5px; }
        .tabel-slim thead th { padding: 10px 8px; font-size: 10.5px; }
        .tabel-slim tbody td { padding: 10px 8px; }

        /* footer tabel: jumlah & per-halaman satu baris, pagination di bawahnya */
        .tabel-kaki { flex-direction: column; align-items: stretch; gap: 10px; padding: 12px; }
        .tabel-kaki-kiri { justify-content: space-between; }
        .paginasi { justify-content: center; }
        .pg-btn { min-width: 34px; height: 34px; }
        .tabel-perhalaman select { height: 34px; }

        /* kalender: tanggal ringkas, acara jadi titik berwarna */
        .kalender-kartu { padding: 12px; }
        .kalender-nav { gap: 8px; margin-bottom: 10px; }
        .kalender-nav-kiri { gap: 6px; min-width: 0; }
        .kalender-nav-kiri h3 { min-width: 0; font-size: 15px; }
        .kalender-grid-header div { font-size: 10.5px; padding: 4px 0; }
        .kalender-grid { gap: 4px; }
        .kalender-sel { min-height: 54px; padding: 4px; border-radius: 8px; align-items: center; gap: 3px; }
        .kalender-sel-angka { font-size: 12px; }
        .kalender-sel-hariini .kalender-sel-angka { width: 22px; height: 22px; }
        .kalender-sel-acara { flex-direction: row; flex-wrap: wrap; justify-content: center; gap: 3px; }
        .kalender-pil { font-size: 0; width: 8px; height: 8px; padding: 0; border-radius: 50%; }
        .kalender-pil.klg-menunggu  { background: #d9a824; }
        .kalender-pil.klg-terjadwal { background: #1f7a4d; }
        .kalender-pil.klg-lain      { background: #8a97a8; }
        .kalender-pil-lainnya { display: none; }
        .kalender-legenda { margin-top: 12px; gap: 14px; }

        /* detail hari: nama penuh satu baris, sisanya mengalir */
        .kalender-detail { padding: 14px; margin-top: 12px; }
        .kalender-detail-baris { gap: 8px 10px; }
        .kalender-detail-info { flex: 1 1 100%; min-width: 0; }
        .kalender-detail-baris .tombol-mulai { margin-left: auto; }
    }

    @media (max-width: 480px) {
        .jadwal-toggle { width: 100%; }
        .jadwal-toggle-btn { flex: 1; justify-content: center; padding: 9px 12px; }
        .kalender-hari-ini-btn { padding: 6px 10px; }
    }
</style>

@endsection
