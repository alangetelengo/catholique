<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Throwable;

trait LogsErrors
{
    /**
     * Accepte les 2 signatures présentes dans le codebase:
     * - logError(Throwable $e, string $message, array $context = [])
     * - logError(string $message, Throwable $e, array $context = [])
     */
    protected function logError(Throwable|string $arg1, Throwable|string|null $arg2 = null, array $context = []): void
    {
        $message = 'Erreur application';
        $exception = null;

        if ($arg1 instanceof Throwable) {
            $exception = $arg1;
            $message = is_string($arg2) ? $arg2 : $message;
        } else {
            $message = $arg1;
            if ($arg2 instanceof Throwable) {
                $exception = $arg2;
            }
        }

        if ($exception instanceof Throwable) {
            $context = array_merge($context, [
                'exception' => $exception::class,
                'exception_message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);
        }

        Log::channel('paroisse')->error($message, $context);
    }

    protected function logInfo(string $message, array $context = []): void
    {
        Log::channel('paroisse')->info($message, $context);
    }

    protected function logWarning(string $message, array $context = []): void
    {
        Log::channel('paroisse')->warning($message, $context);
    }
}
