<?php

namespace App\Services;

use App\Models\AgendaJanjiTemu;
use Barryvdh\DomPDF\Facade\Pdf;
class AgendaJanjiTemuService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function store(array $data)
    {
        $agendaJanjiTemu = new AgendaJanjiTemu($data);
        $agendaJanjiTemu->save();
    }

    public function delete(int $id)
    {
        $agendaJanjiTemu = AgendaJanjiTemu::find($id);
        if ($agendaJanjiTemu) {
            $agendaJanjiTemu->delete();
        }
    }

    public function show($id)
    {
        $agendaJanjiTemu = AgendaJanjiTemu::where("user_id", auth()->user()->id)
            ->where("id", $id)
            ->first();
        if ($agendaJanjiTemu) {
            return $agendaJanjiTemu;
        }
        return null;
    }

    public function generatePdf($id)
    {
        $agendaJanjiTemu = AgendaJanjiTemu::findOrFail($id);

        $pdf = Pdf::loadView("administrasi.surat.janji-temu.template-pdf", [
            "agendaJanjiTemu" => $agendaJanjiTemu,
        ])->setPaper("a4", "portrait");

        return $pdf->download(
            "agenda-janji-temu-" . $agendaJanjiTemu->id . ".pdf",
        );
    }
}
