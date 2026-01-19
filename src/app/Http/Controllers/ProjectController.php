<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectStoreRequest;
use App\Http\Requests\ProjectUpdateRequest;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');

        $projects = Project::query()
            ->when($q, fn($qb) => $qb->where('title', 'like', "%{$q}%"))
            ->orderByDesc('id', 'description')
            ->paginate(20);

        return response()->json($projects);
    }

    public function store(ProjectStoreRequest $request)
    {
        $project = Project::create($request->validated());
        return response()->json($project, 201);
    }

    public function show(Project $project)
    {
        // trazer sócios e pivots
        $project->load('partners');
        return response()->json($project);
    }

    public function update(ProjectUpdateRequest $request, Project $project)
    {
        $project->update($request->validated());
        return response()->json($project);
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return response()->json([], 204);
    }
}
