<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    /**
     * Get all projects with pagination and filters
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 20);
        $perPage = min($perPage, 100); // Max 100 per page

        $query = Project::query()
            ->active()
            ->published()
            // ->with(['developer']) // Temporarily commented out
            ->latest();

        // Apply filters
        $this->applyFilters($query, $request);

        // Apply search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.en')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.ar')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.ku')) LIKE ?", ["%{$search}%"])
                    ->orWhere('full_address', 'LIKE', "%{$search}%")
                    ->orWhere('architect', 'LIKE', "%{$search}%")
                    ->orWhere('contractor', 'LIKE', "%{$search}%");
            });
        }

        // Apply sorting
        $this->applySorting($query, $request);

        $projects = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Projects retrieved successfully',
            'data' => $projects->items(),
            'pagination' => [
                'current_page' => $projects->currentPage(),
                'per_page' => $projects->perPage(),
                'total' => $projects->total(),
                'last_page' => $projects->lastPage(),
                'has_more_pages' => $projects->hasMorePages(),
                'from' => $projects->firstItem(),
                'to' => $projects->lastItem()
            ]
        ], 200);
    }

    /**
     * Get a specific project by ID
     */
    public function show(string $id): JsonResponse
    {
        try {
            $project = Project::with(['properties', 'reviews']) // Removed 'developer' temporarily
                ->where('id', $id)
                ->active()
                ->published()
                ->firstOrFail();

            // Increment view count
            $project->incrementViews();

            return response()->json([
                'success' => true,
                'message' => 'Project retrieved successfully',
                'data' => $project
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found'
            ], 404);
        }
    }

    /**
     * Create a new project
     */
    public function store(Request $request): JsonResponse
    {
        $validator = $this->validateProjectData($request);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $validator->validated();

            // Generate slug from name
            if (isset($data['name'])) {
                $name = is_array($data['name']) ? ($data['name']['en'] ?? reset($data['name'])) : $data['name'];
                $data['slug'] = Str::slug($name) . '-' . Str::random(6);
            }

            // Create project
            $project = Project::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Project created successfully',
                'data' => $project
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create project',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a project
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $project = Project::findOrFail($id);

            $validator = $this->validateProjectData($request, true);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            // Update slug if name changed
            if (isset($data['name'])) {
                $name = is_array($data['name']) ? ($data['name']['en'] ?? reset($data['name'])) : $data['name'];
                $data['slug'] = Str::slug($name) . '-' . Str::random(6);
            }

            $project->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Project updated successfully',
                'data' => $project->fresh()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update project',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a project (soft delete)
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $project = Project::findOrFail($id);
            $project->delete();

            return response()->json([
                'success' => true,
                'message' => 'Project deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete project',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function featured(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 10);
        $strategy = $request->get('strategy', 'balanced'); // balanced, rating, popularity, recent

        $query = Project::active()
            ->published()
            ->featured();

        // Apply different strategies for featured selection
        $this->applyFeaturedStrategy($query, $strategy);

        $projects = $query->limit($perPage)->get();

        // Transform to simplified featured structure
        $featuredProjects = $projects->map(function ($project) {
            return $this->transformToFeaturedProject($project);
        });

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Featured projects retrieved',
            'data' => [
                'data' => $featuredProjects,
                'total' => $featuredProjects->count(),
                'strategy' => $strategy
            ]
        ], 200);
    }
    private function applyFeaturedStrategy($query, string $strategy): void
    {
        switch ($strategy) {
            case 'rating':
                $query->orderBy('rating', 'desc')
                    ->orderBy('reviews_count', 'desc');
                break;

            case 'popularity':
                $query->orderBy('views', 'desc')
                    ->orderBy('units_sold', 'desc');
                break;

            case 'recent':
                $query->orderBy('launch_date', 'desc')
                    ->orderBy('created_at', 'desc');
                break;

            case 'balanced':
            default:
                // Balanced approach considering multiple factors
                $query->orderByRaw('(
                    (rating * 0.3) +
                    (LEAST(views / 100, 10) * 0.2) +
                    (completion_percentage / 10 * 0.2) +
                    (units_sold / GREATEST(total_units, 1) * 100 * 0.2) +
                    (CASE WHEN is_premium = 1 THEN 2 ELSE 0 END) +
                    (CASE WHEN is_hot_project = 1 THEN 1.5 ELSE 0 END)
                ) DESC');
                break;
        }
    }

    /**
     * Transform project to simplified featured structure
     */
    private function transformToFeaturedProject($project): array
    {
        // Calculate featured score
        $featuredScore = $this->calculateFeaturedScore($project);

        // Determine featured reasons
        $featuredReasons = $this->getFeaturedReasons($project);

        return [
            'id' => $project->id,
            'name' => $project->name['en'] ?? $project->name,
            'description' => $project->description['en'] ?? $project->description ?? '',
            'images' => $project->images ?? [],
            'main_image' => $project->cover_image_url ?? ($project->images[0] ?? null),
            'price' => [
                'min_price_iqd' => $project->price_range['min'] ?? 0,
                'max_price_iqd' => $project->price_range['max'] ?? 0,
                'min_price_usd' => isset($project->price_range['min']) ?
                    round($project->price_range['min'] / 1333) : 0, // Convert IQD to USD
                'max_price_usd' => isset($project->price_range['max']) ?
                    round($project->price_range['max'] / 1333) : 0,
                'currency' => $project->pricing_currency ?? 'IQD'
            ],
            'project_type' => $project->project_type,
            'total_units' => $project->total_units,
            'available_units' => $project->available_units,
            'locations' => $project->locations ?? [],
            'location' => $project->locations[0] ?? null,
            'address_details' => $project->address_details ?? [],
            'city' => $project->address_details['city']['en'] ??
                $project->address_details['city'] ?? '',
            'address' => $project->full_address,
            'status' => $project->status,
            'sales_status' => $project->sales_status,
            'completion_percentage' => $project->completion_percentage,
            'is_active' => $project->is_active,
            'published' => $project->published,
            'views' => $project->views,
            'rating' => $project->rating,
            'is_featured' => $project->is_featured,
            'is_premium' => $project->is_premium,
            'is_hot_project' => $project->is_hot_project,
            'eco_friendly' => $project->eco_friendly,
            'expected_completion_date' => $project->expected_completion_date,
            'launch_date' => $project->launch_date,
            'created_at' => $project->created_at,
            'updated_at' => $project->updated_at,
            'featured_reason' => $featuredReasons,
            'featured_score' => $featuredScore
        ];
    }
    private function calculateFeaturedScore($project): float
    {
        $score = 0;

        // Base rating score (0-50 points)
        $score += ($project->rating ?? 0) * 10;

        // Views popularity (0-20 points)
        $score += min(($project->views ?? 0) / 50, 20);

        // Completion percentage (0-15 points)
        $score += ($project->completion_percentage ?? 0) * 0.15;

        // Sales performance (0-10 points)
        if ($project->total_units > 0) {
            $salesRate = ($project->units_sold ?? 0) / $project->total_units;
            $score += $salesRate * 10;
        }

        // Premium bonuses
        if ($project->is_premium) $score += 8;
        if ($project->is_hot_project) $score += 5;
        if ($project->eco_friendly) $score += 3;

        // Recent activity bonus
        $daysSinceLaunch = $project->launch_date ?
            now()->diffInDays($project->launch_date) : 999;
        if ($daysSinceLaunch <= 30) $score += 5;
        if ($daysSinceLaunch <= 90) $score += 2;

        return round($score, 2);
    }
    private function getFeaturedReasons($project): array
    {
        $reasons = [];

        if ($project->rating >= 4.5) {
            $reasons[] = 'Highly rated';
        }

        if ($project->is_premium) {
            $reasons[] = 'Premium project';
        }

        if ($project->is_hot_project) {
            $reasons[] = 'Hot project';
        }

        if ($project->views > 100) {
            $reasons[] = 'Popular project';
        }

        if ($project->eco_friendly) {
            $reasons[] = 'Eco-friendly';
        }

        if ($project->total_units > 0) {
            $salesRate = ($project->units_sold ?? 0) / $project->total_units;
            if ($salesRate > 0.7) {
                $reasons[] = 'Fast selling';
            }
        }

        if ($project->completion_percentage >= 90) {
            $reasons[] = 'Near completion';
        }

        if ($project->launch_date && now()->diffInDays($project->launch_date) <= 30) {
            $reasons[] = 'Recently launched';
        }

        // Ensure at least one reason
        if (empty($reasons)) {
            $reasons[] = 'Featured project';
        }

        return $reasons;
    }

    public function getFeaturedProjects(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'per_page' => 'integer|min:1|max:50',
            'strategy' => 'in:balanced,rating,popularity,recent,sales',
            'project_type' => 'string',
            'min_rating' => 'numeric|min:0|max:5',
            'location' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $perPage = $request->get('per_page', 8);
        $strategy = $request->get('strategy', 'balanced');

        $query = Project::active()
            ->published()
            ->where(function ($q) {
                $q->where('is_featured', true)
                    ->orWhere('is_premium', true)
                    ->orWhere('is_hot_project', true)
                    ->orWhere('rating', '>=', 4.0);
            });

        // Apply additional filters
        if ($request->has('project_type')) {
            $query->where('project_type', $request->project_type);
        }

        if ($request->has('min_rating')) {
            $query->where('rating', '>=', $request->min_rating);
        }

        if ($request->has('location')) {
            $query->where('full_address', 'LIKE', "%{$request->location}%");
        }

        // Apply strategy
        $this->applyFeaturedStrategy($query, $strategy);

        $projects = $query->limit($perPage)->get();

        $featuredProjects = $projects->map(function ($project) {
            return $this->transformToFeaturedProject($project);
        });

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Featured projects retrieved successfully',
            'data' => [
                'data' => $featuredProjects,
                'total' => $featuredProjects->count(),
                'strategy' => $strategy,
                'filters_applied' => [
                    'project_type' => $request->get('project_type'),
                    'min_rating' => $request->get('min_rating'),
                    'location' => $request->get('location')
                ]
            ]
        ], 200);
    }
    /**
     * Get projects by developer
     */
    public function byDeveloper(Request $request, string $developerId): JsonResponse
    {
        $perPage = $request->get('per_page', 20);

        $projects = Project::where('developer_id', $developerId)
            ->active()
            ->published()
            // ->with(['developer']) // Temporarily commented out
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Developer projects retrieved successfully',
            'data' => $projects->items(),
            'pagination' => [
                'current_page' => $projects->currentPage(),
                'per_page' => $projects->perPage(),
                'total' => $projects->total(),
                'last_page' => $projects->lastPage(),
                'has_more_pages' => $projects->hasMorePages()
            ]
        ], 200);
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, Request $request): void
    {
        // Project type filter
        if ($request->has('project_type') && $request->project_type) {
            $query->byType($request->project_type);
        }

        // Status filter
        if ($request->has('status') && $request->status) {
            $query->byStatus($request->status);
        }

        // Sales status filter
        if ($request->has('sales_status') && $request->sales_status) {
            $query->bySalesStatus($request->sales_status);
        }

        // Location filter
        if ($request->has('location') && $request->location) {
            $query->byLocation($request->location);
        }

        // Price range filter
        if ($request->has('min_price') && $request->has('max_price')) {
            $query->byPriceRange($request->min_price, $request->max_price);
        }

        // Featured filter
        if ($request->has('featured') && $request->featured) {
            $query->featured();
        }

        // Premium filter
        if ($request->has('premium') && $request->premium) {
            $query->premium();
        }

        // Hot projects filter
        if ($request->has('hot') && $request->hot) {
            $query->hot();
        }

        // Eco-friendly filter
        if ($request->has('eco_friendly') && $request->eco_friendly) {
            $query->ecoFriendly();
        }

        // Completion percentage filter
        if ($request->has('min_completion')) {
            $query->where('completion_percentage', '>=', $request->min_completion);
        }
        if ($request->has('max_completion')) {
            $query->where('completion_percentage', '<=', $request->max_completion);
        }

        // Available units filter
        if ($request->has('has_available_units') && $request->has_available_units) {
            $query->where('available_units', '>', 0);
        }
    }

    /**
     * Apply sorting to query
     */
    private function applySorting($query, Request $request): void
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSorts = [
            'created_at',
            'name',
            'rating',
            'views',
            'price_range',
            'completion_percentage',
            'expected_completion_date',
            'launch_date',
            'units_sold',
            'sales_velocity'
        ];

        if (in_array($sortBy, $allowedSorts)) {
            if ($sortBy === 'name') {
                $query->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.en')) {$sortOrder}");
            } elseif ($sortBy === 'price_range') {
                $query->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT(price_range, '$.min')) {$sortOrder}");
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        }
    }

    /**
     * Validate project data
     */
    private function validateProjectData(Request $request, bool $isUpdate = false)
    {
        $rules = [
            'developer_id' => $isUpdate ? 'sometimes|required|uuid' : 'required|uuid',
            'developer_type' => $isUpdate ? 'sometimes|required|string' : 'required|string',
            'name' => $isUpdate ? 'sometimes|required|array' : 'required|array',
            'name.en' => 'required|string|max:255',
            'name.ar' => 'nullable|string|max:255',
            'name.ku' => 'nullable|string|max:255',
            'description' => 'nullable|array',
            'images' => 'nullable|array',
            'logo_url' => 'nullable|url',
            'cover_image_url' => 'nullable|url',
            'project_type' => $isUpdate ? 'sometimes|required|in:residential,commercial,mixed_use,industrial,retail,office,hospitality' : 'required|in:residential,commercial,mixed_use,industrial,retail,office,hospitality',
            'project_category' => 'nullable|array',
            'locations' => 'nullable|array',
            'address_details' => 'nullable|array',
            'full_address' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'total_area' => 'nullable|numeric|min:0',
            'built_area' => 'nullable|numeric|min:0',
            'total_units' => 'nullable|integer|min:1',
            'available_units' => 'nullable|integer|min:0',
            'unit_types' => 'nullable|array',
            'total_floors' => 'nullable|integer|min:1',
            'buildings_count' => 'nullable|integer|min:1',
            'year_built' => 'nullable|integer|min:1900|max:' . (date('Y') + 10),
            'completion_year' => 'nullable|integer|min:' . date('Y') . '|max:' . (date('Y') + 20),
            'price_range' => 'nullable|array',
            'price_range.min' => 'nullable|numeric|min:0',
            'price_range.max' => 'nullable|numeric|min:0',
            'pricing_currency' => 'nullable|in:USD,EUR,IQD',
            'status' => 'nullable|in:planning,under_construction,completed,delivered,cancelled,on_hold',
            'sales_status' => 'nullable|in:pre_launch,launched,selling,sold_out,suspended',
            'completion_percentage' => 'nullable|integer|min:0|max:100',
            'launch_date' => 'nullable|date',
            'construction_start_date' => 'nullable|date',
            'expected_completion_date' => 'nullable|date|after:today',
            'handover_date' => 'nullable|date',
            'is_featured' => 'nullable|boolean',
            'is_premium' => 'nullable|boolean',
            'is_hot_project' => 'nullable|boolean',
            'eco_friendly' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'published' => 'nullable|boolean'
        ];

        return Validator::make($request->all(), $rules);
    }








    // zana's code -------------------------------------------------------------------------

       public function showProjects()
    {
        $projects = Project::all();
        return view('agent.ProjectList', compact('projects'));
    }

}