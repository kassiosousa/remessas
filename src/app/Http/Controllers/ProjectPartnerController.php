<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectPartnerSyncRequest;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class ProjectPartnerController extends Controller
{
    public function sync(ProjectPartnerSyncRequest $request, Project $project)
    {
        $data = $request->validated();

        $mode = $data['mode'] ?? 'sync';
        $detachMissing = $mode === 'sync'
            ? (bool)($data['detach_missing'] ?? true)
            : true; 

        $payload = [];
        foreach ($data['partners'] as $p) {
            $payload[(int)$p['partner_id']] = [
                'share_percent' => $p['share_percent'],
                'role' => $p['role'] ?? null,
                'valid_from' => $p['valid_from'] ?? null,
                'valid_until' => $p['valid_until'] ?? null,
            ];
        }

        DB::transaction(function () use ($project, $mode, $detachMissing, $payload) {
            if ($mode === 'attach') {
                $project->partners()->syncWithoutDetaching($payload);
                return;
            }

            if ($mode === 'detach') {
                $project->partners()->detach(array_keys($payload));
                return;
            }

            // default: sync
            $project->partners()->sync($payload, $detachMissing);
        });

        $project->load('partners');

        return response()->json([
            'project' => $project,
            'partners' => $project->partners,
        ], 200);
    }

    public function index(Project $project)
    {
        $project->load('partners');

        return response()->json([
            'project' => $project->only(['id', 'title']),
            'partners' => $project->partners,
        ]);
    }

}
