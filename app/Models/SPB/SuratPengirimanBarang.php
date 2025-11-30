<?php

namespace App\Models\SPB;

use App\Models\Faktur\FakturPenjualan;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class SuratPengirimanBarang extends Model
{
    protected $table = "surat_pengiriman_barang";
    protected $fillable = [
        "nomor_surat",
        "tanggal_barang_diterima",
        "keadaan",
        "keterangan",
        "nama_penerima",
        "nama_pengirim",
        "user_id",
        "faktur_penjualan_id",
    ];

    public function fakturPenjualan()
    {
        return $this->belongsTo(FakturPenjualan::class, "faktur_penjualan_id");
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
