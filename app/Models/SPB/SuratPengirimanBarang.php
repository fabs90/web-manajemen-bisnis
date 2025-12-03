<?php

namespace App\Models\SPB;

use App\Models\Faktur\FakturPenjualan;
use App\Models\SPP\PesananPembelian;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class SuratPengirimanBarang extends Model
{
    protected $table = "surat_pengiriman_barang";
    protected $fillable = [
        "spp_id",
        "nomor_pengiriman_barang",
        "jenis_pengiriman",
        "tanggal_terima",
        "keadaan",
        "keterangan",
        "nama_penerima",
        "nama_pengirim",
        "user_id",
    ];

    public function pesananPembelian()
    {
        return $this->belongsTo(PesananPembelian::class, "spp_id");
    }

    public function suratPengirimanBarangDetail()
    {
        return $this->hasMany(SuratPengirimanBarangDetail::class, "spb_id");
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fakturPenjualan()
    {
        return $this->hasOne(FakturPenjualan::class, "spb_id", "id");
    }
}
