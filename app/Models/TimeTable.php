<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TimeTable extends Model
{
    protected $fillable = [
      'class_id',
      'teacher_id',
      'subject',
      'days',
      'period',
      'start_time',
      'end_time',
    ];

    public function class(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Classes::class, 'class_id', 'class_id');
    }

    public function teacher(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'teacher_id');
    }

    #[Scope]
    public function scopeSearch(Builder $query,$value):void
    {
        if (!empty($value)) {
            $query->where(function ($q) use ($value) {
                $q->where('class_id', 'LIKE', "%{$value}%")
                    ->orWhere('subject', 'LIKE', "%{$value}%")
                    ->orWhere('days', 'LIKE', "%{$value}%");
            });
        }

    }
}
