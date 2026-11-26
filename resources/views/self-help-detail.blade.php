@extends('layouts.app')

@section('title', ($resource['title'] ?? 'Detail Materi') . ' — SILAKON LPPP UNNES')

@section('content')

@php
    /*
     * Foto dari Unsplash (lisensi gratis), sama seperti di self-help_blade.php.
     * Video pakai video YouTube CC (Big Buck Bunny) sebagai PLACEHOLDER —
     * ganti youtube_id dengan ID video asli nanti (ambil dari bagian
     * ...watch?v=XXXXXXXXXXX pada URL YouTube).
     */
    $unsplash = fn ($id) => "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w=1200&h=640&q=70";

    // Jenis materi — harus sama dengan self-help_blade.php
    $types = [
        'artikel'   => ['label' => 'Artikel',   'icon' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h8"/>'],
        'video'     => ['label' => 'Video',     'icon' => '<polygon points="6 4 20 12 6 20 6 4"/>'],
        'worksheet' => ['label' => 'Worksheet', 'icon' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>'],
        'modul'     => ['label' => 'Modul Terpadu', 'icon' => '<path d="M4 5a2 2 0 0 1 2-2h14v17H6a2 2 0 0 0-2 2V5Z"/><path d="M4 20a2 2 0 0 1 2-2h14"/>'],
    ];

    /*
     * Data lengkap materi (DUMMY). Kalau nanti sudah ada controller + database,
     * cukup kirim $resource dari controller (mis. SelfHelpController@show) —
     * blok dummy di bawah ini otomatis tidak dipakai lagi.
     *
     * - artikel   -> pakai 'photo' + 'content' (array blok: p / h2 / quote)
     * - video     -> pakai 'youtube_id' + 'description'
     * - worksheet -> pakai 'instruction' + 'questions' (array pertanyaan jurnal)
     */
    $resources = [
        [
            'id' => 1, 'type' => 'artikel', 'topic' => 'Kesehatan Mental', 'meta' => '8 menit baca',
            'author' => 'Dr. Sari Dewi', 'date' => '3 September 2026',
            'title' => 'Mengatasi Kecemasan Ujian: Panduan Praktis untuk Mahasiswa',
            'photo' => $unsplash('1503676260728-1c00da094a0b'),
            'excerpt' => 'Kecemasan menjelang ujian itu wajar — tapi kalau dibiarkan, bisa mengganggu performa akademikmu.',
            'content' => [
                ['type' => 'p', 'text' => 'Kecemasan ujian (test anxiety) adalah reaksi alami tubuh terhadap situasi yang dianggap menekan. Hampir semua mahasiswa pernah mengalaminya, mulai dari yang ringan sampai yang cukup mengganggu konsentrasi saat mengerjakan soal.'],
                ['type' => 'h2', 'text' => 'Kenali Gejalanya'],
                ['type' => 'p', 'text' => 'Gejala kecemasan ujian bisa muncul secara fisik, seperti jantung berdebar, tangan berkeringat, dan sulit tidur menjelang hari-H. Secara psikologis, bisa berupa pikiran blank, sulit fokus, atau overthinking terhadap kemungkinan gagal.'],
                ['type' => 'h2', 'text' => 'Teknik Mengelola Kecemasan'],
                ['type' => 'p', 'text' => 'Beberapa teknik berikut terbukti membantu menurunkan kecemasan dalam waktu singkat: latihan pernapasan, grounding lewat panca indra, dan menuliskan kekhawatiran sebelum ujian dimulai agar pikiran lebih lega.'],
                ['type' => 'quote', 'text' => 'Tarik napas 4 hitungan, tahan 4 hitungan, embuskan 6 hitungan. Ulangi 5 kali sebelum ujian dimulai.'],
                ['type' => 'p', 'text' => 'Persiapan yang matang jauh-jauh hari juga mengurangi rasa cemas. Membagi materi belajar menjadi bagian kecil dan berlatih soal secara rutin membuat otak lebih terbiasa dengan format ujian, sehingga rasa asing yang memicu cemas berkurang.'],
                ['type' => 'p', 'text' => 'Jika kecemasan terasa berat dan terus berulang, jangan ragu untuk konsultasi dengan konselor di layanan konseling kampus.'],
            ],
        ],
        [
            'id' => 2, 'type' => 'video', 'topic' => 'Akademik', 'meta' => '15 menit',
            'author' => 'Dr. Rina Kusumawati', 'date' => '28 Agustus 2026',
            'title' => 'Teknik Pomodoro: Belajar Efektif dengan Manajemen Waktu',
            'youtube_id' => 'aqz-KE-bpKQ',
            'description' => 'Video ini membahas cara kerja teknik Pomodoro — belajar fokus 25 menit, istirahat 5 menit — dan bagaimana menerapkannya untuk sesi belajar maupun mengerjakan tugas kuliah agar lebih produktif tanpa cepat lelah.',
        ],
        [
            'id' => 3, 'type' => 'worksheet', 'topic' => 'Kesehatan Mental', 'meta' => '5 pertanyaan',
            'author' => 'Dr. Sari Dewi', 'date' => '20 Agustus 2026',
            'title' => 'Jurnal Mindfulness Harian: Lembar Kerja Refleksi Diri',
            'instruction' => 'Luangkan waktu 10–15 menit di tempat yang tenang. Jawab setiap pertanyaan dengan jujur dan tanpa terburu-buru — tidak ada jawaban benar atau salah di sini.',
            'questions' => [
                ['id' => 'q1', 'label' => 'Bagaimana perasaanmu hari ini? Coba gambarkan dengan 3 kata.'],
                ['id' => 'q2', 'label' => 'Apa satu hal yang membuatmu bersyukur hari ini?'],
                ['id' => 'q3', 'label' => 'Momen apa yang terasa paling menantang hari ini, dan bagaimana kamu meresponsnya?'],
                ['id' => 'q4', 'label' => 'Apa yang sedang kamu pikirkan atau khawatirkan saat ini?'],
                ['id' => 'q5', 'label' => 'Satu langkah kecil apa yang bisa kamu lakukan besok untuk merawat dirimu sendiri?'],
            ],
        ],
        [
            'id' => 4, 'type' => 'artikel', 'topic' => 'Pribadi', 'meta' => '7 menit baca',
            'author' => 'Drs. Agus Santoso', 'date' => '15 Agustus 2026',
            'title' => 'Membangun Kepercayaan Diri: Panduan Pengembangan Diri',
            'photo' => $unsplash('1512314889357-e157c22f938d'),
            'excerpt' => 'Kepercayaan diri bukan bakat bawaan — ia bisa dilatih dan dibangun secara bertahap.',
            'content' => [
                ['type' => 'p', 'text' => 'Banyak mahasiswa merasa kurang percaya diri saat harus tampil di depan kelas, berorganisasi, atau menghadapi lingkungan baru. Kabar baiknya, kepercayaan diri adalah keterampilan yang bisa dilatih.'],
                ['type' => 'h2', 'text' => 'Mulai dari Hal Kecil'],
                ['type' => 'p', 'text' => 'Berhasil menyelesaikan tugas-tugas kecil secara konsisten membangun bukti nyata pada diri sendiri bahwa kamu mampu. Rasa percaya diri tumbuh dari akumulasi pengalaman berhasil, sekecil apa pun itu.'],
                ['type' => 'h2', 'text' => 'Ubah Dialog Internal'],
                ['type' => 'p', 'text' => 'Perhatikan cara kamu berbicara pada diri sendiri. Ganti "aku pasti gagal" menjadi "aku belum pernah mencoba, mari lihat hasilnya". Perubahan kecil dalam self-talk berdampak besar pada keberanian bertindak.'],
                ['type' => 'quote', 'text' => 'Percaya diri bukan berarti tidak takut — tapi tetap melangkah meski merasa takut.'],
                ['type' => 'p', 'text' => 'Terakhir, kelilingi diri dengan lingkungan yang suportif. Dukungan dari teman, keluarga, atau komunitas sangat membantu proses ini berjalan lebih ringan.'],
            ],
        ],
        [
            'id' => 5, 'type' => 'video', 'topic' => 'Kesehatan Mental', 'meta' => '10 menit',
            'author' => 'Dr. Sari Dewi', 'date' => '10 Agustus 2026',
            'title' => 'Teknik Pernapasan untuk Mengelola Stres Seketika',
            'youtube_id' => 'aqz-KE-bpKQ',
            'description' => 'Panduan praktik teknik pernapasan box breathing dan pernapasan diafragma yang bisa langsung dipraktikkan saat merasa cemas atau stres mendadak, misalnya sebelum presentasi atau wawancara.',
        ],
        [
            'id' => 6, 'type' => 'worksheet', 'topic' => 'Karier', 'meta' => '6 pertanyaan',
            'author' => 'Dr. Rina Kusumawati', 'date' => '5 Agustus 2026',
            'title' => 'Refleksi Karier: Menemukan Arah Setelah Lulus',
            'instruction' => 'Worksheet ini membantumu memetakan minat, kekuatan, dan kekhawatiran seputar karier setelah lulus. Isi sejujur mungkin, hasilnya bisa jadi bahan diskusi dengan konselor karier.',
            'questions' => [
                ['id' => 'q1', 'label' => 'Aktivitas apa yang membuatmu merasa paling bersemangat selama kuliah?'],
                ['id' => 'q2', 'label' => 'Sebutkan 3 kekuatan atau keahlian yang paling kamu banggakan.'],
                ['id' => 'q3', 'label' => 'Bidang pekerjaan atau industri apa yang selama ini menarik perhatianmu?'],
                ['id' => 'q4', 'label' => 'Apa kekhawatiran terbesarmu soal dunia kerja setelah lulus?'],
                ['id' => 'q5', 'label' => 'Siapa sosok (role model) yang jalur kariernya ingin kamu pelajari, dan mengapa?'],
                ['id' => 'q6', 'label' => 'Satu langkah konkret apa yang bisa kamu ambil bulan ini untuk mendekati tujuan kariermu?'],
            ],
        ],
        [
            'id' => 7, 'type' => 'artikel', 'topic' => 'Sosial', 'meta' => '6 menit baca',
            'author' => 'Drs. Agus Santoso', 'date' => '29 Juli 2026',
            'title' => 'Seni Berkomunikasi Asertif di Lingkungan Kampus',
            'photo' => $unsplash('1511632765486-a01980e01a18'),
            'excerpt' => 'Asertif bukan berarti kasar — ini soal menyampaikan pendapat dengan jelas dan tetap menghargai orang lain.',
            'content' => [
                ['type' => 'p', 'text' => 'Komunikasi asertif adalah kemampuan menyampaikan pikiran, perasaan, dan kebutuhan secara jujur dan jelas, tanpa mengorbankan hak diri sendiri maupun hak orang lain.'],
                ['type' => 'h2', 'text' => 'Bedanya dengan Pasif dan Agresif'],
                ['type' => 'p', 'text' => 'Komunikasi pasif cenderung memendam pendapat demi menghindari konflik, sementara komunikasi agresif menyampaikan pendapat dengan cara yang menyerang. Asertif berada di tengah — tegas namun tetap sopan.'],
                ['type' => 'h2', 'text' => 'Cara Melatihnya'],
                ['type' => 'p', 'text' => 'Gunakan kalimat "aku" alih-alih "kamu", misalnya "aku merasa keberatan kalau jadwal rapat diubah mendadak" dibanding "kamu selalu seenaknya ubah jadwal". Latih juga kontak mata dan nada bicara yang tenang saat menyampaikan pendapat di kelompok atau organisasi.'],
                ['type' => 'p', 'text' => 'Semakin sering dilatih, komunikasi asertif akan terasa lebih alami dan membantumu membangun relasi yang lebih sehat di lingkungan kampus.'],
            ],
        ],
        [
            'id' => 8, 'type' => 'video', 'topic' => 'Karier', 'meta' => '12 menit',
            'author' => 'Dr. Rina Kusumawati', 'date' => '20 Juli 2026',
            'title' => 'Panduan Menulis CV dan Surat Lamaran yang Memikat',
            'youtube_id' => 'aqz-KE-bpKQ',
            'description' => 'Tips praktis menyusun CV dan surat lamaran yang ringkas, relevan, dan menonjolkan pencapaian — lengkap dengan kesalahan umum yang sebaiknya dihindari mahasiswa tingkat akhir.',
        ],
        [
            'id' => 9, 'type' => 'worksheet', 'topic' => 'Akademik', 'meta' => '5 pertanyaan',
            'author' => 'Prof. Bambang Widodo', 'date' => '12 Juli 2026',
            'title' => 'Worksheet Manajemen Waktu: Rencanakan Minggumu',
            'instruction' => 'Gunakan worksheet ini di awal minggu untuk memetakan prioritas dan potensi hambatan, supaya jadwal kuliah, tugas, dan waktu istirahatmu lebih seimbang.',
            'questions' => [
                ['id' => 'q1', 'label' => 'Apa 3 prioritas utamamu minggu ini?'],
                ['id' => 'q2', 'label' => 'Tugas atau deadline apa saja yang perlu diselesaikan minggu ini?'],
                ['id' => 'q3', 'label' => 'Kapan waktu terbaikmu untuk fokus belajar (pagi, siang, malam)? Mengapa?'],
                ['id' => 'q4', 'label' => 'Apa yang biasanya membuat rencanamu berantakan, dan bagaimana mengantisipasinya?'],
                ['id' => 'q5', 'label' => 'Kapan kamu akan menjadwalkan waktu istirahat/me-time minggu ini?'],
            ],
        ],
    ];

    // Ambil ID dari URL /self-help/{id}. Nanti kalau sudah ada route resource,
    // ganti baris ini dengan parameter dari controller.
    $id       = (int) (request()->route('id') ?? request()->segment(2) ?? 1);
    $resource = $resource ?? collect($resources)->firstWhere('id', $id) ?? $resources[0];
    $type     = $types[$resource['type']];
@endphp

{{-- ===================== BREADCRUMB ===================== --}}
<div class="shd-crumb-bar">
    <div class="container">
        <a href="{{ url('/self-help') }}" class="shd-back">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Kembali ke Self Help
        </a>
    </div>
</div>

{{-- ===================== HEADER MATERI ===================== --}}
<section class="shd-header">
    <div class="container shd-header-inner">
        <span class="shd-type-badge shd-type-{{ $resource['type'] }}">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $type['icon'] !!}</svg>
            {{ $type['label'] }}
        </span>

        <h1>{{ $resource['title'] }}</h1>

        <div class="shd-meta-row">
            <span class="shd-topic">{{ $resource['topic'] }}</span>
            <span class="shd-dot">&middot;</span>
            <span>oleh {{ $resource['author'] }}</span>
            <span class="shd-dot">&middot;</span>
            <span>{{ $resource['date'] }}</span>
            <span class="shd-dot">&middot;</span>
            <span>{{ $resource['meta'] }}</span>
        </div>
    </div>
</section>

{{-- ===================== KONTEN MATERI ===================== --}}
<section class="section-pad shd-section">
    <div class="container shd-container">

        @switch($resource['type'])

            {{-- ==================== ARTIKEL ==================== --}}
            @case('artikel')
                @if (!empty($resource['photo']))
                    <img src="{{ $resource['photo'] }}" alt="{{ $resource['title'] }}" class="shd-cover">
                @endif

                <article class="shd-prose">
                    @foreach ($resource['content'] as $block)
                        @if ($block['type'] === 'h2')
                            <h2>{{ $block['text'] }}</h2>
                        @elseif ($block['type'] === 'quote')
                            <blockquote>{{ $block['text'] }}</blockquote>
                        @else
                            <p>{{ $block['text'] }}</p>
                        @endif
                    @endforeach
                </article>
            @break

            {{-- ==================== VIDEO ==================== --}}
            @case('video')
                <div class="shd-video-wrap">
                    <iframe
                        src="https://www.youtube.com/embed/{{ $resource['youtube_id'] }}"
                        title="{{ $resource['title'] }}"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                        loading="lazy">
                    </iframe>
                </div>

                <div class="shd-video-desc">
                    <h2>Tentang video ini</h2>
                    <p>{{ $resource['description'] }}</p>
                </div>
            @break

            {{-- ==================== WORKSHEET ==================== --}}
            @case('worksheet')
            @case('modul')
                <div class="shd-worksheet" data-resource-id="{{ $resource['id'] }}" data-resource-title="{{ $resource['title'] }}" data-resource-type="{{ $resource['type'] }}" data-katalog='@json($katalog ?? [])'>
                    <img src="{{ $resource['photo'] }}" alt="{{ $resource['title'] }}" class="shd-cover" loading="lazy">

                    <p class="shd-ws-intro">{{ $resource['instruction'] }}</p>

                    @if (session('worksheet_result'))
                        <div class="shd-ws-result" id="wsResultCard">
                            <div class="shd-ws-result-icon">✓</div>
                            <h2>Worksheet berhasil diselesaikan</h2>
                            <p>{{ session('worksheet_success') ?: session('worksheet_warning') }}</p>
                            <div class="shd-ws-result-actions">
                                <button type="button" class="shd-btn shd-btn-primary" id="wsDownloadResult">Download Jawaban PDF</button>
                                <a class="shd-btn shd-btn-outline" href="{{ route('self-help.detail', $resource['id']) }}">Kembali ke Worksheet</a>
                            </div>
                            <script type="application/json" id="wsResultData">@json(session('worksheet_result'))</script>
                        </div>
                    @endif

                    <div class="shd-ws-card" id="wsGate">
                        <h2 class="shd-ws-card-title">Isi Identitas Terlebih Dahulu</h2>
                        <p class="shd-ws-card-sub">Identitas diperlukan agar hasil worksheet dapat disimpan dan dikirim ke email.</p>

                        <form id="wsGateForm" class="shd-ws-form-grid" novalidate>
                            <label class="shd-field">
                                <span>Nama Lengkap</span>
                                <input type="text" id="wsNama" required placeholder="Contoh: Ahmad Fauzan">
                            </label>
                            <label class="shd-field">
                                <span>Email</span>
                                <input type="email" id="wsEmail" required placeholder="nama@email.com">
                            </label>
                            <label class="shd-field">
                                <span>Fakultas</span>
                                <select id="wsFakultas" required>
                                    <option value="">Pilih fakultas</option>
                                    @foreach (($katalog ?? []) as $namaFakultas => $jenjangProdi)
                                        <option value="{{ $namaFakultas }}">{{ $namaFakultas }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="shd-field">
                                <span>Program Studi</span>
                                <select id="wsProdi" required>
                                    <option value="">Pilih fakultas terlebih dahulu</option>
                                </select>
                            </label>
                            <label class="shd-field">
                                <span>NIM</span>
                                <input type="text" id="wsNim" required placeholder="Contoh: 1301422000">
                            </label>
                            <label class="shd-module-consent">
                                <input type="checkbox" id="wsConsent" required>
                                <span>Saya menyetujui data identitas dan jawaban digunakan untuk penyimpanan hasil Modul Self Help, pengiriman ringkasan ke email, dan evaluasi layanan secara terbatas.</span>
                            </label>

                            <button type="submit" class="shd-btn shd-btn-primary shd-ws-start">
                                Mulai Mengisi Worksheet
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                            </button>
                        </form>
                    </div>

                    <form class="shd-ws-card" id="wsForm" action="{{ route('self-help.submit', $resource['id']) }}" method="POST" hidden>
                        @csrf
                        <div class="shd-ws-identity" id="wsIdentityBar"></div>
                        <input type="hidden" name="nama" id="wsNamaHidden">
                        <input type="hidden" name="email" id="wsEmailHidden">
                        <input type="hidden" name="fakultas" id="wsFakultasHidden">
                        <input type="hidden" name="prodi" id="wsProdiHidden">
                        <input type="hidden" name="nim" id="wsNimHidden">
                        <div class="shd-pdf-viewer" id="wsPdfViewer" data-pdf="{{ $resource['file_pdf'] ?? '' }}" data-blocks='@json($resource['blocks'] ?? [])'>
                            <div class="shd-pdf-toolbar"><strong>{{ $resource['type'] === 'modul' ? 'Langkah Modul' : 'Worksheet PDF' }}</strong><span id="wsPdfStatus">Memuat halaman...</span></div>
                            <div class="shd-module-progress" aria-label="Progress modul"><span id="wsProgressBar"></span></div>
                            <div class="shd-pdf-nav">
                                <button type="button" id="wsPrevPage" disabled>‹ Sebelumnya</button>
                                <span id="wsPageCounter">Halaman 1</span>
                                <button type="button" id="wsNextPage">Berikutnya ›</button>
                            </div>
                            <div id="wsPdfPages"></div>
                            <div class="shd-pdf-nav shd-pdf-nav-bottom">
                                <button type="button" id="wsPrevPageBottom" disabled>‹ Sebelumnya</button>
                                <span id="wsPageCounterBottom">Halaman 1</span>
                                <button type="button" id="wsNextPageBottom">Berikutnya ›</button>
                            </div>
                        </div>
                        <div class="shd-ws-footer">
                            <p class="shd-ws-note">Isi jawaban pada halaman yang ditentukan, lalu kirim hasilnya untuk disimpan dan dikirim ke email.</p>
                            <button type="submit" class="shd-btn shd-btn-primary" id="wsSubmitButton">Kirim Hasil Modul</button>
                        </div>
                    </form>
                </div>
            @break
        @endswitch

    </div>
</section>

{{-- =====================================================================
     CSS TAMBAHAN (khusus halaman Detail Self Help)
     Dipakai ulang dari landing/list: .container .section-pad
     Warna badge jenis disamakan dengan self-help_blade.php (.sh-type-*)
     ===================================================================== --}}
<style>
    .shd-pdf-frame{width:100%;min-height:680px;border:1px solid #dce8df;border-radius:12px;background:#f8faf9}
    /* ---------- Breadcrumb ---------- */
    .shd-crumb-bar { padding: 20px 0; border-bottom: 1px solid #EEF1EC; }
    .shd-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 14px;
        font-weight: 500;
        color: #4B5563;
        text-decoration: none;
    }
    .shd-back:hover { color: var(--primary, #7DA381); }

    /* ---------- Header ---------- */
    .shd-header { background: #E9F3EA; padding: 40px 0 36px; }
    .shd-header-inner { max-width: 760px; margin: 0 auto; }
    .shd-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 13px;
        font-size: 12.5px;
        font-weight: 600;
        border-radius: 999px;
        margin-bottom: 16px;
    }
    .shd-type-artikel   { background: #DBEAFE; color: #1E40AF; }
    .shd-type-video     { background: #F3F4F6; color: #374151; }
    .shd-type-worksheet { background: #FEF3C7; color: #92400E; }

    .shd-header h1 {
        font-size: clamp(24px, 3.4vw, 34px);
        font-weight: 700;
        line-height: 1.3;
        margin: 0 0 16px;
    }
    .shd-meta-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
        font-size: 14px;
        color: #6B7280;
    }
    .shd-topic {
        font-size: 12px;
        font-weight: 600;
        color: #166534;
        background: #E4F5E8;
        padding: 3px 11px;
        border-radius: 999px;
    }
    .shd-dot { color: #C7CDC3; }

    /* ---------- Kontainer konten ---------- */
    .shd-container { max-width: 760px; margin: 0 auto; }

    /* ---------- Artikel ---------- */
    .shd-cover {
        width: 100%;
        aspect-ratio: 2 / 1;
        object-fit: cover;
        border-radius: 16px;
        margin-bottom: 32px;
    }
    .shd-prose { font-size: 16px; line-height: 1.8; color: #374151; }
    .shd-prose h2 {
        font-size: 20px;
        font-weight: 700;
        color: #111827;
        margin: 32px 0 12px;
    }
    .shd-prose p { margin: 0 0 18px; }
    .shd-prose blockquote {
        margin: 24px 0;
        padding: 14px 20px;
        border-left: 4px solid var(--primary, #7DA381);
        background: #F3F8F3;
        border-radius: 0 10px 10px 0;
        font-style: italic;
        color: #2F4A34;
    }

    /* ---------- Video ---------- */
    .shd-video-wrap {
        position: relative;
        width: 100%;
        aspect-ratio: 16 / 9;
        border-radius: 16px;
        overflow: hidden;
        background: #000;
        margin-bottom: 28px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .1);
    }
    .shd-video-wrap iframe {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        border: 0;
    }
    .shd-video-desc h2 {
        font-size: 18px;
        font-weight: 700;
        margin: 0 0 10px;
    }
    .shd-video-desc p {
        font-size: 15.5px;
        line-height: 1.75;
        color: #4B5563;
        margin: 0;
    }

    /* ---------- Worksheet ---------- */
    .shd-ws-intro {
        font-size: 15.5px;
        line-height: 1.75;
        color: #4B5563;
        margin: 0 0 24px;
    }
    .shd-ws-card {
        background: #fff;
        border: 1px solid #E5E9E1;
        border-radius: 16px;
        padding: 28px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
    }
    .shd-ws-card + .shd-ws-card { margin-top: 20px; }
    .shd-ws-card-title { font-size: 18px; font-weight: 700; margin: 0 0 6px; }
    .shd-ws-card-sub { font-size: 13.5px; color: #6B7280; margin: 0 0 22px; }

    .shd-ws-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .shd-field { display: flex; flex-direction: column; gap: 6px; grid-column: span 1; }
    .shd-field span { font-size: 13px; font-weight: 600; color: #374151; }
    .shd-field input,
    .shd-field select {
        height: 44px;
        padding: 0 14px;
        font: inherit;
        font-size: 14px;
        border: 1px solid #D9DED4;
        border-radius: 10px;
        outline: none;
        transition: border-color .15s, box-shadow .15s;
        background: #fff;
        color: #1F2937;
        width: 100%;
        box-sizing: border-box;
    }
    .shd-field select {
        cursor: pointer;
        appearance: auto;
    }
    .shd-field input:focus,
    .shd-field select:focus {
        border-color: var(--primary, #7DA381);
        box-shadow: 0 0 0 4px rgba(125, 163, 129, .18);
    }
    .shd-field select:disabled {
        background: #F3F4F6;
        color: #9CA3AF;
        cursor: not-allowed;
    }

    .shd-ws-start { grid-column: 1 / -1; justify-self: start; margin-top: 4px; }

    .shd-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font: inherit;
        font-size: 14.5px;
        font-weight: 600;
        padding: 12px 22px;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        transition: background .15s, transform .1s;
    }
    .shd-btn-primary { background: var(--primary, #7DA381); color: #fff; }
    .shd-btn-primary:hover { background: #6d9273; }
    .shd-btn-primary:active { transform: scale(.98); }

    .shd-ws-identity {
        font-size: 13.5px;
        color: #4B5563;
        background: #F3F8F3;
        border: 1px solid #DCEBDD;
        border-radius: 10px;
        padding: 10px 16px;
        margin-bottom: 24px;
    }

    .shd-ws-question { margin-bottom: 22px; }
    .shd-ws-question label {
        display: block;
        font-size: 14.5px;
        font-weight: 600;
        color: #1F2937;
        margin-bottom: 8px;
        line-height: 1.5;
    }
    .shd-ws-question textarea {
        width: 100%;
        font: inherit;
        font-size: 14.5px;
        line-height: 1.6;
        padding: 12px 14px;
        border: 1px solid #D9DED4;
        border-radius: 10px;
        resize: vertical;
        outline: none;
        transition: border-color .15s, box-shadow .15s;
    }
    .shd-ws-question textarea:focus {
        border-color: var(--primary, #7DA381);
        box-shadow: 0 0 0 4px rgba(125, 163, 129, .18);
    }

    .shd-ws-footer {
        border-top: 1px solid #EEF1EC;
        margin-top: 8px;
        padding-top: 20px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }
    .shd-ws-note { font-size: 12.5px; color: #9CA3AF; max-width: 420px; margin: 0; line-height: 1.6; }

    /* ---------- Responsive ---------- */
    @media (max-width: 640px) {
        .shd-header { padding: 32px 0 28px; }
        .shd-ws-card { padding: 20px; }
        .shd-ws-form-grid { grid-template-columns: 1fr; }
        .shd-ws-footer { flex-direction: column; align-items: stretch; }
        .shd-ws-footer .shd-btn { justify-content: center; }
    }
    .shd-ws-alert { padding:14px 18px; border-radius:10px; margin-bottom:16px; font-size:14px; }
    .shd-ws-alert-success { background:#E8F5E9; color:#23613A; }
    .shd-ws-alert-warning { background:#FFF7E6; color:#8A5A00; }
    .shd-pdf-viewer { margin-top:18px; border:1px solid #E5E7EB; border-radius:14px; background:#F8FAFC; overflow:hidden; }
    .shd-pdf-toolbar { display:flex; justify-content:space-between; padding:12px 16px; background:#F1F5F9; color:#475569; font-size:13px; }
    #wsPdfPages { display:block; overflow:visible; touch-action:pan-y; }
    .shd-pdf-page { box-sizing:border-box; width:100%; padding:16px; border-bottom:1px solid #E5E7EB; }
    .shd-pdf-page h3 { margin:0 0 10px; font-size:14px; color:#475569; }
    .shd-pdf-page canvas { display:block; max-width:100%; height:auto; margin:0 auto 14px; background:white; box-shadow:0 2px 8px rgba(15,23,42,.12); }
    .shd-page-questions { display:grid; gap:12px; }
    .shd-page-questions:empty { display:none; }
    .shd-ws-question { padding:14px; border:1px solid #DCE7DE; border-radius:10px; background:#fff; }
    .shd-ws-question label { display:block; font-weight:600; line-height:1.5; margin-bottom:8px; color:#243B2A; }
    .shd-ws-question textarea { width:100%; box-sizing:border-box; border:1px solid #CBD5E1; border-radius:8px; padding:10px; resize:vertical; font:inherit; }
    .shd-pdf-fallback { padding:20px; background:#fff; color:#64748B; }
    .shd-pdf-nav { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:10px 16px; background:#fff; border-bottom:1px solid #E5E7EB; }
    .shd-pdf-nav button { border:1px solid #C9D8CC; background:#fff; color:#356443; border-radius:8px; padding:8px 12px; cursor:pointer; font:inherit; }
    .shd-pdf-nav button:disabled { opacity:.45; cursor:not-allowed; }
    .shd-pdf-nav span { font-size:13px; color:#64748B; font-weight:600; }
    .shd-pdf-nav-bottom { border-top:1px solid #E5E7EB; border-bottom:0; }
    .shd-btn-outline { background:#fff; color:#356443; border:1px solid #BFD2C2; text-decoration:none; }
    .shd-ws-result { background:#F3FAF4; border:1px solid #B9D9BE; border-radius:16px; padding:28px; text-align:center; margin-bottom:24px; }
    .shd-ws-result-icon { width:42px; height:42px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; background:#76A77D; color:#fff; font-size:25px; font-weight:700; }
    .shd-ws-result h2 { margin:12px 0 6px; color:#285A34; font-size:20px; }
    .shd-ws-result p { margin:0 auto 18px; color:#52705A; max-width:560px; }
    .shd-ws-result-actions { display:flex; flex-wrap:wrap; gap:10px; justify-content:center; }
    .shd-module-consent { grid-column:1/-1; display:flex; gap:10px; align-items:flex-start; padding:12px 14px; border:1px solid #d8e6da; border-radius:10px; background:#f7fbf8; color:#486052; font-size:12.5px; line-height:1.5; }
    .shd-module-consent input { margin-top:3px; accent-color:#3f6d5d; flex:none; }
    .shd-module-progress { height:7px; margin:0 16px; border-radius:99px; overflow:hidden; background:#e8f0ea; }
    .shd-module-progress span { display:block; height:100%; width:0; border-radius:inherit; background:linear-gradient(90deg,#5f9670,#2f6a4b); transition:width .2s ease; }
    .shd-modul-page { padding:22px; background:#fff; border:1px solid #dce7de; border-radius:12px; }
    .shd-modul-block { padding:4px 0 18px; color:#405348; line-height:1.65; }
    .shd-modul-block h3 { margin:0 0 8px; color:#285a34; font-size:18px; }
    .shd-video-embed { position:relative; width:100%; aspect-ratio:16/9; overflow:hidden; border-radius:12px; background:#111827; margin-top:10px; }
    .shd-video-embed iframe { position:absolute; inset:0; width:100%; height:100%; border:0; }
    .shd-worksheet.is-filling .shd-cover { display:none; }
</style>

@if (in_array($resource['type'], ['worksheet', 'modul'], true))
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var wrap = document.querySelector('.shd-worksheet');
            var gate = document.getElementById('wsGate');
            var gateForm = document.getElementById('wsGateForm');
            var form = document.getElementById('wsForm');
            var viewer = document.getElementById('wsPdfViewer');
            var pages = document.getElementById('wsPdfPages');
            var status = document.getElementById('wsPdfStatus');
            var prevPage = document.getElementById('wsPrevPage');
            var nextPage = document.getElementById('wsNextPage');
            var pageCounter = document.getElementById('wsPageCounter');
            var prevPageBottom = document.getElementById('wsPrevPageBottom');
            var nextPageBottom = document.getElementById('wsNextPageBottom');
            var pageCounterBottom = document.getElementById('wsPageCounterBottom');
            var progressBar = document.getElementById('wsProgressBar');
            var submitButton = document.getElementById('wsSubmitButton');
            var consentInput = document.getElementById('wsConsent');
            var questions = @json($resource['questions'] ?? []);
            var pdfUrl = viewer ? viewer.dataset.pdf : '';
            var resourceType = wrap ? wrap.dataset.resourceType : 'worksheet';
            var blocks = [];
            try { blocks = JSON.parse(viewer?.dataset.blocks || '[]'); } catch (e) {}
            var currentPage = 0;
            var pageSections = [];
            var storageKey = 'sh_worksheet_identity_' + wrap.dataset.resourceId;
            var katalog = {};
            try { katalog = JSON.parse(wrap.dataset.katalog || '{}'); } catch (e) {}
            var fakultasSelect = document.getElementById('wsFakultas');
            var prodiSelect = document.getElementById('wsProdi');
            var resultCard = document.getElementById('wsResultCard');
            if (resultCard) {
                gate.hidden = true; form.hidden = true;
                var resultButton = document.getElementById('wsDownloadResult');
                if (resultButton) resultButton.addEventListener('click', function () {
                    var node = document.getElementById('wsResultData'); var result = {};
                    try { result = JSON.parse(node.textContent || '{}'); } catch (e) {}
                    var printable = document.createElement('div');
                    // Jangan menaruh elemen di luar viewport karena html2canvas
                    // dapat menghasilkan canvas/PDF kosong pada beberapa browser.
                    // Elemen dibuat sementara di viewport lalu dihapus setelah selesai.
                    printable.style.cssText = 'display:block;background:#fff;font-family:Arial,sans-serif;color:#1F2937;padding:28px;width:700px;min-height:420px;box-sizing:border-box;position:fixed;left:0;top:0;z-index:2147483000;';
                    var html = '<h1 style="font-size:20px;margin:0 0 6px;">' + esc(result.judul) + '</h1>';
                    html += '<p style="color:#6B7280;font-size:12px;margin:0 0 18px;">Hasil Worksheet Self Help SILAKON</p>';
                    html += '<p style="font-size:13px;line-height:1.7;">Nama: ' + esc(result.nama) + '<br>Email: ' + esc(result.email) + '<br>NIM: ' + esc(result.nim) + '<br>Fakultas: ' + esc(result.fakultas) + '<br>Program Studi: ' + esc(result.prodi) + '</p><hr>';
                    (result.jawaban || []).forEach(function (item, i) { html += '<div style="margin:14px 0;"><b>' + (i + 1) + '. ' + esc(item.pertanyaan) + '</b><p style="white-space:pre-wrap;border-left:3px solid #7DA381;padding-left:10px;">' + esc(item.jawaban || '(kosong)') + '</p></div>'; });
                    printable.innerHTML = html; document.body.appendChild(printable);
                    requestAnimationFrame(function () {
                        if (!window.html2pdf) { window.print(); printable.remove(); return; }
                        html2pdf().set({margin:10,filename:'hasil-worksheet-' + String(result.nama || 'konseli').replace(/[^a-z0-9]+/gi,'-') + '.pdf',html2canvas:{scale:2,backgroundColor:'#fff',useCORS:true, logging:false},jsPDF:{unit:'mm',format:'a4'}}).from(printable).save().then(function(){ printable.remove(); }).catch(function(){ printable.remove(); window.print(); });
                    });
                });
            }
            function isiProdi(fakultas, pilihan) {
                prodiSelect.innerHTML = '<option value="">Pilih program studi</option>';
                var levels = katalog[fakultas] || {};
                Object.keys(levels).forEach(function (jenjang) {
                    (levels[jenjang] || []).forEach(function (prodi) {
                        var option = document.createElement('option'); option.value = prodi; option.textContent = jenjang + ' — ' + prodi;
                        if (pilihan && pilihan === prodi) option.selected = true;
                        prodiSelect.appendChild(option);
                    });
                });
                // Tetap dapat diklik; opsi akan diisi setelah fakultas dipilih.
                prodiSelect.disabled = false;
            }
            fakultasSelect.addEventListener('change', function () { isiProdi(fakultasSelect.value, ''); });

            function value(id) { return (document.getElementById(id)?.value || '').trim(); }
            function identity() { return {nama:value('wsNama'), email:value('wsEmail'), fakultas:value('wsFakultas'), prodi:value('wsProdi'), nim:value('wsNim')}; }
            function esc(s) { return String(s || '').replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]; }); }
            function syncIdentity(data) {
                Object.keys(data).forEach(function(k){ var target=document.getElementById('ws'+k.charAt(0).toUpperCase()+k.slice(1)+'Hidden'); if(target) target.value=data[k]||''; });
                document.getElementById('wsIdentityBar').innerHTML = '<strong>'+esc(data.nama)+'</strong> · '+esc(data.prodi)+', '+esc(data.fakultas)+' · NIM '+esc(data.nim)+' · '+esc(data.email);
            }
            function questionHtml(q, index) {
                return '<div class="shd-ws-question"><label for="wsAnswer'+index+'">'+(index+1)+'. '+esc(q.label)+'</label><textarea id="wsAnswer'+index+'" data-ws-answer="1" name="jawaban['+index+']" rows="3" placeholder="Tulis jawabanmu di sini..."></textarea></div>';
            }
            function addQuestions(pageNo, container) {
                questions.forEach(function(q,index){ if(Number(q.page || 1) === pageNo) container.insertAdjacentHTML('beforeend', questionHtml(q,index)); });
            }
            function pageIsComplete(index) {
                var fields = pageSections[index] ? pageSections[index].querySelectorAll('textarea[data-ws-answer]') : [];
                return Array.prototype.every.call(fields, function(field) { return field.value.trim() !== ''; });
            }
            function updatePageControls() {
                var total = pageSections.length || 1;
                pages.style.width = '100%';
                pages.style.transform = 'none';
                pageSections.forEach(function (section, index) { section.style.display = index === currentPage ? 'block' : 'none'; });
                pages.style.height = 'auto';
                pageCounter.textContent = 'Halaman ' + (currentPage + 1) + ' dari ' + total;
                prevPage.disabled = currentPage === 0;
                nextPage.hidden = currentPage >= total - 1;
                nextPage.disabled = !pageIsComplete(currentPage);
                prevPageBottom.disabled = currentPage === 0;
                nextPageBottom.hidden = currentPage >= total - 1;
                nextPageBottom.disabled = !pageIsComplete(currentPage);
                pageCounterBottom.textContent = pageCounter.textContent;
                if (progressBar) progressBar.style.width = (((currentPage + 1) / total) * 100) + '%';
                if (submitButton) submitButton.hidden = resourceType === 'modul' ? currentPage < total - 1 : false;
            }
            function finishRender() {
                updatePageControls();
                pages.querySelectorAll('textarea[data-ws-answer]').forEach(function (field) {
                    field.addEventListener('input', updatePageControls);
                });
            }
            function goPrevious() { if (currentPage > 0) { currentPage--; updatePageControls(); } }
            function goNext() { if (pageIsComplete(currentPage) && currentPage < pageSections.length - 1) { currentPage++; updatePageControls(); } }
            prevPage.addEventListener('click', goPrevious); prevPageBottom.addEventListener('click', goPrevious);
            nextPage.addEventListener('click', goNext); nextPageBottom.addEventListener('click', goNext);
            var swipeStartX = null;
            pages.addEventListener('touchstart', function (e) { swipeStartX = e.changedTouches[0].clientX; }, {passive:true});
            pages.addEventListener('touchend', function (e) {
                if (swipeStartX === null) return;
                var distance = e.changedTouches[0].clientX - swipeStartX;
                swipeStartX = null;
                if (Math.abs(distance) < 45) return;
                if (distance < 0 && pageIsComplete(currentPage) && currentPage < pageSections.length - 1) currentPage++;
                if (distance > 0 && currentPage > 0) currentPage--;
                updatePageControls();
            }, {passive:true});
            function saveAnswers() {
                var answers = {};
                form.querySelectorAll('textarea[data-ws-answer]').forEach(function (field) { answers[field.name] = field.value; });
                try { localStorage.setItem(storageKey + '_answers', JSON.stringify(answers)); } catch (e) {}
            }
            function restoreAnswers() {
                var answers = {};
                try { answers = JSON.parse(localStorage.getItem(storageKey + '_answers') || '{}'); } catch (e) {}
                Object.keys(answers).forEach(function (name) { var field = form.querySelector('[name="' + name + '"]'); if (field) field.value = answers[name] || ''; });
            }
            function renderFallback() {
                status.textContent = resourceType === 'modul' ? 'Materi terpadu' : 'PDF belum tersedia';
                if (resourceType === 'modul') {
                    pages.innerHTML = ''; pageSections = [];
                    blocks.forEach(function (b, i) {
                        var type = String(b.tipe || 'teks').toLowerCase();
                        var section = document.createElement('section'); section.className = 'shd-pdf-page shd-modul-page';
                        var box = document.createElement('div'); box.className = 'shd-modul-block';
                        if (type === 'video') {
                            var url = String(b.url || b.link || '');
                            var videoId = '';
                            try {
                                var parsed = new URL(url);
                                if (parsed.hostname.indexOf('youtu.be') >= 0) videoId = parsed.pathname.replace(/^\//, '').split('/')[0];
                                else if (parsed.hostname.indexOf('youtube.com') >= 0) videoId = parsed.searchParams.get('v') || parsed.pathname.split('/').filter(Boolean).pop();
                            } catch (e) {}
                            var embed = videoId
                                ? '<div class="shd-video-embed"><iframe src="https://www.youtube.com/embed/' + encodeURIComponent(videoId) + '?rel=0" title="' + esc(b.judul || 'Video Modul') + '" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe></div>'
                                : '<p>Video tidak dapat dimuat. Periksa tautan YouTube pada materi.</p>';
                            box.innerHTML = '<h3>' + esc(b.judul || 'Video') + '</h3>' + embed + (b.keterangan ? '<p class="shd-video-description">' + esc(b.keterangan) + '</p>' : '');
                        } else if (type === 'soal') {
                            var questionLabels = String(b.pertanyaan || b.isi || ('Pertanyaan ' + (i + 1))).split(/\r?\n+/).map(function(q){ return q.trim(); }).filter(Boolean);
                            box.innerHTML = '<h3>' + esc(b.judul || 'Pertanyaan') + '</h3>';
                            questionLabels.forEach(function(questionLabel) {
                                var qIndex = questions.findIndex(function(q){ return q.label === questionLabel; });
                                if (qIndex < 0) { qIndex = questions.length; questions.push({label: questionLabel}); }
                                box.insertAdjacentHTML('beforeend', questionHtml({label: questionLabel}, qIndex));
                            });
                        } else if (type === 'pdf') {
                            var pdfUrl = b.url || b.file_pdf || '';
                            box.innerHTML = '<h3>' + esc(b.judul || 'Dokumen PDF') + '</h3>' +
                                (pdfUrl ? '<iframe class="shd-pdf-frame" src="' + esc(pdfUrl) + '#toolbar=1" title="Dokumen PDF"></iframe>' : '<p>Dokumen PDF belum tersedia.</p>');
                        } else if (type === 'kegiatan') {
                            box.innerHTML = '<h3>' + esc(b.judul || 'Kegiatan') + '</h3><p style="white-space:pre-wrap">' + esc(b.instruksi || b.isi || '') + '</p>';
                        } else {
                            box.innerHTML = '<h3>' + esc(b.judul || '') + '</h3><p style="white-space:pre-wrap">' + esc(b.isi || '') + '</p>';
                        }
                        section.appendChild(box); pages.appendChild(section); pageSections.push(section);
                    });
                    currentPage = 0; finishRender(); restoreAnswers(); updatePageControls(); return;
                }
                var total = Math.max(1, questions.reduce(function(max, q) { return Math.max(max, Number(q.page || 1)); }, 1));
                pages.innerHTML = ''; pageSections = [];
                for (var pageNo = 1; pageNo <= total; pageNo++) {
                    var section = document.createElement('section'); section.className='shd-pdf-page';
                    if (pageNo === 1) { var card=document.createElement('div'); card.className='shd-pdf-fallback'; card.innerHTML='<strong>Dokumen worksheet belum tersedia.</strong><p>Silakan isi pertanyaan pada setiap halaman.</p>'; section.appendChild(card); }
                    var qbox=document.createElement('div'); qbox.className='shd-page-questions'; addQuestions(pageNo, qbox); section.appendChild(qbox);
                    pageSections.push(section); pages.appendChild(section);
                }
                currentPage = 0; finishRender();
            }
            function renderPdf() {
                if (!pdfUrl || !window.pdfjsLib) { renderFallback(); return; }
                pdfjsLib.GlobalWorkerOptions.workerSrc='https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                pdfjsLib.getDocument(pdfUrl).promise.then(function(pdf){
                    status.textContent=pdf.numPages+' halaman';
                    pages.innerHTML = ''; pageSections = [];
                    for (let pageNo=1; pageNo<=pdf.numPages; pageNo++) {
                        var section=document.createElement('section'); section.className='shd-pdf-page';
                        var title=document.createElement('h3'); title.textContent='Halaman '+pageNo; section.appendChild(title);
                        var canvas=document.createElement('canvas'); section.appendChild(canvas);
                        var qbox=document.createElement('div'); qbox.className='shd-page-questions'; addQuestions(pageNo,qbox); section.appendChild(qbox); pages.appendChild(section);
                        pageSections.push(section);
                    }
                    currentPage = 0; finishRender();
                    pageSections.forEach(function(section, index) { pdf.getPage(index + 1).then(function(page) { var canvas=section.querySelector('canvas'); var viewport=page.getViewport({scale:1.2}); canvas.width=viewport.width; canvas.height=viewport.height; return page.render({canvasContext:canvas.getContext('2d'), viewport:viewport}).promise; }).then(function () { requestAnimationFrame(updatePageControls); }).catch(function () { status.textContent='PDF gagal dimuat'; }); });
                }).catch(renderFallback);
            }
            gateForm.addEventListener('submit', function(e){ e.preventDefault(); var data=identity(); if(!data.nama||!data.email||!data.fakultas||!data.prodi||!data.nim||!consentInput?.checked){ gateForm.reportValidity(); return; } localStorage.setItem(storageKey,JSON.stringify(data)); syncIdentity(data); wrap.classList.add('is-filling'); var header=document.querySelector('.shd-header'); if(header) header.hidden=true; var intro=document.querySelector('.shd-ws-intro'); if(intro) intro.hidden=true; gate.hidden=true; form.hidden=false; renderPdf(); });
            try { var saved=JSON.parse(localStorage.getItem(storageKey)||'null'); if(saved && saved.nama){ document.getElementById('wsNama').value=saved.nama||''; document.getElementById('wsEmail').value=saved.email||''; document.getElementById('wsNim').value=saved.nim||''; fakultasSelect.value=saved.fakultas||''; isiProdi(saved.fakultas||'', saved.prodi||''); } } catch(e){}
            form.addEventListener('submit', function(e){
                var incomplete = pageSections.findIndex(function (_, index) { return !pageIsComplete(index); });
                if (incomplete !== -1) {
                    e.preventDefault();
                    currentPage = incomplete;
                    updatePageControls();
                    alert('Isi seluruh pertanyaan pada setiap halaman sebelum mengirim worksheet.');
                    return;
                }
                syncIdentity(identity());
                saveAnswers();
            });
            form.addEventListener('input', saveAnswers);
        });
    </script>
    <script>
    // Fallback pengisian prodi agar dropdown tetap terhubung ke data master.
    (function () {
        var fakultas = document.getElementById('wsFakultas');
        var prodi = document.getElementById('wsProdi');
        if (!fakultas || !prodi) return;
        var katalog = @json($katalog ?? []);
        function isiProdiMandiri() {
            var levels = katalog[fakultas.value || ''] || {};
            prodi.innerHTML = '<option value="">Pilih program studi</option>';
            Object.keys(levels).forEach(function (jenjang) {
                var group = document.createElement('optgroup'); group.label = jenjang;
                (levels[jenjang] || []).forEach(function (nama) {
                    var option = document.createElement('option'); option.value = nama; option.textContent = nama; group.appendChild(option);
                });
                prodi.appendChild(group);
            });
            prodi.disabled = false;
        }
        fakultas.addEventListener('change', isiProdiMandiri);
        isiProdiMandiri();
    })();
    </script>
@endif

@endsection

