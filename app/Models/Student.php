<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    use HasFactory;
    protected $fillable = [
        'student_id',
        'parent_id',
        'image',
        'name',
        'password',
        'class',
        'section',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Parents::class, 'parent_id', 'parent_id');
    }

    #[Scope]
    public function scopeSearch(Builder $query,$value):void
    {
        $query->where(function ($q) use ($value) {
            $q->where('students.name', 'LIKE', "%{$value}%")
                ->orWhere('students.student_id', 'LIKE', "%{$value}%")
                ->orWhere('students.class', 'LIKE', "%{$value}%")
                ->orWhere('students.section', 'LIKE', "%{$value}%")
                ->orWhereHas('parent', function ($parentQuery) use ($value) {
                    $parentQuery->where('name', 'LIKE', "%{$value}%");
                });
        });
    }
}
