{{--
    SILAKON — Kalender Jadwal (Admin)
    Simpan sebagai: resources/views/admin/kalender.blade.php
    Route contoh:  Route::get('/admin/kalender', [AdminKalenderController::class, 'index'])->name('admin.kalender');

    Data (jadwal, konselor, ruang) dikirim dari AdminKalenderController dan dirender
    di sisi klien (navigasi bulan, filter, deteksi konflik, panel detail tanpa reload).
    Subjudul tidak di-override, jadi otomatis memakai tanggal hari ini dari navbar.blade.php.
--}}
@extends('layouts.admin')

@section('judul', 'Kalender')

@section('content')

<style>
    .kal {
        --kal-primary: #6f9a72;
        --kal-primary-soft: #eef5ee;
        --kal-ink: #1a2321;
        --kal-muted: #8a918e;
        --kal-line: #e7eae6;
        --kal-blank: #f7f8f6;
    }

    /* Warna per konselor */
    .kal .t-hijau  { --bg: #e8f1e8; --fg: #6d9a72; --bd: #6d9a72; }
    .kal .t-biru   { --bg: #dce8fc; --fg: #3b74d9; --bd: #3b82f6; }
    .kal .t-ungu   { --bg: #e9e3fc; --fg: #7c55e0; --bd: #8b5cf6; }
    .kal .t-kuning { --bg: #fdedb3; --fg: #c58705; --bd: #f5a30b; }
    .kal .t-merah  { --bg: #fcd8d8; --fg: #dc2626; --bd: #ef4444; }

    /* Header halaman */
    .kal-head {
        display: flex; align-items: center; justify-content: space-between;
        gap: 16px; flex-wrap: wrap; margin-bottom: 24px;
    }
    .kal-head h1 { margin: 0; font-size: 22px; font-weight: 700; color: var(--kal-ink); }
    .kal-head p  { margin: 6px 0 0; font-size: 14px; color: var(--kal-muted); }

    .kal-nav { display: flex; align-items: center; gap: 12px; }
    .kal-nav-title { font-size: 17px; font-weight: 700; color: var(--kal-ink); min-width: 0; }
    .kal-nav-btn {
        width: 36px; height: 30px; display: grid; place-items: center;
        background: #fff; border: 1px solid var(--kal-line); border-radius: 8px;
        color: var(--kal-ink); cursor: pointer; transition: background .15s, border-color .15s;
    }
    .kal-nav-btn:hover { background: var(--kal-primary-soft); border-color: var(--kal-primary); }
    .kal-nav-btn svg { width: 16px; height: 16px; }

    /* Banner konflik */
    .kal-alert {
        display: flex; align-items: center; gap: 10px;
        padding: 14px 18px; margin-bottom: 20px;
        background: #fdf3c9; border: 1px solid #f3e1a0; border-radius: 12px;
        color: #8a3f0b; font-size: 14px;
    }
    .kal-alert[hidden] { display: none; }
    .kal-alert svg { width: 17px; height: 17px; flex: none; }

    /* Filter */
    .kal-filters { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; }
    .kal-select {
        appearance: none; -webkit-appearance: none;
        padding: 12px 40px 12px 20px;
        font: inherit; font-size: 14px; font-weight: 500; color: var(--kal-ink);
        background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%231a2321' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E") no-repeat right 14px center;
        border: 1px solid var(--kal-line); border-radius: 12px; cursor: pointer;
        min-width: 150px;
    }
    .kal-select:focus-visible { outline: 2px solid var(--kal-primary); outline-offset: 2px; }

    /* Kalender + panel */
    .kal-layout { display: grid; grid-template-columns: minmax(0, 1fr) 280px; gap: 20px; align-items: start; }
    .kal-layout.no-panel { grid-template-columns: minmax(0, 1fr); }
    .kal-layout.no-panel .kal-panel { display: none; }

    .kal-card {
        background: #fff; border: 1px solid var(--kal-line); border-radius: 20px;
        padding: 20px 20px 24px;
    }
    .kal-grid { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 6px; }
    .kal-dow {
        text-align: center; font-size: 13px; font-weight: 600; color: #a3a9a6;
        padding: 12px 0 10px;
    }

    .kal-day {
        display: flex; flex-direction: column; align-items: stretch; gap: 4px;
        min-height: 80px; padding: 10px 8px 8px 10px; text-align: left;
        font: inherit; color: var(--kal-ink);
        background: #fff; border: 1px solid var(--kal-line); border-radius: 10px;
        cursor: pointer; transition: border-color .15s, background .15s;
    }
    .kal-day:hover { border-color: #c9d5c9; }
    .kal-day:focus-visible { outline: 2px solid var(--kal-primary); outline-offset: 1px; }
    .kal-day.is-selected {
        background: var(--kal-primary-soft);
        border: 2px solid var(--kal-primary);
        padding: 9px 7px 7px 9px;
    }
    .kal-day.is-blank {
        background: var(--kal-blank); border-color: #f0f1ef; cursor: default; pointer-events: none;
    }
    .kal-day-num { font-size: 14px; font-weight: 700; margin-bottom: 2px; }

    .kal-event {
        display: block; padding: 2px 6px; border-radius: 4px;
        font-size: 11px; line-height: 1.5; font-weight: 500;
        background: var(--bg); color: var(--fg);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }

    /* Panel detail hari */
    .kal-panel {
        background: #fff; border: 1px solid var(--kal-line); border-radius: 20px; padding: 20px;
    }
    .kal-panel-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
    .kal-panel-head h2 { margin: 0; font-size: 16px; font-weight: 700; color: var(--kal-ink); }
    .kal-close {
        width: 28px; height: 28px; display: grid; place-items: center;
        background: none; border: 0; border-radius: 6px; color: var(--kal-muted); cursor: pointer;
    }
    .kal-close:hover { background: var(--kal-blank); color: var(--kal-ink); }
    .kal-close svg { width: 16px; height: 16px; }

    .kal-session {
        padding: 12px 14px; margin-bottom: 10px; border-radius: 8px;
        background: var(--bg); border-left: 4px solid var(--bd);
    }
    .kal-session:last-child { margin-bottom: 0; }
    .kal-session-time { font-size: 14px; font-weight: 700; color: var(--bd); }
    .kal-session-name { margin-top: 6px; font-size: 15px; font-weight: 700; color: var(--kal-ink); }
    .kal-session-meta { margin-top: 4px; font-size: 13px; color: var(--kal-muted); }
    .kal-empty { margin: 0; font-size: 14px; color: var(--kal-muted); }

    @media (max-width: 960px) {
        .kal-layout { grid-template-columns: minmax(0, 1fr); }
    }
    @media (max-width: 640px) {
        .kal-card { padding: 12px; border-radius: 16px; }
        .kal-grid { gap: 3px; }
        .kal-dow { font-size: 11px; }
        .kal-day { min-height: 64px; padding: 6px 4px; }
        .kal-day.is-selected { padding: 5px 3px; }
        .kal-event { font-size: 9px; padding: 1px 3px; }
        .kal-select { flex: 1; min-width: 0; }
    }
</style>

<div class="kal" id="kal">

    {{-- ============ JUDUL & NAVIGASI BULAN ============ --}}
    <div class="kal-head">
        <div>
            <h1>Kalender Jadwal</h1>
            <p>Tampilan bulanan per konselor dan ruang</p>
        </div>

        <div class="kal-nav">
            <button type="button" class="kal-nav-btn" id="kal-prev" aria-label="Bulan sebelumnya">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            </button>
            <span class="kal-nav-title" id="kal-title" aria-live="polite"></span>
            <button type="button" class="kal-nav-btn" id="kal-next" aria-label="Bulan berikutnya">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </button>
        </div>
    </div>

    {{-- ============ BANNER KONFLIK JADWAL ============ --}}
    <div class="kal-alert" id="kal-alert" role="alert" hidden>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>
        </svg>
        <span id="kal-alert-text"></span>
    </div>

    {{-- ============ FILTER ============ --}}
    <div class="kal-filters">
        <select class="kal-select" id="kal-filter-konselor" aria-label="Filter konselor">
            <option value="">Semua Konselor</option>
            @foreach($konselor as $k)
                <option value="{{ $k['id'] }}">{{ $k['nama'] }}</option>
            @endforeach
        </select>

        <select class="kal-select" id="kal-filter-ruang" aria-label="Filter ruang">
            <option value="">Semua Ruang</option>
            @foreach($ruang as $r)
                <option value="{{ $r }}">{{ $r }}</option>
            @endforeach
        </select>
    </div>

    {{-- ============ KALENDER & PANEL HARI ============ --}}
    <div class="kal-layout" id="kal-layout">
        <div class="kal-card">
            <div class="kal-grid" id="kal-grid"></div>
        </div>

        <aside class="kal-panel" id="kal-panel" aria-live="polite">
            <div class="kal-panel-head">
                <h2 id="kal-panel-title"></h2>
                <button type="button" class="kal-close" id="kal-close" aria-label="Tutup detail">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
            <div id="kal-panel-body"></div>
        </aside>
    </div>
</div>

<script>
(function () {
    // ===== Data dari controller (dummy) =====
    const JADWAL   = @json($jadwal);
    const KONSELOR = @json($konselor);

    const BULAN       = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    const BULAN_SHORT = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    const HARI        = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];

    const konselorMap = {};
    KONSELOR.forEach(k => { konselorMap[k.id] = k; });

    const [awalTahun, awalBulan] = @json($bulanAwal).split('-').map(Number);
    const state = {
        year: awalTahun,
        month: awalBulan - 1, // 0-11
        konselor: '',
        ruang: '',
        selected: @json($tanggalTerpilih),
    };

    const $ = id => document.getElementById(id);
    const pad = n => String(n).padStart(2, '0');
    const iso = (y, m, d) => `${y}-${pad(m + 1)}-${pad(d)}`;
    const el = (tag, cls, text) => {
        const node = document.createElement(tag);
        if (cls) node.className = cls;
        if (text !== undefined) node.textContent = text;
        return node;
    };
    const toneOf = ev => 't-' + ((konselorMap[ev.konselor_id] || {}).tone || 'hijau');
    const byTime = (a, b) => a.waktu.localeCompare(b.waktu);

    // ===== Filter =====
    function filtered() {
        return JADWAL.filter(ev =>
            (!state.konselor || ev.konselor_id === state.konselor) &&
            (!state.ruang || ev.ruang === state.ruang)
        );
    }

    // ===== Deteksi konflik: ruang fisik yang sama, tanggal & jam yang sama =====
    function renderKonflik() {
        const prefix = `${state.year}-${pad(state.month + 1)}-`;
        const groups = {};
        JADWAL.forEach(ev => {
            if (!ev.tanggal.startsWith(prefix) || ev.ruang === 'Zoom') return;
            const key = `${ev.tanggal}|${ev.waktu}|${ev.ruang}`;
            (groups[key] = groups[key] || []).push(ev);
        });

        const konflik = Object.keys(groups).sort().filter(k => groups[k].length > 1).map(key => {
            const [tgl, waktu, ruang] = key.split('|');
            const [, m, d] = tgl.split('-').map(Number);
            return { jumlah: groups[key].length, label: `${d} ${BULAN_SHORT[m - 1]}, ${waktu}, ${ruang}` };
        });

        const banner = $('kal-alert');
        if (!konflik.length) { banner.hidden = true; return; }

        $('kal-alert-text').textContent = konflik.length === 1
            ? `Terdeteksi konflik jadwal: ${konflik[0].jumlah} sesi dijadwalkan di ruang yang sama pada waktu yang sama (${konflik[0].label}).`
            : `Terdeteksi ${konflik.length} konflik jadwal: ` + konflik.map(k => `${k.jumlah} sesi bentrok (${k.label})`).join('; ') + '.';
        banner.hidden = false;
    }

    // ===== Grid bulan =====
    function renderGrid() {
        const grid = $('kal-grid');
        grid.replaceChildren();

        HARI.forEach(h => grid.appendChild(el('div', 'kal-dow', h)));

        const offset = new Date(state.year, state.month, 1).getDay();
        const jumlahHari = new Date(state.year, state.month + 1, 0).getDate();
        const totalCell = Math.ceil((offset + jumlahHari) / 7) * 7;

        const perTanggal = {};
        filtered().forEach(ev => { (perTanggal[ev.tanggal] = perTanggal[ev.tanggal] || []).push(ev); });

        for (let i = 0; i < totalCell; i++) {
            const d = i - offset + 1;

            if (d < 1 || d > jumlahHari) {
                const blank = el('div', 'kal-day is-blank');
                blank.setAttribute('aria-hidden', 'true');
                grid.appendChild(blank);
                continue;
            }

            const tgl = iso(state.year, state.month, d);
            const cell = el('button', 'kal-day' + (tgl === state.selected ? ' is-selected' : ''));
            cell.type = 'button';
            cell.setAttribute('aria-pressed', tgl === state.selected ? 'true' : 'false');
            cell.setAttribute('aria-label', `${d} ${BULAN[state.month]} ${state.year}`);
            cell.appendChild(el('span', 'kal-day-num', String(d)));

            (perTanggal[tgl] || []).sort(byTime).forEach(ev => {
                cell.appendChild(el('span', `kal-event ${toneOf(ev)}`, `${ev.waktu} ${ev.konseli}`));
            });

            cell.addEventListener('click', () => { state.selected = tgl; render(); });
            grid.appendChild(cell);
        }
    }

    // ===== Panel detail hari =====
    function renderPanel() {
        const layout = $('kal-layout');
        if (!state.selected) { layout.classList.add('no-panel'); return; }
        layout.classList.remove('no-panel');

        const [y, m, d] = state.selected.split('-').map(Number);
        $('kal-panel-title').textContent = `${d} ${BULAN[m - 1]} ${y}`;

        const body = $('kal-panel-body');
        body.replaceChildren();

        const sesi = filtered().filter(ev => ev.tanggal === state.selected).sort(byTime);
        if (!sesi.length) {
            body.appendChild(el('p', 'kal-empty', 'Tidak ada sesi pada tanggal ini.'));
            return;
        }

        sesi.forEach(ev => {
            const kons = konselorMap[ev.konselor_id];
            const card = el('div', `kal-session ${toneOf(ev)}`);
            card.appendChild(el('div', 'kal-session-time', `${ev.waktu} WIB`));
            card.appendChild(el('div', 'kal-session-name', ev.konseli));
            card.appendChild(el('div', 'kal-session-meta', kons ? kons.nama : '-'));
            card.appendChild(el('div', 'kal-session-meta', ev.ruang));
            body.appendChild(card);
        });
    }

    function render() {
        $('kal-title').textContent = `${BULAN[state.month]} ${state.year}`;
        renderKonflik();
        renderGrid();
        renderPanel();
    }

    // ===== Event =====
    function geser(delta) {
        const tgl = new Date(state.year, state.month + delta, 1);
        state.year = tgl.getFullYear();
        state.month = tgl.getMonth();
        state.selected = null;
        render();
    }

    $('kal-prev').addEventListener('click', () => geser(-1));
    $('kal-next').addEventListener('click', () => geser(1));
    $('kal-close').addEventListener('click', () => { state.selected = null; render(); });
    $('kal-filter-konselor').addEventListener('change', e => { state.konselor = e.target.value; render(); });
    $('kal-filter-ruang').addEventListener('change', e => { state.ruang = e.target.value; render(); });

    render();
})();
</script>
@endsection
