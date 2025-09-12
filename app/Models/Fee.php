<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'fee_structure_id',
        'amount',
        'due_date',
        'pending_amount',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function feeStructure(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(FeeStructure::class, 'fee_structure_id', 'id');
    }

    #[Scope]
    public function scopeSearch(Builder $query, $value): void
    {
        if (!empty($value)) {
            $query->where(function ($q) use ($value) {
                $q->where('student_id', 'LIKE', "%{$value}%")
                    ->orWhere('status', 'LIKE', "%{$value}%")
                    ->orWhereHas('student', function ($studentQuery) use ($value) {
                        $studentQuery->where('class', 'LIKE', "%{$value}%")
                            ->orWhere('name', 'LIKE', "%{$value}%"); // optional: search by student name too
                    });
            });
        }
    }

}
