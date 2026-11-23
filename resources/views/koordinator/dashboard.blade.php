{{--
    SILAKON — Dashboard Analisis Koordinator
    Simpan sebagai: resources/views/koordinator/dashboard.blade.php
    Data dummy dikirim dari KoordinatorController@dashboard (variabel tidak berubah).
    Butuh: public/css/koordinator.css versi terbaru (bagian "RESPONSIVE v2 + v3").
--}}
@extends('layouts.koordinator')

@section('judul', 'Dashboard Analisis')
@section('subjudul', now()->translatedFormat('l, d F Y'))

@section('content')
<div class="k-dash">

{{-- ============ HEAD + FILTER ============ --}}
<div class="k-head-row">
    <div class="k-page-head">
        <h2>Dashboard Analisis</h2>
        <p>Rekap layanan konseling LP3 UNNES</p>
    </div>
    <form class="k-filter" method="GET" action="{{ route('koordinator.dashboard') }}">
        <div class="k-range">
            <svg class="k-range-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            <label class="k-range-field"><span>Dari</span>
                <input type="date" name="dari" value="{{ $dariInput ?? '' }}" aria-label="Tanggal awal">
            </label>
            <span class="k-range-sep" aria-hidden="true"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
            <label class="k-range-field"><span>Sampai</span>
                <input type="date" name="sampai" value="{{ $sampaiInput ?? '' }}" aria-label="Tanggal akhir">
            </label>
        </div>
        <div class="k-filter-actions">
            <button type="submit" class="k-btn">Terapkan</button>
            @if(($dariInput ?? '') !== '' || ($sampaiInput ?? '') !== '')
                <a href="{{ route('koordinator.dashboard') }}" class="k-btn k-btn-ghost" title="Tampilkan semua data" aria-label="Tampilkan semua data">
                    <svg class="k-btn-x" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    <span class="k-btn-txt">Semua Data</span>
                </a>
            @endif
        </div>
    </form>
</div>

{{-- ============ KARTU STATISTIK (5 kartu, satu baris) ============ --}}
<div class="k-grid k-grid-5">
    @foreach($statistik as $item)
        <div class="k-card k-stat">
            <span class="k-icon k-icon-lg tone-{{ $item['tone'] }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">{!! $item['icon'] !!}</svg>
            </span>
            <b>{{ $item['nilai'] }}</b>
            <span class="k-stat-label">{{ $item['label'] }}</span>
            <span class="k-stat-sub">{{ $item['sub'] }}</span>
        </div>
    @endforeach
</div>

{{-- ============ TREN + DARURAT (satu baris) ============ --}}
<div class="k-grid k-grid-21">
    @php
        $puncak = max(collect($tren)->max('konseli'), collect($tren)->max('sesi'));
        $skala  = max(10, (int) ceil($puncak / 10) * 10);
    @endphp
    <div class="k-card k-panel">
        <div class="k-panel-head">
            <h3 class="k-panel-title k-title-ic">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
                Tren Bulanan
            </h3>
            <div class="k-legend">
                <span><i class="konseli"></i>Konseli</span>
                <span><i class="sesi"></i>Sesi</span>
            </div>
        </div>

        <div class="k-trend" style="--n: {{ max(1, count($tren)) }}" role="img" aria-label="Grafik jumlah konseli dan sesi per bulan">
            <div class="k-trend-y">
                @foreach([4, 3, 2, 1, 0] as $i)
                    <span>{{ rtrim(rtrim(number_format($skala / 4 * $i, 1, '.', ''), '0'), '.') }}</span>
                @endforeach
            </div>
            <div class="k-trend-plot">
                <div class="k-trend-grid"><i></i><i></i><i></i><i></i><i></i></div>
                <div class="k-trend-cols">
                    @foreach($tren as $t)
                        <div class="k-trend-col">
                            <div class="k-tb konseli" style="height: {{ $t['konseli'] / $skala * 100 }}%" title="Konseli {{ $t['bulan'] }}: {{ $t['konseli'] }}"><b>{{ $t['konseli'] }}</b></div>
                            <div class="k-tb sesi" style="height: {{ $t['sesi'] / $skala * 100 }}%" title="Sesi {{ $t['bulan'] }}: {{ $t['sesi'] }}"><b>{{ $t['sesi'] }}</b></div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="k-trend-months">
                @foreach($tren as $t)
                    <small>{{ $t['bulan'] }}</small>
                @endforeach
            </div>
        </div>
    </div>

    <div class="k-card k-panel k-urgent">
        <h3 class="k-panel-title k-title-ic">
            <svg viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
            Pengajuan Darurat
        </h3>
        <div class="k-urgent-grid">
            @foreach($darurat as $d)
                <div class="k-tile-stat">
                    <b style="color: {{ $d['warna'] }}">{{ $d['nilai'] }}</b>
                    <span>{{ $d['label'] }}</span>
                </div>
            @endforeach
        </div>
        <a href="{{ url('/koordinator/kasus') }}" class="k-btn-outline">Lihat Pengajuan Mendesak</a>
    </div>
</div>

{{-- ============ DISTRIBUSI (donut chart per kategori) ============ --}}
<div class="k-card k-panel">
    <div class="k-panel-head">
        <h3 class="k-panel-title k-title-ic">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
            Distribusi
        </h3>
        <form class="k-dist-form" method="GET" action="{{ route('koordinator.dashboard') }}">
            @if(($dariInput ?? '') !== '')<input type="hidden" name="dari" value="{{ $dariInput }}">@endif
            @if(($sampaiInput ?? '') !== '')<input type="hidden" name="sampai" value="{{ $sampaiInput }}">@endif
            <label for="distribusiMode" class="sr-only">Dasar distribusi</label>
            <select id="distribusiMode" name="distribusi" class="k-select" onchange="this.form.submit()">
                <option value="konseli" @selected(($distribusiMode ?? 'konseli') === 'konseli')>Per Konseli</option>
                <option value="pengajuan" @selected(($distribusiMode ?? 'konseli') === 'pengajuan')>Per Pengajuan</option>
            </select>
        </form>
    </div>

    <div class="k-donuts">
        @foreach($distribusi as $judul => $baris)
            @php
                $baris = collect($baris)->values();
                // Pie/donut kategori memakai seluruh kategori; legenda teks tetap memakai ringkasan "Lainnya".
                $chartBaris = $judul === 'Kategori Masalah' && isset($kategoriMasalahLengkap)
                    ? collect($kategoriMasalahLengkap)->values() : $baris;
                $total = $chartBaris->sum(fn ($r) => (float) $r[1]);
                $kum   = 0; // posisi awal segmen berikutnya (0–100)
                $ganda = $baris->sum(fn ($r) => (float) $r[2]) > 101; // satu konseli/pengajuan bisa punya >1 pilihan
            @endphp
            <div class="k-donut-card" data-satuan="{{ ($distribusiMode ?? 'konseli') === 'pengajuan' ? 'pengajuan' : 'konseli' }}">
                <h4>{{ $judul }}</h4>

                <div class="k-donut" role="img"
                     aria-label="Diagram donat {{ $judul }}: {{ $baris->map(fn ($r) => $r[0].' '.$r[2].'%')->implode(', ') }}">
                    <svg viewBox="0 0 42 42" aria-hidden="true">
                        <circle class="k-donut-track" cx="21" cy="21" r="16" pathLength="100"/>
                         @foreach($chartBaris as $i => $r)
                            @php
                                $p    = $total > 0 ? max(0, min(100 - $kum, (float) $r[1] / $total * 100)) : 0; // lebar busur dari jumlah, bukan dari kolom %
                                $dash = max($p - ($baris->count() > 1 ? 0.8 : 0), 0.1); // celah tipis antar segmen
                            @endphp
                            @if($p > 0)
                                <circle class="k-donut-seg c{{ $i % 7 + 1 }}" cx="21" cy="21" r="16" pathLength="100"
                                        stroke-dasharray="{{ round($dash, 2) }} {{ round(100 - $dash, 2) }}"
                                        stroke-dashoffset="{{ round(25 - $kum, 2) }}"
                                        data-i="{{ $i }}" data-label="{{ $r[0] }}" data-nilai="{{ $r[1] }}" data-pct="{{ $r[2] }}"></circle>
                            @endif
                             @php $kum += $p; @endphp
                        @endforeach
                    </svg>
                    <div class="k-donut-center">
                        <b>{{ (int) $total == $total ? (int) $total : $total }}</b>
                        <span>{{ $ganda ? 'pilihan' : 'total' }}</span>
                    </div>
                </div>

                <div class="k-donut-tip" role="tooltip" hidden></div>

                <ul class="k-donut-legend">
                    @foreach($baris as $i => $r)
                        <li data-i="{{ $i }}">
                            <i class="c{{ $i % 7 + 1 }}"></i>
                            <span class="k-dl">{{ $r[0] }}</span>
                            <b>{{ $r[1] }}</b>
                            <em>{{ $r[2] }}%</em>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
</div>

<script>
/* Tooltip donut: arahkan kursor (atau ketuk) pada segmen untuk melihat datanya */
(function () {
    document.querySelectorAll('.k-donut-card').forEach(function (card) {
        var tip = card.querySelector('.k-donut-tip');
        var judul = card.querySelector('h4').textContent;
        var satuan = card.dataset.satuan;
        var segs = card.querySelectorAll('.k-donut-seg');

        function tandai(i) {
            card.querySelectorAll('.k-donut-legend li').forEach(function (li) {
                li.classList.toggle('is-on', li.dataset.i === i);
            });
            segs.forEach(function (c) {
                c.classList.toggle('is-dim', i !== null && c.dataset.i !== i);
            });
        }
        function tampil(c, e) {
            tip.innerHTML = '';
            var a = document.createElement('small'); a.textContent = judul;
            var b = document.createElement('strong'); b.textContent = c.dataset.label;
            var d = document.createElement('span');
            d.textContent = c.dataset.nilai + ' ' + satuan + ' · ' + c.dataset.pct + '%';
            tip.append(a, b, d);
            tip.hidden = false;
            var r = card.getBoundingClientRect();
            var x = e.clientX - r.left, y = e.clientY - r.top;
            var w = tip.offsetWidth;
            tip.style.left = Math.max(8, Math.min(x - w / 2, r.width - w - 8)) + 'px';
            tip.style.top = (y - tip.offsetHeight - 12) + 'px';
            tandai(c.dataset.i);
        }
        function sembunyi() { tip.hidden = true; tandai(null); }

        segs.forEach(function (c) {
            c.addEventListener('pointerenter', function (e) { tampil(c, e); });
            c.addEventListener('pointermove', function (e) { tampil(c, e); });
            c.addEventListener('pointerleave', sembunyi);
            c.addEventListener('pointerdown', function (e) { tampil(c, e); });
        });
        card.querySelectorAll('.k-donut-legend li').forEach(function (li) {
            li.addEventListener('pointerenter', function () { tandai(li.dataset.i); });
            li.addEventListener('pointerleave', function () { tandai(null); });
        });
        document.addEventListener('pointerdown', function (e) { if (!card.contains(e.target)) sembunyi(); });
    });
})();
</script>

<script>
/* Filter tanggal: "Dari" tidak boleh melewati "Sampai" dan sebaliknya */
(function () {
    var f = document.querySelector('.k-filter');
    if (!f) return;
    var a = f.elements['dari'], c = f.elements['sampai'];
    function sync() { c.min = a.value || ''; a.max = c.value || ''; }
    [a, c].forEach(function (el) {
        el.addEventListener('click', function () { try { el.showPicker(); } catch (e) {} });
    });
    a.addEventListener('change', sync);
    c.addEventListener('change', sync);
    sync();
})();
</script>

{{-- ============ METRIK (baris 1: 2 kartu) ============ --}}
<div class="k-grid k-grid-2">
    <div class="k-card k-panel">
        <h3 class="k-panel-title k-title-ic">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Waktu Tunggu Rata-rata
        </h3>
        <div class="k-big" style="color: var(--k-sage-mid)">{{ $waktuTunggu['nilai'] }}</div>
        <p class="k-big-sub">{{ $waktuTunggu['ket'] }}</p>
        <dl class="k-kv">
            @foreach($waktuTunggu['rincian'] as $r)
                <div><dt>{{ $r[0] }}</dt><dd>{{ $r[1] }}</dd></div>
            @endforeach
        </dl>
    </div>

    <div class="k-card k-panel">
        <h3 class="k-panel-title k-title-ic">
            <svg viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 21a8 8 0 0 1 11.873-7"/><circle cx="10" cy="8" r="5"/><path d="m17 17 5 5"/><path d="m22 17-5 5"/></svg>
            Tingkat Tidak Hadir
        </h3>
        <div class="k-big" style="color: #F59E0B">{{ $tidakHadir['nilai'] }}</div>
        <p class="k-big-sub">{{ $tidakHadir['ket'] }}</p>
        <dl class="k-kv">
            @foreach($tidakHadir['rincian'] as $r)
                <div><dt>{{ $r[0] }}</dt><dd>{{ $r[1] }}</dd></div>
            @endforeach
        </dl>
    </div>
</div>

{{-- ============ TERTUNDA + BEBAN KONSELOR (baris 2, satu baris) ============ --}}
@php $maks = max(1, (int) collect($konselor)->max('aktif')); @endphp
<div class="k-grid k-grid-13">
    <div class="k-card k-panel">
        <h3 class="k-panel-title k-title-ic">
            <svg viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
            Pengajuan Tertunda
        </h3>
        <div class="k-big" style="color: #EF4444">{{ count($tiketMacet) }}</div>
        <p class="k-big-sub">pengajuan tertunda lebih dari 5 hari</p>
        <div class="k-stuck">
            @foreach($tiketMacet as $t)
                <div><strong>{{ $t['kode'] }}</strong><span>{{ $t['ket'] }}</span></div>
            @endforeach
        </div>
    </div>

    <div class="k-card k-panel">
    <h3 class="k-panel-title k-title-ic">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Beban per Konselor
    </h3>
    <div class="k-table-wrap">
        <table class="k-table">
            <thead>
                <tr>
                    <th>Konselor</th>
                    <th class="c">Pendampingan Aktif</th>
                    <th class="c">Sesi Selesai</th>
                    <th>Beban Relatif</th>
                </tr>
            </thead>
            <tbody>
                @foreach($konselor as $k)
                    @php
                        $inisial = collect(explode(' ', trim($k['nama'])))->filter()->take(2)
                            ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
                    @endphp
                    <tr>
                        <td class="nama" data-label="Konselor"><span class="k-who"><i aria-hidden="true">{{ $inisial }}</i>{{ $k['nama'] }}</span></td>
                        <td class="c aktif" data-label="Pendampingan Aktif">{{ $k['aktif'] }}</td>
                        <td class="c" data-label="Sesi Selesai">{{ $k['selesai'] }}</td>
                        <td data-label="Beban Relatif">
                            <div class="k-load">
                                <span class="k-track"><i class="{{ $k['aktif'] >= $maks ? 'bad' : '' }}" style="width: {{ round($k['aktif'] / $maks * 100) }}%"></i></span>
                                <em>{{ $k['aktif'] }}</em>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</div>
</div>
@endsection
