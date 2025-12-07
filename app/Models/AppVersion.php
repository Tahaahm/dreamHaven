<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    /**
     * This model represents app version data from Firebase Firestore
     * It does NOT use MySQL database - data comes from Firestore only
     */

    // Disable database operations since this is Firestore-only
    protected $table = null;
    public $timestamps = false;
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'androidUrl',
        'buildNumber',
        'forceUpdate',
        'iosUrl',
        'minSupportedVersion',
        'releaseDate',
        'updateMessage',
        'version',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'buildNumber' => 'integer',
        'forceUpdate' => 'boolean',
        'minSupportedVersion' => 'string',
        'version' => 'string',
        'releaseDate' => 'datetime',
    ];

    /**
     * Create instance from Firestore data
     *
     * @param array $firestoreData
     * @return static
     */
    public static function fromFirestore(array $firestoreData)
    {
        $instance = new static();

        foreach ($firestoreData as $key => $value) {
            $instance->setAttribute($key, $value);
        }

        return $instance;
    }

    /**
     * Check if current app version needs update
     *
     * @param string $currentVersion
     * @return bool
     */
    public function needsUpdate(string $currentVersion): bool
    {
        return version_compare($currentVersion, $this->version, '<');
    }

    /**
     * Check if current version is below minimum supported
     *
     * @param string $currentVersion
     * @return bool
     */
    public function isVersionSupported(string $currentVersion): bool
    {
        if (!$this->minSupportedVersion) {
            return true;
        }

        return version_compare($currentVersion, $this->minSupportedVersion, '>=');
    }

    /**
     * Check if force update is required
     *
     * @param string $currentVersion
     * @return bool
     */
    public function requiresForceUpdate(string $currentVersion): bool
    {
        return $this->forceUpdate && $this->needsUpdate($currentVersion);
    }

    /**
     * Get update info for client
     *
     * @param string $currentVersion
     * @param string $platform (ios|android)
     * @return array
     */
    public function getUpdateInfo(string $currentVersion, string $platform = 'android'): array
    {
        $needsUpdate = $this->needsUpdate($currentVersion);
        $isSupported = $this->isVersionSupported($currentVersion);
        $forceUpdate = $this->requiresForceUpdate($currentVersion);

        return [
            'needs_update' => $needsUpdate,
            'is_supported' => $isSupported,
            'force_update' => $forceUpdate,
            'latest_version' => $this->version,
            'current_version' => $currentVersion,
            'min_supported_version' => $this->minSupportedVersion,
            'update_message' => $needsUpdate ? $this->updateMessage : null,
            'download_url' => $platform === 'ios' ? $this->iosUrl : $this->androidUrl,
            'build_number' => $this->buildNumber,
            'release_date' => $this->releaseDate,
        ];
    }
}
