{{--
    SILAKON — Jadwal Konseling (Penjadwalan)
    Simpan sebagai: resources/views/admin/penjadwalan.blade.php

    Data ($antrianLanjutan, $jadwal, $bulanTampil) dikirim dari controller,
    tiap $item punya field: konseli, konselor, kategori, sesi/tanggal_terakhir
    (antrian) atau id/status/tanggal/tanggal_iso/waktu/lokasi (jadwal utama).

    DIRAPIKAN mengikuti pola pendaftaran.blade.php:
    - Header dulu pakai <div class="schedule-toolbar"> custom sendiri (beda
      struktur & warna dari halaman lain) → sekarang pakai admin-card-head +
      admin-header-actions, sama persis pola yang dipakai pendaftaran.blade.php.
    - Palet warna dulu hijau #7ca486 + teks #263238 (beda dari admin lain yang
      pakai #3F6D5D / #1F2A24) → disamakan ke token warna pendaftaran.blade.php.
    - Pill kategori/status dulu warna custom sendiri → sekarang pakai admin-pill
      + tone-menunggu/diverifikasi/terjadwal/ditolak, class yang sama persis
      dengan yang dipakai pendaftaran.blade.php, supaya makna warna konsisten
      di seluruh dashboard admin.
    - Tiga <div class="schedule-card"> terpisah (masing-masing punya
      shadow+border sendiri, numpuk jadi berantakan) → sekarang jadi satu
      admin-card (sama seperti halaman lain), dengan section di dalamnya
      dipisah border-top tipis, meniru pola admin-filter-bar di pendaftaran.

    BUG YANG DIPERBAIKI:
    - Baris tabel jadwal utama sebelumnya SELALU diberi data-status="Terjadwal"
      hardcode di HTML, padahal teks pill-nya menampilkan {{ $item['status'] }}
      apa adanya. Akibatnya kalau data dari server ada yang berstatus
      "Dibatalkan"/"Tidak Hadir", baris itu tetap dianggap "Terjadwal" oleh JS
      (termasuk tetap muncul di kalender, padahal getCalendarEvents() harusnya
      menyembunyikan yang "Dibatalkan"). Sekarang data-status dan warna pill
      mengikuti $item['status'] yang sebenarnya.
    - Tanggal "hari ini" & bulan kalender dulu hardcode ke 23/Agustus 2025
      (statis, makin lama makin salah). Sekarang dihitung dari tanggal
      perangkat saat halaman dibuka, begitu juga nilai default tanggal di
      form "Buat Jadwal".
    - Saat aksi "Batalkan"/"Tidak Hadir" dulu mengubah warna pill lewat
      inline style manual (gampang tidak sinkron kalau CSS tone berubah) →
      sekarang cukup ganti class tone-nya, sama seperti pola pill di halaman
      lain.

    DATA TAMBAHAN untuk modal jadwal (dikirim dari controller):
    - $konseliTerverifikasi : daftar pendaftaran berstatus "Diverifikasi" yang belum
      dijadwalkan. Tiap item:
      ['id' => 1, 'nama' => 'Andi Setiawan', 'nim' => '2101...', 'prodi' => 'Psikologi',
       'kategori' => 'Akademik', 'tanggal_verifikasi' => '10 Agu 2025',
       'hari' => 'Senin', 'jam' => '09.00 - 11.00', 'mode' => 'Offline' /* atau 'Online' */]
    - $daftarKonselor       : (opsional) array nama konselor. Kalau tidak dikirim,
      diambil dari nama konselor yang sudah ada di $jadwal & $antrianLanjutan.

    DATA KETERSEDIAAN KONSELOR: $ketersediaanKonselor, tiap item
    ['id', 'konselor', 'tanggal' (Y-m-d), 'mulai' (HH:MM), 'selesai' (HH:MM), 'catatan'].

    Style ikut pola dashboard.blade.php (admin-card, admin-btn, admin-pill,
    admin-table). Class tambahan khusus halaman ini ada di @push('styles').
--}}
@extends('layouts.admin')

@section('judul', 'Jadwal Konseling')

@push('styles')
<style>
    /* ===== Header aksi (sama persis pola pendaftaran.blade.php) ===== */
    .admin-header-actions{
        display:flex;
        align-items:center;
        gap:12px;
        flex-wrap:wrap;
    }

    .view-switch{
        display:flex;
        background:#F1F3F1;
        border-radius:12px;
        padding:3px;
        box-shadow:inset 0 0 0 1px #EEF1ED;
    }
    .view-btn{
        height:38px;
        padding:0 16px;
        border:0;
        border-radius:9px;
        background:transparent;
        color:#6B756E;
        font-family:inherit; font-weight:600; font-size:13px;
        cursor:pointer;
        display:inline-flex;
        align-items:center;
        gap:7px;
        transition:background .15s ease, color .15s ease;
    }
    .view-btn svg{ width:15px; height:15px; }
    .view-btn.active{
        background:#fff;
        color:#1F2A24;
        box-shadow:0 2px 7px rgba(31,42,36,.08);
    }

    .admin-btn{ display:inline-flex; align-items:center; gap:8px; }
    .admin-btn svg{ width:15px; height:15px; }

    /* ===== Tone pill — sama persis dgn pendaftaran.blade.php ===== */
    .admin-pill.tone-menunggu     { background:#FEF3C7; color:#92400E; }
    .admin-pill.tone-diverifikasi { background:#DBEAFE; color:#1E40AF; }
    .admin-pill.tone-terjadwal    { background:#CCFBF1; color:#0F766E; }
    .admin-pill.tone-ditolak      { background:#FEE2E2; color:#991B1B; }

    /* ===== Section di dalam satu admin-card ===== */
    .schedule-section{ padding:26px 0 10px; }
    .schedule-section + .schedule-section{
        margin-top:14px;
        border-top:1px solid #EEF1ED;
    }
    .schedule-section-head{
        display:flex;
        align-items:center;
        gap:10px;
        padding:0 24px;
        margin-bottom:16px;
    }
    .schedule-section-head h3{
        margin:0;
        font-size:15px;
        font-weight:700;
        color:#1F2A24;
    }
    /* Antrian dan ketersediaan ditampilkan sebagai bagian dari penjadwalan. */

    /* ===== Tabel jadwal (lebar kolom saja, tampilan dasar ikut admin-table) ===== */
    .admin-table-wrap{ overflow-x:auto; }
    .schedule-table{ width:100%; min-width:860px; }
    .queue-table th:nth-child(1){width:24%}.queue-table th:nth-child(2){width:26%}.queue-table th:nth-child(3){width:14%}.queue-table th:nth-child(4){width:20%}.queue-table th:nth-child(5){width:16%}
    .main-table th:nth-child(1){width:18%}.main-table th:nth-child(2){width:18%}.main-table th:nth-child(3){width:15%}.main-table th:nth-child(4){width:10%}.main-table th:nth-child(5){width:10%}.main-table th:nth-child(6){width:14%}.main-table th:nth-child(7){width:15%}
    .admin-table-wrap .schedule-table tbody tr{ transition:background .12s ease; }
    .admin-table-wrap .schedule-table tbody tr:hover{ background:#F7F9F7; }

    .person-name{ font-weight:700; color:#1F2A24; }
    .counselor{ color:#4B554E; }
    .session-text{ color:#3F6D5D; font-weight:600; }
    .date-text{ white-space:nowrap; color:#4B554E; }
    .time-text{ color:#3F6D5D; font-weight:700; line-height:1.3; }

    .action-cell{ display:flex; gap:6px; align-items:center; flex-wrap:nowrap; }
    .action-btn{
        width:34px;
        height:34px;
        padding:0;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        border-radius:10px;
        background:#fff;
        cursor:pointer;
        transition:background .15s ease, border-color .15s ease, transform .1s ease;
    }
    .action-btn svg{ width:16px; height:16px; }
    .action-btn:active{ transform:scale(.94); }
    .action-reschedule{ border:1px solid #CFE0D6; color:#3F6D5D; }
    .action-reschedule:hover{ background:#EAF2ED; border-color:#3F6D5D; }
    .action-cancel{ border:1px solid #F3B7B7; color:#B42318; }
    .action-cancel:hover{ background:#FEE2E2; }
    .action-absent{ border:1px solid #F3D48C; color:#92400E; }
    .action-absent:hover{ background:#FEF3C7; }

    .queue-schedule-btn{
        border:0;
        background:#3F6D5D;
        color:#fff;
        border-radius:999px;
        height:30px;
        padding:0 16px;
        font-family:inherit; font-weight:600; font-size:12px;
        cursor:pointer;
        transition:background .15s ease;
    }
    .queue-schedule-btn:hover{ background:#345C4E; }

    /* ===== Kalender ===== */
    .calendar-section{ display:none; padding:26px 24px 28px; overflow-x:auto; }
    .calendar-section.active{ display:block; }
    .list-view{ display:none; }
    .list-view.active{ display:block; }
    .calendar-top{ display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:18px; min-width:640px; }
    .calendar-title h3{ margin:0; font-size:20px; font-weight:800; color:#1F2A24; letter-spacing:-.01em; }
    .calendar-title p{ margin:3px 0 0; font-size:12px; color:#8C948D; }
    .calendar-nav{ display:flex; align-items:center; gap:8px; }
    .calendar-nav button{
        height:36px;
        border:1px solid #E2E5E1;
        background:#fff;
        border-radius:10px;
        color:#4B554E;
        cursor:pointer;
        font-family:inherit; font-weight:600; font-size:13px;
        transition:border-color .15s ease, color .15s ease, background .15s ease;
    }
    .calendar-nav .nav-arrow{ width:36px; font-size:18px; line-height:1; }
    .calendar-nav .nav-today{ padding:0 14px; }
    .calendar-nav button:hover{ border-color:#3F6D5D; color:#3F6D5D; background:#F7F9F7; }

    .calendar-grid{
        display:grid;
        grid-template-columns:repeat(7,minmax(0,1fr));
        gap:1px;
        min-width:640px;
        background:#E8ECE8;
        border:1px solid #E8ECE8;
        border-radius:14px;
        overflow:hidden;
    }
    .calendar-weekday{
        height:38px;
        display:flex; align-items:center; justify-content:center;
        background:#F7F9F7;
        color:#6B756E;
        font-size:11px;
        font-weight:700;
        letter-spacing:.06em;
        text-transform:uppercase;
    }
    .calendar-weekday.weekend{ color:#B4574D; }
    .calendar-day{
        min-height:118px;
        padding:8px;
        background:#fff;
        display:flex;
        flex-direction:column;
        gap:4px;
        min-width:0;
        transition:background .12s ease;
    }
    .calendar-day:hover{ background:#FBFCFB; }
    .calendar-day.muted{ background:#FAFBFA; }
    .calendar-day.muted .day-number{ color:#B7BDB8; }
    .calendar-day.today{ background:#F3F8F5; }
    .day-number{
        width:26px; height:26px;
        display:flex; align-items:center; justify-content:center;
        border-radius:50%;
        font-size:12px;
        font-weight:700;
        color:#4B554E;
        margin-bottom:2px;
    }
    .calendar-day.today .day-number{ background:#3F6D5D; color:#fff; }

    .calendar-event{
        padding:5px 8px;
        border-radius:8px;
        background:#EAF2ED;
        border-left:3px solid #3F6D5D;
        color:#2C4A3E;
        font-size:11px;
        line-height:1.3;
        min-width:0;
    }
    .calendar-event strong,
    .calendar-event span{ display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .calendar-event strong{ font-weight:700; }
    .calendar-event span{ font-size:10px; opacity:.8; }
    .calendar-event.absent{ opacity:.55; }
    .calendar-event.cat-kesehatan-mental{ background:#DBEAFE; border-color:#3B82F6; color:#1E40AF; }
    .calendar-event.cat-akademik{ background:#EDE9FE; border-color:#8B5CF6; color:#5B21B6; }
    .calendar-event.cat-karier{ background:#FEF3C7; border-color:#F59E0B; color:#92400E; }
    .calendar-event.cat-pribadi{ background:#FCE7F3; border-color:#EC4899; color:#9D174D; }
    .calendar-event.cat-sosial{ background:#CCFBF1; border-color:#14B8A6; color:#0F766E; }
    .calendar-event.cat-disabilitas{ background:#FFEDD5; border-color:#F97316; color:#9A3412; }
    .calendar-more{ font-size:10.5px; font-weight:700; color:#6B756E; padding:1px 4px; }

    /* ===== Modal ===== */
    .modal-backdrop{
        position:fixed; inset:0;
        background:rgba(31,42,36,.42);
        display:none; align-items:center; justify-content:center;
        padding:20px;
        z-index:9999;
    }
    .modal-backdrop.show{ display:flex; }
    .schedule-modal{
        width:min(720px,100%);
        max-height:90vh;
        overflow:auto;
        background:#fff;
        border-radius:18px;
        box-shadow:0 22px 65px rgba(25,35,30,.2);
        animation:modalIn .16s ease;
    }
    @keyframes modalIn{ from{opacity:0; transform:translateY(7px) scale(.99);} to{opacity:1; transform:none;} }
    .modal-head{
        display:flex; align-items:flex-start; justify-content:space-between;
        padding:22px 24px 16px;
        border-bottom:1px solid #EEF1ED;
    }
    .modal-head h3{ margin:0; color:#1F2A24; font-size:18px; }
    .modal-head p{ margin:5px 0 0; color:#8C948D; font-size:12px; }
    .modal-close{
        width:32px; height:32px;
        border:0; border-radius:9px;
        background:#F7F9F7;
        color:#4B554E;
        cursor:pointer;
        font-size:20px;
        line-height:1;
    }
    .modal-body{ padding:20px 24px; }
    .form-grid{ display:grid; grid-template-columns:1fr 1fr; gap:15px; }
    .form-group{ display:flex; flex-direction:column; gap:7px; }
    .form-group.full{ grid-column:1/-1; }
    .form-group label{ font-size:12px; font-weight:700; color:#4B554E; }
    .form-control{
        height:41px;
        border:1px solid #E2E5E1;
        border-radius:10px;
        padding:0 12px;
        background:#fff;
        color:#1F2A24;
        font-family:inherit;
        font-size:13px;
        line-height:1.4;
        outline:none;
        transition:border-color .15s ease, box-shadow .15s ease;
    }
    /* kolom tanggal & jam: isi bawaan browser ikut font yang sama */
    input.form-control[type="date"],
    input.form-control[type="time"]{ font-family:inherit; font-size:13px; }
    .form-control::-webkit-datetime-edit,
    .form-control::-webkit-datetime-edit-fields-wrapper,
    .form-control::-webkit-datetime-edit-text,
    .form-control::-webkit-datetime-edit-month-field,
    .form-control::-webkit-datetime-edit-day-field,
    .form-control::-webkit-datetime-edit-year-field,
    .form-control::-webkit-datetime-edit-hour-field,
    .form-control::-webkit-datetime-edit-minute-field,
    .form-control::-webkit-datetime-edit-ampm-field{ font-family:inherit; font-size:13px; }
    .form-control::placeholder{ font-family:inherit; color:#9AA29B; }
    .form-control:focus{ border-color:#3F6D5D; box-shadow:0 0 0 3px rgba(63,109,93,.12); }
    [hidden]{ display:none !important; }

    /* --- Langkah 1: daftar konseli terverifikasi --- */
    .pick-toolbar{ display:flex; align-items:center; gap:12px; padding:16px 24px 12px; }
    .pick-search{ position:relative; flex:1; }
    .pick-search svg{ position:absolute; left:12px; top:50%; transform:translateY(-50%); width:16px; height:16px; color:#8C948D; pointer-events:none; }
    .pick-search input{ width:100%; padding-left:36px; }
    .pick-count{ font-size:12px; color:#8C948D; white-space:nowrap; }
    .pick-list{ padding:0 24px 22px; display:flex; flex-direction:column; gap:10px; max-height:min(58vh,520px); overflow:auto; }
    .pick-item{
        width:100%; text-align:left; font:inherit;
        display:flex; flex-direction:column; gap:10px;
        padding:14px 16px;
        border:1px solid #E8ECE8; border-radius:14px; background:#fff;
        cursor:pointer;
        transition:border-color .15s ease, background .15s ease, box-shadow .15s ease;
    }
    .pick-item:hover{ border-color:#3F6D5D; background:#F7FAF8; box-shadow:0 4px 14px rgba(63,109,93,.08); }
    .pick-item:focus-visible{ outline:none; border-color:#3F6D5D; box-shadow:0 0 0 3px rgba(63,109,93,.18); }
    .pick-row{ display:flex; align-items:flex-start; justify-content:space-between; gap:12px; }
    .pick-name{ font-weight:700; font-size:14px; color:#1F2A24; }
    .pick-sub{ margin-top:2px; font-size:12px; color:#6B756E; }
    .pick-meta{ display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; }
    .pref-chips{ display:flex; align-items:center; gap:6px; flex-wrap:wrap; }
    .pick-label{ font-size:11px; font-weight:700; color:#8C948D; text-transform:uppercase; letter-spacing:.05em; margin-right:2px; }
    .chip{ display:inline-flex; align-items:center; gap:5px; height:24px; padding:0 9px; border-radius:999px; background:#F1F3F1; color:#4B554E; font-size:11.5px; font-weight:600; }
    .chip svg{ width:12px; height:12px; }
    .chip.mode-offline{ background:#EAF2ED; color:#2C4A3E; }
    .chip.mode-online{ background:#DBEAFE; color:#1E40AF; }
    .pick-verif{ font-size:11.5px; color:#8C948D; }
    .pick-empty{ margin:0 24px 24px; }

    /* --- Langkah 2: ringkasan konseli terpilih --- */
    .selected-card{ margin-bottom:18px; padding:14px 16px; border:1px solid #CFE0D6; background:#F3F8F5; border-radius:14px; display:flex; flex-direction:column; gap:10px; }
    .selected-top{ display:flex; align-items:flex-start; justify-content:space-between; gap:12px; }
    .selected-side{ display:flex; align-items:center; gap:8px; }
    .link-btn{ border:0; background:none; color:#3F6D5D; font-family:inherit; font-weight:600; font-size:12px; cursor:pointer; padding:5px 8px; border-radius:7px; }
    .link-btn:hover{ background:#E3EFE8; }

    /* --- Dropdown konselor yang bisa dicari --- */
    .combo{ position:relative; }
    .combo .form-control{ width:100%; padding-right:36px; }
    .combo-caret{ position:absolute; right:12px; top:50%; transform:translateY(-50%); width:16px; height:16px; color:#8C948D; pointer-events:none; }
    .combo-list{
        position:absolute; z-index:5; left:0; right:0; top:calc(100% + 6px);
        margin:0; padding:6px; list-style:none;
        background:#fff; border:1px solid #E2E5E1; border-radius:12px;
        box-shadow:0 12px 30px rgba(31,42,36,.14);
        max-height:210px; overflow:auto;
    }
    .combo-list li{ padding:9px 10px; border-radius:8px; font-size:13px; color:#1F2A24; cursor:pointer; }
    .combo-list li:hover, .combo-list li.active{ background:#EAF2ED; }
    .combo-list li.selected{ font-weight:700; color:#3F6D5D; }
    .combo-list li.empty{ color:#8C948D; cursor:default; }
    .combo-list li.empty:hover{ background:none; }

    .form-control[readonly],
    .form-control:disabled{ background:#F7F9F7; color:#4B554E; cursor:not-allowed; }
    select.form-control{ cursor:pointer; }
    .form-hint{ font-size:11.5px; color:#8C948D; margin-top:-2px; }
    .form-hint.warn{ color:#92400E; }
    textarea.form-control{ height:78px; padding:11px; resize:vertical; }
    .modal-foot{ display:flex; justify-content:flex-end; gap:9px; padding:15px 24px 22px; }
    .btn-secondary{
        height:40px; padding:0 16px;
        border:1px solid #E2E5E1;
        border-radius:10px;
        background:#fff;
        color:#4B554E;
        font-family:inherit; font-weight:600; font-size:12px;
        cursor:pointer;
        transition:border-color .15s ease;
    }
    .btn-secondary:hover{ border-color:#3F6D5D; }
    .btn-primary{
        height:40px; padding:0 18px;
        border:0; border-radius:10px;
        background:#3F6D5D;
        color:#fff;
        font-family:inherit; font-weight:600; font-size:12px;
        cursor:pointer;
        transition:background .15s ease;
    }
    .btn-primary:hover{ background:#345C4E; }

    /* ===== Toast ===== */
    .schedule-toast{
        position:fixed; right:22px; bottom:22px;
        z-index:10000;
        background:#1F2A24;
        color:#fff;
        border-radius:11px;
        padding:12px 16px;
        font-size:12px;
        box-shadow:0 10px 30px rgba(0,0,0,.15);
        opacity:0;
        transform:translateY(8px);
        pointer-events:none;
        transition:.2s;
    }
    .schedule-toast.show{ opacity:1; transform:none; }

    /* ===== Ketersediaan Konselor (panel kanan) ===== */
    .list-split{ display:grid; grid-template-columns:minmax(0,1fr) 270px; align-items:start; }
    .list-main{ min-width:0; }
    .list-main .schedule-table{ min-width:0; table-layout:fixed; }
    .list-main .schedule-table th,
    .list-main .schedule-table td{ padding-left:8px; padding-right:8px; overflow-wrap:anywhere; }
    .list-main .schedule-table th:first-child,
    .list-main .schedule-table td:first-child{ padding-left:20px; }
    .list-main .schedule-table th:last-child,
    .list-main .schedule-table td:last-child{ padding-right:12px; }
    .list-main .action-cell{ gap:4px; }
    .list-main .action-btn{ width:30px; height:30px; border-radius:9px; }
    .list-main .action-btn svg{ width:14px; height:14px; }
    /* --- Empty state (data kosong) agar rapi & seragam --- */
    .list-main .admin-empty-state,
    .avail-panel .admin-empty-state{
        display:flex; flex-direction:column; align-items:center; justify-content:center;
        text-align:center; gap:6px;
        padding:28px 20px; margin:0;
        font-size:13px; line-height:1.55; font-weight:500; color:#8C948D;
    }
    .list-main .admin-empty-state{ min-height:96px; }
    .avail-panel .admin-empty-state{
        padding:20px 12px; font-size:12px;
        border:1px dashed #DDE3DE; border-radius:12px; background:#FAFBFA;
    }
    .list-main .admin-empty-state[hidden],
    .avail-panel .admin-empty-state[hidden]{ display:none; }
    /* judul kolom tabel kosong tidak perlu terlalu renggang */
    .list-main .schedule-table thead th{ white-space:nowrap; }

    .avail-panel{
        position:sticky; top:16px;
        margin:26px 20px 10px 0;
        padding-left:18px;
        border-left:1px solid #EEF1ED;
    }
    .avail-panel-head{ display:flex; align-items:center; gap:10px; margin-bottom:14px; }
    .avail-panel-head h3{ margin:0; font-size:13px; font-weight:700; color:#1F2A24; }
    .avail-panel .form-control{ width:100%; height:34px; margin-bottom:8px; font-size:12px; }
    .avail-panel .admin-pill{ font-size:10.5px; padding:2px 8px; }
    .avail-panel .admin-btn{ height:34px; font-size:12px; gap:6px; }
    .avail-panel .admin-btn svg{ width:13px; height:13px; }
    .avail-panel .admin-btn{ width:100%; justify-content:center; margin-bottom:12px; }
    .avail-list{ display:flex; flex-direction:column; gap:8px; max-height:min(64vh,560px); overflow:auto; padding-right:2px; }
    .avail-item{
        padding:9px 11px;
        border:1px solid #E8ECE8; border-radius:11px; background:#fff;
        display:flex; flex-direction:column; gap:5px;
        transition:border-color .15s ease, background .15s ease;
    }
    .avail-item:hover{ border-color:#CFE0D6; background:#F7FAF8; }
    .avail-item.is-past{ opacity:.5; }
    .avail-top{ display:flex; align-items:flex-start; justify-content:space-between; gap:6px; }
    .avail-top .action-btn{ width:26px; height:26px; border-radius:8px; }
    .avail-top .action-btn svg{ width:12px; height:12px; }
    .avail-top .action-cell{ gap:4px; }
    .action-add{ border:1px solid #3F6D5D; background:#3F6D5D; color:#fff; }
    .action-add:hover{ background:#345C4E; border-color:#345C4E; }
    .slot-hint{
        grid-column:1/-1; margin-top:-6px; padding:9px 12px;
        border-radius:10px; background:#F3F8F5; border:1px solid #CFE0D6;
        color:#2C4A3E; font-size:12px; line-height:1.45;
    }
    .avail-when{ display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
    .avail-top .person-name{ font-size:12.5px; line-height:1.3; }
    .avail-when .date-text{ font-size:11.5px; }
    .avail-item .time-text{ font-size:11.5px; }
    .avail-note{ font-size:11px; color:#6B756E; }
    .avail-modal{ width:min(560px,100%); }
    .avail-range{ display:flex; align-items:center; gap:10px; }
    .avail-range .form-control{ flex:1; min-width:0; }
    .avail-range span{ color:#8C948D; font-size:13px; }

    /* ===== Responsive ===== */
    /* Dasar: cegah overflow horizontal di seluruh halaman */
    .admin-card{ max-width:100%; }
    .admin-card-head{ flex-wrap:wrap; gap:12px; }

    /* --- Laptop kecil / tablet landscape: panel ketersediaan pindah ke bawah --- */
    @media(max-width:1280px){
        .list-split{ grid-template-columns:1fr; }
        .avail-panel{ position:static; margin:6px 24px 20px; padding:22px 0 0; border-left:0; border-top:1px solid #EEF1ED; }
        .avail-list{
            display:grid; grid-template-columns:repeat(auto-fill,minmax(250px,1fr));
            max-height:none; overflow:visible;
        }
        .avail-panel .form-control,
        .avail-panel .admin-btn{ max-width:420px; }
    }

    /* --- Tablet: tabel boleh scroll horizontal, tidak merusak layout --- */
    @media(max-width:1050px){
        .schedule-table{ min-width:980px; }
        .list-main .schedule-table{ min-width:860px; }
    }

    /* --- Tablet portrait & bawah: tabel berubah jadi kartu --- */
    @media(max-width:860px){
        .admin-header-actions{ width:100%; justify-content:space-between; }

        .schedule-section{ padding:20px 0 6px; }
        .schedule-section-head{ padding:0 16px; }

        /* Tabel tetap tabel: kolom tidak ditekan, area tabel bisa digeser (scroll) ke samping */
        .admin-table-wrap{
            overflow-x:auto; -webkit-overflow-scrolling:touch;
            padding:0 16px 8px; scrollbar-width:thin;
        }
        .list-main .schedule-table,
        .schedule-table{ width:100%; table-layout:auto; }
        .list-main .schedule-table.queue-table,
        .queue-table{ min-width:620px; }
        .list-main .schedule-table.main-table,
        .main-table{ min-width:820px; }
        .list-main .schedule-table th,
        .list-main .schedule-table td,
        .schedule-table th,
        .schedule-table td{
            white-space:nowrap; overflow-wrap:normal;
            padding-left:12px; padding-right:12px;
        }
        .list-main .schedule-table th:first-child,
        .list-main .schedule-table td:first-child,
        .schedule-table th:first-child,
        .schedule-table td:first-child{ padding-left:4px; }
        .list-main .schedule-table th:last-child,
        .list-main .schedule-table td:last-child,
        .schedule-table th:last-child,
        .schedule-table td:last-child{ padding-right:4px; }
        /* kolom lokasi boleh turun baris agar tidak terlalu lebar */
        .main-table td:nth-child(5){ white-space:normal; min-width:150px; }
        /* lebar kolom persen dilepas, ikut isi */
        .queue-table th, .main-table th{ width:auto !important; }

        .list-main .admin-empty-state{ margin:0 16px 8px; padding:22px 16px; min-height:0; }

        /* panel ketersediaan */
        .avail-panel{ margin:4px 16px 18px; }
        .avail-list{ grid-template-columns:1fr; }
        .avail-panel .form-control,
        .avail-panel .admin-btn{ max-width:none; }

        /* form & modal */
        .form-grid{ grid-template-columns:1fr; }
        .form-group.full{ grid-column:auto; }
        .pick-toolbar{ flex-wrap:wrap; padding:14px 18px 10px; }
        .pick-search{ flex:1 1 100%; }
        .pick-list{ padding:0 18px 18px; }
        .pick-empty{ margin:0 18px 18px; }
        .modal-head, .modal-body{ padding-left:18px; padding-right:18px; }
        .modal-foot{ padding:14px 18px 20px; }

        /* kalender */
        .calendar-section{ padding:20px 14px 22px; }
        .calendar-top{ min-width:0; flex-wrap:wrap; }
        .calendar-grid{ min-width:0; }
        .calendar-day{ min-height:92px; padding:5px; }
        .calendar-event span{ display:none; }
    }

    /* --- HP (termasuk 375 x 667 dan lebih kecil) --- */
    @media(max-width:480px){
        .admin-card-head h2{ font-size:18px; }
        .admin-header-actions{ gap:8px; }
        #addScheduleBtn{ flex:1 1 100%; justify-content:center; height:42px; }

        .schedule-section-head{ flex-wrap:wrap; }
        .admin-table-wrap{ padding:0 12px 6px; }
        .schedule-table th, .schedule-table td{ font-size:12.5px; }
        .list-main .action-btn{ width:32px; height:32px; }
        .avail-panel{ margin:4px 12px 16px; }

        /* modal jadi bottom-sheet penuh lebar, tinggi menyesuaikan layar 667px */
        .modal-backdrop{ padding:0; align-items:flex-end; }
        .schedule-modal,
        .avail-modal{
            width:100%; max-height:92vh; max-height:92dvh;
            border-radius:18px 18px 0 0;
            animation:sheetIn .2s ease;
        }
        @keyframes sheetIn{ from{ transform:translateY(24px); opacity:.6; } to{ transform:none; opacity:1; } }
        .modal-head{ padding:16px 16px 12px; position:sticky; top:0; background:#fff; z-index:2; }
        .modal-head h3{ font-size:16px; }
        .modal-body{ padding:14px 16px; }
        .pick-toolbar{ padding:12px 16px 8px; }
        .pick-list{ padding:0 16px 16px; max-height:none; }
        .pick-item{ padding:12px; }
        .pick-meta{ flex-direction:column; align-items:flex-start; gap:6px; }
        .pick-row{ flex-wrap:wrap; }
        .selected-top{ flex-direction:column; gap:8px; }
        .selected-side{ justify-content:space-between; width:100%; }

        /* 16px mencegah iOS auto-zoom saat fokus ke input */
        .form-control,
        input.form-control[type="date"],
        input.form-control[type="time"],
        .avail-panel .form-control{ font-size:16px; height:44px; }
        .form-control::-webkit-datetime-edit,
        .form-control::-webkit-datetime-edit-fields-wrapper,
        .form-control::-webkit-datetime-edit-text,
        .form-control::-webkit-datetime-edit-month-field,
        .form-control::-webkit-datetime-edit-day-field,
        .form-control::-webkit-datetime-edit-year-field,
        .form-control::-webkit-datetime-edit-hour-field,
        .form-control::-webkit-datetime-edit-minute-field,
        .form-control::-webkit-datetime-edit-ampm-field{ font-size:16px; }
        textarea.form-control{ height:84px; }
        .combo-list{ max-height:180px; }
        .combo-list li{ padding:12px 10px; font-size:14px; }

        .avail-range{ gap:8px; }
        .avail-range span{ font-size:12px; }

        .modal-foot{
            position:sticky; bottom:0; background:#fff;
            padding:12px 16px calc(14px + env(safe-area-inset-bottom));
            border-top:1px solid #EEF1ED;
        }
        .modal-foot .btn-secondary,
        .modal-foot .btn-primary{ flex:1; height:44px; font-size:13px; }

        /* kalender: kompak, event jadi blok kecil */
        .calendar-section{ padding:16px 10px 18px; }
        .calendar-title h3{ font-size:17px; }
        .calendar-nav button{ height:34px; }
        .calendar-weekday{ height:30px; font-size:10px; letter-spacing:.02em; }
        .calendar-day{ min-height:62px; padding:3px; gap:2px; }
        .day-number{ width:22px; height:22px; font-size:11px; }
        .calendar-event{ padding:2px 4px; border-left-width:2px; border-radius:5px; font-size:9.5px; }
        .calendar-event strong{ font-size:9.5px; }
        .calendar-more{ font-size:9px; padding:0 2px; }

        .schedule-toast{ left:12px; right:12px; bottom:calc(12px + env(safe-area-inset-bottom)); text-align:center; }
    }

    /* --- HP sangat kecil (<= 360px) --- */
    @media(max-width:360px){
        .calendar-day{ min-height:54px; }
        .calendar-event strong{ display:block; }
    }

    /* --- Layar pendek (mis. landscape HP): modal tidak terpotong --- */
    @media(max-height:520px){
        .schedule-modal{ max-height:100vh; border-radius:0; }
        .pick-list{ max-height:none; }
    }
</style>
@endpush

@section('content')

@php
    // Pemetaan status → tone pill, dipakai supaya warna status di sini
    // konsisten dengan tone pill di pendaftaran.blade.php.
    $statusTone = [
        'Terjadwal'   => 'terjadwal',
        'Dibatalkan'  => 'ditolak',
        'Tidak Hadir' => 'menunggu',
    ];

    $konseliTerverifikasi = $konseliTerverifikasi ?? [];
    $daftarKonselor = $daftarKonselor ?? collect($jadwal)->pluck('konselor')
        ->merge(collect($antrianLanjutan)->pluck('konselor'))
        ->filter()->unique()->values()->all();
@endphp

<div class="admin-card">

    <div class="admin-card-head" style="align-items:flex-start;">
        <div>
            <h2>Jadwal Konseling</h2>
            <p id="scheduleMonthText">{{ $bulanTampil }}</p>
        </div>

        <div class="admin-header-actions">
            <button type="button" class="admin-btn" id="addScheduleBtn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Buat Jadwal
            </button>
        </div>
    </div>

    {{-- ============ TAMPILAN LIST ============ --}}
    <div class="list-view active" id="listView">
    <div class="list-split">
    <div class="list-main">

        {{-- ---- Antrian sesi lanjutan ---- --}}
        <div class="schedule-section queue-section">
            <div class="schedule-section-head">
                <h3>Antrian Sesi Lanjutan</h3>
                <span class="admin-pill tone-menunggu" id="waitingCount">{{ count($antrianLanjutan) }} menunggu</span>
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table schedule-table queue-table">
                    <thead>
                        <tr>
                            <th>Konseli</th>
                            <th>Konselor</th>
                            <th>Sesi</th>
                            <th>Tanggal Terakhir</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="queueBody">
                        @forelse($antrianLanjutan as $item)
                            <tr data-queue="1">
                                <td><span class="person-name">{{ $item['konseli'] }}</span></td>
                                <td><span class="counselor">{{ $item['konselor'] }}</span></td>
                                <td><span class="session-text">{{ $item['sesi'] }}</span></td>
                                <td class="date-text">{{ $item['tanggal_terakhir'] }}</td>
                                <td>
                                    <button type="button" class="queue-schedule-btn"
                                        data-queue-konseli="{{ $item['konseli'] }}"
                                        data-queue-konselor="{{ $item['konselor'] }}"
                                        data-queue-kategori="{{ $item['kategori'] }}"
                                        data-queue-pengajuan-id="{{ $item['pengajuan_id'] ?? '' }}">
                                        Jadwalkan
                                    </button>
                                </td>
                            </tr>
                        @empty
                        @endforelse
                    </tbody>
                </table>
                <div class="admin-empty-state" id="queueEmpty" @if(count($antrianLanjutan)) hidden @endif>
                    Tidak ada antrian sesi lanjutan.
                </div>
            </div>
        </div>

        {{-- ---- Jadwal konseling utama ---- --}}
        <div class="schedule-section">
            <div class="schedule-section-head">
                <h3>Jadwal Konseling</h3>
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table schedule-table main-table">
                    <thead>
                        <tr>
                            <th>Konseli</th>
                            <th>Konselor</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="scheduleBody">
                        @forelse($jadwal as $item)
                            <tr data-id="{{ $item['id'] }}"
                                data-pengajuan-id="{{ $item['pengajuan_id'] ?? '' }}"
                                data-status="{{ $item['status'] }}"
                                data-konseli="{{ $item['konseli'] }}"
                                data-konselor="{{ $item['konselor'] }}"
                                data-kategori="{{ $item['kategori'] }}"
                                data-tanggal="{{ $item['tanggal_iso'] }}"
                                data-waktu="{{ $item['waktu'] }}"
                                data-lokasi="{{ $item['lokasi'] }}">
                                <td><span class="person-name">{{ $item['konseli'] }}</span></td>
                                <td><span class="counselor">{{ $item['konselor'] }}</span></td>
                                <td class="date-text">{{ $item['tanggal'] }}</td>
                                <td><span class="time-text">{{ $item['waktu'] }}<br>WIB</span></td>
                                <td class="admin-cell-muted">{{ $item['lokasi'] }}</td>
                                <td><span class="admin-pill tone-{{ $statusTone[$item['status']] ?? 'terjadwal' }}">{{ $item['status'] }}</span></td>
                                <td>
                                    <div class="action-cell">
                                        <button type="button" class="action-btn action-reschedule" title="Atur ulang" aria-label="Atur ulang"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-3-6.7L21 8"/><path d="M21 3v5h-5"/></svg></button>
                                        <button type="button" class="action-btn action-cancel" title="Batalkan" aria-label="Batalkan"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m15 9-6 6M9 9l6 6"/></svg></button>
                                        <button type="button" class="action-btn action-absent" title="Tidak hadir" aria-label="Tidak hadir"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m17 8 5 5M22 8l-5 5"/></svg></button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                        @endforelse
                    </tbody>
                </table>
                <div class="admin-empty-state" id="scheduleEmpty" @if(count($jadwal)) hidden @endif>
                    Belum ada jadwal konseling.
                </div>
            </div>
        </div>
    </div>{{-- /list-main --}}

        {{-- ---- Ketersediaan konselor (panel kanan) ---- --}}
        <aside class="avail-panel" aria-label="Ketersediaan konselor">
            <div class="avail-panel-head">
                <h3>Ketersediaan Konselor</h3>
                <span class="admin-pill tone-terjadwal" id="availCount"></span>
            </div>
            <input class="form-control" id="availSearch" type="search" autocomplete="off" placeholder="Cari nama konselor…">
            <button type="button" class="admin-btn" id="addAvailBtn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                Tambah Ketersediaan
            </button>
            <div class="avail-list" id="availBody"></div>
            <div class="admin-empty-state" id="availEmpty" hidden></div>
        </aside>
    </div>
    </div>

    {{-- ============ TAMPILAN KALENDER ============ --}}
    <div class="schedule-section calendar-section" id="calendarView">
        <div class="calendar-top">
            <div class="calendar-title">
                <h3 id="calendarMonthTitle">{{ $bulanTampil }}</h3>
                <p>Jadwal konseling aktif per bulan</p>
            </div>
            <div class="calendar-nav">
                <button type="button" class="nav-arrow" id="prevMonth" aria-label="Bulan sebelumnya">‹</button>
                <button type="button" class="nav-today" id="todayMonth">Hari ini</button>
                <button type="button" class="nav-arrow" id="nextMonth" aria-label="Bulan berikutnya">›</button>
            </div>
        </div>
        <div class="calendar-grid" id="calendarGrid"></div>
    </div>

</div>

{{-- ============ MODAL BUAT / ATUR ULANG JADWAL ============ --}}
<div class="modal-backdrop" id="scheduleModal">
    <div class="schedule-modal" role="dialog" aria-modal="true" aria-labelledby="scheduleModalTitle">
        <div class="modal-head">
            <div>
                <h3 id="scheduleModalTitle">Buat Jadwal Konseling</h3>
                <p id="scheduleModalSubtitle">Pilih konseli terverifikasi yang akan dijadwalkan.</p>
            </div>
            <button type="button" class="modal-close" id="closeScheduleModal" aria-label="Tutup">×</button>
        </div>

        {{-- Langkah 1: pilih konseli --}}
        <div id="stepPick">
            <div class="pick-toolbar">
                <div class="pick-search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                    <input class="form-control" id="pickSearch" type="search" autocomplete="off" placeholder="Cari nama, NIM, prodi, atau kategori…">
                </div>
                <span class="pick-count" id="pickCount"></span>
            </div>
            <div class="pick-list" id="pickList"></div>
            <div class="admin-empty-state pick-empty" id="pickEmpty" hidden></div>
        </div>

        {{-- Langkah 2: detail jadwal --}}
        <form id="scheduleForm" hidden>
            <div class="modal-body">
                <div class="selected-card" id="selectedCard"></div>

                <div class="form-grid">
                    <div class="form-group full">
                        <label for="konselorSearch">Konselor</label>
                        <div class="combo" id="konselorCombo">
                            <input class="form-control" id="konselorSearch" type="text" autocomplete="off"
                                   placeholder="Cari & pilih konselor…" role="combobox"
                                   aria-expanded="false" aria-controls="konselorList">
                            <svg class="combo-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                            <ul class="combo-list" id="konselorList" role="listbox" hidden>
                                <li class="empty" id="konselorEmpty" hidden>Konselor tidak ditemukan</li>
                            </ul>
                        </div>
                        <input type="hidden" id="formKonselor">
                    </div>
                    <div class="form-group">
                        <label for="formTanggal">Tanggal</label>
                        <input class="form-control" type="date" id="formTanggal" required>
                    </div>
                    <div class="form-group">
                        <label for="formWaktu">Waktu</label>
                        <input class="form-control" type="time" id="formWaktu" required>
                    </div>
                    <div class="slot-hint" id="slotHint" hidden></div>
                    <div class="form-group full">
                        <label for="formLokasi">Tempat</label>
                        <select class="form-control" id="formLokasi" required>
                            <option>Ruang Konseling LPPP Unnes</option>
                            <option>LAB BK FIPP</option>
                            <option>Zoom</option>
                        </select>
                    </div>
                    <div class="form-group full">
                        <label for="formCatatan">Catatan (opsional)</label>
                        <textarea class="form-control" id="formCatatan" placeholder="Tambahkan catatan jadwal..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-secondary" id="cancelScheduleModal">Batal</button>
                <button type="submit" class="btn-primary">Simpan Jadwal</button>
            </div>
        </form>
    </div>
</div>

{{-- ============ MODAL TAMBAH / EDIT KETERSEDIAAN KONSELOR ============ --}}
<div class="modal-backdrop" id="availModal">
    <div class="schedule-modal avail-modal" role="dialog" aria-modal="true" aria-labelledby="availModalTitle">
        <div class="modal-head">
            <div>
                <h3 id="availModalTitle">Tambah Ketersediaan</h3>
                <p>Tentukan kapan konselor bersedia melayani konseling.</p>
            </div>
            <button type="button" class="modal-close" id="closeAvailModal" aria-label="Tutup">×</button>
        </div>
        <form id="availForm" novalidate>
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group full">
                        <label for="availKonselorSearch">Konselor</label>
                        <div class="combo" id="availKonselorCombo">
                            <input class="form-control" id="availKonselorSearch" type="text" autocomplete="off"
                                   placeholder="Cari & pilih konselor…" role="combobox"
                                   aria-expanded="false" aria-controls="availKonselorList">
                            <svg class="combo-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                            <ul class="combo-list" id="availKonselorList" role="listbox" hidden>
                                <li class="empty" id="availKonselorEmpty" hidden>Konselor tidak ditemukan</li>
                            </ul>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="availTanggal">Tanggal</label>
                        <input class="form-control" type="date" id="availTanggal" required>
                    </div>
                    <div class="form-group">
                        <label for="availHari">Hari</label>
                        <input class="form-control" type="text" id="availHari" readonly tabindex="-1" placeholder="Otomatis dari tanggal">
                    </div>
                    <div class="form-group full">
                        <label for="availMulai">Bersedia dari jam – sampai jam</label>
                        <div class="avail-range">
                            <input class="form-control" type="time" id="availMulai" required>
                            <span>sampai</span>
                            <input class="form-control" type="time" id="availSelesai" required>
                        </div>
                        <div class="form-hint warn" id="availError" hidden></div>
                    </div>
                    <div class="form-group full">
                        <label for="availCatatan">Catatan (opsional)</label>
                        <textarea class="form-control" id="availCatatan" placeholder="Mis. hanya tatap muka, atau hanya online…"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-secondary" id="cancelAvailModal">Batal</button>
                <button type="submit" class="btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="schedule-toast" id="scheduleToast"></div>

@endsection

@push('scripts')
<script>
(function(){
    'use strict';

    const monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    const dayNames = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];

    function toIso(d){
        return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
    }

    // Dulu "hari ini" & bulan awal kalender di-hardcode ke 23 Agustus 2025.
    // Sekarang selalu mengikuti tanggal perangkat saat halaman dibuka.
    const today = new Date();
    const todayIso = toIso(today);
    document.getElementById('formTanggal').min = todayIso;

    let calendarDate = new Date(today.getFullYear(), today.getMonth(), 1);
    let editRow = null;

    const modal = document.getElementById('scheduleModal');
    const form = document.getElementById('scheduleForm');
    const toast = document.getElementById('scheduleToast');
    const scheduleBody = document.getElementById('scheduleBody');
    const scheduleEmpty = document.getElementById('scheduleEmpty');

    const statusTone = {
        'Terjadwal': 'terjadwal',
        'Dibatalkan': 'ditolak',
        'Tidak Hadir': 'menunggu'
    };

    function setStatusPill(row, status){
        row.dataset.status = status;
        const pill = row.querySelector('td:nth-child(6) .admin-pill');
        pill.className = 'admin-pill tone-' + (statusTone[status] || 'terjadwal');
        pill.textContent = status;
    }

    function showToast(message){
        toast.textContent = message;
        toast.classList.add('show');
        clearTimeout(window.__silakonToast);
        window.__silakonToast = setTimeout(() => toast.classList.remove('show'), 2300);
    }

    const ICON = {
        cal:    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>',
        clock:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
        pin:    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>',
        screen: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>'
    };

    // Data dari controller
    const verified = @json($konseliTerverifikasi);
    const konselorNames = @json($daftarKonselor);
    const konselorIds = @json($konselorIds ?? []);
    const scheduleStoreUrl = @json(route('admin.penjadwalan.jadwal.store'));
    const availabilityStoreUrl = @json(route('admin.penjadwalan.ketersediaan.store'));
    const availabilityUpdateUrl = @json(url('/admin/penjadwalan/ketersediaan'));
    const csrfToken = @json(csrf_token());

    const stepPick = document.getElementById('stepPick');
    const pickList = document.getElementById('pickList');
    const pickSearch = document.getElementById('pickSearch');
    const pickCount = document.getElementById('pickCount');
    const pickEmpty = document.getElementById('pickEmpty');
    const selectedCard = document.getElementById('selectedCard');

    let pending = verified.slice();   // konseli terverifikasi yang belum dijadwalkan
    let selected = null;              // konseli yang sedang dijadwalkan
    let modalMode = 'create';
    let slotCtx = null;               // slot ketersediaan yang dipakai saat klik tombol "+" (null = Buat Jadwal biasa)

    const keyOf = k => String(k.id !== undefined && k.id !== null ? k.id : k.nama);

    function prefChips(k){
        const chips = [];
        if(k.hari) chips.push('<span class="chip">' + ICON.cal + escapeHtml(k.hari) + '</span>');
        if(k.jam)  chips.push('<span class="chip">' + ICON.clock + escapeHtml(k.jam) + '</span>');
        if(k.mode){
            const on = String(k.mode).toLowerCase() === 'online';
            chips.push('<span class="chip mode-' + (on ? 'online' : 'offline') + '">' + (on ? ICON.screen : ICON.pin) + escapeHtml(k.mode) + '</span>');
        }
        return chips.length ? '<span class="pick-label">Preferensi</span>' + chips.join('') : '';
    }

    function renderPickList(){
        const q = pickSearch.value.trim().toLowerCase();
        const items = pending.filter(k => [k.nama, k.nim, k.prodi, k.kategori].join(' ').toLowerCase().includes(q));

        pickCount.textContent = pending.length + ' menunggu jadwal';
        pickEmpty.hidden = items.length > 0;
        pickEmpty.textContent = pending.length
            ? 'Tidak ada konseli yang cocok dengan pencarian.'
            : 'Belum ada konseli terverifikasi yang menunggu jadwal.';

        pickList.innerHTML = items.map(k => {
            const sub = [k.nim, k.prodi].filter(Boolean).map(escapeHtml).join(' · ');
            return `<button type="button" class="pick-item" data-key="${escapeHtml(keyOf(k))}">
                <div class="pick-row">
                    <div>
                        <div class="pick-name">${escapeHtml(k.nama)}</div>
                        <div class="pick-sub">${sub}</div>
                    </div>
                    <span class="admin-pill tone-diverifikasi">${escapeHtml(k.prioritas ? 'Prioritas ' + k.prioritas : (k.kategori || ''))}</span>
                </div>
                <div class="pick-meta">
                    <div class="pref-chips">${prefChips(k)}</div>
                    ${k.tanggal_verifikasi ? '<span class="pick-verif">Diverifikasi ' + escapeHtml(k.tanggal_verifikasi) + '</span>' : ''}
                    ${k.who5_skor !== null && k.who5_skor !== undefined ? '<span class="pick-verif">WHO-5 ' + escapeHtml(String(k.who5_skor)) + '/100 · SAW ' + escapeHtml(Number(k.saw_nilai || 0).toFixed(2)) + '</span>' : ''}
                </div>
            </button>`;
        }).join('');
    }

    pickSearch.addEventListener('input', renderPickList);
    pickList.addEventListener('click', e => {
        const btn = e.target.closest('.pick-item');
        if(!btn) return;
        const k = pending.find(x => keyOf(x) === btn.dataset.key);
        if(k) chooseKonseli(k);
    });

    // ---- Dropdown konselor yang bisa dicari ----
    const combo = document.getElementById('konselorCombo');
    const cInput = document.getElementById('konselorSearch');
    const cList = document.getElementById('konselorList');
    const cEmpty = document.getElementById('konselorEmpty');
    const cHidden = document.getElementById('formKonselor');
    let cActive = -1;

    function addKonselorItem(name, temp){
        const li = document.createElement('li');
        li.setAttribute('role', 'option');
        li.dataset.value = name;
        li.textContent = name;
        if(temp) li.dataset.temp = '1';
        cList.insertBefore(li, cEmpty);
    }
    konselorNames.forEach(n => addKonselorItem(n, false));

    const cItems = () => Array.from(cList.querySelectorAll('li[data-value]'));
    const cVisible = () => cItems().filter(li => !li.hidden);

    function markSelected(){
        cItems().forEach(li => li.classList.toggle('selected', li.dataset.value === cHidden.value));
    }
    function filterCombo(q){
        q = q.trim().toLowerCase();
        let n = 0;
        cItems().forEach(li => {
            const ok = li.dataset.value.toLowerCase().includes(q);
            li.hidden = !ok;
            if(ok) n++;
        });
        cEmpty.hidden = n > 0;
        cActive = -1;
        cItems().forEach(li => li.classList.remove('active'));
    }
    function openCombo(){
        cList.hidden = false;
        cInput.setAttribute('aria-expanded', 'true');
    }
    function closeCombo(){
        cList.hidden = true;
        cActive = -1;
        cInput.setAttribute('aria-expanded', 'false');
        cInput.value = cHidden.value;
    }
    function chooseKonselor(name){
        cHidden.value = name;
        markSelected();
        closeCombo();
    }
    function setKonselor(name){
        if(name && !cItems().some(li => li.dataset.value === name)) addKonselorItem(name, true);
        cHidden.value = name || '';
        cInput.value = name || '';
        markSelected();
    }
    function clearTempKonselor(){
        cList.querySelectorAll('li[data-temp]').forEach(li => li.remove());
    }

    cInput.addEventListener('focus', () => { filterCombo(''); openCombo(); cInput.select(); });
    cInput.addEventListener('click', () => { if(cList.hidden){ filterCombo(''); openCombo(); } });
    cInput.addEventListener('input', () => { openCombo(); filterCombo(cInput.value); });
    cInput.addEventListener('keydown', e => {
        const vis = cVisible();
        if(e.key === 'ArrowDown' || e.key === 'ArrowUp'){
            e.preventDefault();
            if(cList.hidden){ filterCombo(''); openCombo(); return; }
            if(!vis.length) return;
            cActive = (cActive + (e.key === 'ArrowDown' ? 1 : -1) + vis.length) % vis.length;
            vis.forEach((li, i) => li.classList.toggle('active', i === cActive));
            vis[cActive].scrollIntoView({ block: 'nearest' });
        }else if(e.key === 'Enter'){
            if(!cList.hidden){
                e.preventDefault();
                const pick = vis[cActive] || (vis.length === 1 ? vis[0] : null);
                if(pick) chooseKonselor(pick.dataset.value);
            }
        }else if(e.key === 'Escape'){
            if(!cList.hidden){ e.stopPropagation(); closeCombo(); }
        }else if(e.key === 'Tab'){
            closeCombo();
        }
    });
    cList.addEventListener('click', e => {
        const li = e.target.closest('li[data-value]');
        if(li) chooseKonselor(li.dataset.value);
    });
    document.addEventListener('click', e => { if(!combo.contains(e.target)) closeCombo(); });

    // ---- Langkah & ringkasan ----
    function showStep(step){
        const pick = step === 'pick';
        stepPick.hidden = !pick;
        form.hidden = pick;
        document.getElementById('scheduleModalTitle').textContent =
            pick ? 'Pilih Konseli' : (modalMode === 'edit' ? 'Atur Ulang Jadwal' : 'Buat Jadwal Konseling');
        document.getElementById('scheduleModalSubtitle').textContent =
            pick ? (slotCtx
                    ? 'Pilih konseli untuk ' + slotCtx.konselor + ' · ' + formatDate(slotCtx.tanggal) + ' · ' + dotTime(slotCtx.mulai) + ' – ' + dotTime(slotCtx.selesai) + ' WIB.'
                    : 'Klik konseli terverifikasi yang akan dijadwalkan.')
                 : (modalMode === 'edit' ? 'Perbarui detail jadwal konseling.' : 'Pilih konselor, lalu tentukan tanggal, waktu, dan tempat.');
    }

    function renderSelected(canChange){
        const k = selected;
        const sub = [k.nim, k.prodi].filter(Boolean).map(escapeHtml).join(' · ');
        const chips = prefChips(k);
        selectedCard.innerHTML = `
            <div class="selected-top">
                <div>
                    <div class="pick-name">${escapeHtml(k.nama)}</div>
                    ${sub ? '<div class="pick-sub">' + sub + '</div>' : ''}
                </div>
                <div class="selected-side">
                    ${k.kategori ? '<span class="admin-pill tone-diverifikasi">' + escapeHtml(k.kategori) + '</span>' : ''}
                    ${canChange ? '<button type="button" class="link-btn" id="changeKonseli">Ganti</button>' : ''}
                </div>
            </div>
            ${chips ? '<div class="pref-chips">' + chips + '</div>' : ''}`;
    }

    selectedCard.addEventListener('click', e => {
        if(e.target.closest('#changeKonseli')){
            selected = null;
            renderPickList();
            showStep('pick');
            pickSearch.focus();
        }
    });

    // Tanggal terdekat sesuai hari preferensi konseli (mis. "Senin")
    function nextDateFor(hari){
        const map = { minggu:0, senin:1, selasa:2, rabu:3, kamis:4, jumat:5, "jum'at":5, sabtu:6 };
        const text = String(hari || '').toLowerCase();
        const name = Object.keys(map).find(n => text.includes(n));
        if(name === undefined) return null;
        const d = new Date(today);
        d.setDate(d.getDate() + ((map[name] - d.getDay() + 7) % 7));
        return toIso(d);
    }
    // Jam awal dari preferensi (mis. "09.00 - 11.00" → "09:00")
    function firstTime(jam){
        const m = String(jam || '').match(/(\d{1,2})[.:](\d{2})/);
        return m ? m[1].padStart(2, '0') + ':' + m[2] : null;
    }

    function fillFields(f){
        document.getElementById('formTanggal').value = f.tanggal || todayIso;
        document.getElementById('formWaktu').value = f.waktu || '09:00';
        document.getElementById('formLokasi').value = f.lokasi || 'Ruang Konseling LPPP Unnes';
        document.getElementById('formCatatan').value = f.catatan || '';
    }

    function updateSlotHint(){
        const el = document.getElementById('slotHint');
        el.hidden = !slotCtx;
        if(slotCtx){
            el.textContent = 'Slot ' + slotCtx.konselor + ': ' + dotTime(slotCtx.mulai) + ' – ' + dotTime(slotCtx.selesai)
                + ' WIB. Jam yang dipakai akan otomatis dihapus dari daftar ketersediaan.';
        }
    }

    function chooseKonseli(k){
        selected = Object.assign({}, k, { fromList: true });
        renderSelected(true);
        // Dari tombol "+" ketersediaan: konselor, tanggal & jam otomatis terisi dari slot
        setKonselor(slotCtx ? slotCtx.konselor : '');
        fillFields({
            tanggal: slotCtx ? slotCtx.tanggal : nextDateFor(k.hari),
            waktu: slotCtx ? slotCtx.mulai : firstTime(k.jam),
            lokasi: String(k.mode || '').toLowerCase() === 'online' ? 'Zoom' : 'Ruang Konseling LPPP Unnes'
        });
        updateSlotHint();
        showStep('form');
        setTimeout(() => cInput.focus(), 50);
    }

    function openModal(data = {}, mode = 'create'){
        editRow = data.row || null;
        modalMode = mode;
        slotCtx = data.slot || null;
        updateSlotHint();
        clearTempKonselor();

        if(data.konseli){
            // Atur ulang / sesi lanjutan: konseli sudah pasti, langsung ke detail jadwal
            selected = { id: data.pengajuan_id, pengajuan_id: data.pengajuan_id, nama: data.konseli, kategori: data.kategori };
            renderSelected(false);
            setKonselor(data.konselor || '');
            fillFields(data);
            showStep('form');
            setTimeout(() => cInput.focus(), 50);
        }else{
            selected = null;
            pickSearch.value = '';
            renderPickList();
            showStep('pick');
            setTimeout(() => pickSearch.focus(), 50);
        }
        modal.classList.add('show');
    }

    function closeModal(){
        modal.classList.remove('show');
        editRow = null;
        selected = null;
        slotCtx = null;
        updateSlotHint();
        form.reset();
        cHidden.value = '';
        closeCombo();
    }

    document.getElementById('addScheduleBtn').addEventListener('click', () => openModal());
    document.getElementById('closeScheduleModal').addEventListener('click', closeModal);
    document.getElementById('cancelScheduleModal').addEventListener('click', closeModal);
    modal.addEventListener('click', e => { if(e.target === modal) closeModal(); });
    document.addEventListener('keydown', e => { if(e.key === 'Escape') closeModal(); });

    function formatDate(iso){
        const d = new Date(iso + 'T00:00:00');
        return d.getDate() + ' ' + monthNames[d.getMonth()].slice(0,3) + ' ' + d.getFullYear();
    }

    function rowData(row){
        return {
            pengajuan_id: row.dataset.pengajuanId,
            id: row.dataset.id,
            konseli: row.dataset.konseli,
            konselor: row.dataset.konselor,
            kategori: row.dataset.kategori,
            tanggal: row.dataset.tanggal,
            waktu: row.dataset.waktu,
            lokasi: row.dataset.lokasi
        };
    }

    function bindRowActions(row){
        row.querySelector('.action-reschedule')?.addEventListener('click', () => openModal({...rowData(row), row}, 'edit'));

        row.querySelector('.action-cancel')?.addEventListener('click', () => {
            const name = row.dataset.konseli;
            if(!confirm('Batalkan jadwal ' + name + '?')) return;
            fetch(@json(url('/admin/penjadwalan/jadwal')) + '/' + row.dataset.id + '/batal', {method:'POST', headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken,'X-Requested-With':'XMLHttpRequest'}})
            .then(r=>{ if(!r.ok) throw new Error(); return r.json(); })
            .then(body=>{ row.remove();
            if(body.pengajuan && !pending.some(k => String(k.id) === String(body.pengajuan.id))){
                pending.push(body.pengajuan);
                renderPickList();
            }
            scheduleEmpty.hidden = scheduleBody.querySelectorAll('tr').length > 0;
            showToast('Jadwal dihapus dan pengajuan dikembalikan ke tahap sebelumnya.'); renderCalendar(); })
            .catch(()=>showToast('Jadwal gagal dibatalkan.'));
        });

        row.querySelector('.action-absent')?.addEventListener('click', () => {
            const name = row.dataset.konseli;
            if(!confirm('Tandai ' + name + ' sebagai tidak hadir?')) return;
            fetch(@json(url('/admin/penjadwalan/jadwal')) + '/' + row.dataset.id + '/tidak-hadir', {method:'POST', headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken,'X-Requested-With':'XMLHttpRequest'}})
            .then(r=>{if(!r.ok) throw new Error(); return r.json();}).then(()=>{setStatusPill(row, 'Tidak Hadir'); showToast('Status konseli diperbarui menjadi Tidak Hadir.');}).catch(()=>showToast('Status tidak hadir gagal disimpan.'));
        });
    }

    scheduleBody.querySelectorAll('tr').forEach(bindRowActions);

    document.querySelectorAll('.queue-schedule-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const queueId = btn.dataset.queuePengajuanId;
            const queueSelected = pending.find(item => String(item.id) === String(queueId));
            if (queueSelected) selected = queueSelected;
            openModal({
                konseli: btn.dataset.queueKonseli,
                konselor: btn.dataset.queueKonselor,
                kategori: btn.dataset.queueKategori,
                tanggal: todayIso,
                waktu: '09:00',
                lokasi: 'Ruang Konseling LPPP Unnes',
                pengajuan_id: queueId,
                locked: true
            });
        });
    });

    form.addEventListener('submit', async e => {
        e.preventDefault();

        const data = {
            konseli: selected ? selected.nama : '',
            konselor: cHidden.value,
            kategori: selected ? (selected.kategori || '') : '',
            tanggal: document.getElementById('formTanggal').value,
            waktu: document.getElementById('formWaktu').value,
            lokasi: document.getElementById('formLokasi').value
        };

        if(!data.konseli) return;
        if(!data.konselor){
            showToast('Pilih konselor terlebih dahulu.');
            cInput.focus();
            return;
        }
        if(!data.tanggal || !data.waktu) return;

        // Dari tombol "+": jam harus masih di dalam slot yang dipilih
        if(slotCtx && !editRow && data.konselor === slotCtx.konselor && data.tanggal === slotCtx.tanggal
           && !(data.waktu >= slotCtx.mulai && data.waktu < slotCtx.selesai)){
            showToast('Jam harus di dalam slot ' + dotTime(slotCtx.mulai) + ' – ' + dotTime(slotCtx.selesai) + ' WIB.');
            return;
        }

        if (!selected || !selected.id) {
            showToast('Data pengajuan tidak ditemukan. Silakan pilih konseli dari daftar.');
            return;
        }
        let savedSchedule = null;
        try {
            const response = await fetch(scheduleStoreUrl, {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest'},
                body: JSON.stringify({
                    pengajuan_id: selected.id || selected.pengajuan_id,
                    konselor_id: konselorIds[data.konselor],
                    tanggal: data.tanggal,
                    mulai: String(data.waktu).replace('.', ':'),
                    selesai: fromMin(toMin(data.waktu) + SESSION_MIN),
                    metode: selected.mode || null,
                    lokasi: data.lokasi,
                    catatan: document.getElementById('formCatatan')?.value || null
                })
            });
            const responseBody = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(responseBody.message || 'Gagal menyimpan jadwal');
            savedSchedule = responseBody;
        } catch (error) {
            showToast(error.message || 'Jadwal belum tersimpan ke database. Periksa konselor dan waktu yang dipilih.');
            return;
        }

            if(editRow){
            editRow.dataset.konseli = data.konseli;
            editRow.dataset.konselor = data.konselor;
            editRow.dataset.kategori = data.kategori;
            editRow.dataset.tanggal = data.tanggal;
            editRow.dataset.waktu = data.waktu;
            editRow.dataset.lokasi = data.lokasi;

            editRow.children[0].innerHTML = '<span class="person-name"></span>';
            editRow.children[0].querySelector('.person-name').textContent = data.konseli;
            editRow.children[1].innerHTML = '<span class="counselor"></span>';
            editRow.children[1].querySelector('.counselor').textContent = data.konselor;
            editRow.children[2].textContent = formatDate(data.tanggal);
            editRow.children[3].innerHTML = '<span class="time-text"></span>';
            editRow.children[3].querySelector('.time-text').innerHTML = data.waktu + '<br>WIB';
            editRow.children[4].textContent = data.lokasi;

            // Jadwal yang diatur ulang dianggap aktif lagi jadi "Terjadwal".
            setStatusPill(editRow, 'Terjadwal');
            showToast('Jadwal berhasil diperbarui.');
        } else {
            const id = 'new-' + Date.now();
            const tr = document.createElement('tr');
            tr.dataset.id = savedSchedule.id || id;
            tr.dataset.pengajuanId = selected.id || selected.pengajuan_id || '';
            tr.dataset.status = 'Terjadwal';
            tr.dataset.konseli = data.konseli;
            tr.dataset.konselor = data.konselor;
            tr.dataset.kategori = data.kategori;
            tr.dataset.tanggal = data.tanggal;
            tr.dataset.waktu = data.waktu;
            tr.dataset.lokasi = data.lokasi;
            tr.innerHTML = `
                <td><span class="person-name">${escapeHtml(data.konseli)}</span></td>
                <td><span class="counselor">${escapeHtml(data.konselor)}</span></td>
                <td class="date-text">${formatDate(data.tanggal)}</td>
                <td><span class="time-text">${escapeHtml(data.waktu)}<br>WIB</span></td>
                <td class="admin-cell-muted">${escapeHtml(data.lokasi)}</td>
                <td><span class="admin-pill tone-terjadwal">Terjadwal</span></td>
                <td><div class="action-cell">
                    <button type="button" class="action-btn action-reschedule" title="Atur ulang" aria-label="Atur ulang"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-3-6.7L21 8"/><path d="M21 3v5h-5"/></svg></button>
                    <button type="button" class="action-btn action-cancel" title="Batalkan" aria-label="Batalkan"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m15 9-6 6M9 9l6 6"/></svg></button>
                    <button type="button" class="action-btn action-absent" title="Tidak hadir" aria-label="Tidak hadir"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m17 8 5 5M22 8l-5 5"/></svg></button>
                </div></td>`;
            scheduleBody.appendChild(tr);
            bindRowActions(tr);
            scheduleEmpty.hidden = true;
            if(selected && selected.fromList){
                pending = pending.filter(k => keyOf(k) !== keyOf(selected));
            }
            showToast('Jadwal baru berhasil ditambahkan.');
        }

        closeModal();
        renderCalendar();
        renderAvail();
    });

    function escapeHtml(value){
        return String(value).replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[s]));
    }

    function renderCalendar(){
        const grid = document.getElementById('calendarGrid');
        const y = calendarDate.getFullYear();
        const m = calendarDate.getMonth();
        const label = monthNames[m] + ' ' + y;
        document.getElementById('calendarMonthTitle').textContent = label;
        document.getElementById('scheduleMonthText').textContent = label;

        let html = dayNames.map((d, i) => `<div class="calendar-weekday${i === 0 || i === 6 ? ' weekend' : ''}">${d}</div>`).join('');

        const firstDay = new Date(y, m, 1).getDay();
        const daysInMonth = new Date(y, m + 1, 0).getDate();
        const prevDays = new Date(y, m, 0).getDate();
        const totalCells = Math.ceil((firstDay + daysInMonth) / 7) * 7;

        for(let i=0;i<totalCells;i++){
            const cellDay = i - firstDay + 1;
            let dateObj, dayNum, muted = false;

            if(cellDay < 1){
                dayNum = prevDays + cellDay;
                dateObj = new Date(y, m - 1, dayNum);
                muted = true;
            }else if(cellDay > daysInMonth){
                dayNum = cellDay - daysInMonth;
                dateObj = new Date(y, m + 1, dayNum);
                muted = true;
            }else{
                dayNum = cellDay;
                dateObj = new Date(y, m, dayNum);
            }

            const iso = toIso(dateObj);
            const todayClass = iso === todayIso ? ' today' : '';
            html += `<div class="calendar-day${muted ? ' muted' : ''}${todayClass}">
                        <div class="day-number">${dayNum}</div>
                        ${getCalendarEvents(iso)}
                     </div>`;
        }
        grid.innerHTML = html;
    }

    function getCalendarEvents(iso){
        const rows = Array.from(scheduleBody.querySelectorAll('tr'));
        const events = rows
            .filter(r => r.dataset.tanggal === iso && r.dataset.status !== 'Dibatalkan')
            .sort((a, b) => (a.dataset.waktu || '').localeCompare(b.dataset.waktu || ''));

        if(!events.length) return '';

        const MAX = 2;
        let out = events.slice(0, MAX).map(row => {
            const cat = 'cat-' + String(row.dataset.kategori).toLowerCase().replace(/\s+/g, '-');
            const absent = row.dataset.status === 'Tidak Hadir' ? ' absent' : '';
            const tip = `${row.dataset.waktu} WIB · ${row.dataset.konseli} (${row.dataset.konselor}) · ${row.dataset.kategori} · ${row.dataset.lokasi}`;
            return `<div class="calendar-event ${cat}${absent}" title="${escapeHtml(tip)}">
                <strong>${escapeHtml(row.dataset.waktu)} · ${escapeHtml(row.dataset.konseli)}</strong>
                <span>${escapeHtml(row.dataset.kategori)} · ${escapeHtml(row.dataset.lokasi)}</span>
            </div>`;
        }).join('');

        if(events.length > MAX){
            const rest = events.slice(MAX).map(r => r.dataset.waktu + ' ' + r.dataset.konseli).join(', ');
            out += `<div class="calendar-more" title="${escapeHtml(rest)}">+${events.length - MAX} lainnya</div>`;
        }
        return out;
    }

    document.getElementById('prevMonth').addEventListener('click', () => {
        calendarDate.setMonth(calendarDate.getMonth() - 1);
        renderCalendar();
    });

    document.getElementById('nextMonth').addEventListener('click', () => {
        calendarDate.setMonth(calendarDate.getMonth() + 1);
        renderCalendar();
    });

    document.getElementById('todayMonth').addEventListener('click', () => {
        calendarDate = new Date(today.getFullYear(), today.getMonth(), 1);
        renderCalendar();
    });

    // =====================================================================
    // KETERSEDIAAN KONSELOR
    // Data awal dari controller ($ketersediaanKonselor). Perubahan (tambah/edit/
    // hapus) baru tersimpan di halaman ini saja, sama seperti jadwal di atas.
    // Nanti saat backend siap: ganti 3 titik bertanda "TODO backend" dgn fetch().
    // =====================================================================
    const dayFull = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    let availability = @json($ketersediaanKonselor ?? []);
    let availEditId = null;

    const availBody = document.getElementById('availBody');
    const availEmpty = document.getElementById('availEmpty');
    const availSearch = document.getElementById('availSearch');
    const availCount = document.getElementById('availCount');
    const availModal = document.getElementById('availModal');
    const availForm = document.getElementById('availForm');
    const aTanggal = document.getElementById('availTanggal');
    const aHari = document.getElementById('availHari');
    const aMulai = document.getElementById('availMulai');
    const aSelesai = document.getElementById('availSelesai');
    const aCatatan = document.getElementById('availCatatan');
    const aError = document.getElementById('availError');

    const dayOf = iso => iso ? dayFull[new Date(iso + 'T00:00:00').getDay()] : '';
    const dotTime = t => String(t || '').replace(':', '.');

    function renderAvail(){
        const q = availSearch.value.trim().toLowerCase();
        const rows = availability
            .filter(a => a.konselor.toLowerCase().includes(q))
            .sort((a, b) => (a.tanggal + a.mulai).localeCompare(b.tanggal + b.mulai));

        availCount.textContent = availability.length + ' slot';
        availEmpty.hidden = rows.length > 0;
        availEmpty.textContent = availability.length
            ? 'Tidak ada ketersediaan yang cocok dengan pencarian.'
            : 'Belum ada ketersediaan konselor. Klik "Tambah Ketersediaan" untuk menambahkan.';

        availBody.innerHTML = rows.map(a => `
            <div class="avail-item${a.tanggal < todayIso ? ' is-past' : ''}" data-id="${escapeHtml(a.id)}">
                <div class="avail-top">
                    <span class="person-name">${escapeHtml(a.konselor)}</span>
                    <div class="action-cell">
                        ${a.tanggal < todayIso ? '' : `<button type="button" class="action-btn action-add" data-act="schedule" title="Buat jadwal dengan slot ini" aria-label="Buat jadwal dengan slot ini"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg></button>`}
                        <button type="button" class="action-btn action-reschedule" data-act="edit" title="Edit" aria-label="Edit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4z"/></svg></button>
                        <button type="button" class="action-btn action-cancel" data-act="delete" title="Hapus" aria-label="Hapus"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v6M14 11v6"/></svg></button>
                    </div>
                </div>
                <div class="avail-when">
                    <span class="admin-pill tone-terjadwal">${dayOf(a.tanggal)}</span>
                    <span class="date-text">${formatDate(a.tanggal)}</span>
                </div>
                <div class="time-text">${dotTime(a.mulai)} – ${dotTime(a.selesai)} WIB</div>
                ${a.catatan ? '<div class="avail-note">' + escapeHtml(a.catatan) + '</div>' : ''}
            </div>`).join('');
    }

    availSearch.addEventListener('input', renderAvail);

    // ---- Dropdown konselor (bisa dicari) untuk modal ketersediaan ----
    const aCombo = document.getElementById('availKonselorCombo');
    const aInput = document.getElementById('availKonselorSearch');
    const aList = document.getElementById('availKonselorList');
    const aListEmpty = document.getElementById('availKonselorEmpty');
    let aValue = '';
    let aActive = -1;

    konselorNames.forEach(n => {
        const li = document.createElement('li');
        li.setAttribute('role', 'option');
        li.dataset.value = n;
        li.textContent = n;
        aList.insertBefore(li, aListEmpty);
    });

    const aItems = () => Array.from(aList.querySelectorAll('li[data-value]'));
    const aVisible = () => aItems().filter(li => !li.hidden);

    function aFilter(q){
        q = q.trim().toLowerCase();
        let n = 0;
        aItems().forEach(li => {
            const ok = li.dataset.value.toLowerCase().includes(q);
            li.hidden = !ok;
            if(ok) n++;
            li.classList.remove('active');
        });
        aListEmpty.hidden = n > 0;
        aActive = -1;
    }
    function aOpen(){ aList.hidden = false; aInput.setAttribute('aria-expanded', 'true'); }
    function aClose(){
        aList.hidden = true;
        aActive = -1;
        aInput.setAttribute('aria-expanded', 'false');
        aInput.value = aValue;
    }
    function aChoose(name){
        aValue = name;
        aItems().forEach(li => li.classList.toggle('selected', li.dataset.value === name));
        aClose();
    }
    function aSet(name){
        aValue = name || '';
        aInput.value = aValue;
        aItems().forEach(li => li.classList.toggle('selected', li.dataset.value === aValue));
    }

    aInput.addEventListener('focus', () => { aFilter(''); aOpen(); aInput.select(); });
    aInput.addEventListener('click', () => { if(aList.hidden){ aFilter(''); aOpen(); } });
    aInput.addEventListener('input', () => { aOpen(); aFilter(aInput.value); });
    aInput.addEventListener('keydown', e => {
        const vis = aVisible();
        if(e.key === 'ArrowDown' || e.key === 'ArrowUp'){
            e.preventDefault();
            if(aList.hidden){ aFilter(''); aOpen(); return; }
            if(!vis.length) return;
            aActive = (aActive + (e.key === 'ArrowDown' ? 1 : -1) + vis.length) % vis.length;
            vis.forEach((li, i) => li.classList.toggle('active', i === aActive));
            vis[aActive].scrollIntoView({ block: 'nearest' });
        }else if(e.key === 'Enter'){
            if(!aList.hidden){
                e.preventDefault();
                const pick = vis[aActive] || (vis.length === 1 ? vis[0] : null);
                if(pick) aChoose(pick.dataset.value);
            }
        }else if(e.key === 'Escape'){
            if(!aList.hidden){ e.stopPropagation(); aClose(); }
        }else if(e.key === 'Tab'){
            aClose();
        }
    });
    aList.addEventListener('click', e => {
        const li = e.target.closest('li[data-value]');
        if(li) aChoose(li.dataset.value);
    });
    document.addEventListener('click', e => { if(!aCombo.contains(e.target)) aClose(); });

    // ---- Modal tambah / edit ----
    function showAvailError(msg){
        aError.textContent = msg || '';
        aError.hidden = !msg;
    }
    function updateHari(){ aHari.value = dayOf(aTanggal.value); }
    aTanggal.addEventListener('input', updateHari);
    aTanggal.addEventListener('change', updateHari);

    function openAvailModal(item){
        availEditId = item ? item.id : null;
        document.getElementById('availModalTitle').textContent = item ? 'Edit Ketersediaan' : 'Tambah Ketersediaan';
        aSet(item ? item.konselor : '');
        aTanggal.value = item ? item.tanggal : '';
        aTanggal.min = item ? '' : todayIso;      // jadwal baru tidak boleh di masa lalu
        aMulai.value = item ? item.mulai : '09:00';
        aSelesai.value = item ? item.selesai : '12:00';
        aCatatan.value = item ? (item.catatan || '') : '';
        updateHari();
        showAvailError('');
        availModal.classList.add('show');
        setTimeout(() => (item ? aTanggal : aInput).focus(), 50);
    }
    function closeAvailModal(){
        availModal.classList.remove('show');
        availEditId = null;
        aClose();
        showAvailError('');
    }

    document.getElementById('addAvailBtn').addEventListener('click', () => openAvailModal(null));
    document.getElementById('closeAvailModal').addEventListener('click', closeAvailModal);
    document.getElementById('cancelAvailModal').addEventListener('click', closeAvailModal);
    availModal.addEventListener('click', e => { if(e.target === availModal) closeAvailModal(); });
    document.addEventListener('keydown', e => { if(e.key === 'Escape') closeAvailModal(); });

    availBody.addEventListener('click', e => {
        const btn = e.target.closest('[data-act]');
        if(!btn) return;
        const id = btn.closest('.avail-item').dataset.id;
        const item = availability.find(a => String(a.id) === id);
        if(!item) return;

        if(btn.dataset.act === 'schedule'){
            openModal({ slot: item });
        }else if(btn.dataset.act === 'edit'){
            openAvailModal(item);
        }else{
            if(!confirm('Hapus ketersediaan ' + item.konselor + ' pada ' + formatDate(item.tanggal) + '?')) return;
            fetch(availabilityUpdateUrl + '/' + id, {method:'DELETE', headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken,'X-Requested-With':'XMLHttpRequest'}})
              .then(r => { if(!r.ok) throw new Error(); return r.json(); })
              .then(() => { availability = availability.filter(a => String(a.id) !== id); renderAvail(); showToast('Ketersediaan berhasil dihapus.'); })
              .catch(() => showToast('Ketersediaan gagal dihapus.'));
        }
    });

    availForm.addEventListener('submit', async e => {
        e.preventDefault();

        const data = {
            konselor: aValue,
            tanggal: aTanggal.value,
            mulai: aMulai.value,
            selesai: aSelesai.value,
            catatan: aCatatan.value.trim()
        };

        if(!data.konselor){ showAvailError('Pilih konselor terlebih dahulu.'); aInput.focus(); return; }
        if(!data.tanggal){ showAvailError('Tanggal wajib diisi.'); aTanggal.focus(); return; }
        if(!data.mulai || !data.selesai){ showAvailError('Jam mulai dan jam selesai wajib diisi.'); return; }
        if(data.selesai <= data.mulai){ showAvailError('Jam selesai harus lebih besar dari jam mulai.'); aSelesai.focus(); return; }

        // Cegah slot bertumpuk untuk konselor & tanggal yang sama.
        const bentrok = availability.find(a =>
            String(a.id) !== String(availEditId) &&
            a.konselor === data.konselor &&
            a.tanggal === data.tanggal &&
            data.mulai < a.selesai && data.selesai > a.mulai
        );
        if(bentrok){
            showAvailError(data.konselor + ' sudah punya ketersediaan ' + dotTime(bentrok.mulai) + ' – ' + dotTime(bentrok.selesai) + ' di tanggal ini.');
            return;
        }

        const isNewAvailability = availEditId === null;
        let saved = null;
        try {
            const response = await fetch(isNewAvailability ? availabilityStoreUrl : (availabilityUpdateUrl + '/' + availEditId), {
                method: isNewAvailability ? 'POST' : 'PUT',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest'},
                body: JSON.stringify({konselor_id: konselorIds[data.konselor], tanggal: data.tanggal, mulai: data.mulai, selesai: data.selesai, catatan: data.catatan})
            });
            if (!response.ok) throw new Error('Gagal menyimpan ketersediaan');
            saved = await response.json();
            if (availEditId === null) availEditId = saved.id;
        } catch (error) {
            showAvailError('Ketersediaan belum tersimpan ke database.');
            return;
        }

        if(!isNewAvailability){
            availability = availability.map(a => String(a.id) === String(availEditId) ? Object.assign({}, a, data) : a);
            showToast('Ketersediaan berhasil diperbarui.');
        }else{
            availability.push(Object.assign({ id: saved.id }, data));
            showToast('Ketersediaan berhasil ditambahkan.');
        }

        closeAvailModal();
        renderAvail();
    });

    // ---- Sinkron jadwal <-> ketersediaan ----
    // Lama satu sesi konseling (menit). Ubah angka ini kalau durasi sesi berbeda.
    const SESSION_MIN = 60;
    const toMin = t => { const [h, m] = String(t).replace('.', ':').split(':'); return (+h) * 60 + (+m || 0); };
    const fromMin = n => { n = Math.min(Number(n), 1439); return String(Math.floor(n / 60)).padStart(2, '0') + ':' + String(n % 60).padStart(2, '0'); };

    renderAvail();

    renderCalendar();
})();
</script>
@endpush
