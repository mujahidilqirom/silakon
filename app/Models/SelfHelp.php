<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class SelfHelp extends Model
{
    protected $table = 'self_help';
    protected $fillable = ['judul','tipe','topik','status','author_id','detail','thumbnail','file_pdf'];
    protected $casts = ['detail' => 'array'];
    public function author(): BelongsTo { return $this->belongsTo(User::class, 'author_id'); }

    public function pengerjaan()
    {
        return $this->hasMany(SelfHelpPengerjaan::class, 'self_help_id');
    }

    /** Cover eksternal yang konsisten berdasarkan topik materi. */
    public static function coverUrl(?string $topik, ?string $judul = null): string
    {
        $covers = [
            'Kesehatan Mental' => ['1506126613408-eca07ce68773', '1499209974431-9dddcece7f88', '1505751172876-fa1923c5c528'],
            'Akademik' => ['1434030216411-0b793f4b4173', '1523240795612-9a054b0db644', '1523050854058-8df90110c9f1'],
            'Karier' => ['1521737711867-e3b97375f902', '1551836022-d5d88e9218df', '1521791055366-0d553872125f'],
            'Pribadi' => ['1499750310107-5fef28a66643', '1517245386807-bb43f82c33c4', '1497366754035-f200968a6e72'],
            'Sosial' => ['1529156069898-49953e39b3ac', '1529398063799-6d920e392a4b', '1501785888041-af3ef285b470'],
        ];
        $pilihan = $covers[$topik] ?? ['1516321318423-f06f85e504b3', '1497366811353-6870744d04b2'];
        $id = $pilihan[abs(crc32((string) $judul)) % count($pilihan)];
        return "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w=1200&h=640&q=75";
    }
}
