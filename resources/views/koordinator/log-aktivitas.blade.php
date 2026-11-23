{{--
    SILAKON — Log Aktivitas (Koordinator)
    Simpan sebagai: resources/views/koordinator/log-aktivitas.blade.php
    Variabel dari KoordinatorController@log:
    $logs (data halaman ini), $filter, $opsiAksi, $total, $halaman, $totalHalaman,
    $perHalaman, $awal, $akhir
--}}
@extends('layouts.koordinator')

@section('judul', 'Log Aktivitas')
@section('subjudul', now()->translatedFormat('l, d F Y'))

@section('content')

@php
    // ---- nilai aman (fallback bila controller belum mengirim) ----
    $opsiPerHalaman = [10, 25, 50];
    $perHalaman     = (int) ($perHalaman ?? request('per_halaman', 10));
    if (!in_array($perHalaman, $opsiPerHalaman, true)) $perHalaman = 10;

    $halaman      = max(1, (int) ($halaman ?? request('halaman', 1)));
    $totalHalaman = max(1, (int) ($totalHalaman ?? ceil(($total ?? 0) / $perHalaman)));
    $halaman      = min($halaman, $totalHalaman);

    // query dasar: filter + per_halaman (tanpa nilai kosong biar URL bersih)
    $queryDasar = array_filter(
        array_merge($filter, ['per_halaman' => $perHalaman]),
        fn($v) => $v !== null && $v !== ''
    );
    $baseUrl = url('/koordinator/log-aktivitas');
    $urlHal  = fn($p) => $baseUrl . '?' . http_build_query(array_merge($queryDasar, ['halaman' => $p]));

    // jendela nomor halaman: 1 … 4 5 [6] 7 8 … 20
    $jendela = 2;
    $mulai   = max(1, $halaman - $jendela);
    $selesai = min($totalHalaman, $halaman + $jendela);
@endphp

@if(session('sukses'))
    <div class="k-alert-sukses">{{ session('sukses') }}</div>
@endif

{{-- ============ HEAD ============ --}}
<div class="k-head-row">
    <div class="k-page-head">
        <h2>Log Aktivitas</h2>
        <p>Rekam jejak tindakan sensitif dalam sistem</p>
    </div>
    <a href="{{ url('/koordinator/log-aktivitas/ekspor') . '?' . http_build_query(array_merge(array_filter($filter, fn($v) => $v !== null && $v !== ''), ['jenis'=>'excel'])) }}" class="k-btn k-btn-outline-solid">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M12 18v-6"/><path d="m9 15 3 3 3-3"/></svg>
        Ekspor Log
    </a>
    <a href="{{ url('/koordinator/log-aktivitas/ekspor') . '?' . http_build_query(array_merge(array_filter($filter, fn($v) => $v !== null && $v !== ''), ['jenis'=>'pdf'])) }}" class="k-btn k-btn-outline-solid">PDF</a>
</div>

{{-- ============ FILTER ============ --}}
<form action="{{ $baseUrl }}" method="GET" class="k-card k-log-filter">
    <input type="hidden" name="per_halaman" value="{{ $perHalaman }}">
    <div class="k-field-inline">
        <label for="f_aksi">Aksi</label>
        <select id="f_aksi" name="aksi" class="k-select k-select-full">
            <option value="">Semua Aksi</option>
            @foreach($opsiAksi as $opsi)
                <option value="{{ $opsi }}" @selected($filter['aksi'] === $opsi)>{{ $opsi }}</option>
            @endforeach
        </select>
    </div>
    <div class="k-field-inline">
        <label for="f_pengguna">Pengguna</label>
        <input type="text" id="f_pengguna" name="pengguna" class="k-input" placeholder="Cari nama pengguna..." value="{{ $filter['pengguna'] }}">
    </div>
    <div class="k-field-inline">
        <label for="f_dari">Dari</label>
        <input type="date" id="f_dari" name="dari" class="k-input" value="{{ $filter['dari'] }}">
    </div>
    <div class="k-field-inline">
        <label for="f_sampai">Sampai</label>
        <input type="date" id="f_sampai" name="sampai" class="k-input" value="{{ $filter['sampai'] }}">
    </div>
    <div class="k-field-inline k-field-actions">
        <button type="submit" class="k-btn">Terapkan</button>
        @if($filter['aksi'] || $filter['pengguna'] || $filter['dari'] || $filter['sampai'])
            <a href="{{ $baseUrl . '?per_halaman=' . $perHalaman }}" class="k-link-reset">Reset</a>
        @endif
    </div>
</form>

{{-- ============ TABEL ============ --}}
<div class="k-card k-log-card">
    <div class="k-table-wrap k-log-scroll">
        <table class="k-table k-log-table">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Pengguna</th>
                    <th>Role</th>
                    <th>Aksi</th>
                    <th>Detail</th>
                    <th class="r">IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    @php
                        $tgl = \Illuminate\Support\Carbon::parse($log['waktu']);
                        $inisial = collect(explode(' ', preg_replace('/[.,]/', '', $log['pengguna'])))
                            ->filter(fn($p) => $p !== '' && !in_array(strtolower($p), ['dr','drs','dra','m','psi','pd','si','kons']))
                            ->map(fn($p) => mb_substr($p, 0, 1))
                            ->take(2)->implode('');
                        $roleTone = match($log['role']) {
                            'Koordinator' => 'green',
                            'Admin'       => 'navy',
                            'Konselor'    => 'blue',
                            default       => 'grey',
                        };
                    @endphp
                    <tr>
                        <td class="k-log-waktu">
                            <span class="k-log-tgl">{{ $tgl->translatedFormat('d M Y') }}</span>
                            <span class="k-log-jam">{{ $tgl->format('H.i.s') }}</span>
                        </td>
                        <td>
                            <div class="k-user-cell">
                                <span class="k-avatar tone-{{ $roleTone }}">{{ strtoupper($inisial) }}</span>
                                <span class="k-user-nama">{{ $log['pengguna'] }}</span>
                            </div>
                        </td>
                        <td><span class="k-role-text">{{ $log['role'] }}</span></td>
                        <td>
                            <span class="k-badge" style="background:{{ $log['badge']['bg'] }}; color:{{ $log['badge']['warna'] }}">
                                <i style="background:{{ $log['badge']['warna'] }}"></i>{{ $log['aksi'] }}
                            </span>
                        </td>
                        <td class="k-log-detail" title="{{ $log['detail'] }}">{{ $log['detail'] }}</td>
                        <td class="r"><code class="k-ip">{{ $log['ip'] }}</code></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="k-log-kosong">Tidak ada aktivitas yang cocok dengan filter ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="k-log-footer">
        <div class="k-log-footer-kiri">
            <span class="k-log-info">
                @if($total > 0)
                    Menampilkan <b>{{ $awal }}–{{ $akhir }}</b> dari <b>{{ $total }}</b> entri
                @else
                    Tidak ada entri
                @endif
            </span>

            {{-- pilih jumlah data per halaman --}}
            <form action="{{ $baseUrl }}" method="GET" class="k-perpage">
                @foreach(array_filter($filter, fn($v) => $v !== null && $v !== '') as $k => $v)
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endforeach
                <label for="per_halaman">Tampilkan</label>
                <select id="per_halaman" name="per_halaman" class="k-select-mini" onchange="this.form.submit()">
                    @foreach($opsiPerHalaman as $n)
                        <option value="{{ $n }}" @selected($perHalaman === $n)>{{ $n }}</option>
                    @endforeach
                </select>
                <span>data</span>
            </form>
        </div>

        @if($totalHalaman > 1)
            <nav class="k-pagination" aria-label="Navigasi halaman">
                <a class="k-page-nav {{ $halaman <= 1 ? 'disabled' : '' }}"
                   href="{{ $halaman <= 1 ? '#' : $urlHal($halaman - 1) }}" aria-label="Sebelumnya">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                </a>

                @if($mulai > 1)
                    <a class="k-page-num" href="{{ $urlHal(1) }}">1</a>
                    @if($mulai > 2)<span class="k-page-dots">…</span>@endif
                @endif

                @for($p = $mulai; $p <= $selesai; $p++)
                    <a class="k-page-num {{ $p === $halaman ? 'active' : '' }}" href="{{ $urlHal($p) }}">{{ $p }}</a>
                @endfor

                @if($selesai < $totalHalaman)
                    @if($selesai < $totalHalaman - 1)<span class="k-page-dots">…</span>@endif
                    <a class="k-page-num" href="{{ $urlHal($totalHalaman) }}">{{ $totalHalaman }}</a>
                @endif

                <a class="k-page-nav {{ $halaman >= $totalHalaman ? 'disabled' : '' }}"
                   href="{{ $halaman >= $totalHalaman ? '#' : $urlHal($halaman + 1) }}" aria-label="Berikutnya">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </a>
            </nav>
        @endif
    </div>
</div>

@endsection

@push('styles')
<style>
    .k-alert-sukses {
        background: #EAF3E6; color: #3F5A33; border: 1px solid #C4DCB8;
        padding: 12px 16px; border-radius: 10px; font-size: 14px; margin-bottom: 18px;
    }

    .k-btn-outline-solid {
        display: inline-flex; align-items: center; gap: 8px;
        background: #fff; color: #3F5A33; border: 1px solid #C4DCB8;
        padding: 9px 14px; border-radius: 10px; font-size: 13.5px; font-weight: 600;
        text-decoration: none; transition: background .15s ease;
    }
    .k-btn-outline-solid:hover { background: #F3F8F1; }
    .k-btn-outline-solid svg { width: 16px; height: 16px; }

    /* ---- filter bar (diringkas) ---- */
    .k-log-filter {
        display: grid; grid-template-columns: 1fr 1.6fr 1fr 1fr auto;
        gap: 12px; align-items: end; padding: 14px 18px; margin-bottom: 14px;
    }
    .k-field-inline label { display: block; font-size: 12.5px; font-weight: 600; color: #1B2432; margin-bottom: 5px; }
    .k-field-inline .k-input, .k-field-inline .k-select-full {
        width: 100%; box-sizing: border-box; border: 1px solid #E2E4E9; border-radius: 8px;
        padding: 7px 10px; font-size: 13.5px; font-family: inherit; color: #1B2432; background: #fff;
    }
    .k-field-inline .k-input:focus, .k-field-inline .k-select-full:focus {
        outline: none; border-color: #6E8F60; box-shadow: 0 0 0 3px rgba(110,143,96,.15);
    }
    .k-field-actions { display: flex; align-items: center; gap: 12px; }
    .k-link-reset { font-size: 13px; color: #8A8F98; text-decoration: underline; white-space: nowrap; }
    .k-link-reset:hover { color: #4B5160; }

    /* ---- table card ---- */
    .k-log-card { padding: 0; overflow: hidden; }
    .k-log-scroll { max-height: 68vh; overflow: auto; }
    .k-log-table { width: 100%; border-collapse: collapse; }
    .k-log-table thead th {
        position: sticky; top: 0; z-index: 1;
        text-align: left; font-size: 11px; font-weight: 700; letter-spacing: .04em;
        text-transform: uppercase; color: #9AA0A8; background: #FAFAF8;
        padding: 9px 14px; border-bottom: 1px solid #EDEEEA; white-space: nowrap;
    }
    .k-log-table thead th.r { text-align: right; }
    .k-log-table tbody td {
        padding: 7px 14px; font-size: 13px; line-height: 1.35; color: #1B2432;
        border-bottom: 1px solid #F1F2EE; vertical-align: middle;
    }
    .k-log-table tbody td.r { text-align: right; }
    .k-log-table tbody tr:last-child td { border-bottom: none; }
    .k-log-table tbody tr:hover { background: #FAFBF8; }

    /* waktu: satu baris */
    .k-log-waktu { white-space: nowrap; }
    .k-log-tgl { font-weight: 600; color: #1B2432; }
    .k-log-jam { margin-left: 6px; font-size: 12px; color: #9AA0A8; font-variant-numeric: tabular-nums; }

    .k-user-cell { display: flex; align-items: center; gap: 8px; white-space: nowrap; }
    .k-avatar {
        flex: 0 0 auto; width: 24px; height: 24px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 10px; font-weight: 700; color: #fff;
    }
    .k-avatar.tone-green { background: #6E8F60; }
    .k-avatar.tone-navy  { background: #4A5568; }
    .k-avatar.tone-blue  { background: #3B6FD6; }
    .k-avatar.tone-grey  { background: #9AA0A8; }
    .k-user-nama { font-weight: 600; }

    .k-role-text { color: #6B7280; white-space: nowrap; }

    .k-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 2px 9px; border-radius: 999px; font-size: 11.5px; font-weight: 700; white-space: nowrap;
    }
    .k-badge i { width: 5px; height: 5px; border-radius: 50%; display: inline-block; }

    /* detail: satu baris, dipotong dengan ellipsis (lengkap saat di-hover) */
    .k-log-detail {
        color: #4B5160; max-width: 340px;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }

    .k-ip {
        font-family: "SFMono-Regular", Consolas, Menlo, monospace; font-size: 11.5px;
        color: #9AA0A8; background: #F5F6F3; padding: 2px 6px; border-radius: 5px;
    }

    .k-log-kosong { text-align: center; color: #9AA0A8; padding: 32px 20px !important; }

    /* ---- footer / pagination ---- */
    .k-log-footer {
        display: flex; align-items: center; justify-content: space-between;
        padding: 10px 16px; border-top: 1px solid #EDEEEA; flex-wrap: wrap; gap: 10px;
        background: #fff;
    }
    .k-log-footer-kiri { display: flex; align-items: center; gap: 18px; flex-wrap: wrap; }
    .k-log-info { font-size: 13px; color: #6B7280; }
    .k-log-info b { color: #1B2432; }

    .k-perpage { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #6B7280; margin: 0; }
    .k-perpage label { margin: 0; font-weight: 400; }
    .k-select-mini {
        border: 1px solid #E2E4E9; border-radius: 8px; padding: 4px 8px;
        font-size: 13px; font-family: inherit; color: #1B2432; background: #fff; cursor: pointer;
    }
    .k-select-mini:focus { outline: none; border-color: #6E8F60; box-shadow: 0 0 0 3px rgba(110,143,96,.15); }

    .k-pagination { display: flex; align-items: center; gap: 4px; }
    .k-page-num, .k-page-nav {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 30px; height: 30px; padding: 0 4px; border-radius: 8px;
        font-size: 13px; font-weight: 600; color: #4B5160; text-decoration: none;
        border: 1px solid transparent; transition: background .15s ease, border-color .15s ease;
    }
    .k-page-num:hover { background: #F3F8F1; }
    .k-page-num.active { background: #6E8F60; color: #fff; pointer-events: none; }
    .k-page-dots { padding: 0 4px; color: #9AA0A8; font-size: 13px; }
    .k-page-nav { border-color: #E2E4E9; color: #6B7280; }
    .k-page-nav:hover { background: #F3F8F1; }
    .k-page-nav svg { width: 14px; height: 14px; }
    .k-page-nav.disabled { opacity: .4; pointer-events: none; }

    @media (max-width: 900px) {
        .k-log-filter { grid-template-columns: 1fr 1fr; }
        .k-field-actions { grid-column: 1 / -1; justify-content: flex-start; }
    }
</style>
@endpush
