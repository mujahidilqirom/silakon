{{--
    SILAKON — Pengajuan Mendesak (Koordinator)
    Simpan sebagai: resources/views/koordinator/kasus.blade.php
    Route contoh:  Route::get('/koordinator/kasus', [AdminPendaftaranController::class, 'kasusMendesak'])->name('koordinator.kasus');

    Data ($kasus) dikirim dari AdminPendaftaranController@kasusMendesak (masih dummy).
    Tombol "Tandai Ditangani" membuka modal hasil penanganan; simpan-nya saat ini hanya mengubah tampilan di browser (belum ke server).
    Subjudul tidak di-override, jadi otomatis memakai tanggal hari ini dari navbar.blade.php.
--}}
@extends('layouts.koordinator')

@section('judul', 'Pengajuan Mendesak')

@section('content')

<style>
    .ksm {
        --ksm-primary: #7a9c7c;
        --ksm-primary-dark: #4d7a55;
        --ksm-ink: #1a2321;
        --ksm-muted: #7d8582;
        --ksm-amber: #f5a30b;
    }

    /* Header */
    .ksm-head { margin-bottom: 24px; }
    .ksm-head h1 {
        display: flex; align-items: center; gap: 10px;
        margin: 0; font-size: 22px; font-weight: 700; color: var(--ksm-ink);
    }
    .ksm-head h1 svg { width: 22px; height: 22px; color: var(--ksm-amber); flex: none; }
    .ksm { overflow-wrap: break-word; }
    .ksm-head p { margin: 6px 0 0; font-size: 14px; color: var(--ksm-muted); }

    /* Catatan */
    .ksm-note {
        margin-bottom: 24px; padding: 16px 20px;
        background: #fdf1c3; border: 1px solid #f3e1a0; border-radius: 12px;
        font-size: 14px; line-height: 1.6; color: #8a3f0b;
    }

    /* Daftar kasus */
    .ksm-list { display: flex; flex-direction: column; gap: 16px; container-type: inline-size; }

    .ksm-card {
        display: flex; align-items: center; justify-content: space-between; gap: 20px;
        padding: 24px 24px 24px 24px; background: #fff;
        border: 1.5px solid var(--ksm-amber); border-left-width: 4px; border-radius: 16px;
    }
    .ksm-card.is-done { border-color: var(--ksm-primary); }

    .ksm-main { flex: 1 1 auto; min-width: 0; }
    .ksm-title { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .ksm-title h2 { margin: 0; font-size: 16px; font-weight: 700; color: var(--ksm-ink); min-width: 0; overflow-wrap: anywhere; }
    .ksm-code { font-size: 13px; font-weight: 700; color: var(--ksm-primary-dark); letter-spacing: .01em; }

    .ksm-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 9px; border-radius: 999px; font-size: 11px; font-weight: 700;
    }
    .ksm-badge svg { width: 12px; height: 12px; }
    .ksm-badge-urgent { background: #fdefb9; border: 1px solid #f3d98a; color: #8a3f0b; letter-spacing: .03em; text-transform: uppercase; }
    .ksm-badge-done   { background: #d9f5e3; border: 1px solid #bfe9cf; color: #1f6b3f; font-size: 12px; padding: 4px 11px; }
    .ksm-card.is-done .ksm-badge-urgent,
    .ksm-card.is-urgent .ksm-badge-done { display: none; }

    .ksm-meta {
        display: flex; align-items: center; flex-wrap: wrap;
        margin: 12px 0 0; padding: 0; font-size: 14px; color: var(--ksm-muted);
    }
    .ksm-meta > span { display: inline-flex; align-items: center; gap: 6px; }
    .ksm-meta > span + span::before { content: "·"; margin: 0 12px; }
    .ksm-meta svg { width: 14px; height: 14px; }

    .ksm-actions { display: flex; align-items: center; gap: 8px; flex: none; }
    .ksm-btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 16px; border-radius: 10px; border: 1px solid transparent;
        font: inherit; font-size: 14px; font-weight: 600; text-decoration: none;
        cursor: pointer; transition: background .15s, border-color .15s;
    }
    .ksm-btn svg { width: 15px; height: 15px; }
    .ksm-btn:focus-visible { outline: 2px solid var(--ksm-primary-dark); outline-offset: 2px; }
    .ksm-btn-soft  { background: #eef5ee; border-color: #b8ceb9; color: var(--ksm-primary-dark); }
    .ksm-btn-soft:hover { background: #e2eee3; border-color: var(--ksm-primary); }
    .ksm-btn-solid { background: var(--ksm-primary); color: #fff; }
    .ksm-btn-solid:hover { background: #6b8d6e; }
    .ksm-card.is-done .ksm-btn-solid { display: none; }

    .ksm-empty {
        padding: 40px 20px; text-align: center; font-size: 14px; color: var(--ksm-muted);
        background: #fff; border: 1px dashed #d8ddd6; border-radius: 16px;
    }

    /* Modal "Tandai Sudah Ditangani" */
    .ksm-modal {
        width: min(440px, calc(100vw - 32px)); max-height: calc(100vh - 32px); max-height: calc(100dvh - 32px);
        padding: 0; border: 0; border-radius: 20px; background: #fff; color: var(--ksm-ink);
        box-shadow: 0 24px 64px rgba(0, 0, 0, .28); overflow: auto;
    }
    .ksm-modal::backdrop { background: rgba(38, 42, 40, .5); }
    .ksm-modal form { padding: 32px; }
    .ksm-modal-title { margin: 0 0 24px; font-size: 18px; font-weight: 700; }

    .ksm-fieldset { margin: 0 0 22px; padding: 0; border: 0; min-width: 0; }
    .ksm-label { display: block; margin-bottom: 12px; padding: 0; font-size: 14px; font-weight: 600; }
    .ksm-req { color: #dc2626; }

    .ksm-opt {
        display: flex; align-items: center; gap: 12px;
        margin-bottom: 8px; padding: 0 16px; height: 44px;
        background: #f7f8f8; border: 1px solid transparent; border-radius: 10px;
        font-size: 14px; cursor: pointer; transition: background .15s, border-color .15s;
    }
    .ksm-opt:hover { background: #f0f3f0; }
    .ksm-opt:has(input:checked) { background: #eaf5ea; border-color: #9ec7a3; }
    .ksm-opt:has(input:focus-visible) { outline: 2px solid var(--ksm-primary-dark); outline-offset: 2px; }
    .ksm-opt input {
        appearance: none; -webkit-appearance: none; flex: none;
        width: 16px; height: 16px; margin: 0; border-radius: 50%;
        background: #fff; border: 1.5px solid #6b7370; cursor: pointer;
    }
    .ksm-opt input:checked { border-color: var(--ksm-primary-dark); box-shadow: inset 0 0 0 3px #fff; background: var(--ksm-primary-dark); }

    .ksm-error { margin: 4px 0 0; font-size: 13px; color: #dc2626; }
    .ksm-error[hidden] { display: none; }

    .ksm-textarea {
        display: block; width: 100%; min-height: 80px; margin-top: 0; padding: 12px 14px;
        font: inherit; font-size: 14px; color: var(--ksm-ink); resize: vertical;
        background: #fff; border: 1px solid #dfe3de; border-radius: 10px;
    }
    .ksm-textarea::placeholder { color: #a3a9a6; }
    .ksm-textarea:focus-visible { outline: 2px solid var(--ksm-primary); outline-offset: 1px; }

    .ksm-modal-actions { display: flex; gap: 12px; margin-top: 24px; }
    .ksm-modal-actions .ksm-btn { flex: 1; justify-content: center; padding: 12px 16px; font-size: 15px; }
    .ksm-btn-outline { background: #fff; border-color: var(--ksm-primary); color: var(--ksm-primary-dark); }
    .ksm-btn-outline:hover { background: #eef5ee; }
    .ksm-visually-hidden {
        position: absolute; width: 1px; height: 1px; margin: -1px; padding: 0;
        overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; border: 0;
    }

    /* ============ RESPONSIVE ============ */

    /* Kartu mengikuti lebar area daftar (container query), jadi aman
       walau sidebar layout membuat area konten lebih sempit dari layar. */
    @container (max-width: 640px) {
        .ksm-card { flex-direction: column; align-items: stretch; gap: 16px; padding: 18px; }
        .ksm-title { gap: 6px 10px; }
        .ksm-title h2 { flex: 1 1 100%; }
        .ksm-meta { gap: 6px 16px; margin-top: 10px; font-size: 13.5px; }
        .ksm-meta > span + span::before { content: none; }
        .ksm-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .ksm-card.is-done .ksm-actions { grid-template-columns: 1fr; }
        .ksm-actions .ksm-btn { justify-content: center; min-height: 44px; padding: 10px 12px; }
    }

    /* Area sangat sempit (mis. 357 x 667): tombol ditumpuk, lebar penuh */
    @container (max-width: 400px) {
        .ksm-card { padding: 16px; border-radius: 14px; }
        .ksm-actions { grid-template-columns: 1fr; }
    }

    @media (max-width: 720px) {
        .ksm-head { margin-bottom: 18px; }
        .ksm-head h1 { font-size: 20px; gap: 8px; }
        .ksm-head h1 svg { width: 20px; height: 20px; }
        .ksm-head p { font-size: 13.5px; line-height: 1.5; }
        .ksm-note { margin-bottom: 18px; padding: 14px 16px; font-size: 13.5px; line-height: 1.55; }
        .ksm-list { gap: 12px; }
        .ksm-empty { padding: 32px 16px; }

        .ksm-modal { border-radius: 16px; }
        .ksm-modal form { padding: 24px 20px; }
        .ksm-modal-title { margin-bottom: 18px; font-size: 17px; }
        .ksm-fieldset { margin-bottom: 18px; }
        .ksm-opt { height: 48px; padding: 0 14px; }
        .ksm-textarea { font-size: 16px; } /* cegah auto-zoom di iOS */
        .ksm-modal-actions { margin-top: 20px; gap: 10px; }
        .ksm-modal-actions .ksm-btn { min-height: 46px; padding: 10px 12px; font-size: 14.5px; }
    }

    @media (max-width: 400px) {
        .ksm-head h1 { font-size: 19px; }
        .ksm-note { padding: 12px 14px; font-size: 13px; }
        .ksm-modal { width: calc(100vw - 24px); max-height: calc(100vh - 24px); max-height: calc(100dvh - 24px); }
        .ksm-modal form { padding: 20px 16px; }
        .ksm-modal-actions { gap: 8px; }
    }

    /* Layar pendek (landscape ponsel): modal lebih ringkas */
    @media (max-height: 520px) {
        .ksm-modal form { padding-top: 18px; padding-bottom: 18px; }
        .ksm-opt { height: 40px; margin-bottom: 6px; }
        .ksm-textarea { min-height: 60px; }
    }
</style>

<div class="ksm">

    {{-- ============ JUDUL ============ --}}
    <div class="ksm-head">
        <h1>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>
            </svg>
            Pengajuan Mendesak
        </h1>
        <p>Pengajuan dengan kondisi darurat yang memerlukan tindakan segera.</p>
    </div>

    {{-- ============ CATATAN ============ --}}
    <div class="ksm-note">
        <strong>Catatan:</strong> Pengajuan mendesak diprioritaskan tanpa menunggu antrean biasa. Hubungi konseli secepatnya melalui WhatsApp, kemudian tandai status penanganan. Catatan penanganan darurat terpisah dari catatan konseling dan hanya berisi hasil penanganan singkat.
    </div>

    {{-- ============ DAFTAR KASUS ============ --}}
    @if(count($kasus) > 0)
        <div class="ksm-list" id="ksm-list">
            @foreach($kasus as $k)
                <article class="ksm-card {{ $k['status'] === 'ditangani' ? 'is-done' : 'is-urgent' }}" data-tangani-url="{{ route('koordinator.kasus.tangani', $k['kode']) }}">
                    <div class="ksm-main">
                        <div class="ksm-title">
                            <h2>{{ $k['nama'] }}</h2>
                            <span class="ksm-code">{{ $k['kode'] }}</span>

                            <span class="ksm-badge ksm-badge-urgent">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>
                                </svg>
                                Mendesak
                            </span>
                            <span class="ksm-badge ksm-badge-done">Sudah Ditangani</span>
                        </div>

                        <p class="ksm-meta">
                            <span>{{ $k['peran'] }}</span>
                            <span>{{ $k['kategori'] }}</span>
                            <span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                {{ $k['waktu'] }}
                            </span>
                        </p>
                    </div>

                    <div class="ksm-actions">
                        <a href="https://wa.me/{{ $k['whatsapp'] }}" target="_blank" rel="noopener" class="ksm-btn ksm-btn-soft">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0 1 22 16.92Z"/>
                            </svg>
                            Buka WhatsApp
                        </a>
                        <button type="button" class="ksm-btn ksm-btn-solid" data-tandai>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                            Tandai Ditangani
                        </button>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div class="ksm-empty">Tidak ada pengajuan mendesak saat ini.</div>
    @endif

    {{-- ============ MODAL TANDAI SUDAH DITANGANI ============ --}}
    <dialog class="ksm-modal" id="ksm-modal" aria-labelledby="ksm-modal-title" aria-describedby="ksm-modal-target">
        <form id="ksm-form" novalidate>
            <h2 class="ksm-modal-title" id="ksm-modal-title">Tandai Sudah Ditangani</h2>
            <span class="ksm-visually-hidden" id="ksm-modal-target"></span>

            <fieldset class="ksm-fieldset">
                <legend class="ksm-label">Hasil penanganan <span class="ksm-req" aria-hidden="true">*</span></legend>

                <label class="ksm-opt"><input type="radio" name="hasil" value="dihubungi" required> Sudah dihubungi</label>
                <label class="ksm-opt"><input type="radio" name="hasil" value="ditangani_konselor"> Ditangani konselor</label>
                <label class="ksm-opt"><input type="radio" name="hasil" value="dirujuk"> Dirujuk ke pihak lain</label>
                <label class="ksm-opt"><input type="radio" name="hasil" value="lainnya"> Lainnya</label>

                <p class="ksm-error" id="ksm-error" role="alert" hidden>Pilih hasil penanganan terlebih dahulu.</p>
            </fieldset>

            <div>
                <label class="ksm-label" for="ksm-catatan">Catatan singkat (opsional)</label>
                <textarea class="ksm-textarea" id="ksm-catatan" name="catatan" placeholder="Catatan penanganan darurat..."></textarea>
            </div>

            <div class="ksm-modal-actions">
                <button type="button" class="ksm-btn ksm-btn-outline" id="ksm-batal">Batal</button>
                <button type="submit" class="ksm-btn ksm-btn-solid">Simpan</button>
            </div>
        </form>
    </dialog>
</div>

<script>
(function () {
    const list   = document.getElementById('ksm-list');
    const modal  = document.getElementById('ksm-modal');
    if (!list || !modal) return;

    const form   = document.getElementById('ksm-form');
    const error  = document.getElementById('ksm-error');
    const target = document.getElementById('ksm-modal-target');
    let kartuAktif = null;
    let pemicu = null;

    // Klik "Tandai Ditangani" -> buka modal untuk kartu tersebut
    list.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-tandai]');
        if (!btn) return;

        pemicu = btn;
        kartuAktif = btn.closest('.ksm-card');
        target.textContent = 'Pengajuan ' + kartuAktif.querySelector('h2').textContent;
        modal.showModal();
    });

    // Batal, tombol Esc, atau klik di luar modal -> tutup
    document.getElementById('ksm-batal').addEventListener('click', () => modal.close());
    modal.addEventListener('click', e => { if (e.target === modal) modal.close(); });
    modal.addEventListener('close', function () {
        form.reset();
        error.hidden = true;
        if (pemicu && document.contains(pemicu) && pemicu.offsetParent !== null) pemicu.focus();
    });
    form.addEventListener('change', () => { error.hidden = true; });

    // Simpan -> validasi, lalu ubah kartu jadi "Sudah Ditangani" dan pindah ke bawah
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const hasil = form.querySelector('input[name="hasil"]:checked');
        if (!hasil) { error.hidden = false; return; }

        fetch(kartuAktif.dataset.tanganiUrl, {method:'POST', headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json','Content-Type':'application/json'}, body:JSON.stringify({hasil:hasil.value,catatan:form.elements.catatan.value.trim()})})
          .then(r=>{if(!r.ok) throw new Error(); return r;}).then(()=>{ kartuAktif.classList.remove('is-urgent'); kartuAktif.classList.add('is-done'); list.appendChild(kartuAktif); pemicu=kartuAktif.querySelector('a.ksm-btn'); modal.close(); })
          .catch(()=>alert('Gagal menyimpan penanganan. Silakan coba lagi.'));
    });
})();
</script>
@endsection