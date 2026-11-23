{{--
    SILAKON — Pendampingan Saya (Konselor)
    Simpan sebagai: resources/views/konselor/kasus.blade.php

    Data masih statis, dikirim dari KasusController.

    Routes yang perlu ditambahkan di routes/web.php (grup konselor):
        Route::post('/konselor/kasus/{kode}/catatan', [KasusController::class, 'simpanCatatan'])
            ->name('konselor.kasus.catatan.simpan');
        Route::post('/konselor/kasus/{kode}/survey', [KasusController::class, 'kirimSurvey'])
            ->name('konselor.kasus.survey.kirim');
--}}
@extends('layouts.konselor')

@section('menu', 'kasus')
@section('judul', 'Pendampingan Saya')
@section('subjudul', $tanggalHariIni)

@section('content')

<div class="kasus-halaman">

    {{-- ============ JUDUL ============ --}}
    <div class="kasus-atas">
        <h1>Pendampingan Saya</h1>
        <p>Konseli yang sedang atau pernah Anda tangani beserta riwayat sesi dan catatannya.</p>
    </div>

    {{-- ============ TAB AKTIF / SELESAI ============ --}}
    <div class="kasus-tab" id="kasusTab">
        <button type="button" class="kasus-tab-btn aktif" data-status="Aktif">Aktif</button>
        <button type="button" class="kasus-tab-btn" data-status="Selesai">Selesai</button>
    </div>

    {{-- ============ DAFTAR KASUS ============ --}}
    <div class="kasus-daftar" id="kasusDaftar">
        @forelse($kasus as $item)
            <div class="kartu kasus-kartu" data-status="{{ $item['status'] }}" style="{{ $item['status'] !== 'Aktif' ? 'display:none;' : '' }}">
                <div class="kasus-kartu-isi">
                    <div class="kasus-kartu-judul">
                        <b>{{ $item['nama'] }}</b>
                        <span class="lencana lencana-kasus-{{ \Illuminate\Support\Str::slug($item['status']) }}">{{ $item['status'] }}</span>
                    </div>
                    <div class="kasus-kartu-sub">
                        NIM {{ $item['nim'] }} &middot; {{ $item['kategori'] }} &middot; {{ $item['jumlah_sesi'] }} sesi &middot; Terakhir: {{ $item['terakhir'] }}
                    </div>
                    @if(!empty($item['konselor_sebelumnya']))
                        <div class="kasus-kartu-transfer">Konselor sebelumnya: {{ $item['konselor_sebelumnya'] }}</div>
                    @endif
                </div>

                <div class="kasus-kartu-aksi">
                    @if($item['ada_jadwal'])
                    <button type="button" class="tombol-abu" data-aksi="catatan" data-kode="{{ $item['kode'] }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2Z"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="13" y2="17"/></svg>
                        Catatan
                    </button>
                    <button type="button" class="tombol-hijau" data-aksi="riwayat" data-kode="{{ $item['kode'] }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 2.6-6.3L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l3 3"/></svg>
                        Riwayat
                    </button>
                    @if(!empty($item['survey_tersedia']))
                        <button type="button" class="tombol-garis" data-aksi="survey" data-kode="{{ $item['kode'] }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Survey
                        </button>
                    @endif
                    @else
                        <span style="font-size:13px;color:#7d8582;padding:10px 0;">Menunggu jadwal konseling</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="kartu kasus-kosong">Belum ada konseli yang sedang ditangani.</div>
        @endforelse

        <div class="kasus-kosong-status" id="kasusKosongStatus" style="display:none;">
            Tidak ada pendampingan dengan status ini.
        </div>
    </div>

    {{-- ============ PAGINATION & JUMLAH DATA ============ --}}
    @if(count($kasus) > 0)
        <div class="kartu kasus-kaki" id="kasusKaki">
            <div class="kasus-kaki-kiri">
                <label class="kasus-perhalaman">
                    Tampilkan
                    <select id="selPerHalaman" aria-label="Jumlah data per halaman">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    data
                </label>
                <span class="kasus-info" id="infoKasus"></span>
            </div>
            <nav class="paginasi" id="paginasi" aria-label="Navigasi halaman"></nav>
        </div>
    @endif

</div>

{{-- ============ MODAL (Catatan / Riwayat / Survey) ============ --}}
<div class="modal-overlay" id="modalOverlay">
    <div class="modal-box" id="modalBox">
        <div class="modal-kepala">
            <h3 id="modalJudul">&nbsp;</h3>
            <button type="button" class="modal-tutup" id="modalTutup" aria-label="Tutup">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-isi" id="modalIsi"></div>
    </div>
</div>

<script>
(function () {
    // ============ Data dari controller ============
    var dataKasus = @json($kasus);

    var urlCatatanBase = "{{ route('konselor.kasus.catatan.simpan', ':kode') }}";
    var urlSurveyBase  = "{{ route('konselor.kasus.survey.kirim', ':kode') }}";
    var publicSurveyBase = @json(route('survei.tampil', '__TOKEN__'));
    var csrfToken      = "{{ csrf_token() }}";

    function cariKasus(kode) {
        for (var i = 0; i < dataKasus.length; i++) {
            if (dataKasus[i].kode === kode) return dataKasus[i];
        }
        return null;
    }

    // ============ Tab Aktif / Selesai + Pagination ============
    var tombolTab = document.querySelectorAll('#kasusTab .kasus-tab-btn');
    var kartuSemua = Array.prototype.slice.call(document.querySelectorAll('#kasusDaftar .kasus-kartu'));
    var kosongStatus = document.getElementById('kasusKosongStatus');
    var kakiKasus = document.getElementById('kasusKaki');
    var selPerHalaman = document.getElementById('selPerHalaman');
    var navPaginasi = document.getElementById('paginasi');
    var infoKasus = document.getElementById('infoKasus');

    var statusAktif = 'Aktif';
    var halaman = 1;
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

    function renderKasus() {
        if (!kartuSemua.length) return;

        var cocok = kartuSemua.filter(function (k) { return k.dataset.status === statusAktif; });
        var total = cocok.length;
        var totalHalaman = Math.max(1, Math.ceil(total / perHalaman));
        if (halaman > totalHalaman) halaman = totalHalaman;
        if (halaman < 1) halaman = 1;

        var awal = (halaman - 1) * perHalaman;
        var akhir = Math.min(awal + perHalaman, total);

        kartuSemua.forEach(function (k) { k.style.display = 'none'; });
        cocok.forEach(function (k, i) {
            if (i >= awal && i < akhir) k.style.display = '';
        });

        kosongStatus.style.display = total ? 'none' : 'block';
        if (kakiKasus) kakiKasus.style.display = total ? '' : 'none';
        if (!total) return;

        infoKasus.textContent = 'Menampilkan ' + (awal + 1) + '\u2013' + akhir + ' dari ' + total + ' data';

        var html = '<button type="button" class="pg-btn" data-hal="' + (halaman - 1) + '" aria-label="Halaman sebelumnya"' +
            (halaman === 1 ? ' disabled' : '') + '>&lsaquo;</button>';
        daftarHalaman(halaman, totalHalaman).forEach(function (n) {
            if (n === '...') {
                html += '<span class="pg-titik">&hellip;</span>';
            } else {
                html += '<button type="button" class="pg-btn' + (n === halaman ? ' aktif' : '') + '" data-hal="' + n + '"' +
                    (n === halaman ? ' aria-current="page"' : '') + '>' + n + '</button>';
            }
        });
        html += '<button type="button" class="pg-btn" data-hal="' + (halaman + 1) + '" aria-label="Halaman berikutnya"' +
            (halaman === totalHalaman ? ' disabled' : '') + '>&rsaquo;</button>';
        navPaginasi.innerHTML = html;
    }

    function keAtasDaftar() {
        document.getElementById('kasusTab').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    tombolTab.forEach(function (btn) {
        btn.addEventListener('click', function () {
            tombolTab.forEach(function (b) { b.classList.remove('aktif'); });
            btn.classList.add('aktif');
            statusAktif = btn.dataset.status;
            halaman = 1;
            renderKasus();
        });
    });

    if (navPaginasi) {
        navPaginasi.addEventListener('click', function (e) {
            var btn = e.target.closest('.pg-btn');
            if (!btn || btn.disabled) return;
            halaman = parseInt(btn.dataset.hal, 10);
            renderKasus();
            keAtasDaftar();
        });
        selPerHalaman.addEventListener('change', function () {
            perHalaman = parseInt(selPerHalaman.value, 10);
            halaman = 1;
            renderKasus();
        });
    }

    renderKasus();

    // ============ Modal dasar ============
    var overlay = document.getElementById('modalOverlay');
    var modalJudul = document.getElementById('modalJudul');
    var modalIsi = document.getElementById('modalIsi');

    function bukaModal(judul, isiHtml) {
        modalJudul.textContent = judul;
        modalIsi.innerHTML = isiHtml;
        overlay.classList.add('tampil');
    }
    function tutupModal() {
        overlay.classList.remove('tampil');
        modalIsi.innerHTML = '';
    }
    document.getElementById('modalTutup').addEventListener('click', tutupModal);
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) tutupModal();
    });

    // ============ Modal: Catatan ============
    function bukaCatatan(k) {
        var sesi = k.sesi || [];
        var escHtml = function (v) { return String(v || '').replace(/[&<>"']/g, function (c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]; }); };
        var html = '<p class="modal-ket">Setiap sesi memiliki catatan sendiri. Catatan sesi yang sudah selesai tetap tersimpan dan tidak akan ditimpa oleh sesi berikutnya.</p><div class="catatan-sesi-list">';
        sesi.forEach(function (s) {
            var selesai = s.status.toLowerCase() === 'selesai';
            html += '<section class="catatan-sesi-item"><div class="catatan-sesi-head"><b>Sesi ' + s.nomor + '</b><span>' + escHtml(s.tanggal) + ' · ' + escHtml(s.jam) + ' · ' + escHtml(s.status) + '</span></div>';
            if (selesai) {
                html += '<div class="modal-catatan-final"><b>Catatan:</b><br>' + escHtml(s.catatan || 'Belum diisi').replace(/\n/g, '<br>') + (s.hasil ? '<br><br><b>Hasil:</b> ' + escHtml(s.hasil) : '') + '</div>';
            } else {
                html += '<form class="formCatatanSesi" data-jadwal-id="' + s.id + '"><textarea class="modal-textarea" name="catatan" rows="6" placeholder="Tulis catatan untuk sesi ' + s.nomor + '...">' + escHtml(s.catatan || '') + '</textarea><div class="modal-aksi"><button type="submit" class="tombol-hijau-solid">Simpan Catatan Sesi ' + s.nomor + '</button></div></form>';
            }
            html += '</section>';
        });
        html += '</div><div class="modal-aksi"><button type="button" class="tombol-abu" id="batalCatatan">Tutup</button></div>';
        bukaModal('Catatan Konseling — ' + k.nama, html);
        document.getElementById('batalCatatan').addEventListener('click', tutupModal);
        document.querySelectorAll('.formCatatanSesi').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var teks = form.querySelector('textarea[name="catatan"]').value.trim();
                if (!teks) return tampilkanToast('Catatan wajib diisi.');
                fetch(urlCatatanBase.replace(':kode', encodeURIComponent(k.kode)), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: new URLSearchParams({ _token: csrfToken, catatan: teks, jadwal_id: form.dataset.jadwalId }).toString()
                }).then(function (response) {
                    if (!response.ok) return response.json().catch(function(){ return {}; }).then(function (body) { throw new Error(body.message || 'Gagal menyimpan catatan'); });
                    var sesiData = (k.sesi || []).find(function (s) { return String(s.id) === String(form.dataset.jadwalId); });
                    if (sesiData) sesiData.catatan = teks;
                    tutupModal();
                    tampilkanToast('Catatan sesi berhasil disimpan.');
                }).catch(function (error) { tampilkanToast(error.message || 'Catatan gagal disimpan.'); });
            });
        });
    }

    // ============ Modal: Riwayat ============
    function bukaRiwayat(k) {
        var total = k.riwayat.length;
        var html = '<p class="modal-ket">Total <b>' + total + ' entri</b> pengajuan/sesi tercatat untuk konseli ini.</p>';
        html += '<div class="riwayat-list">';

        k.riwayat.forEach(function (r) {
            html += '' +
                '<div class="riwayat-baris">' +
                    '<div class="riwayat-bulat">' + r.sesi + '</div>' +
                    '<div class="riwayat-info">' +
                        '<b>' + (r.sesi ? ('Sesi ' + r.sesi) : 'Pengajuan baru') + '</b>' +
                        '<span class="sel-sub">' + r.tanggal + ' &middot; ' + r.konselor + (r.pengajuan ? ' &middot; ' + r.pengajuan : '') + '</span>' +
                        '<span class="sel-sub">Status: ' + (r.status || '-') + '</span>' +
                        '<span class="sel-sub"><b>Uraian:</b> ' + (r.uraian || '-') + '</span>' +
                        (r.catatan ? '<span class="sel-sub"><b>Catatan:</b> ' + r.catatan + '</span>' : '') +
                        (r.hasil ? '<span class="sel-sub"><b>Hasil:</b> ' + r.hasil + '</span>' : '') +
                    '</div>' +
                    '<span class="lencana lencana-kategori">' + r.kategori + '</span>' +
                '</div>';
        });

        html += '</div>';
        bukaModal('Riwayat Sesi — ' + k.nama, html);
    }

    // ============ Modal: Survey ============
    function bukaSurvey(k) {
        var targetSesi = (k.sesi || []).find(function (s) { return s.boleh_survey; }) || (k.sesi || []).find(function (s) { return s.survey_status === 'dikirim'; });
        var link = targetSesi && targetSesi.survey_url ? targetSesi.survey_url : 'Link belum dibuat. Klik Generate Link.';
        var statusLencana = targetSesi && targetSesi.survey_status === 'dikirim'
            ? '<span class="lencana lencana-survey-terkirim">Sudah Dibagikan</span>'
            : '<span class="lencana lencana-survey-belum">Belum Dibagikan</span>';

        var html = '' +
            '<p class="modal-ket">Survey dibuat per sesi. Pilihan saat ini: <b>Sesi ' + (targetSesi ? targetSesi.nomor : '-') + '</b>. Survey sesi sebelumnya tidak akan tertimpa.</p>' +
            statusLencana +
            '<div class="survey-contoh">' +
                '<div class="survey-contoh-judul">Contoh pertanyaan pada survey:</div>' +
                '<ul>' +
                    '<li>Seberapa membantu sesi konseling ini dalam menyelesaikan masalah Anda?</li>' +
                    '<li>Apakah suasana sesi konseling membuat Anda nyaman untuk terbuka?</li>' +
                    '<li>Apakah tujuan konseling yang Anda harapkan tercapai?</li>' +
                    '<li>Apa saran Anda untuk proses konseling ke depannya?</li>' +
                '</ul>' +
            '</div>' +
            '<div class="survey-link-baris">' +
                '<input type="text" class="survey-link-input" id="surveyLinkInput" value="' + link + '" readonly>' +
                '<button type="button" class="tombol-abu" id="salinLink">Salin</button>' +
            '</div>' +
            '<div class="survey-wa-baris"><a class="tombol-wa" id="bagikanWa" href="#" target="_blank" rel="noopener">' +
                '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/><path d="M8 9.5c.2 1.2 1.3 2.4 2.5 3.2 1.2.8 2.4 1.1 3.5.6l1-.5"/></svg> Bagikan ke WhatsApp</a></div>' +
            '<div class="modal-aksi">' +
                '<button type="button" class="tombol-abu" id="batalSurvey">Tutup</button>' +
                (targetSesi ? '<button type="button" class="tombol-hijau-solid" id="kirimSurvey">' + (targetSesi.survey_status === 'dikirim' ? 'Gunakan Link Sesi Ini' : 'Generate Link Sesi ' + targetSesi.nomor) + '</button>' : '<span class="sel-sub">Survey tersedia setelah sesi selesai.</span>') +
            '</div>';

        bukaModal('Bagikan Survey Konseling — ' + k.nama, html);

        document.getElementById('batalSurvey').addEventListener('click', tutupModal);

        var wa = ((targetSesi && targetSesi.survey_wa) || k.survey_wa || '').replace(/\D/g, '');
        if (wa.charAt(0) === '0') wa = '62' + wa.slice(1); else if (wa.charAt(0) === '8') wa = '62' + wa;
        var pesanWa = 'Halo ' + k.nama + ', terima kasih telah mengikuti sesi konseling di LPPP UNNES.\\n\\nMohon mengisi survei layanan konseling berikut:\\n' + link + '\\n\\nTerima kasih.';
        var tombolWa = document.getElementById('bagikanWa');
        tombolWa.href = wa && link.indexOf('Link belum') !== 0 ? 'https://wa.me/' + wa + '?text=' + encodeURIComponent(pesanWa) : '#';
        tombolWa.addEventListener('click', function (e) { if (tombolWa.href.endsWith('#')) { e.preventDefault(); tampilkanToast('Generate link terlebih dahulu.'); } });

        document.getElementById('salinLink').addEventListener('click', function () {
            var input = document.getElementById('surveyLinkInput');
            if (!input.value || input.value.indexOf('Link belum') === 0) return tampilkanToast('Generate link terlebih dahulu.');
            input.select();
            input.setSelectionRange(0, 99999);
            try {
                navigator.clipboard.writeText(input.value);
            } catch (e) {
                document.execCommand('copy');
            }
            tampilkanToast('Tautan survey disalin.');
        });

        var tombolGenerate = document.getElementById('kirimSurvey');
        if (tombolGenerate) tombolGenerate.addEventListener('click', function () {
            var tombol = this; tombol.disabled = true; tombol.textContent = 'Membuat link...';
            fetch(urlSurveyBase.replace(':kode', encodeURIComponent(k.kode)), {
                method: 'POST', headers: {'Accept':'application/json','X-CSRF-TOKEN':csrfToken,'X-Requested-With':'XMLHttpRequest'},
                body: new URLSearchParams({ jadwal_id: targetSesi ? targetSesi.id : '' }).toString()
            }).then(function (response) { return response.json().then(function(body){ if(!response.ok) throw new Error(body.message || 'Gagal membuat link survei'); return body; });
            }).then(function (body) {
                if (targetSesi) { targetSesi.survey_status = 'dikirim'; targetSesi.survey_url = body.url; }
                k.survey_terkirim = true; k.survey_url = body.url;
                tutupModal(); tampilkanToast('Link survei berhasil dibuat.');
            }).catch(function (error) { tombol.disabled = false; tombol.textContent = 'Bagikan ke Konseli'; tampilkanToast(error.message); });
        });
    }

    // ============ Toast sederhana ============
    var toastTimer = null;
    function tampilkanToast(pesan) {
        var t = document.getElementById('kasusToast');
        if (!t) {
            t = document.createElement('div');
            t.id = 'kasusToast';
            t.className = 'kasus-toast';
            document.body.appendChild(t);
        }
        t.textContent = pesan;
        t.classList.add('tampil');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { t.classList.remove('tampil'); }, 2600);
    }

    // ============ Klik tombol aksi di kartu ============
    document.getElementById('kasusDaftar').addEventListener('click', function (e) {
        var btn = e.target.closest('button[data-aksi]');
        if (!btn) return;

        var k = cariKasus(btn.dataset.kode);
        if (!k) return;

        if (btn.dataset.aksi === 'catatan') bukaCatatan(k);
        if (btn.dataset.aksi === 'riwayat') bukaRiwayat(k);
        if (btn.dataset.aksi === 'survey') bukaSurvey(k);
    });
})();
</script>

<style>
    .catatan-sesi-list { display:flex; flex-direction:column; gap:14px; }
    .catatan-sesi-item { border:1px solid #e0e7e1; border-radius:10px; padding:14px; background:#fbfdfb; }
    .catatan-sesi-head { display:flex; justify-content:space-between; gap:12px; margin-bottom:10px; color:#294333; }
    .catatan-sesi-head span { color:#7a887f; font-size:12px; }
    .kasus-atas { margin-bottom: 16px; }
    .kasus-atas h1 { font-size: 22px; margin: 0 0 4px; color: #16241d; }
    .kasus-atas p { margin: 0; color: #6b7a72; font-size: 14px; }

    .kasus-tab {
        display: inline-flex;
        gap: 8px;
        margin-bottom: 18px;
    }
    .kasus-tab-btn {
        padding: 8px 18px;
        border: none;
        border-radius: 999px;
        background: #eef2ef;
        color: #5c6b63;
        font-size: 13.5px;
        font-weight: 700;
        cursor: pointer;
        transition: background .15s, color .15s;
    }
    .kasus-tab-btn.aktif { background: #2f6b52; color: #fff; }
    .kasus-tab-btn:not(.aktif):hover { background: #e2e9e4; }

    .kasus-daftar { display: flex; flex-direction: column; gap: 16px; }

    .kasus-kartu {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 22px;
        flex-wrap: wrap;
    }
    .kasus-kartu-isi { flex: 1; min-width: 0; }
    .kasus-kartu-judul { display: flex; align-items: center; gap: 10px; margin-bottom: 6px; }
    .kasus-kartu-judul { flex-wrap: wrap; }
    .kasus-kartu-judul b { font-size: 16px; color: #16241d; overflow-wrap: anywhere; }
    .kasus-kartu-sub { font-size: 13px; color: #6b7a72; overflow-wrap: anywhere; }
    .kasus-kartu-transfer { font-size: 12px; color: #9aa5a0; margin-top: 6px; }

    .kasus-kartu-aksi { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }

    .kasus-kosong, .kasus-kosong-status {
        text-align: center;
        color: #8a978f;
        font-size: 13.5px;
        padding: 30px 12px;
    }

    .lencana {
        display: inline-flex;
        align-items: center;
        padding: 3px 12px;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 700;
        white-space: nowrap;
    }
    .lencana-kasus-aktif { background: #dff3e6; color: #1f7a4d; }
    .lencana-kasus-selesai { background: #e5e9f0; color: #445; }
    .lencana-kategori { background: #dfeaf9; color: #1d5fa8; }
    .lencana-survey-terkirim { background: #dff3e6; color: #1f7a4d; margin-bottom: 12px; }
    .lencana-survey-belum { background: #fdf3d0; color: #92720c; margin-bottom: 12px; }

    /* ============ Tombol ============ */
    .tombol-abu, .tombol-hijau, .tombol-garis, .tombol-hijau-solid {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 16px;
        border-radius: 8px;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        white-space: nowrap;
        transition: background .15s, border-color .15s, color .15s;
    }
    .tombol-abu svg, .tombol-hijau svg, .tombol-garis svg { width: 14px; height: 14px; }

    .tombol-abu { background: #eef2ef; color: #33403a; }
    .tombol-abu:hover { background: #e2e9e4; }

    .tombol-hijau { background: #2f6b52; color: #fff; }
    .tombol-hijau:hover { background: #24543f; }

    .tombol-garis { background: #fff; color: #2f6b52; border: 1px solid #bcd9c9; }
    .tombol-garis:hover { background: #f2f8f4; }

    .tombol-hijau-solid { background: #2f6b52; color: #fff; padding: 10px 18px; }
    .tombol-hijau-solid:hover { background: #24543f; }

    /* ============ Modal ============ */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(16, 24, 20, .45);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: opacity .15s;
        z-index: 1000;
        padding: 20px;
    }
    .modal-overlay.tampil { opacity: 1; visibility: visible; }

    .modal-box {
        background: #fff;
        border-radius: 14px;
        width: 100%;
        max-width: 520px;
        max-height: 85vh;
        overflow-y: auto;
        box-shadow: 0 20px 50px rgba(16,24,20,.25);
    }
    .modal-kepala {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 20px;
        border-bottom: 1px solid #eef2ef;
        position: sticky;
        top: 0;
        background: #fff;
    }
    .modal-kepala h3 { margin: 0; font-size: 16px; color: #16241d; }
    .modal-tutup {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 8px;
        border: none;
        background: #f2f5f3;
        color: #5c6b63;
        cursor: pointer;
    }
    .modal-tutup svg { width: 14px; height: 14px; }
    .modal-tutup:hover { background: #e6ece8; }

    .modal-isi { padding: 20px; }
    .modal-ket { margin: 0 0 14px; font-size: 13px; color: #6b7a72; line-height: 1.5; }

    .modal-textarea {
        width: 100%;
        border: 1px solid #dfe6e1;
        border-radius: 10px;
        padding: 12px 14px;
        font-size: 13.5px;
        color: #33403a;
        font-family: inherit;
        resize: vertical;
        min-height: 130px;
    }
    .modal-textarea:focus { outline: none; border-color: #2f6b52; }

    .modal-catatan-final {
        background: #f7faf8;
        border: 1px solid #e6ece8;
        border-radius: 10px;
        padding: 14px 16px;
        font-size: 13.5px;
        color: #33403a;
        line-height: 1.6;
    }

    .modal-aksi { display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px; }

    .riwayat-list { display: flex; flex-direction: column; gap: 4px; }
    .riwayat-baris {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid #eef2ef;
    }
    .riwayat-baris:last-child { border-bottom: none; }
    .riwayat-bulat {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #dff3e6;
        color: #1f7a4d;
        font-size: 12.5px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .riwayat-info { display: flex; flex-direction: column; flex: 1; }
    .riwayat-info b { font-size: 13.5px; color: #16241d; }
    .sel-sub { font-size: 11.5px; color: #8a978f; margin-top: 1px; }

    .survey-contoh {
        background: #f7faf8;
        border: 1px solid #e6ece8;
        border-radius: 10px;
        padding: 14px 16px;
        margin-bottom: 14px;
    }
    .survey-contoh-judul { font-size: 12.5px; font-weight: 700; color: #33403a; margin-bottom: 8px; }
    .survey-contoh ul { margin: 0; padding-left: 18px; }
    .survey-contoh li { font-size: 12.5px; color: #5c6b63; margin-bottom: 5px; line-height: 1.5; }
    .survey-contoh li:last-child { margin-bottom: 0; }

    .survey-link-baris { display: flex; gap: 8px; }
    .survey-link-input {
        flex: 1;
        border: 1px solid #dfe6e1;
        border-radius: 8px;
        padding: 9px 12px;
        font-size: 12.5px;
        color: #5c6b63;
        background: #f7faf8;
    }
    .survey-wa-baris { margin-top: 10px; }
    .tombol-wa {
        display: inline-flex; align-items: center; gap: 7px; padding: 9px 13px;
        border-radius: 8px; background: #25D366; color: #fff; text-decoration: none;
        font-size: 12.5px; font-weight: 700; transition: filter .15s ease;
    }
    .tombol-wa:hover { filter: brightness(.93); }
    .tombol-wa svg { width: 16px; height: 16px; }

    .kasus-toast {
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%) translateY(20px);
        background: #16241d;
        color: #fff;
        padding: 12px 20px;
        border-radius: 10px;
        font-size: 13px;
        opacity: 0;
        visibility: hidden;
        transition: opacity .2s, transform .2s;
        z-index: 1100;
    }
    .kasus-toast.tampil { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); }

    /* ============ Footer: jumlah data & pagination ============ */
    .kasus-kaki {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 16px;
        padding: 12px 18px;
        font-size: 12.5px;
        color: #5c6b63;
    }
    .kasus-kaki-kiri { display: flex; align-items: center; flex-wrap: wrap; gap: 8px 16px; }
    .kasus-perhalaman { display: inline-flex; align-items: center; gap: 6px; }
    .kasus-perhalaman select {
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
    .kasus-perhalaman select:focus { outline: none; border-color: #2f6b52; }
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

    @media (max-width: 640px) {
        .kasus-kaki { justify-content: center; }
        .kasus-kartu { flex-direction: column; }
        .kasus-kartu-aksi { width: 100%; }
        .kasus-kartu-aksi button { flex: 1; justify-content: center; }
    }
</style>

@endsection
