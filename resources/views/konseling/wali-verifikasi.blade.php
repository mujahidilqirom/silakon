@extends('layouts.app')
@section('title', 'Persetujuan Pengajuan Konseling — SILAKON')
@section('content')
<div style="max-width:760px;margin:40px auto;padding:20px;">
<form method="POST" action="{{ route('konseling.wali.selesaikan', $pending->token) }}" style="background:#fff;border:1px solid #DCE8DF;border-radius:18px;padding:28px;box-shadow:0 12px 30px rgba(31,42,36,.08);">
@csrf
<h1 style="margin:0;color:#1F2A24;font-size:24px;">Persetujuan Pengajuan Konseling</h1>
<p style="color:#657169;line-height:1.6;">Wali Anda mengajukan konseling terkait Anda. Periksa informasi berikut, lalu isi WHO-5 berdasarkan kondisi Anda selama dua minggu terakhir.</p>
<div style="background:#F5F9F6;border-radius:12px;padding:15px;margin:18px 0;color:#34463B;line-height:1.7;">
    <strong>Nama mahasiswa:</strong> {{ data_get($pending->data, 'wali_mahasiswa_nama', '-') }}<br>
    <strong>NIM:</strong> {{ data_get($pending->data, 'wali_mahasiswa_nim', '-') }}<br>
    <strong>Hubungan pengaju:</strong> {{ data_get($pending->data, 'wali_hubungan', '-') }}
</div>
<h2 style="font-size:17px;color:#1F2A24;margin-top:24px;">WHO-5 Well-Being Screening</h2>
<p style="font-size:13px;color:#657169;">0 = Tidak pernah, 5 = Selalu</p>
@php($questions=['Saya merasa ceria dan bersemangat.','Saya merasa tenang dan santai.','Saya merasa aktif dan penuh energi.','Saya bangun dengan perasaan segar dan beristirahat cukup.','Kehidupan sehari-hari saya dipenuhi hal-hal yang menarik bagi saya.'])
@foreach($questions as $i => $question)
<fieldset style="border:1px solid #E1EAE3;border-radius:12px;padding:14px;margin:12px 0;"><legend style="font-size:13px;color:#33463A;padding:0 5px;">{{ $i+1 }}. {{ $question }}</legend><div style="display:grid;grid-template-columns:repeat(6,1fr);gap:7px;">@foreach(range(0,5) as $v)<label style="text-align:center;font-size:12px;color:#526158;"><input type="radio" name="who5[{{ $i }}]" value="{{ $v }}" required> {{ $v }}</label>@endforeach</div></fieldset>
@endforeach
<label style="display:flex;gap:9px;align-items:flex-start;margin:20px 0;color:#34463B;font-size:13px;line-height:1.5;"><input type="checkbox" name="persetujuan_wali" value="1" required> Saya menyetujui pengajuan konseling ini dan mengonfirmasi bahwa jawaban WHO-5 saya isi sendiri.</label>
<button type="submit" style="width:100%;border:0;border-radius:10px;padding:13px;background:#3F6D5D;color:#fff;font-weight:700;font-size:14px;cursor:pointer;">Setujui dan Kirim Pengajuan</button>
</form>
</div>
@endsection
