@extends('layouts.app')
@section('title', 'Verifikasi Pengajuan Wali — SILAKON')
@section('content')
<div style="max-width:680px;margin:70px auto;padding:24px;">
    <div style="background:#fff;border:1px solid #DCE8DF;border-radius:18px;padding:32px;box-shadow:0 12px 30px rgba(31,42,36,.08);text-align:center;">
        <div style="font-size:38px;color:#3F6D5D;margin-bottom:12px;">✓</div>
        <h1 style="margin:0 0 10px;color:#1F2A24;font-size:25px;">Tautan persetujuan telah dikirim</h1>
        <p style="color:#657169;line-height:1.7;margin:0;">Kami mengirim tautan ke <strong>{{ $email }}</strong>. Mahasiswa perlu membuka tautan tersebut, menyetujui pengajuan, dan mengisi WHO-5 secara mandiri sebelum pengajuan diteruskan ke Admin.</p>
    </div>
</div>
@endsection
