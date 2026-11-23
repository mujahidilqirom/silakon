@extends('layouts.app')

@section('title', 'Detail Status Pengajuan — SILAKON LPPP UNNES')

@section('content')

{{--
    Variabel yang diharapkan terkirim dari controller ($pengajuan berupa array/objek):

    $pengajuan = [
        'kode_pengajuan'   => 'SLK-2024-001234',
        'status'           => 'terjadwal',        // slug status pengajuan saat ini
        'status_label'     => 'Terjadwal',
        'status_class'     => 'st-green',          // class badge, lihat opsi di bawah
        'nama'             => 'Budi Santoso',
        'jenis_konseli'    => 'Mahasiswa',
        'kategori'         => 'Akademik',
        'tanggal_pengajuan'=> '5 Agustus 2024',
        'riwayat' => [
            [
                'label'      => 'Pengajuan Diterima',
                'status'     => 'selesai',         // selesai | aktif | menunggu
                'waktu'      => '5 Agt 2024, 10:23',
                'keterangan' => null,
                'detail'     => null,
            ],
            [
                'label'      => 'Diverifikasi',
                'status'     => 'selesai',
                'waktu'      => '5 Agt 2024, 14:05',
                'keterangan' => 'Pengajuan telah diverifikasi oleh admin',
                'detail'     => null,
            ],
            [
                'label'      => 'Pengajuan Diterima',
                'status'     => 'selesai',
                'waktu'      => '6 Agt 2024, 09:00',
                'keterangan' => null,
                'detail'     => null,
            ],
            [
                'label'      => 'Pengajuan Diterima',
                'status'     => 'selesai',
                'waktu'      => '6 Agt 2024, 11:30',
                'keterangan' => null,
                'detail'     => null,
            ],
            [
                'label'      => 'Terjadwal',
                'status'     => 'aktif',
                'waktu'      => '6 Agt 2024, 14:00',
                'keterangan' => null,
                'detail'     => [
                    'konselor' => 'Dr. Sari Dewi, M.Pd',
                    'tanggal'  => 'Rabu, 14 Agustus 2024',
                    'waktu'    => '10.00 – 11.00 WIB',
                    'metode'   => 'Tatap Muka',
                    'lokasi'   => 'Lab BK FIP, Gedung FIP UNNES',
                ],
            ],
            [
                'label'      => 'Selesai',
                'status'     => 'menunggu',
                'waktu'      => null,
                'keterangan' => null,
                'detail'     => null,
            ],
        ],
    ];
--}}

@php
    $riwayat = collect($pengajuan['riwayat']);
    $tahapan = [
        'menunggu_pemeriksaan' => 'Menunggu Pemeriksaan',
        'diterima' => 'Pengajuan Diterima',
        'terjadwal' => 'Terjadwal',
        'selesai' => 'Selesai',
    ];
    $urutanTahap = array_keys($tahapan);
    $statusSaatIni = $pengajuan['status'];
    $tahap = match ($statusSaatIni) {
        'menunggu_verifikasi', 'menunggu_pemeriksaan' => 1,
        'diterima' => 2,
        'ditugaskan', 'menunggu_jadwal' => 2,
        'terjadwal', 'berlangsung' => 3,
        'selesai' => 4,
        'sesi_lanjutan' => 4,
        default => 1,
    };
    $total = count($tahapan);
    $pesanStatus = match ($statusSaatIni) {
        'menunggu_verifikasi', 'menunggu_pemeriksaan' => [
            'kelas' => 'cs-message-wait', 'judul' => 'Cerita Anda sedang diperiksa',
            'isi' => 'Setiap cerita membutuhkan waktu untuk didengarkan dengan baik. Pengajuan Anda sedang diperiksa oleh Admin. Silakan menunggu dengan tenang dan periksa kembali statusnya secara berkala.',
            'kutipan' => 'Keberanian untuk mencari bantuan adalah langkah kecil yang berarti.',
        ],
        'diterima', 'ditugaskan', 'menunggu_jadwal' => [
            'kelas' => 'cs-message-accepted', 'judul' => 'Langkah Anda telah diterima',
            'isi' => 'Pengajuan Anda telah diterima. Waktu yang tepat akan dipersiapkan agar Anda dapat berbicara dengan nyaman dan aman.',
            'kutipan' => 'Anda tidak harus menyiapkan kata-kata yang sempurna. Cukup hadir sebagai diri sendiri.',
        ],
        'terjadwal', 'berlangsung' => [
            'kelas' => 'cs-message-scheduled', 'judul' => 'Waktu untuk berbicara telah disiapkan',
            'isi' => 'Datanglah dengan tenang. Tidak perlu membawa kata-kata yang sempurna—cukup hadir dan ceritakan apa yang Anda rasakan.',
            'kutipan' => 'Satu ruang aman dapat menjadi awal dari langkah yang lebih ringan.',
        ],
        'selesai' => [
            'kelas' => 'cs-message-done', 'judul' => 'Terima kasih telah membuka ruang untuk diri sendiri',
            'isi' => 'Tidak semua hal harus selesai hari ini, tetapi setiap langkah tetap berarti. Semoga perjalanan Anda terasa sedikit lebih ringan setelah ini.',
            'kutipan' => 'Merawat diri bukan tanda kelemahan, melainkan bentuk keberanian.',
        ],
        'sesi_lanjutan' => [
            'kelas' => 'cs-message-wait', 'judul' => 'Perjalanan ini dapat dilanjutkan',
            'isi' => 'Beberapa cerita membutuhkan lebih dari satu pertemuan untuk dipahami. Anda tidak harus melewati semuanya sekaligus.',
            'kutipan' => 'Berjalan perlahan tetap berarti bergerak maju.',
        ],
        'ditolak', 'dibatalkan' => [
            'kelas' => 'cs-message-rejected', 'judul' => 'Jangan merasa sendirian dalam proses ini',
            'isi' => 'Pengajuan ini belum dapat dilanjutkan. Silakan membaca penjelasan yang tersedia dan hubungi Admin bila membutuhkan arahan berikutnya.',
            'kutipan' => 'Satu pintu yang tertutup bukan berarti tidak ada jalan lain untuk mendapatkan bantuan.',
        ],
        default => [
            'kelas' => 'cs-message-wait', 'judul' => 'Terima kasih telah menghubungi kami',
            'isi' => 'Informasi pengajuan Anda sedang dipersiapkan. Silakan pantau halaman ini untuk pembaruan berikutnya.',
            'kutipan' => 'Anda layak didengarkan dengan aman dan penuh hormat.',
        ],
    };
@endphp

<section class="section-pad cs-detail-pad">
    <div class="container cs-detail-container">
        @if(session('pengajuan_aktif'))
            <div class="cs-active-alert" role="status">
                <strong>Pengajuan masih aktif</strong>
                <span>{{ session('pengajuan_aktif') }}</span>
            </div>
        @endif

        <div class="cs-message-card {{ $pesanStatus['kelas'] }}" role="status">
            <div class="cs-message-icon" aria-hidden="true">
                @if($statusSaatIni === 'selesai') ✓ @elseif(in_array($statusSaatIni, ['ditolak','dibatalkan'], true)) ! @else ♥ @endif
            </div>
            <div class="cs-message-content">
                <p class="cs-message-eyebrow">Untuk Anda</p>
                <h2>{{ $pesanStatus['judul'] }}</h2>
                <p>{{ $pesanStatus['isi'] }}</p>
                <blockquote>“{{ $pesanStatus['kutipan'] }}”</blockquote>
                <a class="cs-message-action" href="#cs-timeline">Lihat perjalanan pengajuan <span aria-hidden="true">↓</span></a>
            </div>
        </div>

        <a href="{{ route('cek-status.index') }}" class="cs-back">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
            Kembali ke Cek Status
        </a>

        {{-- ===================== RINGKASAN STATUS ===================== --}}
        <div class="cs-summary">
            <h1 class="cs-detail-title">Detail Status Pengajuan</h1>

            <div class="cs-summary-row">
                <div class="cs-kode-wrap">
                    <span class="cs-kode" id="cs-kode">{{ $pengajuan['kode_pengajuan'] }}</span>
                    <button type="button" class="cs-copy" data-copy="#cs-kode" aria-label="Salin kode pengajuan">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        <span data-copy-text>Salin</span>
                    </button>
                </div>
                <span class="status-badge {{ $pengajuan['status_class'] }}">{{ $pengajuan['status_label'] }}</span>
            </div>

            <div class="cs-progress" role="img" aria-label="Tahap {{ $tahap }} dari {{ $total }}">
                <div class="cs-progress-bar">
                    @foreach (array_values($tahapan) as $index => $labelTahap)
                        <span class="cs-seg cs-seg--{{ $index + 1 < $tahap ? 'selesai' : ($index + 1 === $tahap ? 'aktif' : 'menunggu') }}"></span>
                    @endforeach
                </div>
                <p class="cs-progress-text">Tahap <strong>{{ $tahap }}</strong> dari {{ $total }}</p>
            </div>
        </div>

        {{-- ===================== DATA KONSELI ===================== --}}
        <div class="cs-panel">
            <h2 class="cs-panel-title">Data Konseli</h2>

            <dl class="cs-data-grid">
                <div class="cs-data-item">
                    <span class="cs-data-icon" aria-hidden="true">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </span>
                    <div>
                        <dt class="cs-data-label">Nama</dt>
                        <dd class="cs-data-value">{{ $pengajuan['nama'] }}</dd>
                    </div>
                </div>
                <div class="cs-data-item">
                    <span class="cs-data-icon" aria-hidden="true">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c3 2 9 2 12 0v-5"/></svg>
                    </span>
                    <div>
                        <dt class="cs-data-label">Jenis konseli</dt>
                        <dd class="cs-data-value">{{ $pengajuan['jenis_konseli'] }}</dd>
                    </div>
                </div>
                <div class="cs-data-item">
                    <span class="cs-data-icon" aria-hidden="true">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8Z"/><circle cx="7.5" cy="7.5" r="1"/></svg>
                    </span>
                    <div>
                        <dt class="cs-data-label">Kategori</dt>
                        <dd class="cs-data-value">{{ $pengajuan['kategori'] }}</dd>
                    </div>
                </div>
                <div class="cs-data-item">
                    <span class="cs-data-icon" aria-hidden="true">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    </span>
                    <div>
                        <dt class="cs-data-label">Tanggal pengajuan</dt>
                        <dd class="cs-data-value">{{ $pengajuan['tanggal_pengajuan'] }}</dd>
                    </div>
                </div>
            </dl>
        </div>

        {{-- ===================== RIWAYAT STATUS ===================== --}}
        <div class="cs-panel" id="cs-timeline">
            <h2 class="cs-panel-title">Riwayat Status</h2>

            <ol class="cs-timeline">
                @foreach ($pengajuan['riwayat'] as $item)
                    <li class="cs-tl-item cs-tl-{{ $item['status'] }}">
                        <span class="cs-tl-dot" aria-hidden="true">
                            @if ($item['status'] === 'selesai')
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                            @elseif ($item['status'] === 'aktif')
                                <span class="cs-tl-pulse"></span>
                            @endif
                        </span>

                        <div class="cs-tl-body">
                            <div class="cs-tl-heading">
                                <h3>{{ $item['label'] }}</h3>
                                @if ($item['status'] === 'menunggu')
                                    <span class="cs-tl-pending">Menunggu</span>
                                @else
                                    <span class="cs-tl-tag cs-tl-tag--{{ $item['status'] }}">
                                        {{ $item['status'] === 'selesai' ? 'Selesai' : 'Sedang berjalan' }}
                                    </span>
                                @endif
                            </div>

                            @if ($item['waktu'])
                                <p class="cs-tl-time">{{ $item['waktu'] }}</p>
                            @endif

                            @if (!empty($item['keterangan']))
                                <p class="cs-tl-note">{{ $item['keterangan'] }}</p>
                            @endif

                            @if (!empty($item['detail']))
                                <div class="cs-session-box">
                                    <p class="cs-session-title">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                                        Informasi sesi konseling
                                    </p>
                                    <dl class="cs-session-grid">
                                        <div>
                                            <dt>Konselor</dt>
                                            <dd>{{ $item['detail']['konselor'] }}</dd>
                                        </div>
                                        <div>
                                            <dt>Metode</dt>
                                            <dd>{{ $item['detail']['metode'] }}</dd>
                                        </div>
                                        <div>
                                            <dt>Tanggal</dt>
                                            <dd>{{ $item['detail']['tanggal'] }}</dd>
                                        </div>
                                        <div>
                                            <dt>Waktu</dt>
                                            <dd>{{ $item['detail']['waktu'] }}</dd>
                                        </div>
                                        <div class="cs-session-wide">
                                            <dt>Lokasi</dt>
                                            <dd>{{ $item['detail']['lokasi'] }}</dd>
                                        </div>
                                    </dl>
                                </div>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>

        {{-- ===================== KONTAK ===================== --}}
        <div class="cs-contact-box">
            <span class="cs-contact-icon" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2Z"/></svg>
            </span>
            <div>
                <p class="cs-contact-title">Ada pertanyaan?</p>
                <p>
                    Hubungi LPPP UNNES melalui WhatsApp atau email
                    <a href="mailto:konseling@unnes.ac.id">konseling@unnes.ac.id</a>
                </p>
            </div>
        </div>

    </div>
</section>

{{-- =====================================================================
     CSS TAMBAHAN (khusus halaman Detail Status)
     Class yang dipakai ulang dari landing/panduan:
     .container .section-pad .status-badge .st-yellow .st-blue .st-purple
     .st-green .st-mint .st-red
     ===================================================================== --}}
<style>
    .cs-detail-container { max-width: 760px; }

    .cs-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 14px;
        color: #6B7280;
        text-decoration: none;
        margin-bottom: 18px;
        padding: 4px 10px 4px 4px;
        border-radius: 999px;
        transition: color .15s, background-color .15s;
    }
    .cs-back:hover { color: #2F6B3F; background: #EEF6EF; }

    /* ---------- Ringkasan ---------- */
    .cs-summary {
        background: linear-gradient(160deg, #F2F8F3 0%, #FFFFFF 70%);
        border: 1px solid #DCE8DE;
        border-radius: 20px;
        padding: 28px;
        margin-bottom: 22px;
    }
    .cs-detail-title {
        font-size: clamp(22px, 3.4vw, 28px);
        font-weight: 700;
        letter-spacing: -.01em;
        margin: 0 0 16px;
    }
    .cs-summary-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px 14px;
        margin-bottom: 22px;
    }
    .cs-kode-wrap {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #fff;
        border: 1px solid #DDE3D9;
        border-radius: 12px;
        padding: 4px 4px 4px 14px;
    }
    .cs-kode {
        font-family: 'SFMono-Regular', Consolas, monospace;
        font-size: 14px;
        font-weight: 600;
        color: #1F2937;
        letter-spacing: .02em;
    }
    .cs-copy {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font: inherit;
        font-size: 12px;
        font-weight: 600;
        color: #2F6B3F;
        background: #EEF6EF;
        border: 0;
        border-radius: 9px;
        padding: 7px 10px;
        cursor: pointer;
        transition: background-color .15s;
    }
    .cs-copy:hover { background: #DCEDDF; }
    .cs-copy:focus-visible { outline: 3px solid rgba(75, 138, 91, .35); outline-offset: 1px; }
    .cs-copy.is-copied { background: #4B8A5B; color: #fff; }

    /* progress bertahap */
    .cs-progress-bar {
        display: flex;
        gap: 6px;
    }
    .cs-seg {
        flex: 1;
        height: 8px;
        border-radius: 999px;
        background: #E3E8DF;
    }
    .cs-seg--selesai { background: #4B8A5B; }
    .cs-seg--aktif {
        background: linear-gradient(90deg, #4B8A5B 0%, #8CC49A 100%);
    }
    .cs-progress-text {
        margin: 10px 0 0;
        font-size: 13px;
        color: #6B7280;
    }
    .cs-progress-text strong { color: #1F2937; }

    /* ---------- Panel ---------- */
    .cs-panel {
        background: #fff;
        border: 1px solid #E3E8DF;
        border-radius: 20px;
        padding: 26px 28px;
        margin-bottom: 22px;
        box-shadow: 0 1px 2px rgba(31, 41, 55, .04);
    }
    .cs-panel-title {
        font-size: 16px;
        font-weight: 700;
        color: #1F2937;
        margin: 0 0 22px;
    }

    .cs-data-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 22px 20px;
        margin: 0;
    }
    .cs-data-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    .cs-data-icon {
        flex: 0 0 36px;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: #EEF6EF;
        color: #2F6B3F;
    }
    .cs-data-label {
        font-size: 12px;
        color: #9CA3AF;
        margin: 0 0 3px;
    }
    .cs-data-value {
        font-size: 15px;
        font-weight: 600;
        color: #1F2937;
        margin: 0;
        overflow-wrap: anywhere;
    }

    /* ---------- Timeline ---------- */
    .cs-timeline {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .cs-tl-item {
        position: relative;
        display: flex;
        gap: 18px;
        padding-bottom: 28px;
    }
    .cs-tl-item:last-child { padding-bottom: 0; }
    .cs-tl-item::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 34px;
        bottom: 2px;
        width: 2px;
        border-radius: 2px;
        background: #E3E8DF;
    }
    .cs-tl-item:last-child::before { display: none; }
    .cs-tl-selesai::before { background: #4B8A5B; }

    .cs-tl-dot {
        flex: 0 0 32px;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1;
    }
    .cs-tl-selesai .cs-tl-dot { background: #4B8A5B; color: #fff; }
    .cs-tl-aktif .cs-tl-dot {
        background: #fff;
        border: 2px solid #4B8A5B;
        box-shadow: 0 0 0 5px rgba(75, 138, 91, .14);
    }
    .cs-tl-pulse {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #4B8A5B;
        animation: cs-pulse 1.8s ease-in-out infinite;
    }
    @keyframes cs-pulse {
        0%, 100% { transform: scale(1); opacity: 1; }
        50%      { transform: scale(.7); opacity: .55; }
    }
    .cs-tl-menunggu .cs-tl-dot { background: #fff; border: 2px solid #DDE3D9; }

    .cs-tl-body { flex: 1; min-width: 0; padding-top: 4px; }
    .cs-tl-heading {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px 10px;
    }
    .cs-tl-heading h3 {
        font-size: 15px;
        font-weight: 700;
        color: #1F2937;
        margin: 0;
    }
    .cs-tl-menunggu .cs-tl-heading h3 { color: #9CA3AF; font-weight: 600; }

    .cs-tl-tag {
        font-size: 11px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 999px;
    }
    .cs-tl-tag--selesai { background: #E9F3EA; color: #2F6B3F; }
    .cs-tl-tag--aktif   { background: #DCFCE7; color: #166534; }
    .cs-tl-pending { font-size: 12px; color: #9CA3AF; }

    .cs-tl-time { font-size: 13px; color: #9CA3AF; margin: 4px 0 0; }
    .cs-tl-note { font-size: 13px; line-height: 1.55; color: #6B7280; margin: 4px 0 0; }

    .cs-session-box {
        background: #F2F8F3;
        border: 1px solid #DCE8DE;
        border-left: 4px solid #4B8A5B;
        border-radius: 14px;
        padding: 18px 20px;
        margin-top: 14px;
    }
    .cs-session-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 700;
        color: #2F6B3F;
        margin: 0 0 14px;
    }
    .cs-session-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px 20px;
        margin: 0;
    }
    .cs-session-wide { grid-column: 1 / -1; }
    .cs-session-grid dt {
        font-size: 12px;
        color: #6B7F6F;
        margin: 0 0 2px;
    }
    .cs-session-grid dd {
        font-size: 14px;
        font-weight: 600;
        color: #1F2937;
        margin: 0;
        line-height: 1.45;
    }

    /* ---------- Kontak ---------- */
    .cs-contact-box {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        background: #fff;
        border: 1px solid #E3E8DF;
        border-radius: 20px;
        padding: 20px 24px;
    }
    .cs-contact-icon {
        flex: 0 0 38px;
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: #EEF6EF;
        color: #2F6B3F;
    }
    .cs-contact-box p {
        font-size: 14px;
        color: #4B5563;
        margin: 0;
        line-height: 1.6;
    }
    .cs-contact-box .cs-contact-title {
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 2px;
    }
    .cs-contact-box a {
        display: inline-block;
        color: #2F6B3F;
        font-weight: 600;
        text-decoration: none;
        border-bottom: 1.5px solid rgba(47, 107, 63, .3);
    }
    .cs-contact-box a:hover { border-color: #2F6B3F; }

    @media (max-width: 640px) {
        .cs-summary, .cs-panel { padding: 20px; border-radius: 18px; }
        .cs-data-grid { grid-template-columns: 1fr; }
        .cs-session-grid { grid-template-columns: 1fr; }
        .cs-contact-box { padding: 18px 20px; }
    }
    @media (prefers-reduced-motion: reduce) {
        .cs-tl-pulse { animation: none; }
    }
.cs-active-alert { display:flex; flex-direction:column; gap:3px; margin:0 auto 16px; max-width:760px; padding:13px 16px; border:1px solid #bbf7d0; border-radius:12px; background:#f0fdf4; color:#166534; font-size:13px; line-height:1.45; }
.cs-active-alert strong { font-size:14px; }

/* Pesan pendamping yang berubah mengikuti status pengajuan */
.cs-message-card { display:flex; gap:16px; align-items:flex-start; max-width:760px; margin:0 auto 20px; padding:22px 24px; border:1px solid #dce8de; border-radius:20px; background:linear-gradient(135deg,#f8fcf8,#eef7ef); box-shadow:0 8px 24px rgba(43,83,52,.06); }
.cs-message-icon { flex:0 0 42px; width:42px; height:42px; display:flex; align-items:center; justify-content:center; border-radius:14px; background:#dff1e1; color:#3d7650; font-size:21px; font-weight:700; }
.cs-message-content { min-width:0; }
.cs-message-eyebrow { margin:0 0 3px; color:#6d8573; font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; }
.cs-message-content h2 { margin:0 0 7px; color:#203c29; font-size:18px; line-height:1.35; }
.cs-message-content > p:not(.cs-message-eyebrow) { margin:0; color:#506257; font-size:13.5px; line-height:1.65; }
.cs-message-content blockquote { margin:13px 0 12px; padding-left:12px; border-left:3px solid #92b69a; color:#66806d; font-size:13px; font-style:italic; }
.cs-message-action { display:inline-flex; align-items:center; gap:6px; color:#376d46; font-size:12.5px; font-weight:700; text-decoration:none; }
.cs-message-action:hover { text-decoration:underline; }
.cs-message-accepted { background:linear-gradient(135deg,#f7fbf8,#edf7f0); }
.cs-message-scheduled { background:linear-gradient(135deg,#f4f9ff,#edf5fb); border-color:#d8e8f3; }
.cs-message-scheduled .cs-message-icon { background:#deedf8; color:#28628a; }
.cs-message-done { background:linear-gradient(135deg,#f7fbf4,#eef7e6); border-color:#dce9cd; }
.cs-message-done .cs-message-icon { background:#e1f0d3; color:#4e7b38; }
.cs-message-rejected { background:linear-gradient(135deg,#fff9f7,#fff1ef); border-color:#f2d8d1; }
.cs-message-rejected .cs-message-icon { background:#fde4de; color:#a64c3e; }
.cs-message-rejected .cs-message-content h2 { color:#713d35; }
@media (max-width:640px) { .cs-message-card { padding:18px; border-radius:16px; } .cs-message-icon { flex-basis:36px; width:36px; height:36px; border-radius:11px; } .cs-message-content h2 { font-size:16px; } }
</style>

@endsection

@push('scripts')
<script>
    (function () {
        document.querySelectorAll('[data-copy]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var target = document.querySelector(btn.getAttribute('data-copy'));
                var label = btn.querySelector('[data-copy-text]');
                if (!target || !navigator.clipboard) return;
                navigator.clipboard.writeText(target.textContent.trim()).then(function () {
                    btn.classList.add('is-copied');
                    if (label) label.textContent = 'Tersalin';
                    setTimeout(function () {
                        btn.classList.remove('is-copied');
                        if (label) label.textContent = 'Salin';
                    }, 1800);
                });
            });
        });
    })();
</script>
@endpush
