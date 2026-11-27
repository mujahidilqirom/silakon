@extends('layouts.app')

@section('title', 'Panduan Penggunaan — SILAKON LPPP UNNES')

@section('content')

@php
    $waAdminTampil = data_get($kontakLayanan ?? [], 'wa_admin', '085179510121');
    // Siapa yang dapat menggunakan layanan
    $audiences = [
        [
            'title' => 'Mahasiswa UNNES',
            'desc'  => 'Mahasiswa aktif S1, S2, S3 di semua fakultas Universitas Negeri Semarang.',
            'icon'  => '<path d="M22 10 12 5 2 10l10 5 10-5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>',
        ],
        [
            'title' => 'Alumni UNNES',
            'desc'  => 'Lulusan UNNES yang masih membutuhkan pendampingan karier dan pengembangan diri.',
            'icon'  => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
        ],
        [
            'title' => 'Tenaga Kependidikan',
            'desc'  => 'Staf dan karyawan UNNES yang membutuhkan dukungan profesional.',
            'icon'  => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m16 11 2 2 4-4"/>',
        ],
        [
            'title' => 'Wali Mahasiswa',
            'desc'  => 'Orang tua atau wali yang ingin berkonsultasi mengenai kondisi mahasiswa.',
            'icon'  => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        ],
    ];

    // Langkah-langkah pengajuan
    $steps = [
        [
            'title' => 'Kunjungi Halaman Pengajuan',
            'desc'  => 'Akses halaman "Ajukan Konseling" melalui menu navigasi atau tombol utama di beranda.',
            'icon'  => '<rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>',
        ],
        [
            'title' => 'Pilih Jenis Konseli',
            'desc'  => 'Tentukan kategori diri Anda: Mahasiswa, Alumni, Tenaga Kependidikan, atau Wali Mahasiswa.',
            'icon'  => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        ],
        [
            'title' => 'Isi Data Diri',
            'desc'  => 'Lengkapi formulir data diri dengan informasi yang valid. Mahasiswa perlu mencantumkan NIM, Fakultas, dan Program Studi.',
            'icon'  => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h8"/>',
        ],
        [
            'title' => 'Pilih Kategori Konseling',
            'desc'  => 'Pilih kategori permasalahan yang paling sesuai dengan kebutuhan Anda dari daftar yang tersedia.',
            'icon'  => '<rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M9 12h6M9 16h6"/>',
        ],
        [
            'title' => 'Deskripsikan Permasalahan',
            'desc'  => 'Ceritakan permasalahan Anda secara singkat dan jelas agar konselor dapat mempersiapkan diri dengan baik.',
            'icon'  => '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
        ],
        [
            'title' => 'Pilih Jadwal Preferensi',
            'desc'  => 'Tentukan waktu yang paling nyaman bagi Anda: Pagi, Siang, atau Sore hari.',
            'icon'  => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        ],
        [
            'title' => 'Kirim Pengajuan',
            'desc'  => 'Setujui pernyataan kerahasiaan dan kirimkan pengajuan Anda. Simpan kode pengajuan yang diberikan.',
            'icon'  => '<path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>',
        ],
        [
            'title' => 'Tunggu Konfirmasi',
            'desc'  => 'Tim admin akan memverifikasi pengajuan dalam 1×24 jam dan menghubungi Anda melalui email atau WhatsApp.',
            'icon'  => '<path d="M5 22h14M5 2h14"/><path d="M17 22v-4.17a2 2 0 0 0-.59-1.42L12 12l-4.41 4.41A2 2 0 0 0 7 17.83V22"/><path d="M7 2v4.17a2 2 0 0 0 .59 1.42L12 12l4.41-4.41A2 2 0 0 0 17 6.17V2"/>',
        ],
    ];

    // Penjelasan status pengajuan
    $statuses = [
        ['label' => 'Menunggu Pemeriksaan', 'class' => 'st-yellow', 'desc' => 'Pengajuan sudah tersimpan setelah verifikasi email dan sedang diperiksa Admin.'],
        ['label' => 'Pengajuan Diterima',   'class' => 'st-blue',   'desc' => 'Admin menerima pengajuan dan menyiapkan konselor serta jadwal konseling.'],
        ['label' => 'Terjadwal',            'class' => 'st-green',  'desc' => 'Jadwal konseling sudah ditetapkan. Hadiri sesi sesuai waktu dan lokasi yang ditampilkan.'],
        ['label' => 'Selesai',              'class' => 'st-mint',   'desc' => 'Sesi konseling telah selesai dan tercatat sebagai riwayat layanan.'],
        ['label' => 'Ditolak',              'class' => 'st-red',    'desc' => 'Pengajuan ditolak oleh Admin. Alasan penolakan dapat dilihat pada detail status dan pengajuan baru dapat dikirim kembali.'],
    ];

    // FAQ (jawaban silakan disesuaikan dengan kebijakan LPPP)
    $faqs = [
        [
            'q' => 'Apakah saya perlu membuat akun untuk mengajukan konseling?',
            'a' => 'Tidak perlu. Anda cukup mengisi formulir pengajuan konseling. Setelah pengajuan terkirim, Anda akan mendapatkan kode pengajuan yang dapat digunakan untuk memantau status melalui menu "Cek Status".',
        ],
        [
            'q' => 'Berapa kali saya bisa mengajukan konseling?',
            'a' => 'Anda dapat mengajukan konseling kembali setelah pengajuan sebelumnya selesai diproses. Jika masih membutuhkan pendampingan lanjutan, silakan sampaikan kepada konselor pada sesi berjalan.',
        ],
        [
            'q' => 'Apakah data saya akan dijaga kerahasiaannya?',
            'a' => 'Ya. Seluruh data dan isi konseling bersifat rahasia dan hanya dapat diakses oleh pihak yang berwenang, yaitu admin dan konselor yang menangani pengajuan Anda.',
        ],
        [
            'q' => 'Apa yang terjadi jika saya tidak bisa hadir pada jadwal yang ditetapkan?',
            'a' => 'Segera hubungi admin melalui email atau WhatsApp sebelum jadwal dimulai agar sesi dapat dijadwalkan ulang sesuai ketersediaan konselor.',
        ],
        [
            'q' => 'Bagaimana jika permasalahan saya darurat atau membutuhkan penanganan segera?',
            'a' => 'Untuk kondisi darurat, jangan menunggu proses verifikasi. Hubungi admin LPPP melalui WhatsApp '.$waAdminTampil.' atau email karir@mail.unnes.ac.id, atau segera hubungi layanan kesehatan / orang terdekat Anda.',
        ],
    ];
@endphp

{{-- ===================== HERO ===================== --}}
<section class="page-hero">
    <div class="container">
        <span class="page-hero-badge">Panduan Penggunaan</span>
        <h1>Cara Menggunakan SILAKON</h1>
        <p>
            Ikuti panduan lengkap berikut untuk menggunakan layanan konseling
            SILAKON dengan mudah dan benar.
        </p>
    </div>
</section>

{{-- ===================== SIAPA YANG DAPAT MENGGUNAKAN ===================== --}}
<section class="section-pad">
    <div class="container">
        <div class="section-head">
            <h2>Siapa yang Dapat Menggunakan Layanan Ini?</h2>
        </div>

        <div class="audience-grid">
            @foreach ($audiences as $item)
                <div class="audience-card">
                    <span class="audience-icon">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">{!! $item['icon'] !!}</svg>
                    </span>
                    <h3>{{ $item['title'] }}</h3>
                    <p>{{ $item['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===================== LANGKAH-LANGKAH PENGAJUAN ===================== --}}
<section class="section-pad bg-soft">
    <div class="container">
        <div class="section-head">
            <h2>Langkah-Langkah Pengajuan</h2>
        </div>

        <div class="step-list">
            @foreach ($steps as $i => $step)
                <div class="step-card">
                    <span class="step-num">{{ $i + 1 }}</span>
                    <div class="step-body">
                        <h3>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $step['icon'] !!}</svg>
                            {{ $step['title'] }}
                        </h3>
                        <p>{{ $step['desc'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="section-cta">
            <a href="{{ url('/ajukan-konseling') }}" class="btn btn-primary">Mulai Ajukan Konseling &rarr;</a>
        </div>
    </div>
</section>

{{-- ===================== PENJELASAN STATUS ===================== --}}
<section class="section-pad">
    <div class="container">
        <div class="section-head">
            <h2>Penjelasan Status Pengajuan</h2>
            <p>Pahami arti setiap status pengajuan konseling Anda.</p>
        </div>

        <div class="status-grid">
            @foreach ($statuses as $status)
                <div class="status-card">
                    <span class="status-badge {{ $status['class'] }}">{{ $status['label'] }}</span>
                    <p>{{ $status['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===================== FAQ ===================== --}}
<section class="section-pad bg-soft">
    <div class="container">
        <div class="section-head">
            <h2>Pertanyaan yang Sering Diajukan</h2>
        </div>

        <div class="faq-list">
            @foreach ($faqs as $faq)
                <div class="faq-item">
                    <button class="faq-question">
                        {{ $faq['q'] }}
                        <svg class="chevron" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="faq-answer">
                        <div class="faq-answer-inner">
                            {{ $faq['a'] }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="section-cta">
            <a href="{{ url('/ajukan-konseling') }}" class="btn btn-primary">Ajukan Konseling Sekarang</a>
        </div>
    </div>
</section>

{{-- =====================================================================
     CSS TAMBAHAN (khusus halaman Panduan)
     Class yang sudah ada di landing dipakai ulang:
     .container .section-pad .bg-soft .section-head .btn .btn-primary
     .faq-list .faq-item .faq-question .faq-answer .faq-answer-inner .chevron
     Bagian di bawah hanya class baru yang belum ada di landing.
     ===================================================================== --}}
<style>
    /* ---------- Page hero ---------- */
    .page-hero {
        background: #E9F3EA;
        padding: 46px 0 46px;
        text-align: center;
    }
    .page-hero-badge {
        display: inline-block;
        background: #D7EBDB;
        color: #2F6B3F;
        font-size: 12px;
        font-weight: 600;
        padding: 5px 14px;
        border-radius: 999px;
        margin-bottom: 18px;
    }
    .page-hero h1 {
        font-size: clamp(28px, 4vw, 40px);
        font-weight: 700;
        line-height: 1.2;
        margin: 0 0 14px;
    }
    .page-hero p {
        max-width: 520px;
        margin: 0 auto;
        color: #6B7280;
        font-size: 16px;
        line-height: 1.6;
    }

    /* ---------- Audience cards ---------- */
    .audience-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
        max-width: 1080px;
        margin: 0 auto;
    }
    .audience-card {
        background: #fff;
        border: 1px solid #E5E9E1;
        border-radius: 16px;
        padding: 28px 22px;
        text-align: center;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
    }
    .audience-icon {
        display: block;
        text-align: left;
        color: #4B5563;
        margin-bottom: 14px;
    }
    .audience-card h3 {
        font-size: 15px;
        font-weight: 600;
        margin: 0 0 10px;
    }
    .audience-card p {
        font-size: 13px;
        line-height: 1.6;
        color: #6B7280;
        margin: 0;
    }

    /* ---------- Step list ---------- */
    .step-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
        max-width: 790px;
        margin: 0 auto;
    }
    .step-card {
        display: flex;
        align-items: center;
        gap: 18px;
        background: #fff;
        border: 1px solid #E5E9E1;
        border-radius: 14px;
        padding: 18px 22px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
    }
    .step-num {
        flex: 0 0 44px;
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--primary, #7DA381);
        color: #fff;
        font-weight: 700;
        font-size: 15px;
        border-radius: 12px;
    }
    .step-body h3 {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 600;
        margin: 0 0 4px;
    }
    .step-body h3 svg {
        color: var(--primary, #7DA381);
        flex-shrink: 0;
    }
    .step-body p {
        font-size: 13px;
        line-height: 1.55;
        color: #6B7280;
        margin: 0;
    }

    /* ---------- Section CTA ---------- */
    .section-cta {
        text-align: center;
        margin-top: 36px;
    }

    /* ---------- Status cards ---------- */
    .status-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
        max-width: 790px;
        margin: 0 auto;
    }
    .status-card {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        background: #fff;
        border: 1px solid #E5E9E1;
        border-radius: 14px;
        padding: 20px 22px;
        min-height: 92px;
    }
    .status-card p {
        font-size: 13px;
        line-height: 1.55;
        color: #6B7280;
        margin: 0;
    }
    .status-badge {
        flex-shrink: 0;
        font-size: 11px;
        font-weight: 600;
        padding: 4px 12px;
        border-radius: 999px;
        white-space: nowrap;
    }
    .st-yellow { background: #FEF3C7; color: #92400E; }
    .st-blue   { background: #DBEAFE; color: #1E40AF; }
    .st-purple { background: #EDE9FE; color: #5B21B6; }
    .st-green  { background: #DCFCE7; color: #166534; }
    .st-mint   { background: #E9F3EA; color: #2F6B3F; }
    .st-red    { background: #FEE2E2; color: #B91C1C; }

    /* ---------- Responsive ---------- */
    @media (max-width: 991px) {
        .audience-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 640px) {
        .page-hero { padding: 48px 0 40px; }
        .audience-grid,
        .status-grid { grid-template-columns: 1fr; }
        .step-card { padding: 16px; gap: 14px; }
    }
</style>

@endsection
