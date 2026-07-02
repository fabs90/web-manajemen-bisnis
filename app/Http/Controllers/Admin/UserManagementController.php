<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgendaJanjiTemu;
use App\Models\AgendaPerjalanan;
use App\Models\Faktur\FakturPenjualan;
use App\Models\JournalEntry;
use App\Models\KasirTransactionLog;
use App\Models\KasKecil;
use App\Models\KasKecilDetail;
use App\Models\KasKecilFormulir;
use App\Models\MemoKredit\MemoKredit;
use App\Models\PengisianKasKecilLog;
use App\Models\SPB\SuratPengirimanBarang;
use App\Models\SPP\PesananPembelian;
use App\Models\SPP\SuratPesananPenjualan;
use App\Models\SuratUndanganRapat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = DB::table('users')->get();

        return view('superadmin.dashboard.manage-user.index', compact('users'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = DB::table('users')->where('id', $id)->first();

        if (!$user) {
            abort(404);
        }

        return view('superadmin.dashboard.manage-user.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);

        return view('superadmin.dashboard.manage-user.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:superadmin,ukm,nelayan,koperasi',
            'is_verified' => 'required|boolean',
            'nomor_telepon' => 'nullable|string|max:25',
            'alamat' => 'nullable|string',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'is_verified' => $request->is_verified,
            'nomor_telepon' => $request->nomor_telepon,
            'alamat' => $request->alamat,
        ]);

        return redirect()->route('superadmin.user-management.index')->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            // Hapus data journal_items terlebih dahulu agar tidak terkena constraint foreign key dari accounts
            \App\Models\JournalItem::where('user_id', $id)->delete();
            \App\Models\JournalEntry::where('user_id', $id)->delete();

            // Setelah itu baru hapus user (sebagian besar tabel lain sudah diset cascade untuk user_id)
            User::where('id', $id)->delete();

            DB::commit();
            return redirect()->route('superadmin.user-management.index')->with('success', 'User berhasil dihapus.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('superadmin.user-management.index')->with('error', 'Gagal menghapus user: ' . $th->getMessage());
        }
    }

    /**
     * Restore the financial of specified user
     */
    public function restoreUserNeracaAwal(string $id)
    {
        DB::beginTransaction();
        try {
            // Delete all operational documents
            FakturPenjualan::where('user_id', $id)->delete();
            SuratPengirimanBarang::where('user_id', $id)->delete();
            PesananPembelian::where('user_id', $id)->delete();
            SuratPesananPenjualan::where('user_id', $id)->delete();
            MemoKredit::where('user_id', $id)->delete();
            KasKecilFormulir::where('user_id', $id)->delete();
            KasKecilDetail::whereHas('kasKecil', function ($q) use ($id) {
                $q->where('user_id', $id);
            })->delete();
            KasKecil::where('user_id', $id)->delete();
            PengisianKasKecilLog::where('user_id', $id)->delete();
            KasirTransactionLog::where('user_id', $id)->delete();

            // Optional: delete administrasi surat
            AgendaPerjalanan::where('user_id', $id)->delete();
            SuratUndanganRapat::where('user_id', $id)->delete();
            AgendaJanjiTemu::where('user_id', $id)->delete();

            // Keep Neraca Awal, delete other journal entries
            JournalEntry::where('user_id', $id)
                ->where('transaction_type', '!=', 'neraca_awal')
                ->delete();

            // Note: JournalItem is cascade-deleted.
            // KartuGudang might have its journal_entry_id set to null due to constraints,
            // but we leave them or ideally delete operational ones if needed.

            DB::commit();

            return redirect()->back()->with('success', 'Data finansial user berhasil di-restore ke Neraca Awal.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal merestore data finansial: ' . $th->getMessage());
        }
    }
}