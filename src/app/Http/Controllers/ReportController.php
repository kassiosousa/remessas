<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportStoreRequest;
use App\Http\Requests\ReportUpdateRequest;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $q = Report::query()
            ->withCount(['projects as projects_count', 'payouts as payouts_count'])
            ->when($request->platform, fn($x) => $x->where('platform', $request->platform))
            ->when($request->period_month, fn($x) => $x->where('period_month', $request->period_month))
            ->orderByDesc('id');

        return $q->paginate($request->integer('per_page', 20));
    }

    public function store(ReportStoreRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id ?? null;

        $report = Report::create($data);
        return response()->json($report, 201);
    }

    public function show(Report $report)
    {
        $report->load(['projects.project', 'payouts.partner']);
        return $report;
    }
    

    public function update(ReportUpdateRequest $request, Report $report)
    {
        $report->update($request->validated());
        return $report->fresh();
    }

    public function destroy(Report $report)
    {
        $report->delete();
        return response()->noContent();
    }
}
