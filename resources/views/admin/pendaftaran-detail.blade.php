{{--
    SILAKON — Detail Pengajuan
    Simpan sebagai: resources/views/admin/pendaftaran-detail.blade.php

    Dikirim dari AdminPendaftaranController@show:
      $pengajuan  : baris ringkas (kode, nama, kategori, tanggal, status, tone)
      $detail     : isi formulir pengajuan (lihat detailPengajuan() di controller)
      $identitas  : daftar isian identitas sesuai jenis konseli
      $verifikasi : ['waktu' => ..., 'oleh' => ...] atau null
      $tahapan    : urutan tahap status untuk pelacak di bagian atas

    Aturan tombol: "Verifikasi pengajuan" HANYA tampil bila tone = 'menunggu'.
    Status lain (diverifikasi dst.) tidak punya tombol verifikasi.

    Route yang dipakai:
      admin.pendaftaran              -> daftar pendaftaran masuk
      admin.pendaftaran.verifikasi   -> POST verifikasi
--}}
@extends('layouts.admin')

@section('judul', 'Detail Pengajuan')

@push('styles')
<style>
    /* ===== Kerangka halaman ===== */
    .dp-page{
        display:grid; gap:20px; min-width:0;
        /* Jarak sticky dari atas = tinggi header admin (+ jarak napas). Sesuaikan bila header berubah tinggi. */
        --dp-sticky-top:96px;
    }
    /* .admin-card di layout ini tidak punya padding, jadi diberi di sini. */
    .dp-page .admin-card{ margin:0; padding:24px 26px; box-sizing:border-box; }
    @media (max-width:640px){ .dp-page .admin-card{ padding:18px; } }

    .dp-back{
        display:inline-flex; align-items:center; gap:6px;
        font-size:13px; font-weight:500; color:#4B554E; text-decoration:none;
        transition:color .15s ease;
    }
    .dp-back svg{ width:16px; height:16px; }
    .dp-back:hover{ color:#3F6D5D; }

    .dp-layout{
        display:grid;
        grid-template-columns:minmax(0,1fr) 320px;
        gap:20px;
        align-items:start;
    }
    .dp-main{ display:grid; gap:20px; min-width:0; }
    .dp-aside{ display:grid; gap:20px; position:sticky; top:var(--dp-sticky-top); min-width:0; }
    @media (max-width:1024px){
        .dp-layout{ grid-template-columns:minmax(0,1fr); }
        .dp-aside{ position:static; order:-1; }
    }

    /* ===== Pesan (flash) & peringatan ===== */
    .dp-flash{
        display:flex; align-items:flex-start; gap:10px;
        padding:12px 16px; border-radius:12px; font-size:13.5px; line-height:1.5;
        border:1px solid #BBE5CC; background:#EAF7F0; color:#14532D;
    }
    .dp-flash svg{ width:18px; height:18px; flex-shrink:0; margin-top:1px; }
    .dp-flash.is-error{ border-color:#FECACA; background:#FEF2F2; color:#991B1B; }

    .dp-alert{
        display:flex; align-items:center; gap:14px; flex-wrap:wrap;
        padding:16px 18px; border-radius:14px;
        border:1px solid #FECACA; background:#FEF2F2; color:#7F1D1D;
    }
    .dp-alert-icon{
        width:40px; height:40px; border-radius:11px; flex-shrink:0;
        display:flex; align-items:center; justify-content:center;
        background:#FEE2E2; color:#B91C1C;
    }
    .dp-alert-icon svg{ width:22px; height:22px; }
    .dp-alert-text{ flex:1; min-width:220px; }
    .dp-alert-text strong{ display:block; font-size:14.5px; color:#7F1D1D; }
    .dp-alert-text span{ display:block; font-size:13px; margin-top:2px; color:#991B1B; }
    .dp-alert-btn{
        display:inline-flex; align-items:center; gap:8px;
        padding:9px 14px; border-radius:10px; font-size:13px; font-weight:600;
        text-decoration:none; background:#B91C1C; color:#fff;
        transition:background .15s ease;
    }
    .dp-alert-btn:hover{ background:#991B1B; }
    .dp-alert-btn svg{ width:16px; height:16px; }

    /* ===== Kepala: identitas pengajuan + pelacak status ===== */
    .dp-head-top{
        display:flex; align-items:flex-start; justify-content:space-between;
        gap:16px; flex-wrap:wrap;
    }
    .dp-kode{
        display:inline-block;
        font-family:ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-size:13px; letter-spacing:.2px;
        background:#EEF1ED; color:#3F6D5D; padding:3px 8px; border-radius:6px;
    }
    .dp-name{ margin:10px 0 0; font-size:24px; line-height:1.25; font-weight:700; color:#1F2A24; }
    .dp-meta{
        display:flex; flex-wrap:wrap; gap:8px 22px; margin-top:12px;
        font-size:13.5px; color:#6B756E;
    }
    .dp-meta span{ display:inline-flex; align-items:center; gap:7px; }
    .dp-meta svg{ width:16px; height:16px; color:#9AA39C; }

    .dp-status{
        display:inline-flex; align-items:center; padding:6px 14px; border-radius:999px;
        font-size:13px; font-weight:600; white-space:nowrap;
    }
    .dp-status.tone-menunggu     { background:#FEF3C7; color:#92400E; }
    .dp-status.tone-diverifikasi { background:#DBEAFE; color:#1E40AF; }
    .dp-status.tone-terjadwal    { background:#CCFBF1; color:#0F766E; }
    .dp-status.tone-ditugaskan   { background:#EDE9FE; color:#5B21B6; }
    .dp-status.tone-selesai      { background:#DCFCE7; color:#166534; }
    .dp-status.tone-ditolak      { background:#FEE2E2; color:#991B1B; }
    .dp-priority-badge{display:inline-flex;align-items:center;padding:6px 12px;border-radius:999px;font-size:13px;font-weight:700;background:#EEF3F0;color:#2F6A4B;white-space:nowrap}
    .dp-priority-badge.is-tinggi,.dp-priority-badge.is-darurat{background:#FDECEC;color:#A33A35}.dp-priority-badge.is-sedang{background:#FFF4D9;color:#925F08}

    .dp-track-wrap{ margin-top:26px; padding-top:22px; border-top:1px solid #EEF1ED; }
    .dp-track{ display:flex; list-style:none; margin:0; padding:0; }
    .dp-track li{
        flex:1; position:relative; min-width:0;
        display:flex; flex-direction:column; align-items:flex-start; gap:9px;
        font-size:12.5px; color:#8C948D;
    }
    .dp-track li::after{
        content:""; position:absolute; top:11px; left:32px; right:8px; height:2px;
        background:#E2E5E1; border-radius:2px;
    }
    .dp-track li:last-child::after{ display:none; }
    .dp-track li.is-done::after{ background:#3F6D5D; }
    .dp-dot{
        width:24px; height:24px; border-radius:50%; flex-shrink:0;
        display:flex; align-items:center; justify-content:center;
        background:#fff; border:2px solid #D5DAD5; color:#fff;
    }
    .dp-dot svg{ width:13px; height:13px; display:none; }
    .dp-track li.is-done .dp-dot{ background:#3F6D5D; border-color:#3F6D5D; }
    .dp-track li.is-done .dp-dot svg{ display:block; }
    .dp-track li.is-done{ color:#4B554E; }
    .dp-track li.is-current .dp-dot{
        border-color:#3F6D5D; background:#fff;
        box-shadow:0 0 0 4px rgba(63,109,93,.16);
    }
    .dp-track li.is-current .dp-dot::before{
        content:""; width:8px; height:8px; border-radius:50%; background:#3F6D5D;
    }
    .dp-track li.is-current{ color:#1F2A24; font-weight:600; }
    .dp-track li.is-current .dp-dot{ background:#3F6D5D; border-color:#3F6D5D; color:#fff; }
    .dp-track li.is-current .dp-dot::before{ background:#fff; }

    .dp-proof-modal{border:0;border-radius:18px;padding:0;max-width:min(820px,calc(100vw - 32px));width:100%;box-shadow:0 24px 80px rgba(15,23,42,.28);}
    .dp-proof-modal::backdrop{background:rgba(15,23,42,.58);}
    .dp-proof-head{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #edf1ed;}
    .dp-proof-head strong{font-size:16px;color:#1f2a24;}
    .dp-proof-close{border:0;background:#f1f5f3;border-radius:999px;width:32px;height:32px;cursor:pointer;color:#526158;font-size:20px;line-height:1;}
    .dp-proof-body{padding:20px;background:#f8faf9;text-align:center;}
    .dp-proof-body img{max-width:100%;max-height:70vh;object-fit:contain;border-radius:10px;background:#fff;box-shadow:0 4px 14px rgba(15,23,42,.1);}

    .dp-rejected{
        display:flex; align-items:center; gap:10px; margin-top:22px; padding:12px 14px;
        border-radius:12px; background:#FEF2F2; color:#991B1B; font-size:13.5px;
        border:1px solid #FECACA;
    }
    .dp-rejected svg{ width:18px; height:18px; flex-shrink:0; }

    @media (max-width:640px){
        .dp-track{ flex-direction:column; gap:14px; }
        .dp-track li{ flex:none; flex-direction:row; align-items:center; gap:12px; font-size:13.5px; }
        .dp-track li::after{ top:24px; left:11px; right:auto; width:2px; height:14px; }
        .dp-name{ font-size:21px; }
    }

    /* ===== Judul bagian & isi kartu ===== */
    .dp-title{ margin:0; font-size:16px; font-weight:600; color:#1F2A24; }
    .dp-hint{ margin:4px 0 0; font-size:13px; color:#8C948D; }
    .dp-block{ margin-top:22px; padding-top:20px; border-top:1px solid #EEF1ED; }
    .dp-body > .dp-block:first-child{ margin-top:0; padding-top:0; border-top:0; }
    .dp-block-title{ margin:0 0 14px; font-size:13.5px; font-weight:600; color:#4B554E; }
    .dp-body{ margin-top:18px; }

    .dp-fields{
        display:grid; grid-template-columns:repeat(2,minmax(0,1fr));
        gap:18px 28px; margin:0;
    }
    .dp-field{ min-width:0; }
    .dp-field.is-full{ grid-column:1 / -1; }
    .dp-field dt{ font-size:12.5px; color:#8C948D; margin-bottom:3px; }
    .dp-field dd{ margin:0; font-size:14px; line-height:1.5; color:#1F2A24; overflow-wrap:anywhere; }
    .dp-empty{ color:#B3BAB4; }
    .dp-link{
        display:inline-flex; align-items:center; gap:6px;
        color:#1F2A24; text-decoration:none;
        border-bottom:1px solid #CBD3CC; transition:color .15s ease, border-color .15s ease;
    }
    .dp-link:hover{ color:#3F6D5D; border-color:#3F6D5D; }
    .dp-link svg{ width:14px; height:14px; color:#3F6D5D; }
    @media (max-width:640px){
        .dp-fields{ grid-template-columns:minmax(0,1fr); }
    }

    .dp-contacts{ display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
    .dp-contact{
        padding:13px 15px; border-radius:12px;
        background:#F7F9F7; border:1px solid #EEF1ED; min-width:0;
    }
    .dp-contact small{ display:block; font-size:12px; color:#8C948D; margin-bottom:4px; }
    .dp-contact strong{ display:block; font-size:14px; color:#1F2A24; }
    .dp-contact span{ display:block; font-size:13px; color:#6B756E; margin:1px 0 8px; }
    @media (max-width:640px){ .dp-contacts{ grid-template-columns:minmax(0,1fr); } }

    .dp-history-list{ display:grid; gap:10px; }
    .dp-history-row{ display:grid; grid-template-columns:minmax(150px,1.2fr) minmax(130px,1fr) minmax(120px,1fr) auto; gap:14px; align-items:center; padding:13px 14px; border:1px solid #E7ECE8; border-radius:12px; background:#FAFCFA; }
    .dp-history-row.is-current{ border-color:#A9CBB4; background:#F1F8F3; }
    .dp-history-cell small{ display:block; color:#8C948D; font-size:11.5px; margin-bottom:3px; }
    .dp-history-cell strong{ display:block; color:#1F2A24; font-size:13.5px; overflow-wrap:anywhere; }
    .dp-history-badge{ display:inline-block; padding:4px 9px; border-radius:999px; background:#E8F1ED; color:#3F6D5D; font-size:11.5px; font-weight:600; white-space:nowrap; }
    @media(max-width:700px){ .dp-history-row{ grid-template-columns:repeat(2,minmax(0,1fr)); } }

    /* ===== Preferensi sesi ===== */
    .dp-prefs{ display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:14px; }
    .dp-pref{ display:flex; align-items:center; gap:12px; min-width:0; }
    .dp-pref-icon{
        width:38px; height:38px; border-radius:10px; flex-shrink:0;
        display:flex; align-items:center; justify-content:center;
        background:#E8F1ED; color:#3F6D5D;
    }
    .dp-pref-icon svg{ width:18px; height:18px; }
    .dp-pref small{ display:block; font-size:12.5px; color:#8C948D; }
    .dp-pref strong{ display:block; font-size:14px; font-weight:600; color:#1F2A24; }

    /* ===== Tingkat keparahan ===== */
    .dp-meter-bar{ display:grid; grid-template-columns:repeat(3,1fr); gap:6px; }
    .dp-meter-bar span{ height:8px; border-radius:999px; background:#E8ECE8; }
    .dp-meter.tone-rendah .dp-meter-bar span:nth-child(-n+1){ background:#4C9A6B; }
    .dp-meter.tone-sedang .dp-meter-bar span:nth-child(-n+2){ background:#D99A1E; }
    .dp-meter.tone-tinggi .dp-meter-bar span:nth-child(-n+3){ background:#C2413B; }
    .dp-meter-labels{ display:grid; grid-template-columns:repeat(3,1fr); gap:6px; margin-top:8px; font-size:12.5px; color:#8C948D; }
    .dp-meter-labels span.is-on{ color:#1F2A24; font-weight:600; }
    .dp-meter-note{ margin:12px 0 0; font-size:13.5px; color:#4B554E; }

    .dp-chips{ display:flex; flex-wrap:wrap; gap:8px; }
    .dp-chip{
        padding:6px 12px; border-radius:999px; font-size:13px;
        background:#EEF3F0; color:#2F5446; border:1px solid #DCE8E1;
    }

    /* ===== Uraian ===== */
    .dp-quote{
        padding:16px 18px; border-radius:12px; background:#F7F9F7;
        border-left:3px solid #3F6D5D;
        font-size:14.5px; line-height:1.7; color:#1F2A24; max-width:75ch;
    }
    .dp-secret{
        display:flex; align-items:flex-start; gap:8px; margin:10px 0 0;
        font-size:12.5px; line-height:1.5; color:#8C948D;
    }
    .dp-secret svg{ width:14px; height:14px; flex-shrink:0; margin-top:2px; }

    /* ===== Persetujuan & berkas ===== */
    .dp-checks{ display:grid; gap:12px; margin:0; padding:0; list-style:none; }
    .dp-checks li{ display:flex; align-items:flex-start; gap:12px; font-size:14px; color:#1F2A24; min-width:0; }
    .dp-tick{
        width:22px; height:22px; border-radius:50%; flex-shrink:0; margin-top:1px;
        display:flex; align-items:center; justify-content:center;
        background:#DCFCE7; color:#166534;
    }
    .dp-tick svg{ width:13px; height:13px; }
    .dp-tick.is-no{ background:#FEE2E2; color:#991B1B; }
    .dp-checks small{ display:block; font-size:12.5px; color:#8C948D; margin-top:1px; overflow-wrap:anywhere; }

    .dp-file{
        display:flex; align-items:center; gap:14px; padding:12px 14px;
        border:1px solid #E2E5E1; border-radius:12px; background:#fff;
    }
    .dp-file-icon{
        width:42px; height:42px; border-radius:10px; flex-shrink:0;
        display:flex; align-items:center; justify-content:center;
        background:#EEF1ED; color:#3F6D5D;
    }
    .dp-file-icon svg{ width:20px; height:20px; }
    .dp-file-name{ flex:1; min-width:0; }
    .dp-file-name strong{ display:block; font-size:14px; color:#1F2A24; overflow-wrap:anywhere; }
    .dp-file-name small{ display:block; font-size:12.5px; color:#8C948D; margin-top:1px; }
    .dp-btn-ghost{
        display:inline-flex; align-items:center; justify-content:center; gap:8px;
        padding:9px 16px; border-radius:10px; border:1px solid #E2E5E1; background:#fff;
        color:#1F2A24; font-family:inherit; font-size:13px; font-weight:500;
        text-decoration:none; cursor:pointer; white-space:nowrap;
        transition:border-color .15s ease, background .15s ease;
    }
    .dp-btn-ghost:hover{ border-color:#3F6D5D; background:#F7F9F7; }
    .dp-btn-ghost svg{ width:16px; height:16px; }

    /* ===== Panel tindakan (kanan) ===== */
    .dp-action p{ margin:8px 0 18px; font-size:13.5px; line-height:1.6; color:#6B756E; }
    .dp-btn-verify{
        width:100%;
        display:inline-flex; align-items:center; justify-content:center; gap:10px;
        padding:13px 18px; border-radius:11px; border:0; cursor:pointer;
        font-family:inherit; font-size:14.5px; font-weight:600; color:#fff;
        background:linear-gradient(180deg,#4A7E6C 0%,#3F6D5D 100%);
        box-shadow:0 1px 2px rgba(31,42,36,.14), inset 0 1px 0 rgba(255,255,255,.18);
        transition:background .15s ease, transform .15s ease, box-shadow .15s ease;
    }
    .dp-btn-verify svg{ width:19px; height:19px; }
    .dp-btn-verify:hover{
        background:linear-gradient(180deg,#3F6D5D 0%,#335A4C 100%);
        transform:translateY(-1px);
        box-shadow:0 6px 14px rgba(63,109,93,.32);
    }
    .dp-btn-verify:active{ transform:translateY(0); box-shadow:0 1px 2px rgba(31,42,36,.14); }
    .dp-btn-verify:focus-visible,
    .dp-btn-ghost:focus-visible{ outline:none; box-shadow:0 0 0 3px rgba(63,109,93,.28); }
    .dp-btn-verify[disabled]{ opacity:.7; cursor:progress; transform:none; }

    .dp-done{ display:flex; align-items:flex-start; gap:12px; }
    .dp-done .dp-tick{ width:30px; height:30px; margin-top:0; }
    .dp-done .dp-tick svg{ width:17px; height:17px; }
    .dp-done strong{ display:block; font-size:14.5px; color:#1F2A24; }
    .dp-done span{ display:block; margin-top:3px; font-size:13px; line-height:1.55; color:#6B756E; }

    .dp-info{ margin:14px 0 0; display:grid; gap:14px; }
    .dp-info div{ display:flex; justify-content:space-between; gap:16px; font-size:13.5px; }
    .dp-info dt{ color:#8C948D; }
    .dp-info dd{ margin:0; text-align:right; color:#1F2A24; font-weight:500; }
    .dp-tag{
        display:inline-block; padding:2px 10px; border-radius:999px; font-size:12.5px; font-weight:600;
        background:#EEF1ED; color:#4B554E;
    }
    .dp-tag.is-danger{ background:#FEE2E2; color:#991B1B; }

    /* ===== Dialog konfirmasi ===== */
    .dp-dialog{
        border:0; border-radius:18px; padding:28px; color:#1F2A24;
        width:min(440px, calc(100vw - 32px));
        box-shadow:0 28px 64px rgba(31,42,36,.28);
    }
    .dp-dialog::backdrop{ background:rgba(31,42,36,.5); }
    .dp-dialog[open]{ animation:dpPop .18s ease; }
    @keyframes dpPop{ from{ opacity:0; transform:translateY(8px) scale(.98); } to{ opacity:1; transform:none; } }
    @media (prefers-reduced-motion:reduce){ .dp-dialog[open]{ animation:none; } .dp-btn-verify{ transition:none; } }
    .dp-dialog-icon{
        width:46px; height:46px; border-radius:13px; margin-bottom:16px;
        display:flex; align-items:center; justify-content:center;
        background:#E8F1ED; color:#3F6D5D;
    }
    .dp-dialog-icon svg{ width:24px; height:24px; }
    .dp-dialog h3{ margin:0; font-size:18px; font-weight:700; }
    .dp-dialog p{ margin:10px 0 0; font-size:14px; line-height:1.6; color:#4B554E; }
    .dp-dialog-actions{ display:flex; justify-content:flex-end; gap:10px; margin-top:24px; }
    .dp-dialog-actions .dp-btn-verify{ width:auto; padding:10px 20px; font-size:14px; }
    .dp-screening-values{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;margin-top:14px;}
    .dp-screening-values>div{padding:12px;border:1px solid #e1e9e2;border-radius:10px;background:#f8fbf8;}
    .dp-screening-values small{display:block;color:#718078;font-size:11px;margin-bottom:3px;}
    .dp-screening-values strong{display:block;color:#1f3d29;font-size:14px;}
    /* =====================================================================
       RESPONSIF TAMBAHAN (ditaruh paling akhir supaya menimpa aturan dasar)
       ===================================================================== */
    .dp-name{ overflow-wrap:anywhere; }
    .dp-info dd{ overflow-wrap:anywhere; min-width:0; }
    .dp-info dt{ flex-shrink:0; }
    .dp-screening-values strong{ overflow-wrap:anywhere; }

    /* Tablet: kolom kanan jadi grid 2 kolom di atas konten, kartu tindakan selebar penuh */
    @media (max-width:1024px){
        .dp-aside{ grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
        .dp-aside > .dp-action{ grid-column:1 / -1; }
        .dp-aside > .admin-card:last-child:nth-child(odd){ grid-column:1 / -1; }
        .dp-btn-verify, .dp-action .dp-btn-ghost{ max-width:420px; }
    }

    /* Tablet kecil: pelacak status vertikal lebih awal agar label tidak berdesakan */
    @media (max-width:760px){
        .dp-aside{ grid-template-columns:minmax(0,1fr); }
        .dp-btn-verify, .dp-action .dp-btn-ghost{ max-width:none; }
        .dp-track{ flex-direction:column; gap:14px; }
        .dp-track li{ flex:none; flex-direction:row; align-items:center; gap:12px; font-size:13.5px; }
        .dp-track li::after{ top:24px; left:11px; right:auto; width:2px; height:14px; }
        .dp-history-row{ grid-template-columns:repeat(2,minmax(0,1fr)); }
        .dp-history-row > div:last-child{ grid-column:1 / -1; }
    }

    /* HP */
    @media (max-width:640px){
        .dp-page{ gap:16px; }
        .dp-layout, .dp-main{ gap:16px; }
        .dp-head-top{ flex-direction:column-reverse; align-items:flex-start; gap:12px; }
        .dp-meta{ flex-direction:column; gap:8px; }
        .dp-track-wrap{ margin-top:20px; padding-top:18px; }
        .dp-prefs{ grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
        .dp-pref{ gap:10px; align-items:flex-start; }
        .dp-pref-icon{ width:34px; height:34px; }
        .dp-info div{ flex-wrap:wrap; gap:2px 12px; }
        .dp-block{ margin-top:18px; padding-top:16px; }
        .dp-quote{ padding:14px; font-size:14px; }
        .dp-alert{ padding:14px; gap:12px; }
        .dp-alert-text{ min-width:0; flex:1 1 calc(100% - 56px); }
        .dp-alert-btn{ width:100%; justify-content:center; box-sizing:border-box; padding:11px 14px; }
        .dp-proof-body{ padding:12px; }
        .dp-proof-head{ padding:12px 14px; }

        /* dialog konfirmasi: tombol bertumpuk, mudah ditekan */
        .dp-dialog{ padding:20px; }
        .dp-dialog-actions{ flex-direction:column-reverse; align-items:stretch; gap:8px; }
        .dp-dialog-actions .dp-btn-ghost,
        .dp-dialog-actions .dp-btn-verify{ width:100%; padding:12px 16px; }
    }

    @media (max-width:420px){
        .dp-page .admin-card{ padding:14px; }
        .dp-history-row{ grid-template-columns:minmax(0,1fr); gap:10px; }
        .dp-prefs{ grid-template-columns:minmax(0,1fr); }
        .dp-name{ font-size:19px; }
    }
</style>
@endpush

@section('content')
@php
    // ---- Label tampilan (mengikuti pilihan di formulir pengajuan) ----
    $labelJenis = [
        'mahasiswa'           => 'Mahasiswa',
        'alumni'              => 'Alumni',
        'tenaga_kependidikan' => 'Tenaga kependidikan / dosen',
        'wali_mahasiswa'      => 'Wali mahasiswa',
    ];
    $labelMetode = [
        'tatap_muka' => ['Tatap muka', 'Di ruang layanan LPPP UNNES'],
        'daring'     => ['Daring', 'Panggilan video terjadwal'],
    ];
    $labelWaktu = [
        'pagi'  => 'Pagi, 08.00–11.00',
        'siang' => 'Siang, 11.00–14.00',
        'sore'  => 'Sore, 14.00–16.00',
    ];
    $labelKonselor = ['profesional' => 'Profesional', 'tutor_sebaya' => 'Tutor sebaya'];

    // ---- Ikon (path SVG, gaya garis) ----
    $ikon = [
        'kembali' => '<path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>',
        'centang' => '<path d="M20 6 9 17l-5-5"/>',
        'silang'  => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
        'tanggal' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        'label'   => '<path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L2 12V2h10l8.6 8.6a2 2 0 0 1 0 2.8Z"/><circle cx="7" cy="7" r="1.2"/>',
        'orang'   => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'video'   => '<path d="m22 8-6 4 6 4V8Z"/><rect x="2" y="6" width="14" height="12" rx="2"/>',
        'gedung'  => '<path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9v.01M9 12v.01M9 15v.01M9 18v.01"/>',
        'jam'     => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
        'pesan'   => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2Z"/>',
        'kunci'   => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
        'berkas'  => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/>',
        'peringatan' => '<path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4M12 17h.01"/>',
        'wa'      => '<path d="M21 11.5a8.5 8.5 0 0 1-12.6 7.4L3 20.5l1.7-5A8.5 8.5 0 1 1 21 11.5Z"/>',
        'surat'   => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/>',
        'perisai' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/>',
    ];
    $svg = fn ($nama, $tebal = 2) => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="' . $tebal . '" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $ikon[$nama] . '</svg>';

    // Nomor 08xxxx -> tautan WhatsApp 628xxxx
    $wa = fn ($no) => 'https://wa.me/' . preg_replace('/^0/', '62', preg_replace('/\D/', '', (string) $no));

    // ---- Status ----
    $tone      = $pengajuan['tone'];
    $menunggu  = $tone === 'menunggu';
    $ditolak   = $tone === 'ditolak';
    $keys      = array_column($tahapan, 'key');
    $toneKey   = ['menunggu' => 'menunggu_pemeriksaan', 'diverifikasi' => 'diterima', 'terjadwal' => 'terjadwal', 'ditugaskan' => 'ditugaskan', 'selesai' => 'selesai'][$tone] ?? $tone;
    $posisi    = array_search($toneKey, $keys, true);
    $posisi    = $posisi === false ? -1 : $posisi;

    $jenis     = $detail['jenis_konseli'];
    $metode    = $labelMetode[$detail['metode']] ?? [$detail['metode'], ''];
    $darurat   = $detail['darurat'] === 'ya';
@endphp

<div class="dp-page">

    <div>
        <a href="{{ route('admin.pendaftaran') }}" class="dp-back">
            {!! $svg('kembali') !!} Kembali ke pendaftaran masuk
        </a>
    </div>

    @if(session('sukses'))
        <div class="dp-flash" role="status">{!! $svg('centang', 2.4) !!}<span>{{ session('sukses') }}</span></div>
    @endif
    @if(session('galat'))
        <div class="dp-flash is-error" role="alert">{!! $svg('peringatan') !!}<span>{{ session('galat') }}</span></div>
    @endif

    {{-- ============ PERINGATAN KONDISI DARURAT ============ --}}
    @if($darurat)
        <div class="dp-alert" role="alert">
            <span class="dp-alert-icon">{!! $svg('peringatan') !!}</span>
            <div class="dp-alert-text">
                <strong>Konseli menyatakan sedang dalam kondisi darurat</strong>
                <span>Hubungi konseli sesegera mungkin, tidak perlu menunggu proses verifikasi selesai.</span>
            </div>
            <a href="{{ $wa($detail['telepon']) }}" target="_blank" rel="noopener" class="dp-alert-btn">
                {!! $svg('wa') !!} Hubungi lewat WhatsApp
            </a>
        </div>
    @endif

    {{-- ============ KEPALA + PELACAK STATUS ============ --}}
    <div class="admin-card">
        <div class="dp-head-top">
            <div>
                <span class="dp-kode">{{ $pengajuan['kode'] }}</span>
                <h2 class="dp-name">{{ $pengajuan['nama'] }}</h2>
                <div class="dp-meta">
                    <span>{!! $svg('orang') !!}{{ $labelJenis[$jenis] ?? $jenis }}</span>
                    <span>{!! $svg('tanggal') !!}Diajukan {{ $pengajuan['tanggal_lengkap'] ?? $pengajuan['tanggal'] }}</span>
                    <span>{!! $svg('label') !!}{{ $pengajuan['kategori'] }}</span>
                </div>
            </div>
            <span class="dp-status tone-{{ $tone }}">{{ $pengajuan['status'] }}</span>
        </div>

        <div class="dp-track-wrap">
            @if($ditolak)
                <div class="dp-rejected" style="margin-top:0;">
                    {!! $svg('silang') !!}
                    <span>Pengajuan ini ditolak dan tidak diproses ke tahap berikutnya.</span>
                </div>
            @else
                <ol class="dp-track" aria-label="Tahapan pengajuan">
                    @foreach($tahapan as $i => $t)
                        <li class="{{ $i < $posisi ? 'is-done' : ($i === $posisi ? 'is-current' : '') }}" @if($i === $posisi) aria-current="step" @endif>
                            <span class="dp-dot">{!! $svg('centang', 3.2) !!}</span>
                            <span>{{ $t['label'] }}</span>
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>
    </div>

    <div class="dp-layout">

        {{-- ================= KOLOM UTAMA ================= --}}
        <div class="dp-main">

            {{-- ---------- Data konseli ---------- --}}
            <div class="admin-card">
                <h3 class="dp-title">Data konseli</h3>
                <p class="dp-hint">Sesuai isian formulir pengajuan.</p>

                <div class="dp-body">
                    <dl class="dp-fields">
                        @foreach($identitas as $f)
                            <div class="dp-field {{ $f['penuh'] ? 'is-full' : '' }}">
                                <dt>{{ $f['label'] }}</dt>
                                <dd>
                                    @if(blank($f['nilai']))
                                        <span class="dp-empty">Tidak diisi</span>
                                    @elseif($f['tipe'] === 'telepon')
                                        <a href="{{ $wa($f['nilai']) }}" target="_blank" rel="noopener" class="dp-link" title="Buka WhatsApp">
                                            {{ $f['nilai'] }} {!! $svg('wa') !!}
                                        </a>
                                    @elseif($f['tipe'] === 'email')
                                        <a href="mailto:{{ $f['nilai'] }}" class="dp-link">{{ $f['nilai'] }}</a>
                                    @else
                                        {{ $f['nilai'] }}
                                    @endif
                                </dd>
                            </div>
                        @endforeach
                    </dl>

                    @if(!empty($detail['kontak_darurat']))
                        <div class="dp-block">
                            <h4 class="dp-block-title">Kontak darurat</h4>
                            <div class="dp-contacts">
                                @foreach($detail['kontak_darurat'] as $i => $k)
                                    <div class="dp-contact">
                                        <small>Kontak darurat {{ ['I', 'II'][$i] ?? $i + 1 }}</small>
                                        <strong>{{ $k['nama'] }}</strong>
                                        <span>{{ $k['hubungan'] }}</span>
                                        <a href="{{ $wa($k['nomor']) }}" target="_blank" rel="noopener" class="dp-link">
                                            {{ $k['nomor'] }} {!! $svg('wa') !!}
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ---------- Riwayat pengajuan/konseling konseli ---------- --}}
            @if($riwayatKonseling->count() > 0)
                <div class="admin-card">
                    <h3 class="dp-title">Riwayat konseling konseli</h3>
                    <p class="dp-hint">Seluruh pengajuan tetap terpisah, tetapi terhubung ke identitas konseli yang sama.</p>
                    <div class="dp-body">
                        <div class="dp-history-list">
                            @foreach($riwayatKonseling as $riwayat)
                                <div class="dp-history-row {{ $riwayat['is_current'] ? 'is-current' : '' }}">
                                    <div class="dp-history-cell"><small>Kode pengajuan</small><strong>{{ $riwayat['kode'] }}</strong></div>
                                    <div class="dp-history-cell"><small>Konselor</small><strong>{{ $riwayat['konselor'] }}</strong></div>
                                    <div class="dp-history-cell"><small>Tanggal sesi/pengajuan</small><strong>{{ $riwayat['tanggal'] ?: '-' }}@if($riwayat['jam']) · {{ $riwayat['jam'] }}@endif</strong></div>
                                    <div><span class="dp-history-badge">{{ $riwayat['is_current'] ? 'Pengajuan ini' : $riwayat['status'] }}</span></div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- ---------- Kategori & permasalahan ---------- --}}
            <div class="admin-card">
                <h3 class="dp-title">Kategori dan permasalahan</h3>
                <p class="dp-hint">Preferensi sesi bersifat usulan. Jadwal pasti ditentukan setelah verifikasi.</p>

                <div class="dp-body">
                    <div class="dp-block">
                        <h4 class="dp-block-title">Preferensi sesi</h4>
                        <div class="dp-prefs">
                            <div class="dp-pref">
                                <span class="dp-pref-icon">{!! $svg($detail['metode'] === 'daring' ? 'video' : 'gedung') !!}</span>
                                <div><small>Metode</small><strong>{{ $metode[0] }}</strong></div>
                            </div>
                            <div class="dp-pref">
                                <span class="dp-pref-icon">{!! $svg('tanggal') !!}</span>
                                <div><small>Hari</small><strong>{{ ucfirst($detail['hari']) }}</strong></div>
                            </div>
                            <div class="dp-pref">
                                <span class="dp-pref-icon">{!! $svg('jam') !!}</span>
                                <div><small>Waktu</small><strong>{{ $labelWaktu[$detail['waktu']] ?? $detail['waktu'] }}</strong></div>
                            </div>
                            <div class="dp-pref">
                                <span class="dp-pref-icon">{!! $svg('orang') !!}</span>
                                <div><small>Pilihan konselor</small><strong>{{ $labelKonselor[$detail['konselor']] ?? $detail['konselor'] }}</strong></div>
                            </div>
                        </div>
                    </div>

                    <div class="dp-block dp-screening-block">
                        <h4 class="dp-block-title">Screening WHO-5 &amp; rekomendasi SAW</h4>
                        <p class="dp-meter-note">Prioritas dihitung otomatis dari skor WHO-5 dan waktu tunggu menggunakan algoritma SAW, bukan dari pilihan tingkat manual.</p>
                        <div class="dp-screening-values">
                            <div><small>Skor WHO-5</small><strong>{{ $pengajuan['who5_skor'] !== null ? $pengajuan['who5_skor'].' / 100' : 'Belum tersedia' }}</strong></div>
                            <div><small>Nilai SAW</small><strong>{{ $pengajuan['saw_nilai'] !== null ? number_format((float) $pengajuan['saw_nilai'], 4) : 'Belum dihitung' }}</strong></div>
                            <div><small>Prioritas</small><strong>{{ $pengajuan['prioritas'] ?? 'Belum dihitung' }}</strong></div>
                        </div>
                    </div>

                    <div class="dp-block">
                        <h4 class="dp-block-title">Lingkup hambatan</h4>
                        <div class="dp-chips">
                            @foreach($detail['hambatan'] as $h)
                                <span class="dp-chip">{{ $h }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div class="dp-block">
                        <h4 class="dp-block-title">Uraian permasalahan</h4>
                        <div class="dp-secret" style="margin-top:8px;">
                            {!! $svg('kunci') !!}
                            <span>Uraian permasalahan bersifat rahasia dan tidak dapat dilihat oleh Admin. Uraian hanya dapat diakses oleh konselor yang menangani sesuai kewenangan layanan.</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ---------- Persetujuan & berkas ---------- --}}
            <div class="admin-card">
                <h3 class="dp-title">Persetujuan dan berkas</h3>
                <p class="dp-hint">Periksa bukti ini sebelum memverifikasi pengajuan.</p>

                <div class="dp-body">
                    <ul class="dp-checks">
                        <li>
                            <span class="dp-tick {{ $detail['persetujuan'] ? '' : 'is-no' }}">{!! $svg($detail['persetujuan'] ? 'centang' : 'silang', 3) !!}</span>
                            <div>
                                {{ $detail['persetujuan'] ? 'Konseli menyetujui pernyataan data benar dan kesediaan mengikuti proses layanan' : 'Konseli belum menyetujui pernyataan' }}
                            </div>
                        </li>
                        <li>
                            <span class="dp-tick {{ $detail['email_terverifikasi'] ? '' : 'is-no' }}">{!! $svg($detail['email_terverifikasi'] ? 'centang' : 'silang', 3) !!}</span>
                            <div>
                                {{ $detail['email_terverifikasi'] ? 'Email sudah diverifikasi dengan kode' : 'Email belum diverifikasi' }}
                                <small>{{ $detail['email'] }}</small>
                            </div>
                        </li>
                    </ul>

                </div>
            </div>

        </div>

        {{-- ================= KOLOM KANAN ================= --}}
        <aside class="dp-aside">

            @if($menunggu)
                <div class="admin-card dp-action">
                    <h3 class="dp-title">Verifikasi pengajuan</h3>
                    <p>Periksa data konseli dan persetujuan layanan. Setelah diverifikasi, konseli masuk ke daftar penjadwalan.</p>
                    <button type="button" class="dp-btn-verify" data-open-verify>
                        {!! $svg('centang', 2.6) !!} Verifikasi pengajuan
                    </button>
                    <button type="button" class="dp-btn-ghost" data-open-reject style="width:100%;margin-top:10px;color:#A33A35;border-color:#E8C4C1;">Tolak pengajuan</button>
                </div>
            @elseif($ditolak)
                <div class="admin-card">
                    <div class="dp-done">
                        <span class="dp-tick is-no">{!! $svg('silang', 3) !!}</span>
                        <div>
                            <strong>Pengajuan ditolak</strong>
                            <span>Pengajuan ini tidak dapat diverifikasi.</span>
                        </div>
                    </div>
                </div>
            @else
                <div class="admin-card">
                    <div class="dp-done">
                        <span class="dp-tick">{!! $svg('centang', 3) !!}</span>
                        <div>
                            <strong>Sudah diverifikasi</strong>
                            <span>
                                @if($verifikasi)
                                    Diverifikasi pada {{ $verifikasi['waktu'] }} oleh {{ $verifikasi['oleh'] }}.
                                @else
                                    Pengajuan ini sudah melewati tahap verifikasi.
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            <div class="admin-card">
                <h3 class="dp-title">Informasi pengajuan</h3>
                <dl class="dp-info">
                    <div><dt>Diajukan pada</dt><dd>{{ $pengajuan['tanggal_lengkap'] ?? $pengajuan['tanggal'] }}</dd></div>
                    <div><dt>Kategori</dt><dd>{{ $pengajuan['kategori'] }}</dd></div>
                    <div><dt>Jenis konseli</dt><dd>{{ $labelJenis[$jenis] ?? $jenis }}</dd></div>
                    @if($detail['darurat_digunakan'] ?? false)
                    <div>
                        <dt>Kondisi darurat</dt>
                        <dd><span class="dp-tag {{ $darurat ? 'is-danger' : '' }}">{{ $darurat ? 'Ya' : 'Tidak' }}</span></dd>
                    </div>
                    @endif
                </dl>
            </div>

            <div class="admin-card" style="border:1px solid #DCE8DF;background:linear-gradient(180deg,#F7FBF8 0%,#fff 100%);">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;">
                    <div>
                        <h3 class="dp-title" style="margin-bottom:4px;">Screening WHO-5 & rekomendasi SAW</h3>
                        <p class="dp-hint" style="margin:0;">Nilai ini membantu mengurutkan pemeriksaan pengajuan dan bukan diagnosis psikologis.</p>
                    </div>
                    <span class="dp-priority-badge is-{{ strtolower($pengajuan['prioritas'] ?? 'belum') }}">Prioritas {{ $pengajuan['prioritas'] ?? 'Belum dihitung' }}</span>
                </div>
                <dl class="dp-info" style="margin-top:16px;">
                    <div><dt>Skor WHO-5</dt><dd><strong>{{ $pengajuan['who5_skor'] !== null ? $pengajuan['who5_skor'].' / 100' : 'Belum tersedia' }}</strong></dd></div>
                    <div><dt>Nilai SAW</dt><dd><strong>{{ $pengajuan['saw_nilai'] !== null ? number_format((float) $pengajuan['saw_nilai'], 4) : 'Belum dihitung' }}</strong></dd></div>
                </dl>
            </div>

        </aside>
    </div>
</div>

{{-- ============ DIALOG KONFIRMASI VERIFIKASI (hanya saat menunggu) ============ --}}
@if($menunggu)
    <dialog class="dp-dialog" id="dpVerifyDialog" aria-labelledby="dpVerifyTitle">
        <form method="POST" action="{{ route('admin.pendaftaran.verifikasi', $pengajuan['kode']) }}" id="dpVerifyForm">
            @csrf
            <div class="dp-dialog-icon">{!! $svg('perisai') !!}</div>
            <h3 id="dpVerifyTitle">Verifikasi pengajuan ini?</h3>
            <p>
                Pengajuan <strong>{{ $pengajuan['kode'] }}</strong> atas nama <strong>{{ $pengajuan['nama'] }}</strong>
                akan berstatus Diverifikasi dan muncul di daftar konseli terverifikasi pada menu Penjadwalan.
            </p>
            <label for="alasanPenolakan" style="display:block;text-align:left;font-size:13px;font-weight:600;margin:14px 0 6px;">Alasan penolakan (wajib jika memilih Tolak)</label>
            <textarea id="alasanPenolakan" name="alasan_penolakan" rows="3" style="width:100%;box-sizing:border-box;border:1px solid #E2E5E1;border-radius:10px;padding:10px;font:inherit;" placeholder="Jelaskan alasan penolakan"></textarea>
            <input type="hidden" name="keputusan" value="diterima" id="keputusanPengajuan">
            <div class="dp-dialog-actions">
                <button type="button" class="dp-btn-ghost" data-close-verify>Batal</button>
                <button type="submit" class="dp-btn-ghost" data-submit-reject style="color:#A33A35;border-color:#E8C4C1;">Tolak</button>
                <button type="submit" class="dp-btn-verify">{!! $svg('centang', 2.6) !!} Verifikasi</button>
            </div>
        </form>
    </dialog>
@endif


@endsection

@push('scripts')
@if($menunggu)
<script>
    (function () {
        var dialog = document.getElementById('dpVerifyDialog');
        var form   = document.getElementById('dpVerifyForm');
        var opener = document.querySelector('[data-open-verify]');
        var rejectOpener = document.querySelector('[data-open-reject]');
        var decision = document.getElementById('keputusanPengajuan');
        if (!dialog || !form || !opener) return;

        opener.addEventListener('click', function () {
            decision.value = 'diterima';
            document.getElementById('dpVerifyTitle').textContent = 'Verifikasi pengajuan ini?';
            if (typeof dialog.showModal === 'function') {
                dialog.showModal();
            } else if (window.confirm('Verifikasi pengajuan ini?')) {
                form.submit();
            }
        });

        rejectOpener?.addEventListener('click', function () {
            decision.value = 'ditolak';
            document.getElementById('dpVerifyTitle').textContent = 'Tolak pengajuan ini?';
            if (typeof dialog.showModal === 'function') dialog.showModal();
        });

        form.querySelector('[data-submit-reject]')?.addEventListener('click', function () {
            decision.value = 'ditolak';
        });

        dialog.querySelector('[data-close-verify]').addEventListener('click', function () {
            dialog.close();
        });

        // Klik di luar kotak dialog (area gelap) menutup dialog.
        dialog.addEventListener('click', function (e) {
            if (e.target === dialog) dialog.close();
        });

        // Cegah klik ganda saat dikirim.
        form.addEventListener('submit', function () {
            var btn = form.querySelector('button[type="submit"]');
            if (btn) btn.disabled = true;
        });
    })();
</script>
@endif
@endpush