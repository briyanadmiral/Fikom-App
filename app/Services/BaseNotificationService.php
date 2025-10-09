<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Base service untuk notifikasi surat (Tugas & Keputusan)
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
     *
     * @param int $userId
     * @param int $referenceId
     * @param string $message
     * @return bool Success status
     */
    protected function createNotification(int $userId, int $referenceId, string $message): bool
    {
        try {
            Notifikasi::create([
                'pengguna_id' => $userId,
                'tipe' => $this->getNotificationType(),
                'referensi_id' => $referenceId,
                'pesan' => $message,
                'dibaca' => false,
            ]);

            Log::debug('Notification created', [
                'user_id' => $userId,
                'type' => $this->getNotificationType(),
                'reference_id' => $referenceId
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to create notification', [
                'user_id' => $userId,
                'type' => $this->getNotificationType(),
                'reference_id' => $referenceId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get active user by ID dengan validation
     *
     * @param int|null $userId
     * @return User|null
     */
    protected function getActiveUser(?int $userId): ?User
    {
        if (!$userId) {
            return null;
        }

        return User::where('id', $userId)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Validate email address
     *
     * @param string|null $email
     * @return bool
     */
    protected function isValidEmail(?string $email): bool
    {
        return !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Queue email dengan error handling
     *
     * @param object $mailable
     * @param string $email
     * @param string $queue
     * @param int $delaySeconds
     * @return bool Success status
     */
    protected function queueEmail(
        object $mailable,
        string $email,
        string $queue = 'mail',
        int $delaySeconds = 5
    ): bool {
        try {
            Mail::to($email)
                ->later(now()->addSeconds($delaySeconds), $mailable);

            Log::debug('Email queued', [
                'email' => $email,
                'queue' => $queue,
                'delay' => $delaySeconds
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to queue email', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Dispatch queue job dengan error handling
     *
     * @param object $job
     * @param string $queue
     * @param int $delaySeconds
     * @return bool Success status
     */
    protected function dispatchJob(
        object $job,
        string $queue = 'mail',
        int $delaySeconds = 5
    ): bool {
        try {
            dispatch($job)
                ->onQueue($queue)
                ->delay(now()->addSeconds($delaySeconds));

            Log::debug('Job dispatched', [
                'job' => get_class($job),
                'queue' => $queue,
                'delay' => $delaySeconds
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to dispatch job', [
                'job' => get_class($job),
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Log notification activity
     *
     * @param string $action
     * @param int $documentId
     * @param array $context
     * @return void
     */
    protected function logNotificationActivity(string $action, int $documentId, array $context = []): void
    {
        Log::info("Notification: {$action}", array_merge([
            'type' => $this->getNotificationType(),
            'document_id' => $documentId,
        ], $context));
    }
}
