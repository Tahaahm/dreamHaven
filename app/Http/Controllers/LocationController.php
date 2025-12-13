<?php

// app/Http/Controllers/LocationController.php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    // ==================== BRANCH METHODS ====================

    /**
     * Get all branches (cities) with their areas
     */
    public function getBranches(Request $request)
    {
        $locale = $request->header('Accept-Language', 'en');
        app()->setLocale($locale);

        $branches = Branch::active()
            ->with(['areas' => function ($query) use ($locale) {
                $query->active()->orderBy("area_name_{$locale}");
            }])
            ->orderBy("city_name_{$locale}")
            ->get();

        return response()->json([
            'success' => true,
            'data' => $branches
        ]);
    }

    /**
     * Get cities only (without areas)
     */
    public function getCities(Request $request)
    {
        $locale = $request->header('Accept-Language', 'en');
        app()->setLocale($locale);

        $cities = Branch::active()
            ->select('id', 'city_name_en', 'city_name_ar', 'city_name_ku', 'latitude', 'longitude', 'is_active')
            ->orderBy("city_name_{$locale}")
            ->get();

        return response()->json([
            'success' => true,
            'data' => $cities
        ]);
    }

    /**
     * Get single branch with all areas
     */
    public function getBranch(Request $request, $id)
    {
        $locale = $request->header('Accept-Language', 'en');
        app()->setLocale($locale);

        $branch = Branch::with(['areas' => function ($query) use ($locale) {
            $query->active()->orderBy("area_name_{$locale}");
        }])
            ->where('is_active', true)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $branch
        ]);
    }

    /**
     * Create new branch (city)
     */
    public function createBranch(Request $request)
    {
        $validated = $request->validate([
            'city_name_en' => 'required|string|max:255|unique:branches,city_name_en',
            'city_name_ar' => 'required|string|max:255|unique:branches,city_name_ar',
            'city_name_ku' => 'required|string|max:255|unique:branches,city_name_ku',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ], [
            'city_name_en.required' => 'English city name is required',
            'city_name_ar.required' => 'Arabic city name is required',
            'city_name_ku.required' => 'Kurdish city name is required',
            'city_name_en.unique' => 'This city name already exists in English',
            'city_name_ar.unique' => 'This city name already exists in Arabic',
            'city_name_ku.unique' => 'This city name already exists in Kurdish',
        ]);

        $branch = Branch::create([
            'city_name_en' => $validated['city_name_en'],
            'city_name_ar' => $validated['city_name_ar'],
            'city_name_ku' => $validated['city_name_ku'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'is_active' => true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Branch created successfully',
            'data' => $branch
        ], 201);
    }

    /**
     * Update branch
     */
    public function updateBranch(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $validated = $request->validate([
            'city_name_en' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('branches')->ignore($branch->id)
            ],
            'city_name_ar' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('branches')->ignore($branch->id)
            ],
            'city_name_ku' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('branches')->ignore($branch->id)
            ],
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
            'is_active' => 'sometimes|boolean'
        ]);

        $branch->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Branch updated successfully',
            'data' => $branch->fresh()
        ]);
    }

    /**
     * Delete branch (soft delete)
     */
    public function deleteBranch($id)
    {
        $branch = Branch::findOrFail($id);

        // Check if branch has any properties or users
        $propertiesCount = $branch->properties()->count();
        $usersCount = $branch->users()->count();
        $areasCount = $branch->areas()->count();

        if ($propertiesCount > 0 || $usersCount > 0 || $areasCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete branch. It has {$areasCount} areas, {$propertiesCount} properties and {$usersCount} users."
            ], 400);
        }

        // Soft delete
        $branch->delete();

        return response()->json([
            'success' => true,
            'message' => 'Branch deleted successfully'
        ]);
    }

    // ==================== AREA METHODS ====================

    /**
     * Get all areas by branch
     */
    public function getAreasByBranch(Request $request, $branchId)
    {
        $locale = $request->header('Accept-Language', 'en');
        app()->setLocale($locale);

        $branch = Branch::findOrFail($branchId);

        $areas = Area::where('branch_id', $branchId)
            ->active()
            ->orderBy("area_name_{$locale}")
            ->get();

        return response()->json([
            'success' => true,
            'branch' => $branch,
            'data' => $areas
        ]);
    }

    /**
     * Get single area
     */
    public function getArea(Request $request, $id)
    {
        $locale = $request->header('Accept-Language', 'en');
        app()->setLocale($locale);

        $area = Area::with('branch')
            ->where('is_active', true)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $area
        ]);
    }

    /**
     * Create new area
     */
    public function createArea(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'area_name_en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('areas')->where(function ($query) use ($request) {
                    return $query->where('branch_id', $request->branch_id);
                })
            ],
            'area_name_ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('areas')->where(function ($query) use ($request) {
                    return $query->where('branch_id', $request->branch_id);
                })
            ],
            'area_name_ku' => [
                'required',
                'string',
                'max:255',
                Rule::unique('areas')->where(function ($query) use ($request) {
                    return $query->where('branch_id', $request->branch_id);
                })
            ],
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ], [
            'area_name_en.required' => 'English area name is required',
            'area_name_ar.required' => 'Arabic area name is required',
            'area_name_ku.required' => 'Kurdish area name is required',
            'area_name_en.unique' => 'This area already exists in English in the selected city',
            'area_name_ar.unique' => 'This area already exists in Arabic in the selected city',
            'area_name_ku.unique' => 'This area already exists in Kurdish in the selected city',
        ]);

        $area = Area::create([
            'branch_id' => $validated['branch_id'],
            'area_name_en' => $validated['area_name_en'],
            'area_name_ar' => $validated['area_name_ar'],
            'area_name_ku' => $validated['area_name_ku'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'is_active' => true
        ]);

        $area->load('branch');

        return response()->json([
            'success' => true,
            'message' => 'Area created successfully',
            'data' => $area
        ], 201);
    }

    /**
     * Update area
     */
    public function updateArea(Request $request, $id)
    {
        $area = Area::findOrFail($id);

        $validated = $request->validate([
            'area_name_en' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('areas')->where(function ($query) use ($area) {
                    return $query->where('branch_id', $area->branch_id);
                })->ignore($area->id)
            ],
            'area_name_ar' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('areas')->where(function ($query) use ($area) {
                    return $query->where('branch_id', $area->branch_id);
                })->ignore($area->id)
            ],
            'area_name_ku' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('areas')->where(function ($query) use ($area) {
                    return $query->where('branch_id', $area->branch_id);
                })->ignore($area->id)
            ],
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'sometimes|boolean'
        ], [
            'area_name_en.unique' => 'This area already exists in English in this city',
            'area_name_ar.unique' => 'This area already exists in Arabic in this city',
            'area_name_ku.unique' => 'This area already exists in Kurdish in this city',
        ]);

        $area->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Area updated successfully',
            'data' => $area->fresh(['branch'])
        ]);
    }

    /**
     * Delete area (soft delete)
     */
    public function deleteArea($id)
    {
        $area = Area::findOrFail($id);

        // Check if area has any properties or users
        $propertiesCount = $area->properties()->count();
        $usersCount = $area->users()->count();

        if ($propertiesCount > 0 || $usersCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete area. It has {$propertiesCount} properties and {$usersCount} users."
            ], 400);
        }

        // Soft delete
        $area->delete();

        return response()->json([
            'success' => true,
            'message' => 'Area deleted successfully'
        ]);
    }

    // ==================== UTILITY METHODS ====================

    /**
     * Search locations (branches and areas)
     */
    public function searchLocations(Request $request)
    {
        $locale = $request->header('Accept-Language', 'en');
        app()->setLocale($locale);

        $keyword = $request->input('q', '');

        $branches = Branch::active()
            ->where(function ($query) use ($keyword) {
                $query->where('city_name_en', 'like', "%{$keyword}%")
                    ->orWhere('city_name_ar', 'like', "%{$keyword}%")
                    ->orWhere('city_name_ku', 'like', "%{$keyword}%");
            })
            ->with(['areas' => function ($query) {
                $query->active();
            }])
            ->get();

        $areas = Area::active()
            ->where(function ($query) use ($keyword) {
                $query->where('area_name_en', 'like', "%{$keyword}%")
                    ->orWhere('area_name_ar', 'like', "%{$keyword}%")
                    ->orWhere('area_name_ku', 'like', "%{$keyword}%");
            })
            ->with('branch')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'branches' => $branches,
                'areas' => $areas
            ]
        ]);
    }

    /**
     * Get location statistics
     */
    public function getLocationStats()
    {
        $stats = [
            'total_branches' => Branch::active()->count(),
            'total_areas' => Area::active()->count(),
            'branches_with_most_areas' => Branch::active()
                ->withCount('areas')
                ->orderBy('areas_count', 'desc')
                ->limit(5)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
