{{--
    SILAKON — Detail Sesi Konseling (Konselor)
    Simpan sebagai: resources/views/konselor/detail-sesi.blade.php

    Diakses dari tombol "Mulai" di halaman Jadwal Saya.
    Data masih statis, dikirim dari DetailSesiController.

    Isi halaman disesuaikan dengan form pengajuan (konseling/create). Yang ditampilkan hanya data
    yang dibutuhkan konselor: identitas ringkas, kontak, kondisi darurat, dan terutama URAIAN PERMASALAHAN.
    Data administratif (alamat, tempat/tgl lahir, bukti Instagram) sengaja tidak ditampilkan.
--}}
@extends('layouts.konselor')

@section('menu', 'jadwal')
@section('judul', 'Detail Sesi')
@section('subjudul', $tanggalHariIni)

@section('content')

<div class="ck-halaman">

    <a href="{{ route('konselor.jadwal') }}" class="ck-kembali">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Kembali
    </a>

    <div class="ck-grid">

        {{-- ============ KOLOM KIRI ============ --}}
        <div class="ck-kolom-utama">

            {{-- Informasi Pemohon (ringkas) --}}
            <div class="kartu ck-kartu">
                <div class="ck-kartu-judul">
                    <span class="ck-kartu-ikon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </span>
                    <h2>Informasi Pemohon</h2>
                </div>

                <div class="ck-profil-baris">
                    <div class="ck-avatar">{{ strtoupper(substr($item['nama'], 0, 1)) }}</div>
                    <div class="ck-profil-info">
                        <b>{{ $item['nama'] }}</b>
                        {{-- identitas: mahasiswa/alumni = NIM · Fakultas · Prodi, dosen/tendik = Unit kerja, wali = wali dari {nama} ({nim}) --}}
                        <span>{{ $item['identitas'] }}</span>
                    </div>
                    <span class="lencana lencana-kategori ck-badge-kategori">{{ $item['jenis_konseli'] }}</span>
                </div>

                @php
                    // 08xxxx -> 628xxxx (format wa.me)
                    $waNomor = preg_replace('/\D/', '', $item['telepon']);
                    if (str_starts_with($waNomor, '0')) { $waNomor = '62' . substr($waNomor, 1); }
                    elseif (str_starts_with($waNomor, '8')) { $waNomor = '62' . $waNomor; }
                @endphp
                <div class="ck-info-grid">
                    <div class="ck-info-kotak">
                        <span class="ck-info-label">Nomor Pengajuan</span>
                        <span class="ck-info-nilai">{{ $kode }}</span>
                    </div>
                    <div class="ck-info-kotak">
                        <span class="ck-info-label">Nomor WhatsApp</span>
                        <div class="ck-wa-baris">
                            <span class="ck-info-nilai">{{ $item['telepon'] }}</span>
                            <a href="https://wa.me/{{ $waNomor }}" target="_blank" rel="noopener" class="ck-tombol-wa" title="Buka chat WhatsApp">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.4 8.4 0 0 1-12.4 7.4L3 21l2.1-5.5A8.4 8.4 0 1 1 21 11.5z"/></svg>
                                Chat
                            </a>
                        </div>
                    </div>
                    <div class="ck-info-kotak ck-span-2">
                        <span class="ck-info-label">Email</span>
                        <span class="ck-info-nilai">{{ $item['email'] }}</span>
                    </div>
                    @if(!empty($item['kontak_darurat']))
                        <div class="ck-info-kotak ck-span-2">
                            <span class="ck-info-label">Kontak Darurat</span>
                            <span class="ck-info-nilai">{{ $item['kontak_darurat']['nama'] }} ({{ $item['kontak_darurat']['hubungan'] }}) &middot; {{ $item['kontak_darurat']['nomor'] }}</span>
                        </div>
                    @endif
                </div>
                @if($item['jenis_konseli'] === 'Wali Mahasiswa')
                    <div class="ck-info-grid ck-info-grid-jarak">
                        <div class="ck-info-kotak ck-span-2">
                            <span class="ck-info-label">Yang membutuhkan konseling</span>
                            <span class="ck-info-nilai">{{ $item['subjek_konseling'] ?: '-' }}</span>
                        </div>
                        <div class="ck-info-kotak">
                            <span class="ck-info-label">Nama wali</span>
                            <span class="ck-info-nilai">{{ $item['wali_nama'] ?: '-' }}</span>
                        </div>
                        <div class="ck-info-kotak">
                            <span class="ck-info-label">Nama mahasiswa</span>
                            <span class="ck-info-nilai">{{ $item['mahasiswa_nama'] ?: '-' }}</span>
                        </div>
                        @if($item['subjek_konseling'] === 'Mahasiswa')
                            <div class="ck-info-kotak ck-span-2">
                                <span class="ck-info-label">NIM mahasiswa</span>
                                <span class="ck-info-nilai">{{ $item['mahasiswa_nim'] ?: '-' }}</span>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Permasalahan Konseli (inti halaman) --}}
            <div class="kartu ck-kartu">
                <div class="ck-kartu-judul">
                    <span class="ck-kartu-ikon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    </span>
                    <h2>Permasalahan Konseli</h2>
                </div>

                @if($masalah['darurat'])
                    <div class="ck-darurat" role="alert">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <span><b>Konseli menyatakan dalam kondisi darurat.</b> Prioritaskan dan pastikan kontak darurat dapat dihubungi bila diperlukan.</span>
                    </div>
                @endif

                <div class="ck-meta-baris">
                    <div class="ck-meta">
                        <span class="ck-info-label">Prioritas SAW</span>
                        <span class="ck-tingkat">{{ $masalah['prioritas'] }}</span>
                        <small class="ck-screening-note">WHO-5: {{ $masalah['who5_skor'] !== null ? $masalah['who5_skor'].'/100' : '—' }} · SAW: {{ $masalah['saw_nilai'] !== null ? number_format((float) $masalah['saw_nilai'], 4) : '—' }}</small>
                    </div>
                    <div class="ck-meta">
                        <span class="ck-info-label">Konselor Pilihan</span>
                        <span class="ck-info-nilai">{{ $masalah['pilihan_konselor'] }}</span>
                    </div>
                </div>

                <div class="ck-meta ck-meta-blok">
                    <span class="ck-info-label">Lingkup Hambatan</span>
                    <div class="ck-chip-baris">
                        @foreach($masalah['lingkup'] as $hambatan)
                            <span class="ck-chip">{{ $hambatan }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="ck-meta ck-meta-blok">
                    <span class="ck-info-label">Uraian Permasalahan</span>
                    <div class="ck-uraian">{{ $masalah['uraian'] }}</div>
                    <div class="ck-rahasia">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Bersifat rahasia, hanya untuk konselor yang menangani, admin, dan Koordinator LP3.
                    </div>
                </div>
                @php
                    $who5Questions = ['Saya merasa ceria dan bersemangat.','Saya merasa tenang dan santai.','Saya merasa aktif dan penuh energi.','Saya bangun dengan perasaan segar dan beristirahat cukup.','Kehidupan sehari-hari saya dipenuhi hal-hal yang menarik bagi saya.'];
                    $who5Words = ['Tidak pernah','Jarang','Kurang dari setengah waktu','Lebih dari setengah waktu','Sering','Selalu'];
                @endphp
                @if(count($masalah['who5_jawaban'] ?? []) === 5)
                    <div class="ck-meta ck-meta-blok">
                        <span class="ck-info-label">Jawaban WHO-5</span>
                        <div class="ck-who5-list">
                            @foreach($who5Questions as $i => $question)
                                <div><strong>{{ $i + 1 }}. {{ $question }}</strong><span>{{ $who5Words[(int) ($masalah['who5_jawaban'][$i] ?? 0)] ?? 'Tidak diisi' }} ({{ $masalah['who5_jawaban'][$i] }}/5)</span></div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Riwayat Konseling --}}
            <div class="kartu ck-kartu">
                <div class="ck-kartu-judul">
                    <span class="ck-kartu-ikon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    </span>
                    <h2>Riwayat Konseling</h2>
                </div>

                <div class="tabel-bungkus">
                    <table class="tabel-slim">
                        <thead>
                            <tr>
                                <th>No. Pengajuan</th>
                                <th>Tanggal</th>
                                <th>Kategori</th>
                                <th>Status</th>
                                <th>Konselor</th>
                                <th>Catatan / Hasil</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($riwayat as $r)
                                <tr>
                                    <td>
                                        <strong>{{ $r['kode'] }}</strong>
                                        @if(!empty($r['sesi']))<small style="display:block;color:#7a887f">Sesi {{ $r['sesi'] }}</small>@else<small style="display:block;color:#7a887f">Pengajuan</small>@endif
                                    </td>
                                    <td>{{ $r['tanggal'] }}</td>
                                    <td><span class="lencana lencana-kategori">{{ $r['kategori'] }}</span></td>
                                    <td><span class="lencana lencana-selesai">{{ $r['status'] }}</span></td>
                                    <td>{{ $r['konselor'] }}</td>
                                    <td style="min-width:220px">
                                        <details>
                                            <summary style="cursor:pointer;color:#527b58">Lihat detail</summary>
                                            <div style="margin-top:8px;font-size:12px;line-height:1.5">
                                                <b>Uraian:</b> {{ $r['uraian'] ?? '-' }}<br>
                                                <b>Catatan:</b> {{ $r['catatan'] ?? 'Belum diisi' }}<br>
                                                <b>Hasil:</b> {{ $r['hasil'] ?? 'Belum ada' }}
                                            </div>
                                        </details>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="tabel-kosong">Belum ada riwayat konseling sebelumnya.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Catatan Sesi Konseling --}}
            <div class="kartu ck-kartu">
                <div class="ck-kartu-judul">
                    <span class="ck-kartu-ikon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.1 2.1 0 0 1 3 3L12 15l-4 1 1-4z"/></svg>
                    </span>
                    <h2>Catatan Sesi Konseling</h2>
                </div>

                <form id="formCatatanKonseling">
                    <label class="ck-label" for="ckCatatan">Catatan Konseling <span class="ck-wajib">*</span></label>
                    <textarea id="ckCatatan" class="ck-textarea" rows="5" placeholder="Tuliskan catatan proses konseling, teknik yang digunakan, respons konseli, dan observasi penting..." {{ ($item['status'] ?? '') === 'Selesai' ? 'disabled' : '' }}>{{ $item['catatan'] ?? '' }}</textarea>
                    <p class="ck-hint">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        Catatan ini akan dikirim ke konseli setelah sesi selesai.
                    </p>

                    <label class="ck-label ck-label-jarak" for="ckHasil">Hasil Konseling</label>
                    <select id="ckHasil" class="ck-select" {{ ($item['status'] ?? '') === 'Selesai' ? 'disabled' : '' }}>
                        <option value="" disabled {{ empty($item['hasil']) ? 'selected' : '' }}>Pilih hasil konseling...</option>
                        @foreach($opsiHasilKonseling as $opsi)
                            <option value="{{ $opsi }}" {{ ($item['hasil'] ?? '') === $opsi ? 'selected' : '' }}>{{ $opsi }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            {{-- Rekomendasi & Tindakan --}}
            <div class="kartu ck-kartu">
                <div class="ck-kartu-judul">
                    <span class="ck-kartu-ikon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </span>
                    <h2>Rekomendasi &amp; Tindakan</h2>
                </div>

                <div class="ck-aksi-baris">
                    <button type="button" class="ck-tombol ck-tombol-hijau" id="ckSelesai" disabled title="Isi catatan dan hasil konseling terlebih dahulu">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        Selesaikan Konseling
                    </button>
                    <button type="button" class="ck-tombol ck-tombol-bahaya" id="ckDarurat" {{ $masalah['darurat'] ? 'disabled' : '' }}>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <span id="ckDaruratLabel">{{ $masalah['darurat'] ? 'Sudah Ditandai Darurat' : 'Tandai Sebagai Kasus Darurat' }}</span>
                    </button>
                </div>
                <p class="ck-hint ck-hint-aksi">Tombol selesai aktif setelah catatan dan hasil konseling terisi.</p>
            </div>

        </div>

        {{-- ============ KOLOM KANAN ============ --}}
        <div class="ck-kolom-samping">

            {{-- Info Sesi --}}
            <div class="kartu ck-kartu">
                <div class="ck-kartu-judul">
                    <span class="ck-kartu-ikon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </span>
                    <h2>Info Sesi</h2>
                </div>

                <div class="ck-info-baris">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <div>
                        <span class="ck-info-label">Tanggal</span>
                        <span class="ck-info-nilai">{{ $item['tanggal'] }}</span>
                    </div>
                </div>
                <div class="ck-info-baris">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <div>
                        <span class="ck-info-label">Waktu</span>
                        <span class="ck-info-nilai">{{ $item['jam'] }} WIB</span>
                    </div>
                </div>
                <div class="ck-info-baris">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    <div>
                        <span class="ck-info-label">Metode</span>
                        <span class="ck-info-nilai">{{ $item['metode'] }}</span>
                    </div>
                </div>
                <div class="ck-info-baris">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <div>
                        <span class="ck-info-label">Lokasi</span>
                        <span class="ck-info-nilai">{{ $item['lokasi'] }}</span>
                    </div>
                </div>
                <div class="ck-info-baris">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <div>
                        <span class="ck-info-label">Konselor</span>
                        <span class="ck-info-nilai">{{ $item['konselor'] }}</span>
                    </div>
                </div>
            </div>

            {{-- Status Pengajuan --}}
            <div class="kartu ck-kartu">
                <div class="ck-kartu-judul">
                    <span class="ck-kartu-ikon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </span>
                    <h2>Status Pengajuan</h2>
                </div>

                <div class="ck-status-list">
                    @foreach($tahapan as $i => $label)
                        @php $no = $i + 1; @endphp
                        <div class="ck-status-baris {{ $no === $langkahAktif ? 'aktif' : '' }}">
                            <div class="ck-status-bulat {{ $no < $langkahAktif ? 'selesai' : ($no === $langkahAktif ? 'aktif' : 'depan') }}">
                                @if($no < $langkahAktif)
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                @else
                                    {{ $no }}
                                @endif
                            </div>
                            <span>{{ $label }}</span>
                            @if($no === $langkahAktif)
                                <span class="ck-badge-aktif">Aktif</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Peringatan selama catatan atau hasil belum lengkap --}}
            @if(empty($item['catatan']) || empty($item['hasil']))
            <div class="ck-peringatan">
                <div class="ck-peringatan-judul">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    Catatan Belum Diisi
                </div>
                <p>Isi catatan dan hasil konseling sebelum menyelesaikan sesi.</p>
            </div>
            @endif

        </div>

    </div>
</div>

<script>
    (function () {
        var catatan = document.getElementById('ckCatatan');
        var tombolSelesai = document.getElementById('ckSelesai');
        var tombolDarurat = document.getElementById('ckDarurat');
        var endpoint = @json(route('konselor.detail-sesi.catatan', $kode));
        var csrf = @json(csrf_token());

        function perbaruiTombolSelesai() {
            var terisi = catatan.value.trim().length > 0 && document.getElementById('ckHasil').value.trim().length > 0;
            tombolSelesai.disabled = !terisi || @json(($item['status'] ?? '') === 'Selesai');
        }

        catatan.addEventListener('input', perbaruiTombolSelesai);
        document.getElementById('ckHasil').addEventListener('change', perbaruiTombolSelesai);
        perbaruiTombolSelesai();

        async function simpan(selesai) {
            var response = await fetch(endpoint, { method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf}, body:JSON.stringify({catatan:catatan.value, hasil:document.getElementById('ckHasil').value, selesai:selesai}) });
            var body = await response.json().catch(function () { return {}; });
            if (!response.ok) throw new Error(body.message || 'Gagal menyimpan catatan');
            if (selesai) {
                alert('Konseling berhasil diselesaikan. Catatan dan hasil sudah tersimpan.');
                window.location.reload();
            }
        }
        tombolSelesai.addEventListener('click', function () { simpan(true).catch(function (e) { alert(e.message || 'Catatan gagal disimpan.'); }); });
        tombolDarurat.addEventListener('click', async function () {
            if (!confirm('Tandai pengajuan ini sebagai kasus darurat untuk Admin dan Koordinator?')) return;
            try {
                var response = await fetch(@json(route('konselor.detail-sesi.darurat', $kode)), {method:'POST', headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf,'X-Requested-With':'XMLHttpRequest'}});
                var body = await response.json().catch(function () { return {}; });
                if (!response.ok) throw new Error(body.message || 'Gagal menandai kasus darurat.');
                alert(body.message || 'Pengajuan ditandai sebagai kasus darurat.');
                tombolDarurat.disabled = true;
                document.getElementById('ckDaruratLabel').textContent = 'Sudah Ditandai Darurat';
            } catch (e) { alert(e.message || 'Gagal menandai kasus darurat.'); }
        });
    })();
</script>

<style>
    .ck-who5-list{display:grid;gap:8px;margin-top:10px}.ck-who5-list>div{display:grid;gap:3px;padding:10px 12px;border:1px solid #e2ebe4;border-radius:10px;background:#fbfdfb}.ck-who5-list strong{font-size:12px;color:#33463a}.ck-who5-list span{font-size:12px;color:#477055}
    .ck-kembali {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #2f6b52;
        font-size: 13.5px;
        font-weight: 600;
        text-decoration: none;
        margin-bottom: 16px;
    }
    .ck-kembali svg { width: 15px; height: 15px; }
    .ck-kembali:hover { text-decoration: underline; }

    .ck-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 340px;
        gap: 24px;
        align-items: start;
    }
    .ck-kolom-utama,
    .ck-kolom-samping {
        display: flex;
        flex-direction: column;
        gap: 24px;
        min-width: 0;
    }
    .ck-kolom-samping {
        position: sticky;
        top: 16px;
    }

    .kartu.ck-kartu {
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(22, 36, 29, 0.06), 0 1px 2px rgba(22, 36, 29, 0.04);
        border: 1px solid #eef2ef;
        padding: 24px !important;
        box-sizing: border-box;
    }
    .ck-kartu { margin: 0; }

    .ck-kartu-judul {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 18px;
        padding-bottom: 14px;
        border-bottom: 1px solid #eef2ef;
    }
    .ck-kartu-ikon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 9px;
        background: #eaf3ee;
        color: #2f6b52;
        flex-shrink: 0;
    }
    .ck-kartu-ikon svg { width: 16px; height: 16px; }
    .ck-kartu-judul h2 { margin: 0; font-size: 15px; font-weight: 700; color: #16241d; }

    /* Informasi Pemohon */
    .ck-profil-baris {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px 12px;
        margin-bottom: 16px;
    }
    .ck-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #2f6b52;
        color: #fff;
        font-weight: 700;
        font-size: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .ck-profil-info {
        display: flex;
        flex-direction: column;
        gap: 1px;
        flex: 1 1 180px;
        min-width: 0;
    }
    .ck-profil-info b {
        font-size: 15px;
        color: #16241d;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .ck-profil-info span {
        font-size: 12.5px;
        color: #8a978f;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .ck-badge-kategori {
        margin-left: auto;
        flex-shrink: 1;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ck-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }
    .ck-info-grid-jarak { margin-top: 12px; }
    .ck-info-kotak {
        display: flex;
        flex-direction: column;
        gap: 3px;
        background: #f6f9f7;
        border-radius: 10px;
        padding: 10px 12px;
    }
    .ck-info-label {
        font-size: 11px;
        color: #8a978f;
        text-transform: uppercase;
        letter-spacing: .02em;
    }
    .ck-info-nilai {
        font-size: 13.5px;
        font-weight: 600;
        color: #16241d;
        overflow-wrap: break-word;
    }

    .ck-halaman .lencana {
        display: inline-flex;
        align-items: center;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 700;
        white-space: nowrap;
        line-height: 1.6;
    }
    .ck-halaman .lencana-kategori { background: #eef2ef; color: #45564d; }
    .ck-halaman .lencana-selesai { background: #dff3e6; color: #1f7a4d; }

    .ck-span-2 { grid-column: 1 / -1; }

    .ck-wa-baris { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
    .ck-tombol-wa {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: 999px;
        background: #25a05a;
        color: #fff;
        font-size: 11.5px;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .ck-tombol-wa svg { width: 12px; height: 12px; }
    .ck-tombol-wa:hover { background: #1d8449; }

    .ck-hint {
        display: flex;
        align-items: center;
        gap: 6px;
        margin: 8px 0 0;
        font-size: 12px;
        color: #8a978f;
    }
    .ck-hint svg { width: 13px; height: 13px; flex-shrink: 0; }

    /* Permasalahan Konseli */
    .ck-darurat {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        background: #fdecea;
        border: 1px solid #f5c6c2;
        color: #8c1d18;
        border-radius: 10px;
        padding: 12px 14px;
        font-size: 13px;
        line-height: 1.5;
        margin-bottom: 16px;
    }
    .ck-darurat svg { width: 17px; height: 17px; flex-shrink: 0; margin-top: 2px; }

    .ck-meta-baris {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 16px;
    }
    .ck-meta { display: flex; flex-direction: column; gap: 6px; align-items: flex-start; }
    .ck-meta-blok { margin-bottom: 16px; align-items: stretch; }
    .ck-meta-blok:last-child { margin-bottom: 0; }

    .ck-tingkat {
        display: inline-flex;
        padding: 3px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }
    .ck-tingkat-ringan { background: #dff3e6; color: #1f7a4d; }
    .ck-tingkat-sedang { background: #fdf0d2; color: #92720c; }
    .ck-tingkat-berat  { background: #fdecea; color: #b3261e; }

    .ck-chip-baris { display: flex; flex-wrap: wrap; gap: 6px; }
    .ck-chip {
        background: #eaf3ee;
        color: #2f6b52;
        border-radius: 999px;
        padding: 4px 12px;
        font-size: 12.5px;
        font-weight: 600;
    }

    .ck-uraian {
        background: #f6f9f7;
        border-left: 3px solid #2f6b52;
        border-radius: 0 10px 10px 0;
        padding: 14px 16px;
        font-size: 14px;
        line-height: 1.7;
        color: #22302a;
        white-space: pre-line;
        overflow-wrap: break-word;
    }
    .ck-rahasia {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11.5px;
        color: #8a978f;
    }
    .ck-rahasia svg { width: 12px; height: 12px; flex-shrink: 0; }

    /* Riwayat Konseling (tabel) */
    .ck-kartu .tabel-bungkus { overflow-x: auto; }
    .ck-kartu .tabel-slim {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .ck-kartu .tabel-slim th,
    .ck-kartu .tabel-slim td {
        padding: 11px 14px;
        text-align: left;
        white-space: nowrap;
        border-bottom: 1px solid #eef2ef;
    }
    .ck-kartu .tabel-slim thead th {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .02em;
        color: #8a978f;
        background: #f6f9f7;
        border-bottom: 1px solid #eef2ef;
    }
    .ck-kartu .tabel-slim thead th:first-child { border-top-left-radius: 8px; }
    .ck-kartu .tabel-slim thead th:last-child { border-top-right-radius: 8px; }
    .ck-kartu .tabel-slim tbody tr:last-child td { border-bottom: none; }
    .ck-kartu .tabel-slim td { color: #33403a; }
    .ck-kartu .tabel-slim .lencana { display: inline-block; }
    .ck-kartu .tabel-kosong {
        padding: 24px 14px;
        text-align: center;
        color: #8a978f;
        font-size: 13px;
        white-space: normal;
    }

    /* Catatan Sesi Konseling */
    .ck-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #16241d;
        margin-bottom: 8px;
    }
    .ck-label-jarak { margin-top: 18px; }
    .ck-wajib { color: #b3261e; }
    .ck-textarea {
        width: 100%;
        border: 1px solid #dfe6e1;
        border-radius: 10px;
        padding: 12px 14px;
        font-size: 13.5px;
        font-family: inherit;
        color: #33403a;
        resize: vertical;
        min-height: 120px;
        line-height: 1.6;
        outline: none;
        box-sizing: border-box;
    }
    .ck-textarea:focus { border-color: #6faf8a; }
    .ck-select {
        width: 100%;
        border: 1px solid #dfe6e1;
        border-radius: 10px;
        padding: 11px 14px;
        font-size: 13.5px;
        font-family: inherit;
        color: #33403a;
        background: #fff;
        outline: none;
        box-sizing: border-box;
    }
    .ck-select:focus { border-color: #6faf8a; }

    /* Rekomendasi & Tindakan */
    .ck-aksi-baris {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .ck-tombol {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        min-height: 46px;
        padding: 12px 18px;
        border-radius: 10px;
        font-family: inherit;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.2;
        cursor: pointer;
        box-sizing: border-box;
        transition: background .15s, border-color .15s, color .15s;
    }
    .ck-tombol svg { width: 16px; height: 16px; flex-shrink: 0; }
    .ck-tombol:focus-visible { outline: 2px solid #6faf8a; outline-offset: 2px; }
    .ck-tombol-hijau {
        background: #2f6b52;
        border: 1px solid #2f6b52;
        color: #fff;
        box-shadow: 0 2px 6px rgba(47, 107, 82, 0.25);
    }
    .ck-tombol-hijau:hover:not(:disabled) { background: #24543f; border-color: #24543f; }
    .ck-tombol-hijau:disabled {
        background: #cfe1d7;
        border-color: #cfe1d7;
        color: #fff;
        box-shadow: none;
        cursor: not-allowed;
    }
    .ck-tombol-bahaya {
        background: #fff;
        border: 1px solid #f0b9b4;
        color: #b3261e;
    }
    .ck-tombol-bahaya:hover:not(:disabled) { background: #fdecea; border-color: #e59a94; }
    .ck-tombol-bahaya:disabled {
        background: #f6f9f7;
        border-color: #e3e9e5;
        color: #a6b3ac;
        cursor: not-allowed;
    }
    .ck-hint-aksi { margin-top: 12px; }

    /* Kolom samping */
    .ck-info-baris {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 10px 0;
        border-bottom: 1px solid #eef2ef;
    }
    .ck-info-baris:last-child { border-bottom: none; padding-bottom: 0; }
    .ck-info-baris:first-child { padding-top: 0; }
    .ck-info-baris svg { width: 16px; height: 16px; color: #8a978f; margin-top: 2px; flex-shrink: 0; }
    .ck-info-baris > div { display: flex; flex-direction: column; gap: 2px; }
    .ck-info-baris .ck-info-label { font-size: 11px; }
    .ck-info-baris .ck-info-nilai { font-size: 13.5px; }

    .ck-status-list { display: flex; flex-direction: column; }
    .ck-status-baris {
        position: relative;
        display: flex;
        align-items: center;
        gap: 12px;
        padding-bottom: 18px;
    }
    .ck-status-baris:last-child { padding-bottom: 0; }
    .ck-status-baris:not(:last-child)::before {
        content: "";
        position: absolute;
        left: 10px;
        top: 22px;
        bottom: 0;
        width: 2px;
        background: #e6ece8;
    }
    .ck-status-baris span { font-size: 13px; color: #5c6b63; }
    .ck-status-baris.aktif span { color: #16241d; font-weight: 700; }
    .ck-status-bulat {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
        flex-shrink: 0;
    }
    .ck-status-bulat svg { width: 12px; height: 12px; }
    .ck-status-bulat { position: relative; z-index: 1; }
    .ck-status-bulat.selesai { background: #2f6b52; color: #fff; }
    .ck-status-bulat.aktif { background: #16241d; color: #fff; }
    .ck-status-bulat.depan { background: #e6ece8; color: #a6b3ac; }
    .ck-badge-aktif {
        margin-left: auto;
        background: #dff3e6;
        color: #1f7a4d;
        font-size: 10.5px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 999px;
    }

    /* Catatan Belum Diisi */
    .ck-peringatan {
        background: #fdf6e8;
        border: 1px solid #f3e3b8;
        border-radius: 14px;
        padding: 16px;
    }
    .ck-peringatan-judul {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #92720c;
        font-weight: 700;
        font-size: 13.5px;
        margin-bottom: 6px;
    }
    .ck-peringatan-judul svg { width: 16px; height: 16px; }
    .ck-peringatan p {
        margin: 0;
        font-size: 12.5px;
        color: #8a7a4f;
        line-height: 1.5;
    }

    @media (max-width: 900px) {
        .ck-grid { grid-template-columns: 1fr; }
        .ck-info-grid { grid-template-columns: 1fr; }
        .ck-meta-baris { grid-template-columns: 1fr; }
        .ck-kolom-samping { position: static; }
        .ck-kartu-judul { margin-bottom: 14px; padding-bottom: 12px; }
        .kartu.ck-kartu { padding: 18px !important; }
    }
</style>

@endsection
