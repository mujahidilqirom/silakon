@extends('layouts.app')

@section('title', 'Ajukan Konseling')

@section('content')
@php
    $steps = ['Data Konseli', 'Kategori & Permasalahan', 'Persetujuan', 'Verifikasi'];
    $waAdminLayanan = preg_replace('/^0/', '62', preg_replace('/\D/', '', (string) data_get($kontakLayanan ?? [], 'wa_admin', '085179510121')));
    $waAdminTampil = data_get($kontakLayanan ?? [], 'wa_admin', '085179510121');
    $waDaruratLayanan = preg_replace('/^0/', '62', preg_replace('/\D/', '', (string) data_get($kontakLayanan ?? [], 'wa_darurat', '082134059075')));
    $jamLayanan = data_get($kontakLayanan ?? [], 'jam_layanan', 'Senin–Kamis, 08.00–16.00 WIB; Jumat, 08.00–15.00 WIB');

    $konseliTypes = [
        [
            'value' => 'mahasiswa',
            'title' => 'Mahasiswa',
            'desc'  => 'Mahasiswa aktif UNNES',
            'icon'  => '<path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1.7 2.7 3 6 3s6-1.3 6-3v-5"/>',
        ],
        [
            'value' => 'alumni',
            'title' => 'Alumni',
            'desc'  => 'Lulusan UNNES',
            'icon'  => '<circle cx="12" cy="9" r="6"/><path d="m8.2 14.3-1.4 7 5.2-2.6 5.2 2.6-1.4-7"/>',
        ],
        [
            'value' => 'tenaga_kependidikan',
            'title' => 'Tenaga Kependidikan / Dosen',
            'desc'  => 'Staf & karyawan UNNES',
            'icon'  => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/>',
        ],
        [
            'value' => 'wali_mahasiswa',
            'title' => 'Wali Mahasiswa',
            'desc'  => 'Orang tua atau wali',
            'icon'  => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9"/><path d="M16 3.1a4 4 0 0 1 0 7.8"/>',
        ],
    ];

    $metodeLayanan = [
        ['value' => 'tatap_muka', 'title' => 'Tatap muka', 'desc' => 'Di ruang layanan LPPP UNNES.'],
        ['value' => 'daring', 'title' => 'Daring', 'desc' => 'Melalui panggilan video terjadwal.'],
    ];

    $kondisiDarurat = [
        ['value' => 'ya', 'title' => 'Ya', 'desc' => 'Saya sedang dalam kondisi darurat.'],
        ['value' => 'tidak', 'title' => 'Tidak', 'desc' => 'Saya tidak dalam kondisi darurat.'],
    ];

    $lingkupHambatan = [
        'Motivasi', 'Stres kuliah', 'Depresi', 'Harga diri/kepercayaan diri',
        'Pengekspresian diri/perasaan', 'Kehilangan', 'Kecemasan/panik', 'Trauma',
        'Pengasuhan', 'Home Sickness', 'Putus asa', 'Manajemen Keuangan',
        'Konflik dengan pasangan', 'Tekanan Keluarga', 'Manajemen Waktu',
        'Spiritualitas/Keberagamaan', 'Hambatan di masa transisi', 'Kesepian',
        'Konflik interpersonal', 'Karier', 'Yang lain',
    ];
@endphp

<style>
    .konseling-note[hidden] { display: none; }
    .konseling-field[hidden] { display: none !important; }

    /* Tombol/elemen dengan atribut hidden harus benar-benar tersembunyi (class .btn bisa menimpa display) */
    .konseling-actions [hidden] { display: none !important; }

    /* ---------- Tooltip info (ikon i) ---------- */
    .konseling-label-tip { position: relative; display: block; }
    .konseling-tip-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 18px; height: 18px; margin-left: 6px; padding: 0;
        vertical-align: middle; border: 0; border-radius: 50%;
        background: transparent; color: #6b7280; cursor: help;
        transition: color .15s ease, background-color .15s ease;
    }
    .konseling-tip-btn svg { width: 18px; height: 18px; }
    .konseling-tip-btn:hover,
    .konseling-tip-btn:focus-visible,
    .konseling-label-tip.is-open .konseling-tip-btn { color: #2563eb; background: rgba(37, 99, 235, .1); outline: none; }
    .konseling-tip-bubble {
        position: absolute; left: 0; top: 100%; z-index: 20;
        width: min(300px, 100%); margin-top: 8px; padding: 10px 12px;
        border-radius: 10px; background: #1f2937; color: #fff;
        font-size: 12.5px; font-weight: 400; line-height: 1.5; text-align: left;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .18);
        opacity: 0; visibility: hidden; transform: translateY(-4px);
        transition: opacity .15s ease, transform .15s ease, visibility .15s;
        pointer-events: none;
    }
    .konseling-label-tip:hover .konseling-tip-bubble,
    .konseling-label-tip:focus-within .konseling-tip-bubble,
    .konseling-label-tip.is-open .konseling-tip-bubble {
        opacity: 1; visibility: visible; transform: translateY(0);
    }
    .konseling-note.is-danger { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
    .konseling-note.is-danger a { color: inherit; font-weight: 600; text-decoration: underline; }
    .konseling-note.is-secret { margin-bottom: 10px; }
    .konseling-resend { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-top: 14px; }
    .konseling-resend [data-code-status] { font-size: 13px; }
    .konseling-resend [data-code-status][data-state="error"] { color: #b91c1c; }

    /* ---------- Checkbox (lingkup hambatan & persetujuan) ----------
       Bug: tanda centang tidak tampil saat checkbox masih fokus (baru muncul setelah klik di
       tempat lain). Penyebab: gaya generik .konseling-control:focus menimpa background-image
       tanda centang. Blok ini membuat state :checked selalu menang, termasuk saat :focus/:hover/:active. */
    .konseling-check input[type="checkbox"],
    .konseling-consent input[type="checkbox"] {
        -webkit-appearance: none !important;
        appearance: none !important;
        flex: 0 0 auto;
        width: 20px !important;
        height: 20px !important;
        min-width: 20px;
        padding: 0 !important;
        margin: 0;
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 6px !important;
        background-color: #fff !important;
        background-image: none !important;
        background-repeat: no-repeat !important;
        background-position: center !important;
        background-size: 14px 14px !important;
        box-shadow: none !important;
        cursor: pointer;
        transition: background-color .15s ease, border-color .15s ease;
    }
    .konseling-check input[type="checkbox"]:focus-visible,
    .konseling-consent input[type="checkbox"]:focus-visible {
        outline: 2px solid #16a34a;
        outline-offset: 2px;
    }
    .konseling-check input[type="checkbox"]:checked,
    .konseling-check input[type="checkbox"]:checked:hover,
    .konseling-check input[type="checkbox"]:checked:focus,
    .konseling-check input[type="checkbox"]:checked:active,
    .konseling-consent input[type="checkbox"]:checked,
    .konseling-consent input[type="checkbox"]:checked:hover,
    .konseling-consent input[type="checkbox"]:checked:focus,
    .konseling-consent input[type="checkbox"]:checked:active {
        background-color: #16a34a !important;
        border-color: #16a34a !important;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23fff' stroke-width='3.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M5 12.5l4.5 4.5L19 7.5'/%3E%3C/svg%3E") !important;
    }
    /* Kotak konfirmasi darurat: checkbox | ikon | teks, sejajar rapi dalam satu baris */
    .konseling-darurat-confirm {
        display: grid !important;
        grid-template-columns: 20px 22px minmax(0, 1fr);
        align-items: start !important;
        column-gap: 12px !important;
        width: 100%;
        box-sizing: border-box;
        padding: 16px 18px !important;
        margin-top: 12px;
        border: 1px solid #f2c6c6 !important;
        border-radius: 14px !important;
        background: linear-gradient(135deg, #fff8f8 0%, #fff 100%) !important;
        color: #7f1d1d !important;
        box-shadow: 0 5px 16px rgba(153, 27, 27, .06);
        cursor: pointer;
    }
    .konseling-darurat-confirm:hover,
    .konseling-darurat-confirm:has(input:checked) {
        border-color: #dc8f8f !important;
        background: #fff3f3 !important;
    }
    .konseling-darurat-confirm > input[type="checkbox"] { margin: 1px 0 0 !important; }
    .konseling-darurat-confirm > svg {
        width: 22px;
        height: 22px;
        margin-top: 0;
        color: #b42318;
    }
    .konseling-darurat-confirm .konseling-emergency-copy {
        display: grid;
        gap: 4px;
        min-width: 0;
        line-height: 1.5;
    }
    .konseling-darurat-confirm .konseling-emergency-copy strong {
        color: #991b1b;
        font-size: 14px;
        font-weight: 700;
    }
    .konseling-darurat-confirm .konseling-emergency-copy span {
        color: #7f1d1d;
        font-size: 13px;
        font-weight: 400;
    }

    /* Kotak info/peringatan: ikon kiri, teks mengalir normal di kanan (spasi antar kata terjaga) */
    .konseling-callout {
        display: grid;
        grid-template-columns: 20px minmax(0, 1fr);
        column-gap: 12px;
        align-items: start;
        box-sizing: border-box;
        width: 100%;
        margin-top: 12px;
        padding: 14px 16px;
        border: 1px solid #cfe3d5;
        border-radius: 12px;
        background: #f4faf6;
        color: #2f5d44;
        font-size: 13px;
        line-height: 1.6;
    }
    .konseling-callout > svg { width: 20px; height: 20px; margin-top: 1px; color: currentColor; }
    .konseling-callout > div,
    .konseling-callout > span { display: block; min-width: 0; }
    .konseling-callout strong { font-weight: 700; }
    .konseling-callout.is-danger { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
    .konseling-callout.is-danger a { color: inherit; font-weight: 700; text-decoration: underline; }
    .konseling-callout[hidden] { display: none !important; }

    /* Kolom "lingkup hambatan lainnya": tampil sebagai panel kecil di bawah pilihan */
    .konseling-other-field {
        grid-column: 1 / -1;
        margin-top: 14px !important;
        padding: 14px 16px 12px;
        border: 1px solid #dfe9e2;
        border-left: 3px solid #16a34a;
        border-radius: 12px;
        background: #f9fcfa;
    }
    .konseling-other-field > label { display: block; margin: 0 0 8px; font-size: 13.5px; font-weight: 600; }
    .konseling-other-field > input { width: 100%; box-sizing: border-box; background: #fff; }
    .konseling-other-field > .konseling-hint { display: block; margin-top: 8px; font-size: 12px; line-height: 1.4; }
    /* Atribut hidden harus menang atas display:flex dari .konseling-check. */
    .konseling-darurat-confirm[hidden],
    [data-darurat-alert][hidden] {
        display: none !important;
    }
    .konseling-terms {
        margin: 18px 0 14px;
        border: 1px solid #dce8df;
        border-radius: 14px;
        background: #fbfdfb;
        overflow: hidden;
    }
    .konseling-terms-head {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 13px 16px;
        border-bottom: 1px solid #e6eee8;
        background: #f1f8f3;
        color: #28563d;
        font-size: 13px;
        font-weight: 700;
    }
    .konseling-terms-head svg { width: 18px; height: 18px; flex: 0 0 auto; }
    .konseling-terms-body {
        max-height: 190px;
        overflow-y: auto;
        padding: 14px 17px 16px;
        color: #536158;
        font-size: 12.5px;
        line-height: 1.6;
        scrollbar-color: #a9c8b1 #edf4ef;
    }
    .konseling-terms-body p { margin: 0 0 9px; }
    .konseling-terms-body ol { margin: 0; padding-left: 20px; }
    .konseling-terms-body li { margin: 0 0 7px; padding-left: 3px; }
    .konseling-terms-hint {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 9px 16px;
        border-top: 1px solid #e6eee8;
        color: #718078;
        font-size: 11.5px;
    }
    .konseling-terms-hint.is-read { color: #2f6a4b; }
    .konseling-terms-hint svg { width: 15px; height: 15px; flex: 0 0 auto; }
    .konseling-consent.is-disabled { opacity: .58; }
    .konseling-consent input[type="checkbox"]:disabled { cursor: not-allowed; }

    /* ---------- Radio (metode layanan & kondisi darurat) ----------
       Bug yang sama dengan checkbox: gaya :focus pada .konseling-control menimpa penanda terpilih. */
    .konseling-option-card input[type="radio"] {
        -webkit-appearance: none !important;
        appearance: none !important;
        flex: 0 0 auto;
        width: 20px !important;
        height: 20px !important;
        min-width: 20px;
        padding: 0 !important;
        margin: 2px 0 0;
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 50% !important;
        background-color: #fff !important;
        background-image: none !important;
        box-shadow: none !important;
        cursor: pointer;
        transition: background-color .15s ease, border-color .15s ease;
    }
    .konseling-option-card input[type="radio"]:focus-visible {
        outline: 2px solid #16a34a;
        outline-offset: 2px;
    }
    .konseling-option-card input[type="radio"]:checked,
    .konseling-option-card input[type="radio"]:checked:hover,
    .konseling-option-card input[type="radio"]:checked:focus,
    .konseling-option-card input[type="radio"]:checked:active {
        border-color: #16a34a !important;
        background-color: #16a34a !important;
        background-image: radial-gradient(circle, #fff 0 32%, transparent 36%) !important;
    }
    /* WHO-5 screening card */
    .who5-card{border:1px solid #DCE8DF;background:linear-gradient(180deg,#F7FBF8 0%,#fff 100%);border-radius:16px;padding:20px 22px;margin-bottom:22px;box-shadow:0 8px 22px rgba(42,78,59,.05)}
    .who5-heading{display:flex;gap:13px;align-items:flex-start}.who5-icon{width:36px;height:36px;border-radius:11px;background:#DCEFE2;color:#2F6A4B;display:grid;place-items:center;font-size:18px;flex:none}.who5-heading h3{margin:0;color:#1F2A24;font-size:16px}.who5-heading p{margin:4px 0 0;color:#6B756E;font-size:12.5px;line-height:1.5}.who5-scale{display:flex;justify-content:space-between;margin:18px 0 8px;color:#87948B;font-size:11.5px}.who5-list{display:grid;gap:10px}.who5-question{background:#fff;border:1px solid #E4ECE6;border-radius:12px;padding:13px 14px}.who5-question p{display:flex;gap:9px;align-items:flex-start;margin:0 0 11px;color:#2D3C33;font-size:13px;line-height:1.45}.who5-question p>span{width:22px;height:22px;border-radius:7px;background:#EEF6F0;color:#347052;display:grid;place-items:center;font-weight:700;font-size:11px;flex:none}.who5-options{display:grid;grid-template-columns:repeat(6,1fr);gap:7px}.who5-options label{position:relative}.who5-options input{position:absolute;opacity:0;pointer-events:none}.who5-options label span{height:32px;border:1px solid #DDE7DF;border-radius:8px;display:grid;place-items:center;color:#526158;font-weight:600;font-size:12px;cursor:pointer;transition:.15s}.who5-options input:checked+span{background:#3F6D5D;border-color:#3F6D5D;color:#fff;box-shadow:0 3px 8px rgba(63,109,93,.2)}.who5-options input:focus-visible+span{outline:2px solid #8AB99B;outline-offset:2px}
    [data-legacy-priority]{display:none!important}
    @media(max-width:560px){.who5-card{padding:16px}.who5-question{padding:12px}.who5-options{gap:4px}.who5-options label span{height:30px;font-size:11px}}
</style>

<div class="konseling-page">
    <div class="konseling-shell">

        {{-- ==================== RAIL KIRI ==================== --}}
        <aside class="konseling-rail">
            <div class="konseling-header">
                <span class="eyebrow">Formulir pengajuan</span>
                <h1>Ajukan konseling</h1>
                <p>Empat langkah singkat. Data Anda hanya dibaca oleh pihak LP3 yang berwenang: admin untuk verifikasi, serta konselor yang menangani.</p>
            </div>

            <nav class="konseling-stepper" aria-label="Langkah pengajuan konseling">
                @foreach($steps as $index => $label)
                    <div class="konseling-step {{ $index === 0 ? 'is-active' : '' }}" data-step="{{ $index + 1 }}">
                        <div class="konseling-step-number">{{ $index + 1 }}</div>
                        <span class="konseling-step-label">{{ $label }}</span>
                    </div>
                @endforeach
            </nav>

            <div class="konseling-help">
                <strong>Butuh bantuan mengisi?</strong>
                <p>Hubungi kami pada jam layanan: {{ $jamLayanan }}</p>
                <a href="https://wa.me/{{ $waAdminLayanan }}" target="_blank" rel="noopener">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8.1 9.8a16 16 0 0 0 6 6l1.3-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.8 2.1Z"/></svg>
                    Hubungi admin layanan
                </a>
            </div>
        </aside>

        {{-- ==================== KOLOM FORM ==================== --}}
        <div class="konseling-main">

            <div class="konseling-progress" data-progress>
                <div class="konseling-progress-top">
                    <b data-progress-label>Data Konseli</b>
                    <span data-progress-count>Langkah 1 dari 4</span>
                </div>
                <div class="konseling-progress-track">
                    <div class="konseling-progress-bar" data-progress-bar style="width:25%"></div>
                </div>
            </div>

            <form class="konseling-card" data-konseling-form method="POST" action="{{ route('konseling.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- ==================== STEP 1 ==================== --}}
                <section class="konseling-panel is-active" data-step-panel="1">
                    <div class="konseling-panel-head">
                        <h2>Data konseli</h2>
                        <p>Pilih jenis konseli terlebih dahulu. Formulir identitas akan menyesuaikan pilihan Anda.</p>
                    </div>

                    <div class="konseling-field" data-type-wrap>
                        <label>Jenis konseli <span class="konseling-required">*</span></label>
                        <div class="konseli-types">
                            @foreach($konseliTypes as $type)
                                <label class="konseli-type" data-konseli-type>
                                    <input type="radio" name="jenis_konseli" value="{{ $type['value'] }}" @if($loop->first) required @endif>
                                    <span class="konseli-type-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $type['icon'] !!}</svg>
                                    </span>
                                    <span class="konseli-type-text">
                                        <strong>{{ $type['title'] }}</strong>
                                        <small>{{ $type['desc'] }}</small>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <div class="konseling-error">Pilih jenis konseli terlebih dahulu.</div>
                    </div>

                    {{-- Mahasiswa --}}
                    <div data-konseli-fields="mahasiswa" hidden>
                        <div class="konseling-section-title">Identitas mahasiswa</div>
                        <div class="konseling-grid">
                            @include('konseling.partials.fields-mahasiswa', ['alumni' => false])
                        </div>
                    </div>

                    {{-- Alumni --}}
                    <div data-konseli-fields="alumni" hidden>
                        <div class="konseling-section-title">Identitas alumni</div>
                        <div class="konseling-grid">
                            @include('konseling.partials.fields-mahasiswa', ['alumni' => true])
                        </div>
                    </div>

                    {{-- Tenaga Kependidikan / Dosen --}}
                    <div data-konseli-fields="tenaga_kependidikan" hidden>
                        <div class="konseling-section-title">Identitas tenaga kependidikan atau dosen</div>
                        <div class="konseling-grid">
                            <div class="konseling-field">
                                <label>Nama <span class="konseling-required">*</span></label>
                                <input name="tk_nama" type="text" placeholder="Nama lengkap" required>
                                <div class="konseling-error">Nama wajib diisi.</div>
                            </div>
                            <div class="konseling-field">
                                <label>Jenis kelamin <span class="konseling-required">*</span></label>
                                <select name="tk_jenis_kelamin" required>
                                    <option value="">Pilih jenis kelamin</option>
                                    <option value="laki_laki">Laki-laki</option>
                                    <option value="perempuan">Perempuan</option>
                                </select>
                                <div class="konseling-error">Pilih jenis kelamin.</div>
                            </div>
                            <div class="konseling-field">
                                <label>Tempat lahir <span class="konseling-required">*</span></label>
                                <input name="tk_tempat_lahir" type="text" placeholder="Kota tempat lahir" required>
                                <div class="konseling-error">Tempat lahir wajib diisi.</div>
                            </div>
                            <div class="konseling-field">
                                <label>Tanggal lahir <span class="konseling-required">*</span></label>
                                <input name="tk_tanggal_lahir" type="date" max="{{ now()->toDateString() }}" required>
                                <div class="konseling-error">Tanggal lahir wajib diisi dan tidak boleh melebihi hari ini.</div>
                            </div>
                            <div class="konseling-field full">
                                <label>Unit kerja <span class="konseling-required">*</span></label>
                                <select id="tkUnitKerjaPilihan" aria-label="Pilih fakultas atau unit kerja" required>
                                    <option value="">Pilih fakultas/unit kerja</option>
                                    @foreach (array_keys($katalog ?? []) as $namaFakultas)
                                        <option value="{{ $namaFakultas }}">{{ $namaFakultas }}</option>
                                    @endforeach
                                    <option value="__lainnya">Lainnya (isi manual)</option>
                                </select>
                                <input name="tk_unit_kerja" id="tkUnitKerja" type="text" placeholder="Tulis unit kerja lain" required hidden>
                                <div class="konseling-error">Unit kerja wajib diisi.</div>
                            </div>
                            <div class="konseling-field full">
                                <label>Alamat <span class="konseling-required">*</span></label>
                                <textarea name="tk_alamat" placeholder="Alamat lengkap" required></textarea>
                                <div class="konseling-error">Alamat wajib diisi.</div>
                            </div>
                            <div class="konseling-field">
                                <label>Nomor telepon <span class="konseling-required">*</span></label>
                                <input name="tk_telepon" type="tel" placeholder="08xxxxxxxxxx" required>
                                <div class="konseling-error">Nomor telepon wajib diisi.</div>
                            </div>
                            <div class="konseling-field">
                                <label>Email <span class="konseling-required">*</span></label>
                                <input name="tk_email" type="email" placeholder="nama@email.com" required>
                                <div class="konseling-error">Masukkan alamat email yang valid.</div>
                            </div>
                        </div>

                        <div class="konseling-section-title">Kontak darurat I</div>
                        <div class="konseling-grid">
                            <div class="konseling-field">
                                <label>Nama <span class="konseling-required">*</span></label>
                                <input name="tk_darurat_1_nama" type="text" placeholder="Nama kontak darurat" required>
                                <div class="konseling-error">Nama kontak wajib diisi.</div>
                            </div>
                            <div class="konseling-field">
                                <label>Hubungan <span class="konseling-required">*</span></label>
                                <input name="tk_darurat_1_hubungan" type="text" placeholder="Contoh: Suami, Istri, Kakak" required>
                                <div class="konseling-error">Hubungan wajib diisi.</div>
                            </div>
                            <div class="konseling-field">
                                <label>Nomor <span class="konseling-required">*</span></label>
                                <input name="tk_darurat_1_nomor" type="tel" placeholder="Nomor telepon" required>
                                <div class="konseling-error">Nomor kontak wajib diisi.</div>
                            </div>
                        </div>

                        <div class="konseling-section-title">Kontak darurat II <small>opsional</small></div>
                        <div class="konseling-grid">
                            <div class="konseling-field">
                                <label>Nama</label>
                                <input name="tk_darurat_2_nama" type="text" placeholder="Nama kontak darurat">
                            </div>
                            <div class="konseling-field">
                                <label>Hubungan</label>
                                <input name="tk_darurat_2_hubungan" type="text" placeholder="Contoh: Suami, Istri, Kakak">
                            </div>
                            <div class="konseling-field">
                                <label>Nomor</label>
                                <input name="tk_darurat_2_nomor" type="tel" placeholder="Nomor telepon">
                            </div>
                        </div>
                    </div>

                    {{-- Wali --}}
                    <div data-konseli-fields="wali_mahasiswa" hidden>
                        <div class="konseling-section-title">Data wali mahasiswa</div>
                        <div class="konseling-grid">
                            <div class="konseling-field">
                                <label>Nama wali <span class="konseling-required">*</span></label>
                                <input name="wali_nama" type="text" placeholder="Nama lengkap wali" required>
                                <div class="konseling-error">Nama wali wajib diisi.</div>
                            </div>
                            <div class="konseling-field">
                                <label>Jenis kelamin wali <span class="konseling-required">*</span></label>
                                <select name="wali_jenis_kelamin" required>
                                    <option value="">Pilih jenis kelamin</option>
                                    <option value="laki_laki">Laki-laki</option>
                                    <option value="perempuan">Perempuan</option>
                                </select>
                                <div class="konseling-error">Pilih jenis kelamin wali.</div>
                            </div>
                            <div class="konseling-field">
                                <label>Hubungan dengan mahasiswa <span class="konseling-required">*</span></label>
                                <input name="wali_hubungan" type="text" placeholder="Ayah, ibu, atau wali" required>
                                <div class="konseling-error">Hubungan wajib diisi.</div>
                            </div>
                            <div class="konseling-field full">
                                <label>Nama mahasiswa yang diwakili <span class="konseling-required">*</span></label>
                                <input name="wali_mahasiswa_nama" type="text" placeholder="Nama mahasiswa" required>
                                <div class="konseling-error">Nama mahasiswa wajib diisi.</div>
                            </div>
                            <div class="konseling-field">
                                <label>Jenis kelamin mahasiswa <span class="konseling-required">*</span></label>
                                <select name="wali_mahasiswa_jenis_kelamin" required>
                                    <option value="">Pilih jenis kelamin</option>
                                    <option value="laki_laki">Laki-laki</option>
                                    <option value="perempuan">Perempuan</option>
                                </select>
                                <div class="konseling-error">Pilih jenis kelamin mahasiswa.</div>
                            </div>
                            <div class="konseling-field">
                                <label>NIM mahasiswa yang diwakili <span class="konseling-required">*</span></label>
                                <input name="wali_mahasiswa_nim" type="text" placeholder="NIM mahasiswa" required>
                                <div class="konseling-error">NIM wajib diisi.</div>
                            </div>
                            <div class="konseling-field">
                                <label>Fakultas mahasiswa <span class="konseling-required">*</span></label>
                                <select name="wali_mahasiswa_fakultas" id="waliMahasiswaFakultas" required>
                                    <option value="">Pilih fakultas</option>
                                    @foreach (($katalog ?? []) as $namaFakultas => $jenjangProdi)
                                        <option value="{{ $namaFakultas }}">{{ $namaFakultas }}</option>
                                    @endforeach
                                </select>
                                <div class="konseling-error">Fakultas mahasiswa wajib dipilih.</div>
                            </div>
                            <div class="konseling-field">
                                <label>Program studi mahasiswa <span class="konseling-required">*</span></label>
                                <select name="wali_mahasiswa_prodi" id="waliMahasiswaProdi" required disabled>
                                    <option value="">Pilih fakultas terlebih dahulu</option>
                                </select>
                                <div class="konseling-error">Program studi mahasiswa wajib dipilih.</div>
                            </div>
                            <div class="konseling-field full">
                                <label>Yang akan melakukan konseling <span class="konseling-required">*</span></label>
                                <div class="konseling-options">
                                    <label class="konseling-option-card"><input class="konseling-control" type="radio" name="wali_subjek_konseling" value="wali" required><span><strong>Wali mahasiswa</strong><span>Saya sendiri yang membutuhkan konseling.</span></span></label>
                                    <label class="konseling-option-card"><input class="konseling-control" type="radio" name="wali_subjek_konseling" value="mahasiswa" required><span><strong>Mahasiswa</strong><span>Mahasiswa yang diwakili yang membutuhkan konseling.</span></span></label>
                                </div>
                                <div class="konseling-callout">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></svg>
                                    <div>Jika yang membutuhkan konseling adalah <strong>wali mahasiswa</strong>, topik harus berkaitan dengan permasalahan mahasiswa yang diwakili dan berhubungan dengan perkuliahannya.</div>
                                </div>
                                <div class="konseling-error">Pilih siapa yang akan melakukan konseling.</div>
                            </div>
                            <div class="konseling-field">
                                <label>Nomor telepon wali <span class="konseling-required">*</span></label>
                                <input name="wali_telepon" type="tel" placeholder="08xxxxxxxxxx" required>
                                <div class="konseling-error">Nomor telepon wajib diisi.</div>
                            </div>
                            <div class="konseling-field" data-wali-student-phone hidden>
                                <label>Nomor WhatsApp mahasiswa <span class="konseling-required">*</span></label>
                                <input name="wali_mahasiswa_telepon" type="tel" placeholder="08xxxxxxxxxx">
                                <span class="konseling-hint">Diisi karena mahasiswa yang akan mengikuti konseling.</span>
                                <div class="konseling-error">Nomor WhatsApp mahasiswa wajib diisi.</div>
                            </div>
                            <div class="konseling-field">
                                <label>Email wali <span class="konseling-required">*</span></label>
                                <input name="wali_email" type="email" placeholder="nama@email.com" required>
                                <div class="konseling-error">Masukkan alamat email yang valid.</div>
                            </div>
                            <div class="konseling-field" data-wali-student-email hidden>
                                <label>Email mahasiswa <span class="konseling-required">*</span></label>
                                <input name="wali_mahasiswa_email" type="email" placeholder="username@student.unnes.ac.id" data-email-domain="@students.unnes.ac.id">
                                <span class="konseling-hint">Tautan persetujuan dan pengisian WHO-5 dikirim ke email mahasiswa.</span>
                                <div class="konseling-error">Email mahasiswa wajib diisi.</div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- ==================== STEP 2 : KATEGORI & PERMASALAHAN ==================== --}}
                <section class="konseling-panel" data-step-panel="2">
                    <div class="konseling-panel-head">
                        <h2>Kategori dan permasalahan</h2>
                        <p>Tentukan pelaksanaan sesi, lalu pilih lingkup hambatan dan jelaskan kondisi yang sedang Anda hadapi.</p>
                    </div>

                    <div class="konseling-section-title">Pelaksanaan sesi</div>

                    <div class="konseling-field">
                        <label>Metode <span class="konseling-required">*</span></label>
                        <div class="konseling-options">
                            @foreach($metodeLayanan as $metode)
                                <label class="konseling-option-card">
                                    <input class="konseling-control" type="radio" name="metode_layanan" value="{{ $metode['value'] }}" @if($loop->first) required @endif>
                                    <span>
                                        <strong>{{ $metode['title'] }}</strong>
                                        <span>{{ $metode['desc'] }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <div class="konseling-error">Pilih metode pelaksanaan.</div>
                    </div>

                    <div class="konseling-grid" style="margin-top:22px;">
                        <div class="konseling-field">
                            <label>Preferensi hari <span class="konseling-required">*</span></label>
                            <select name="preferensi_hari" required>
                                <option value="">Pilih hari</option>
                                <option value="senin">Senin</option>
                                <option value="selasa">Selasa</option>
                                <option value="rabu">Rabu</option>
                                <option value="kamis">Kamis</option>
                                <option value="jumat">Jumat</option>
                            </select>
                            <div class="konseling-error">Pilih preferensi hari.</div>
                        </div>
                        <div class="konseling-field">
                            <label>Preferensi waktu <span class="konseling-required">*</span></label>
                            <select name="preferensi_waktu" required>
                                <option value="">Pilih rentang waktu</option>
                                <option value="pagi">Pagi, 08.00–11.00</option>
                                <option value="siang">Siang, 11.00–14.00</option>
                                <option value="sore">Sore, 14.00–16.00</option>
                            </select>
                            <div class="konseling-error">Pilih preferensi waktu.</div>
                        </div>
                    </div>

                    <div class="konseling-note" style="margin-top:22px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                        <span>Preferensi jadwal bersifat usulan. Jadwal pasti dikonfirmasi admin setelah pengajuan diterima.</span>
                    </div>

                    <div class="konseling-section-title">Permasalahan</div>

                    <div class="konseling-field" data-obstacle-wrap>
                        <label>Lingkup hambatan <span class="konseling-required">*</span></label>
                        <span class="konseling-hint">Boleh pilih lebih dari satu.</span>
                        <div class="konseling-check-grid">
                            @foreach($lingkupHambatan as $obstacle)
                                <label class="konseling-check">
                                    <input class="konseling-control" type="checkbox" name="lingkup_hambatan[]" value="{{ $obstacle }}">
                                    <span>{{ $obstacle }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="konseling-error">Pilih minimal satu lingkup hambatan.</div>
                    </div>
                    <div class="konseling-field konseling-other-field" data-other-obstacle hidden>
                        <label for="lingkup_hambatan_lainnya">Tuliskan lingkup hambatan lainnya <span class="konseling-required">*</span></label>
                        <input class="konseling-control" id="lingkup_hambatan_lainnya" name="lingkup_hambatan_lainnya" type="text" maxlength="150" placeholder="Contoh: adaptasi lingkungan baru">
                        <span class="konseling-hint">Wajib diisi karena Anda memilih “Yang lain”. Maksimal 150 karakter.</span>
                    </div>

                    <div class="konseling-section-title">Screening kesejahteraan</div>

                    <div class="who5-card" data-who5-card>
                        <div class="who5-heading">
                            <div class="who5-icon">♥</div>
                            <div>
                                <h3>WHO-5 Well-Being Screening</h3>
                                <p>Jawab berdasarkan kondisi Anda selama <strong>dua minggu terakhir</strong>. Tidak ada jawaban benar atau salah.</p>
                            </div>
                        </div>
                        <div class="who5-scale" aria-label="Skala jawaban WHO-5">
                            <span>0 = Tidak pernah</span><span>5 = Selalu</span>
                        </div>
                        @php
                            $who5Questions = [
                                'Saya merasa ceria dan bersemangat.',
                                'Saya merasa tenang dan santai.',
                                'Saya merasa aktif dan penuh energi.',
                                'Saya bangun dengan perasaan segar dan beristirahat cukup.',
                                'Kehidupan sehari-hari saya dipenuhi hal-hal yang menarik bagi saya.',
                            ];
                        @endphp
                        <div class="who5-list">
                            @foreach($who5Questions as $index => $question)
                                <div class="who5-question">
                                    <p><span>{{ $index + 1 }}</span>{{ $question }}</p>
                                    <div class="who5-options" role="radiogroup" aria-label="{{ $question }}">
                                        @foreach(range(0, 5) as $value)
                                            <label><input type="radio" name="who5[{{ $index }}]" value="{{ $value }}" required><span>{{ $value }}</span></label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="konseling-note" style="margin-top:16px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                            <span>Jawaban ini digunakan sebagai screening dan rekomendasi prioritas pemeriksaan, bukan diagnosis psikologis.</span>
                        </div>
                    </div>

                    <div class="konseling-section-title">Detail kondisi</div>

                    <div class="konseling-grid">
                        <div class="konseling-field">
                            <label>Pilihan konselor <span class="konseling-required">*</span></label>
                            <select name="pilihan_konselor" required>
                                <option value="">Pilih konselor</option>
                                <option value="profesional">Profesional</option>
                                <option value="tutor_sebaya">Tutor sebaya</option>
                            </select>
                            <div class="konseling-error">Pilih pilihan konselor.</div>
                        </div>

                        <div class="konseling-field full" data-darurat-section>
                            <label>Apakah Anda dalam kondisi darurat? <span class="konseling-required">*</span></label>
                            <div class="konseling-options">
                                @foreach($kondisiDarurat as $opsi)
                                    <label class="konseling-option-card">
                                        <input class="konseling-control" type="radio" name="kondisi_darurat" value="{{ $opsi['value'] }}" @if($loop->first) required @endif>
                                        <span>
                                            <strong>{{ $opsi['title'] }}</strong>
                                            <span>{{ $opsi['desc'] }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            <div class="konseling-error">Pilih salah satu: ya atau tidak.</div>
                            <label class="konseling-check konseling-darurat-confirm" data-darurat-confirm-wrap hidden>
                                <input class="konseling-control" type="checkbox" name="konfirmasi_darurat" value="1" data-darurat-confirm>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 3 20 6v5c0 5-3.4 8.5-8 10-4.6-1.5-8-5-8-10V6l8-3Z"/>
                                    <path d="m8.5 12 2.3 2.3 4.8-5"/>
                                </svg>
                                <span class="konseling-emergency-copy">
                                    <strong>Konfirmasi kondisi darurat</strong>
                                    <span>Saya memastikan kondisi ini benar-benar membutuhkan penanganan segera dan bukan sekadar preferensi jadwal.</span>
                                </span>
                            </label>
                            <div class="konseling-callout is-danger" data-darurat-alert role="alert" hidden>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4M12 17h.01"/></svg>
                                <div><strong>Jika Anda benar-benar dalam kondisi darurat,</strong> segera hubungi Admin LP3 melalui <a href="https://wa.me/{{ $waAdminLayanan }}?text={{ rawurlencode('Halo Admin SILAKON, saya membutuhkan penanganan konseling segera.') }}" target="_blank" rel="noopener">WhatsApp Admin</a> atau layanan darurat tambahan <a href="https://wa.me/{{ $waDaruratLayanan }}" target="_blank" rel="noopener">{{ $waDaruratLayanan }}</a>. Pengajuan tetap masuk ke antrean, tetapi akan ditandai untuk pemeriksaan segera.</div>
                            </div>
                        </div>

                        <div class="konseling-field full">
                            <label>Uraian permasalahan <span class="konseling-required">*</span></label>
                            <div class="konseling-note is-secret">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                <span>Uraian ini bersifat <strong>RAHASIA</strong> dan hanya dapat dibaca oleh admin LP3 (untuk verifikasi pengajuan), konselor yang menangani, dan Koordinator LP3. Isinya tidak dibagikan kepada pihak lain.</span>
                            </div>
                            <span class="konseling-hint">Ceritakan sejak kapan berlangsung dan apa yang sudah Anda coba.</span>
                            <textarea name="deskripsi_masalah" placeholder="Tulis sesingkat atau selengkap yang Anda mau..." required></textarea>
                            <div class="konseling-error">Uraian permasalahan wajib diisi.</div>
                        </div>
                    </div>
                </section>

                {{-- ==================== STEP 3 : PERSETUJUAN ==================== --}}
                <section class="konseling-panel" data-step-panel="3">
                    <div class="konseling-panel-head">
                        <h2>Persetujuan</h2>
                        <p>Periksa kembali ringkasan berikut. Setelah menyetujui, Anda akan diminta memverifikasi email.</p>
                    </div>

                    <div class="konseling-summary" data-summary>
                        <div class="konseling-summary-head">Ringkasan pengajuan</div>
                        <dl class="konseling-summary-row">
                            <dt>Jenis konseli</dt>
                            <dd data-summary-value="jenis_konseli"></dd>
                        </dl>
                        <dl class="konseling-summary-row">
                            <dt>Metode dan jadwal</dt>
                            <dd data-summary-value="jadwal"></dd>
                        </dl>
                        <dl class="konseling-summary-row">
                            <dt>Lingkup hambatan</dt>
                            <dd data-summary-value="lingkup_hambatan"></dd>
                        </dl>
                        <dl class="konseling-summary-row">
                            <dt>Pilihan konselor</dt>
                            <dd data-summary-value="pilihan_konselor"></dd>
                        </dl>
                    </div>

                    <div class="konseling-terms" data-terms-box>
                        <div class="konseling-terms-head">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3.5h9l3 3V20a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4.5a1 1 0 0 1 1-1Z"/><path d="M14 3.5V7h4M8 11h8M8 14.5h8M8 18h5"/></svg>
                            <span>Persetujuan dan Pernyataan Privasi (Informed Consent)</span>
                        </div>
                        <div class="konseling-terms-body" data-terms-scroll tabindex="0" aria-label="Persetujuan dan Pernyataan Privasi (Informed Consent)">
                            <p>Dengan mengajukan layanan konseling SILAKON, Anda memahami dan menyetujui ketentuan berikut:</p>
                            <ol>
                                <li>Data identitas, kontak, pengajuan, hasil WHO-5, dan dokumen pendukung disimpan secara aman di dalam sistem untuk kebutuhan layanan.</li>
                                <li>Data pengajuan digunakan oleh Admin untuk pemeriksaan dan administrasi, serta oleh Koordinator untuk pemantauan status, statistik, dan pengelolaan layanan.</li>
                                <li>Uraian permasalahan dan catatan konseling hanya dapat diakses oleh konselor yang menangani sesi dan pihak layanan yang memang diberi kewenangan; data tersebut tidak ditampilkan kepada publik.</li>
                                <li>Data dapat digunakan untuk penjadwalan, komunikasi layanan, pengingat, survei setelah sesi, dan pembuatan laporan agregat tanpa membuka identitas secara tidak perlu.</li>
                                <li>Anda wajib mengisi data dengan benar. Pengajuan dapat ditolak atau ditindaklanjuti apabila data tidak valid atau digunakan tidak sesuai tujuan layanan.</li>
                                <li>Jika membutuhkan pertolongan segera, gunakan kontak darurat yang tersedia dan jangan hanya menunggu proses pengajuan daring.</li>
                            </ol>
                        </div>
                        <div class="konseling-terms-hint" data-terms-hint>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                            <span>Gulir sampai bagian akhir untuk mengaktifkan persetujuan.</span>
                        </div>
                    </div>

                    <div class="konseling-consent konseling-field is-disabled" data-consent-wrap>
                        <input id="consent" class="konseling-control" type="checkbox" name="consent" required disabled>
                        <label for="consent">
                            Saya telah membaca dan menyetujui Informed Consent serta menyatakan data yang saya isi benar. <span class="konseling-required">*</span>
                            <span class="konseling-error">Persetujuan wajib dicentang.</span>
                        </label>
                    </div>

                </section>

                {{-- ==================== STEP 4 : VERIFIKASI EMAIL ==================== --}}
                <section class="konseling-panel" data-step-panel="4" data-verify-panel>
                    <div class="konseling-panel-head">
                        <h2>Verifikasi email</h2>
                        <p data-verify-description>Masukkan kode verifikasi yang kami kirim ke email Anda untuk menyelesaikan pengajuan.</p>
                    </div>

                    <div class="konseling-note">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
                        <span>Verifikasi dikirim ke <strong data-verify-email>email Anda</strong>. Jika belum masuk, periksa folder spam.</span>
                    </div>

                    <div class="konseling-field" style="margin-top:22px;" data-otp-only>
                        <label for="verification_code">Kode verifikasi <span class="konseling-required">*</span></label>
                        <span class="konseling-hint">Masukkan 6 digit kode dari email.</span>
                        <input id="verification_code" name="verification_code" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9]{6}" placeholder="000000" data-code-input required>
                        <div class="konseling-error">Masukkan 6 digit kode verifikasi.</div>
                    </div>

                    <div class="konseling-resend" data-otp-only>
                        <button type="button" class="btn btn-outline" data-resend-code>Kirim ulang kode</button>
                        <span data-code-status role="status" aria-live="polite"></span>
                    </div>

                    <div class="konseling-success" data-success>
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                        <span>Formulir lolos validasi. Pengajuan siap dikirim.</span>
                    </div>
                </section>

                <div class="konseling-actions">
                    <button type="button" class="btn btn-outline" data-prev>Kembali</button>
                    <button type="button" class="btn btn-primary" data-next>Selanjutnya</button>
                    <button type="submit" class="btn btn-primary" data-submit hidden>Kirim pengajuan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Pelengkap tampilan: progress bar, ringkasan, peringatan darurat, dan pengiriman kode verifikasi email.
     Tidak mengubah logika navigasi/validasi form yang sudah ada. --}}
<script>
(function () {
    const form = document.querySelector('[data-konseling-form]');
    if (!form) return;

    // Endpoint pengiriman kode verifikasi (POST, body JSON: { email }). Sesuaikan dengan route Anda.
    const SEND_CODE_URL = @json(url('/konseling/kirim-kode'));
    const CSRF_TOKEN    = @json(csrf_token());
    const RESEND_SECONDS = 60;

    const steps    = Array.from(document.querySelectorAll('.konseling-step'));
    const panels   = Array.from(form.querySelectorAll('[data-step-panel]'));
    const bar      = document.querySelector('[data-progress-bar]');
    const label    = document.querySelector('[data-progress-label]');
    const counter  = document.querySelector('[data-progress-count]');
    const total    = panels.length;

    const summaryIndex = panels.findIndex((p) => p.querySelector('[data-summary]'));
    const verifyIndex  = panels.findIndex((p) => p.hasAttribute('data-verify-panel'));

    const daruratAlert = form.querySelector('[data-darurat-alert]');
    const verifyEmail  = form.querySelector('[data-verify-email]');
    const codeStatus   = form.querySelector('[data-code-status]');
    const resendBtn    = form.querySelector('[data-resend-code]');
    const codeInput    = form.querySelector('[data-code-input]');
    const termsScroll  = form.querySelector('[data-terms-scroll]');
    const consentInput = form.querySelector('#consent');
    const consentWrap  = form.querySelector('[data-consent-wrap]');
    const termsHint    = form.querySelector('[data-terms-hint]');
    const DRAFT_KEY = 'silakon.pengajuan.draft.v1';
    let draftTimer = null;

    function enableConsentAfterReading() {
        if (!termsScroll || !consentInput) return;
        const reachedEnd = termsScroll.scrollTop + termsScroll.clientHeight >= termsScroll.scrollHeight - 8;
        if (!reachedEnd) return;
        consentInput.disabled = false;
        if (consentWrap) consentWrap.classList.remove('is-disabled');
        if (termsHint) {
            termsHint.classList.add('is-read');
            termsHint.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg><span>S&K sudah dibaca. Anda dapat memberikan persetujuan.</span>';
        }
    }
    termsScroll?.addEventListener('scroll', enableConsentAfterReading, { passive: true });
    termsScroll?.addEventListener('keydown', (event) => {
        if (event.key === 'End') window.setTimeout(enableConsentAfterReading, 0);
    });
    enableConsentAfterReading();

    // Simpan isian formulir di browser agar refresh atau salah membuka langkah
    // tidak menghapus seluruh data. File upload dan OTP sengaja tidak disimpan.
    function saveDraft() {
        clearTimeout(draftTimer);
        draftTimer = setTimeout(() => {
            const data = {};
            Array.from(form.elements).forEach((el) => {
                if (!el.name || el.type === 'file' || el.name === 'verification_code' || el.disabled) return;
                if (el.type === 'checkbox') {
                    if (!Array.isArray(data[el.name])) data[el.name] = [];
                    if (el.checked) data[el.name].push(el.value || true);
                } else if (el.type === 'radio') {
                    if (el.checked) data[el.name] = el.value;
                } else {
                    data[el.name] = el.value;
                }
            });
            try { localStorage.setItem(DRAFT_KEY, JSON.stringify(data)); } catch (e) {}
        }, 250);
    }

    function restoreDraft() {
        let data;
        try { data = JSON.parse(localStorage.getItem(DRAFT_KEY) || 'null'); } catch (e) { data = null; }
        if (!data) return;
        Object.entries(data).forEach(([name, value]) => {
            const fields = Array.from(form.querySelectorAll('[name="' + CSS.escape(name) + '"]'));
            if (!fields.length) return;
            fields.forEach((el) => {
                if (el.type === 'checkbox') el.checked = Array.isArray(value) && value.includes(el.value);
                else if (el.type === 'radio') el.checked = el.value === value;
                else if (typeof value === 'string') el.value = value;
            });
        });
        form.dispatchEvent(new Event('change', { bubbles: true }));
    }

    const emailFieldByType = {
        mahasiswa: 'mahasiswa_email',
        alumni: 'alumni_email',
        tenaga_kependidikan: 'tk_email',
        wali_mahasiswa: 'wali_email',
    };

    const labelText = (name) => {
        const input = form.querySelector('[name="' + name + '"]:checked');
        if (!input) return '';
        const strong = input.closest('label')?.querySelector('strong');
        return strong ? strong.textContent.trim() : input.value;
    };

    const selectText = (name) => {
        const el = form.querySelector('[name="' + name + '"]');
        return el && el.value ? el.options[el.selectedIndex].text : '';
    };

    function fillSummary() {
        const checked = Array.from(form.querySelectorAll('[name="lingkup_hambatan[]"]:checked'))
            .map((el) => el.value);

        const jadwal = [selectText('preferensi_hari'), selectText('preferensi_waktu')]
            .filter(Boolean).join(', ');
        const metode = labelText('metode_layanan');

        const values = {
            jenis_konseli: labelText('jenis_konseli'),
            jadwal: [metode, jadwal].filter(Boolean).join(' • '),
            lingkup_hambatan: checked.join(', '),
            pilihan_konselor: selectText('pilihan_konselor'),
        };

        Object.entries(values).forEach(([key, value]) => {
            const cell = form.querySelector('[data-summary-value="' + key + '"]');
            if (!cell) return;
            cell.textContent = value;
            cell.classList.toggle('is-empty', !value);
        });
    }

    /* ---------- Verifikasi email ---------- */
    let lastAttemptEmail = '';
    let sending = false;
    let cooldownTimer = null;

    function konseliEmail() {
        const type = form.querySelector('[name="jenis_konseli"]:checked');
        const waliSubject = form.querySelector('[name="wali_subjek_konseling"]:checked')?.value;
        const fieldName = type?.value === 'wali_mahasiswa' && waliSubject === 'mahasiswa'
            ? 'wali_mahasiswa_email'
            : (type ? emailFieldByType[type.value] : null);
        const field = fieldName ? form.querySelector('[name="' + fieldName + '"]') : null;
        if (!field) return '';

        let email = field.value.trim().toLowerCase();
        // Email mahasiswa diisi sebagai username, lalu domain kampus ditambahkan
        // sebelum OTP dikirim dan sebelum ditampilkan kepada konseli.
        const domain = field.dataset.emailDomain || '';
        if (domain && email && !email.includes('@')) {
            email += domain;
        }
        return email;
    }

    function setStatus(text, state) {
        if (!codeStatus) return;
        codeStatus.textContent = text;
        codeStatus.dataset.state = state || '';
    }

    function startCooldown() {
        clearInterval(cooldownTimer);
        let left = RESEND_SECONDS;
        const tick = () => {
            if (left <= 0) {
                clearInterval(cooldownTimer);
                resendBtn.disabled = false;
                resendBtn.textContent = 'Kirim ulang kode';
                return;
            }
            resendBtn.disabled = true;
            resendBtn.textContent = 'Kirim ulang dalam ' + left + ' detik';
            left--;
        };
        tick();
        cooldownTimer = setInterval(tick, 1000);
    }

    async function sendCode() {
        const email = konseliEmail();
        if (!email || sending) return;

        sending = true;
        lastAttemptEmail = email;
        if (verifyEmail) verifyEmail.textContent = email;
        resendBtn.disabled = true;
        setStatus('Mengirim kode...');

        try {
            const res = await fetch(SEND_CODE_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ email }),
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            setStatus('Kode terkirim. Cek email Anda.', 'ok');
            startCooldown();
        } catch (err) {
            setStatus('Kode gagal dikirim. Coba kirim ulang.', 'error');
            resendBtn.disabled = false;
            resendBtn.textContent = 'Kirim ulang kode';
        } finally {
            sending = false;
        }
    }

    function onEnterVerify() {
        const email = konseliEmail();
        const isWaliStudent = form.querySelector('[name="wali_subjek_konseling"]:checked')?.value === 'mahasiswa';
        if (verifyEmail) verifyEmail.textContent = email || 'email Anda';
        if (isWaliStudent) {
            setStatus('Setelah dikirim, tautan persetujuan akan masuk ke email mahasiswa.', 'ok');
            return;
        }
        // Kirim otomatis saat pertama kali masuk, atau bila email berubah sejak percobaan terakhir.
        if (email && email !== lastAttemptEmail) sendCode();
    }

    if (resendBtn) resendBtn.addEventListener('click', sendCode);
    if (codeInput) {
        codeInput.addEventListener('input', () => {
            codeInput.value = codeInput.value.replace(/\D/g, '').slice(0, 6);
        });
    }

    /* ---------- Stepper / progress ---------- */
    function sync() {
        const index = panels.findIndex((p) => p.classList.contains('is-active'));
        const current = index < 0 ? 0 : index;

        steps.forEach((step, i) => {
            step.classList.toggle('is-active', i === current);
            step.classList.toggle('is-complete', i < current);
        });

        if (bar) bar.style.width = ((current + 1) / total * 100) + '%';
        if (counter) counter.textContent = 'Langkah ' + (current + 1) + ' dari ' + total;
        if (label && steps[current]) {
            label.textContent = steps[current].querySelector('.konseling-step-label').textContent.trim();
        }
        // Tombol "Kirim pengajuan" hanya muncul di langkah terakhir.
        const submitBtn = form.querySelector('[data-submit]');
        if (submitBtn) submitBtn.hidden = current !== total - 1;
        if (current === summaryIndex) fillSummary();
        if (current === verifyIndex) onEnterVerify();
    }

    new MutationObserver(sync).observe(form, {
        subtree: true,
        attributes: true,
        attributeFilter: ['class'],
    });

    form.addEventListener('change', (e) => {
        saveDraft();
        if (e.target.matches('[data-upload]')) {
            const name = document.querySelector('[data-file-name]');
            if (name) name.textContent = e.target.files[0] ? e.target.files[0].name : 'Belum ada file dipilih';
        }
        if (e.target.matches('[name="kondisi_darurat"]') && daruratAlert) {
            daruratAlert.hidden = e.target.value !== 'ya';
            const confirmWrap = form.querySelector('[data-darurat-confirm-wrap]');
            const confirmInput = form.querySelector('[data-darurat-confirm]');
            const isEmergency = e.target.value === 'ya';
            if (confirmWrap) confirmWrap.hidden = !isEmergency;
            if (confirmInput) { confirmInput.required = isEmergency; if (!isEmergency) confirmInput.checked = false; }
        }
        if (e.target.matches('[name="lingkup_hambatan[]"]')) {
            const other = form.querySelector('[data-other-obstacle]');
            const input = form.querySelector('[name="lingkup_hambatan_lainnya"]');
            const checked = form.querySelector('[name="lingkup_hambatan[]"][value="Yang lain"]')?.checked;
            if (other) other.hidden = !checked;
            if (input) input.required = !!checked;
        }
        if (e.target.matches('[name="wali_subjek_konseling"]')) {
            const wrap = form.querySelector('[data-wali-student-phone]');
            const input = wrap?.querySelector('[name="wali_mahasiswa_telepon"]');
            const emailWrap = form.querySelector('[data-wali-student-email]');
            const emailInput = emailWrap?.querySelector('[name="wali_mahasiswa_email"]');
            const whoInputs = form.querySelectorAll('[name^="who5["]');
            const otpParts = form.querySelectorAll('[data-otp-only]');
            const otpInput = form.querySelector('[name="verification_code"]');
            const whoCard = form.querySelector('[data-who5-card]');
            const verifyDescription = form.querySelector('[data-verify-description]');
            const isStudent = e.target.value === 'mahasiswa';
            if (wrap) wrap.hidden = !isStudent;
            if (input) input.required = isStudent;
            if (emailWrap) emailWrap.hidden = !isStudent;
            if (emailInput) emailInput.required = isStudent;
            // WHO-5 untuk mahasiswa yang diwakili diisi sendiri melalui tautan
            // email, bukan oleh wali pada formulir awal.
            whoInputs.forEach((el) => { el.disabled = isStudent; el.required = !isStudent; });
            if (whoCard) whoCard.hidden = isStudent;
            if (verifyDescription) verifyDescription.textContent = isStudent
                ? 'Pengajuan akan diteruskan setelah mahasiswa menyetujui tautan dan mengisi WHO-5.'
                : 'Masukkan kode verifikasi yang kami kirim ke email Anda untuk menyelesaikan pengajuan.';
            otpParts.forEach((el) => { el.hidden = isStudent; });
            if (otpInput) { otpInput.disabled = isStudent; otpInput.required = !isStudent; }
        }
    });
    form.addEventListener('input', saveDraft);
    form.addEventListener('submit', () => {
        try { localStorage.removeItem(DRAFT_KEY); } catch (e) {}
    });

    /* ---------- Tooltip info: tap/klik untuk buka di layar sentuh ---------- */
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-tip-toggle]');
        document.querySelectorAll('.konseling-label-tip.is-open').forEach((el) => {
            if (!btn || el !== btn.closest('.konseling-label-tip')) el.classList.remove('is-open');
        });
        if (btn) {
            e.preventDefault();
            btn.closest('.konseling-label-tip').classList.toggle('is-open');
        }
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.konseling-label-tip.is-open').forEach((el) => el.classList.remove('is-open'));
        }
    });

    restoreDraft();
    sync();
})();

// Pilihan fakultas/prodi wali mengikuti data master yang sama dengan mahasiswa.
(function () {
    const fakultas = document.getElementById('waliMahasiswaFakultas');
    const prodi = document.getElementById('waliMahasiswaProdi');
    if (!fakultas || !prodi) return;
    const katalog = @json($katalog ?? []);
    fakultas.addEventListener('change', function () {
        prodi.innerHTML = '<option value="">Pilih program studi</option>';
        const levels = katalog[fakultas.value] || {};
        Object.keys(levels).forEach(function (jenjang) {
            const group = document.createElement('optgroup'); group.label = jenjang;
            (levels[jenjang] || []).forEach(function (nama) {
                const option = document.createElement('option'); option.value = nama; option.textContent = nama; group.appendChild(option);
            });
            prodi.appendChild(group);
        });
        prodi.disabled = !fakultas.value;
    });
})();

// Unit kerja tendik memakai data fakultas sebagai pilihan utama, dengan
// opsi "Lainnya" bila unit kerja tidak tercantum di master.
(function () {
    const pilihan = document.getElementById('tkUnitKerjaPilihan');
    const manual = document.getElementById('tkUnitKerja');
    if (!pilihan || !manual) return;
    function sinkronUnitKerja() {
        const lainnya = pilihan.value === '__lainnya';
        manual.hidden = !lainnya;
        manual.required = lainnya;
        pilihan.required = !lainnya;
        if (!lainnya) manual.value = pilihan.value;
        else if (manual.value === pilihan.value) manual.value = '';
    }
    pilihan.addEventListener('change', sinkronUnitKerja);
    sinkronUnitKerja();
})();
</script>
@endsection
