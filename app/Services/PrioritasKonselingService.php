<?php

namespace App\Services;

use App\Models\Pengajuan;
use Carbon\Carbon;
use App\Models\Setting;

/**
 * Perhitungan WHO-5 dan SAW untuk rekomendasi prioritas pemeriksaan.
 * WHO-5 bukan alat diagnosis; hasil ini hanya membantu antrean Admin.
 */
class PrioritasKonselingService
{
    public const WHO5_BOBOT = 0.70;
    public const AGING_BOBOT = 0.30;

    private function parameter(): array
    {
        $s = Setting::whereIn('key', ['saw_bobot_who5','saw_bobot_aging','saw_batas_tinggi','saw_batas_sedang'])->pluck('value','key');
        $who = max(0, min(100, (int) ($s['saw_bobot_who5'] ?? 70)));
        $aging = (int) ($s['saw_bobot_aging'] ?? (100 - $who));
        $aging = max(0, min(100, $aging));
        if ($who + $aging !== 100) { $who = 70; $aging = 30; }
        return ['who' => $who / 100, 'aging' => $aging / 100, 'tinggi' => ((int) ($s['saw_batas_tinggi'] ?? 67)) / 100, 'sedang' => ((int) ($s['saw_batas_sedang'] ?? 34)) / 100];
    }

    /** WHO-5 terdiri dari 5 jawaban bernilai 0..5, lalu dikonversi menjadi 0..100. */
    public function skorWho5(array $jawaban): int
    {
        $nilai = array_map(static fn ($value) => max(0, min(5, (int) $value)), array_values($jawaban));
        return (int) round((array_sum($nilai) / 25) * 100);
    }

    /**
     * SAW: skor WHO-5 diperlakukan sebagai kriteria cost (semakin rendah,
     * semakin tinggi prioritas), lalu aging memberi kesempatan pada antrean lama.
     */
    public function hitung(array $jawaban, ?Carbon $dibuatPada = null, bool $darurat = false): array
    {
        $who5 = $this->skorWho5($jawaban);
        $nilaiWho5 = (100 - $who5) / 100;
        $hariMenunggu = max(0, ($dibuatPada ?: now())->diffInDays(now()));
        $nilaiAging = min($hariMenunggu / 30, 1);
        $parameter = $this->parameter();
        $nilaiSaw = ($nilaiWho5 * $parameter['who']) + ($nilaiAging * $parameter['aging']);

        // Kondisi darurat tetap menjadi aturan operasional terpisah.
        if ($darurat) {
            $nilaiSaw = 1.0;
        }

        return [
            'who5_skor' => $who5,
            'saw_nilai' => round($nilaiSaw, 4),
            'prioritas' => $darurat ? 'Darurat' : $this->label($nilaiSaw),
            'hari_menunggu' => $hariMenunggu,
            'nilai_who5' => round($nilaiWho5, 4),
            'nilai_aging' => round($nilaiAging, 4),
        ];
    }

    public function untuk(Pengajuan $pengajuan): array
    {
        $data = (array) ($pengajuan->data ?? []);
        $jawaban = (array) ($data['who5_jawaban'] ?? []);
        if ($jawaban === [] && $pengajuan->who5_skor === null) {
            return ['who5_skor' => null, 'saw_nilai' => null, 'prioritas' => 'Belum dihitung', 'hari_menunggu' => null];
        }
        if ($jawaban === [] && $pengajuan->who5_skor !== null) {
            return $this->hitungDariSkor((int) $pengajuan->who5_skor, $pengajuan->created_at, data_get($data, 'kondisi_darurat') === 'ya');
        }
        return $this->hitung($jawaban, $pengajuan->created_at, data_get($data, 'kondisi_darurat') === 'ya');
    }

    /** Hitung ulang data lama yang tidak menyimpan jawaban WHO-5 per butir. */
    private function hitungDariSkor(int $who5, ?Carbon $dibuatPada, bool $darurat = false): array
    {
        $hariMenunggu = max(0, ($dibuatPada ?: now())->diffInDays(now()));
        $nilaiWho5 = (100 - max(0, min(100, $who5))) / 100;
        $nilaiAging = min($hariMenunggu / 30, 1);
        $parameter = $this->parameter();
        $nilaiSaw = ($nilaiWho5 * $parameter['who']) + ($nilaiAging * $parameter['aging']);
        if ($darurat) $nilaiSaw = 1.0;
        return [
            'who5_skor' => $who5,
            'saw_nilai' => round($nilaiSaw, 4),
            'prioritas' => $darurat ? 'Darurat' : $this->label($nilaiSaw),
            'hari_menunggu' => $hariMenunggu,
            'nilai_who5' => round($nilaiWho5, 4),
            'nilai_aging' => round($nilaiAging, 4),
        ];
    }

    private function label(float $nilai): string
    {
        $parameter = $this->parameter();
        return match (true) {
            $nilai >= $parameter['tinggi'] => 'Tinggi',
            $nilai >= $parameter['sedang'] => 'Sedang',
            default => 'Rendah',
        };
    }
}
