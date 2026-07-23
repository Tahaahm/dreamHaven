<?php

namespace App\Http\Controllers\Concerns;

use App\Helper\ApiResponse;
use App\Http\Controllers\NotificationController;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Create/update/delete and status-changing endpoints (store, update,
 * destroy, boost, verify, publish, bulk actions, image upload). Extracted
 * from PropertyController as-is — no behavior changed, only relocated.
 * See ManagesPropertyEngagement.php for why this is safe (traits compile
 * directly into the class that uses them, so routing is unaffected).
 *
 * Depends on $this->transformPropertyData() and $this->interactionService,
 * defined on PropertyController / the other Concerns it uses.
 */
trait ManagesPropertyMutations
{
    public function create()
    {
        return view('upload');
    }

    public function uploadImages(Request $request)
    {
        $requestId = uniqid('upload_');

        Log::info("📤 [$requestId] uploadImages: Request received", [
            'has_files'      => $request->hasFile('images'),
            'files_count'    => $request->hasFile('images') ? count($request->file('images')) : 0,
            'content_type'   => $request->header('Content-Type'),
            'content_length' => $request->header('Content-Length'),
        ]);

        $urls = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                try {
                    Log::info("📎 [$requestId] uploadImages: Processing file [$index]", [
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type'     => $file->getMimeType(),
                        'size_kb'       => round($file->getSize() / 1024, 2),
                        'is_valid'      => $file->isValid(),
                    ]);

                    // ✅ Compress and resize the image
                    $compressedPath = $this->compressImage($file);

                    if ($compressedPath) {
                        $url = asset('storage/' . $compressedPath);
                        $urls[] = $url;
                        Log::info("✅ [$requestId] uploadImages: File [$index] compressed & stored", [
                            'path' => $compressedPath,
                            'url'  => $url,
                        ]);
                    } else {
                        // Fallback: store original if compression fails
                        $path = $file->store('property_images', 'public');
                        $url  = asset('storage/' . $path);
                        $urls[] = $url;
                        Log::warning("⚠️ [$requestId] uploadImages: File [$index] compression failed, stored original");
                    }
                } catch (\Exception $e) {
                    Log::error("❌ [$requestId] uploadImages: File [$index] FAILED", [
                        'error' => $e->getMessage(),
                        'file'  => $e->getFile(),
                        'line'  => $e->getLine(),
                    ]);
                }
            }
        } else {
            Log::warning("⚠️ [$requestId] uploadImages: No files found in request");
        }

        Log::info("📤 [$requestId] uploadImages: Done", [
            'uploaded_count' => count($urls),
            'urls'           => $urls,
        ]);

        return response()->json(['urls' => $urls]);
    }

    /**
     * ✅ Compress and resize image using GD
     * Max width: 1280px, Quality: 75%, Format: JPEG
     */
    private function compressImage($file): ?string
    {
        try {
            $mime = $file->getMimeType();
            $sourcePath = $file->getRealPath();

            // Create image resource based on mime type
            $sourceImage = match ($mime) {
                'image/jpeg', 'image/jpg' => imagecreatefromjpeg($sourcePath),
                'image/png'               => imagecreatefrompng($sourcePath),
                'image/webp'              => imagecreatefromwebp($sourcePath),
                default                   => null,
            };

            if (!$sourceImage) return null;

            // Get original dimensions
            $originalWidth  = imagesx($sourceImage);
            $originalHeight = imagesy($sourceImage);

            // ✅ Max dimensions
            $maxWidth  = 1280;
            $maxHeight = 1280;

            // Calculate new dimensions keeping aspect ratio
            $ratio     = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
            $newWidth  = $ratio < 1 ? (int)($originalWidth * $ratio) : $originalWidth;
            $newHeight = $ratio < 1 ? (int)($originalHeight * $ratio) : $originalHeight;

            // Create new resized image
            $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

            // Handle PNG transparency
            if ($mime === 'image/png') {
                imagealphablending($resizedImage, false);
                imagesavealpha($resizedImage, true);
                $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
                imagefilledrectangle($resizedImage, 0, 0, $newWidth, $newHeight, $transparent);
            }

            // Resize
            imagecopyresampled(
                $resizedImage,
                $sourceImage,
                0,
                0,
                0,
                0,
                $newWidth,
                $newHeight,
                $originalWidth,
                $originalHeight
            );

            // Save to temp file
            $tempPath  = sys_get_temp_dir() . '/' . uniqid('img_') . '.jpg';
            imagejpeg($resizedImage, $tempPath, 75); // ✅ 75% quality

            // Free memory
            imagedestroy($sourceImage);
            imagedestroy($resizedImage);

            // Store compressed file to storage
            $storagePath = 'property_images/' . uniqid('prop_') . '.jpg';
            $fullPath    = storage_path('app/public/' . $storagePath);

            // Ensure directory exists
            if (!file_exists(dirname($fullPath))) {
                mkdir(dirname($fullPath), 0755, true);
            }

            rename($tempPath, $fullPath);

            return $storagePath;
        } catch (\Exception $e) {
            Log::error('Image compression failed', ['error' => $e->getMessage()]);
            return null;
        }
    }


    // ✅ REPLACE YOUR ENTIRE store() METHOD WITH THIS

    public function store(Request $request)
    {
        $requestId = uniqid('store_');

        try {
            Log::info("🏠 [$requestId] store: Request received", [
                'method'         => $request->method(),
                'content_type'   => $request->header('Content-Type'),
                'content_length' => $request->header('Content-Length'),
                'authorization'  => $request->header('Authorization') ? 'Bearer ***' : 'MISSING',
                'all_input_keys' => array_keys($request->all()),
                'has_files'      => $request->hasFile('images'),
                'raw_images'     => $request->input('images'),
            ]);

            $sanctumUser = auth('sanctum')->user();

            Log::info("🔐 [$requestId] store: Auth resolved", [
                'sanctum_user_class' => $sanctumUser ? get_class($sanctumUser) : 'NULL — no valid token',
                'sanctum_user_id'    => $sanctumUser?->id,
                'agent_guard_check'  => Auth::guard('agent')->check(),
                'agent_guard_id'     => Auth::guard('agent')->check() ? Auth::guard('agent')->id() : null,
            ]);

            if (!$sanctumUser) {
                Log::error("🚫 [$requestId] store: UNAUTHENTICATED — no valid Sanctum token");
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthenticated',
                    'data'    => 'No valid Sanctum token provided',
                ], 401);
            }

            if ($sanctumUser instanceof \App\Models\RealEstateOffice) {
                $request->merge(['owner_type' => 'RealEstateOffice', 'owner_id' => (string) $sanctumUser->id]);
            } elseif ($sanctumUser instanceof \App\Models\Agent) {
                $request->merge(['owner_type' => 'Agent', 'owner_id' => (string) $sanctumUser->id]);
            } elseif ($sanctumUser instanceof \App\Models\User) {
                $request->merge(['owner_type' => 'User', 'owner_id' => (string) $sanctumUser->id]);
            } elseif (Auth::guard('agent')->check()) {
                $request->merge(['owner_type' => 'Agent', 'owner_id' => (string) Auth::guard('agent')->id()]);
            }

            Log::info("👤 [$requestId] store: Owner resolved", [
                'owner_type' => $request->input('owner_type'),
                'owner_id'   => $request->input('owner_id'),
            ]);

            $parsedData = [];
            foreach ($request->all() as $key => $value) {
                if (is_string($value) && $this->isJson($value)) {
                    $parsedData[$key] = json_decode($value, true);
                } else {
                    $parsedData[$key] = $value;
                }
            }

            // ✅ FIX: Force price.iqd to 1 if missing, null, or zero
            if (
                !isset($parsedData['price']['iqd']) ||
                $parsedData['price']['iqd'] === null ||
                $parsedData['price']['iqd'] === '' ||
                (is_numeric($parsedData['price']['iqd']) && (float)$parsedData['price']['iqd'] <= 0)
            ) {
                $parsedData['price']['iqd'] = 1;
            }

            Log::info("🔄 [$requestId] store: Parsed data keys", [
                'keys'           => array_keys($parsedData),
                'images_type'    => gettype($parsedData['images'] ?? null),
                'images_count'   => is_array($parsedData['images'] ?? null) ? count($parsedData['images']) : 'NOT_ARRAY',
                'images_preview' => array_slice((array)($parsedData['images'] ?? []), 0, 2),
                'name'           => $parsedData['name'] ?? 'MISSING',
                'listing_type'   => $parsedData['listing_type'] ?? 'MISSING',
                'owner_type'     => $parsedData['owner_type'] ?? 'MISSING',
                'owner_id'       => $parsedData['owner_id'] ?? 'MISSING',
                'price'          => $parsedData['price'] ?? 'MISSING',
                'area'           => $parsedData['area'] ?? 'MISSING',
                'locations'      => $parsedData['locations'] ?? 'MISSING',
            ]);

            if (!isset($parsedData['images']) || !is_array($parsedData['images']) || count($parsedData['images']) < 1) {
                Log::error("❌ [$requestId] store: Images missing or invalid", [
                    'images_raw'  => $parsedData['images'] ?? 'KEY_NOT_SET',
                    'images_type' => gettype($parsedData['images'] ?? null),
                ]);
                return response()->json([
                    'status'  => false,
                    'message' => 'Validation failed',
                    'data'    => ['images' => ['At least one image is required']],
                ], 400);
            }

            $validator = Validator::make($parsedData, [
                'name'                    => 'required|array',
                'name.en'                 => 'required|string|max:255',
                'description'             => 'required|array',
                'description.en'          => 'required|string|min:10',
                'type'                    => 'required|array',
                'type.category'           => 'required|string',
                'area'                    => 'required|numeric|min:1',
                'furnished'               => 'required|boolean',
                'price'                   => 'required|array',
                'price.iqd'               => 'required|numeric|min:1', // ✅ always satisfied now
                'price.usd'               => 'required|numeric|min:1',
                'listing_type'            => 'required|in:rent,sell',
                'rooms'                   => 'required|array',
                'rooms.bedroom.count'     => 'required|integer|min:0',
                'rooms.bathroom.count'    => 'required|integer|min:0',
                'locations'               => 'required|array|min:1',
                'locations.*.lat'         => 'required|numeric|between:-90,90',
                'locations.*.lng'         => 'required|numeric|between:-180,180',
                'address_details'         => 'required|array',
                'address_details.city'    => 'required|array',
                'address_details.city.en' => 'required|string|min:2',
                'images'                  => 'required|array|min:1',
                'images.*'                => 'string|url',
                'owner_id'                => 'required|string',
                'owner_type'              => 'required|string',
                'electricity'             => 'nullable|boolean',
                'water'                   => 'nullable|boolean',
                'internet'                => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                Log::error("❌ [$requestId] store: Validation FAILED", [
                    'errors'       => $validator->errors()->toArray(),
                    'failed_rules' => $validator->failed(),
                ]);
                return response()->json([
                    'status'  => false,
                    'message' => 'Validation failed',
                    'data'    => $validator->errors(),
                ], 400);
            }

            Log::info("✅ [$requestId] store: Validation passed");

            DB::beginTransaction();
            Log::info("🗄️ [$requestId] store: DB transaction started");

            $imageUrls  = $parsedData['images'];
            $propertyId = $this->generateUniquePropertyId();

            Log::info("🚀 [$requestId] store: Inserting property", [
                'property_id'  => $propertyId,
                'owner_id'     => $parsedData['owner_id'],
                'owner_type'   => $this->getFullOwnerType($parsedData['owner_type'] ?? 'User'),
                'images_count' => count($imageUrls),
            ]);

            $propertyData = [
                'id'          => $propertyId,
                'owner_id'    => (string) $parsedData['owner_id'],
                'owner_type'  => $this->getFullOwnerType($parsedData['owner_type'] ?? 'User'),
                'name'            => json_encode($parsedData['name']),
                'description'     => json_encode($parsedData['description']),
                'type'            => json_encode($parsedData['type']),
                'price'           => json_encode($parsedData['price']),
                'rooms'           => json_encode($parsedData['rooms']),
                'locations'       => json_encode($parsedData['locations']),
                'address_details' => json_encode($parsedData['address_details']),
                'listing_type'    => $parsedData['listing_type'],
                'area'            => (float) $parsedData['area'],
                'address'         => $parsedData['address'] ?? null,
                'furnished'       => ($parsedData['furnished']    ?? false) ? 1 : 0,
                'electricity'     => ($parsedData['electricity']  ?? true)  ? 1 : 0,
                'water'           => ($parsedData['water']        ?? true)  ? 1 : 0,
                'internet'        => ($parsedData['internet']     ?? false) ? 1 : 0,
                'images'          => json_encode($imageUrls),
                'features'        => json_encode($parsedData['features']  ?? []),
                'amenities'       => json_encode($parsedData['amenities'] ?? []),
                'furnishing_details' => json_encode($parsedData['furnishing_details'] ?? ['status' => 'unfurnished']),
                'floor_details'   => isset($parsedData['floor_details']) && is_array($parsedData['floor_details'])
                    ? json_encode($parsedData['floor_details']) : null,
                'rental_period'    => $parsedData['rental_period']    ?? null,
                'floor_number'     => isset($parsedData['floor_number'])  ? (int) $parsedData['floor_number']  : null,
                'year_built'       => isset($parsedData['year_built'])    ? (int) $parsedData['year_built']    : null,
                'virtual_tour_url' => $parsedData['virtual_tour_url'] ?? null,
                'floor_plan_url'   => $parsedData['floor_plan_url']   ?? null,
                'availability'     => json_encode(['status' => 'available', 'labels' => ['en' => 'Available', 'ar' => 'متوفر', 'ku' => 'بەردەست']]),
                'verified'            => 0,
                'is_active'           => 1,
                'published'           => 1,
                'status'              => $parsedData['status'] ?? 'available',
                'views'               => 0,
                'favorites_count'     => 0,
                'rating'              => 0,
                'is_boosted'          => 0,
                'view_analytics'      => json_encode(['unique_views' => 0, 'returning_views' => 0]),
                'favorites_analytics' => json_encode(['last_30_days' => 0]),
            ];

            DB::table('properties')->insert($propertyData + [
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info("✅ [$requestId] store: DB insert successful", ['property_id' => $propertyId]);

            $property = Property::find($propertyId);

            if (!$property) {
                Log::error("❌ [$requestId] store: Property not found after insert", [
                    'attempted_id' => $propertyId,
                ]);
                DB::rollBack();
                return response()->json(['status' => false, 'message' => 'Failed to retrieve property after insert'], 500);
            }

            if ($sanctumUser instanceof \App\Models\RealEstateOffice) {
                $sanctumUser->incrementPropertyCount();
                Log::info("📈 [$requestId] store: Office property count incremented");
            }

            try {
                app(NotificationController::class)->sendNewPropertyNotifications($property->id);
                Log::info("🔔 [$requestId] store: Notifications sent");
            } catch (\Exception $e) {
                Log::warning("⚠️ [$requestId] store: Notification failed (non-fatal)", [
                    'error' => $e->getMessage(),
                ]);
            }

            DB::commit();
            Log::info("🎉 [$requestId] store: SUCCESS — property created", ['property_id' => $property->id]);

            return response()->json([
                'status'  => true,
                'message' => 'Property created successfully',
                'data'    => $property,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("💥 [$requestId] store: UNCAUGHT EXCEPTION", [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => array_slice(
                    array_map(fn($f) => ($f['file'] ?? '?') . ':' . ($f['line'] ?? '?'), $e->getTrace()),
                    0,
                    8
                ),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to create property',
                'data'    => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * ✅ Helper: Check if string is valid JSON
     */
    private function isJson($string)
    {
        if (!is_string($string)) return false;
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
    public function update(Request $request, $id)
    {
        $property = Property::findOrFail($id);

        // Only update fields that exist in the form
        $updates = $request->only([
            'title',
            'description',
            'price',
            'location',
            'type',
            'photos', // or any other field your form sends
        ]);

        // Merge and preserve important fields
        $property->fill($updates);

        // Keep these always as they were (never overwrite)
        $property->owner_id = $property->owner_id ?? Auth::id();
        $property->published = $property->published ?? 1;
        $property->is_active = $property->is_active ?? 1;

        // Save back
        $property->save();

        return redirect()->route('agent.property.list')->with('success', 'Property updated successfully!');
    }

    /**
     * ✅ Dedicated Update Method for Mobile/API (Fixed for Casts)
     */
    public function updateMobile(Request $request, $id)
    {
        try {
            $property = Property::findOrFail($id);

            // 1. Parse JSON strings (Fix for Flutter sending nested objects as strings)
            $data = $request->all();
            $parsedData = [];

            foreach ($data as $key => $value) {
                if (is_string($value) && $this->isJson($value)) {
                    $parsedData[$key] = json_decode($value, true);
                } else {
                    $parsedData[$key] = $value;
                }
            }

            Log::info('📱 Mobile Update Request', ['id' => $id, 'parsed_data' => $parsedData]);

            // 2. Prepare Update Array
            $updatePayload = [];

            // JSON Fields: Re-encode them for the DB update
            // Note: Since we are using ->update(), if the model casts are 'array',
            // Laravel expects arrays, not JSON strings.
            // However, to be safe and explicit with raw updates or specific handling:

            if (isset($parsedData['name'])) $updatePayload['name'] = $parsedData['name'];
            if (isset($parsedData['description'])) $updatePayload['description'] = $parsedData['description'];
            if (isset($parsedData['price'])) $updatePayload['price'] = $parsedData['price'];
            if (isset($parsedData['type'])) $updatePayload['type'] = $parsedData['type'];
            if (isset($parsedData['rooms'])) $updatePayload['rooms'] = $parsedData['rooms'];
            if (isset($parsedData['locations'])) $updatePayload['locations'] = $parsedData['locations'];
            if (isset($parsedData['address_details'])) $updatePayload['address_details'] = $parsedData['address_details'];

            // ✅ Images: If sending new full list, use it directly
            if (isset($parsedData['images'])) $updatePayload['images'] = $parsedData['images'];

            // Simple fields
            if (isset($parsedData['listing_type'])) $updatePayload['listing_type'] = $parsedData['listing_type'];
            if (isset($parsedData['area'])) $updatePayload['area'] = $parsedData['area'];
            if (isset($parsedData['address'])) $updatePayload['address'] = $parsedData['address'];
            if (isset($parsedData['status'])) $updatePayload['status'] = $parsedData['status'];

            // Booleans
            if (isset($parsedData['furnished'])) $updatePayload['furnished'] = $parsedData['furnished'] ? true : false;
            if (isset($parsedData['electricity'])) $updatePayload['electricity'] = $parsedData['electricity'] ? true : false;
            if (isset($parsedData['water'])) $updatePayload['water'] = $parsedData['water'] ? true : false;
            if (isset($parsedData['internet'])) $updatePayload['internet'] = $parsedData['internet'] ? true : false;

            // Integers
            if (isset($parsedData['floor_number'])) $updatePayload['floor_number'] = (int)$parsedData['floor_number'];
            if (isset($parsedData['year_built'])) $updatePayload['year_built'] = (int)$parsedData['year_built'];

            // 3. Handle Image Removal Logic
            // If the app sends specific indexes/urls to remove, we process that here.
            // OTHERWISE, if the app sent a fresh 'images' array above, that overwrites everything.

            if (isset($parsedData['images_to_remove']) && is_array($parsedData['images_to_remove']) && !empty($parsedData['images_to_remove'])) {

                // ✅ CRITICAL FIX: Access directly as array (Laravel casts handles decoding)
                $currentImages = $property->images ?? [];

                // If mistakenly returned as string due to some raw query elsewhere
                if (is_string($currentImages)) {
                    $currentImages = json_decode($currentImages, true) ?? [];
                }

                // Remove images by Index (if integers passed) or by Value (if URL strings passed)
                foreach ($parsedData['images_to_remove'] as $removeItem) {
                    if (is_int($removeItem)) {
                        unset($currentImages[$removeItem]);
                    } else {
                        $key = array_search($removeItem, $currentImages);
                        if ($key !== false) unset($currentImages[$key]);
                    }
                }

                // Re-index array keys and save
                $updatePayload['images'] = array_values($currentImages);
            }

            // 4. Update Database
            // Because your model has $casts = ['images' => 'array', ...],
            // passing PHP arrays into update() is the correct way. Laravel will json_encode them automatically.
            $property->update($updatePayload);

            return ApiResponse::success(
                'Property updated successfully',
                $this->transformPropertyData($property->fresh()),
                200
            );
        } catch (\Exception $e) {
            Log::error('Mobile update error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return ApiResponse::error('Failed to update property', $e->getMessage(), 500);
        }
    }

    /**
     * Delete property
     */
    public function destroy($id)
    {
        try {
            $property = Property::find($id);

            if (!$property) {
                return ApiResponse::error(
                    'Property not found',
                    ['id' => $id],
                    404
                );
            }

            $property->delete();

            return ApiResponse::success(
                'Property deleted successfully',
                ['id' => $id],
                200
            );
        } catch (\Exception $e) {
            Log::error('Property delete error', [
                'message' => $e->getMessage(),
                'property_id' => $id
            ]);

            return ApiResponse::error(
                'Failed to delete property',
                $e->getMessage(),
                500
            );
        }
    }

    public function toggleVerification($id)
    {
        try {
            $property = Property::find($id);

            if (!$property) {
                return ApiResponse::error(
                    'Property not found',
                    ['id' => $id],
                    404
                );
            }

            $wasVerified = $property->verified;
            $property->verified = !$property->verified;
            $property->save();

            // Send verification notification if property got verified
            if (!$wasVerified && $property->verified) {
                app(NotificationController::class)->sendPropertyVerificationNotification($property->id);
            }

            return ApiResponse::success(
                $property->verified ? 'Property verified' : 'Property verification removed',
                [
                    'id' => $property->id,
                    'verified' => $property->verified
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error('Property verification error', [
                'message' => $e->getMessage(),
                'property_id' => $id
            ]);

            return ApiResponse::error(
                'Failed to toggle verification',
                $e->getMessage(),
                500
            );
        }
    }

    public function toggleActive($id)
    {
        try {
            $property = Property::find($id);

            if (!$property) {
                return ApiResponse::error(
                    'Property not found',
                    ['id' => $id],
                    404
                );
            }

            $property->is_active = !$property->is_active;
            $property->save();

            return ApiResponse::success(
                $property->is_active ? 'Property activated' : 'Property deactivated',
                [
                    'id' => $property->id,
                    'is_active' => $property->is_active
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error('Property active toggle error', [
                'message' => $e->getMessage(),
                'property_id' => $id
            ]);

            return ApiResponse::error(
                'Failed to toggle active status',
                $e->getMessage(),
                500
            );
        }
    }

    /**
     * Convert short owner type to full class name
     */
    private function getFullOwnerType($shortType)
    {
        $mapping = [
            'User' => 'App\\Models\\User',
            'Agent' => 'App\\Models\\Agent',
            'RealEstateOffice' => 'App\\Models\\RealEstateOffice'
        ];

        return $mapping[$shortType] ?? $shortType;
    }

    public function toggleBoost($id, Request $request)
    {
        try {
            $property = Property::find($id);
            if (!$property) {
                return ApiResponse::error('Property not found', ['id' => $id], 404);
            }

            $validator = Validator::make($request->all(), [
                'boost_start_date'    => 'nullable|date|after_or_equal:today',
                'boost_end_date'      => 'nullable|date|after:boost_start_date',
                'boost_duration_days' => 'nullable|integer|min:1|max:365'
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Invalid boost parameters', $validator->errors(), 400);
            }

            $property->is_boosted = !$property->is_boosted;

            if ($property->is_boosted) {
                $property->boost_start_date = $request->get('boost_start_date', now());

                if ($request->has('boost_end_date')) {
                    $property->boost_end_date = $request->boost_end_date;
                } elseif ($request->has('boost_duration_days')) {
                    $property->boost_end_date = now()->addDays($request->boost_duration_days);
                }
            } else {
                $property->boost_start_date = null;
                $property->boost_end_date   = null;
            }

            $property->save();

            $this->bustFeaturedCache();

            return ApiResponse::success(
                $property->is_boosted ? 'Property boosted successfully' : 'Property boost removed',
                [
                    'id'               => $property->id,
                    'is_boosted'       => $property->is_boosted,
                    'boost_start_date' => $property->boost_start_date,
                    'boost_end_date'   => $property->boost_end_date,
                    'boost_active'     => $property->isBoosted()
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error('Toggle boost error', [
                'message'     => $e->getMessage(),
                'property_id' => $id
            ]);
            return ApiResponse::error('Failed to toggle boost status', $e->getMessage(), 500);
        }
    }

    private function bustFeaturedCache(): void
    {
        $this->interactionService->bustFeaturedCache();

        // Keep old patterns too for safety
        foreach (['balanced', 'premium', 'engagement', 'recent'] as $strategy) {
            foreach ([5, 10, 20, 50] as $limit) {
                \Illuminate\Support\Facades\Cache::forget("featured_properties_{$strategy}_{$limit}");
            }
        }
    }

    public function updateStatus($id, Request $request)
    {
        try {
            $property = Property::find($id);

            if (!$property) {
                return ApiResponse::error(
                    'Property not found',
                    ['id' => $id],
                    404
                );
            }

            $validator = Validator::make($request->all(), [
                'status' => 'required|in:cancelled,pending,approved,available,sold,rented',
                'note' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return ApiResponse::error(
                    'Invalid status parameters',
                    $validator->errors(),
                    400
                );
            }

            $oldStatus = $property->status;
            $newStatus = $request->status;

            $property->status = $newStatus;

            // Auto-adjust related fields based on status
            switch ($newStatus) {
                case 'cancelled':
                    $property->is_active = false;
                    $property->published = false;
                    break;
                case 'pending':
                    $property->published = false;
                    break;
                case 'approved':
                    $property->is_active = true;
                    $property->published = true;
                    break;
                case 'sold':
                case 'rented':
                    $property->is_active = false;
                    $property->published = false;
                    break;
                case 'available':
                    $property->is_active = true;
                    $property->published = true;
                    break;
            }

            $property->save();

            return ApiResponse::success(
                'Property status updated successfully',
                [
                    'id' => $property->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'is_active' => $property->is_active,
                    'published' => $property->published,
                    'note' => $request->get('note')
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error('Update status error', [
                'message' => $e->getMessage(),
                'property_id' => $id
            ]);

            return ApiResponse::error(
                'Failed to update property status',
                $e->getMessage(),
                500
            );
        }
    }

    public function bulkUpdate(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'property_ids' => 'required|array|min:1',
                'property_ids.*' => 'required|string',
                'action' => 'required|in:activate,deactivate,verify,unverify,publish,unpublish,boost,unboost',
                'boost_duration_days' => 'required_if:action,boost|nullable|integer|min:1|max:365'
            ]);

            if ($validator->fails()) {
                return ApiResponse::error(
                    'Invalid bulk update parameters',
                    $validator->errors(),
                    400
                );
            }

            $propertyIds = $request->property_ids;
            $action = $request->action;

            $updateData = [];
            switch ($action) {
                case 'activate':
                    $updateData['is_active'] = true;
                    break;
                case 'deactivate':
                    $updateData['is_active'] = false;
                    break;
                case 'verify':
                    $updateData['verified'] = true;
                    break;
                case 'unverify':
                    $updateData['verified'] = false;
                    break;
                case 'publish':
                    $updateData['published'] = true;
                    break;
                case 'unpublish':
                    $updateData['published'] = false;
                    break;
                case 'boost':
                    $updateData['is_boosted'] = true;
                    $updateData['boost_start_date'] = now();
                    if ($request->has('boost_duration_days')) {
                        $updateData['boost_end_date'] = now()->addDays($request->boost_duration_days);
                    }
                    break;
                case 'unboost':
                    $updateData['is_boosted'] = false;
                    $updateData['boost_start_date'] = null;
                    $updateData['boost_end_date'] = null;
                    break;
            }

            $updatedCount = Property::whereIn('id', $propertyIds)->update($updateData);

            return ApiResponse::success(
                "Bulk {$action} completed successfully",
                [
                    'updated_count' => $updatedCount,
                    'action' => $action,
                    'property_ids' => $propertyIds
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error('Bulk update error', [
                'message' => $e->getMessage(),
                'action' => $request->action ?? 'unknown'
            ]);

            return ApiResponse::error(
                'Failed to perform bulk update',
                $e->getMessage(),
                500
            );
        }
    }

    private function generateUniquePropertyId(): string
    {
        do {
            $propertyId = 'prop_' . date('Y_m_d') . '_' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (Property::where('id', $propertyId)->exists());

        return $propertyId;
    }

    public function togglePublish($id)
    {
        try {
            $property = Property::find($id);

            if (!$property) {
                return ApiResponse::error('Property not found', ['id' => $id], 404);
            }

            $property->published = !$property->published;
            $property->save();

            return ApiResponse::success(
                $property->published ? 'Property published' : 'Property unpublished',
                [
                    'id' => $property->id,
                    'published' => $property->published
                ],
                200
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to toggle publish status', $e->getMessage(), 500);
        }
    }

    public function bulkVerify(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'property_ids' => 'required|array|min:1',
                'property_ids.*' => 'required|string',
                'verify' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Invalid parameters', $validator->errors(), 400);
            }

            $updatedCount = Property::whereIn('id', $request->property_ids)
                ->update(['verified' => $request->verify]);

            return ApiResponse::success(
                'Bulk verification completed',
                [
                    'updated_count' => $updatedCount,
                    'verified' => $request->verify
                ],
                200
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to bulk verify', $e->getMessage(), 500);
        }
    }

    public function bulkPublish(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'property_ids' => 'required|array|min:1',
                'property_ids.*' => 'required|string',
                'publish' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Invalid parameters', $validator->errors(), 400);
            }

            $updatedCount = Property::whereIn('id', $request->property_ids)
                ->update(['published' => $request->publish]);

            return ApiResponse::success(
                'Bulk publish operation completed',
                [
                    'updated_count' => $updatedCount,
                    'published' => $request->publish
                ],
                200
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to bulk publish', $e->getMessage(), 500);
        }
    }

    public function bulkStatusUpdate(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'property_ids' => 'required|array|min:1',
                'property_ids.*' => 'required|string',
                'status' => 'required|in:available,sold,rented,pending'
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Invalid parameters', $validator->errors(), 400);
            }

            $updatedCount = Property::whereIn('id', $request->property_ids)
                ->update(['status' => $request->status]);

            return ApiResponse::success(
                'Bulk status update completed',
                [
                    'updated_count' => $updatedCount,
                    'new_status' => $request->status
                ],
                200
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to bulk update status', $e->getMessage(), 500);
        }
    }

    public function edit($property_id)
    {
        $property = Property::findOrFail($property_id);
        return view('agent.edit-property', compact('property'));
    }

    public function removeImage(Request $request, $property_id)
    {
        $property = Property::find($property_id);
        if (!$property) {
            return redirect()->back()->withErrors('Property not found');
        }

        $photoPath = $request->input('photo_path');
        if (!$photoPath) {
            return redirect()->back()->withErrors('No photo specified');
        }

        // Remove the photo from the property images array
        $images = is_string($property->images) ? json_decode($property->images, true) : $property->images;

        if (($key = array_search($photoPath, $images)) !== false) {
            unset($images[$key]);
            $images = array_values($images); // reindex
            $property->images = json_encode($images);
            $property->save();

            // Optionally delete the file from storage
            if (file_exists(public_path($photoPath))) {
                @unlink(public_path($photoPath));
            }
        }

        return redirect()->back()->with('success', 'Image removed successfully');
    }
}
