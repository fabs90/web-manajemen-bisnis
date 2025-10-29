<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NeracaAwal extends Model
{
    use HasFactory;
    protected $table = "neraca_awal";
    protected $fillable = [
        "user_id",
        "kas_awal",
        "total_piutang",
        "total_hutang",
        "total_persediaan",
        "modal_awal",
        "tanah_bangunan",
        "kendaraan",
        "meubel_peralatan",
        "total_debit",
        "total_kredit",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function barang()
    {
        return $this->belongsToMany(
            Barang::class,
            "barang_neraca_awal",
        )->withPivot("user_id");
    }

    public function bukuBesarKas()
    {
        return $this->hasMany(BukuBesarKas::class);
    }
}
