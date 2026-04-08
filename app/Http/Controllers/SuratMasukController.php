<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Log};
use App\Http\Requests\SuratMasukRequest;
use App\Models\AgendaSuratMasuk;
use App\Services\FileUploadService;
use Throwable;

class SuratMasukController extends Controller
{
    public function __construct(protected FileUploadService $fileUploadService)
    {
    }


    public function index()
    {
        $agendaSuratMasuk = AgendaSuratMasuk::where(
            'user_id',
            auth()->id(),
        )->get();

        return view(
            'administrasi.surat.surat-masuk.index',
            compact('agendaSuratMasuk'),
        );
    }

    public function create()
    {
        return view(
            'administrasi.surat.surat-masuk.create',
        );
    }

    public function store(SuratMasukRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();
            $data['user_id'] = auth()->id();
            $data['email'] = auth()->user()->email;
            $fileName = null;
            if ($request->hasFile('file_surat')) {
                $fileName = $this->fileUploadService->upload($data['file_surat'], 'surat-masuk/surat', $data['email']);
            }
            AgendaSuratMasuk::create([
                'user_id' => $data['user_id'],
                'nomor_agenda' => $data['nomor_agenda'],
                'tanggal_terima' => $data['tanggal_terima'],
                'nomor_surat' => $data['nomor_surat'],
                'tanggal_surat' => $data['tanggal_surat'],
                'pengirim' => $data['pengirim'],
                'perihal' => $data['perihal'],
                'file_surat' => $fileName,
            ]);
            DB::commit();

            return redirect()
                ->route('administrasi.surat-masuk.index')
                ->with('success', 'Surat masuk berhasil ditambahkan.');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Gagal simpan surat masuk: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'payload' => $request->all(),
            ]);

            return back()
                ->withErrors(['error' => 'Terjadi kesalahan sistem. Silakan coba lagi.'])
                ->withInput();
        }
    }

    public function showDisposisi($id)
    {
        $surat = AgendaSuratMasuk::where('user_id', auth()->id())
            ->where('id', $id)
            ->first();

        return view(
            'administrasi.surat.surat-masuk.create-disposisi',
            compact('surat'),
        );
    }

    public function storeDisposisi(Request $request, $id)
    {
        $request->validate([
            'disposisi_status' => 'required|array',
            'tujuan_status' => 'required|array',
            'catatan' => 'nullable|string',
            'tanggal_disposisi' => 'required|date',
            'file_ttd_pimpinan' => 'nullable|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);
        DB::beginTransaction();
        try {
            $surat = DB::table('agenda_surat_masuk')->where('user_id', auth()->id())->where('id', $id)->first();
            if (!$surat) {
                return redirect()
                    ->route('administrasi.surat-masuk.index')
                    ->with('error', 'Surat masuk tidak ditemukan.');
            }

            if ($request->hasFile('file_ttd_pimpinan')) {
                $ttdPath = $this->fileUploadService->upload($request->file('file_ttd_pimpinan'), 'surat-masuk/ttd-pimpinan', auth()->user()->email);
            }

            $disposisiMap = [
                'Segera' => 'disp_segera',
                'Teliti dan beri pendapat' => 'disp_teliti',
                'Edarkan' => 'disp_edarkan',
                'Untuk diketahui' => 'disp_diketahui',
                'Koordinasikan' => 'disp_koordinasikan',
                'Proses lebih lanjut' => 'disp_proses_lanjut',
                'Arsipkan' => 'disp_arsipkan',
                'Mohon dijawab' => 'disp_mohon_dijawab',
            ];

            $disposisi = [];
            foreach ($disposisiMap as $label => $column) {
                $disposisi[$column] = in_array(
                    $label,
                    $request->disposisi_status ?? [],
                );
            }

            $tujuanMap = [
                'Keuangan' => 'tujuan_keuangan',
                'Kepala Bagian Gudang' => 'tujuan_gudang',
                'Karyawan' => 'tujuan_karyawan',
                'Lainnya' => 'tujuan_lainnya',
            ];

            $tujuan = [];
            foreach ($tujuanMap as $label => $column) {
                $tujuan[$column] = in_array($label, $request->tujuan_status ?? []);
            }
            DB::table('agenda_surat_masuk')
                ->where('id', $id)
                ->update(
                    array_merge(
                        [
                            'catatan' => $request->catatan,
                            'tanggal_disposisi' => $request->tanggal_disposisi,
                            'ttd_pimpinan' => $ttdPath ?? $surat->ttd_pimpinan,
                            'status_disposisi' => 'selesai',
                        ],
                        $disposisi,
                        $tujuan,
                    )
                );
            DB::commit();

            return redirect()
                ->route('administrasi.surat-masuk.index')
                ->with('success', 'Disposisi berhasil disimpan.');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Disposisi store error: ' . $e->getMessage());

            return back()->with(
                'error',
                'Terjadi kesalahan saat menyimpan disposisi.',
            );
        }
    }

    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            $agendaSuratMasuk = DB::table('agenda_surat_masuk')->where('user_id', auth()->id())->where('id', $id)->first();
            if ($agendaSuratMasuk->file_surat) {
                $this->fileUploadService->delete($agendaSuratMasuk->file_surat);
                if ($agendaSuratMasuk->ttd_pimpinan) {
                    $this->fileUploadService->delete($agendaSuratMasuk->ttd_pimpinan);
                }
            }
            DB::table('agenda_surat_masuk')->where('user_id', auth()->id())->where('id', $id)->delete();
            DB::commit();

            return redirect()
                ->route('administrasi.surat-masuk.index')
                ->with('success', 'Agenda surat masuk berhasil dihapus');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error(
                "Error deleting agenda surat masuk ID $id: " . $e->getMessage(),
            );

            return redirect()
                ->route('administrasi.surat-masuk.index')
                ->with('error', 'Agenda surat masuk gagal dihapus');
        }
    }
}