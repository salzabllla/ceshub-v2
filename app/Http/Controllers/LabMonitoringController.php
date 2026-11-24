<?php

namespace App\Http\Controllers;

use App\Models\LabSubmersible;
use App\Models\Project;
use App\Models\SubmersibleConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LabMonitoringController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to] = $this->period($request);
        $project = Project::where('status', 'published')->where(function ($query) {
            $query->where('slug', 'lab-scale')->orWhere('dashboard_url', '/dashboard/lab-scale')
                ->orWhere('dashboard_url', 'like', '%/dashboard/lab-scale');
        })->with(['teamMembers', 'posts' => fn ($query) => $query->where('status', 'published')->latest('published_at')])->first();
        $history = LabSubmersible::whereBetween('created_at', [$from, $to])
            ->orderByDesc('created_at')->orderByDesc('id')->paginate(25)->withQueryString();

        return view('lab-dashboard', compact('project', 'history', 'from', 'to'));
    }

    public function data()
    {
        $latestRows = LabSubmersible::orderByDesc('created_at')->orderByDesc('id')->limit(4)->get();
        $latest = $latestRows->first();
        $age = $latest ? $latest->created_at->diffInSeconds(now(), false) : null;
        $config = SubmersibleConfig::find(2);

        $reading = $latest
            ? $this->readingsWithSs2($latestRows->reverse()->values())->last()
            : null;

        return response()->json([
            'health' => \App\Services\LabDeviceHealth::read(storage_path('app/ces-ota/health.json')),
            'reading' => $reading,
            'online' => $age !== null && $age >= -60 && $age <= 300,
            'commands' => ['lampu1' => (int) ($config?->lampu1 ?? 0), 'lampu2' => (int) ($config?->lampu2 ?? 0)],
        ])->header('Cache-Control', 'no-store');
    }

    private function ss2NextPower(LabSubmersible $row): float
    {
        // SS2 MATLAB: x[k+1]=0.9883x[k]+B.u[k], y[k]=1102x[k].
        $state = (float) $row->daya / 1102;
        $irradiance = (float) $row->estimasi_irradiansi;
        $temperature = (float) $row->suhu;

        return round(1102 * (
            0.9883 * $state
            + 8.604e-06 * $irradiance
            + 9.239e-07 * $temperature
        ), 2);
    }

    private function ss2Comparison(LabSubmersible $previous, LabSubmersible $current, int $candidateStreak = 0): array
    {
        $predicted = $this->ss2NextPower($previous);
        $difference = round((float) $current->daya - $predicted, 2);
        $absoluteDifference = abs($difference);
        $level = $absoluteDifference <= 3.12 ? 'normal' : ($absoluteDifference <= 4.68 ? 'observe' : 'candidate');
        $anomaly = $level === 'candidate' && $candidateStreak >= 3;
        $status = match (true) {
            $anomaly => 'Anomali terdeteksi',
            $level === 'candidate' => 'Indikasi anomali',
            $level === 'observe' => 'Perlu diamati',
            default => 'Dalam rentang normal',
        };

        return [
            'ss2_prediksi' => $predicted,
            'ss2_selisih' => $difference,
            'ss2_anomali' => $anomaly,
            'ss2_level' => $anomaly ? 'anomaly' : $level,
            'ss2_status' => $status,
        ];
    }

    private function emptySs2Comparison(): array
    {
        return ['ss2_prediksi' => null, 'ss2_selisih' => null, 'ss2_anomali' => false, 'ss2_level' => 'waiting', 'ss2_status' => 'Menunggu data pembanding'];
    }

    private function readingsWithSs2($records)
    {
        $records = $records->values();

        $candidateStreak = 0;

        return $records->map(function ($row, $index) use ($records, &$candidateStreak) {
            if ($index === 0) return array_merge($this->reading($row), $this->emptySs2Comparison());

            $comparison = $this->ss2Comparison($records[$index - 1], $row);
            $candidateStreak = $comparison['ss2_level'] === 'candidate' ? $candidateStreak + 1 : 0;

            return array_merge($this->reading($row), $this->ss2Comparison($records[$index - 1], $row, $candidateStreak));
        });
    }

    public function readings(Request $request)
    {
        if ($request->boolean('latest')) {
            $records = LabSubmersible::orderByDesc('created_at')->orderByDesc('id')->limit(61)->get()->reverse()->values();
            $rows = $this->readingsWithSs2($records)->take(-60)->values();
            return response()->json(['rows' => $rows])->header('Cache-Control', 'no-store');
        }

        [$from, $to] = $this->period($request);
        $query = LabSubmersible::whereBetween('created_at', [$from, $to]);
        if ($request->boolean('table')) {
            $history = $query->orderByDesc('created_at')->orderByDesc('id')
                ->paginate(25, ['*'], 'page', $request->integer('page', 1))
                ->withPath(route('dashboard.lab-scale'))
                ->appends($request->only(['from', 'to']));

            return response()->json([
                'html' => view('partials.lab-history-rows', compact('history'))->render(),
                'pagination' => view('partials.lab-history-pagination', compact('history'))->render(),
                'period' => ['from' => $from->format('Y-m-d\TH:i'), 'to' => $to->format('Y-m-d\TH:i')],
            ])->header('Cache-Control', 'no-store');
        }
        return response()->json([
            'from' => $from->format('d/m/Y H:i:s'), 'to' => $to->format('d/m/Y H:i:s'),
            'timezone' => config('app.timezone'),
            'rows' => $this->readingsWithSs2($query->orderBy('created_at')->orderBy('id')->get()),
        ]);
    }

    private function period(Request $request): array
    {
        $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'to' => ['nullable', 'date_format:Y-m-d\TH:i', 'after_or_equal:from'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $from = $request->filled('from') ? Carbon::parse($request->input('from')) : now()->startOfDay();
        $to = $request->filled('to') ? Carbon::parse($request->input('to'))->endOfMinute() : now()->endOfDay();
        if ($to->lt($from)) {
            throw ValidationException::withMessages(['to' => 'Waktu akhir harus sesudah waktu mulai.']);
        }
        return [$from, $to];
    }

    private function reading(LabSubmersible $row): array
    {
        return [
            'time' => $row->created_at->format('d/m/Y H:i:s'),
            'label' => $row->created_at->format('H:i:s'),
            'tegangan' => (float) $row->tegangan,
            'arus' => $row->tegangan > 0 ? round($row->daya / $row->tegangan, 2) : 0,
            'daya' => (float) $row->daya,
            'suhu' => (float) $row->suhu,
            'intensitas_cahaya' => (int) $row->intensitas_cahaya,
            'irradiansi' => $row->estimasi_irradiansi,
            'satuan_irradiansi' => 'W/m2',
            'lampu1' => $row->lampu1 === null ? null : (int) $row->lampu1,
            'lampu2' => $row->lampu2 === null ? null : (int) $row->lampu2,
        ];
    }
}
