<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Get all projects or filter by real estate office.
     */
    public function index(Request $request)
    {
        // Check if there's an office ID to filter projects
        if ($request->has('office_id')) {
            $projects = Project::where('office_id', $request->office_id)->get();
        } else {
            // Retrieve all projects
            $projects = Project::all();
        }

        return response()->json($projects, 200);
    }

    /**
     * Get a specific project by ID.
     */
    public function show($id)
    {
        // Find the project by ID
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        return response()->json($project, 200);
    }

    /**
     * Create a new project for a real estate office.
     */
    public function store(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:in_progress,completed',
            'image' => 'nullable|url',
            'office_id' => 'required|exists:real_estate_offices,office_id',
        ]);

        // Create a new project
        $project = Project::create($validatedData);

        return response()->json(['message' => 'Project created successfully', 'project' => $project], 201);
    }
}
