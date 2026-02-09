<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Base service untuk notifikasi surat (Tugas & Keputusan)
 *
 * ✅ REFACTORED: Enhanced security dengan input validation dan log sanitization
 *
 * Shared functionality:
 * - Database notification creation
 * - User validation & filtering
 * - Error handling & logging
 * - Common helper methods
 */
abstract class BaseNotificationService
{
    /**
     * Tipe notifikasi untuk child classes
     * Must be overridden by child
     */
    abstract protected function getNotificationType(): string;

    /**
     * Create database notification dengan error handling
     * ✅ IMPROVED: Added input validation
     *
     * @return bool Success status
     */
    protected function createNotification(int $userId, int $referenceId, string $message): bool
    {
        try {
            // ✅ ADDED: Validate IDs
            $validUserId = validate_integer_id($userId);
            $validReferenceId = validate_integer_id($referenceId);

            if ($validUserId === null || $validReferenceId === null) {
                Log::warning('Invalid IDs for notification', [
                    'user_id' => $userId,
                    'reference_id' => $referenceId,
                ]);

                return false;
            }

            // ✅ ADDED: Sanitize message
            $sanitizedMessage = sanitize_notification($message, 500);

            if (empty($sanitizedMessage)) {
                Log::warning('Empty or invalid notification message', [
                    'user_id' => $validUserId,
                    'reference_id' => $validReferenceId,
                ]);

                return false;
            }

            // ✅ ADDED: Check if user exists and is active
            $user = $this->getActiveUser($validUserId);
            if (! $user) {
                Log::warning('User not found or inactive', [
                    'user_id' => $validUserId,
                ]);

                return false;
            }

            // ✅ IMPROVED: Create notification with validated data
            Notifikasi::create([
                'pengguna_id' => $validUserId,
                'tipe' => $this->getNotificationType(),
                'referensi_id' => $validReferenceId,
                'pesan' => $sanitizedMessage,
                'dibaca' => false,
                'dibuat_pada' => now(), // ✅ ADDED
            ]);

            Log::debug('Notification created', [
                'user_id' => $validUserId,
                'type' => sanitize_log_message($this->getNotificationType()), // ✅ ADDED sanitization
                'reference_id' => $validReferenceId,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to create notification', [
                'user_id' => $userId,
                'type' => sanitize_log_message($this->getNotificationType()), // ✅ ADDED sanitization
                'reference_id' => $referenceId,
                'error' => sanitize_log_message($e->getMessage()), // ✅ ADDED sanitization
            ]);

            return false;
        }
    }

    /**
     * Get active user by ID dengan validation
     * ✅ IMPROVED: Enhanced validation
     */
    protected function getActiveUser(?int $userId): ?User
    {
        // ✅ ADDED: Validate ID
        $validUserId = validate_integer_id($userId);

        if ($validUserId === null) {
            return null;
        }

        return User::where('id', $validUserId)->where('status', 'aktif')->first();
    }

    /**
     * Validate email address
     * ✅ IMPROVED: Enhanced email validation
     */
    protected function isValidEmail(?string $email): bool
    {
        if (empty($email)) {
            return false;
        }

        // ✅ ADDED: Sanitize email first
        $sanitizedEmail = sanitize_email($email);

        return ! empty($sanitizedEmail) && filter_var($sanitizedEmail, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Queue email dengan error handling
     * ✅ IMPROVED: Added email validation
     *
     * @return bool Success status
     */
    protected function queueEmail(object $mailable, string $email, string $queue = 'mail', int $delaySeconds = 5): bool
    {
        try {
            // ✅ ADDED: Validate email
            if (! $this->isValidEmail($email)) {
                Log::warning('Invalid email address for queueing', [
                    'email' => sanitize_log_message($email),
                ]);

                return false;
            }

            // ✅ ADDED: Sanitize email
            $sanitizedEmail = sanitize_email($email);

            // ✅ ADDED: Validate queue name
            $queue = sanitize_input($queue, 50) ?? 'mail';

            // ✅ ADDED: Validate delay (max 1 hour)
            $delaySeconds = max(0, min($delaySeconds, 3600));

            Mail::to($sanitizedEmail)->later(now()->addSeconds($delaySeconds), $mailable);

            Log::debug('Email queued', [
                'email' => sanitize_log_message($sanitizedEmail), // ✅ ADDED sanitization
                'queue' => sanitize_log_message($queue), // ✅ ADDED sanitization
                'delay' => $delaySeconds,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to queue email', [
                'email' => sanitize_log_message($email), // ✅ ADDED sanitization
                'error' => sanitize_log_message($e->getMessage()), // ✅ ADDED sanitization
            ]);

            return false;
        }
    }

    /**
     * Dispatch queue job dengan error handling
     * ✅ IMPROVED: Enhanced validation
     *
     * @return bool Success status
     */
    protected function dispatchJob(object $job, string $queue = 'mail', int $delaySeconds = 5): bool
    {
        try {
            // ✅ ADDED: Validate queue name
            $queue = sanitize_input($queue, 50) ?? 'mail';

            // ✅ ADDED: Validate delay (max 1 hour)
            $delaySeconds = max(0, min($delaySeconds, 3600));

            dispatch($job)
                ->onQueue($queue)
                ->delay(now()->addSeconds($delaySeconds));

            Log::debug('Job dispatched', [
                'job' => sanitize_log_message(get_class($job)), // ✅ ADDED sanitization
                'queue' => sanitize_log_message($queue), // ✅ ADDED sanitization
                'delay' => $delaySeconds,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to dispatch job', [
                'job' => sanitize_log_message(get_class($job)), // ✅ ADDED sanitization
                'error' => sanitize_log_message($e->getMessage()), // ✅ ADDED sanitization
            ]);

            return false;
        }
    }

    /**
     * Log notification activity
     * ✅ IMPROVED: Enhanced sanitization
     */
    protected function logNotificationActivity(string $action, int $documentId, array $context = []): void
    {
        // ✅ ADDED: Validate document ID
        $validDocId = validate_integer_id($documentId);

        if ($validDocId === null) {
            Log::warning('Invalid document ID for logging', [
                'document_id' => $documentId,
            ]);

            return;
        }

        // ✅ ADDED: Sanitize all context values
        $sanitizedContext = [];
        foreach ($context as $key => $value) {
            $sanitizedKey = sanitize_input($key, 50);

            if (is_string($value)) {
                $sanitizedContext[$sanitizedKey] = sanitize_log_message($value);
            } elseif (is_numeric($value)) {
                $sanitizedContext[$sanitizedKey] = $value;
            } elseif (is_bool($value)) {
                $sanitizedContext[$sanitizedKey] = $value;
            } elseif (is_array($value)) {
                // For arrays, convert to JSON safely
                $sanitizedContext[$sanitizedKey] = json_encode($value);
            } else {
                $sanitizedContext[$sanitizedKey] = 'non-displayable';
            }
        }

        Log::info(
            'Notification: '.sanitize_log_message($action),
            array_merge(
                [
                    'type' => sanitize_log_message($this->getNotificationType()),
                    'document_id' => $validDocId,
                ],
                $sanitizedContext,
            ),
        );
    }

    /**
     * ✅ ADDED: Batch create notifications
     *
     * @return array [success_count, failed_count]
     */
    protected function createBatchNotifications(array $userIds, int $referenceId, string $message): array
    {
        $successCount = 0;
        $failedCount = 0;

        // ✅ Validate reference ID
        $validReferenceId = validate_integer_id($referenceId);
        if ($validReferenceId === null) {
            return [0, count($userIds)];
        }

        // ✅ Sanitize message once
        $sanitizedMessage = sanitize_notification($message, 500);
        if (empty($sanitizedMessage)) {
            return [0, count($userIds)];
        }

        foreach ($userIds as $userId) {
            if ($this->createNotification($userId, $validReferenceId, $sanitizedMessage)) {
                $successCount++;
            } else {
                $failedCount++;
            }
        }

        Log::info('Batch notifications created', [
            'type' => sanitize_log_message($this->getNotificationType()),
            'reference_id' => $validReferenceId,
            'success' => $successCount,
            'failed' => $failedCount,
        ]);

        return [$successCount, $failedCount];
    }

    /**
     * ✅ ADDED: Get notification statistics
     */
    protected function getNotificationStats(int $userId): array
    {
        $validUserId = validate_integer_id($userId);

        if ($validUserId === null) {
            return [
                'total' => 0,
                'unread' => 0,
                'read' => 0,
            ];
        }

        try {
            $stats = Notifikasi::where('pengguna_id', $validUserId)
                ->where('tipe', $this->getNotificationType())
                ->selectRaw(
                    '
                    COUNT(*) as total,
                    SUM(CASE WHEN dibaca = 0 THEN 1 ELSE 0 END) as unread,
                    SUM(CASE WHEN dibaca = 1 THEN 1 ELSE 0 END) as read
                ',
                )
                ->first();

            return [
                'total' => $stats->total ?? 0,
                'unread' => $stats->unread ?? 0,
                'read' => $stats->read ?? 0,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get notification stats', [
                'user_id' => $validUserId,
                'type' => sanitize_log_message($this->getNotificationType()),
                'error' => sanitize_log_message($e->getMessage()),
            ]);

            return [
                'total' => 0,
                'unread' => 0,
                'read' => 0,
            ];
        }
    }
}
