{{--
    SILAKON — Survei Konseli (Admin)
    Simpan sebagai: resources/views/admin/survei.blade.php
    Route: Route::get('/admin/survei', [AdminSurveiController::class, 'index'])->name('admin.survei');

    Data ($konseli, $totalSelesai) dari AdminSurveiController@index (dummy).
    Tiap baris punya data-state: "belum" (tombol Generate Survei), "dibuat" (tombol Kirim Link),
    "terisi" (survei sudah diisi, tanpa aksi). Tampilan pill & tombol diatur lewat CSS berdasarkan state itu.
--}}
@extends('layouts.admin')

@section('judul', 'Survei Konseli')

@section('content')

<style>
    .sa {
        --sa-primary: #7a9c7c;
        --sa-primary-dark: #4d7a55;
        --sa-ink: #1a2321;
        --sa-body: #4f5a56;
        --sa-muted: #7d8582;
        --sa-line: #e7eae6;
    }

    /* Header */
    .sa-head { margin-bottom: 24px; }
    .sa-head h1 { margin: 0; font-size: 22px; font-weight: 700; color: var(--sa-ink); }
    .sa-head p  { margin: 6px 0 0; font-size: 14px; color: var(--sa-muted); }

    /* Pencarian */
    .sa-search { margin-bottom: 20px; max-width: 420px; }
    .sa-input {
        width: 100%; height: 46px; padding: 0 16px; font: inherit; font-size: 14px; color: var(--sa-ink);
        background: #fff; border: 1px solid var(--sa-line); border-radius: 12px;
    }
    .sa-input::placeholder { color: #a3a9a6; }
    .sa-input:focus-visible { outline: 2px solid var(--sa-primary); outline-offset: 1px; }

    /* Tabel */
    .sa-card { background: #fff; border: 1px solid var(--sa-line); border-radius: 20px; overflow: hidden; }
    .sa-wrap { overflow-x: auto; }
    .sa-table { width: 100%; border-collapse: collapse; min-width: 900px; }
    .sa-table th {
        padding: 18px 16px; text-align: left; white-space: nowrap;
        font-size: 12px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;
        color: #4a524f; background: #f6f7f5;
    }
    .sa-table td { padding: 16px; border-top: 1px solid #eff1ee; font-size: 14px; color: var(--sa-body); vertical-align: middle; }
    .sa-table tr:first-child td { border-top: 0; }
    .sa-table tr[hidden] { display: none; }

    .sa-name  { font-weight: 700; color: var(--sa-ink); }
    .sa-sub   { display: block; margin-top: 2px; font-size: 12.5px; font-weight: 400; color: var(--sa-muted); word-break: break-all; }
    .sa-bold  { font-weight: 700; color: var(--sa-ink); }
    .sa-dash  { color: #b4bab7; }
    .sa-empty { padding: 40px 16px !important; text-align: center; color: var(--sa-muted) !important; }

    .sa-pill {
        display: inline-block; padding: 4px 12px; border-radius: 999px;
        font-size: 12px; font-weight: 600; line-height: 1.3; white-space: nowrap;
    }
    .sa-pill.jenis      { background: #eceeec; color: #4b5350; }
    .sa-pill.tone-hijau { background: #d9f5e3; color: #1f7a4a; }
    .sa-pill.tone-biru  { background: #dce8fc; color: #1d4ed8; }
    .sa-pill.tone-abu   { background: #eceeec; color: #4b5350; }

    /* Tampilan berdasarkan state baris */
    .sa-row .s-belum, .sa-row .s-dibuat, .sa-row .s-terisi,
    .sa-row .sa-btn-gen, .sa-row .sa-btn-kirim, .sa-row .sa-none { display: none; }

    .sa-row[data-state="belum"]  .s-belum,
    .sa-row[data-state="dibuat"] .s-dibuat,
    .sa-row[data-state="terisi"] .s-terisi { display: inline-block; }
    .sa-row[data-state="belum"]  .sa-btn-gen,
    .sa-row[data-state="dibuat"] .sa-btn-kirim { display: inline-flex; }
    .sa-row[data-state="terisi"] .sa-none { display: inline; }

    /* Tombol */
    .sa-btn {
        align-items: center; gap: 8px; white-space: nowrap; height: 38px; padding: 0 16px;
        border-radius: 999px; font: inherit; font-size: 13px; font-weight: 600; cursor: pointer;
    }
    .sa-btn svg { width: 15px; height: 15px; flex: none; }
    .sa-btn:focus-visible { outline: 2px solid var(--sa-primary-dark); outline-offset: 2px; }
    .sa-btn:disabled { opacity: .6; cursor: default; }
    .sa-btn-gen   { background: var(--sa-primary-dark); border: 1px solid var(--sa-primary-dark); color: #fff; }
    .sa-btn-gen:hover:not(:disabled) { background: #416a48; }
    .sa-btn-kirim { background: #fff; border: 1px solid var(--sa-primary); color: var(--sa-primary-dark); }
    .sa-btn-kirim:hover { background: #f2f7f2; }

    .sa-foot { margin-top: 24px; font-size: 14px; color: var(--sa-muted); }

    /* Tab */
    .sa-tabs { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
    .sa-tab {
        display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 18px;
        background: #eceeec; border: 1px solid transparent; border-radius: 999px;
        font: inherit; font-size: 14px; font-weight: 600; color: var(--sa-body); cursor: pointer;
    }
    .sa-tab.is-active { background: #fff; border-color: var(--sa-line); color: var(--sa-ink); box-shadow: 0 1px 2px rgba(20, 30, 25, .05); }
    .sa-tab:focus-visible { outline: 2px solid var(--sa-primary-dark); outline-offset: 2px; }
    .sa-badge {
        display: inline-grid; place-items: center; min-width: 18px; height: 18px; padding: 0 6px;
        background: #dfe9df; color: var(--sa-primary-dark); font-size: 11px; font-weight: 700; border-radius: 999px;
    }
    .sa-view[hidden] { display: none; }

    /* Teks kecil di tabel */
    .sa-table td.sa-small { font-size: 12.5px; }
    .sa-table td.sa-soft  { color: var(--sa-muted); }

    /* Hasil: ringkasan */
    .sa-sum { background: #fff; border: 1px solid var(--sa-line); border-radius: 20px; padding: 24px; margin-bottom: 16px; }
    .sa-sum-title { margin: 0 0 14px; font-size: 18px; font-weight: 700; color: var(--sa-ink); }
    .sa-sum-title small { font-size: 14px; font-weight: 500; color: var(--sa-muted); }
    .sa-track { height: 8px; background: #eceeec; border-radius: 999px; overflow: hidden; }
    .sa-track i { display: block; height: 100%; background: var(--sa-primary-dark); border-radius: 999px; }
    .sa-sum-cap { margin: 10px 0 0; font-size: 13px; color: var(--sa-muted); }

    /* Hasil: distribusi */
    .sa-dist { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px; margin-bottom: 24px; }
    .sa-dist-card { background: #fff; border: 1px solid var(--sa-line); border-radius: 20px; padding: 22px; }
    .sa-dist-card h3 { margin: 0 0 16px; font-size: 14.5px; line-height: 1.4; font-weight: 700; color: var(--sa-ink); }
    .sa-opt + .sa-opt { margin-top: 14px; }
    .sa-opt-top { display: flex; justify-content: space-between; gap: 12px; margin-bottom: 6px; font-size: 13px; color: var(--sa-body); }
    .sa-opt-top b { flex: none; color: var(--sa-ink); }

    .sa-sec-title { margin: 0 0 12px; font-size: 16px; font-weight: 700; color: var(--sa-ink); }
    .sa-btn-lihat { display: inline-flex; background: #fff; border: 1px solid var(--sa-primary); color: var(--sa-primary-dark); }
    .sa-btn-lihat:hover { background: #f2f7f2; }
    .sa-kosong { padding: 48px 16px; text-align: center; color: var(--sa-muted); background: #fff; border: 1px solid var(--sa-line); border-radius: 20px; }

    /* Detail jawaban */
    .sa-qa + .sa-qa { margin-top: 16px; }
    .sa-qa dt { font-size: 12.5px; color: var(--sa-muted); margin-bottom: 3px; }
    .sa-qa dd { margin: 0; font-size: 14px; color: var(--sa-ink); line-height: 1.5; }
    .sa-qa-list { margin: 0 0 22px; }

    /* Modal */
    .sa-overlay {
        position: fixed; inset: 0; background: rgba(20, 30, 25, .45);
        display: none; align-items: center; justify-content: center; padding: 20px; z-index: 50;
    }
    .sa-overlay.is-open { display: flex; }
    .sa-modal {
        width: 100%; max-width: 520px; max-height: calc(100vh - 40px); overflow-y: auto;
        background: #fff; border-radius: 20px; padding: 28px; box-shadow: 0 20px 50px rgba(20, 30, 25, .25);
    }
    .sa-modal-head { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 4px; }
    .sa-modal-head h2 { margin: 0; font-size: 18px; font-weight: 700; color: var(--sa-ink); }
    .sa-close {
        border: none; background: transparent; cursor: pointer; color: var(--sa-muted);
        width: 28px; height: 28px; display: grid; place-items: center; border-radius: 8px;
    }
    .sa-close:hover { background: #f2f3f1; color: var(--sa-ink); }
    .sa-close svg { width: 18px; height: 18px; }
    .sa-desc { margin: 0 0 18px; font-size: 14px; color: var(--sa-body); line-height: 1.5; }

    .sa-ok {
        display: flex; align-items: center; gap: 8px; margin-bottom: 16px; padding: 10px 14px;
        background: #e8f7ee; border: 1px solid #c6ecd5; color: #1f7a4a; border-radius: 12px; font-size: 13px; font-weight: 600;
    }
    .sa-ok svg { width: 16px; height: 16px; flex: none; }
    .sa-ok[hidden] { display: none; }

    .sa-label { display: block; font-size: 14px; font-weight: 600; color: var(--sa-ink); margin-bottom: 8px; }
    .sa-linkrow { display: flex; gap: 8px; margin-bottom: 20px; }
    .sa-linkrow .sa-input { height: 44px; border-radius: 10px; font-size: 13px; min-width: 0; }
    .sa-linkrow .sa-btn { height: 44px; border-radius: 10px; }

    .sa-preview {
        margin: 0 0 22px; padding: 14px 16px; background: #f6f7f5; border: 1px solid var(--sa-line); border-radius: 12px;
        font: inherit; font-size: 13px; line-height: 1.55; color: var(--sa-body); white-space: pre-wrap; word-break: break-word;
    }

    .sa-modal-foot { display: flex; justify-content: flex-end; gap: 12px; flex-wrap: wrap; }
    .sa-btn-tutup { background: #fff; border: 1px solid var(--sa-line); color: var(--sa-ink); height: 44px; border-radius: 10px; padding: 0 22px; }
    .sa-btn-tutup:hover { border-color: var(--sa-primary); }
    .sa-btn-wa {
        display: inline-flex; align-items: center; gap: 8px; height: 44px; padding: 0 22px; border-radius: 10px;
        background: var(--sa-primary-dark); border: 1px solid var(--sa-primary-dark); color: #fff;
        font: inherit; font-size: 14px; font-weight: 600; text-decoration: none;
    }
    .sa-btn-wa:hover { background: #416a48; }
    .sa-btn-wa svg { width: 17px; height: 17px; }
    .sa-btn-wa:focus-visible { outline: 2px solid var(--sa-primary-dark); outline-offset: 2px; }

    @media (max-width: 560px) {
        .sa-search { max-width: none; }
        .sa-linkrow { flex-direction: column; }
        .sa-modal-foot > * { flex: 1 1 100%; justify-content: center; }
    }
</style>

<div class="sa">

    {{-- ============ JUDUL ============ --}}
    <div class="sa-head">
        <h1>Survei Konseli</h1>
        <p>Kirim survei ke konseli yang sesinya sudah selesai dan lihat hasilnya</p>
    </div>

    {{-- ============ TAB ============ --}}
    <div class="sa-tabs" role="tablist">
        <button type="button" class="sa-tab is-active" id="sa-tab-kirim" role="tab" aria-selected="true">Kirim Survei</button>
        <button type="button" class="sa-tab" id="sa-tab-hasil" role="tab" aria-selected="false">
            Hasil Survei
            <span class="sa-badge">{{ $ringkasan['terisi'] }}/{{ $ringkasan['total'] }}</span>
        </button>
    </div>

    {{-- ============ VIEW: KIRIM SURVEI ============ --}}
    <div class="sa-view" id="sa-view-kirim">

    {{-- ============ PENCARIAN ============ --}}
    <div class="sa-search">
        <input type="search" class="sa-input" id="sa-q" placeholder="Cari nama atau email konseli..." aria-label="Cari konseli" autocomplete="off">
    </div>

    {{-- ============ TABEL ============ --}}
    <div class="sa-card">
        <div class="sa-wrap">
            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Konseli</th>
                        <th>Jenis</th>
                        <th>Konselor</th>
                        <th>Sesi</th>
                        <th>Selesai</th>
                        <th>Survei</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="sa-body">
                    @foreach($konseli as $k)
                        <tr class="sa-row"
                            data-cari="{{ strtolower($k['nama'] . ' ' . $k['email']) }}"
                            data-state="{{ $k['state'] }}"
                            data-nama="{{ $k['nama'] }}"
                            data-wa="{{ $k['wa'] }}"
                            data-url="{{ $k['url'] }}"
                            data-generate="{{ route('admin.survei.generate', $k['id']) }}">
                            <td class="sa-name">
                                {{ $k['nama'] }}
                                <span class="sa-sub">{{ $k['email'] }}</span>
                            </td>
                            <td><span class="sa-pill jenis">{{ $k['jenis'] }}</span></td>
                            <td>{{ $k['konselor'] }}</td>
                            <td class="sa-small">Ke-{{ $k['sesi'] }}</td>
                            <td class="sa-small sa-soft">{{ $k['selesai'] }}</td>
                            <td>
                                <span class="sa-pill s-belum tone-abu">Belum dibuat</span>
                                <span class="sa-pill s-dibuat tone-biru">Link dibuat</span>
                                <span class="sa-pill s-terisi tone-hijau">Sudah diisi</span>
                            </td>
                            <td>
                                <button type="button" class="sa-btn sa-btn-gen">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                    Generate Survei
                                </button>
                                <button type="button" class="sa-btn sa-btn-kirim">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                                    Kirim Link
                                </button>
                                <span class="sa-dash sa-none">-</span>
                            </td>
                        </tr>
                    @endforeach
                    <tr id="sa-empty" hidden>
                        <td colspan="7" class="sa-empty">Tidak ada konseli yang cocok dengan pencarian.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="sa-foot" id="sa-count" aria-live="polite">{{ $totalSelesai }} konseli dengan sesi selesai</div>
    </div>

    {{-- ============ VIEW: HASIL SURVEI ============ --}}
    <div class="sa-view" id="sa-view-hasil" hidden>
        <div class="sa-sum">
            <h2 class="sa-sum-title">
                {{ $ringkasan['terisi'] }} dari {{ $ringkasan['total'] }} konseli sudah mengisi survei
                <small>({{ $ringkasan['persen'] }}%)</small>
            </h2>
            <div class="sa-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $ringkasan['persen'] }}">
                <i style="width: {{ $ringkasan['persen'] }}%"></i>
            </div>
            <p class="sa-sum-cap">{{ $ringkasan['link_dibuat'] }} dari {{ $ringkasan['total'] }} konseli sudah dibuatkan link survei.</p>
        </div>

        @if($ringkasan['terisi'] > 0)
            <div class="sa-dist">
                @foreach($distribusi as $d)
                    <div class="sa-dist-card">
                        <h3>{{ $d['judul'] }}</h3>
                        @foreach($d['opsi'] as $o)
                            <div class="sa-opt">
                                <div class="sa-opt-top">
                                    <span>{{ $o['label'] }}</span>
                                    <b>{{ $o['jumlah'] }} orang ({{ $o['persen'] }}%)</b>
                                </div>
                                <div class="sa-track"><i style="width: {{ $o['persen'] }}%"></i></div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>

            <h2 class="sa-sec-title">Jawaban masuk</h2>
            <div class="sa-card">
                <div class="sa-wrap">
                    <table class="sa-table">
                        <thead>
                            <tr>
                                <th>Konseli</th>
                                <th>Jenis</th>
                                <th>Konselor</th>
                                <th>Diisi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($responden as $r)
                                <tr data-nama="{{ $r['nama'] }}" data-detail="{{ json_encode($r['detail']) }}">
                                    <td class="sa-name">{{ $r['nama'] }}</td>
                                    <td><span class="sa-pill jenis">{{ $r['jenis'] }}</span></td>
                                    <td>{{ $r['konselor'] }}</td>
                                    <td class="sa-small sa-soft">{{ $r['diisi'] }}</td>
                                    <td>
                                        <button type="button" class="sa-btn sa-btn-lihat">Lihat jawaban</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="sa-kosong">Belum ada konseli yang mengisi survei.</div>
        @endif
    </div>
</div>

{{-- ============ MODAL: LINK SURVEI ============ --}}
<div class="sa-overlay" id="sa-overlay">
    <div class="sa-modal" role="dialog" aria-modal="true" aria-labelledby="sa-modal-title">
        <div class="sa-modal-head">
            <h2 id="sa-modal-title">Link Survei</h2>
            <button type="button" class="sa-close" id="sa-close" aria-label="Tutup">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <p class="sa-desc">Kirim link ini ke <strong id="sa-nama"></strong>. Link hanya berlaku untuk konseli ini.</p>

        <div class="sa-ok" id="sa-ok" hidden>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
            Link survei berhasil dibuat
        </div>

        <label class="sa-label" for="sa-link">Link survei</label>
        <div class="sa-linkrow">
            <input type="text" class="sa-input" id="sa-link" readonly>
            <button type="button" class="sa-btn sa-btn-kirim" id="sa-salin" style="display:inline-flex">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                <span id="sa-salin-teks">Salin Link</span>
            </button>
        </div>

        <span class="sa-label">Pesan WhatsApp</span>
        <pre class="sa-preview" id="sa-pesan"></pre>

        <div class="sa-modal-foot">
            <button type="button" class="sa-btn sa-btn-tutup" id="sa-tutup" style="display:inline-flex">Tutup</button>
            <a class="sa-btn-wa" id="sa-wa" href="#" target="_blank" rel="noopener">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>
                Kirim ke WhatsApp
            </a>
        </div>
    </div>
</div>

{{-- ============ MODAL: DETAIL JAWABAN ============ --}}
<div class="sa-overlay" id="sa-overlay-detail">
    <div class="sa-modal" role="dialog" aria-modal="true" aria-labelledby="sa-detail-title">
        <div class="sa-modal-head">
            <h2 id="sa-detail-title">Jawaban Survei</h2>
            <button type="button" class="sa-close" id="sa-detail-close" aria-label="Tutup">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <p class="sa-desc">Jawaban dari <strong id="sa-detail-nama"></strong></p>
        <dl class="sa-qa-list" id="sa-detail-isi"></dl>
        <div class="sa-modal-foot">
            <button type="button" class="sa-btn sa-btn-tutup" id="sa-detail-tutup" style="display:inline-flex">Tutup</button>
        </div>
    </div>
</div>

<script>
(function () {
    const CSRF    = '{{ csrf_token() }}';
    const body    = document.getElementById('sa-body');
    const rows    = Array.from(body.querySelectorAll('.sa-row'));
    const empty   = document.getElementById('sa-empty');
    const count   = document.getElementById('sa-count');
    const q       = document.getElementById('sa-q');

    const overlay = document.getElementById('sa-overlay');
    const elNama  = document.getElementById('sa-nama');
    const elOk    = document.getElementById('sa-ok');
    const elLink  = document.getElementById('sa-link');
    const elPesan = document.getElementById('sa-pesan');
    const elWa    = document.getElementById('sa-wa');
    const btnSalin = document.getElementById('sa-salin');
    const salinTeks = document.getElementById('sa-salin-teks');

    let salinTimer = null;

    // ---------- Pencarian ----------
    q.addEventListener('input', function () {
        const kata = q.value.trim().toLowerCase();
        let tampil = 0;
        rows.forEach(function (r) {
            const cocok = !kata || r.dataset.cari.includes(kata);
            r.hidden = !cocok;
            if (cocok) tampil++;
        });
        empty.hidden = tampil > 0;
        count.textContent = tampil + ' konseli dengan sesi selesai';
    });

    // ---------- Template pesan & nomor WA ----------
    function buatPesan(nama, url) {
        return 'Halo ' + nama + ', terima kasih telah mengikuti sesi konseling di Pusat Layanan Bimbingan Konseling LPPP UNNES.\n\n'
             + 'Kami mohon kesediaan Anda mengisi survei singkat berikut agar layanan kami semakin baik:\n'
             + url + '\n\n'
             + 'Terima kasih.';
    }

    function normalisasiWa(nomor) {
        let n = (nomor || '').replace(/\D/g, '');
        if (!n) return '';
        if (n.startsWith('0')) n = '62' + n.slice(1);
        else if (n.startsWith('8')) n = '62' + n;
        return n;
    }

    // ---------- Modal ----------
    function bukaModal(row, baru) {
        const url   = row.dataset.url;
        const nama  = row.dataset.nama;
        const pesan = buatPesan(nama, url);

        elNama.textContent = nama;
        elLink.value = url;
        elPesan.textContent = pesan;
        elOk.hidden = !baru;
        elWa.href = 'https://wa.me/' + normalisasiWa(row.dataset.wa) + '?text=' + encodeURIComponent(pesan);

        clearTimeout(salinTimer);
        salinTeks.textContent = 'Salin Link';

        overlay.classList.add('is-open');
        btnSalin.focus();
    }

    function tutupModal() { overlay.classList.remove('is-open'); }

    document.getElementById('sa-close').addEventListener('click', tutupModal);
    document.getElementById('sa-tutup').addEventListener('click', tutupModal);
    overlay.addEventListener('click', function (e) { if (e.target === overlay) tutupModal(); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && overlay.classList.contains('is-open')) tutupModal();
    });

    // ---------- Salin link ----------
    btnSalin.addEventListener('click', function () {
        function berhasil() {
            salinTeks.textContent = 'Tersalin';
            clearTimeout(salinTimer);
            salinTimer = setTimeout(function () { salinTeks.textContent = 'Salin Link'; }, 2000);
        }

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(elLink.value).then(berhasil);
        } else {
            elLink.select();
            document.execCommand('copy');
            berhasil();
        }
    });

    // ---------- Klik tombol di tabel ----------
    body.addEventListener('click', function (e) {
        const btnKirim = e.target.closest('.sa-btn-kirim');
        const btnGen   = e.target.closest('.sa-btn-gen');
        const row      = e.target.closest('.sa-row');
        if (!row) return;

        if (btnKirim) {
            bukaModal(row, false);
            return;
        }

        if (btnGen) {
            btnGen.disabled = true;

            fetch(row.dataset.generate, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
            })
            .then(function (res) { if (!res.ok) throw new Error(); return res.json(); })
            .then(function (data) {
                row.dataset.url = data.url;
                row.dataset.state = 'dibuat';   // tombol otomatis berganti jadi "Kirim Link"
                bukaModal(row, true);
            })
            .catch(function () {
                alert('Link survei gagal dibuat. Coba lagi.');
            })
            .finally(function () { btnGen.disabled = false; });
        }
    });

    // ---------- Tab ----------
    const tabKirim  = document.getElementById('sa-tab-kirim');
    const tabHasil  = document.getElementById('sa-tab-hasil');
    const viewKirim = document.getElementById('sa-view-kirim');
    const viewHasil = document.getElementById('sa-view-hasil');

    function pilihTab(nama) {
        const kirimAktif = nama !== 'hasil';
        tabKirim.classList.toggle('is-active', kirimAktif);
        tabKirim.setAttribute('aria-selected', kirimAktif);
        tabHasil.classList.toggle('is-active', !kirimAktif);
        tabHasil.setAttribute('aria-selected', !kirimAktif);
        viewKirim.hidden = !kirimAktif;
        viewHasil.hidden = kirimAktif;
        history.replaceState(null, '', kirimAktif ? location.pathname + location.search : '#hasil');
    }

    tabKirim.addEventListener('click', function () { pilihTab('kirim'); });
    tabHasil.addEventListener('click', function () { pilihTab('hasil'); });
    if (location.hash === '#hasil') pilihTab('hasil');

    // ---------- Detail jawaban ----------
    const overlayDetail = document.getElementById('sa-overlay-detail');
    const detailNama    = document.getElementById('sa-detail-nama');
    const detailIsi     = document.getElementById('sa-detail-isi');

    function tutupDetail() { overlayDetail.classList.remove('is-open'); }

    viewHasil.addEventListener('click', function (e) {
        const btn = e.target.closest('.sa-btn-lihat');
        if (!btn) return;

        const row = btn.closest('tr');
        detailNama.textContent = row.dataset.nama;
        detailIsi.textContent = '';

        JSON.parse(row.dataset.detail).forEach(function (qa) {
            const wrap = document.createElement('div');
            const dt = document.createElement('dt');
            const dd = document.createElement('dd');
            wrap.className = 'sa-qa';
            dt.textContent = qa.tanya;
            dd.textContent = qa.jawab;
            wrap.appendChild(dt);
            wrap.appendChild(dd);
            detailIsi.appendChild(wrap);
        });

        overlayDetail.classList.add('is-open');
        document.getElementById('sa-detail-tutup').focus();
    });

    document.getElementById('sa-detail-close').addEventListener('click', tutupDetail);
    document.getElementById('sa-detail-tutup').addEventListener('click', tutupDetail);
    overlayDetail.addEventListener('click', function (e) { if (e.target === overlayDetail) tutupDetail(); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && overlayDetail.classList.contains('is-open')) tutupDetail();
    });
})();
</script>
@endsection