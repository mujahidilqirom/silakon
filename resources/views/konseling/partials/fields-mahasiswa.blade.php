@php
    // Katalog fakultas => jenjang => program studi (sumber: unnes.ac.id). Ubah di sini bila ada perubahan prodi.
    $__katalog = !empty($katalog ?? []) ? $katalog : config('silakon.katalog_fakultas_prodi', [
        'Fakultas Ilmu Pendidikan dan Psikologi (FIPP)' => [
            'S1' => ['Bimbingan dan Konseling', 'Teknologi Pendidikan', 'Pendidikan Non Formal', 'Psikologi', 'Pendidikan Guru Sekolah Dasar', 'Pendidikan Guru PAUD'],
            'S2' => ['Pengembangan Kurikulum', 'Pendidikan Non Formal', 'Pendidikan Anak Usia Dini', 'Bimbingan dan Konseling'],
            'S3' => ['Pendidikan Bimbingan dan Konseling'],
            'Profesi' => ['Pendidikan Profesi Konselor'],
        ],
        'Fakultas Bahasa dan Seni (FBS)' => [
            'D3' => ['Desain Komunikasi Visual'],
            'S1' => ['Pendidikan Bahasa dan Sastra Indonesia', 'Sastra Indonesia', 'Pendidikan Bahasa Inggris', 'Sastra Inggris', 'Pendidikan Bahasa Perancis', 'Sastra Perancis', 'Pendidikan Bahasa Jepang', 'Pendidikan Bahasa Arab', 'Pendidikan Bahasa Mandarin', 'Pendidikan Seni Rupa', 'Seni Rupa', 'Pendidikan Seni Musik', 'Pendidikan Seni Tari', 'Pendidikan Bahasa Jawa', 'Sastra Jawa'],
            'S2' => ['Pendidikan Bahasa Indonesia', 'Pendidikan Bahasa Inggris', 'Pendidikan Seni'],
            'S3' => ['Pendidikan Seni', 'Ilmu Pendidikan Bahasa'],
        ],
        'Fakultas Ilmu Sosial dan Ilmu Politik (FISIP)' => [
            'S1' => ['Pendidikan Sosiologi dan Antropologi', 'Pendidikan Pancasila dan Kewarganegaraan', 'Pendidikan Sejarah', 'Ilmu Sejarah', 'Ilmu Politik', 'Geografi', 'Pendidikan Geografi', 'Pendidikan Ilmu Pengetahuan Sosial', 'Ilmu Komunikasi'],
            'S2' => ['Kajian Sejarah', 'Pendidikan Ilmu Pengetahuan Sosial', 'Pendidikan Geografi'],
            'S3' => ['Pendidikan Ilmu Pengetahuan Sosial'],
        ],
        'Fakultas Matematika & Ilmu Pengetahuan Alam (FMIPA)' => [
            'D3' => ['Statistika Terapan dan Komputasi'],
            'S1' => ['Pendidikan Matematika', 'Matematika', 'Pendidikan Fisika', 'Fisika', 'Pendidikan Kimia', 'Kimia', 'Pendidikan Biologi', 'Biologi', 'Pendidikan Ilmu Pengetahuan Alam', 'Teknik Informatika', 'Sistem Informasi', 'Ilmu Lingkungan', 'Statistika dan Sains Data'],
            'S2' => ['Pendidikan Ilmu Pengetahuan Alam', 'Pendidikan Matematika', 'Pendidikan Fisika', 'Fisika', 'Pendidikan Kimia', 'Biologi', 'Informatika'],
            'S3' => ['Pendidikan Ilmu Pengetahuan Alam', 'Pendidikan Matematika'],
        ],
        'Fakultas Teknik (FT)' => [
            'S1' => ['Pendidikan Teknik Bangunan', 'Teknik Sipil', 'Arsitektur', 'Pendidikan Teknik Mesin', 'Teknik Mesin', 'Pendidikan Teknik Otomotif', 'Pendidikan Teknik Elektro', 'Teknik Elektro', 'Pendidikan Teknik Informatika dan Komputer', 'Pendidikan Kesejahteraan Keluarga', 'Pendidikan Tata Kecantikan', 'Pendidikan Tata Boga', 'Pendidikan Tata Busana', 'Teknik Kimia', 'Teknik Komputer'],
            'S2' => ['Teknik Sipil', 'Teknik Kimia'],
            'Profesi' => ['Program Profesi Insinyur'],
        ],
        'Fakultas Ilmu Keolahragaan (FIK)' => [
            'S1' => ['Ilmu Keolahragaan', 'Pendidikan Kesehatan Jasmani dan Rekreasi', 'Pendidikan Jasmani Sekolah Dasar', 'Pendidikan Kepelatihan Olahraga'],
            'S2' => ['Pendidikan Olahraga'],
            'S3' => ['Pendidikan Olahraga'],
        ],
        'Fakultas Ekonomika dan Bisnis (FEB)' => [
            'S1' => ['Manajemen', 'Akuntansi', 'Ekonomi Pembangunan', 'Pendidikan Ekonomi', 'Pendidikan Akuntansi', 'Pendidikan Administrasi Perkantoran', 'Ekonomi dan Keuangan Islam'],
            'S2' => ['Pendidikan Ekonomi', 'Ilmu Ekonomi', 'Akuntansi', 'Manajemen'],
            'S3' => ['Pendidikan Ekonomi'],
        ],
        'Fakultas Hukum (FH)' => [
            'S1' => ['Ilmu Hukum'],
            'S2' => ['Ilmu Hukum'],
            'S3' => ['Ilmu Hukum'],
        ],
        'Fakultas Kedokteran (FK)' => [
            'S1' => ['Kedokteran', 'Kedokteran Hewan', 'Ilmu Kesehatan Masyarakat', 'Gizi', 'Farmasi'],
            'S2' => ['Ilmu Kesehatan Masyarakat'],
            'S3' => ['Ilmu Kesehatan Masyarakat'],
            'Profesi' => ['Profesi Dokter'],
        ],
        'Sekolah Pascasarjana' => [
            'S2' => ['Penelitian dan Evaluasi Pendidikan', 'Pendidikan Dasar', 'Pendidikan Kejuruan', 'Manajemen Pendidikan'],
            'S3' => ['Manajemen Pendidikan', 'Pendidikan Dasar', 'Pendidikan Vokasi'],
            'Profesi' => ['Pendidikan Profesi Guru (PPG)'],
        ],
    ]);

    // Ratakan: fakultas => [ ['n' => nama, 'j' => jenjang, 'v' => nilai yang diisikan ke kolom], ... ]
    // Profesi tidak diberi akhiran jenjang pada nilai.
    $__prodiPerFakultas = [];
    foreach ($__katalog as $__fak => $__jenjangs) {
        foreach ($__jenjangs as $__jenjang => $__list) {
            foreach ($__list as $__p) {
                $__prodiPerFakultas[$__fak][] = [
                    'n' => $__p,
                    'j' => $__jenjang,
                    'v' => $__jenjang === 'Profesi' ? $__p : $__p . ' (' . $__jenjang . ')',
                ];
            }
        }
    }
    $__pfx = $alumni ? 'alumni' : 'mahasiswa';
@endphp
<div class="konseling-field full"><label>Nama <span class="konseling-required">*</span></label><input name="{{ $alumni ? 'alumni' : 'mahasiswa' }}_nama" type="text" placeholder="Nama lengkap" required><div class="konseling-error">Nama wajib diisi.</div></div>
<div class="konseling-field"><label>Jenis Kelamin <span class="konseling-required">*</span></label><select name="{{ $alumni ? 'alumni' : 'mahasiswa' }}_jenis_kelamin" required><option value="">Pilih jenis kelamin</option><option value="laki_laki">Laki-laki</option><option value="perempuan">Perempuan</option></select><div class="konseling-error">Pilih jenis kelamin.</div></div>
<div class="konseling-field"><label>NIM <span class="konseling-required">*</span></label><input name="{{ $alumni ? 'alumni' : 'mahasiswa' }}_nim" type="text" placeholder="Nomor Induk Mahasiswa" required><div class="konseling-error">NIM wajib diisi.</div></div>
<div class="konseling-field"><label>Fakultas <span class="konseling-required">*</span></label><select name="{{ $__pfx }}_fakultas" data-fakultas-select data-prodi-input="{{ $__pfx }}_jurusan" required><option value="">Pilih fakultas</option>@foreach(array_keys($__prodiPerFakultas) as $__fak)<option value="{{ $__fak }}">{{ $__fak }}</option>@endforeach</select><div class="konseling-error">Pilih fakultas.</div></div>
<div class="konseling-field"><label>Program Studi <span class="konseling-required">*</span></label><div class="konseling-combo" data-combo><input name="{{ $__pfx }}_jurusan" type="text" autocomplete="off" placeholder="Ketik atau pilih program studi" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="konseling-prodi-list-{{ $__pfx }}" data-prodi-combo data-fakultas-name="{{ $__pfx }}_fakultas" required><svg class="konseling-combo-chevron" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg><ul class="konseling-combo-list" id="konseling-prodi-list-{{ $__pfx }}" role="listbox" hidden></ul></div><div class="konseling-error">Program studi wajib diisi.</div></div>
@if($alumni)
<div class="konseling-field"><label>Tahun Angkatan <span class="konseling-required">*</span></label><input name="alumni_tahun_angkatan" type="number" min="1900" max="2100" placeholder="Contoh: 2020" required><div class="konseling-error">Tahun angkatan wajib diisi.</div></div>
@endif
<div class="konseling-field"><label>Tempat Lahir <span class="konseling-required">*</span></label><input name="{{ $alumni ? 'alumni' : 'mahasiswa' }}_tempat_lahir" type="text" placeholder="Kota tempat lahir" required><div class="konseling-error">Tempat lahir wajib diisi.</div></div>
<div class="konseling-field"><label>Tanggal Lahir <span class="konseling-required">*</span></label><input name="{{ $alumni ? 'alumni' : 'mahasiswa' }}_tanggal_lahir" type="date" max="{{ now()->toDateString() }}" required><div class="konseling-error">Tanggal lahir wajib diisi dan tidak boleh melebihi hari ini.</div></div>
<div class="konseling-field full"><label>Alamat Kos <span class="konseling-required">*</span></label><textarea name="{{ $alumni ? 'alumni' : 'mahasiswa' }}_alamat_kos" placeholder="Alamat kos / tempat tinggal saat ini" required></textarea><div class="konseling-error">Alamat kos wajib diisi.</div></div>
<div class="konseling-field full"><label>Alamat Rumah <span class="konseling-required">*</span></label><textarea name="{{ $alumni ? 'alumni' : 'mahasiswa' }}_alamat_rumah" placeholder="Alamat rumah" required></textarea><div class="konseling-error">Alamat rumah wajib diisi.</div></div>
<div class="konseling-field full"><label>Tempat Bekerja <small>(opsional)</small></label><input name="{{ $alumni ? 'alumni' : 'mahasiswa' }}_tempat_bekerja" type="text" placeholder="Nama instansi / tempat bekerja"></div>
<div class="konseling-field"><label>Nomor Telepon <span class="konseling-required">*</span></label><input name="{{ $alumni ? 'alumni' : 'mahasiswa' }}_telepon" type="tel" placeholder="08xxxxxxxxxx" required><div class="konseling-error">Nomor telepon wajib diisi.</div></div>
@if($alumni)
<div class="konseling-field"><label>Email <span class="konseling-required">*</span></label><input name="alumni_email" type="email" placeholder="nama@email.com" required><div class="konseling-error">Masukkan alamat email yang valid.</div></div>
@else
<div class="konseling-field"><label>Email UNNES <span class="konseling-required">*</span></label><div class="konseling-email-suffix"><input name="mahasiswa_email" type="text" inputmode="email" placeholder="nama pengguna" data-email-domain="@students.unnes.ac.id" autocapitalize="off" autocomplete="email" required><span>@students.unnes.ac.id</span></div><div class="konseling-error">Isi bagian awal email UNNES.</div></div>
@endif
<div class="konseling-section-title" style="grid-column:1/-1;">Kontak Darurat I</div>
<div class="konseling-field"><label>Nama <span class="konseling-required">*</span></label><input name="{{ $alumni ? 'alumni' : 'mahasiswa' }}_darurat_1_nama" type="text" placeholder="Nama kontak darurat" required><div class="konseling-error">Nama kontak wajib diisi.</div></div>
<div class="konseling-field"><label>Hubungan <span class="konseling-required">*</span></label><input name="{{ $alumni ? 'alumni' : 'mahasiswa' }}_darurat_1_hubungan" type="text" placeholder="Contoh: Bapak, Ibu, Kakak" required><div class="konseling-error">Hubungan wajib diisi.</div></div>
<div class="konseling-field"><label>Nomor <span class="konseling-required">*</span></label><input name="{{ $alumni ? 'alumni' : 'mahasiswa' }}_darurat_1_nomor" type="tel" placeholder="Nomor telepon" required><div class="konseling-error">Nomor kontak wajib diisi.</div></div>
<div class="konseling-section-title" style="grid-column:1/-1;">Kontak Darurat II <small>(opsional)</small></div>
<div class="konseling-field"><label>Nama</label><input name="{{ $alumni ? 'alumni' : 'mahasiswa' }}_darurat_2_nama" type="text" placeholder="Nama kontak darurat"></div>
<div class="konseling-field"><label>Hubungan</label><input name="{{ $alumni ? 'alumni' : 'mahasiswa' }}_darurat_2_hubungan" type="text" placeholder="Contoh: Bapak, Ibu, Kakak"></div>
<div class="konseling-field"><label>Nomor</label><input name="{{ $alumni ? 'alumni' : 'mahasiswa' }}_darurat_2_nomor" type="tel" placeholder="Nomor telepon"></div>
@once
<style>
    .konseling-email-suffix{display:flex;align-items:stretch;width:100%;}
    .konseling-email-suffix input{min-width:0;flex:1;border-radius:10px 0 0 10px!important;}
    .konseling-email-suffix span{display:flex;align-items:center;padding:0 12px;background:#f1f5f3;border:1px solid #dfe7e1;border-left:0;border-radius:0 10px 10px 0;color:#64746a;font-size:13px;white-space:nowrap;}
    .konseling-combo { position: relative; }
    .konseling-field .konseling-combo > input { width: 100%; padding-right: 40px; }
    .konseling-combo-chevron {
        position: absolute; right: 13px; top: 50%; margin-top: -9px;
        color: #94a3b8; pointer-events: none; transition: transform .18s ease, color .18s ease;
    }
    .konseling-combo.is-open .konseling-combo-chevron { transform: rotate(180deg); color: #16a34a; }

    .konseling-combo-list {
        position: absolute; z-index: 40; left: 0; right: 0; top: calc(100% + 6px);
        max-height: 288px; overflow-y: auto; overscroll-behavior: contain;
        margin: 0; padding: 6px; list-style: none;
        background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
        box-shadow: 0 16px 36px -10px rgba(15, 23, 42, .22), 0 2px 8px rgba(15, 23, 42, .06);
        transform-origin: top center; animation: konselingComboIn .14s ease-out;
        scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent;
    }
    .konseling-combo-list[hidden] { display: none; }
    .konseling-combo-list::-webkit-scrollbar { width: 8px; }
    .konseling-combo-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; border: 2px solid #fff; }

    .konseling-combo-head {
        position: sticky; top: -6px; z-index: 1;
        margin: -6px -6px 4px; padding: 10px 14px 8px;
        background: #f8fafc; border-bottom: 1px solid #eef2f6; border-radius: 14px 14px 0 0;
        font-size: 12px; line-height: 1.4; color: #64748b;
    }
    .konseling-combo-item {
        display: flex; align-items: center; justify-content: space-between; gap: 10px;
        padding: 9px 10px; border-radius: 9px; cursor: pointer;
        font-size: 14px; line-height: 1.35; color: #0f172a;
    }
    .konseling-combo-item + .konseling-combo-item { margin-top: 1px; }
    .konseling-combo-item.is-active { background: #f0fdf4; color: #166534; }
    .konseling-combo-item mark {
        background: #dcfce7; color: inherit; font-weight: 600; border-radius: 3px; padding: 0 1px;
    }
    .konseling-combo-badge {
        flex: 0 0 auto; padding: 2px 8px; border-radius: 999px;
        background: #f1f5f9; color: #475569; font-size: 11px; font-weight: 600; letter-spacing: .02em;
    }
    .konseling-combo-item.is-active .konseling-combo-badge { background: #dcfce7; color: #166534; }
    .konseling-combo-empty { padding: 16px 12px; text-align: center; font-size: 13px; line-height: 1.5; color: #64748b; }

    @keyframes konselingComboIn {
        from { opacity: 0; transform: translateY(-4px) scale(.985); }
        to   { opacity: 1; transform: none; }
    }
    @media (prefers-reduced-motion: reduce) {
        .konseling-combo-list { animation: none; }
        .konseling-combo-chevron { transition: none; }
    }
</style>
<script type="application/json" id="konseling-prodi-data">@json($__prodiPerFakultas)</script>
<script>
(function () {
    const dataEl = document.getElementById('konseling-prodi-data');
    if (!dataEl) return;

    let data = {};
    try { data = JSON.parse(dataEl.textContent); } catch (e) { return; }

    // Semua prodi (tanpa duplikat) untuk saat fakultas belum dipilih.
    const semua = Array.from(new Map(Object.values(data).flat().map((p) => [p.v, p])).values());
    const state = new WeakMap(); // input -> indeks item aktif

    const norm = (s) => s.toLowerCase();
    const esc = (s) => s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const listOf = (input) => document.getElementById(input.getAttribute('aria-controls'));
    const wrapOf = (input) => input.closest('[data-combo]');
    const facSelect = (input) => (input.form || document).querySelector('[name="' + input.dataset.fakultasName + '"]');
    const isCombo = (el) => el && el.matches && el.matches('[data-prodi-combo]');

    function shortFak(name) {
        const m = name.match(/\(([^)]+)\)\s*$/);
        return m ? m[1] : name;
    }

    // Tebalkan bagian teks yang cocok dengan kata yang diketik.
    function highlight(el, text, tokens) {
        if (!tokens.length) { el.textContent = text; return; }
        const re = new RegExp('(' + tokens.map(esc).join('|') + ')', 'gi');
        let last = 0, m;
        while ((m = re.exec(text))) {
            if (m.index > last) el.appendChild(document.createTextNode(text.slice(last, m.index)));
            const mark = document.createElement('mark');
            mark.textContent = m[0];
            el.appendChild(mark);
            last = m.index + m[0].length;
        }
        if (last < text.length) el.appendChild(document.createTextNode(text.slice(last)));
    }

    function render(input) {
        const list = listOf(input);
        const sel = facSelect(input);
        const fak = sel ? sel.value : '';
        const base = data[fak] || semua;
        const q = input.value.trim();

        // Kalau isinya sudah persis salah satu prodi, tampilkan daftar penuh (bukan disaring lagi).
        const exact = semua.some((p) => p.v === q);
        const tokens = exact ? [] : norm(q).split(/\s+/).filter(Boolean);

        let matches = base.filter((p) => tokens.every((t) => norm(p.v).includes(t)));
        if (tokens.length) {
            const first = tokens[0];
            const depan = matches.filter((p) => norm(p.n).startsWith(first));
            matches = depan.concat(matches.filter((p) => !depan.includes(p)));
        }

        list.textContent = '';

        const head = document.createElement('li');
        head.className = 'konseling-combo-head';
        head.setAttribute('role', 'presentation');
        head.textContent = fak
            ? 'Program studi ' + shortFak(fak)
            : 'Semua program studi · pilih fakultas untuk menyaring';
        list.appendChild(head);

        if (!matches.length) {
            const empty = document.createElement('li');
            empty.className = 'konseling-combo-empty';
            empty.setAttribute('role', 'presentation');
            empty.textContent = 'Tidak ada saran yang cocok. Anda tetap bisa mengetik program studi sendiri.';
            list.appendChild(empty);
        }

        matches.forEach((p, i) => {
            const li = document.createElement('li');
            li.className = 'konseling-combo-item';
            li.id = list.id + '-opt-' + i;
            li.setAttribute('role', 'option');
            li.setAttribute('aria-selected', 'false');
            li.dataset.value = p.v;
            li.dataset.index = String(i);

            const name = document.createElement('span');
            highlight(name, p.n, tokens);
            const badge = document.createElement('span');
            badge.className = 'konseling-combo-badge';
            badge.textContent = p.j;

            li.appendChild(name);
            li.appendChild(badge);
            list.appendChild(li);
        });

        state.set(input, -1);
        input.removeAttribute('aria-activedescendant');
        list.scrollTop = 0;
    }

    function open(input) {
        render(input);
        listOf(input).hidden = false;
        input.setAttribute('aria-expanded', 'true');
        wrapOf(input).classList.add('is-open');
    }

    function close(input) {
        const list = listOf(input);
        if (list) list.hidden = true;
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-activedescendant');
        const wrap = wrapOf(input);
        if (wrap) wrap.classList.remove('is-open');
    }

    function setActive(input, index, scroll) {
        const opts = listOf(input).querySelectorAll('[role="option"]');
        opts.forEach((el, i) => {
            const on = i === index;
            el.classList.toggle('is-active', on);
            el.setAttribute('aria-selected', on ? 'true' : 'false');
            if (on) {
                input.setAttribute('aria-activedescendant', el.id);
                if (scroll) el.scrollIntoView({ block: 'nearest' });
            }
        });
        state.set(input, index);
    }

    function pick(input, opt) {
        input.value = opt.dataset.value;
        // Beri tahu validasi/JS lain bahwa nilai berubah, lalu tutup daftar.
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
        close(input);
    }

    document.addEventListener('focusin', (e) => { if (isCombo(e.target)) open(e.target); });
    document.addEventListener('focusout', (e) => { if (isCombo(e.target)) close(e.target); });
    document.addEventListener('input', (e) => { if (isCombo(e.target)) open(e.target); });

    document.addEventListener('click', (e) => {
        // Klik lagi pada kolom yang sudah fokus (mis. setelah Esc) membuka daftar kembali.
        if (isCombo(e.target) && listOf(e.target).hidden) open(e.target);
    });

    // mousedown (bukan click) agar kolom tidak kehilangan fokus sebelum item terpilih.
    document.addEventListener('mousedown', (e) => {
        const list = e.target.closest && e.target.closest('.konseling-combo-list');
        if (!list) return;
        e.preventDefault();
        const opt = e.target.closest('[role="option"]');
        const input = document.querySelector('[aria-controls="' + list.id + '"]');
        if (opt && input) pick(input, opt);
    });

    document.addEventListener('mouseover', (e) => {
        const opt = e.target.closest && e.target.closest('.konseling-combo-item');
        if (!opt) return;
        const list = opt.parentElement;
        const input = document.querySelector('[aria-controls="' + list.id + '"]');
        if (input) setActive(input, Number(opt.dataset.index), false);
    });

    document.addEventListener('keydown', (e) => {
        const input = e.target;
        if (!isCombo(input)) return;
        const list = listOf(input);
        const n = list.querySelectorAll('[role="option"]').length;

        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            e.preventDefault();
            if (list.hidden) open(input);
            const total = list.querySelectorAll('[role="option"]').length;
            if (!total) return;
            const cur = state.get(input);
            const next = e.key === 'ArrowDown'
                ? (cur + 1) % total
                : (cur < 0 ? total - 1 : (cur - 1 + total) % total);
            setActive(input, next, true);
        } else if (e.key === 'Enter') {
            const cur = state.get(input);
            if (!list.hidden && n && cur >= 0) {
                e.preventDefault();
                pick(input, list.querySelectorAll('[role="option"]')[cur]);
            }
        } else if (e.key === 'Escape') {
            if (!list.hidden) { e.preventDefault(); close(input); }
        }
    });

    // Ganti fakultas => kosongkan program studi (saran ikut menyesuaikan fakultas baru).
    document.addEventListener('change', (e) => {
        if (!e.target.matches || !e.target.matches('[data-fakultas-select]')) return;
        const input = (e.target.form || document).querySelector('[name="' + e.target.dataset.prodiInput + '"]');
        if (!input) return;
        input.value = '';
        if (!listOf(input).hidden) render(input);
    });
})();
</script>
@endonce
