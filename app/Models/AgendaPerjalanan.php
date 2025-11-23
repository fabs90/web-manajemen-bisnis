<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgendaPerjalanan extends Model
{
    public $table = "agenda_perjalanan";

    protected $fillable = [
        "user_id",
        "nama_pelaksana",
        "jabatan",
        "tujuan",
        "tanggal_mulai",
        "tanggal_selesai",
        "keperluan",
        "disiapkan_oleh",
        "tanggal_disiapkan",
        "disetujui_oleh",
        "tanggal_disetujui",
        "transport",
        "akomodasi",
        "konsumsi",
        "lain_lain",
        "total_biaya",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function agendaPerjalananDetail()
    {
        return $this->hasMany(AgendaPerjalananDetail::class);
    }

    public function agendaPerjalananAkomodasi()
    {
        return $this->hasMany(AgendaPerjalananAkomodasi::class);
    }

    public function agendaPerjalananKontak()
    {
        return $this->hasMany(AgendaPerjalananKontak::class);
    }

    public function agendaPerjalananTransportasi()
    {
        return $this->hasMany(AgendaPerjalananTransportasi::class);
    }
}
