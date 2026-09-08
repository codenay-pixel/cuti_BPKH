<?php

namespace App\Logging;

use Illuminate\Log\Logger;
use Illuminate\Support\Str;

class TambahKonteksAudit
{
    public function __invoke(Logger $logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->pushProcessor(function ($record) {
                $request = app()->bound('request') ? request() : null;
                $user = $request ? auth()->user() : null;

                $requestId = null;

                if ($request) {
                    if (! $request->attributes->has('audit_request_id')) {
                        $request->attributes->set('audit_request_id', (string) Str::uuid());
                    }

                    $requestId = $request->attributes->get('audit_request_id');
                }

                $konteks = [
                    'request_id' => $requestId,
                    'ip'         => $request?->ip(),
                    'user_agent' => $request?->userAgent(),
                    'user_id'    => $user?->id,
                    'user_nama'  => $user?->name,
                    'user_peran' => $user?->role,
                ];

                if (method_exists($record, 'with')) {

                    return $record->with(extra: array_merge($record->extra, $konteks));
                }

                $record['extra'] = array_merge($record['extra'] ?? [], $konteks);

                return $record;
            });
        }
    }
}
