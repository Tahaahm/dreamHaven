<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class UserAppointmentService
{
    /**
     * Get user appointments with filters and pagination
     */
    public function getUserAppointments(User $user, array $filters = []): array
    {
        $query = DB::table('appointments')->where('user_id', $user->id);

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('appointment_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('appointment_date', '<=', $filters['date_to']);
        }

        $limit = $filters['limit'] ?? 20;
        $offset = $filters['offset'] ?? 0;

        $appointments = $query
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get();

        $transformedAppointments = $appointments->map(function ($appointment) {
            return $this->transformAppointmentData($appointment);
        });

        // Get total count for pagination
        $totalQuery = DB::table('appointments')->where('user_id', $user->id);
        if (isset($filters['status'])) $totalQuery->where('status', $filters['status']);
        if (isset($filters['type'])) $totalQuery->where('type', $filters['type']);
        if (isset($filters['date_from'])) $totalQuery->whereDate('appointment_date', '>=', $filters['date_from']);
        if (isset($filters['date_to'])) $totalQuery->whereDate('appointment_date', '<=', $filters['date_to']);
        $totalCount = $totalQuery->count();

        return [
            'appointments' => $transformedAppointments,
            'total_count' => $totalCount,
            'current_count' => $appointments->count(),
            'has_more' => ($offset + $appointments->count()) < $totalCount,
        ];
    }

    /**
     * Get appointments by user ID
     */
    public function getAppointmentsByUserId(string $userId): array
    {
        $appointments = DB::table('appointments')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        $transformedAppointments = $appointments->map(function ($appointment) {
            return $this->transformAppointmentData($appointment);
        });

        return [
            'appointments' => $transformedAppointments,
            'user_id' => $userId,
            'total_count' => $appointments->count(),
        ];
    }

    /**
     * Cancel appointment
     */
    public function cancelAppointment(User $user, string $appointmentId): bool
    {
        $appointment = DB::table('appointments')
            ->where('id', $appointmentId)
            ->where('user_id', $user->id)
            ->first();

        if (!$appointment || $appointment->status === 'cancelled' || $appointment->status === 'completed') {
            return false;
        }

        DB::beginTransaction();
        try {
            DB::table('appointments')
                ->where('id', $appointmentId)
                ->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'updated_at' => now()
                ]);

            // Send notification
            $this->sendAppointmentStatusNotification($appointmentId, 'cancelled');

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to cancel appointment', [
                'appointment_id' => $appointmentId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Reschedule appointment
     */
    public function rescheduleAppointment(User $user, string $appointmentId, string $date, string $time): ?array
    {
        $appointment = DB::table('appointments')
            ->where('id', $appointmentId)
            ->where('user_id', $user->id)
            ->first();

        if (!$appointment || in_array($appointment->status, ['completed', 'cancelled'])) {
            return null;
        }

        DB::beginTransaction();
        try {
            DB::table('appointments')
                ->where('id', $appointmentId)
                ->update([
                    'appointment_date' => $date,
                    'appointment_time' => $time,
                    'status' => 'pending',
                    'confirmed_at' => null,
                    'updated_at' => now()
                ]);

            // Send notification
            $this->sendAppointmentStatusNotification($appointmentId, 'pending');

            $updatedAppointment = DB::table('appointments')->where('id', $appointmentId)->first();

            DB::commit();
            return $this->transformAppointmentData($updatedAppointment);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reschedule appointment', [
                'appointment_id' => $appointmentId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Delete appointment
     */
    public function deleteAppointment(User $user, string $appointmentId): bool
    {
        $appointment = DB::table('appointments')
            ->where('id', $appointmentId)
            ->where('user_id', $user->id)
            ->first();

        if (!$appointment || $appointment->status === 'completed') {
            return false;
        }

        $deleted = DB::table('appointments')
            ->where('id', $appointmentId)
            ->where('user_id', $user->id)
            ->delete();

        return $deleted > 0;
    }

    /**
     * Create appointment
     */
    public function createAppointment(array $data): array
    {
        DB::beginTransaction();
        try {
            $appointmentData = [
                'id' => (string) Str::uuid(),
                'user_id' => $data['user_id'],
                'agent_id' => $data['agent_id'] ?? null,
                'office_id' => $data['office_id'] ?? null,
                'property_id' => $data['property_id'] ?? null,
                'appointment_date' => $data['appointment_date'],
                'appointment_time' => $data['appointment_time'],
                'status' => $data['status'] ?? 'pending',
                'type' => $data['type'],
                'location' => $data['location'] ?? null,
                'notes' => $data['notes'] ?? null,
                'client_name' => $data['client_name'],
                'client_phone' => $data['client_phone'] ?? null,
                'client_email' => $data['client_email'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            DB::table('appointments')->insert($appointmentData);

            // Send notifications
            $this->sendAppointmentNotifications($appointmentData['id']);

            DB::commit();
            return $appointmentData;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Create bulk appointments
     */
    public function createBulkAppointments(array $appointments): array
    {
        DB::beginTransaction();
        try {
            $insertData = [];
            foreach ($appointments as $appointment) {
                $insertData[] = [
                    'id' => (string) Str::uuid(),
                    'user_id' => $appointment['user_id'],
                    'agent_id' => $appointment['agent_id'] ?? null,
                    'office_id' => $appointment['office_id'] ?? null,
                    'property_id' => $appointment['property_id'] ?? null,
                    'appointment_date' => $appointment['appointment_date'],
                    'appointment_time' => $appointment['appointment_time'],
                    'status' => $appointment['status'] ?? 'pending',
                    'type' => $appointment['type'],
                    'location' => $appointment['location'] ?? null,
                    'notes' => $appointment['notes'] ?? null,
                    'client_name' => $appointment['client_name'],
                    'client_phone' => $appointment['client_phone'] ?? null,
                    'client_email' => $appointment['client_email'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('appointments')->insert($insertData);

            DB::commit();
            return [
                'created_count' => count($insertData),
                'appointments' => $insertData
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function transformAppointmentData($appointment): array
    {
        return [
            'id' => $appointment->id,
            'appointment_date' => $appointment->appointment_date,
            'appointment_time' => $appointment->appointment_time,
            'status' => $appointment->status,
            'type' => $appointment->type,
            'location' => $appointment->location,
            'notes' => $appointment->notes,
            'client_name' => $appointment->client_name,
            'client_phone' => $appointment->client_phone,
            'client_email' => $appointment->client_email,
            'confirmed_at' => $appointment->confirmed_at ?? null,
            'completed_at' => $appointment->completed_at ?? null,
            'cancelled_at' => $appointment->cancelled_at ?? null,
            'created_at' => $appointment->created_at,
            'updated_at' => $appointment->updated_at,
        ];
    }

    private function sendAppointmentNotifications(string $appointmentId): void
    {
        try {
            if (class_exists('App\Http\Controllers\NotificationController')) {
                app(\App\Http\Controllers\NotificationController::class)->sendAppointmentNotifications($appointmentId);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to send appointment notifications', [
                'appointment_id' => $appointmentId,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function sendAppointmentStatusNotification(string $appointmentId, string $status): void
    {
        try {
            if (class_exists('App\Http\Controllers\NotificationController')) {
                app(\App\Http\Controllers\NotificationController::class)->sendAppointmentStatusNotification($appointmentId, $status);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to send appointment status notification', [
                'appointment_id' => $appointmentId,
                'status' => $status,
                'error' => $e->getMessage()
            ]);
        }
    }
}
