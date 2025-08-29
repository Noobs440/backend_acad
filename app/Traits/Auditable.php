<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\SoftDeletes;

trait Auditable
{
    public static function bootAuditable()
    {
        // Événements de base
        $events = ['created', 'updated', 'deleted'];

        // Si le modèle utilise SoftDeletes, on ajoute restored/forceDeleted
        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            $events[] = 'restored';
            $events[] = 'forceDeleted';
        }

        foreach ($events as $event) {
            static::$event(function ($model) use ($event) {
                $model->logActivity($event);
            });
        }
    }

    protected function logActivity(string $event): void
    {
        try {
            $user = Auth::user();

            $old = null;
            $new = null;

            if ($event === 'updated') {
                $old = array_intersect_key(
                    $this->getOriginal(),
                    $this->getDirty()
                );
                $new = $this->getDirty();
            } elseif ($event === 'deleted') {
                $old = $this->getOriginal();
            } elseif ($event === 'created' || $event === 'restored') {
                $new = $this->getAttributes();
            }

            ActivityLog::create([
                'user_id'     => $user?->id,
                'subject_type'=> get_class($this),
                'subject_id'  => $this->getKey(),
                'event'       => $event,
                'description' => ucfirst($event) . " sur " . class_basename($this),
                'old_values'  => $old,
                'new_values'  => $new,
                'url'         => request()?->fullUrl(),
                'method'      => request()?->method(),
                'ip_address'  => request()?->ip(),
                'user_agent'  => request()?->userAgent(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Audit log error: ' . $e->getMessage());
        }
    }
}
