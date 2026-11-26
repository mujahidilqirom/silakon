<?php
namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Pengajuan;
use App\Models\MasterFakultas;
use App\Models\MasterProgramStudi;
use App\Models\MasterKategoriMasalah;
use App\Models\MasterLokasiRuang;
use App\Models\MasterTemplatePesan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
class AdminDataMasterController extends Controller
{
 private function jsonOk(string $message){ return response()->json(['message'=>$message]); }
 public function fakultasStore(Request $r){$d=$r->validate(['nama'=>'required|string|max:150','singkatan'=>'required|string|max:20']);$f=MasterFakultas::create($d+['aktif'=>true]);return response()->json(['message'=>'Fakultas tersimpan','id'=>$f->id]);}
 public function fakultasDestroy(int $id){MasterFakultas::findOrFail($id)->delete();return $this->jsonOk('Fakultas dihapus');}
 public function prodiStore(Request $r){$d=$r->validate(['fakultas_id'=>'required|exists:master_fakultas,id','nama'=>'required|string|max:150','jenjang'=>'required|in:D3,S1,S2,S3,Profesi']);$p=MasterProgramStudi::create($d+['aktif'=>true]);return response()->json(['message'=>'Program studi tersimpan','id'=>$p->id]);}
 public function prodiUpdate(Request $r,int $id){$d=$r->validate(['nama'=>'required|string|max:150','jenjang'=>'required|in:D3,S1,S2,S3,Profesi']);MasterProgramStudi::findOrFail($id)->update($d);return $this->jsonOk('Program studi diperbarui');}
 public function prodiDestroy(int $id){MasterProgramStudi::findOrFail($id)->delete();return $this->jsonOk('Program studi dihapus');}
 public function kategoriStore(Request $r){$d=$r->validate(['nama'=>'required|string|max:150']);$k=MasterKategoriMasalah::create($d+['aktif'=>true]);return response()->json(['message'=>'Kategori tersimpan','id'=>$k->id]);}
 public function kategoriToggle(int $id){$k=MasterKategoriMasalah::findOrFail($id);$k->update(['aktif'=>!$k->aktif]);return $this->jsonOk('Status kategori diperbarui');}
 public function kategoriDestroy(int $id){MasterKategoriMasalah::findOrFail($id)->delete();return $this->jsonOk('Kategori dihapus');}
 public function lokasiStore(Request $r){$d=$r->validate(['nama'=>'required|string|max:150','deskripsi'=>'required|string|max:500']);$l=MasterLokasiRuang::create($d+['aktif'=>true]);return response()->json(['message'=>'Lokasi tersimpan','id'=>$l->id]);}
 public function lokasiUpdate(Request $r,int $id){$d=$r->validate(['nama'=>'required|string|max:150','deskripsi'=>'required|string|max:500']);MasterLokasiRuang::findOrFail($id)->update($d);return $this->jsonOk('Lokasi diperbarui');}
 public function lokasiToggle(int $id){$l=MasterLokasiRuang::findOrFail($id);$l->update(['aktif'=>!$l->aktif]);return $this->jsonOk('Status lokasi diperbarui');}
 public function lokasiDestroy(int $id){MasterLokasiRuang::findOrFail($id)->delete();return $this->jsonOk('Lokasi dihapus');}
 public function index(Request $request){$tabs=collect([['key'=>'konselor','label'=>'Konselor'],['key'=>'fakultas-prodi','label'=>'Fakultas & Prodi'],['key'=>'kategori-masalah','label'=>'Kategori Masalah'],['key'=>'lokasi-ruang','label'=>'Lokasi & Ruang']]);$tabAktif=$tabs->contains('key',$request->query('tab'))?$request->query('tab'):'konselor';return view('admin.data-master',['menuAktif'=>'data-master','tab'=>$tabs,'tabAktif'=>$tabAktif,'labelTab'=>$tabs->firstWhere('key',$tabAktif)['label'],'konselor'=>$this->dataKonselor(),'fakultasProdi'=>$this->dataFakultasProdi(),'kategoriMasalah'=>$this->dataKategoriMasalah(),'lokasiRuang'=>$this->dataLokasiRuang()]);}
 private function dataKonselor(){return User::where('role','konselor')->latest()->get()->map(function($u){$n=preg_replace('/\D/','',$u->phone??'');return ['nama'=>$u->name,'email'=>$u->email,'whatsapp'=>$n,'whatsapp_tampil'=>$n?('0'.substr($n,2)):'-','keterangan'=>'Konselor','status'=>$u->is_active?'Aktif':'Nonaktif','status_tone'=>$u->is_active?'hijau':'abu'];})->values();}
 private function dataFakultasProdi(){
  if (Schema::hasTable('master_fakultas')) {
    $normalisasi = static function ($nama): string {
     $k = mb_strtolower(trim((string) $nama));
     return match ($k) {
      'fakultas teknik', 'ft' => 'Fakultas Teknik (FT)',
      'fakultas bahasa dan seni', 'fbs' => 'Fakultas Bahasa dan Seni (FBS)',
      'fakultas matematika & ilmu pengetahuan alam', 'fmipa' => 'Fakultas Matematika & Ilmu Pengetahuan Alam (FMIPA)',
      'fakultas ilmu pendidikan dan psikologi', 'fipp' => 'Fakultas Ilmu Pendidikan dan Psikologi (FIPP)',
      default => trim((string) $nama),
     };
    };
    return MasterFakultas::with(['prodi'=>fn($q)=>$q->where('aktif',true)->orderBy('nama')])->where('aktif',true)->orderBy('nama')->get()
     ->groupBy(fn($f) => $normalisasi($f->nama))
     ->map(function($grup, $nama) {
      $utama = $grup->first();
      $prodi = $grup->flatMap(fn($f) => $f->prodi)->unique(fn($p) => mb_strtolower($p->jenjang.'|'.$p->nama))->sortBy('nama')->values();
      preg_match('/\(([^)]+)\)$/', $nama, $m);
      return ['id'=>$utama->id,'nama'=>$nama,'singkatan'=>$m[1] ?? $utama->singkatan,'prodi'=>$prodi->map(fn($p)=>['id'=>$p->id,'nama'=>$p->nama,'jenjang'=>$p->jenjang])->values()];
     })->values();
  }
  $catalog=config('silakon.katalog_fakultas_prodi',[]);
  $groups=collect($catalog)->map(fn($levels)=>collect($levels)->flatMap(fn($programs,$jenjang)=>collect($programs)->map(fn($nama)=>['nama'=>$nama,'jenjang'=>$jenjang])));
  /* Keep submitted values visible when a legacy or manually entered faculty exists. */
  /* The canonical faculty/prodi list above remains the source for the form and master data. */
  /*
  $fallback=[
   'Fakultas Ilmu Pendidikan dan Psikologi (FIPP)'=>['Bimbingan dan Konseling','Teknologi Pendidikan','Pendidikan Non Formal','Psikologi','Pendidikan Guru Sekolah Dasar','Pendidikan Guru PAUD','Pengembangan Kurikulum','Pendidikan Anak Usia Dini','Pendidikan Bimbingan dan Konseling','Pendidikan Profesi Konselor'],
   'Fakultas Bahasa dan Seni (FBS)'=>['Desain Komunikasi Visual','Pendidikan Bahasa dan Sastra Indonesia','Sastra Indonesia','Pendidikan Bahasa Inggris','Sastra Inggris','Pendidikan Bahasa Perancis','Sastra Perancis','Pendidikan Bahasa Jepang','Pendidikan Bahasa Arab','Pendidikan Bahasa Mandarin','Pendidikan Seni Rupa','Seni Rupa','Pendidikan Seni Musik','Pendidikan Seni Tari','Pendidikan Bahasa Jawa','Sastra Jawa','Pendidikan Bahasa Indonesia','Pendidikan Seni','Ilmu Pendidikan Bahasa'],
   'Fakultas Ilmu Sosial dan Ilmu Politik (FISIP)'=>['Pendidikan Sosiologi dan Antropologi','Pendidikan Pancasila dan Kewarganegaraan','Pendidikan Sejarah','Ilmu Sejarah','Ilmu Politik','Geografi','Pendidikan Geografi','Pendidikan Ilmu Pengetahuan Sosial','Ilmu Komunikasi','Kajian Sejarah'],
   'Fakultas Matematika & Ilmu Pengetahuan Alam (FMIPA)'=>['Statistika Terapan dan Komputasi','Pendidikan Matematika','Matematika','Pendidikan Fisika','Fisika','Pendidikan Kimia','Kimia','Pendidikan Biologi','Biologi','Pendidikan Ilmu Pengetahuan Alam','Teknik Informatika','Sistem Informasi','Ilmu Lingkungan','Statistika dan Sains Data','Informatika'],
   'Fakultas Teknik (FT)'=>['Pendidikan Teknik Bangunan','Teknik Sipil','Arsitektur','Pendidikan Teknik Mesin','Teknik Mesin','Pendidikan Teknik Otomotif','Pendidikan Teknik Elektro','Teknik Elektro','Pendidikan Teknik Informatika dan Komputer','Pendidikan Kesejahteraan Keluarga','Pendidikan Tata Kecantikan','Pendidikan Tata Boga','Pendidikan Tata Busana','Teknik Kimia','Teknik Komputer','Program Profesi Insinyur'],
   'Fakultas Ilmu Keolahragaan (FIK)'=>['Ilmu Keolahragaan','Pendidikan Kesehatan Jasmani dan Rekreasi','Pendidikan Jasmani Sekolah Dasar','Pendidikan Kepelatihan Olahraga','Pendidikan Olahraga'],
   'Fakultas Ekonomika dan Bisnis (FEB)'=>['Manajemen','Akuntansi','Ekonomi Pembangunan','Pendidikan Ekonomi','Pendidikan Akuntansi','Pendidikan Administrasi Perkantoran','Ekonomi dan Keuangan Islam','Ilmu Ekonomi'],
   'Fakultas Hukum (FH)'=>['Ilmu Hukum'],
   'Fakultas Kedokteran (FK)'=>['Kedokteran','Kedokteran Hewan','Ilmu Kesehatan Masyarakat','Gizi','Farmasi','Profesi Dokter'],
   'Sekolah Pascasarjana'=>['Penelitian dan Evaluasi Pendidikan','Pendidikan Dasar','Pendidikan Kejuruan','Manajemen Pendidikan','Pendidikan Vokasi','Pendidikan Profesi Guru (PPG)'],
  ]; */
   foreach(Pengajuan::get() as $p){$d=$p->data??[];$f=$d['mahasiswa_fakultas']??$d['alumni_fakultas']??$d['fakultas']??null;$pr=$d['mahasiswa_jurusan']??$d['alumni_jurusan']??$d['prodi']??$d['program_studi']??null;if($f && $pr){$k=mb_strtolower(trim((string)$f));$f=match($k){'fakultas teknik','ft'=>'Fakultas Teknik (FT)','fakultas bahasa dan seni','fbs'=>'Fakultas Bahasa dan Seni (FBS)',default=>$f};if(!$groups->has($f))$groups[$f]=collect([['nama'=>$pr,'jenjang'=>'S1']]);}}
  return $groups->map(function($prodi,$nama){preg_match('/\(([^)]+)\)$/',$nama,$m);$singkatan=$m[1]??'';return ['id'=>md5($nama),'nama'=>$nama,'singkatan'=>$singkatan,'prodi'=>$prodi->values()->map(fn($item,$i)=>['id'=>$i+1,'nama'=>$item['nama'],'jenjang'=>$item['jenjang']])];})->values();
 }
 private function dataKategoriMasalah(){
  if (Schema::hasTable('master_kategori_masalah')) return MasterKategoriMasalah::where('aktif',true)->orderBy('nama')->get()->map(fn($k)=>['id'=>$k->id,'nama'=>$k->nama,'status'=>'Aktif','status_tone'=>'hijau','jumlah'=>Pengajuan::get()->filter(fn($p)=>in_array($k->nama,(array)data_get($p->data,'lingkup_hambatan',[]),true))->count()]);
  $labels=['Motivasi','Stres kuliah','Depresi','Harga diri/kepercayaan diri','Pengekspresian diri/perasaan','Kehilangan','Kecemasan/panik','Trauma','Pengasuhan','Home Sickness','Putus asa','Manajemen Keuangan','Konflik dengan pasangan','Tekanan Keluarga','Manajemen Waktu','Spiritualitas/Keberagamaan','Hambatan di masa transisi','Kesepian','Konflik interpersonal','Karier','Yang lain'];$counts=collect();
  foreach(Pengajuan::get() as $p){foreach((array)data_get($p->data,'lingkup_hambatan',[]) as $x){$counts[$x]=($counts[$x]??0)+1;}}
  return collect($labels)->map(fn($nama)=>['id'=>md5($nama),'nama'=>$nama,'status'=>'Aktif','status_tone'=>'hijau','jumlah'=>$counts[$nama]??0]);
 }
 private function dataLokasiRuang(){if(Schema::hasTable('master_lokasi_ruang')) return MasterLokasiRuang::where('aktif',true)->orderBy('nama')->get()->map(fn($l)=>['id'=>$l->id,'nama'=>$l->nama,'deskripsi'=>$l->deskripsi,'status'=>'Aktif','status_tone'=>'hijau']);return collect([['id'=>1,'nama'=>'Ruang Konseling LPPP Unnes','deskripsi'=>'Ruang layanan konseling LPPP Unnes','status'=>'Aktif','status_tone'=>'hijau'],['id'=>2,'nama'=>'LAB BK FIPP','deskripsi'=>'Laboratorium Bimbingan Konseling FIPP','status'=>'Aktif','status_tone'=>'hijau'],['id'=>3,'nama'=>'Zoom','deskripsi'=>'Sesi konseling daring melalui Zoom','status'=>'Aktif','status_tone'=>'hijau']]);}
 private function dataTemplatePesan(){$v=['nama_konseli','kode_tiket','nama_konselor','tanggal','waktu','lokasi'];$rows=Schema::hasTable('master_template_pesan')?MasterTemplatePesan::where('aktif',true)->orderBy('nama')->get()->map(fn($t)=>['id'=>$t->kode,'nama'=>$t->nama,'kanal'=>$t->kanal,'isi'=>$t->isi]):collect([['id'=>'pengajuan-diterima','nama'=>'Pengajuan Diterima','kanal'=>'Email','isi'=>'Yth. {{nama_konseli}}, pengajuan konseling {{kode_tiket}} telah diterima.'],['id'=>'jadwal-ditetapkan','nama'=>'Jadwal Ditetapkan','kanal'=>'Email','isi'=>'Jadwal konseling {{kode_tiket}} bersama {{nama_konselor}} pada {{tanggal}}, pukul {{waktu}} di {{lokasi}}.'],['id'=>'pengingat-h1','nama'=>'Pengingat H-1','kanal'=>'WhatsApp','isi'=>'Pengingat sesi konseling {{kode_tiket}} besok pukul {{waktu}} di {{lokasi}}.']]);return $rows->map(function($t)use($v){$t=(array)$t;$t['kanal_tone']=$t['kanal']==='WhatsApp'?'hijau':'biru';$t['isi_html']=e($t['isi']);$t['variabel']=collect($v)->map(fn($x)=>'{{'.$x.'}}')->all();return $t;});}
}
