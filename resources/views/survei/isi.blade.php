{{--
    SILAKON — Survei Layanan Konseling (Konseli)
    Simpan sebagai: resources/views/survei/isi.blade.php
    Halaman publik (tanpa layout admin), CSS langsung di file ini.

    Data dari SurveiController@tampil: $konseli, $token
    Pertanyaan dikelola lewat array $pertanyaan di bawah — tinggal ubah/tambah di situ.
--}}
@php
    $pertanyaan = [
        [
            'name'  => 'perasaan', 'tipe' => 'pilihan', 'lain' => true,
            'label' => 'Bagaimana perasaan, pemahaman dan tindakan Saudara pasca konseling?',
            'opsi'  => ['harapan' => 'Memberi harapan/pencerahan', 'keruh' => 'Semakin memperkeruh kondisi/permasalahan'],
        ],
        [
            'name'  => 'membantu', 'tipe' => 'pilihan', 'lain' => false,
            'label' => 'Apakah layanan konseling di Pusat Layanan Bimbingan Konseling LPPP UNNES membantu permasalahan yang Saudara alami?',
            'opsi'  => ['ya' => 'Ya', 'tidak' => 'Tidak'],
        ],
        [
            'name'  => 'manfaat', 'tipe' => 'isian',
            'label' => 'Manfaat yang Saudara dapatkan setelah melakukan layanan konseling',
            'ph'    => 'Ceritakan manfaat yang Anda rasakan',
        ],
        [
            'name'  => 'kekurangan_kelebihan', 'tipe' => 'isian',
            'label' => 'Adakah kekurangan atau kelebihan setelah melakukan layanan konseling?',
            'ph'    => 'Tuliskan kekurangan atau kelebihan yang Anda alami',
        ],
        [
            'name'  => 'saran', 'tipe' => 'isian',
            'label' => 'Harapan/saran untuk perbaikan layanan konseling yang akan datang',
            'ph'    => 'Tuliskan harapan atau saran Anda',
        ],
        [
            'name'  => 'sikap', 'tipe' => 'pilihan', 'lain' => true,
            'label' => 'Bagaimana sikap konselor dalam melayani Saudara?',
            'opsi'  => ['memfasilitasi' => 'Memfasilitasi', 'menghakimi' => 'Lebih banyak memberikan penilaian (judgment) dan berlaku buruk'],
        ],
    ];

    $labelKerja = $konseli['jenis'] === 'Alumni' ? 'Pekerjaan' : 'Program Studi';
    $selesai    = session('survei_selesai') || ($surveiSudahDiisi ?? false);
    $namaDepan  = explode(' ', $konseli['nama'])[0];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>Survei Layanan Konseling — SILAKON</title>

    <style>
        :root {
            --primary: #7a9c7c;
            --primary-dark: #4d7a55;
            --ink: #1a2321;
            --body: #4f5a56;
            --muted: #7d8582;
            --line: #e7eae6;
            --bg: #f6f7f5;
            --danger: #b91c1c;
            --danger-bg: #fdf1f1;
        }
        * { box-sizing: border-box; }
        html { -webkit-text-size-adjust: 100%; }
        body {
            margin: 0; background: var(--bg); color: var(--body);
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 15px; line-height: 1.55;
        }

        .sv { max-width: 720px; margin: 0 auto; padding: 32px 16px 0; }

        /* Kepala halaman */
        .sv-top { margin-bottom: 24px; }
        .sv-brand { display: inline-flex; align-items: center; gap: 8px; font-weight: 700; font-size: 15px; color: var(--primary-dark); }
        .sv-brand i { width: 10px; height: 10px; border-radius: 50%; background: var(--primary); display: inline-block; }
        .sv-top h1 { margin: 14px 0 6px; font-size: 26px; line-height: 1.25; font-weight: 700; color: var(--ink); }
        .sv-top p { margin: 0; color: var(--muted); font-size: 14px; }

        /* Kartu */
        .sv-card {
            background: #fff; border: 1px solid var(--line); border-radius: 20px;
            padding: 24px; margin-bottom: 16px;
        }
        .sv-card h2 { margin: 0; font-size: 16px; font-weight: 700; color: var(--ink); }
        .sv-note { margin: 4px 0 18px; font-size: 13px; color: var(--muted); }

        /* Data diri */
        .sv-data { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 24px; margin: 0; }
        .sv-data div { min-width: 0; }
        .sv-data dt { font-size: 12.5px; color: var(--muted); margin-bottom: 2px; }
        .sv-data dd { margin: 0; font-weight: 600; color: var(--ink); word-break: break-word; }
        .sv-data .full { grid-column: 1 / -1; }

        /* Pertanyaan */
        .sv-q.has-error { border-color: #f0b8b8; }
        .sv-qtitle { display: flex; gap: 12px; align-items: flex-start; margin-bottom: 16px !important; line-height: 1.45 !important; }
        .sv-no {
            flex: none; min-width: 26px; height: 26px; display: grid; place-items: center;
            border-radius: 8px; background: #eef3ee; color: var(--primary-dark); font-size: 13px; font-weight: 700;
        }

        .sv-opts { display: flex; flex-direction: column; gap: 10px; }
        .sv-opt {
            display: flex; align-items: center; gap: 12px; padding: 13px 16px;
            border: 1px solid var(--line); border-radius: 12px; cursor: pointer; background: #fff;
            color: var(--ink); font-size: 14.5px;
        }
        .sv-opt:hover { border-color: var(--primary); }
        .sv-opt input[type="radio"] { flex: none; width: 18px; height: 18px; margin: 0; accent-color: var(--primary-dark); cursor: pointer; }
        .sv-opt:has(input[type="radio"]:checked) { border-color: var(--primary-dark); background: #f2f7f2; }
        .sv-opt:has(input[type="radio"]:focus-visible) { outline: 2px solid var(--primary-dark); outline-offset: 2px; }
        .sv-opt-lain span { flex: none; }

        .sv-lain, .sv-area {
            font: inherit; font-size: 14.5px; color: var(--ink); background: #fff;
            border: 1px solid var(--line); border-radius: 10px;
        }
        .sv-lain { flex: 1; min-width: 0; height: 38px; padding: 0 12px; }
        .sv-area { display: block; width: 100%; padding: 12px 14px; resize: vertical; min-height: 104px; }
        .sv-lain::placeholder, .sv-area::placeholder { color: #a3a9a6; }
        .sv-lain:focus-visible, .sv-area:focus-visible { outline: 2px solid var(--primary); outline-offset: 1px; }

        .sv-err { margin: 12px 0 0; font-size: 13px; color: var(--danger); }
        .sv-err:empty { display: none; }

        /* Bar bawah: progres + kirim */
        .sv-bar {
            position: sticky; bottom: 0; z-index: 5; margin: 8px -16px 0; padding: 14px 16px calc(14px + env(safe-area-inset-bottom, 0px));
            background: rgba(246, 247, 245, .94); backdrop-filter: blur(6px); border-top: 1px solid var(--line);
            display: flex; align-items: center; gap: 16px;
        }
        .sv-prog { flex: 1; min-width: 0; }
        .sv-prog span { display: block; font-size: 13px; color: var(--body); margin-bottom: 6px; }
        .sv-track { height: 6px; background: #e3e7e3; border-radius: 999px; overflow: hidden; }
        .sv-fill { display: block; height: 100%; width: 0; background: var(--primary-dark); border-radius: 999px; transition: width .25s ease; }
        .sv-submit {
            flex: none; height: 46px; padding: 0 26px; border: 0; border-radius: 999px;
            background: var(--primary-dark); color: #fff; font: inherit; font-size: 14.5px; font-weight: 600; cursor: pointer;
        }
        .sv-submit:hover { background: #416a48; }
        .sv-submit:disabled { opacity: .6; cursor: default; }
        .sv-submit:focus-visible { outline: 2px solid var(--primary-dark); outline-offset: 2px; }

        /* Selesai */
        .sv-done { text-align: center; padding: 48px 24px; }
        .sv-done-ico {
            width: 56px; height: 56px; margin: 0 auto 18px; border-radius: 50%;
            background: #d9f5e3; color: #1f7a4a; display: grid; place-items: center;
        }
        .sv-done-ico svg { width: 28px; height: 28px; }
        .sv-done h2 { font-size: 20px; margin-bottom: 8px; }
        .sv-done p { margin: 0 auto; max-width: 420px; color: var(--body); }

        .sv-foot { text-align: center; font-size: 12.5px; color: var(--muted); padding: 24px 0 28px; }

        @media (max-width: 560px) {
            .sv { padding-top: 24px; }
            .sv-top h1 { font-size: 22px; }
            .sv-card { padding: 20px 18px; border-radius: 16px; }
            .sv-data { grid-template-columns: 1fr; }
            .sv-opt-lain { flex-wrap: wrap; }
            .sv-opt-lain .sv-lain { flex: 1 1 100%; }
            .sv-bar { gap: 12px; }
            .sv-submit { padding: 0 20px; }
        }
        @media (prefers-reduced-motion: reduce) { .sv-fill { transition: none; } }
    </style>
</head>
<body>
<main class="sv">

    <header class="sv-top">
        <div class="sv-brand"><i aria-hidden="true"></i> SILAKON</div>
        <h1>Survei Layanan Konseling</h1>
        <p>Pusat Layanan Bimbingan Konseling LPPP UNNES</p>
    </header>

    @if($selesai)
        {{-- ============ SETELAH KIRIM ============ --}}
        <section class="sv-card sv-done" role="status">
            <div class="sv-done-ico" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
            </div>
            <h2>Terima kasih, {{ $namaDepan }}</h2>
            <p>Jawaban survei Anda sudah kami terima dan akan kami gunakan untuk memperbaiki layanan konseling. Halaman ini boleh ditutup.</p>
        </section>
    @else
        <form method="POST" action="{{ route('survei.kirim', $token) }}" id="sv-form" novalidate>
            @csrf

            {{-- ============ DATA DIRI (dari sistem, hanya baca) ============ --}}
            <section class="sv-card" aria-labelledby="sv-data-judul">
                <h2 id="sv-data-judul">Data diri</h2>
                <p class="sv-note">Data ini sudah terisi otomatis dari sistem dan tidak perlu diubah.</p>

                <dl class="sv-data">
                    <div class="full"><dt>Email</dt><dd>{{ $konseli['email'] }}</dd></div>
                    <div class="full"><dt>{{ $labelKerja }}</dt><dd>{{ $konseli['prodi_pekerjaan'] }}</dd></div>
                    <div class="full"><dt>Nama konselor</dt><dd>{{ $konseli['konselor'] }}</dd></div>
                    <div><dt>Sesi pertemuan</dt><dd>Ke-{{ $konseli['sesi'] }}</dd></div>
                    <div><dt>Konseling bersama</dt><dd>{{ $konseli['bersama'] }}</dd></div>
                </dl>
            </section>

            {{-- ============ ISI SURVEI ============ --}}
            @foreach($pertanyaan as $i => $q)
                @php
                    $nm  = $q['name'];
                    $err = $errors->first($nm) ?: (($q['lain'] ?? false) ? $errors->first($nm . '_lain') : '');
                @endphp
                <section class="sv-card sv-q {{ $err ? 'has-error' : '' }}" data-tipe="{{ $q['tipe'] }}">
                    <h2 class="sv-qtitle" id="q-{{ $nm }}">
                        <span class="sv-no">{{ $i + 1 }}</span>
                        <span>{{ $q['label'] }}</span>
                    </h2>

                    @if($q['tipe'] === 'pilihan')
                        <div class="sv-opts" role="radiogroup" aria-labelledby="q-{{ $nm }}">
                            @foreach($q['opsi'] as $val => $teks)
                                <label class="sv-opt">
                                    <input type="radio" name="{{ $nm }}" value="{{ $val }}" {{ old($nm) === $val ? 'checked' : '' }}>
                                    <span>{{ $teks }}</span>
                                </label>
                            @endforeach

                            @if($q['lain'])
                                <label class="sv-opt sv-opt-lain">
                                    <input type="radio" name="{{ $nm }}" value="lain" {{ old($nm) === 'lain' ? 'checked' : '' }}>
                                    <span>Yang lain:</span>
                                    <input type="text" class="sv-lain" name="{{ $nm }}_lain" value="{{ old($nm . '_lain') }}"
                                           maxlength="500" placeholder="Tulis jawaban Anda" aria-label="Jawaban lain" autocomplete="off">
                                </label>
                            @endif
                        </div>
                    @else
                        <textarea class="sv-area" name="{{ $nm }}" rows="4" maxlength="2000"
                                  placeholder="{{ $q['ph'] }}" aria-labelledby="q-{{ $nm }}">{{ old($nm) }}</textarea>
                    @endif

                    <p class="sv-err" role="alert">{{ $err }}</p>
                </section>
            @endforeach

            {{-- ============ PROGRES + KIRIM ============ --}}
            <div class="sv-bar">
                <div class="sv-prog">
                    <span id="sv-prog-teks" aria-live="polite">0 dari {{ count($pertanyaan) }} pertanyaan terjawab</span>
                    <div class="sv-track"><i class="sv-fill" id="sv-fill"></i></div>
                </div>
                <button type="submit" class="sv-submit" id="sv-submit">Kirim survei</button>
            </div>
        </form>
    @endif

    <p class="sv-foot">Jawaban Anda hanya digunakan untuk peningkatan mutu layanan.</p>
</main>

@unless($selesai)
<script>
(function () {
    const form   = document.getElementById('sv-form');
    const cards  = Array.from(document.querySelectorAll('.sv-q'));
    const teks   = document.getElementById('sv-prog-teks');
    const fill   = document.getElementById('sv-fill');
    const kirim  = document.getElementById('sv-submit');

    function pesanKosong(card) {
        if (card.dataset.tipe === 'isian') return 'Kolom ini wajib diisi.';
        const r = card.querySelector('input[type="radio"]:checked');
        return r ? 'Tulis jawaban Anda pada kolom "Yang lain".' : 'Pilih salah satu jawaban.';
    }

    function terjawab(card) {
        if (card.dataset.tipe === 'isian') {
            return card.querySelector('textarea').value.trim() !== '';
        }
        const r = card.querySelector('input[type="radio"]:checked');
        if (!r) return false;
        if (r.value === 'lain') return card.querySelector('.sv-lain').value.trim() !== '';
        return true;
    }

    function perbarui() {
        const n = cards.filter(terjawab).length;
        teks.textContent = n + ' dari ' + cards.length + ' pertanyaan terjawab';
        fill.style.width = (n / cards.length * 100) + '%';
    }

    function bersihkanError(card) {
        card.classList.remove('has-error');
        card.querySelector('.sv-err').textContent = '';
    }

    cards.forEach(function (card) {
        card.addEventListener('input',  function () { perbarui(); if (terjawab(card)) bersihkanError(card); });
        card.addEventListener('change', function () { perbarui(); if (terjawab(card)) bersihkanError(card); });

        // Mengetik di "Yang lain" otomatis memilih radio-nya.
        const lain = card.querySelector('.sv-lain');
        if (lain) {
            lain.addEventListener('focus', function () {
                lain.closest('label').querySelector('input[type="radio"]').checked = true;
                perbarui();
            });
        }
    });

    form.addEventListener('submit', function (e) {
        const kosong = cards.filter(function (c) { return !terjawab(c); });

        if (kosong.length) {
            e.preventDefault();
            kosong.forEach(function (c) {
                c.classList.add('has-error');
                c.querySelector('.sv-err').textContent = pesanKosong(c);
            });
            kosong[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        kirim.disabled = true;
        kirim.textContent = 'Mengirim...';
    });

    perbarui();
})();
</script>
@endunless
</body>
</html>
