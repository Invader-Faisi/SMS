<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'staff_id',
        'salary_structure_id',
        'gross_salary',
        'total_deduction',
        'net_salary',
        'payment_date',
        'payment_method',
        'account',
        'status',
    ];

    public function teacher(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'teacher_id');
    }

    public function staff(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }

    public function structure(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SalaryStructure::class, 'salary_structure_id');
    }


    #[Scope]
    public function scopeSearch(Builder $query, $value): void
    {
        if (!empty($value)) {
            $query->where(function ($q) use ($value) {
                $q->where('account', 'LIKE', "%{$value}%")
                    ->orWhere('status', 'LIKE', "%{$value}%")
                    ->orWhereHas('teacher', function ($teacherQuery) use ($value) {
                        $teacherQuery->where('name', 'LIKE', "%{$value}%")
                            ->orWhere('designation', 'LIKE', "%{$value}%")
                            ->orWhere('teacher_id', 'LIKE', "%{$value}%");
                    })
                    ->orWhereHas('staff', function ($staffQuery) use ($value) {
                        $staffQuery->where('name', 'LIKE', "%{$value}%")
                            ->orWhere('designation', 'LIKE', "%{$value}%")
                            ->orWhere('staff_id', 'LIKE', "%{$value}%");
                    });
            });
        }
    }
}
