{{--
    SILAKON — Pengaturan Sistem (Koordinator)
    Simpan sebagai: resources/views/koordinator/pengaturan.blade.php
    Data dikirim dari KoordinatorController@pengaturan.
--}}
@extends('layouts.koordinator')

@section('judul', 'Pengaturan')
@section('subjudul', now()->translatedFormat('l, d F Y'))

@section('content')

@if(session('sukses'))
    <div class="k-alert-sukses" role="status">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
        <span>{{ session('sukses') }}</span>
    </div>
@endif

@if($errors->any())
    <div class="k-alert-error" role="alert">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
        <span>Pengaturan belum tersimpan. Periksa kembali kolom yang ditandai merah.</span>
    </div>
@endif

<form action="{{ route('koordinator.pengaturan.save') }}" method="POST" class="k-pengaturan-form" novalidate>
    @csrf

    {{-- ============ HEAD ============ --}}
    <div class="k-head-row">
        <div class="k-page-head">
            <h2>Pengaturan Sistem</h2>
            <p>Atur kontak, prioritas antrean, dan keamanan sesi.</p>
        </div>
        <button type="submit" class="k-btn k-btn-save k-btn-head">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15.2 3a2 2 0 0 1 1.4.6l3.8 3.8a2 2 0 0 1 .6 1.4V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/><path d="M17 21v-7a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v7"/><path d="M7 3v4a1 1 0 0 0 1 1h7"/></svg>
            Simpan Pengaturan
        </button>
    </div>

    {{-- ============ KONTAK DARURAT & JAM LAYANAN ============ --}}
    <section class="k-card k-pengaturan-blok" aria-labelledby="h-kontak">
        <header class="k-pengaturan-head">
            <span class="k-icon-badge tone-green" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M13.832 16.568a1 1 0 0 0 1.213-.303l.355-.465A2 2 0 0 1 17 15h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2A18 18 0 0 1 2 4a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-.8 1.6l-.468.351a1 1 0 0 0-.292 1.233 14 14 0 0 0 6.392 6.384"/></svg>
            </span>
            <div>
                <h3 id="h-kontak">Kontak Darurat &amp; Jam Layanan</h3>
                <p>Informasi kontak yang ditampilkan kepada konseli</p>
            </div>
        </header>

        <div class="k-grid-2">
            <div class="k-field @error('wa_admin') has-error @enderror">
                <label for="wa_admin">Nomor WhatsApp Admin</label>
                <input type="tel" inputmode="numeric" autocomplete="off" id="wa_admin" name="wa_admin" class="k-input" placeholder="08xxxxxxxxxx" value="{{ old('wa_admin', $pengaturan['wa_admin']) }}">
                <small>Format: 08xxxxxxxxxx (tanpa spasi atau tanda hubung)</small>
                @error('wa_admin')<small class="k-field-error">{{ $message }}</small>@enderror
            </div>

            <div class="k-field @error('wa_darurat') has-error @enderror">
                <label for="wa_darurat">Nomor Darurat Tambahan <em>(opsional)</em></label>
                <input type="tel" inputmode="numeric" autocomplete="off" id="wa_darurat" name="wa_darurat" class="k-input" placeholder="08xxxxxxxxxx" value="{{ old('wa_darurat', $pengaturan['wa_darurat']) }}">
                <small>Dikosongkan jika tidak ada nomor tambahan.</small>
                @error('wa_darurat')<small class="k-field-error">{{ $message }}</small>@enderror
            </div>
        </div>

        <div class="k-field @error('jam_layanan') has-error @enderror">
            <label for="jam_layanan">Jam Layanan</label>
            <input type="text" id="jam_layanan" name="jam_layanan" class="k-input" value="{{ old('jam_layanan', $pengaturan['jam_layanan']) }}">
            <small>Ditampilkan pada form kondisi darurat.</small>
            @error('jam_layanan')<small class="k-field-error">{{ $message }}</small>@enderror
        </div>
    </section>

    {{-- ============ PRIORITAS WHO-5 & SAW ============ --}}
    <section class="k-card k-pengaturan-blok" aria-labelledby="h-saw">
        <header class="k-pengaturan-head">
            <span class="k-icon-badge tone-purple" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/></svg>
            </span>
            <div>
                <h3 id="h-saw">Prioritas WHO-5 dan Waktu Tunggu</h3>
                <p>Bobot dan batas kategori prioritas (metode SAW). Dapat diubah oleh Koordinator.</p>
            </div>
        </header>

        <div class="k-subjudul">Bobot penilaian</div>
        <div class="k-grid-2">
            <div class="k-field @error('saw_bobot_who5') has-error @enderror">
                <label for="saw_bobot_who5">Bobot WHO-5</label>
                <div class="k-input-suffix">
                    <input type="number" inputmode="numeric" id="saw_bobot_who5" name="saw_bobot_who5" class="k-input" min="0" max="100" value="{{ old('saw_bobot_who5', $pengaturan['saw_bobot_who5']) }}">
                    <span>%</span>
                </div>
                <small>Semakin rendah skor WHO-5, semakin tinggi nilai prioritas.</small>
                @error('saw_bobot_who5')<small class="k-field-error">{{ $message }}</small>@enderror
            </div>

            <div class="k-field @error('saw_bobot_aging') has-error @enderror">
                <label for="saw_bobot_aging">Bobot Waktu Tunggu (Aging)</label>
                <div class="k-input-suffix">
                    <input type="number" inputmode="numeric" id="saw_bobot_aging" name="saw_bobot_aging" class="k-input" min="0" max="100" value="{{ old('saw_bobot_aging', $pengaturan['saw_bobot_aging']) }}">
                    <span>%</span>
                </div>
                <small>Total bobot WHO-5 dan Aging harus 100%.</small>
                @error('saw_bobot_aging')<small class="k-field-error">{{ $message }}</small>@enderror
            </div>
        </div>

        <div class="k-total" id="saw-total" data-ok="false" aria-live="polite">
            <span class="k-total-label">Total bobot</span>
            <span class="k-total-bar"><i id="saw-total-bar"></i></span>
            <strong id="saw-total-nilai">0%</strong>
            <span class="k-total-note" id="saw-total-note"></span>
        </div>

        <div class="k-subjudul">Batas kategori</div>
        <div class="k-grid-2">
            <div class="k-field @error('saw_batas_tinggi') has-error @enderror">
                <label for="saw_batas_tinggi">Batas Prioritas Tinggi</label>
                <input type="number" inputmode="numeric" id="saw_batas_tinggi" name="saw_batas_tinggi" class="k-input" min="1" max="100" value="{{ old('saw_batas_tinggi', $pengaturan['saw_batas_tinggi']) }}">
                <small>Contoh: 67 berarti nilai SAW minimal 0,67 masuk Tinggi.</small>
                @error('saw_batas_tinggi')<small class="k-field-error">{{ $message }}</small>@enderror
            </div>

            <div class="k-field @error('saw_batas_sedang') has-error @enderror">
                <label for="saw_batas_sedang">Batas Prioritas Sedang</label>
                <input type="number" inputmode="numeric" id="saw_batas_sedang" name="saw_batas_sedang" class="k-input" min="0" max="99" value="{{ old('saw_batas_sedang', $pengaturan['saw_batas_sedang']) }}">
                <small>Di bawah batas ini menjadi Rendah. Harus lebih kecil dari Batas Tinggi.</small>
                @error('saw_batas_sedang')<small class="k-field-error">{{ $message }}</small>@enderror
            </div>
        </div>

        <div class="k-rentang" aria-live="polite">
            <span class="k-chip chip-rendah">Rendah <b id="rng-rendah">&lt; 0</b></span>
            <span class="k-chip chip-sedang">Sedang <b id="rng-sedang">0 – 0</b></span>
            <span class="k-chip chip-tinggi">Tinggi <b id="rng-tinggi">≥ 0</b></span>
        </div>
    </section>

    {{-- ============ SESI TIMEOUT ============ --}}
    <section class="k-card k-pengaturan-blok" aria-labelledby="h-sesi">
        <header class="k-pengaturan-head">
            <span class="k-icon-badge tone-yellow" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </span>
            <div>
                <h3 id="h-sesi">Sesi Timeout</h3>
                <p>Batas waktu sesi pengguna yang tidak aktif</p>
            </div>
        </header>

        <div class="k-field @error('sesi_timeout_menit') has-error @enderror">
            <label for="sesi_timeout_menit">Sesi berakhir setelah</label>
            <div class="k-inline-input">
                <input type="number" inputmode="numeric" id="sesi_timeout_menit" name="sesi_timeout_menit" class="k-input k-input-sm" min="5" max="120" value="{{ old('sesi_timeout_menit', $pengaturan['sesi_timeout_menit']) }}">
                <span>menit tidak aktif</span>
            </div>
            <small>Nilai default: 30 menit. Rentang yang diizinkan: 5–120 menit.</small>
            @error('sesi_timeout_menit')<small class="k-field-error">{{ $message }}</small>@enderror
        </div>
    </section>

    {{-- ============ INFORMASI KEAMANAN ============ --}}
    <section class="k-card k-pengaturan-blok" aria-labelledby="h-aman">
        <header class="k-pengaturan-head">
            <span class="k-icon-badge tone-purple" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/></svg>
            </span>
            <div>
                <h3 id="h-aman">Informasi Keamanan</h3>
                <p>Catatan penting tentang keamanan sistem</p>
            </div>
        </header>

        <ul class="k-info-list">
            <li>Semua data konseli dienkripsi sebelum disimpan di server.</li>
            <li>Log aktivitas dicatat untuk tindakan administratif penting seperti perubahan data dan ekspor laporan.</li>
            <li>Reset password hanya dapat dilakukan oleh Admin atau Koordinator.</li>
            <li>Akses ke data konseli dibatasi berdasarkan peran pengguna (role-based access control).</li>
        </ul>
    </section>

    {{-- ============ FOOTER SIMPAN (menempel di bawah layar) ============ --}}
    <div class="k-pengaturan-footer">
        <p>Perubahan baru berlaku setelah disimpan.</p>
        <button type="submit" class="k-btn k-btn-save">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15.2 3a2 2 0 0 1 1.4.6l3.8 3.8a2 2 0 0 1 .6 1.4V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/><path d="M17 21v-7a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v7"/><path d="M7 3v4a1 1 0 0 0 1 1h7"/></svg>
            Simpan Semua Pengaturan
        </button>
    </div>
</form>

<script>
(function () {
    var who   = document.getElementById('saw_bobot_who5');
    var aging = document.getElementById('saw_bobot_aging');
    var tinggi = document.getElementById('saw_batas_tinggi');
    var sedang = document.getElementById('saw_batas_sedang');
    if (!who || !aging || !tinggi || !sedang) return;

    var total = document.getElementById('saw-total');
    var bar   = document.getElementById('saw-total-bar');
    var nilai = document.getElementById('saw-total-nilai');
    var note  = document.getElementById('saw-total-note');

    function num(el) { var n = parseInt(el.value, 10); return isNaN(n) ? 0 : n; }

    function update() {
        var t = num(who) + num(aging);
        var ok = t === 100;
        nilai.textContent = t + '%';
        bar.style.width = Math.min(t, 100) + '%';
        total.setAttribute('data-ok', ok ? 'true' : 'false');
        note.textContent = ok ? 'Sudah 100%' : (t < 100 ? 'Kurang ' + (100 - t) + '%' : 'Lebih ' + (t - 100) + '%');

        var h = num(tinggi), s = num(sedang);
        document.getElementById('rng-rendah').textContent = '< ' + s;
        document.getElementById('rng-sedang').textContent = s + ' – ' + Math.max(h - 1, s);
        document.getElementById('rng-tinggi').textContent = '≥ ' + h;
    }

    [who, aging, tinggi, sedang].forEach(function (el) { el.addEventListener('input', update); });
    update();
})();
</script>

@endsection

@push('styles')
<style>
    /* ---------- Dasar halaman ---------- */
    .k-pengaturan-form {
        --p-ink: #1B2432;
        --p-muted: #6B7280;
        --p-line: #E2E4E9;
        --p-soft: #F6F7F4;
        --p-green: #6E8F60;
        --p-green-dark: #587A4B;
        --p-red: #C93B3B;
        --p-red-soft: #FDF0F0;

        display: flex; flex-direction: column; gap: 20px;
        width: 100%; max-width: 920px; margin: 0 auto;
    }
    .k-pengaturan-form * { box-sizing: border-box; }

    /* ---------- Alert ---------- */
    .k-alert-sukses, .k-alert-error {
        display: flex; align-items: flex-start; gap: 10px;
        max-width: 920px; margin: 0 auto 18px;
        padding: 12px 16px; border-radius: 12px; font-size: 14px; line-height: 1.45;
    }
    .k-alert-sukses svg, .k-alert-error svg { flex: 0 0 auto; width: 18px; height: 18px; margin-top: 1px; }
    .k-alert-sukses { background: #EAF3E6; color: #3F5A33; border: 1px solid #C4DCB8; }
    .k-alert-error  { background: var(--p-red-soft, #FDF0F0); color: #8F2A2A; border: 1px solid #F0C4C4; }

    /* ---------- Head ---------- */
    .k-pengaturan-form .k-head-row {
        display: flex; align-items: flex-end; justify-content: space-between;
        gap: 16px; flex-wrap: wrap;
    }
    .k-pengaturan-form .k-page-head { min-width: 0; flex: 1 1 260px; }
    .k-pengaturan-form .k-page-head h2 { margin: 0 0 4px; font-size: clamp(20px, 2.4vw, 24px); color: var(--p-ink); }
    .k-pengaturan-form .k-page-head p  { margin: 0; font-size: 14px; color: var(--p-muted); }

    /* ---------- Tombol ---------- */
    .k-btn-save {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        min-height: 44px; padding: 0 20px; cursor: pointer;
        transition: background-color .15s ease, transform .1s ease;
    }
    .k-btn-save svg { width: 16px; height: 16px; flex: 0 0 auto; }
    .k-btn-save:active { transform: translateY(1px); }
    .k-btn-save:focus-visible { outline: 3px solid rgba(110,143,96,.45); outline-offset: 2px; }

    /* ---------- Blok kartu ---------- */
    .k-pengaturan-blok { padding: 26px 28px; }

    .k-pengaturan-head { display: flex; align-items: flex-start; gap: 14px; margin-bottom: 22px; padding-bottom: 18px; border-bottom: 1px solid var(--p-line); }
    .k-pengaturan-head > div { min-width: 0; }
    .k-pengaturan-head h3 { margin: 0 0 3px; font-size: 16px; font-weight: 700; color: var(--p-ink); line-height: 1.3; }
    .k-pengaturan-head p  { margin: 0; font-size: 13px; color: var(--p-muted); line-height: 1.45; }

    .k-icon-badge {
        flex: 0 0 auto; width: 40px; height: 40px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
    }
    .k-icon-badge svg { width: 20px; height: 20px; }
    .k-icon-badge.tone-green  { background: #E4EEDE; color: #5C7D4F; }
    .k-icon-badge.tone-blue   { background: #DEE9FB; color: #3B6FD6; }
    .k-icon-badge.tone-yellow { background: #FBEED9; color: #B97A14; }
    .k-icon-badge.tone-purple { background: #E9E1F7; color: #7448C2; }

    .k-subjudul { font-size: 13px; font-weight: 600; color: var(--p-muted); margin: 0 0 12px; }

    /* ---------- Grid & field ---------- */
    .k-grid-2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0 22px; }

    .k-field { margin-bottom: 20px; min-width: 0; }
    .k-field:last-child { margin-bottom: 0; }
    .k-field label { display: block; font-size: 14px; font-weight: 600; color: var(--p-ink); margin-bottom: 6px; }
    .k-field label em { font-weight: 400; font-style: normal; color: var(--p-muted); }
    .k-field small { display: block; font-size: 12.5px; line-height: 1.45; color: var(--p-muted); margin-top: 6px; }
    .k-field small.k-field-desc { margin-top: 0; margin-bottom: 8px; }
    .k-field small.k-field-error { color: var(--p-red); font-weight: 500; }

    .k-input, .k-textarea {
        width: 100%; border: 1px solid var(--p-line); border-radius: 10px;
        padding: 11px 14px; min-height: 44px; font-size: 14px; font-family: inherit; color: var(--p-ink);
        background: #fff; transition: border-color .15s ease, box-shadow .15s ease;
    }
    .k-input::placeholder { color: #A7ACB5; }
    .k-input:hover, .k-textarea:hover { border-color: #CBCFD6; }
    .k-input:focus, .k-textarea:focus { outline: none; border-color: var(--p-green); box-shadow: 0 0 0 3px rgba(110,143,96,.18); }
    .k-textarea { resize: vertical; line-height: 1.55; min-height: 96px; }

    .k-field.has-error .k-input,
    .k-field.has-error .k-textarea { border-color: var(--p-red); background: var(--p-red-soft); }
    .k-field.has-error .k-input:focus,
    .k-field.has-error .k-textarea:focus { box-shadow: 0 0 0 3px rgba(201,59,59,.15); }

    /* input dengan akhiran (%) */
    .k-input-suffix { position: relative; }
    .k-input-suffix .k-input { padding-right: 40px; }
    .k-input-suffix span {
        position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
        font-size: 14px; color: var(--p-muted); pointer-events: none;
    }

    .k-inline-input { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .k-input-sm { width: 104px; flex: 0 0 auto; }
    .k-inline-input span { font-size: 14px; color: var(--p-ink); }

    /* ---------- Indikator total bobot ---------- */
    .k-total {
        display: grid; grid-template-columns: auto 1fr auto; align-items: center; gap: 6px 14px;
        padding: 14px 16px; margin: 4px 0 24px;
        background: var(--p-soft); border: 1px solid var(--p-line); border-radius: 12px;
    }
    .k-total-label { font-size: 13px; font-weight: 600; color: var(--p-ink); }
    .k-total-bar { height: 8px; background: #E3E6DD; border-radius: 99px; overflow: hidden; }
    .k-total-bar i { display: block; height: 100%; width: 0; background: #DB9A2C; border-radius: 99px; transition: width .2s ease, background-color .2s ease; }
    .k-total strong { font-size: 14px; color: var(--p-ink); min-width: 44px; text-align: right; }
    .k-total-note { grid-column: 1 / -1; font-size: 12.5px; color: #8F6410; }
    .k-total[data-ok="true"] .k-total-bar i { background: var(--p-green); }
    .k-total[data-ok="true"] .k-total-note { color: #3F5A33; }

    /* ---------- Rentang kategori ---------- */
    .k-rentang { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 22px; padding-top: 18px; border-top: 1px dashed var(--p-line); }
    .k-chip { display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border-radius: 99px; font-size: 13px; font-weight: 500; }
    .k-chip b { font-weight: 700; }
    .chip-rendah { background: #E4EEDE; color: #3F5A33; }
    .chip-sedang { background: #FBEED9; color: #8F6410; }
    .chip-tinggi { background: #FBE3E3; color: #9A2D2D; }

    /* ---------- Daftar info ---------- */
    .k-info-list { margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 12px; }
    .k-info-list li {
        position: relative; padding-left: 28px;
        font-size: 14px; color: #4B5160; line-height: 1.55;
    }
    .k-info-list li::before {
        content: ""; position: absolute; left: 0; top: 3px; width: 16px; height: 16px; border-radius: 50%;
        background: #E4EEDE url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%235C7D4F' stroke-width='3.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m5 12 5 5L20 7'/%3E%3C/svg%3E") center / 10px no-repeat;
    }

    /* ---------- Footer simpan ---------- */
    .k-pengaturan-footer {
        display: flex; align-items: center; justify-content: space-between; gap: 16px;
        position: sticky; bottom: 0; z-index: 5;
        padding: 14px 20px; margin-top: 4px;
        background: rgba(255,255,255,.92); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
        border: 1px solid var(--p-line); border-radius: 14px;
        box-shadow: 0 -4px 18px rgba(27,36,50,.06);
    }
    .k-pengaturan-footer p { margin: 0; font-size: 13px; color: var(--p-muted); }

    /* ---------- Tablet ---------- */
    @media (max-width: 900px) {
        .k-pengaturan-blok { padding: 22px 22px; }
    }

    /* ---------- Ponsel ---------- */
    @media (max-width: 640px) {
        .k-pengaturan-form { gap: 14px; }

        /* tombol di header disembunyikan, diganti footer yang menempel */
        .k-btn-head { display: none; }

        .k-pengaturan-blok { padding: 18px 16px; }
        .k-pengaturan-head { gap: 12px; margin-bottom: 18px; padding-bottom: 14px; }
        .k-icon-badge { width: 36px; height: 36px; border-radius: 9px; }
        .k-icon-badge svg { width: 18px; height: 18px; }

        .k-grid-2 { grid-template-columns: 1fr; gap: 0; }
        .k-grid-2 .k-field { margin-bottom: 20px; }
        .k-grid-2 .k-field:last-child { margin-bottom: 0; }
        .k-grid-2 + .k-field { margin-top: 20px; }

        /* 16px mencegah iOS memperbesar layar saat fokus */
        .k-input, .k-textarea { font-size: 16px; }

        .k-total { grid-template-columns: 1fr auto; }
        .k-total-bar { grid-column: 1 / -1; order: 3; }
        .k-total-note { order: 4; }

        .k-pengaturan-footer {
            padding: 10px 12px; border-radius: 12px; gap: 10px;
            padding-bottom: calc(10px + env(safe-area-inset-bottom, 0px));
        }
        .k-pengaturan-footer p { display: none; }
        .k-pengaturan-footer .k-btn-save { width: 100%; }
    }

    @media (prefers-reduced-motion: reduce) {
        .k-btn-save, .k-input, .k-textarea, .k-total-bar i { transition: none; }
    }
</style>
@endpush