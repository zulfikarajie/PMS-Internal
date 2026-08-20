<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id', 'action', 'model_type', 'model_id', 'old_value', 'new_value',
    ];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $action, Model $model, ?array $oldValue = null, ?array $newValue = null): void
    {
        self::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'old_value' => $oldValue,
            'new_value' => $newValue,
        ]);
    }
}