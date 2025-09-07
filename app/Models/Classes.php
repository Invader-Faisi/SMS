<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Classes extends Model
{
    use HasFactory;

    /**
     * @var mixed|string
     */

    protected $fillable = [
        'class_id',
        'teacher_id',
        'capacity',
        'academic_year',
    ];

    public static $classes = ['Nursery','Prep','I','II','III','IV','V','VI','VII','VIII','IX','X'];
    public static $sections = ['A', 'B', 'C', 'D', 'E', 'F'];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'teacher_id');
    }

    public function timetables(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Timetable::class, 'class_id', 'class_id');
    }

    #[Scope]
    public function scopeSearch(Builder $query,$value):void
    {
        if (!empty($value)) {
            $query->where(function ($q) use ($value) {
                $q->where('class_id', 'LIKE', "%{$value}%")
                    ->orWhere('subject', 'LIKE', "%{$value}%");
            });
        }
    }

}
