<?php
namespace App\Http\Controllers;
use App\Models\SelfHelp;
use App\Models\SelfHelpPengerjaan;
use App\Models\Konseli;
use App\Models\MasterFakultas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;
use App\Support\InteractiveMail;
class SelfHelpController extends Controller
{
    public function index()
    {
        $resources = SelfHelp::with('author')->where('status', 'Dipublikasi')->where('tipe', 'Modul')->latest()->get()->map(fn ($m) => $this->map($m))->values()->all();
        return view('self-help', compact('resources'));
    }
    public function show(int $id)
    {
        $resource = $this->map(SelfHelp::with('author')->where('status', 'Dipublikasi')->where('tipe', 'Modul')->findOrFail($id), true);
        $katalog = MasterFakultas::with(['prodi' => fn ($q) => $q->where('aktif', true)->orderBy('jenjang')->orderBy('nama')])
            ->where('aktif', true)->orderBy('nama')->get()->mapWithKeys(fn ($f) => [
                $f->nama => $f->prodi->groupBy('jenjang')->map->pluck('nama')->all(),
            ])->all();
        return view('self-help-detail', compact('resource', 'katalog'));
    }

    public function submit(Request $request, int $id)
    {
        $materi = SelfHelp::where('status', 'Dipublikasi')->whereIn('tipe', ['Worksheet', 'Modul'])->findOrFail($id);
        $data = $request->validate([
            'nama' => 'required|string|max:150',
            'email' => 'required|email|max:255',
            'nim' => 'required|string|max:40',
            'fakultas' => 'required|string|max:150',
            'prodi' => 'required|string|max:150',
            'jawaban' => 'required|array',
            'jawaban.*' => 'nullable|string|max:5000',
        ]);
        $konseli = Konseli::where('email', $data['email'])->first();
        $pengerjaan = SelfHelpPengerjaan::create([
            'self_help_id' => $materi->id,
            'konseli_id' => $konseli?->id,
            'nama' => $data['nama'],
            'nim' => $data['nim'],
            'fakultas' => $data['fakultas'],
            'prodi' => $data['prodi'],
            'email' => $data['email'],
            'jawaban' => $data['jawaban'],
            'selesai_at' => now(),
        ]);

        $terkirim = true;
        try {
            $jawaban = collect($data['jawaban'])->map(fn ($jawab, $nomor) => ($nomor + 1) . '. ' . ($jawab ?: '(kosong)'))->implode("\n\n");
            InteractiveMail::send($data['email'], 'Hasil Modul Self Help - '.$materi->judul, [
                'greeting' => 'Halo, '.$data['nama'],
                'message' => 'Anda telah menyelesaikan modul self help. Berikut identitas pengerjaan dan jawaban yang tersimpan untuk Anda.',
                'status' => 'Modul selesai',
                'details' => ['Modul' => $materi->judul, 'NIM' => $data['nim'], 'Fakultas' => $data['fakultas'], 'Program Studi' => $data['prodi'], 'Ringkasan jawaban' => $jawaban],
                'quote' => 'Terima kasih telah meluangkan waktu untuk memahami diri sendiri.',
            ]);
        } catch (Throwable) {
            $terkirim = false;
        }

        $hasil = [
            'judul' => $materi->judul,
            'nama' => $data['nama'], 'email' => $data['email'], 'nim' => $data['nim'],
            'fakultas' => $data['fakultas'], 'prodi' => $data['prodi'],
            'jawaban' => collect($this->questionsFor($materi))->values()->map(function ($q, $i) use ($data) {
                return ['pertanyaan' => $q['teks'] ?? $q['label'] ?? ('Pertanyaan ' . ($i + 1)), 'jawaban' => $data['jawaban'][$i] ?? ''];
            })->all(),
        ];

        return redirect()->route('self-help.detail', $id)->with('worksheet_result', $hasil)->with($terkirim ? 'worksheet_success' : 'worksheet_warning', $terkirim
            ? 'Worksheet selesai. Hasilnya sudah dikirim ke email Anda.'
            : 'Worksheet tersimpan, tetapi email belum dapat dikirim. Hubungi admin untuk mendapatkan hasilnya.');
    }
    private function map(SelfHelp $m, bool $detail = false): array
    {
        $d = $m->detail ?: [];
        $role = strtolower((string) ($m->author?->role ?? ''));
        $dibuatOleh = str_contains($role, 'koordinator') ? 'Koordinator' : (str_contains($role, 'admin') ? 'Admin' : 'Tim SILAKON');
        $r = ['id' => $m->id, 'type' => strtolower($m->tipe), 'topic' => $m->topik, 'meta' => $d['durasi'] ?? ($d['meta'] ?? (in_array($m->tipe, ['Worksheet','Modul'], true) ? count($this->questionsFor($m)) . ' pertanyaan' : '')), 'description' => $d['deskripsi'] ?? $d['keterangan'] ?? '', 'author' => $dibuatOleh, 'date' => optional($m->created_at)->translatedFormat('d F Y'), 'title' => $m->judul, 'photo' => $m->thumbnail ? (str_starts_with($m->thumbnail, 'http') ? $m->thumbnail : asset('storage/' . $m->thumbnail)) : SelfHelp::coverUrl($m->topik, $m->judul)];
        if ($m->tipe === 'Artikel') { $r['excerpt'] = $d['excerpt'] ?? Str::limit(strip_tags($d['isi'] ?? ''), 150); $r['content'] = $d['content'] ?? [['type' => 'p', 'text' => $d['isi'] ?? '']]; }
        if ($m->tipe === 'Video') { $url = $d['link_youtube'] ?? ''; parse_str(parse_url($url, PHP_URL_QUERY) ?: '', $q); $r['youtube_id'] = $q['v'] ?? basename(parse_url($url, PHP_URL_PATH) ?: ''); $r['description'] = $d['keterangan'] ?? ''; }
        if ($m->tipe === 'Worksheet') {
            $r['instruction'] = $d['instruction'] ?? 'Isi worksheet secara bertahap. Pertanyaan akan muncul sesuai halaman PDF.';
            $pdf = $m->file_pdf ?: ($d['file_pdf'] ?? null);
            // Gunakan URL relatif untuk file lokal agar tetap satu origin saat
            // aplikasi dibuka melalui 127.0.0.1 maupun localhost.
            $r['file_pdf'] = $pdf ? (str_starts_with($pdf, 'http') ? $pdf : '/storage/' . ltrim($pdf, '/')) : null;
            $r['questions'] = collect($d['pertanyaan'] ?? $d['questions'] ?? [])->values()->map(fn ($q, $i) => ['id' => $q['id'] ?? 'q' . ($i + 1), 'label' => $q['teks'] ?? $q['label'] ?? '', 'page' => (int) ($q['halaman'] ?? $q['page'] ?? 1)])->all();
        }
        if ($m->tipe === 'Modul') {
            $blok = collect($d['blok'] ?? [])->values()->all();
            $r['blocks'] = $blok;
            $r['questions'] = collect($this->questionsFor($m))->values()->map(fn ($q, $i) => ['id' => 'q' . ($i + 1), 'label' => $q['teks'] ?? '', 'page' => 1])->all();
            $r['instruction'] = 'Ikuti materi secara berurutan, lalu jawab pertanyaan dan lakukan kegiatan yang tersedia.';
        }
        return $r;
    }

    private function questionsFor(SelfHelp $m): array
    {
        if ($m->tipe === 'Worksheet') return $m->detail['pertanyaan'] ?? $m->detail['questions'] ?? [];
        return collect($m->detail['blok'] ?? [])->filter(fn ($b) => ($b['tipe'] ?? '') === 'soal')->map(fn ($b) => ['teks' => $b['pertanyaan'] ?? $b['isi'] ?? ''])->values()->all();
    }
}
