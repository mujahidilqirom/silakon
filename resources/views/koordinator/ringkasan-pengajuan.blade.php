@extends('layouts.koordinator')
@section('judul', 'Ringkasan Pengajuan')
@section('content')
<style>
.rk{container-type:inline-size;width:100%}.rk-head{margin-bottom:22px}.rk-head h1{margin:0;color:#1a2321;font-size:24px}.rk-head p{margin:6px 0 0;color:#7d8582;font-size:14px}.rk-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:22px}.rk-stat{background:#fff;border:1px solid #e3e7e1;border-radius:14px;padding:16px 18px}.rk-stat small{display:block;color:#7d8582;font-size:12px}.rk-stat b{display:block;margin-top:5px;font-size:25px;color:#315b3a}.rk-stat:nth-child(2) b{color:#a06b13}.rk-stat:nth-child(3) b{color:#287b68}.rk-stat:nth-child(4) b{color:#b04444}.rk-info{background:#eef5ee;border:1px solid #cfe0cf;border-radius:12px;padding:13px 16px;margin-bottom:18px;color:#37583d;font-size:13px;line-height:1.5}.rk-tools{display:grid;grid-template-columns:minmax(0,360px) minmax(0,190px) minmax(0,210px);gap:12px;margin-bottom:16px}.rk-input,.rk-select{width:100%;min-width:0;box-sizing:border-box;text-overflow:ellipsis;height:42px;border:1px solid #dfe3de;border-radius:10px;background:#fff;padding:0 13px;font:inherit;font-size:13px;color:#26342d}.rk-card{background:#fff;border:1px solid #e3e7e1;border-radius:16px;overflow:auto;overscroll-behavior-x:contain;-webkit-overflow-scrolling:touch}.rk-table{width:100%;min-width:930px;border-collapse:collapse}.rk-table th{padding:13px 15px;text-align:left;background:#f6f8f5;color:#68746e;font-size:11px;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap}.rk-table td{padding:13px 15px;border-top:1px solid #eceeea;color:#4a524f;font-size:13px;vertical-align:middle}.rk-table tr[hidden]{display:none}.rk-code{color:#3e704a;font-weight:700}.rk-name{font-weight:700;color:#1a2321}.rk-pill{display:inline-block;padding:4px 9px;border-radius:999px;font-size:11px;font-weight:700;white-space:nowrap}.rk-green{background:#d9f5e3;color:#1f6b3f}.rk-yellow{background:#fdefb9;color:#8a3f0b}.rk-gray{background:#eef0ef;color:#68746e}.rk-result{color:#344d3b}.rk-dash{color:#a3aaa6}.rk-empty{padding:32px;text-align:center;color:#7d8582}.rk-select option{font-size:13px}.rk-hint{display:none}.rk-foot{display:flex;justify-content:flex-end;gap:12px;padding-top:12px;color:#7d8582;font-size:12px}
/* ===== RESPONSIVE (mengikuti lebar area konten, bukan lebar layar) ===== */
@container (max-width:880px){
.rk-table{min-width:780px}
.rk-table th{padding:10px 12px;font-size:10.5px}
.rk-table td{padding:10px 12px;font-size:12.5px;white-space:nowrap}
.rk-table td:nth-child(7){white-space:normal;min-width:150px;max-width:220px}
/* kolom Kode tetap terlihat saat tabel digeser */
.rk-table th:first-child,.rk-table td:first-child{position:sticky;left:0;z-index:1;box-shadow:inset -1px 0 #e3e7e1}
.rk-table td:first-child{background:#fff}
.rk-table th:first-child{background:#f6f8f5;z-index:2}
.rk-hint{display:inline}
.rk-foot{justify-content:space-between}
}
@container (max-width:800px){
.rk-stats{grid-template-columns:repeat(2,minmax(0,1fr))}
.rk-tools{grid-template-columns:repeat(2,minmax(0,1fr))}
.rk-input{grid-column:1/-1}
}
@container (max-width:560px){
.rk-head{margin-bottom:18px}
.rk-head h1{font-size:20px;line-height:1.3}
.rk-head p{font-size:13.5px;line-height:1.5}
.rk-stats{gap:10px;margin-bottom:18px}
.rk-stat{padding:14px}
.rk-stat b{font-size:22px}
.rk-info{padding:12px 14px;font-size:12.5px;margin-bottom:16px}
.rk-tools{gap:10px}
.rk-input,.rk-select{height:40px;font-size:13px}
.rk-select{padding:0 8px 0 10px}
.rk-foot{font-size:11.5px;gap:8px}
}
</style>
<div class="rk">
<div class="rk-head"><h1>Ringkasan Pengajuan Konseling</h1><p>Statistik dan status pengajuan konseli tanpa membuka isi catatan konseling.</p></div>
<div class="rk-stats">@foreach($statistik as $s)<div class="rk-stat"><small>{{ $s['label'] }}</small><b>{{ $s['nilai'] }}</b></div>@endforeach</div>
<div class="rk-info">Koordinator dapat memantau status, hasil, dan kelengkapan proses. Isi catatan konseling bersifat rahasia dan hanya dapat diakses konselor yang menangani.</div>
<div class="rk-tools"><input class="rk-input" id="rk-q" type="search" placeholder="Cari kode, nama konseli, atau konselor..." autocomplete="off"><select class="rk-select" id="rk-status"><option value="">Semua Status</option>@foreach($opsiStatus as $o)<option value="{{ $o }}">{{ $o }}</option>@endforeach</select><select class="rk-select" id="rk-konselor"><option value="">Semua Konselor</option>@foreach($opsiKonselor as $o)<option value="{{ $o }}">{{ $o }}</option>@endforeach<option value="__none">Belum ditugaskan</option></select></div>
<div class="rk-card">@if($pengajuan->isEmpty())<div class="rk-empty">Belum ada pengajuan konseling.</div>@else<table class="rk-table"><thead><tr><th>Kode</th><th>Konseli</th><th>Konselor</th><th>Tanggal Pengajuan</th><th>Status</th><th>Catatan</th><th>Hasil</th><th>Survei</th></tr></thead><tbody id="rk-body">@foreach($pengajuan as $p)<tr data-search="{{ strtolower($p['kode'].' '.$p['konseli'].' '.($p['konselor'] ?? '')) }}" data-status="{{ $p['status'] }}" data-konselor="{{ $p['konselor'] ?? '__none' }}"><td><span class="rk-code">{{ $p['kode'] }}</span></td><td class="rk-name">{{ $p['konseli'] }}</td><td>{{ $p['konselor'] ?: '—' }}</td><td>{{ $p['tanggal'] }}</td><td><span class="rk-pill {{ $p['status_tone']==='hijau'?'rk-green':($p['status_tone']==='abu'?'rk-gray':'rk-yellow') }}">{{ $p['status'] }}</span></td><td><span class="rk-pill {{ $p['catatan']==='sudah'?'rk-green':'rk-yellow' }}">{{ $p['catatan']==='sudah'?'Sudah':'Belum' }}</span></td><td>{!! $p['hasil'] ? '<span class="rk-result">'.e($p['hasil']).'</span>' : '<span class="rk-dash">—</span>' !!}</td><td>@if($p['survei']==='sudah')<span class="rk-pill rk-green">Sudah</span>@elseif($p['survei']==='belum')<span class="rk-pill rk-gray">Belum</span>@else<span class="rk-dash">—</span>@endif</td></tr>@endforeach<tr id="rk-empty" hidden><td colspan="8" class="rk-empty">Tidak ada data yang cocok.</td></tr></tbody></table>@endif</div><div class="rk-foot"><span class="rk-hint">Geser tabel untuk melihat kolom lain</span><span id="rk-count"></span></div>
</div><script>(function(){const body=document.getElementById('rk-body'),q=document.getElementById('rk-q'),st=document.getElementById('rk-status'),ko=document.getElementById('rk-konselor'),empty=document.getElementById('rk-empty'),count=document.getElementById('rk-count');if(!body)return;function filter(){let n=0,query=(q.value||'').toLowerCase(),status=st.value,konselor=ko.value;body.querySelectorAll('tr[data-search]').forEach(r=>{let ok=(!query||r.dataset.search.includes(query))&&(!status||r.dataset.status===status)&&(!konselor||r.dataset.konselor===konselor);r.hidden=!ok;if(ok)n++});if(empty)empty.hidden=n>0;count.textContent=n+' pengajuan ditampilkan'}[q,st,ko].forEach(x=>x.addEventListener('input',filter));filter()})();</script>
@endsection