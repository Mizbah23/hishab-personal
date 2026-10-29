<?php

namespace App\Models\Concerns;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::created(function (Model $model): void {
            $model->recordActivity('created');
        });

        static::updated(function (Model $model): void {
            $model->recordActivity('updated');
        });

        static::deleted(function (Model $model): void {
            $model->recordActivity('deleted');
        });
    }

    public function recordActivity(string $action): void
    {
        $changes = match ($action) {
            'created' => Arr::except($this->getAttributes(), ['created_at', 'updated_at']),
            'updated' => Arr::except($this->getChanges(), ['updated_at']),
            default => [],
        };

        $changes = Arr::except($changes, ['attachment_path', 'attachment_name']);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => get_class($this),
            'auditable_id' => $this->getKey(),
            'description' => sprintf('%s %s', class_basename($this).' '.$this->auditLabel(), $action),
            'changes' => $changes ?: null,
        ]);
    }

    protected function auditLabel(): string
    {
        return (string) ($this->title ?? $this->voucher_number ?? $this->name ?? '#'.$this->getKey());
    }
}
