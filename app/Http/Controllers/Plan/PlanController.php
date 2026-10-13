<?php

namespace App\Http\Controllers\Plan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Plan\StorePlanRequest;
use App\Models\Plan;
use App\Services\Plan\PlanService;
use Illuminate\Support\Facades\Log;

class PlanController extends Controller
{
    public function __construct(
        protected PlanService $planService
    ) {}

    public function index()
    {
        $plan = Plan::with('planFeatures')->get();

        return view('superadmin.plan.index', compact('plan'));
    }

    public function create()
    {
        return view('superadmin.plan.create');
    }

    public function store(StorePlanRequest $request)
    {
        $validated = $request->validated();
        try {
            $this->planService->store($validated);

            return redirect()->route('superadmin.plan.index')->with('success', 'Paket berhasil dibuat.');
        } catch (\Throwable $th) {
            Log::error('Store plan error', [
                'exception' => $th,
            ]);

            return back()->withInput()->with('error', 'Gagal membuat paket. Silakan coba beberapa saat lagi.');
        }
    }
}
