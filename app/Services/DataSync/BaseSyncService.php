<?php

namespace App\Services\DataSync;

use App\Exceptions\DataSyncException;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class BaseSyncService
{
    /**
     * Execute sync operation within a transaction
     *
     * @throws DataSyncException
     */
    protected function executeSync(callable $syncOperation): mixed
    {
        DB::beginTransaction();

        try {
            $result = $syncOperation();
            DB::commit();

            return $result;
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Sync failed: '.$e->getMessage(), [
                'exception' => $e,
                'service' => static::class,
            ]);

            throw new DataSyncException(
                'Failed to sync data: '.$e->getMessage(),
                previous: $e
            );
        }
    }

    /**
     * Log sync progress
     */
    protected function logProgress(string $message, array $context = []): void
    {
        Log::info("[{$this->getSyncName()}] {$message}", $context);
    }

    /**
     * Get sync operation name for logging
     */
    abstract protected function getSyncName(): string;
}
