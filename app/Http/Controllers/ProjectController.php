<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Helper\ResponseDetails;
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

        return ApiResponse::success(
            ResponseDetails::successMessage('Projects retrieved successfully'),
            $projects,
            ResponseDetails::CODE_SUCCESS
        );
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
            return ApiResponse::error(
                ResponseDetails::validationErrorMessage(),
                $validator->errors(),
                ResponseDetails::CODE_VALIDATION_ERROR
            );
        }

        $project = Project::create($request->all());

        return ApiResponse::success(
            ResponseDetails::successMessage('Project created successfully'),
            $project,
            ResponseDetails::CODE_SUCCESS
        );
    }

    /**
     * Display the specified project.
     */
    public function show($id)
    {
        $project = Project::find($id);
        if (!$project) {
            return ApiResponse::error(
                ResponseDetails::notFoundMessage('Project not found'),
                null,
                ResponseDetails::CODE_NOT_FOUND
            );
        }
        return ApiResponse::success(
            ResponseDetails::successMessage('Project retrieved successfully'),
            $project,
            ResponseDetails::CODE_SUCCESS
        );
    }

    /**
     * Update the specified project in storage.
     */
    public function update(Request $request, $id)
    {
        $project = Project::find($id);
        if (!$project) {
            return ApiResponse::error(
                ResponseDetails::notFoundMessage('Project not found'),
                null,
                ResponseDetails::CODE_NOT_FOUND
            );
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'status' => 'in:in_progress,completed',
            'image' => 'nullable|url',
            'office_id' => 'exists:real_estate_offices,office_id'
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(
                ResponseDetails::validationErrorMessage(),
                $validator->errors(),
                ResponseDetails::CODE_VALIDATION_ERROR
            );
        }

        $project->update($request->all());

        return ApiResponse::success(
            ResponseDetails::successMessage('Project updated successfully'),
            $project,
            ResponseDetails::CODE_SUCCESS
        );
    }
    /**
     * Remove the specified project from storage.
     */
    public function destroy($id)
    {
        $project = Project::find($id);
        if (!$project) {
            return ApiResponse::error(
                ResponseDetails::notFoundMessage('Project not found'),
                null,
                ResponseDetails::CODE_NOT_FOUND
            );
        }

        $project->delete();

        return ApiResponse::success(
            ResponseDetails::successMessage('Project deleted successfully'),
            null,
            ResponseDetails::CODE_SUCCESS
        );
    }
}
