<?php

namespace App\Http\Controllers;

use App\Models\MemoKredit\MemoKredit;
use App\Models\ReturPembelian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReturController extends Controller
{
    public function listPenjualan()
    {
        $userId = Auth::id();
        $returPenjualan = MemoKredit::where('user_id', $userId)
            ->with(['fakturPenjualan.suratPengirimanBarang.pesananPenjualan.pelanggan', 'memoKreditDetail'])
            ->latest('tanggal')
            ->get();

        return view('retur-kredit.list-penjualan', compact('returPenjualan'));
    }

    public function listPembelian()
    {
        $userId = Auth::id();
        $returPengeluaran = ReturPembelian::where('user_id', $userId)
            ->with(['pesananPembelian.supplier', 'detail'])
            ->latest('tanggal')
            ->get();

        return view('retur-kredit.list-pembelian', compact('returPengeluaran'));
    }
}
