<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class ProjectController extends Controller
{
    /**
     * Display a listing of the projects.
     */
    public function index(Request $request)
    {
        if ($request->has('office_id')) {
            $projects = Project::where('office_id', $request->office_id)->get();
        } else {
            $projects = Project::all();
        }

        return response()->json($projects);
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:in_progress,completed',
            'image' => 'nullable|url',
            'office_id' => 'required|exists:real_estate_offices,office_id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $project = Project::create($request->all());

        return response()->json(['message' => 'Project created successfully', 'project' => $project], 201);
    }

    /**
     * Display the specified project.
     */
    public function show($id)
    {
        $project = Project::find($id);
        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }
        return response()->json($project);
    }

    /**
     * Update the specified project in storage.
     */
    public function update(Request $request, $id)
    {
        $project = Project::find($id);
        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'status' => 'in:in_progress,completed',
            'image' => 'nullable|url',
            'office_id' => 'exists:real_estate_offices,office_id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $project->update($request->all());

        return response()->json(['message' => 'Project updated successfully', 'project' => $project]);
    }

    /**
     * Remove the specified project from storage.
     */
    public function destroy($id)
    {
        $project = Project::find($id);
        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        $project->delete();

        return response()->json(['message' => 'Project deleted successfully']);
    }
}
